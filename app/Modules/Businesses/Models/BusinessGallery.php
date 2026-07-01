<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessGallery extends Model
{
    protected $table = 'business_gallery';

    protected $fillable = ['business_id', 'image_path', 'caption_fr', 'caption_en', 'sort_order'];

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function getUrlAttribute(): string
    {
        return \Storage::disk('s3')->temporaryUrl($this->image_path, now()->addHours(24));
    }
}
