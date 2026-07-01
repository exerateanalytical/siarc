<?php

namespace App\Modules\Admin\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'old_values', 'new_values', 'ip'];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function record(?string $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        static::create([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip'          => request()?->ip(),
            'created_at'  => now(),
        ]);
    }
}
