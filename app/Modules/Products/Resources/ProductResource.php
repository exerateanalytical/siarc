<?php

namespace App\Modules\Products\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return [
            'id'                 => $this->id,
            'slug'               => $this->slug,
            'name'               => $pick($this->name_fr, $this->name_en),
            'name_fr'            => $this->name_fr,
            'name_en'            => $this->name_en,
            'description'        => $pick($this->description_fr, $this->description_en),
            'unit'            => $this->quantity_unit,
            'moq'             => $this->moq,
            'moq_unit'        => $this->moq_unit,
            'is_export_ready' => $this->is_export_ready,
            'is_organic'      => $this->is_organic,
            'is_certified'    => $this->is_certified,
            'status'             => $this->status,
            'views_count'        => $this->views_count,
            'images'             => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id'  => $img->id,
                'url' => $img->url,
                'alt' => $pick($img->alt_fr, $img->alt_en),
            ])),
            'attributes'         => $this->whenLoaded('attributes', fn () => $this->attributes->map(fn ($a) => [
                'value' => $pick($a->value_fr, $a->value_en),
                'unit'  => $a->unit,
            ])),
            'videos'             => $this->whenLoaded('videos', fn () => $this->videos->map(fn ($v) => [
                'platform'  => $v->platform,
                'embed_url' => $v->embed_url,
                'title'     => $pick($v->title_fr, $v->title_en),
            ])),
            'category'           => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'slug' => $this->category->slug,
                'name' => $pick($this->category->name_fr, $this->category->name_en),
            ]),
            'business'           => $this->whenLoaded('business', fn () => [
                'id'                => $this->business->id,
                'slug'              => $this->business->slug,
                'name'              => $pick($this->business->name_fr, $this->business->name_en),
                'logo_url'          => $this->business->logo_url,
                'verification_tier' => $this->business->verification_tier,
                'phone'             => $this->business->phone,
                'whatsapp'          => $this->business->whatsapp,
                'email'             => $this->business->email,
                'region'            => $this->business->relationLoaded('region') ? [
                    'name' => $pick($this->business->region?->name_fr, $this->business->region?->name_en),
                ] : null,
            ]),
            'created_at'         => $this->created_at?->toIso8601String(),
        ];
    }
}
