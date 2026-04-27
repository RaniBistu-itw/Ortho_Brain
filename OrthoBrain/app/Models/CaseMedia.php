<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CaseMedia extends Model
{
    use HasFactory;

    protected $table = 'case_media';

    protected $fillable = [
        'case_id',
        'section',
        'tile_id',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'original_name',
        'crop_params',
    ];

    protected $casts = [
        'crop_params' => 'array',
        'size_bytes'  => 'integer',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function publicUrl(): ?string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
