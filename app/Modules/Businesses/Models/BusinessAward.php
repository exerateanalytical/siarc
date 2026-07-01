<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessAward extends Model
{
    protected $fillable = [
        'business_id', 'title_fr', 'title_en', 'issuer_fr', 'issuer_en', 'year', 'description_fr', 'description_en',
    ];

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
