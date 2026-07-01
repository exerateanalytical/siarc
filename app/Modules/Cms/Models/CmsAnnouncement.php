<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAnnouncement extends Model
{
    protected $fillable = [
        'title_fr', 'title_en', 'body_fr', 'body_en',
        'type', 'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
