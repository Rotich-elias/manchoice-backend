<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and has admin role
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials'])
                ->withInput($request->only('email'));
        }

        // Check if user has staff role (not customer)
        $allowedRoles = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN,
            User::ROLE_MANAGER,
            User::ROLE_CLERK,
            User::ROLE_COLLECTOR,
        ];
        if (!in_array($user->role, $allowedRoles)) {
            return back()
                ->withErrors(['email' => 'Access denied. Staff access only.'])
                ->withInput($request->only('email'));
        }

        // Log the user in
        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
