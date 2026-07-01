<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSocialLink extends Model
{
    protected $fillable = ['business_id', 'platform', 'url'];

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
