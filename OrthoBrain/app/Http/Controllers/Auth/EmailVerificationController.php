<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * GET /verify-email — show the OTP entry form. Email is passed as a
     * query parameter from the registration redirect; no auth required.
     */
    public function showForm(Request $request)
    {
        $email = (string) $request->query('email', '');

        return view('auth.verify-email', [
            'email' => $email,
        ]);
    }

    /**
     * POST /verify-email — user submitted the 6-digit code.
     *
     * Validates code, marks the user verified, and sends them to /login.
     */
    public function submitOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Same generic response for "no such user" and "already verified" so
        // the form doesn't leak which emails are registered.
        if (! $user) {
            return back()->withErrors(['otp' => 'Invalid or expired code.'])->withInput();
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')->with('success', 'Your email is already verified — you can sign in.');
        }

        if (! $user->verification_otp
            || ! $user->verification_otp_expires_at
            || $user->verification_otp_expires_at->isPast()
            || ! hash_equals((string) $user->verification_otp, (string) $data['otp'])
        ) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please request a new one.'])->withInput();
        }

        $this->markVerified($user);

        return redirect()->route('login')->with('success', 'Email verified successfully. Your account is pending admin approval — you\'ll receive an email once approved.');
    }

    /**
     * GET /verify-email/link/{user} — user clicked the link in their email.
     *
     * Laravel's `signed` middleware on the route guarantees the URL came
     * from us and hasn't been tampered with. We still match the OTP from
     * the query string against the DB so an old (already-used) link can't
     * verify someone twice.
     */
    public function verifyLink(Request $request, User $user)
    {
        if ($user->email_verified_at) {
            return redirect()->route('login')->with('success', 'Your email is already verified — you can sign in.');
        }

        $otp = (string) $request->query('otp', '');

        if (! $otp
            || ! $user->verification_otp
            || ! $user->verification_otp_expires_at
            || $user->verification_otp_expires_at->isPast()
            || ! hash_equals((string) $user->verification_otp, $otp)
        ) {
            return redirect()
                ->route('verify-email.show', ['email' => $user->email])
                ->withErrors(['otp' => 'This verification link is invalid or has expired. Please request a new one.']);
        }

        $this->markVerified($user);

        return redirect()->route('login')->with('success', 'Email verified successfully. Your account is pending admin approval — you\'ll receive an email once approved.');
    }

    /**
     * POST /verify-email/resend — generate a fresh OTP + signed link and
     * email it. Rate-limited via `throttle:resend-verification`.
     */
    public function resend(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // Always show a generic success message so the form can't be used
        // to enumerate registered emails. (Real users get the email; bots
        // probing random addresses see the same response.)
        $genericRedirect = back()->with('status', 'If an unverified account exists for that email, a new verification message has been sent.');

        if (! $user || $user->email_verified_at) {
            return $genericRedirect;
        }

        $this->sendVerificationEmail($user);

        return $genericRedirect;
    }

    /**
     * Generate a fresh OTP, persist it, build a signed verification link,
     * and queue the email. Used by both the resend endpoint and (via the
     * static helper below) by RegisterController.
     */
    public static function sendVerificationEmail(User $user): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'verification_otp'             => $otp,
            'verification_otp_expires_at'  => now()->addMinutes(30),
        ])->save();

        $verifyUrl = URL::temporarySignedRoute(
            'verify-email.link',
            now()->addMinutes(30),
            ['user' => $user->id, 'otp' => $otp],
        );

        Mail::to($user->email)->queue(new EmailVerificationMail($user, $otp, $verifyUrl));
    }

    private function markVerified(User $user): void
    {
        $user->forceFill([
            'email_verified_at'            => now(),
            'verification_otp'             => null,
            'verification_otp_expires_at'  => null,
        ])->save();

        // Reveal the Doctor row to admin queries. RegisterController
        // soft-deletes it at registration time so unverified sign-ups don't
        // clutter the admin approval queue; restoring here makes the row
        // visible exactly when the user proves they own the inbox.
        $doctor = $user->doctor()->withTrashed()->first();
        if ($doctor && $doctor->trashed()) {
            $doctor->restore();
        }
    }
}
