<?php

namespace App\Modules\Support\Models;

use Illuminate\Database\Eloquent\Model;

class HelpCategory extends Model
{
    protected $fillable = ['slug', 'name_fr', 'name_en', 'icon', 'sort_order'];

    public function articles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HelpArticle::class, 'category_id')->published()->orderBy('sort_order');
    }
}
