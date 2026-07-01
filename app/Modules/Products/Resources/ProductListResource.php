<?php

namespace App\Modules\Products\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'name'        => $pick($this->name_fr, $this->name_en),
            'unit'            => $this->quantity_unit,
            'is_export_ready' => $this->is_export_ready,
            'thumbnail'   => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->url),
            'category'    => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $pick($this->category->name_fr, $this->category->name_en),
            ]),
            'business'    => $this->whenLoaded('business', fn () => [
                'slug' => $this->business->slug,
                'name' => $pick($this->business->name_fr, $this->business->name_en),
                'verification_tier' => $this->business->verification_tier,
            ]),
            'views_count' => $this->views_count,
        ];
    }
}
