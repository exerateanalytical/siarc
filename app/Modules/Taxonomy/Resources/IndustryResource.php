<?php

namespace App\Modules\Taxonomy\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndustryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'slug'            => $this->slug,
            'name'            => $this->preferredName($request),
            'name_fr'         => $this->name_fr,
            'name_en'         => $this->name_en,
            'icon'            => $this->icon,
            'description'     => $request->header('Accept-Language') === 'en'
                                  ? ($this->description_en ?? $this->description_fr)
                                  : ($this->description_fr ?? $this->description_en),
            'sectors'         => SectorResource::collection($this->whenLoaded('sectors')),
            'businesses_count' => $this->whenCounted('businesses'),
        ];
    }

    private function preferredName(Request $request): string
    {
        $lang = $request->header('Accept-Language', 'fr');
        return ($lang === 'en' && $this->name_en) ? $this->name_en : $this->name_fr;
    }
}
