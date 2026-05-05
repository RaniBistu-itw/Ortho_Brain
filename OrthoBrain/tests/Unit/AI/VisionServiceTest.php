<?php

use App\Services\AI\Contracts\VisionProvider;
use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\DTO\QcResult;
use App\Services\AI\VisionService;

function fakeProvider(
    string $name,
    bool $available = true,
    ?QcResult $qc = null,
    ?string $smilePlan = null,
    ?\Throwable $throw = null,
): VisionProvider {
    return new class($name, $available, $qc, $smilePlan, $throw) implements VisionProvider {
        public function __construct(
            private string $providerName,
            private bool $available,
            private ?QcResult $qcResult,
            private ?string $smilePlan,
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

        public function classifyPhoto(PhotoBlob $photo): QcResult
        {
            if ($this->throw) {
                throw $this->throw;
            }

            return $this->qcResult ?? new QcResult($photo->tileId, $photo->tileId, 0.9, 'fake');
        }

        public function generateSmilePlan(array $photos): string
        {
            if ($this->throw) {
                throw $this->throw;
            }

            return $this->smilePlan ?? '## fake plan';
        }
    };
}

function photo(string $tile = 'profile'): PhotoBlob
{
    return new PhotoBlob($tile, "\x00\x01", 'image/jpeg');
}

it('returns the first available providers result', function () {
    $service = new VisionService([
        fakeProvider('primary',  available: true,  qc: new QcResult('profile', 'profile', 0.87, 'ok')),
        fakeProvider('fallback', available: true,  qc: new QcResult('profile', 'left-buccal', 0.99, 'never hit')),
    ]);

    $result = $service->classifyPhoto(photo());

    expect($result->predictedTile)->toBe('profile');
    expect($result->confidence)->toBe(0.87);
});

it('skips providers that report unavailable', function () {
    $service = new VisionService([
        fakeProvider('primary',  available: false),
        fakeProvider('fallback', available: true, qc: new QcResult('profile', 'profile', 0.75, 'fallback')),
    ]);

    $result = $service->classifyPhoto(photo());

    expect($result->reason)->toBe('fallback');
});

it('advances to the next provider on exception', function () {
    $service = new VisionService([
        fakeProvider('primary',  available: true, throw: new RuntimeException('gemini 5xx')),
        fakeProvider('fallback', available: true, qc: new QcResult('profile', 'profile', 0.71, 'recovered')),
    ]);

    $result = $service->classifyPhoto(photo());

    expect($result->reason)->toBe('recovered');
});

it('throws when every provider fails', function () {
    $service = new VisionService([
        fakeProvider('primary',  available: true, throw: new RuntimeException('primary down')),
        fakeProvider('fallback', available: true, throw: new RuntimeException('fallback down')),
    ]);

    expect(fn () => $service->classifyPhoto(photo()))
        ->toThrow(RuntimeException::class, 'All vision providers failed');
});

it('walks the chain for smile plan generation too', function () {
    $service = new VisionService([
        fakeProvider('primary',  available: true, throw: new RuntimeException('rate limited')),
        fakeProvider('fallback', available: true, smilePlan: '### recovered narrative'),
    ]);

    $plan = $service->generateSmilePlan([photo('profile'), photo('frontal-smile'), photo('frontal-rest'), photo('frontal-bite')]);

    expect($plan)->toContain('recovered');
});

it('marks mismatches correctly via QcResult::isMismatch', function () {
    $result = new QcResult('frontal-smile', 'left-buccal', 0.82, 'buccal view');
    expect($result->isMismatch())->toBeTrue();
    expect($result->toArray()['mismatch'])->toBeTrue();

    $lowConfidence = new QcResult('frontal-smile', 'left-buccal', 0.3, 'unsure');
    expect($lowConfidence->isMismatch())->toBeFalse();

    $match = new QcResult('frontal-smile', 'frontal-smile', 0.95, 'ok');
    expect($match->isMismatch())->toBeFalse();
});
