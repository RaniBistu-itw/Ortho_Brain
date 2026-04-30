<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Observers\DoctorObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Doctor::observe(DoctorObserver::class);

        $this->configureRateLimiters();
    }

    /**
     * Named rate limiters used by `throttle:<name>` middleware on auth routes.
     *
     * Each closure runs per request and may key off live request data
     * (e.g. IP + email composite for login). Keep these in sync with the
     * middleware references in routes/web.php.
     */
    private function configureRateLimiters(): void
    {
        // Register: 5 per minute per IP. Tight enough to slow bots; short
        // window means a tripped IP only waits ~60s before retrying.
        RateLimiter::for('register', fn (Request $r) =>
            Limit::perMinute(5)->by($r->ip()));

        // Login uses a per-account lockout (failed_login_attempts /
        // locked_until on users) instead of an IP/email rate limiter, so
        // there's no `throttle:login` named limiter here.

        // Password reset: keep a bot from flooding a victim's mailbox with
        // reset links / using the form to enumerate registered emails.
        RateLimiter::for('password-reset', fn (Request $r) =>
            Limit::perHour(3)->by($r->ip()));

        // OTP submission: 5 attempts per 15 minutes per email. With a
        // 30-minute OTP lifetime, 1,000,000 codes can't be brute-forced
        // within the validity window.
        RateLimiter::for('verify-otp', fn (Request $r) =>
            Limit::perMinutes(15, 5)->by(strtolower((string) ($r->input('email') ?: $r->ip()))));

        // Resend verification: cap at 3 per hour per email so an attacker
        // can't mail-bomb a real inbox by repeatedly hitting "resend".
        RateLimiter::for('resend-verification', fn (Request $r) =>
            Limit::perHour(3)->by(strtolower((string) ($r->input('email') ?: $r->ip()))));
    }
}
