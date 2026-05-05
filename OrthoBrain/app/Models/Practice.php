<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'website',
        'phone_country_code',
        'phone_number',
        'logo_path',
        'street_address_1',
        'street_address_2',
        'zip_id',
        'city_id',
        'state_id',
        'country_id',
        'status',
    ];

    public function logoUrl(): ?string
    {
        // Root-relative so it works regardless of APP_URL (localhost vs 127.0.0.1 etc.)
        return $this->logo_path ? '/storage/' . ltrim($this->logo_path, '/') : null;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Doctor::class, 'practice_id');
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_practice')
            ->withPivot([
                'id',
                'approval_status',
                'is_primary',
                'requested_at',
                'approved_at',
                'approved_by_admin_id',
                'rejected_at',
                'rejection_reason',
                'left_at',
            ])
            ->withTimestamps();
    }

    public function activeDoctors()
    {
        return $this->doctors()->wherePivot('approval_status', 'APPROVED');
    }

    public function pendingDoctors()
    {
        return $this->doctors()->wherePivot('approval_status', 'PENDING');
    }

    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class, 'zip_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
