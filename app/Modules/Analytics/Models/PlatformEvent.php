<?php

namespace App\Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_type', 'user_id', 'entity_type', 'entity_id',
        'meta', 'ip_address', 'user_agent', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array', 'occurred_at' => 'datetime'];
    }
}
