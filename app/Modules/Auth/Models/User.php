<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes;

    protected $keyType    = 'string';
    public    $incrementing = false;
    protected $guard_name = 'sanctum';

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'avatar', 'language_preference', 'status',
        'is_email_verified', 'is_phone_verified',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'last_login_at'       => 'datetime',
            'is_email_verified'   => 'boolean',
            'is_phone_verified'   => 'boolean',
            'password'            => 'hashed',
        ];
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\Businesses\Models\Business::class, 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasBusiness(): bool
    {
        return $this->business()->exists();
    }
}
