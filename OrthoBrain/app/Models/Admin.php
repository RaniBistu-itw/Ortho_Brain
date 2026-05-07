<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'profile_photo_s3_key',
        'phone_country_code',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function avatarUrl(): ?string
    {
        return $this->profile_photo_s3_key ? '/storage/' . ltrim($this->profile_photo_s3_key, '/') : null;
    }
}
