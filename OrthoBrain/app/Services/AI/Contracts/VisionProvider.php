<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\DTO\PhotoBlob;
use App\Services\AI\DTO\QcResult;

interface VisionProvider
{
    public function name(): string;

    public function isAvailable(array $context = []): bool;

    public function classifyPhoto(PhotoBlob $photo): QcResult;

    /**
     * @param  PhotoBlob[]  $photos  tile-ordered
     */
    public function generateSmilePlan(array $photos): string;
}
