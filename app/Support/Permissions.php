<?php

namespace App\Support;

/**
 * Maps route names to permission keys ("<module>.<action>") and lists every
 * permission the role editor can grant.
 */
class Permissions
{
    /** Sentinel: the route is open to every signed-in user. */
    public const PUBLIC = 'public';

    /** Sentinel: no permission maps to the route, so only super admins pass. */
    public const SUPER_ONLY = 'super';

    /**
     * Permissions that grant access to a route.
     *
     * @return string|array<int,string> PUBLIC, SUPER_ONLY or a list of keys (any one is enough)
     */
    public static function forRoute(?string $routeName): string|array
    {
        if (! $routeName) {
            return self::SUPER_ONLY;
        }

        if (in_array($routeName, config('permissions.public_routes', []), true)) {
            return self::PUBLIC;
        }

        $explicit = config('permissions.route_permissions', []);
        if (isset($explicit[$routeName])) {
            return (array) $explicit[$routeName];
        }

        $segments = explode('.', $routeName);
        if (count($segments) < 2) {
            return self::SUPER_ONLY;
        }

        $routeAction = array_pop($segments);
        $routeModule = end($segments);

        $aliases = config('permissions.module_aliases', []);
        $module = $aliases[$routeModule . '.' . $routeAction] ?? $aliases[$routeModule] ?? $routeModule;
        $action = config('permissions.route_actions.' . $routeAction);

        $definition = self::module($module);
        if (! $action || ! $definition || ! in_array($action, $definition['actions'], true)) {
            return self::SUPER_ONLY;
        }

        return [$module . '.' . $action];
    }

    /** @return array{label:string, actions:array<int,string>}|null */
    public static function module(string $key): ?array
    {
        foreach (config('permissions.modules', []) as $modules) {
            if (isset($modules[$key])) {
                return $modules[$key];
            }
        }

        return null;
    }

    /** @return array<string, array<string, array{label:string, actions:array<int,string>}>> */
    public static function grouped(): array
    {
        return config('permissions.modules', []);
    }

    /** @return array<string,string> */
    public static function actions(): array
    {
        return config('permissions.actions', []);
    }

    /** Every grantable permission key. */
    public static function all(): array
    {
        $keys = [];
        foreach (self::grouped() as $modules) {
            foreach ($modules as $module => $definition) {
                foreach ($definition['actions'] as $action) {
                    $keys[] = $module . '.' . $action;
                }
            }
        }

        return $keys;
    }
}
