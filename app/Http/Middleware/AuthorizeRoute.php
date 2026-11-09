<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks any request whose route the signed-in user has no permission for.
 * The required permission is derived from the route name (see App\Support\Permissions).
 */
class AuthorizeRoute
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        if (! $user || ! $user->canAccessRoute($routeName)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
