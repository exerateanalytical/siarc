<?php

namespace App\Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'query', 'results_count', 'user_id', 'ip_address', 'searched_at',
    ];

    protected function casts(): array
    {
        return ['searched_at' => 'datetime'];
    }
}
