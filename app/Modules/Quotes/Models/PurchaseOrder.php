<?php

namespace App\Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expected_delivery_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (self $order) {
            if (! $order->reference) {
                $order->forceFill([
                    'reference' => sprintf('PO-%s-%05d', now()->format('Y'), $order->id),
                ])->saveQuietly();
            }
        });
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(QuoteProposal::class, 'quote_proposal_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
