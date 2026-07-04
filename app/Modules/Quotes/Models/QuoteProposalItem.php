<?php

namespace App\Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteProposalItem extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->total = (int) round($item->quantity * $item->unit_price * (1 - $item->discount_pct / 100));
        });
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(QuoteProposal::class, 'quote_proposal_id');
    }
}
