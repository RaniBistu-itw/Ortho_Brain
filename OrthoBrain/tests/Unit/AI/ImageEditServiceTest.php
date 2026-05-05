<?php

use App\Services\AI\Contracts\ImageEditProvider;
use App\Services\AI\DTO\EditedImage;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\ImageEditService;

function fakeEditProvider(
    string $name,
    bool $available = true,
    ?EditedImage $result = null,
    ?\Throwable $throw = null,
): ImageEditProvider {
    return new class($name, $available, $result, $throw) implements ImageEditProvider {
        public function __construct(
            private string $providerName,
            private bool $available,
            private ?EditedImage $result,
            private ?\Throwable $throw,
        ) {
        }

        public function name(): string
        {
            return $this->providerName;
        }

        public function isAvailable(array $context = []): bool
        {
            return $this->available;
        }

        public function editImage(PhotoBlob $photo, string $prompt): EditedImage
        {
            if ($this->throw) {
                throw $this->throw;
            }

            return $this->result ?? new EditedImage("\x89PNG\x00fake", 'image/png', $this->providerName);
        }
    };
}

function editPhoto(): PhotoBlob
{
    return new PhotoBlob('frontal-smile', "\x00\x01", 'image/jpeg');
}

it('returns the first available edit providers result', function () {
    $service = new ImageEditService([
        fakeEditProvider('primary',  available: true, result: new EditedImage('hit', 'image/png', 'primary')),
        fakeEditProvider('fallback', available: true, result: new EditedImage('miss', 'image/png', 'fallback')),
    ]);

    $result = $service->editImage(editPhoto(), 'prompt');

    expect($result->providerName)->toBe('primary');
    expect($result->bytes)->toBe('hit');
});

it('skips unavailable edit providers', function () {
    $service = new ImageEditService([
        fakeEditProvider('primary',  available: false),
        fakeEditProvider('fallback', available: true, result: new EditedImage('ok', 'image/png', 'fallback')),
    ]);

    $result = $service->editImage(editPhoto(), 'prompt');

    expect($result->providerName)->toBe('fallback');
});

it('advances when an edit provider throws', function () {
    $service = new ImageEditService([
        fakeEditProvider('primary',  available: true, throw: new RuntimeException('gemini 429')),
        fakeEditProvider('fallback', available: true, result: new EditedImage('recovered', 'image/png', 'fallback')),
    ]);

    $result = $service->editImage(editPhoto(), 'prompt');

    expect($result->bytes)->toBe('recovered');
});

it('raises when every edit provider fails', function () {
    $service = new ImageEditService([
        fakeEditProvider('primary',  available: true, throw: new RuntimeException('down')),
        fakeEditProvider('fallback', available: true, throw: new RuntimeException('also down')),
    ]);

    expect(fn () => $service->editImage(editPhoto(), 'prompt'))
        ->toThrow(RuntimeException::class, 'All image-edit providers failed');
});

it('EditedImage::toDataUrl produces a valid data URL', function () {
    $img = new EditedImage('hello', 'image/png', 'gemini');
    $url = $img->toDataUrl();
    expect($url)->toStartWith('data:image/png;base64,');
    expect(base64_decode(substr($url, strlen('data:image/png;base64,'))))->toBe('hello');
});
