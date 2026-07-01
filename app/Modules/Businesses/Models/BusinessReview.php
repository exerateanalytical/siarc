<?php

namespace App\Modules\Businesses\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class BusinessReview extends Model
{
    protected $fillable = [
        'reviewer_id', 'business_id', 'rating', 'title', 'body', 'is_verified_contact', 'status',
    ];

    protected function casts(): array
    {
        return [
            'is_verified_contact' => 'boolean',
        ];
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
