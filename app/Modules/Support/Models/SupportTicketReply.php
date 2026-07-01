<?php

namespace App\Modules\Support\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'body_fr', 'body_en', 'is_staff'];

    protected function casts(): array
    {
        return ['is_staff' => 'boolean'];
    }

    public function ticket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
