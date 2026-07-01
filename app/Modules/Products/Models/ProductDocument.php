<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDocument extends Model
{
    protected $fillable = [
        'product_id', 'type', 'name_fr', 'name_en', 'file_path', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        $disk = config('filesystems.default');

        return $disk === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->file_path, now()->addHours(24))
            : \Storage::disk($disk)->url($this->file_path);
    }
}
