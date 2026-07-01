<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessDocument extends Model
{
    protected $fillable = [
        'business_id', 'document_type', 'file_path', 'original_filename',
        'file_size', 'mime_type', 'is_verified', 'verified_at', 'verified_by',
        'expires_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'expires_at'  => 'datetime',
        ];
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
