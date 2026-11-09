<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('role')
            ->search($request->input('search'))
            ->when($request->filled('role'), function ($q) use ($request) {
                $request->input('role') === 'none'
                    ? $q->whereNull('role_id')
                    : $q->where('role_id', $request->input('role'));
            })
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(Request $request)
    {
        return view('admin.users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $sendInvite = $request->input('password_mode') === 'email';

        $data['password'] = $sendInvite ? Str::random(40) : $data['password'];
        $user = User::create($data);

        if ($sendInvite) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return redirect()->route('admin.users.index')->with('success', $sendInvite
            ? "User {$user->name} created. A link to choose a password was emailed to {$user->email}."
            : "User {$user->name} created.");
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(Request $request, User $user)
    {
        $this->guardTarget($request->user(), $user);

        return view('admin.users.edit', [
            'user' => $user->load('role'),
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $actor = $request->user();
        $this->guardTarget($actor, $user);

        $data = $this->validated($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Nobody changes their own role or deactivates themselves from here.
        if ($actor->is($user)) {
            $data['role_id'] = $user->role_id;
            $data['is_active'] = true;
        }

        if ($this->wouldRemoveLastSuperAdmin($user, $data['role_id'] ?? null, $data['is_active'])) {
            return back()->withInput()->with('error', 'At least one active administrator must remain.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} updated.");
    }

    public function destroy(Request $request, User $user)
    {
        $actor = $request->user();
        $this->guardTarget($actor, $user);

        if ($actor->is($user)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($this->wouldRemoveLastSuperAdmin($user, null, false)) {
            return back()->with('error', 'At least one active administrator must remain.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} deleted.");
    }

    public function toggle(Request $request, User $user)
    {
        $actor = $request->user();
        $this->guardTarget($actor, $user);

        if ($actor->is($user)) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->is_active && $this->wouldRemoveLastSuperAdmin($user, $user->role_id, false)) {
            return back()->with('error', 'At least one active administrator must remain.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', "User {$user->name} " . ($user->is_active ? 'activated.' : 'deactivated.'));
    }

    public function resetLink(Request $request, User $user)
    {
        $this->guardTarget($request->user(), $user);

        if (! $user->is_active) {
            return back()->with('error', 'Activate the user before sending a reset link.');
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', "Password reset link sent to {$user->email}.")
            : back()->with('error', __($status));
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $roleIds = $this->assignableRoles($request->user())->pluck('id')->all();
        $creating = $user === null;
        $needsPassword = $creating && $request->input('password_mode') !== 'email';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_id' => ['nullable', Rule::in($roleIds)],
            'password_mode' => ['nullable', 'in:set,email'],
            'password' => [$needsPassword ? 'required' : 'nullable', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [
            'role_id.in' => 'You are not allowed to assign this role.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        unset($data['password_mode']);

        return $data;
    }

    /**
     * Roles the actor may hand out: super admins can give any role; others only
     * roles whose permissions they hold themselves (no privilege escalation).
     */
    private function assignableRoles(User $actor): Collection
    {
        $roles = Role::orderByDesc('is_super')->orderBy('name')->get();

        if ($actor->isSuperAdmin()) {
            return $roles;
        }

        return $roles->filter(function (Role $role) use ($actor) {
            if ($role->is_super) {
                return false;
            }
            foreach ($role->permissions ?? [] as $permission) {
                if (! $actor->hasPermission($permission)) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /** Only super admins may manage super-admin accounts. */
    private function guardTarget(User $actor, User $target): void
    {
        if ($target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            abort(403, 'Only administrators can manage administrator accounts.');
        }
    }

    private function wouldRemoveLastSuperAdmin(User $user, $newRoleId, bool $staysActive): bool
    {
        if (! $user->isSuperAdmin() || ! $user->is_active) {
            return false;
        }

        $stillSuper = $staysActive && Role::whereKey($newRoleId)->where('is_super', true)->exists();
        if ($stillSuper) {
            return false;
        }

        $others = User::where('id', '!=', $user->id)
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->where('is_super', true))
            ->exists();

        return ! $others;
    }
}
