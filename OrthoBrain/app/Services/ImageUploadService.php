<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Wraps Storage facade. Switch between local & S3 by changing FILESYSTEM_DISK
 * in .env — no code changes needed here or in controllers.
 */
class ImageUploadService
{
    public function store(?UploadedFile $file, string $folder): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $key = trim($folder, '/') . '/' . Str::uuid() . '.' . $extension;

        Storage::disk($this->disk())->put(
            $key,
            file_get_contents($file->getRealPath())
        );

        return $key;
    }

    public function delete(?string $key): void
    {
        if (! $key) return;
        Storage::disk($this->disk())->delete($key);
    }

    public function url(?string $key): ?string
    {
        if (! $key) return null;

        $disk = Storage::disk($this->disk());

        try {
            return $disk->url($key);
        } catch (\Throwable $e) {
            return asset('storage/' . $key);
        }
    }

    private function disk(): string
    {
        // 'public' disk makes uploaded images publicly accessible after
        // `php artisan storage:link` — the right default for local dev.
        return config('filesystems.default', 'public') === 's3' ? 's3' : 'public';
    }
}
