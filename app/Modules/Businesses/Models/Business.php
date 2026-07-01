<?php

namespace App\Modules\Businesses\Models;

use App\Modules\Taxonomy\Models\City;
use App\Modules\Taxonomy\Models\Industry;
use App\Modules\Taxonomy\Models\Region;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'slug', 'user_id', 'industry_id', 'region_id', 'city_id',
        'name_fr', 'name_en', 'tagline_fr', 'tagline_en',
        'description_fr', 'description_en',
        'logo', 'cover_image',
        'phone', 'whatsapp', 'email', 'website',
        'address_fr', 'address_en', 'gps_lat', 'gps_lng',
        'year_established', 'employee_count', 'ownership_type',
        'export_countries', 'languages_spoken',
        'is_featured', 'featured_until',
        'verification_tier', 'status',
        'views_count', 'response_time_hours',
    ];

    protected function casts(): array
    {
        return [
            'export_countries'  => 'array',
            'languages_spoken'  => 'array',
            'is_featured'       => 'boolean',
            'featured_until'    => 'datetime',
            'gps_lat'           => 'float',
            'gps_lng'           => 'float',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Business $business) {
            if (! $business->uuid) {
                $business->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (! $business->slug) {
                $business->slug = static::generateSlug($business->name_fr);
            }
        });
    }

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    // Relations
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Auth\Models\User::class, 'user_id');
    }

    public function industry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function gallery(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessGallery::class)->orderBy('sort_order');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessDocument::class);
    }

    public function socialLinks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessSocialLink::class);
    }

    public function certifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessCertification::class)->with('certification');
    }

    public function awards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessAward::class)->orderByDesc('year');
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessTag::class);
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Products\Models\Product::class);
    }

    public function verificationApplications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VerificationApplication::class)->latest();
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessReview::class)->published()->latest();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                     ->where(fn ($q) => $q->whereNull('featured_until')->orWhere('featured_until', '>', now()));
    }

    // Helpers
    public function isOwner(\App\Modules\Auth\Models\User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? $this->fileUrl($this->logo) : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? $this->fileUrl($this->cover_image) : null;
    }

    private function fileUrl(string $path): string
    {
        return config('filesystems.default') === 's3'
            ? \Storage::disk('s3')->temporaryUrl($path, now()->addHours(24))
            : \Storage::disk('public')->url($path);
    }

    public function averageRating(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }

    public function repeatCustomersCount(): int
    {
        return \App\Modules\Messaging\Models\Conversation::where('business_id', $this->id)
            ->select('buyer_id')
            ->groupBy('buyer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
    }

    public function dealsReportedCount(): int
    {
        return \App\Modules\Messaging\Models\Conversation::where('business_id', $this->id)
            ->whereNotNull('deal_marked_at')
            ->count();
    }
}
