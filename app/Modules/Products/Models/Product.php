<?php

namespace App\Modules\Products\Models;

use App\Modules\Businesses\Models\Business;
use App\Modules\Taxonomy\Models\ProductCategory;
use App\Modules\Taxonomy\Models\Region;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id', 'category_id', 'origin_region_id',
        'uuid', 'slug',
        'name_fr', 'name_en',
        'description_fr', 'description_en',
        'quantity_available', 'quantity_unit',
        'moq', 'moq_unit',
        'price_type', 'price_amount', 'price_currency', 'price_unit',
        'is_available', 'is_export_ready', 'is_custom_order',
        'is_wholesale', 'is_retail', 'is_organic', 'is_certified',
        'status', 'views_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available'    => 'boolean',
            'is_export_ready' => 'boolean',
            'is_custom_order' => 'boolean',
            'is_wholesale'    => 'boolean',
            'is_retail'       => 'boolean',
            'is_organic'      => 'boolean',
            'is_certified'    => 'boolean',
            'price_amount'    => 'decimal:2',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Product $product) {
            if (! $product->uuid) {
                $product->uuid = (string) Str::uuid();
            }
            if (! $product->slug) {
                $product->slug = static::generateSlug($product->name_fr);
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

    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function originRegion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Region::class, 'origin_region_id');
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort_order')->limit(1);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductDocument::class);
    }

    public function attributes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function videos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductReport::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
