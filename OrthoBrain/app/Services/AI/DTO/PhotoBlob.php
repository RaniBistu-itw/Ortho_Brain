<?php

namespace App\Services\AI\DTO;

final class PhotoBlob
{
    public function __construct(
        public readonly string $tileId,
        public readonly string $bytes,
        public readonly string $mimeType,
    ) {
    }

    public function base64(): string
    {
        return base64_encode($this->bytes);
    }
}
