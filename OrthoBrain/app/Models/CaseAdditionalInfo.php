<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseAdditionalInfo extends Model
{
    protected $table = 'case_additional_info';

    protected $fillable = [
        'case_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
