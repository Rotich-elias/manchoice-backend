<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationFeePaid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, let the auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if registration fee is paid
        if (!$user->registration_fee_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Please pay the registration fee to access this feature',
                'fee_required' => true,
                'fee_amount' => 300.00,
            ], 402); // 402 Payment Required
        }

        return $next($request);
    }
}
