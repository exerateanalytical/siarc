<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReportController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse
    {
        $request->validate([
            'reason'  => ['required', 'string', 'in:inappropriate,fake,duplicate,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::where('slug', $slug)->published()->firstOrFail();

        $existing = ProductReport::where('product_id', $product->id)
            ->where('reporter_id', $request->user()->id)
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'You have already reported this product.'], 422);
        }

        ProductReport::create([
            'product_id'  => $product->id,
            'reporter_id' => $request->user()->id,
            'reason'      => $request->reason,
            'details'     => $request->details,
            'status'      => 'pending',
        ]);

        return response()->json(['message' => 'Report submitted.'], 201);
    }
}
