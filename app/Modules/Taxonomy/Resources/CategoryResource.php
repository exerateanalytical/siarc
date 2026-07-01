<?php

namespace App\Modules\Taxonomy\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'fr');
        return [
            'id'       => $this->id,
            'slug'     => $this->slug,
            'name'     => ($lang === 'en' && $this->name_en) ? $this->name_en : $this->name_fr,
            'name_fr'  => $this->name_fr,
            'name_en'  => $this->name_en,
            'depth'    => $this->depth,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
