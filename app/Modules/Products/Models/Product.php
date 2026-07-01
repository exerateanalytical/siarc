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
        'uuid', 'slug', 'sku', 'product_type', 'brand', 'scientific_name', 'species', 'local_names',
        'name_fr', 'name_en',
        'description_fr', 'description_en',
        'gps_lat', 'gps_lng', 'batch_number', 'pond_number', 'stocking_date',
        'feed_history', 'treatments_administered', 'packaging_date', 'delivery_route',
        'quantity_available', 'quantity_unit', 'quantity_updated_at',
        'moq', 'moq_unit', 'max_order',
        'price_type', 'price_amount', 'price_currency', 'price_unit',
        'grade', 'quality_notes', 'inspection_status', 'quality_score',
        'veterinary_inspection_at', 'mortality_rate',
        'harvest_method', 'next_harvest_at',
        'daily_production', 'monthly_production', 'annual_production', 'production_unit',
        'packaging_type', 'package_sizes', 'is_custom_packaging', 'is_ice_packed', 'is_vacuum_packed', 'is_live_transport', 'is_bulk_packaging',
        'shelf_life_days', 'storage_conditions',
        'delivery_radius_km', 'lead_time_days', 'pickup_available', 'delivery_available', 'is_cold_chain',
        'shipping_company', 'warehouse_location', 'ready_for_shipment', 'container_loading', 'shipping_methods',
        'payment_terms', 'accepted_currencies', 'payment_methods', 'deposit_required', 'trade_finance_support',
        'water_usage', 'energy_source', 'carbon_footprint', 'waste_management', 'environmental_certifications',
        'is_available', 'is_export_ready', 'is_custom_order',
        'is_wholesale', 'is_retail', 'is_organic', 'is_certified',
        'status', 'views_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available'      => 'boolean',
            'is_export_ready'   => 'boolean',
            'is_custom_order'   => 'boolean',
            'is_wholesale'      => 'boolean',
            'is_retail'         => 'boolean',
            'is_organic'        => 'boolean',
            'is_certified'      => 'boolean',
            'is_custom_packaging' => 'boolean',
            'is_ice_packed'     => 'boolean',
            'is_vacuum_packed'  => 'boolean',
            'is_live_transport' => 'boolean',
            'is_bulk_packaging' => 'boolean',
            'pickup_available'  => 'boolean',
            'delivery_available' => 'boolean',
            'is_cold_chain'     => 'boolean',
            'ready_for_shipment' => 'boolean',
            'container_loading' => 'boolean',
            'deposit_required'  => 'boolean',
            'trade_finance_support' => 'boolean',
            'price_amount'      => 'decimal:2',
            'mortality_rate'    => 'decimal:2',
            'gps_lat'           => 'decimal:7',
            'gps_lng'           => 'decimal:7',
            'quantity_updated_at' => 'datetime',
            'next_harvest_at'   => 'datetime',
            'veterinary_inspection_at' => 'date',
            'stocking_date'     => 'date',
            'packaging_date'    => 'date',
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
            if ($product->quantity_available !== null && ! $product->quantity_updated_at) {
                $product->quantity_updated_at = now();
            }
        });
        static::updating(function (Product $product) {
            if ($product->isDirty('quantity_available')) {
                $product->quantity_updated_at = now();
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

    public function harvestDates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductHarvestDate::class)->orderBy('harvest_date');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Composite quality score (0-100), computed from signals actually available on a
     * no-trading, no-price platform: seller rating, certification, and inspection status.
     * Not AI-generated — a plain weighted formula.
     */
    public function computeQualityScore(): int
    {
        $score = 40; // baseline

        $avgRating = $this->business?->averageRating() ?? 0;
        $score += (int) round(($avgRating / 5) * 30); // up to +30

        if ($this->is_certified) $score += 10;
        if ($this->inspection_status === 'passed') $score += 15;
        if ($this->grade === 'premium') $score += 5;

        return min(100, max(0, $score));
    }

    public function complaintRate(): float
    {
        $views = max(1, $this->views_count);
        $complaints = $this->reports()->count();

        return round(($complaints / $views) * 100, 2);
    }
}
