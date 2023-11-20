<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if the user is authenticated
        if (!$request->user()) {
            return response('Unauthorized action.', 403);
        }

        // Dump or log user roles for debugging
        // dump($request->user()->roles);

        // Check if the user has any of the required roles
        foreach ($roles as $role) {
            if ($role === 'admin' && $request->user()->isAdmin()) {
                return $next($request);
            } elseif ($request->user()->hasAnyRole($role)) {
                return $next($request);
            }
        }

        // If none of the roles match, return a 403 Forbidden response
        return response('Forbidden action.', 403);
    }
}
