<?php

namespace App\Modules\Businesses\Models;

use App\Modules\Taxonomy\Models\Certification;
use Illuminate\Database\Eloquent\Model;

class BusinessCertification extends Model
{
    protected $fillable = [
        'business_id', 'certification_id',
        'issued_at', 'expires_at', 'certificate_file', 'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at'  => 'date',
            'expires_at' => 'date',
        ];
    }

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function certification(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
