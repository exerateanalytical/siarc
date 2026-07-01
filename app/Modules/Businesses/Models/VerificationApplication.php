<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationApplication extends Model
{
    protected $fillable = [
        'business_id', 'requested_tier', 'current_tier',
        'status', 'admin_notes', 'reviewed_by', 'reviewed_at', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at'  => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'reviewed_by');
    }
}
