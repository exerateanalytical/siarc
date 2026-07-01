<?php

namespace App\Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'name_fr', 'name_en', 'logo', 'website', 'tier',
        'description_fr', 'description_en', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }
        $disk = config('filesystems.default');

        return $disk === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->logo, now()->addHours(24))
            : \Storage::disk($disk)->url($this->logo);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
