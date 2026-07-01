<?php

namespace App\Modules\ApiProduct\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'api_key_id', 'endpoint', 'method', 'status_code',
        'response_time_ms', 'ip_address', 'requested_at',
    ];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime'];
    }

    public function apiKey(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
