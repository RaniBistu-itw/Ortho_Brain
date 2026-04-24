<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\VisionProvider;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\DTO\QcResult;
use RuntimeException;

// Last-resort provider. Reads static JSON/markdown from storage/app/ai/demo/
// and returns it verbatim. Intentionally scoped to a single demo case id so
// real doctors never see canned text.
class CannedFallbackProvider implements VisionProvider
{
    public function __construct(private readonly string $demoPath)
    {
    }

    public function name(): string
    {
        return 'canned';
    }

    public function isAvailable(array $context = []): bool
    {
        $demoCaseId = config('ai.demo_case_id');
        if (empty($demoCaseId)) {
            return false;
        }
        $contextCaseId = $context['case_id'] ?? null;
        if ($contextCaseId === null) {
            return false;
        }

        return (string) $contextCaseId === (string) $demoCaseId;
    }

    public function classifyPhoto(PhotoBlob $photo): QcResult
    {
        $file = $this->demoPath.'/classify-'.$photo->tileId.'.json';
        if (! is_file($file)) {
            throw new RuntimeException("Canned QC response missing: {$file}");
        }
        $raw = file_get_contents($file);
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            throw new RuntimeException("Canned QC response is not valid JSON: {$file}");
        }

        return new QcResult(
            expectedTile:  $photo->tileId,
            predictedTile: (string) ($data['predicted_tile'] ?? $photo->tileId),
            confidence:    (float) ($data['confidence'] ?? 1.0),
            reason:        (string) ($data['reason'] ?? ''),
        );
    }

    public function generateSmilePlan(array $photos): string
    {
        $file = $this->demoPath.'/smile-plan.md';
        if (! is_file($file)) {
            throw new RuntimeException("Canned smile plan missing: {$file}");
        }

        return (string) file_get_contents($file);
    }
}
