<?php

namespace App\Http\Middleware;

use Closure;

class AllowActivationRoute
{
    /**
     * Allow activation route to be handled when system is not activated,
     * so we don't create a redirect loop.
     */
    public function handle($request, Closure $next)
    {
        // If the request is for the activation UI or its POST handler, allow it
        if ($request->is('activate') || $request->is('activate/*') || $request->routeIs('activate')) {
            return $next($request);
        }

        // Otherwise, proceed (other middleware can decide to redirect)
        return $next($request);
    }
}
