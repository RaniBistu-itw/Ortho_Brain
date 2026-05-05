<?php

namespace App\Services\AI\DTO;

final class QcResult
{
    public function __construct(
        public readonly string $expectedTile,
        public readonly string $predictedTile,
        public readonly float $confidence,
        public readonly string $reason,
    ) {
    }

    public function isMismatch(float $threshold = 0.6): bool
    {
        return $this->predictedTile !== $this->expectedTile
            && $this->confidence >= $threshold;
    }

    public function toArray(): array
    {
        return [
            'expected_tile'  => $this->expectedTile,
            'predicted_tile' => $this->predictedTile,
            'confidence'     => $this->confidence,
            'reason'         => $this->reason,
            'mismatch'       => $this->isMismatch(),
        ];
    }
}
