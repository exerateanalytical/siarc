<?php

namespace App\Modules\Taxonomy\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'fr');
        return [
            'id'     => $this->id,
            'code'   => $this->code,
            'name'   => ($lang === 'en' && $this->name_en) ? $this->name_en : $this->name_fr,
            'name_fr' => $this->name_fr,
            'name_en' => $this->name_en,
            'cities' => $this->whenLoaded('cities', fn () => $this->cities->map(fn ($c) => [
                'id'      => $c->id,
                'name'    => ($lang === 'en' && $c->name_en) ? $c->name_en : $c->name_fr,
                'name_fr' => $c->name_fr,
                'name_en' => $c->name_en,
            ])),
        ];
    }
}
