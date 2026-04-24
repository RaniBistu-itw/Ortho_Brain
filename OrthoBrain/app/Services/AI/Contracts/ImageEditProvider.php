<?php

namespace App\Services\AI\Contracts;

use App\Services\AI\DTO\EditedImage;
use App\Services\AI\DTO\PhotoBlob;

interface ImageEditProvider
{
    public function name(): string;

    public function isAvailable(array $context = []): bool;

    public function editImage(PhotoBlob $photo, string $prompt): EditedImage;
}
