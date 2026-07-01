<?php

namespace App\Http\Controllers;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductActionsWebController extends Controller
{
    public function toggleSave(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return $request->wantsJson()
                ? response()->json(['message' => 'unauthenticated'], 401)
                : redirect('/login?next=' . urlencode($request->input('return_to', '/')));
        }

        $product = Product::where('slug', $slug)->firstOrFail();

        $existing = DB::table('saved_products')
            ->where('user_id', $siacUser['id'])
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            DB::table('saved_products')
                ->where('user_id', $siacUser['id'])
                ->where('product_id', $product->id)
                ->delete();
            $saved = false;
        } else {
            DB::table('saved_products')->insert([
                'user_id'    => $siacUser['id'],
                'product_id' => $product->id,
                'created_at' => now(),
            ]);
            $saved = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['saved' => $saved]);
        }

        return redirect($request->input('return_to', '/'))
            ->with('success', $saved
                ? 'Produit sauvegardé.'
                : 'Produit retiré des favoris.');
    }

    public function report(Request $request, string $slug): RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->input('return_to', '/')));
        }

        $data = $request->validate([
            'reason'  => ['required', 'in:spam,misleading,inappropriate,duplicate,other'],
            'details' => ['nullable', 'string', 'max:1000'],
            'return_to' => ['nullable', 'string'],
        ]);

        $product = Product::where('slug', $slug)->firstOrFail();

        ProductReport::create([
            'product_id'  => $product->id,
            'reporter_id' => $siacUser['id'],
            'reason'      => $data['reason'],
            'details'     => $data['details'] ?? null,
            'status'      => 'open',
        ]);

        return redirect($data['return_to'] ?? '/')
            ->with('success', 'Merci, votre signalement a été transmis à notre équipe.');
    }
}
