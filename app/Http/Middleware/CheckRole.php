<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $request->user();

        // Check if user is active
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.'
            ], 403);
        }

        // Check if user has any of the required roles
        if (!empty($roles) && !in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
