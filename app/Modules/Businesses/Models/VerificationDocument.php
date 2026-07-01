<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationDocument extends Model
{
    protected $fillable = [
        'application_id', 'type', 'file_path', 'original_name', 'status', 'notes',
    ];

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VerificationApplication::class, 'application_id');
    }

    public function getUrlAttribute(): string
    {
        return config('filesystems.default') === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->file_path, now()->addHours(24))
            : \Storage::disk('public')->url($this->file_path);
    }
}
