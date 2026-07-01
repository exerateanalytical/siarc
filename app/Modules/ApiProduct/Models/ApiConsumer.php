<?php

namespace App\Modules\ApiProduct\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApiConsumer extends Model
{
    protected $fillable = [
        'user_id', 'app_name', 'app_description', 'website', 'use_case',
        'status', 'approved_at', 'approved_by',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keys(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApiKey::class);
    }
}
