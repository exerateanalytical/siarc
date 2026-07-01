<?php

namespace App\Modules\Businesses\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationDocument extends Model
{
    protected $fillable = [
        'verification_application_id', 'document_type', 'file_path', 'original_filename', 'file_size',
    ];

    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VerificationApplication::class, 'verification_application_id');
    }
}
