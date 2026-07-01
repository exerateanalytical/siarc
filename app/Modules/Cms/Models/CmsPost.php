<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPost extends Model
{
    protected $fillable = [
        'slug', 'type', 'title_fr', 'title_en', 'excerpt_fr', 'excerpt_en',
        'content_fr', 'content_en', 'cover_image', 'author_id',
        'is_published', 'published_at', 'views_count',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
