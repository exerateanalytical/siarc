@extends('layouts.admin')

@php $pageTitle = $lang === 'fr' ? 'Pages CMS' : 'CMS Pages'; @endphp

@section('content')
<div class="max-w-3xl">

    <!-- Pages -->
    <h2 class="ui-card-title mb-3">{{ $lang === 'fr' ? 'Pages statiques' : 'Static Pages' }}</h2>
    <div class="ui-card ui-card--flush mb-4">
        @forelse($pages as $page)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-[#F5F1E8] dark:border-[#262B21] last:border-0">
            <div class="w-9 h-9 rounded-lg bg-[#F8F4EC] dark:bg-[#1A1E16] flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-4 h-4 text-[#B8B2A4] dark:text-[#868778]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $page->title_fr }}</p>
                <p class="ui-dt">/{{ $page->slug }}</p>
            </div>
            <span @class(['text-xs font-medium px-2 py-1 rounded-full shrink-0', 'bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#339B56]' => $page->is_published, 'bg-[#F1EDE3] dark:bg-[#1A1E16] text-[#8A857A] dark:text-[#868778]' => !$page->is_published])>
                {{ $page->is_published ? ($lang === 'fr' ? 'Publiée' : 'Published') : ($lang === 'fr' ? 'Brouillon' : 'Draft') }}
            </span>
            <button type="button" onclick="document.getElementById('edit-page-{{ $page->id }}').classList.toggle('hidden')" class="ui-btn ui-btn-ghost ui-btn-sm shrink-0">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
            <form method="POST" action="{{ route('admin.cms.pages.destroy', ['id' => $page->id]) }}" onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer cette page ?' : 'Delete this page?' }}')">
                @csrf
                <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm shrink-0"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </form>
        </div>
        <div id="edit-page-{{ $page->id }}" class="hidden px-4 py-4 bg-[#F8F4EC] dark:bg-[#1A1E16] border-b border-[#F5F1E8] dark:border-[#262B21]">
            <form method="POST" action="{{ route('admin.cms.pages.update', ['id' => $page->id]) }}" class="space-y-2">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input name="slug" value="{{ $page->slug }}" required class="ui-field">
                    <input name="title_fr" value="{{ $page->title_fr }}" required placeholder="{{ $lang === 'fr' ? 'Titre (FR)' : 'Title (FR)' }}" class="ui-field">
                </div>
                <textarea name="content_fr" rows="4" placeholder="{{ $lang === 'fr' ? 'Contenu (FR)' : 'Content (FR)' }}" class="ui-field ui-textarea w-full">{{ $page->content_fr }}</textarea>
                <label class="ui-check-row items-center">
                    <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }} class="ui-check">
                    {{ $lang === 'fr' ? 'Publiée' : 'Published' }}
                </label>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">{{ $lang === 'fr' ? 'Enregistrer' : 'Save' }}</button>
            </form>
        </div>
        @empty
        {{-- cms_pages is empty and the form that fills it is on this same
             screen (CmsWebController::storePage), so point at it rather than
             leaving a one-word dead end. --}}
        @include('pages.partials.empty-state', [
            'icon'  => 'file-text',
            'state' => 'empty',
            'title' => $lang === 'fr' ? 'Aucune page éditoriale' : 'No editorial pages',
            'body'  => $lang === 'fr'
                ? 'Les pages créées ici (mentions légales, conditions, à propos) sont servies publiquement sous leur adresse. Utilisez le formulaire ci-dessous pour en créer une.'
                : 'Pages created here (legal notice, terms, about) are served publicly at their own address. Use the form below to create one.',
        ])
        @endforelse
    </div>

    <div class="ui-card mb-8">
        <h3 class="ui-card-title mb-3">{{ $lang === 'fr' ? 'Nouvelle page' : 'New page' }}</h3>
        <form method="POST" action="{{ route('admin.cms.pages.store') }}" class="space-y-2">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <input name="slug" required placeholder="slug-url" class="ui-field">
                <input name="title_fr" required placeholder="{{ $lang === 'fr' ? 'Titre (FR)' : 'Title (FR)' }}" class="ui-field">
            </div>
            <textarea name="content_fr" rows="3" placeholder="{{ $lang === 'fr' ? 'Contenu (FR)' : 'Content (FR)' }}" class="ui-field ui-textarea w-full"></textarea>
            <label class="ui-check-row items-center">
                <input type="checkbox" name="is_published" value="1" class="ui-check">
                {{ $lang === 'fr' ? 'Publier immédiatement' : 'Publish immediately' }}
            </label>
            <button type="submit" class="ui-btn ui-btn-primary">{{ $lang === 'fr' ? 'Créer' : 'Create' }}</button>
        </form>
    </div>

    <!-- FAQs -->
    <h2 class="ui-card-title mb-3">FAQ</h2>
    <div class="ui-card ui-card--flush mb-4">
        @forelse($faqs as $faq)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-[#F5F1E8] dark:border-[#262B21] last:border-0">
            <div class="w-9 h-9 rounded-lg bg-[#F8F4EC] dark:bg-[#1A1E16] flex items-center justify-center shrink-0">
                <i data-lucide="help-circle" class="w-4 h-4 text-[#B8B2A4] dark:text-[#868778]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $faq->question_fr }}</p>
                <p class="text-xs text-[#B8B2A4] dark:text-[#868778] truncate">{{ $lang === 'fr' ? $faq->category?->name_fr : ($faq->category?->name_en ?? '—') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.cms.faqs.destroy', ['id' => $faq->id]) }}" onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer ?' : 'Delete?' }}')">
                @csrf
                <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm shrink-0"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </form>
        </div>
        @empty
        {{-- cms_faqs is empty; CmsWebController::storeFaq is the form below. --}}
        @include('pages.partials.empty-state', [
            'icon'  => 'help-circle',
            'state' => 'empty',
            'title' => $lang === 'fr' ? 'Aucune question enregistrée' : 'No questions recorded',
            'body'  => $lang === 'fr'
                ? 'Les questions ajoutées ici alimentent la page FAQ publique. Ajoutez-en une avec le formulaire ci-dessous.'
                : 'Questions added here feed the public FAQ page. Add one with the form below.',
        ])
        @endforelse
    </div>

    <div class="ui-card">
        <h3 class="ui-card-title mb-3">{{ $lang === 'fr' ? 'Nouvelle question' : 'New FAQ' }}</h3>
        <form method="POST" action="{{ route('admin.cms.faqs.store') }}" class="space-y-2">
            @csrf
            @if($faqCategories->isNotEmpty())
            <select name="category_id" class="ui-field ui-select w-full">
                <option value="">{{ $lang === 'fr' ? 'Catégorie (optionnel)' : 'Category (optional)' }}</option>
                @foreach($faqCategories as $cat)
                <option value="{{ $cat->id }}">{{ $lang === 'fr' ? $cat->name_fr : ($cat->name_en ?? $cat->name_fr) }}</option>
                @endforeach
            </select>
            @endif
            <input name="question_fr" required placeholder="{{ $lang === 'fr' ? 'Question (FR)' : 'Question (FR)' }}" class="ui-field w-full">
            <textarea name="answer_fr" required rows="3" placeholder="{{ $lang === 'fr' ? 'Réponse (FR)' : 'Answer (FR)' }}" class="ui-field ui-textarea w-full"></textarea>
            <button type="submit" class="ui-btn ui-btn-primary">{{ $lang === 'fr' ? 'Ajouter' : 'Add' }}</button>
        </form>
    </div>
</div>
@endsection
