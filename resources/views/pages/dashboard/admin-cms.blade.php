@extends('layouts.dashboard')

@php $pageTitle = $lang === 'fr' ? 'Pages CMS' : 'CMS Pages'; @endphp

@section('content')
<div class="max-w-3xl mx-auto">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3.5 mb-4">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <!-- Pages -->
    <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Pages statiques' : 'Static Pages' }}</h2>
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        @forelse($pages as $page)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $page->title_fr }}</p>
                <p class="text-xs text-gray-400">/{{ $page->slug }}</p>
            </div>
            <span @class(['text-xs font-medium px-2 py-1 rounded-full shrink-0', 'bg-green-100 text-green-700' => $page->is_published, 'bg-gray-100 text-gray-500' => !$page->is_published])>
                {{ $page->is_published ? ($lang === 'fr' ? 'Publiée' : 'Published') : ($lang === 'fr' ? 'Brouillon' : 'Draft') }}
            </span>
            <button type="button" onclick="document.getElementById('edit-page-{{ $page->id }}').classList.toggle('hidden')" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 shrink-0">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
            <form method="POST" action="{{ route('admin.cms.pages.destroy', ['id' => $page->id]) }}" onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer cette page ?' : 'Delete this page?' }}')">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-500 shrink-0"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </form>
        </div>
        <div id="edit-page-{{ $page->id }}" class="hidden px-4 py-4 bg-gray-50 border-b border-gray-100">
            <form method="POST" action="{{ route('admin.cms.pages.update', ['id' => $page->id]) }}" class="space-y-2">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <input name="slug" value="{{ $page->slug }}" required class="text-sm border border-gray-200 rounded-lg px-3 py-2">
                    <input name="title_fr" value="{{ $page->title_fr }}" required placeholder="{{ $lang === 'fr' ? 'Titre (FR)' : 'Title (FR)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2">
                </div>
                <textarea name="content_fr" rows="4" placeholder="{{ $lang === 'fr' ? 'Contenu (FR)' : 'Content (FR)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none">{{ $page->content_fr }}</textarea>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }} class="rounded border-gray-300 text-forest-600">
                    {{ $lang === 'fr' ? 'Publiée' : 'Published' }}
                </label>
                <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-xs font-semibold px-3 py-2 rounded-lg">{{ $lang === 'fr' ? 'Enregistrer' : 'Save' }}</button>
            </form>
        </div>
        @empty
        <div class="text-center py-8 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune page.' : 'No pages.' }}</div>
        @endforelse
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-8">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Nouvelle page' : 'New page' }}</h3>
        <form method="POST" action="{{ route('admin.cms.pages.store') }}" class="space-y-2">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <input name="slug" required placeholder="slug-url" class="text-sm border border-gray-200 rounded-lg px-3 py-2">
                <input name="title_fr" required placeholder="{{ $lang === 'fr' ? 'Titre (FR)' : 'Title (FR)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <textarea name="content_fr" rows="3" placeholder="{{ $lang === 'fr' ? 'Contenu (FR)' : 'Content (FR)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none"></textarea>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-forest-600">
                {{ $lang === 'fr' ? 'Publier immédiatement' : 'Publish immediately' }}
            </label>
            <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Créer' : 'Create' }}</button>
        </form>
    </div>

    <!-- FAQs -->
    <h2 class="text-sm font-semibold text-gray-900 mb-3">FAQ</h2>
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        @forelse($faqs as $faq)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                <i data-lucide="help-circle" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $faq->question_fr }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $lang === 'fr' ? $faq->category?->name_fr : ($faq->category?->name_en ?? '—') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.cms.faqs.destroy', ['id' => $faq->id]) }}" onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer ?' : 'Delete?' }}')">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-500 shrink-0"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </form>
        </div>
        @empty
        <div class="text-center py-8 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune question.' : 'No FAQs.' }}</div>
        @endforelse
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Nouvelle question' : 'New FAQ' }}</h3>
        <form method="POST" action="{{ route('admin.cms.faqs.store') }}" class="space-y-2">
            @csrf
            @if($faqCategories->isNotEmpty())
            <select name="category_id" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2">
                <option value="">{{ $lang === 'fr' ? 'Catégorie (optionnel)' : 'Category (optional)' }}</option>
                @foreach($faqCategories as $cat)
                <option value="{{ $cat->id }}">{{ $lang === 'fr' ? $cat->name_fr : ($cat->name_en ?? $cat->name_fr) }}</option>
                @endforeach
            </select>
            @endif
            <input name="question_fr" required placeholder="{{ $lang === 'fr' ? 'Question (FR)' : 'Question (FR)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2">
            <textarea name="answer_fr" required rows="3" placeholder="{{ $lang === 'fr' ? 'Réponse (FR)' : 'Answer (FR)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none"></textarea>
            <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Ajouter' : 'Add' }}</button>
        </form>
    </div>
</div>
@endsection
