<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHarvestDate extends Model
{
    protected $fillable = [
        'product_id', 'harvest_date', 'expected_quantity', 'unit', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date' => 'date',
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')->where('harvest_date', '>=', now()->toDateString());
    }
}
