<?php

namespace App\Providers;

use App\Services\AI\Providers\CannedFallbackProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\VisionService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VisionService::class, function ($app) {
            $chain = config('ai.provider_chain', []);
            $providers = [];

            foreach ($chain as $name) {
                $providers[] = $this->makeProvider($name);
            }

            return new VisionService($providers);
        });
    }

    private function makeProvider(string $name)
    {
        $timeoutSeconds = (int) config('ai.timeout_seconds', 15);

        return match ($name) {
            'gemini' => new GeminiProvider(
                apiKey:         config('ai.providers.gemini.api_key'),
                model:          config('ai.providers.gemini.model'),
                baseUrl:        config('ai.providers.gemini.base_url'),
                timeoutSeconds: $timeoutSeconds,
            ),
            'ollama' => new OllamaProvider(
                baseUrl:                 config('ai.providers.ollama.base_url'),
                qcModel:                 config('ai.providers.ollama.qc_model'),
                smilePlanModel:          config('ai.providers.ollama.smile_plan_model'),
                timeoutSeconds:          $timeoutSeconds,
                smilePlanTimeoutSeconds: (int) config('ai.providers.ollama.smile_plan_timeout_seconds', 90),
            ),
            'canned' => new CannedFallbackProvider(
                demoPath: config('ai.providers.canned.path'),
            ),
            default => throw new InvalidArgumentException("Unknown AI provider: {$name}"),
        };
    }
}
