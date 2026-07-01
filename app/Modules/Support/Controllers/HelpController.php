<?php

namespace App\Modules\Support\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Support\Models\HelpArticle;
use App\Modules\Support\Models\HelpCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $cats = HelpCategory::with('articles')->orderBy('sort_order')->get();

        return response()->json(['data' => $cats->map(fn ($c) => [
            'id'            => $c->id,
            'slug'          => $c->slug,
            'name'          => $pick($c->name_fr, $c->name_en),
            'icon'          => $c->icon,
            'article_count' => $c->articles->count(),
        ])]);
    }

    public function articles(Request $request, string $categorySlug): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $category = HelpCategory::where('slug', $categorySlug)->firstOrFail();
        $articles  = $category->articles()->get();

        return response()->json(['data' => $articles->map(fn ($a) => [
            'slug'  => $a->slug,
            'title' => $pick($a->title_fr, $a->title_en),
        ])]);
    }

    public function article(Request $request, string $slug): JsonResponse
    {
        $lang    = $request->header('Accept-Language', 'fr');
        $pick    = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;
        $article = HelpArticle::where('slug', $slug)->published()->firstOrFail();
        $article->increment('views_count');

        return response()->json(['data' => [
            'slug'     => $article->slug,
            'title'    => $pick($article->title_fr, $article->title_en),
            'content'  => $pick($article->content_fr, $article->content_en),
            'category' => $article->category ? [
                'slug' => $article->category->slug,
                'name' => $pick($article->category->name_fr, $article->category->name_en),
            ] : null,
        ]]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        $lang   = $request->header('Accept-Language', 'fr');
        $pick   = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;
        $search = '%' . $request->q . '%';

        $articles = HelpArticle::published()
            ->where(fn ($q) => $q->where('title_fr', 'like', $search)
                                  ->orWhere('title_en', 'like', $search)
                                  ->orWhere('content_fr', 'like', $search)
                                  ->orWhere('content_en', 'like', $search))
            ->limit(10)
            ->get();

        return response()->json(['data' => $articles->map(fn ($a) => [
            'slug'  => $a->slug,
            'title' => $pick($a->title_fr, $a->title_en),
        ])]);
    }
}
