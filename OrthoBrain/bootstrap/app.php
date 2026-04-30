<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'           => \App\Http\Middleware\EnsureSuperAdmin::class,
            'active.practice' => \App\Http\Middleware\EnsureActivePractice::class,
            'recaptcha'       => \App\Http\Middleware\VerifyRecaptcha::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\LogSlowRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Graceful render for ThrottleRequestsException on browser
        // submissions: instead of bubbling up to a raw 429 / debug page,
        // redirect back to the form with input preserved (minus passwords)
        // and flash the absolute retry-at instant. Pages that opt in
        // (login, register) read this and render a live countdown banner;
        // pages that don't at least see their form again with their input.
        //
        // JSON / AJAX clients short-circuit (return null) so Laravel's
        // default 429 JSON response stays the contract for API callers.
        //
        // Registered first so the PHP 8.3 var-dumper fallback below never
        // sees this exception — render callbacks run in registration order
        // and the first non-null response wins.
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return null;
            }

            $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 60);
            $retryAt    = now()->addSeconds($retryAfter)->toIso8601String();

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('throttle_retry_at', $retryAt)
                ->with('throttle_message', 'Too many attempts. Please wait '
                    . max(1, (int) ceil($retryAfter / 60)) . ' minute(s) and try again.');
        });

        // Local PHP 8.3 dev workaround.
        //
        // The team's composer.json pins symfony/var-dumper v8 (Symfony 8),
        // whose Caster::getClassProperties() calls $reflectionProperty->isVirtual()
        // — a method added in PHP 8.4. On this machine (PHP 8.3) every uncaught
        // exception triggers a SECOND fatal inside Symfony's HtmlErrorRenderer
        // when it tries to clone the exception via VarCloner, leaving the user
        // staring at a blank 500 with no useful info.
        //
        // This callback runs ONLY when:
        //   - PHP < 8.4, AND
        //   - APP_DEBUG=true, AND
        //   - the request expects an HTML response (JSON/AJAX paths don't go
        //     through HtmlErrorRenderer so they were unaffected anyway).
        //
        // It returns a minimal but useful debug page built from the exception's
        // own getMessage() / getFile() / getTraceAsString() — no var-dumper,
        // no FlattenException::createWithDataRepresentation(), no crash.
        //
        // Has zero effect on PHP 8.4+ (where the team's setup works fine) and
        // zero effect in production (APP_DEBUG=false there).
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (\PHP_VERSION_ID >= 80400) {
                return null;
            }
            if (! config('app.debug')) {
                return null;
            }
            if ($request->expectsJson() || $request->ajax()) {
                return null;
            }
            // Skip exceptions Laravel converts to specific responses
            // (redirects, validation errors, 419 CSRF). Without these skips
            // we'd e.g. show our debug page for AuthenticationException
            // instead of redirecting the user to /login.
            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return null;
            }

            $class   = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
            $message = htmlspecialchars($e->getMessage() ?: '(no message)', ENT_QUOTES, 'UTF-8');
            $file    = htmlspecialchars($e->getFile().':'.$e->getLine(), ENT_QUOTES, 'UTF-8');
            $trace   = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
            $url     = htmlspecialchars($request->fullUrl(), ENT_QUOTES, 'UTF-8');
            $method  = htmlspecialchars($request->method(), ENT_QUOTES, 'UTF-8');
            $status  = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $php     = htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8');

            $html = <<<HTML
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>{$class}</title>
<style>
body{font:14px/1.5 -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#1f2937;color:#e5e7eb;padding:2rem;margin:0}
.wrap{max-width:1100px;margin:0 auto}
h1{color:#f87171;font-size:1.35rem;margin:0 0 .25rem;font-weight:600;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
.sub{color:#fde68a;font-size:.95rem;margin:0 0 1rem}
.box{background:#111827;border:1px solid #374151;border-radius:.5rem;padding:1rem 1.25rem;margin:1rem 0}
.box h2{margin:0 0 .5rem;font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;font-weight:600}
pre{margin:0;white-space:pre-wrap;word-break:break-word;font:12px/1.55 ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;color:#f3f4f6}
.note{font-size:.72rem;color:#6b7280;margin-top:1.5rem;padding-top:1rem;border-top:1px solid #374151}
.note code{color:#9ca3af;background:#0b1220;padding:1px 5px;border-radius:3px}
</style></head><body><div class="wrap">
<h1>{$class}</h1>
<p class="sub">{$message}</p>
<div class="box"><h2>Request</h2><pre>{$method} {$url}</pre></div>
<div class="box"><h2>Source</h2><pre>{$file}</pre></div>
<div class="box"><h2>Stack trace</h2><pre>{$trace}</pre></div>
<p class="note">Rendered by the local PHP {$php} dev fallback (Symfony var-dumper bypassed). Full Symfony debug page is available on PHP 8.4+. See <code>bootstrap/app.php</code>.</p>
</div></body></html>
HTML;

            return response($html, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
        });
    })->create();