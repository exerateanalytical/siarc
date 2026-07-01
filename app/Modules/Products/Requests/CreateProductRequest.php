<?php

namespace App\Modules\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'                  => ['required', 'integer', 'exists:product_categories,id'],
            'name_fr'                      => ['required', 'string', 'max:255'],
            'name_en'                      => ['nullable', 'string', 'max:255'],
            'description_fr'               => ['nullable', 'string', 'max:5000'],
            'description_en'               => ['nullable', 'string', 'max:5000'],
            'quantity_unit'                => ['nullable', 'string', 'max:50'],
            'unit_fr'                      => ['nullable', 'string', 'max:50'], // legacy alias
            'moq'                          => ['nullable', 'integer', 'min:1'],
            'moq_unit'                     => ['nullable', 'string', 'max:50'],
            'is_export_ready'              => ['boolean'],
            'is_organic'                   => ['boolean'],
            'is_certified'                 => ['boolean'],
            'attributes'                   => ['nullable', 'array', 'max:30'],
            'attributes.*.attribute_template_id' => ['nullable', 'integer', 'exists:attribute_templates,id'],
            'attributes.*.value_fr'        => ['required', 'string', 'max:255'],
            'attributes.*.value_en'        => ['nullable', 'string', 'max:255'],
            'attributes.*.unit'            => ['nullable', 'string', 'max:50'],
        ];
    }
}
