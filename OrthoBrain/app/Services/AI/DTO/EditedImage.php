<?php

namespace App\Services\AI\DTO;

final class EditedImage
{
    public function __construct(
        public readonly string $bytes,
        public readonly string $mimeType,
        public readonly string $providerName,
    ) {
    }

    public function toDataUrl(): string
    {
        return 'data:'.$this->mimeType.';base64,'.base64_encode($this->bytes);
    }
}
