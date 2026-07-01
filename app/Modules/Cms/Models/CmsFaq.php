<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CmsFaq extends Model
{
    protected $fillable = [
        'category_id', 'question_fr', 'question_en', 'answer_fr', 'answer_en', 'sort_order',
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CmsFaqCategory::class, 'category_id');
    }
}
