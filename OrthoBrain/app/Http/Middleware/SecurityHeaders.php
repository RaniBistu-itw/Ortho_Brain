<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Append a baseline of defensive HTTP headers to every web response.
     *
     * - X-Frame-Options:        no third-party iframe embedding (clickjacking)
     * - X-Content-Type-Options: browsers must trust our Content-Type (no sniff)
     * - Referrer-Policy:        don't leak full URLs to off-site links
     * - Permissions-Policy:     deny browser APIs we don't use
     * - Content-Security-Policy: limit script/img/connect origins; allow
     *   reCAPTCHA + Vite dev assets so the app keeps working.
     *
     * CSP is intentionally permissive ('unsafe-inline' for scripts/styles)
     * because the existing Blade templates use inline event handlers and
     * inline <style> blocks. Tighten once those are externalised.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;

        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'same-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google.com https://www.gstatic.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://www.google.com",
            "frame-src https://www.google.com",
            "worker-src 'self' blob:",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);
        $headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
