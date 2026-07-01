<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessDocument extends Model
{
    protected $fillable = [
        'business_id', 'type', 'name_fr', 'name_en', 'file_path',
        'issued_by', 'issued_at', 'expires_at', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public'  => 'boolean',
            'issued_at'  => 'date',
            'expires_at' => 'date',
        ];
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function getUrlAttribute(): string
    {
        return config('filesystems.default') === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->file_path, now()->addHours(24))
            : \Storage::disk('public')->url($this->file_path);
    }
}
