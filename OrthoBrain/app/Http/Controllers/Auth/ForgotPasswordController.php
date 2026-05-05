<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('forgot-password', ['status' => session('status')]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:150',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if ($user) {
            $plainToken = Str::random(64);

            $user->password_reset_token      = hash('sha256', $plainToken);
            $user->password_reset_expires_at = now()->addHour();
            $user->save();

            Mail::to($user->email)->send(new ResetPasswordMail($user, $plainToken));
        }

        return redirect()
            ->route('password.request')
            ->with('status', 'If an account with that email exists, a reset link has been sent.');
    }

    public function showReset(Request $request, string $token)
    {
        $email = $request->query('email');
        $user  = $this->resolveUserFromToken($email, $token);

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        return view('reset-password', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|max:150',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/\d/|regex:/[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\\\\/]/',
            'password_confirmation' => 'required|string',
        ], [
            'password.regex' => 'Password must include uppercase, lowercase, number & special character.',
        ]);

        $user = $this->resolveUserFromToken($request->email, $request->token);

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        $user->password_hash              = Hash::make($request->password);
        $user->password_reset_token       = null;
        $user->password_reset_expires_at  = null;
        $user->save();

        return redirect('/login')->with('success', 'Password reset successfully. Please log in with your new password.');
    }

    private function resolveUserFromToken(?string $email, ?string $plainToken): ?User
    {
        if (! $email || ! $plainToken) {
            return null;
        }

        $user = User::where('email', $email)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $user || ! $user->password_reset_token || ! $user->password_reset_expires_at) {
            return null;
        }

        if ($user->password_reset_expires_at->isPast()) {
            return null;
        }

        if (! hash_equals($user->password_reset_token, hash('sha256', $plainToken))) {
            return null;
        }

        return $user;
    }
}
