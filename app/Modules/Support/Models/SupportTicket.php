<?php

namespace App\Modules\Support\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'category_id', 'subject_fr', 'subject_en', 'status', 'priority',
        'assigned_to', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (SupportTicket $ticket) {
            if (! $ticket->uuid) {
                $ticket->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SupportCategory::class);
    }

    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->oldest();
    }

    public function assignee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
