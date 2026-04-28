<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'practice_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'biological_gender',
        'biological_gender_other',
        'chart_id',
        'email',
        'phone',
        'chief_complaint',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function practice(): BelongsTo
    {
        return $this->belongsTo(Practice::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseModel::class, 'patient_id');
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
