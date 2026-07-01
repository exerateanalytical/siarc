<?php

namespace App\Modules\Messaging\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'uuid', 'buyer_id', 'business_id', 'product_id',
        'subject', 'status', 'last_message_at', 'deal_marked_at',
        'buyer_archived_at', 'business_archived_at',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Conversation $conversation) {
            if (! $conversation->uuid) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'last_message_at'       => 'datetime',
            'deal_marked_at'        => 'datetime',
            'buyer_archived_at'     => 'datetime',
            'business_archived_at'  => 'datetime',
        ];
    }

    public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class)->oldest();
    }

    public function latestMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()->where('sender_id', '!=', $user->id)->whereNull('read_at')->count();
    }
}
