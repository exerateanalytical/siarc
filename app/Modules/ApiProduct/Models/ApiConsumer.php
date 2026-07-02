<?php

namespace App\Modules\ApiProduct\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiConsumer extends Model
{
    protected $fillable = [
        'uuid', 'name', 'email', 'company', 'country', 'purpose',
        'website', 'status', 'approved_at',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (ApiConsumer $consumer) {
            if (! $consumer->uuid) {
                $consumer->uuid = (string) Str::uuid();
            }
        });
    }

    // Consumers are linked to platform users by email — the table has no user_id column.
    public static function forEmail(string $email): ?self
    {
        return static::where('email', strtolower($email))->first();
    }

    public function keys(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApiKey::class, 'consumer_id');
    }
}
