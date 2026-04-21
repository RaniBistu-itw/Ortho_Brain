<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'arches',
        'ipr_enabled',
        'ipr_value',
        'attachments_enabled',
        'attachments_value',
        'attachments_specific_step',
        'elastics_enabled',
        'elastics_value',
        'extractions_enabled',
        'extractions_value',
        'tooth_movement_mode',
        'attachment_restrictions_mode',
        'additional_comments',
        'future_restorative_work_has_work',
        'future_restorative_work_explanation',
    ];

    protected $casts = [
        'ipr_enabled' => 'boolean',
        'attachments_enabled' => 'boolean',
        'attachments_specific_step' => 'integer',
        'elastics_enabled' => 'boolean',
        'extractions_enabled' => 'boolean',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function toothRestrictions()
    {
        return $this->hasMany(PrescriptionToothRestriction::class);
    }

    public function movementRestrictions()
    {
        return $this->toothRestrictions()->where('restriction_type', 'MOVEMENT');
    }

    public function attachmentRestrictions()
    {
        return $this->toothRestrictions()->where('restriction_type', 'ATTACHMENT');
    }
}
