<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'profile_photo_s3_key',
        'practice_name',
        'practice_phone_country_code',
        'practice_phone_number',
        'practice_website',
        'preferred_language',
        'currently_providing_ortho_services',
        'preferred_contact_mode',
        'doctor_contact_email',
        'doctor_cell_phone',
        'other_email',
        'preferred_tooth_numbering_system',
        'smile_arc_pref',
        'small_lateral_incisors_pref',
        'mixed_dentition_pref',
        'orthodontic_extractions_pref',
        'ipr_protocol_pref',
        'ipr_protocol_other_note',
        'elastics_bonded_buttons_pref',
        'extractions_if_suggested_pref',
        'attachment_stage_pref',
        'approval_status',
        'approved_at',
        'approved_by_admin_id',
        'rejection_reason',
    ];

    protected $casts = [
        'currently_providing_ortho_services' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approverAdmin()
    {
        return $this->belongsTo(Admin::class, 'approved_by_admin_id');
    }
}
