<?php

namespace App\Modules\Businesses\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'industry_id'        => ['sometimes', 'integer', 'exists:industries,id'],
            'region_id'          => ['nullable', 'integer', 'exists:regions,id'],
            'city_id'            => ['nullable', 'integer', 'exists:cities,id'],
            'name_fr'            => ['sometimes', 'string', 'max:255'],
            'name_en'            => ['nullable', 'string', 'max:255'],
            'tagline_fr'         => ['nullable', 'string', 'max:255'],
            'tagline_en'         => ['nullable', 'string', 'max:255'],
            'description_fr'     => ['nullable', 'string', 'max:5000'],
            'description_en'     => ['nullable', 'string', 'max:5000'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'whatsapp'           => ['nullable', 'string', 'max:30'],
            'email'              => ['nullable', 'email', 'max:255'],
            'website'            => ['nullable', 'url', 'max:255'],
            'address_fr'         => ['nullable', 'string', 'max:500'],
            'address_en'         => ['nullable', 'string', 'max:500'],
            'gps_lat'            => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng'            => ['nullable', 'numeric', 'between:-180,180'],
            'year_established'   => ['nullable', 'integer', 'between:1800,' . date('Y')],
            'employee_count'     => ['nullable', 'string', 'max:50'],
            'ownership_type'     => ['nullable', 'string', 'max:100'],
            'export_countries'   => ['nullable', 'array'],
            'export_countries.*' => ['string', 'max:5'],
            'languages_spoken'   => ['nullable', 'array'],
            'languages_spoken.*' => ['string', 'max:10'],
            'social_links'       => ['nullable', 'array', 'max:10'],
            'social_links.*.platform' => ['required_with:social_links', 'string', 'max:50'],
            'social_links.*.url'      => ['required_with:social_links', 'url', 'max:255'],
            'tags'               => ['nullable', 'array', 'max:15'],
            'tags.*'             => ['string', 'max:50'],
        ];
    }
}
