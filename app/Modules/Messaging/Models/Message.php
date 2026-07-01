<?php

namespace App\Modules\Messaging\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 'sender_id', 'body', 'read_at', 'deleted_by_sender_at', 'deleted_by_receiver_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at'                  => 'datetime',
            'deleted_by_sender_at'     => 'datetime',
            'deleted_by_receiver_at'   => 'datetime',
        ];
    }

    public function conversation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
