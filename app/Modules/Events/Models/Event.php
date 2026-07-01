<?php

namespace App\Modules\Events\Models;

use App\Modules\Businesses\Models\Business;
use App\Modules\Taxonomy\Models\Industry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'uuid', 'slug', 'name_fr', 'name_en',
        'description_fr', 'description_en', 'cover_image',
        'location_fr', 'location_en', 'starts_at', 'ends_at',
        'industry_id', 'created_by', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'    => 'datetime',
            'ends_at'      => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Event $event) {
            if (! $event->uuid) {
                $event->uuid = (string) Str::uuid();
            }
            if (! $event->slug) {
                $event->slug = static::generateSlug($event->name_fr);
            }
        });
    }

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    public function industry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function exhibitors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventExhibitor::class);
    }

    public function attendees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function exhibitingBusinesses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'event_exhibitors')
            ->withPivot(['booth_number', 'status', 'registered_at'])
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('starts_at', '<', now());
    }

    public function getCoverUrlAttribute(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }
        $disk = config('filesystems.default');

        return $disk === 's3'
            ? \Storage::disk('s3')->temporaryUrl($this->cover_image, now()->addHours(24))
            : \Storage::disk($disk)->url($this->cover_image);
    }
}
