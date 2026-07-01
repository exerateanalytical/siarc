<?php

namespace App\Modules\Events\Models;

use App\Modules\Businesses\Models\Business;
use Illuminate\Database\Eloquent\Model;

class EventExhibitor extends Model
{
    protected $fillable = ['event_id', 'business_id', 'booth_number', 'status', 'registered_at'];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime'];
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
