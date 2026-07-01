<?php

namespace App\Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    protected $fillable = ['message_id', 'file_path', 'original_name', 'file_size', 'mime_type'];

    public function message(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute(): string
    {
        return config('filesystems.default') === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->file_path, now()->addMinutes(60))
            : \Storage::disk('public')->url($this->file_path);
    }
}
