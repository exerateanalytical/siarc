<?php

namespace App\Modules\Businesses\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return [
            'id'                => $this->id,
            'uuid'              => $this->uuid,
            'slug'              => $this->slug,
            'name'              => $pick($this->name_fr, $this->name_en),
            'name_fr'           => $this->name_fr,
            'name_en'           => $this->name_en,
            'tagline'           => $pick($this->tagline_fr, $this->tagline_en),
            'description'       => $pick($this->description_fr, $this->description_en),
            'logo_url'          => $this->logo_url,
            'cover_url'         => $this->cover_url,
            'phone'             => $this->phone,
            'whatsapp'          => $this->whatsapp,
            'email'             => $this->email,
            'website'           => $this->website,
            'address'           => $pick($this->address_fr, $this->address_en),
            'gps_lat'           => $this->gps_lat,
            'gps_lng'           => $this->gps_lng,
            'year_established'  => $this->year_established,
            'employee_count'    => $this->employee_count,
            'ownership_type'    => $this->ownership_type,
            'export_countries'  => $this->export_countries,
            'languages_spoken'  => $this->languages_spoken,
            'is_featured'       => $this->is_featured,
            'verification_tier' => $this->verification_tier,
            'status'            => $this->status,
            'views_count'       => $this->views_count,
            'response_time_hours' => $this->response_time_hours,
            'industry'          => $this->whenLoaded('industry', fn () => [
                'id'   => $this->industry->id,
                'slug' => $this->industry->slug,
                'name' => $pick($this->industry->name_fr, $this->industry->name_en),
                'icon' => $this->industry->icon,
            ]),
            'region'            => $this->whenLoaded('region', fn () => [
                'id'   => $this->region->id,
                'name' => $pick($this->region->name_fr, $this->region->name_en),
            ]),
            'city'              => $this->whenLoaded('city', fn () => [
                'id'   => $this->city->id,
                'name' => $pick($this->city->name_fr, $this->city->name_en),
            ]),
            'social_links'      => $this->whenLoaded('socialLinks', fn () => $this->socialLinks->map(fn ($s) => [
                'platform' => $s->platform,
                'url'      => $s->url,
            ])),
            'gallery'           => $this->whenLoaded('gallery', fn () => $this->gallery->map(fn ($g) => [
                'id'      => $g->id,
                'url'     => $g->url,
                'caption' => $pick($g->caption_fr, $g->caption_en),
            ])),
            'certifications'    => $this->whenLoaded('certifications', fn () => $this->certifications->map(fn ($bc) => [
                'id'          => $bc->id,
                'name'        => $pick($bc->certification->name_fr ?? '', $bc->certification->name_en ?? ''),
                'number'      => $bc->certificate_number,
                'issued_at'   => $bc->issued_at?->toDateString(),
                'expires_at'  => $bc->expires_at?->toDateString(),
                'is_verified' => $bc->is_verified,
            ])),
            'awards'            => $this->whenLoaded('awards', fn () => $this->awards->map(fn ($a) => [
                'id'     => $a->id,
                'title'  => $pick($a->title_fr, $a->title_en),
                'issuer' => $pick($a->issuer_fr, $a->issuer_en),
                'year'   => $a->year,
            ])),
            'tags'              => $this->whenLoaded('tags', fn () => $this->tags->pluck('tag')),
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
