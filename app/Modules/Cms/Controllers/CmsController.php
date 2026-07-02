<?php

namespace App\Modules\CMS\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CMS\Models\CmsAnnouncement;
use App\Modules\CMS\Models\CmsFaqCategory;
use App\Modules\CMS\Models\CmsPage;
use App\Modules\CMS\Models\CmsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    public function page(Request $request, string $slug): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $page = CmsPage::where('slug', $slug)->published()->firstOrFail();

        return response()->json(['data' => [
            'slug'             => $page->slug,
            'title'            => $pick($page->title_fr, $page->title_en),
            'content'          => $pick($page->content_fr, $page->content_en),
            'meta_title'       => $pick($page->meta_title_fr, $page->meta_title_en),
            'meta_description' => $pick($page->meta_description_fr, $page->meta_description_en),
            'published_at'     => $page->published_at?->toIso8601String(),
        ]]);
    }

    public function posts(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $query = CmsPost::published()->orderByDesc('published_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $posts = $query->paginate(max(1, min($request->integer('per_page', 12), 100)));

        return response()->json([
            'data' => collect($posts->items())->map(fn ($p) => [
                'slug'         => $p->slug,
                'type'         => $p->type,
                'title'        => $pick($p->title_fr, $p->title_en),
                'excerpt'      => $pick($p->excerpt_fr, $p->excerpt_en),
                'cover_image'  => $p->cover_image ? \Storage::disk('s3')->temporaryUrl($p->cover_image, now()->addHours(24)) : null,
                'published_at' => $p->published_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $posts->total(), 'last_page' => $posts->lastPage()],
        ]);
    }

    public function post(Request $request, string $slug): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $post = CmsPost::where('slug', $slug)->published()->firstOrFail();
        $post->increment('views_count');

        return response()->json(['data' => [
            'slug'         => $post->slug,
            'type'         => $post->type,
            'title'        => $pick($post->title_fr, $post->title_en),
            'content'      => $pick($post->content_fr, $post->content_en),
            'cover_image'  => $post->cover_image ? \Storage::disk('s3')->temporaryUrl($post->cover_image, now()->addHours(24)) : null,
            'published_at' => $post->published_at?->toIso8601String(),
        ]]);
    }

    public function faqs(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $categories = CmsFaqCategory::with('faqs')->orderBy('sort_order')->get();

        return response()->json(['data' => $categories->map(fn ($cat) => [
            'id'   => $cat->id,
            'name' => $pick($cat->name_fr, $cat->name_en),
            'faqs' => $cat->faqs->map(fn ($f) => [
                'id'       => $f->id,
                'question' => $pick($f->question_fr, $f->question_en),
                'answer'   => $pick($f->answer_fr, $f->answer_en),
            ]),
        ])]);
    }

    public function announcements(Request $request): JsonResponse
    {
        $lang          = $request->header('Accept-Language', 'fr');
        $pick          = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;
        $announcements = CmsAnnouncement::active()->orderByDesc('created_at')->get();

        return response()->json(['data' => $announcements->map(fn ($a) => [
            'id'       => $a->id,
            'type'     => $a->type,
            'title'    => $pick($a->title_fr, $a->title_en),
            'body'     => $pick($a->body_fr, $a->body_en),
            'ends_at'  => $a->ends_at?->toIso8601String(),
        ])]);
    }
}
