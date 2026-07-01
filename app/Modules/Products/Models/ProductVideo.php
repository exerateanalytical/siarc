<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVideo extends Model
{
    protected $fillable = ['product_id', 'category', 'url', 'type', 'caption_fr', 'caption_en', 'sort_order'];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Convert a normal YouTube/Vimeo watch URL into an embeddable iframe URL.
     * Uploaded files are returned as-is (rendered via <video>, not <iframe>).
     */
    public function getEmbedUrlAttribute(): string
    {
        if ($this->type === 'youtube') {
            if (preg_match('/(?:youtu\.be\/|v=|embed\/)([A-Za-z0-9_-]{11})/', $this->url, $m)) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }
        }

        if ($this->type === 'vimeo') {
            if (preg_match('/vimeo\.com\/(\d+)/', $this->url, $m)) {
                return 'https://player.vimeo.com/video/' . $m[1];
            }
        }

        return $this->url;
    }
}
