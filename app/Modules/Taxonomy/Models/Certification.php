<?php

namespace App\Modules\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['industry_id', 'name_fr', 'name_en', 'issuing_body_fr', 'issuing_body_en', 'description_fr', 'description_en'];

    public function industry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
