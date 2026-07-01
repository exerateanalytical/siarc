<?php

namespace App\Modules\Businesses\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return [
            'id'                => $this->id,
            'slug'              => $this->slug,
            'name'              => $pick($this->name_fr, $this->name_en),
            'tagline'           => $pick($this->tagline_fr, $this->tagline_en),
            'logo_url'          => $this->logo_url,
            'cover_url'         => $this->cover_url,
            'verification_tier' => $this->verification_tier,
            'is_featured'       => $this->is_featured,
            'industry'          => $this->whenLoaded('industry', fn () => [
                'slug' => $this->industry->slug,
                'name' => $pick($this->industry->name_fr, $this->industry->name_en),
            ]),
            'region'            => $this->whenLoaded('region', fn () => [
                'name' => $pick($this->region->name_fr, $this->region->name_en),
            ]),
            'city'              => $this->whenLoaded('city', fn () => [
                'name' => $pick($this->city->name_fr, $this->city->name_en),
            ]),
            'views_count'       => $this->views_count,
            'tags'              => $this->whenLoaded('tags', fn () => $this->tags->pluck('tag')),
        ];
    }
}
