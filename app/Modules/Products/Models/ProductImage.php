<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'category', 'file_path', 'caption_fr', 'caption_en', 'is_cover', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return config('filesystems.default') === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->file_path, now()->addHours(24))
            : \Storage::disk('public')->url($this->file_path);
    }
}
