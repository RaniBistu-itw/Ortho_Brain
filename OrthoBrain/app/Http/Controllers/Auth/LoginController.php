<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivePractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Account lockout policy: lock for 2 minutes after 5 consecutive
     * failed password attempts. Reset on successful login.
     */
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES     = 2;

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

        // Look up the account ahead of Auth::attempt so we can apply the
        // lockout gate without leaking which emails exist (the message
        // for "no such user" matches "wrong password" below).
        $user = User::where('email', $credentials['email'])->first();

        // Gate 1: account-level lockout. Flash the unlock instant as ISO
        // so the login view can render a live countdown banner; the
        // browser ticks down locally against this absolute timestamp.
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            return back()
                ->with('locked_until', $user->locked_until->toIso8601String())
                ->with('locked_email', $user->email)
                ->onlyInput('email');
        }

        // Gate 2: credentials. On failure, increment the per-account
        // counter and lock if we hit the threshold.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($user) {
                $user->forceFill([
                    'failed_login_attempts' => $user->failed_login_attempts + 1,
                ])->save();

                if ($user->failed_login_attempts >= self::MAX_FAILED_ATTEMPTS) {
                    $user->forceFill([
                        'failed_login_attempts' => 0,
                        'locked_until'          => now()->addMinutes(self::LOCKOUT_MINUTES),
                    ])->save();

                    // Tripped on this attempt — flash the unlock instant
                    // so the view shows the countdown banner immediately.
                    return back()
                        ->with('locked_until', $user->locked_until->toIso8601String())
                        ->with('locked_email', $user->email)
                        ->onlyInput('email');
                }
            }

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // Successful password — clear the failure counter & any prior lock.
        $user->forceFill([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ])->save();

        // Gate 3: email verification. Doctors must verify their email
        // before the account is usable. (Admin / system accounts created
        // by seeders are pre-verified by the migration backfill.)
        if (is_null($user->email_verified_at)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('verify-email.show', ['email' => $user->email])
                ->withErrors(['email' => 'Please verify your email before signing in. We\'ve sent you a 6-digit code.'])
                ->onlyInput('email');
        }

        // Gate 4: doctor approval status (existing behaviour, unchanged).
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

        // Admins → /admin dashboard; doctors → dashboard (or pending page if no active practice yet)
        if ($user->role === 'ADMIN') {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Bootstrap the active-practice session pointer; if the doctor has no
        // approved practices yet, send them to the pending page instead of cases.
        if (! ActivePractice::get()) {
            return redirect()->route('doctor.practices.pending');
        }

        return redirect()->intended(route('doctor.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
