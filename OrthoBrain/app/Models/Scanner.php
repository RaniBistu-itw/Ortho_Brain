<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scanner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'portal_password', 'portal_link', 'status',
    ];

    protected $hidden = [
        'portal_password',
    ];
}
