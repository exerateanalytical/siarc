<?php

namespace App\Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteProposal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'valid_until' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (self $proposal) {
            if (! $proposal->reference) {
                $proposal->forceFill([
                    'reference' => sprintf('QUO-%s-%06d', now()->format('Y'), $proposal->id),
                ])->saveQuietly();
            }
        });
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class, 'quote_request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteProposalItem::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    /** Recompute money columns from the items + fees. */
    public function recalculateTotals(float $taxRate = 0.1925): void
    {
        $subtotal = (int) $this->items()->sum('total');
        $discount = (int) round($subtotal * ($this->global_discount_pct / 100));
        $taxable  = $subtotal - $discount;
        $tax      = (int) round($taxable * $taxRate);

        $this->forceFill([
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'tax_amount'      => $tax,
            'total'           => $taxable + $tax + $this->delivery_fee + $this->insurance_fee,
        ])->save();
    }
}
