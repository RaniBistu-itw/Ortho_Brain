<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ], [
            'email.required'    => 'This field is required.',
            'email.email'       => 'Please enter valid email.',
            'password.required' => 'This field is required.',
            'password.min'      => 'The password must be at least 8 characters.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Update last_login_at (ERD field)
            $user->forceFill(['last_login_at' => now()])->save();

            $request->session()->regenerate();

            // Admins → /admin dashboard; doctors → cases list
            if ($user->role === 'ADMIN') {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended('/dev/cases/list');
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}