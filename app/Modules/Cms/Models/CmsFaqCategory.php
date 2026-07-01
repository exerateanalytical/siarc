<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CmsFaqCategory extends Model
{
    protected $fillable = ['name_fr', 'name_en', 'sort_order'];

    public function faqs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CmsFaq::class, 'category_id')->where('is_published', true)->orderBy('sort_order');
    }
}
