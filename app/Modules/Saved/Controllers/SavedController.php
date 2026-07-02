<?php

namespace App\Modules\Saved\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Resources\BusinessListResource;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Resources\ProductListResource;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavedController extends Controller
{
    public function businesses(Request $request): JsonResponse
    {
        $user = $request->user();

        $saved = DB::table('saved_businesses')
            ->where('user_id', $user->id)
            ->pluck('business_id');

        $businesses = Business::published()
            ->whereIn('id', $saved)
            ->with(['industry', 'region', 'tags'])
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        return response()->json([
            'data' => BusinessListResource::collection($businesses->items()),
            'meta' => ['total' => $businesses->total(), 'last_page' => $businesses->lastPage()],
        ]);
    }

    public function toggleBusiness(Request $request, string $slug): JsonResponse
    {
        $user     = $request->user();
        $business = Business::where('slug', $slug)->published()->firstOrFail();

        $existing = DB::table('saved_businesses')
            ->where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if ($existing) {
            DB::table('saved_businesses')
                ->where('user_id', $user->id)
                ->where('business_id', $business->id)
                ->delete();
            return response()->json(['saved' => false]);
        }

        DB::table('saved_businesses')->insert([
            'user_id'     => $user->id,
            'business_id' => $business->id,
            'created_at'  => now(),
        ]);

        return response()->json(['saved' => true]);
    }

    public function products(Request $request): JsonResponse
    {
        $user = $request->user();

        $saved = DB::table('saved_products')
            ->where('user_id', $user->id)
            ->pluck('product_id');

        $products = Product::published()
            ->whereIn('id', $saved)
            ->whereHas('business', fn ($q) => $q->published())
            ->with(['primaryImage', 'category', 'business'])
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        return response()->json([
            'data' => ProductListResource::collection($products->items()),
            'meta' => ['total' => $products->total(), 'last_page' => $products->lastPage()],
        ]);
    }

    public function toggleProduct(Request $request, string $slug): JsonResponse
    {
        $user    = $request->user();
        $product = Product::where('slug', $slug)->published()->firstOrFail();

        $existing = DB::table('saved_products')
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            DB::table('saved_products')
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->delete();
            return response()->json(['saved' => false]);
        }

        DB::table('saved_products')->insert([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'created_at' => now(),
        ]);

        return response()->json(['saved' => true]);
    }
}
