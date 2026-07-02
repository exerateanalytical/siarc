<?php

namespace App\Modules\ApiProduct\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key_id', 'endpoint', 'method', 'status_code',
        'response_time_ms', 'ip', 'called_at',
    ];

    protected function casts(): array
    {
        return ['called_at' => 'datetime'];
    }

    public function apiKey(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ApiKey::class, 'key_id');
    }
}
