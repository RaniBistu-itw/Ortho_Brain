<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\VisionProvider;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\DTO\QcResult;
use Illuminate\Support\Facades\Log;
use RuntimeException;

// Walks a provider chain top-down. First provider whose isAvailable() returns
// true gets one attempt; on exception we log + advance. If every provider
// fails we throw AiUnavailableException so the controller returns 503.
class VisionService
{
    /** @param  VisionProvider[]  $providers */
    public function __construct(private readonly array $providers)
    {
    }

    public function classifyPhoto(PhotoBlob $photo, array $context = []): QcResult
    {
        return $this->dispatch(
            operation: 'classify',
            context:   $context + ['tile' => $photo->tileId],
            run:       fn (VisionProvider $p) => $p->classifyPhoto($photo),
        );
    }

    public function generateSmilePlan(array $photos, array $context = []): string
    {
        return $this->dispatch(
            operation: 'smile_plan',
            context:   $context + ['photo_count' => count($photos)],
            run:       fn (VisionProvider $p) => $p->generateSmilePlan($photos),
        );
    }

    private function dispatch(string $operation, array $context, callable $run): mixed
    {
        $lastError = null;

        foreach ($this->providers as $provider) {
            if (! $provider->isAvailable($context)) {
                Log::channel('ai')->debug("{$operation} provider={$provider->name()} outcome=skipped_unavailable", $context);

                continue;
            }

            $started = microtime(true);
            try {
                $result = $run($provider);
                $latency = (int) round((microtime(true) - $started) * 1000);
                Log::channel('ai')->info("{$operation} provider={$provider->name()} latency_ms={$latency} outcome=success", $context);

                return $result;
            } catch (\Throwable $e) {
                $latency = (int) round((microtime(true) - $started) * 1000);
                $msg = str_replace(["\n", "\r"], ' ', $e->getMessage());
                Log::channel('ai')->warning("{$operation} provider={$provider->name()} latency_ms={$latency} outcome=error error=\"{$msg}\"", $context);
                $lastError = $e;
            }
        }

        throw new RuntimeException(
            'All vision providers failed'.($lastError ? ': '.$lastError->getMessage() : ''),
            previous: $lastError instanceof \Throwable ? $lastError : null,
        );
    }
}
