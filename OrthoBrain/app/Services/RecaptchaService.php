<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify a Google reCAPTCHA v3 token.
     *
     * Returns true when:
     *   - keys are not configured (graceful skip on localhost), OR
     *   - Google's verify endpoint reports success AND
     *     the score meets the configured minimum AND
     *     the action matches what the form requested.
     *
     * Returns false on any failure mode (network error, bad token,
     * low score, action mismatch).
     */
    public function verify(?string $token, string $expectedAction): bool
    {
        $secret = (string) config('captcha.secret_key');

        // No key configured → skip verification (warn so it's visible in logs).
        if ($secret === '') {
            Log::warning('reCAPTCHA secret key not configured — skipping captcha verification.');
            return true;
        }

        if (! $token) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('captcha.timeout', 4))
                ->post((string) config('captcha.verify_url'), [
                    'secret'   => $secret,
                    'response' => $token,
                ]);
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verify request failed: ' . $e->getMessage());
            return false;
        }

        if (! $response->ok()) {
            return false;
        }

        $body = $response->json();

        $success = (bool) ($body['success'] ?? false);
        $score   = (float) ($body['score']   ?? 0);
        $action  = (string) ($body['action'] ?? '');

        return $success
            && $action === $expectedAction
            && $score >= (float) config('captcha.min_score', 0.5);
    }
}
