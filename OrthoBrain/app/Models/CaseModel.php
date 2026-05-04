<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cases';

    protected $fillable = [
        'doctor_id',
        'practice_id',
        'patient_id',
        'practice_id',
        'scanner_id',
        'impression_method',
        'case_code',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class, 'case_id');
    }

    public function media()
    {
        return $this->hasMany(CaseMedia::class, 'case_id');
    }

    public function additionalInfo()
    {
        return $this->hasOne(CaseAdditionalInfo::class, 'case_id');
    }

    public function shippingAddress()
    {
        return $this->hasOne(CaseShippingAddress::class, 'case_id');
    }

    public function scanner()
    {
        return $this->belongsTo(Scanner::class);
    }

    public function scopeForPractice($query, int $practiceId)
    {
        return $query->where('practice_id', $practiceId);
    }
}
