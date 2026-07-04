<?php

namespace App\Modules\Quotes\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'desired_response_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (self $request) {
            if (! $request->reference) {
                $request->forceFill([
                    'reference' => sprintf('RFQ-%s-%06d', now()->format('Y'), $request->id),
                ])->saveQuietly();
            }
        });
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(QuoteProposal::class);
    }
}
