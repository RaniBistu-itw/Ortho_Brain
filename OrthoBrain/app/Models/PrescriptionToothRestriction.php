<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionToothRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'restriction_type',
        'tooth_code',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
