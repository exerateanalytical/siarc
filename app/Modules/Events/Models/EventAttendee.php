<?php

namespace App\Modules\Events\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class EventAttendee extends Model
{
    protected $fillable = ['event_id', 'user_id', 'status', 'registered_at'];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime'];
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
