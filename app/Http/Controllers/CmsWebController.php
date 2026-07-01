<?php

namespace App\Http\Controllers;

use App\Modules\Cms\Models\CmsFaq;
use App\Modules\Cms\Models\CmsFaqCategory;
use App\Modules\Cms\Models\CmsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CmsWebController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function requireAdmin(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || empty($siacUser['is_admin'])) {
            return redirect('/login');
        }
        return $siacUser;
    }

    public function index(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $pages = CmsPage::orderBy('title_fr')->get();
        $faqs = CmsFaq::with('category')->orderBy('sort_order')->get();
        $faqCategories = CmsFaqCategory::orderBy('sort_order')->get();

        return view('pages.dashboard.admin-cms', compact('lang', 'pages', 'faqs', 'faqCategories'));
    }

    public function storePage(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $this->validatePage($request);
        CmsPage::create($data);

        return back()->with('success', $lang === 'fr' ? 'Page créée.' : 'Page created.');
    }

    public function updatePage(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        CmsPage::findOrFail($id)->update($this->validatePage($request));

        return back()->with('success', $lang === 'fr' ? 'Page mise à jour.' : 'Page updated.');
    }

    public function destroyPage(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        CmsPage::findOrFail($id)->delete();

        return back()->with('success', $lang === 'fr' ? 'Page supprimée.' : 'Page removed.');
    }

    public function storeFaq(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $this->validateFaq($request);
        CmsFaq::create($data);

        return back()->with('success', $lang === 'fr' ? 'Question ajoutée.' : 'FAQ added.');
    }

    public function destroyFaq(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        CmsFaq::findOrFail($id)->delete();

        return back()->with('success', $lang === 'fr' ? 'Question supprimée.' : 'FAQ removed.');
    }

    private function validatePage(Request $request): array
    {
        return $request->validate([
            'slug'         => ['required', 'string', 'max:255', 'alpha_dash'],
            'title_fr'     => ['required', 'string', 'max:255'],
            'title_en'     => ['nullable', 'string', 'max:255'],
            'content_fr'   => ['nullable', 'string'],
            'content_en'   => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]) + ['is_published' => $request->boolean('is_published'), 'published_at' => $request->boolean('is_published') ? now() : null];
    }

    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:cms_faq_categories,id'],
            'question_fr' => ['required', 'string', 'max:500'],
            'question_en' => ['nullable', 'string', 'max:500'],
            'answer_fr'   => ['required', 'string'],
            'answer_en'   => ['nullable', 'string'],
        ]);
    }
}
