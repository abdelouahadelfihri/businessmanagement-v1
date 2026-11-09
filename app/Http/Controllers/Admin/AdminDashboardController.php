<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'roles' => Role::count(),
            'logged_today' => User::where('last_login_at', '>=', now()->startOfDay())->count(),
            'never_logged' => User::whereNull('last_login_at')->count(),
        ];

        $roles = Role::withCount('users')->orderByDesc('is_super')->orderBy('name')->get();
        $unassigned = User::whereNull('role_id')->count();

        $recentLogins = User::with('role')->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')->limit(8)->get();

        $newestUsers = User::with('role')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'roles', 'unassigned', 'recentLogins', 'newestUsers'));
    }
}
