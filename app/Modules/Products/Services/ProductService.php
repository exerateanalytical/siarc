<?php

namespace App\Modules\Products\Services;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductAttribute;
use Illuminate\Support\Arr;

class ProductService
{
    public function create(Business $business, array $data): Product
    {
        $product = Product::create([
            'business_id'      => $business->id,
            'category_id'      => $data['category_id'],
            'name_fr'          => $data['name_fr'],
            'name_en'          => $data['name_en'] ?? null,
            'description_fr'   => $data['description_fr'] ?? null,
            'description_en'   => $data['description_en'] ?? null,
            'quantity_available' => $data['quantity_available'] ?? null,
            'quantity_unit'    => $data['quantity_unit'] ?? $data['unit_fr'] ?? null,
            'moq'              => $data['moq'] ?? $data['min_order_quantity'] ?? null,
            'moq_unit'         => $data['moq_unit'] ?? null,
            'price_type'       => $data['price_type'] ?? 'contact',
            'price_amount'     => $data['price_amount'] ?? null,
            'price_unit'       => $data['price_unit'] ?? null,
            'is_export_ready'  => $data['is_export_ready'] ?? $data['is_exported'] ?? false,
            'is_organic'       => $data['is_organic'] ?? false,
            'is_certified'     => $data['is_certified'] ?? false,
            'is_wholesale'     => $data['is_wholesale'] ?? false,
            'is_retail'        => $data['is_retail'] ?? true,
            'is_custom_order'  => $data['is_custom_order'] ?? false,
            'is_available'     => $data['is_available'] ?? true,
            'status'           => 'draft',
        ]);

        if (! empty($data['attributes'])) {
            $this->syncAttributes($product, $data['attributes']);
        }

        return $product->fresh(['category', 'images', 'attributes']);
    }

    public function update(Product $product, array $data): Product
    {
        $mapped = Arr::except($data, ['attributes', 'unit_fr', 'unit_en', 'min_order_quantity', 'is_exported']);

        if (isset($data['unit_fr'])) {
            $mapped['quantity_unit'] = $data['unit_fr'];
        }
        if (isset($data['min_order_quantity'])) {
            $mapped['moq'] = $data['min_order_quantity'];
        }
        if (isset($data['is_exported'])) {
            $mapped['is_export_ready'] = $data['is_exported'];
        }

        $product->update($mapped);

        if (array_key_exists('attributes', $data)) {
            $this->syncAttributes($product, $data['attributes'] ?? []);
        }

        return $product->fresh(['category', 'images', 'attributes']);
    }

    public function publish(Product $product): Product
    {
        $product->update(['status' => 'published']);
        return $product;
    }

    public function unpublish(Product $product): Product
    {
        $product->update(['status' => 'draft']);
        return $product;
    }

    private function syncAttributes(Product $product, array $attributes): void
    {
        $product->attributes()->delete();
        foreach ($attributes as $attr) {
            ProductAttribute::create([
                'product_id'            => $product->id,
                'attribute_template_id' => $attr['attribute_template_id'] ?? null,
                'value_fr'              => $attr['value_fr'],
                'value_en'              => $attr['value_en'] ?? null,
                'unit'                  => $attr['unit'] ?? null,
            ]);
        }
    }
}
