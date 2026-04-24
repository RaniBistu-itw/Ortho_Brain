<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\ImageEditProvider;
use App\Services\AI\DTO\EditedImage;
use App\Services\AI\DTO\PhotoBlob;
use RuntimeException;

// Demo-case-scoped last-resort. Serves a pre-baked PNG from storage/app/ai/demo.
// Keeps real doctors from ever seeing canned output if Gemini is down.
class CannedImageEditProvider implements ImageEditProvider
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

    public function editImage(PhotoBlob $photo, string $prompt): EditedImage
    {
        $file = $this->demoPath.'/smile-preview.png';
        if (! is_file($file)) {
            throw new RuntimeException("Canned smile preview missing: {$file}");
        }
        $bytes = (string) file_get_contents($file);

        return new EditedImage(
            bytes:        $bytes,
            mimeType:     'image/png',
            providerName: $this->name(),
        );
    }
}
