<?php

use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\Providers\CannedFallbackProvider;

beforeEach(function () {
    $this->tmp = sys_get_temp_dir().'/ai-canned-'.bin2hex(random_bytes(4));
    mkdir($this->tmp, 0777, true);
});

afterEach(function () {
    if (is_dir($this->tmp)) {
        array_map('unlink', glob($this->tmp.'/*') ?: []);
        rmdir($this->tmp);
    }
    config()->set('ai.demo_case_id', null);
});

it('is only available for the configured demo case id', function () {
    config()->set('ai.demo_case_id', '42');

    $provider = new CannedFallbackProvider($this->tmp);

    expect($provider->isAvailable(['case_id' => 42]))->toBeTrue();
    expect($provider->isAvailable(['case_id' => 99]))->toBeFalse();
    expect($provider->isAvailable([]))->toBeFalse();
});

it('is unavailable when no demo case id is configured', function () {
    config()->set('ai.demo_case_id', null);
    $provider = new CannedFallbackProvider($this->tmp);

    expect($provider->isAvailable(['case_id' => 1]))->toBeFalse();
});

it('returns the canned QC response from disk', function () {
    file_put_contents(
        $this->tmp.'/classify-left-buccal.json',
        json_encode(['predicted_tile' => 'left-buccal', 'confidence' => 0.88, 'reason' => 'canned'])
    );

    $provider = new CannedFallbackProvider($this->tmp);
    $result = $provider->classifyPhoto(new PhotoBlob('left-buccal', "\x00", 'image/jpeg'));

    expect($result->predictedTile)->toBe('left-buccal');
    expect($result->confidence)->toBe(0.88);
    expect($result->reason)->toBe('canned');
});

it('throws when the canned smile plan is missing', function () {
    $provider = new CannedFallbackProvider($this->tmp);

    expect(fn () => $provider->generateSmilePlan([]))
        ->toThrow(RuntimeException::class);
});

it('reads the canned smile plan markdown', function () {
    file_put_contents($this->tmp.'/smile-plan.md', '### Test plan');
    $provider = new CannedFallbackProvider($this->tmp);

    $plan = $provider->generateSmilePlan([]);
    expect($plan)->toContain('Test plan');
});
