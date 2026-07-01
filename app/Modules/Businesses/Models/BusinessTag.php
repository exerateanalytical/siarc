<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTag extends Model
{
    public $timestamps = false;
    protected $fillable = ['business_id', 'tag'];

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
