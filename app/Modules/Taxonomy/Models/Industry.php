<?php

namespace App\Modules\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = ['slug', 'name_fr', 'name_en', 'icon', 'description_fr', 'description_en', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function sectors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sector::class)->orderBy('sort_order');
    }

    public function attributeTemplates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttributeTemplate::class);
    }

    public function certifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function businesses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Businesses\Models\Business::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
