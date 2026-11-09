<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        // Inactive users can do nothing; super admins can do everything.
        Gate::before(function (User $user) {
            if (! $user->is_active) {
                return false;
            }

            return $user->isSuperAdmin() ? true : null;
        });

        // One gate per permission key, usable as @can('customers.edit').
        foreach (Permissions::all() as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        // @canroute('sales.salesorders.edit') ... @endcanroute: shows a link or button
        // only when the current user may open that route.
        Blade::if('canroute', function (string $routeName) {
            return (bool) auth()->user()?->canAccessRoute($routeName);
        });

        // @canany-style helper for sidebar groups: any of the given routes.
        Blade::if('cananyroute', function (string ...$routeNames) {
            $user = auth()->user();
            if (! $user) {
                return false;
            }
            foreach ($routeNames as $name) {
                if ($user->canAccessRoute($name)) {
                    return true;
                }
            }

            return false;
        });
    }
}
