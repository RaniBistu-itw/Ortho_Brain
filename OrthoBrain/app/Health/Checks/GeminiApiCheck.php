<?php

namespace App\Health\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class GeminiApiCheck extends Check
{
    public function run(): Result
    {
        $apiKey = config('ai.providers.gemini.api_key');

        if (empty($apiKey)) {
            return Result::make()
                ->warning('GEMINI_API_KEY is not set');
        }

        $baseUrl = rtrim((string) config('ai.providers.gemini.base_url'), '/');
        $model   = config('ai.providers.gemini.model', 'gemini-2.0-flash');
        $url     = "{$baseUrl}/models/{$model}?key={$apiKey}";

        try {
            $ctx = stream_context_create([
                'http' => [
                    'method'          => 'GET',
                    'timeout'         => 5,
                    'ignore_errors'   => true,
                ],
                'ssl' => [
                    'verify_peer'      => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $body = @file_get_contents($url, false, $ctx);
            $code = 0;
            if (!empty($http_response_header)) {
                preg_match('/HTTP\/\S+ (\d+)/', $http_response_header[0], $m);
                $code = (int) ($m[1] ?? 0);
            }

            if ($code === 200) {
                return Result::make()->ok("Gemini API reachable ({$model})");
            }

            if ($code === 401 || $code === 403) {
                return Result::make()->failed("Gemini API key invalid (HTTP {$code})");
            }

            if ($code === 429) {
                return Result::make()->warning("Gemini API rate-limited (HTTP 429)");
            }

            if ($code >= 500) {
                return Result::make()->warning("Gemini API server error (HTTP {$code})");
            }

            return Result::make()->failed("Gemini API returned HTTP {$code}");
        } catch (\Throwable $e) {
            return Result::make()->failed("Gemini API unreachable: {$e->getMessage()}");
        }
    }
}
