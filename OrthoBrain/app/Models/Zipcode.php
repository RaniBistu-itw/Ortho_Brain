<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zipcode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'city_id', 'status', 'details'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
