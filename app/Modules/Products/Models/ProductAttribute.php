<?php

namespace App\Modules\Products\Models;

use App\Modules\Taxonomy\Models\AttributeTemplate;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'attribute_template_id', 'key_fr', 'key_en', 'value_fr', 'value_en', 'unit',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function template(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttributeTemplate::class, 'attribute_template_id');
    }
}
