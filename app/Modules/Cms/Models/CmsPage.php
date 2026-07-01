<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'slug', 'title_fr', 'title_en', 'content_fr', 'content_en',
        'meta_title_fr', 'meta_title_en', 'meta_description_fr', 'meta_description_en',
        'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
