<?php

namespace App\Modules\Notifications\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id', 'type', 'email_enabled', 'push_enabled', 'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled'  => 'boolean',
            'push_enabled'   => 'boolean',
            'in_app_enabled' => 'boolean',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
