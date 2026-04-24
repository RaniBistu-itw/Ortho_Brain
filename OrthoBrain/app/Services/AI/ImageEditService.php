<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\ImageEditProvider;
use App\Services\AI\DTO\EditedImage;
use App\Services\AI\DTO\PhotoBlob;
use Illuminate\Support\Facades\Log;
use RuntimeException;

// Walks a chain of ImageEditProviders top-down. Mirrors VisionService's
// dispatch() pattern — first available provider gets one attempt; exceptions
// advance to the next. If everything fails, throws so the controller returns 503.
class ImageEditService
{
    /** @param  ImageEditProvider[]  $providers */
    public function __construct(private readonly array $providers)
    {
    }

    public function editImage(PhotoBlob $photo, string $prompt, array $context = []): EditedImage
    {
        $lastError = null;

        foreach ($this->providers as $provider) {
            if (! $provider->isAvailable($context)) {
                Log::channel('ai')->debug("smile_preview provider={$provider->name()} outcome=skipped_unavailable", $context);

                continue;
            }

            $started = microtime(true);
            try {
                $result = $provider->editImage($photo, $prompt);
                $latency = (int) round((microtime(true) - $started) * 1000);
                Log::channel('ai')->info("smile_preview provider={$provider->name()} latency_ms={$latency} outcome=success", $context);

                return $result;
            } catch (\Throwable $e) {
                $latency = (int) round((microtime(true) - $started) * 1000);
                $msg = str_replace(["\n", "\r"], ' ', $e->getMessage());
                Log::channel('ai')->warning("smile_preview provider={$provider->name()} latency_ms={$latency} outcome=error error=\"{$msg}\"", $context);
                $lastError = $e;
            }
        }

        throw new RuntimeException(
            'All image-edit providers failed'.($lastError ? ': '.$lastError->getMessage() : ''),
            previous: $lastError instanceof \Throwable ? $lastError : null,
        );
    }
}
