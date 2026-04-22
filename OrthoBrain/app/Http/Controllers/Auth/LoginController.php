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

            // Doctors must be APPROVED before they can log in.
            if ($user->role === 'DOCTOR') {
                $status = $user->doctor?->approval_status;
                if ($status !== 'APPROVED') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $msg = match ($status) {
                        'PENDING'   => 'Your account is pending admin approval. You\'ll receive an email once it\'s approved.',
                        'REJECTED'  => 'Your registration was not approved. Please contact support for details.',
                        'SUSPENDED' => 'Your account has been suspended. Please contact support.',
                        default     => 'Your account is not yet active. Please contact support.',
                    };
                    return back()->withErrors(['email' => $msg])->onlyInput('email');
                }
            }

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