<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\ImageEditProvider;
use App\Services\AI\DTO\EditedImage;
use App\Services\AI\DTO\PhotoBlob;
use Illuminate\Support\Facades\Http;
use RuntimeException;

// Google Gemini 2.5 Flash Image (free-tier image-edit).
// Accepts one input image + text prompt; returns base64 image bytes.
class GeminiImageEditProvider implements ImageEditProvider
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeoutSeconds,
    ) {
    }

    public function name(): string
    {
        return 'gemini';
    }

    public function isAvailable(array $context = []): bool
    {
        return ! empty($this->apiKey);
    }

    public function editImage(PhotoBlob $photo, string $prompt): EditedImage
    {
        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inline_data' => [
                        'mime_type' => $photo->mimeType,
                        'data'      => $photo->base64(),
                    ]],
                ],
            ]],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 2048,
            ],
            'safetySettings' => $this->permissiveSafetySettings(),
        ];

        $url = rtrim($this->baseUrl, '/').'/models/'.$this->model.':generateContent';

        $response = Http::timeout($this->timeoutSeconds)
            ->withQueryParameters(['key' => $this->apiKey])
            ->acceptJson()
            ->post($url, $body);

        if ($response->status() === 429) {
            throw new RuntimeException('Gemini rate-limited (429)');
        }
        if (! $response->successful()) {
            throw new RuntimeException("Gemini returned HTTP {$response->status()}: ".$response->body());
        }

        // Walk every part looking for inline_data — Gemini image-edit may also
        // return a leading text part ("Here is the edited image:") before the
        // image bytes.
        $parts = (array) ($response->json('candidates.0.content.parts') ?? []);
        foreach ($parts as $part) {
            $inline = $part['inlineData'] ?? $part['inline_data'] ?? null;
            if (! is_array($inline)) {
                continue;
            }
            $b64 = (string) ($inline['data'] ?? '');
            if ($b64 === '') {
                continue;
            }
            $bytes = base64_decode($b64, strict: true);
            if ($bytes === false || $bytes === '') {
                continue;
            }
            $mime = (string) ($inline['mimeType'] ?? $inline['mime_type'] ?? 'image/png');

            return new EditedImage(
                bytes:        $bytes,
                mimeType:     $mime,
                providerName: $this->name(),
            );
        }

        // No image in the response — typically a safety-filter soft-refusal where
        // the model returns only text. Treat as a retryable provider failure so
        // the service can walk to the next provider.
        throw new RuntimeException('Gemini image-edit returned no inline image data (possibly safety-blocked).');
    }

    private function permissiveSafetySettings(): array
    {
        $categories = [
            'HARM_CATEGORY_HARASSMENT',
            'HARM_CATEGORY_HATE_SPEECH',
            'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'HARM_CATEGORY_DANGEROUS_CONTENT',
        ];

        return array_map(fn ($c) => ['category' => $c, 'threshold' => 'BLOCK_ONLY_HIGH'], $categories);
    }
}
