<?php

namespace App\Modules\Notifications\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'type', 'channel',
        'title_fr', 'title_en', 'body_fr', 'body_en',
        'data', 'read_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
