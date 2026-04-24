<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\VisionProvider;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\DTO\QcResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

// Local Ollama offline fallback. Uses moondream for per-upload QC (fast on CPU)
// and llava:7b for the one-shot 9-image smile plan.
class OllamaProvider implements VisionProvider
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $qcModel,
        private readonly string $smilePlanModel,
        private readonly int $timeoutSeconds,
        private readonly int $smilePlanTimeoutSeconds,
    ) {
    }

    public function name(): string
    {
        return 'ollama';
    }

    public function isAvailable(array $context = []): bool
    {
        return Cache::remember('ai.ollama.available', 30, function () {
            try {
                $res = Http::timeout(2)->get($this->baseUrl.'/api/tags');

                return $res->successful();
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public function classifyPhoto(PhotoBlob $photo): QcResult
    {
        $tiles = implode(', ', config('ai.photo_tiles'));
        $prompt = <<<PROMPT
You classify orthodontic clinical photographs.

Respond ONLY with JSON in this exact shape — no prose, no markdown fences:
{"predicted_tile": "<one of: {$tiles}>", "confidence": <0-1>, "reason": "<<=20 words>"}

A doctor uploaded the attached image to the "{$photo->tileId}" slot.
Classify what the image actually shows.
PROMPT;

        $response = Http::timeout($this->timeoutSeconds)
            ->post($this->baseUrl.'/api/generate', [
                'model'  => $this->qcModel,
                'prompt' => $prompt,
                'images' => [$photo->base64()],
                'stream' => false,
                'format' => 'json',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("Ollama returned HTTP {$response->status()}");
        }

        $text = (string) ($response->json('response') ?? '');
        $data = $this->extractJson($text);

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
        $prompt = $this->smilePlanPrompt($photos);

        $response = Http::timeout($this->smilePlanTimeoutSeconds)
            ->post($this->baseUrl.'/api/generate', [
                'model'  => $this->smilePlanModel,
                'prompt' => $prompt,
                'images' => array_map(fn (PhotoBlob $p) => $p->base64(), $photos),
                'stream' => false,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("Ollama returned HTTP {$response->status()}");
        }

        $text = trim((string) ($response->json('response') ?? ''));
        if ($text === '') {
            throw new RuntimeException('Ollama returned empty smile plan.');
        }

        return $text;
    }

    private function smilePlanPrompt(array $photos): string
    {
        $order = array_map(fn (PhotoBlob $p) => $p->tileId, $photos);
        $orderList = implode(', ', $order);

        return <<<PROMPT
You are an orthodontic clinician assistant. Given {$this->photoCount($photos)} clinical photos (in order: {$orderList}), produce a concise pre-treatment clinical narrative in Markdown with these sections:

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
    }

    private function photoCount(array $photos): int
    {
        return count($photos);
    }

    private function extractJson(string $text): array
    {
        $trimmed = trim($text);

        // Strip markdown fences if the model returns them despite format=json.
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed) ?? $trimmed;

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Last-ditch: scan for the first { ... } block.
        if (preg_match('/\{.*\}/s', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new RuntimeException('Ollama QC response was not valid JSON: '.$trimmed);
    }
}
