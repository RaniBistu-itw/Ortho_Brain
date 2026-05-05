<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\VisionProvider;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\DTO\QcResult;
use Illuminate\Support\Facades\Http;
use RuntimeException;

// Google Gemini 2.0 Flash, free-tier vision primary. REST-only; no SDK needed.
class GeminiProvider implements VisionProvider
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

    public function classifyPhoto(PhotoBlob $photo): QcResult
    {
        $tiles = implode(', ', config('ai.photo_tiles'));
        $instructions = <<<PROMPT
You classify orthodontic clinical photographs. Respond ONLY with JSON (no prose, no fences):
{"predicted_tile": "<one of: {$tiles}>", "confidence": <0-1>, "reason": "<<=20 words>"}

A doctor uploaded the attached image to the "{$photo->tileId}" slot.
Classify what the image actually shows.
PROMPT;

        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $instructions],
                    ['inline_data' => [
                        'mime_type' => $photo->mimeType,
                        'data'      => $photo->base64(),
                    ]],
                ],
            ]],
            'generationConfig' => [
                'temperature'      => 0.2,
                'response_mime_type' => 'application/json',
                'maxOutputTokens'  => 200,
            ],
            'safetySettings' => $this->permissiveSafetySettings(),
        ];

        $text = $this->call($body);
        $data = json_decode($text, true) ?: $this->extractJson($text);

        $predicted = (string) ($data['predicted_tile'] ?? $photo->tileId);
        if (! in_array($predicted, config('ai.photo_tiles'), true)) {
            $predicted = $photo->tileId;
        }

        return new QcResult(
            expectedTile:  $photo->tileId,
            predictedTile: $predicted,
            confidence:    (float) ($data['confidence'] ?? 0.5),
            reason:        (string) ($data['reason'] ?? ''),
        );
    }

    public function generateSmilePlan(array $photos): string
    {
        $order = array_map(fn (PhotoBlob $p) => $p->tileId, $photos);
        $orderList = implode(', ', $order);

        $instructions = <<<PROMPT
You are an orthodontic clinician assistant. You will receive {$this->photoCount($photos)} clinical photographs attached in this exact order: {$orderList}.

Produce a concise pre-treatment clinical narrative in Markdown with these sections:

### Facial Analysis
- Profile type, lip competence, facial balance (from profile + frontal views)

### Dental Classification
- Molar/canine class left & right (from buccal + retracted views)

### Arch Analysis
- Crowding/spacing upper & lower (from occlusals)

### Bite Analysis
- Overjet, overbite, crossbites, midline deviation (from frontal bite + retracted)

### Recommended Approach
- 2-3 sentence treatment suggestion, inviting the doctor to confirm

Close with: "*This is an AI-generated draft. Review and edit before submission.*"
PROMPT;

        $parts = [['text' => $instructions]];
        foreach ($photos as $p) {
            $parts[] = ['inline_data' => [
                'mime_type' => $p->mimeType,
                'data'      => $p->base64(),
            ]];
        }

        $body = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 1200,
            ],
            'safetySettings' => $this->permissiveSafetySettings(),
        ];

        return trim($this->call($body));
    }

    private function call(array $body): string
    {
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

        $text = (string) ($response->json('candidates.0.content.parts.0.text') ?? '');
        if ($text === '') {
            throw new RuntimeException('Gemini returned empty text (possibly blocked by safety filters).');
        }

        return $text;
    }

    private function permissiveSafetySettings(): array
    {
        // Clinical intra-oral photography can trip default "medical" filters.
        // BLOCK_ONLY_HIGH is the most permissive category; Gemini does not
        // allow fully disabling safety in the free tier.
        $categories = [
            'HARM_CATEGORY_HARASSMENT',
            'HARM_CATEGORY_HATE_SPEECH',
            'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'HARM_CATEGORY_DANGEROUS_CONTENT',
        ];

        return array_map(fn ($c) => ['category' => $c, 'threshold' => 'BLOCK_ONLY_HIGH'], $categories);
    }

    private function photoCount(array $photos): int
    {
        return count($photos);
    }

    private function extractJson(string $text): array
    {
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        throw new RuntimeException('Gemini QC response was not valid JSON: '.$text);
    }
}
