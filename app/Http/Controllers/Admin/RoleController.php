<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderByDesc('is_super')->orderBy('name')->get();
        $total = count(Permissions::all());

        return view('admin.roles.index', compact('roles', 'total'));
    }

    public function create(Request $request)
    {
        $copyFrom = $request->filled('copy') ? Role::find($request->input('copy')) : null;

        $role = new Role([
            'name' => $copyFrom ? $copyFrom->name . ' (copy)' : '',
            'description' => $copyFrom?->description,
        ]);
        $role->permissions = $copyFrom?->is_super ? Permissions::all() : ($copyFrom?->permissions ?? []);

        return view('admin.roles.create', $this->formData($request->user(), $role));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $role = Role::create($data);

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} created.");
    }

    public function show(Role $role)
    {
        return redirect()->route('admin.roles.edit', $role);
    }

    public function edit(Request $request, Role $role)
    {
        $this->guardRole($request->user(), $role);

        return view('admin.roles.edit', $this->formData($request->user(), $role->loadCount('users')));
    }

    public function update(Request $request, Role $role)
    {
        $this->guardRole($request->user(), $role);

        $data = $this->validated($request, $role);

        // The super role always keeps every permission.
        if ($role->is_super) {
            $data['permissions'] = [];
        }

        $role->update($data);

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} updated.");
    }

    public function destroy(Request $request, Role $role)
    {
        $this->guardRole($request->user(), $role);

        if ($role->is_super) {
            return back()->with('error', 'The administrator role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', "Role {$role->name} is still assigned to users. Move them to another role first.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} deleted.");
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $actor = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permissions::all())],
        ]);

        $data['permissions'] = $data['permissions'] ?? [];

        // A non-administrator can only grant permissions they hold themselves.
        if (! $actor->isSuperAdmin()) {
            $data['permissions'] = array_values(array_filter(
                $data['permissions'],
                fn ($p) => $actor->hasPermission($p)
            ));
        }

        return $data;
    }

    /** Non-administrators cannot edit the administrator role or their own role. */
    private function guardRole(User $actor, Role $role): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        if ($role->is_super || $actor->role_id === $role->id) {
            abort(403, 'You cannot change this role.');
        }
    }

    private function formData(User $actor, Role $role): array
    {
        return [
            'role' => $role,
            'groups' => Permissions::grouped(),
            'actions' => Permissions::actions(),
            'granted' => $role->is_super ? Permissions::all() : ($role->permissions ?? []),
            'grantable' => $actor->isSuperAdmin()
                ? Permissions::all()
                : array_values(array_filter(Permissions::all(), fn ($p) => $actor->hasPermission($p))),
        ];
    }
}
