<?php

namespace App\Modules\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeTemplate extends Model
{
    protected $fillable = [
        'industry_id', 'name_fr', 'name_en', 'field_key', 'field_type',
        'options_fr', 'options_en', 'unit', 'is_required', 'is_filterable', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options_fr'    => 'array',
            'options_en'    => 'array',
            'is_required'   => 'boolean',
            'is_filterable' => 'boolean',
        ];
    }

    public function industry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
