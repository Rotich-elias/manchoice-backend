<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

        // Check if user is super admin
        if (auth()->user()->role !== User::ROLE_SUPER_ADMIN) {
            abort(403, 'Unauthorized. Super admin access only.');
        }

        return $next($request);
    }
}
