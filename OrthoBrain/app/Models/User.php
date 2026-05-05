<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'email', 'password_hash', 'role', 'is_active', 'last_login_at',
    'password_reset_token', 'password_reset_expires_at',
    'email_verified_at', 'verification_otp', 'verification_otp_expires_at',
    'failed_login_attempts', 'locked_until',
])]
#[Hidden(['password_hash', 'password_reset_token', 'verification_otp'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'verification_otp_expires_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }
}
