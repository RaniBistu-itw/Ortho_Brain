<?php

namespace App\Http\Middleware;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    public function __construct(private RecaptchaService $captcha) {}

    /**
     * Verify the `g-recaptcha-response` token submitted with the form.
     *
     * Usage in routes: ->middleware('recaptcha:register')
     * The string after the colon is the action name passed to
     * grecaptcha.execute() in the page's JS — it must match what
     * Google echoes back in the verify response.
     */
    public function handle(Request $request, Closure $next, string $action = ''): Response
    {
        $token = (string) $request->input('g-recaptcha-response', '');

        if (! $this->captcha->verify($token, $action)) {
            return back()
                ->withInput($request->except(['password', 'confirm_password', 'password_confirmation', 'g-recaptcha-response']))
                ->withErrors(['captcha' => 'CAPTCHA verification failed. Please try again.']);
        }

        return $next($request);
    }
}
