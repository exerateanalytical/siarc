<?php

namespace App\Http\Controllers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Services\ProductImageService;
use App\Modules\Products\Services\ProductService;
use App\Modules\Taxonomy\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
        private readonly ProductImageService $imageService,
    ) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function myBusiness(Request $request): Business|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }
        $business = Business::where('user_id', $siacUser['id'])->first();
        if (! $business) {
            return redirect()->route('business.create')
                ->with('success', null);
        }
        return $business;
    }

    private function categoriesForIndustry(?int $industryId)
    {
        return ProductCategory::whereHas('sector', fn ($q) => $q->where('industry_id', $industryId))
            ->where('is_active', true)
            ->with('sector')
            ->orderBy('name_fr')
            ->get();
    }

    public function create(Request $request)
    {
        $lang = $this->lang($request);
        $business = $this->myBusiness($request);
        if ($business instanceof RedirectResponse) return $business;

        $categories = $this->categoriesForIndustry($business->industry_id);

        return view('pages.dashboard.product-form', [
            'lang' => $lang, 'business' => $business, 'product' => null, 'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->myBusiness($request);
        if ($business instanceof RedirectResponse) return $business;

        $data = $this->validated($request);
        $product = $this->service->create($business, $data);
        $this->service->publish($product);
        $this->handleImages($request, $product);

        return redirect()->route('dashboard.entrepreneur')
            ->with('success', $this->lang($request) === 'fr' ? 'Produit créé et publié.' : 'Product created and published.');
    }

    public function edit(Request $request, string $slug)
    {
        $lang = $this->lang($request);
        $business = $this->myBusiness($request);
        if ($business instanceof RedirectResponse) return $business;

        $product = Product::where('business_id', $business->id)->where('slug', $slug)
            ->with('images')->firstOrFail();
        $categories = $this->categoriesForIndustry($business->industry_id);

        return view('pages.dashboard.product-form', compact('lang', 'business', 'product', 'categories'));
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $business = $this->myBusiness($request);
        if ($business instanceof RedirectResponse) return $business;

        $product = Product::where('business_id', $business->id)->where('slug', $slug)->firstOrFail();
        $data = $this->validated($request);
        $this->service->update($product, $data);
        $this->handleImages($request, $product);

        return redirect()->route('products.web-edit', ['slug' => $product->slug])
            ->with('success', $this->lang($request) === 'fr' ? 'Produit mis à jour.' : 'Product updated.');
    }

    public function destroyImage(Request $request, string $slug, int $imageId): RedirectResponse
    {
        $business = $this->myBusiness($request);
        if ($business instanceof RedirectResponse) return $business;

        $product = Product::where('business_id', $business->id)->where('slug', $slug)->firstOrFail();
        $image = $product->images()->findOrFail($imageId);
        $this->imageService->delete($image);

        return redirect()->route('products.web-edit', ['slug' => $product->slug])
            ->with('success', $this->lang($request) === 'fr' ? 'Image supprimée.' : 'Image deleted.');
    }

    private function handleImages(Request $request, Product $product): void
    {
        foreach ($request->file('images', []) as $file) {
            if ($file && $file->isValid()) {
                $this->imageService->upload($file, $product);
            }
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id'         => ['required', 'exists:product_categories,id'],
            'name_fr'             => ['required', 'string', 'max:255'],
            'name_en'             => ['nullable', 'string', 'max:255'],
            'description_fr'      => ['nullable', 'string', 'max:5000'],
            'description_en'      => ['nullable', 'string', 'max:5000'],
            'quantity_available'  => ['nullable', 'integer', 'min:0'],
            'quantity_unit'       => ['nullable', 'string', 'max:20'],
            'moq'                 => ['nullable', 'integer', 'min:0'],
            'moq_unit'            => ['nullable', 'string', 'max:20'],
            'price_type'          => ['nullable', 'in:retail,wholesale,negotiable,contact'],
            'price_amount'        => ['nullable', 'numeric', 'min:0'],
            'price_unit'          => ['nullable', 'string', 'max:20'],
            'is_export_ready'     => ['nullable', 'boolean'],
            'is_organic'          => ['nullable', 'boolean'],
            'is_certified'        => ['nullable', 'boolean'],
            'is_wholesale'        => ['nullable', 'boolean'],
            'is_retail'           => ['nullable', 'boolean'],
            'is_custom_order'     => ['nullable', 'boolean'],
            'is_available'        => ['nullable', 'boolean'],
            'images'              => ['nullable', 'array', 'max:8'],
            'images.*'            => ['nullable', 'image', 'max:4096'],
        ]);
    }
}
