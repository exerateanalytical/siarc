<?php

namespace App\Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $invoice) {
            if (! $invoice->reference) {
                $invoice->forceFill([
                    'reference' => sprintf('INV-%s-%05d', now()->format('Y'), $invoice->id),
                ])->saveQuietly();
            }
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
