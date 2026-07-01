<?php

namespace App\Modules\ApiProduct\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'consumer_id', 'key_prefix', 'key_hash', 'name',
        'rate_limit_per_minute', 'is_active', 'last_used_at', 'expires_at',
    ];

    protected $hidden = ['key_hash'];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'last_used_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function consumer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApiConsumer::class);
    }

    public function usageLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApiUsageLog::class);
    }
}
