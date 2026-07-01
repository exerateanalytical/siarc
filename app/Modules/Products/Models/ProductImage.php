<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'alt_fr', 'alt_en', 'sort_order'];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        $disk = config('filesystems.default');

        return $disk === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->image_path, now()->addHours(24))
            : \Storage::disk($disk)->url($this->image_path);
    }
}
