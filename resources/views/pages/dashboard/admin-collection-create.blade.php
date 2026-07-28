@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'collections';
    $pageTitle = $isFr?'Ajouter une Collection':'Add a Collection';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr?'Collections Héritage':'Heritage Collections', route('admin.collections', ['lang'=>$lang])], [$isFr?'Ajouter une Collection':'Add a Collection', null]];
    $inputCls = 'ui-field';
    $labelCls = 'ui-label';
    $tabs = [['file-text', $isFr?'Informations Générales':'General Information', true],['image', $isFr?'Médias & Galerie':'Media & Gallery', false],['layers', $isFr?'Éléments de la Collection':'Collection Items', false],['settings', $isFr?'Paramètres & SEO':'Settings & SEO', false],['upload-cloud', 'Publication', false]];
@endphp

@section('content')
            <form method="POST" action="{{ route('admin.collections.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                {{-- Title + actions --}}
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="mt-0.5 text-[12.5px] text-[#6F6B60] dark:text-[#868778]"><a href="{{ route('dashboard.admin') }}" class="hover:text-[#157A43] dark:hover:text-[#339B56]">{{ $isFr?'Accueil':'Home' }}</a> <span class="mx-1">/</span> <a href="{{ route('admin.collections', ['lang'=>$lang]) }}" class="hover:text-[#157A43] dark:hover:text-[#339B56]">{{ $isFr?'Collections Héritage':'Heritage Collections' }}</a> <span class="mx-1">/</span> <span class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'Ajouter une Collection':'Add a Collection' }}</span></p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('admin.collections', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour à la liste':'Back to list' }}</a>
                        <button type="submit" name="status" value="draft" class="ui-btn ui-btn-secondary"><i data-lucide="save" class="w-4 h-4"></i>{{ $isFr?'Enregistrer le brouillon':'Save draft' }}</button>
                    </div>
                </div>

                @if($errors->any())<div class="ui-alert ui-alert-danger mt-4">{{ $errors->first() }}</div>@endif

                {{-- Tabs --}}
                <div class="mt-4 flex items-center gap-6 border-b border-[#EFEBE2] dark:border-[#262B21] overflow-x-auto">
                    @foreach($tabs as [$tIcon, $tLabel, $tActive])
                    <span class="flex items-center gap-2 pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] dark:text-[#339B56] border-b-2 border-[#14652F] dark:border-[#2E9250] ' : 'text-[#8A857A] dark:text-[#868778] ' }}"><i data-lucide="{{ $tIcon }}" class="w-4 h-4"></i>{{ $tLabel }}</span>
                    @endforeach
                </div>

                <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_330px] gap-5 items-start">
                    {{-- Form --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Informations Générales':'General Information' }}</h2>
                        <p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Renseignez les informations principales de la collection':'Enter the collection\'s main information' }}</p>

                        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="lg:col-span-1">
                                <label class="{{ $labelCls }}">{{ $isFr?'Titre de la Collection':'Collection title' }} *</label>
                                <input type="text" name="name_fr" required maxlength="150" value="{{ old('name_fr') }}" placeholder="{{ $isFr?'Ex: Masques Traditionnels du Cameroun':'Ex: Traditional Masks of Cameroon' }}" class="{{ $inputCls }}">
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Région':'Region' }} *</label>
                                <select name="region_fr" class="{{ $inputCls }} ui-select">
                                    <option value="">{{ $isFr?'Sélectionner une région':'Select a region' }}</option>
                                    @foreach($regions as $r)<option value="{{ $r->name_fr }}" @selected(old('region_fr')===$r->name_fr)>{{ $r->name_fr }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">Slug (URL) *</label>
                                <div class="ui-field-group"><span class="px-3 bg-[#F5F3EE] dark:bg-[#1A1E16] text-[12px] text-[#8A857A] dark:text-[#868778] h-full flex items-center border-r border-[#EAE5D8] dark:border-[#262B21]">collections/</span><input type="text" name="slug" value="{{ old('slug') }}" placeholder="{{ $isFr?'ex: masques-traditionnels-cameroun':'ex: traditional-masks' }}" class="ui-field-bare flex-1 min-w-0"></div>
                                <p class="mt-1 text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $isFr?'Lettres minuscules, chiffres et tirets uniquement.':'Lowercase letters, numbers and hyphens only.' }}</p>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Centre d\'Artisanat':'Craft Centre' }}</label>
                                <select name="centre" class="{{ $inputCls }} ui-select">
                                    <option value="">{{ $isFr?'Sélectionner un centre':'Select a centre' }}</option>
                                    @foreach($centres as $c)<option value="{{ $c->name_fr }}">{{ $c->name_fr }}</option>@endforeach
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="{{ $labelCls }}">{{ $isFr?'Description Détaillée':'Detailed description' }} *</label>
                                {{-- The toolbar sits above the field rather than inside a second border,
                                     so the editor keeps the same box as every other field. --}}
                                <div>
                                    <div class="flex items-center gap-1 mb-1.5 text-[#8A857A] dark:text-[#868778]">@foreach(['bold','italic','underline','list','link','quote'] as $tb)<span class="w-7 h-7 rounded flex items-center justify-center hover:bg-[#F5F3EE] dark:hover:bg-[#242A1E]"><i data-lucide="{{ $tb }}" class="w-3.5 h-3.5"></i></span>@endforeach</div>
                                    <textarea name="description_fr" rows="6" maxlength="5000" placeholder="{{ $isFr?'Décrivez en détail l\'histoire, la signification et l\'importance de cette collection...':'Describe in detail the history, meaning and importance of this collection...' }}" class="ui-field ui-textarea">{{ old('description_fr') }}</textarea>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Catégorie Principale':'Main category' }} *</label>
                                <select name="category_fr" class="{{ $inputCls }} ui-select">
                                    <option value="">{{ $isFr?'Sélectionner une catégorie':'Select a category' }}</option>
                                    @foreach($industries as $i)<option value="{{ $i->name_fr }}" @selected(old('category_fr')===$i->name_fr)>{{ $i->name_fr }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Origine Culturelle':'Cultural origin' }}</label>
                                <input type="text" name="origin" value="{{ old('origin') }}" placeholder="{{ $isFr?'Ex: Bamileke, Bassa, Grassfields...':'Ex: Bamileke, Bassa, Grassfields...' }}" class="{{ $inputCls }}">
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Statut de la Collection':'Collection status' }} *</label>
                                <div class="flex items-center gap-2">
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="status" value="draft" class="peer sr-only" checked><span class="block text-center rounded-lg border border-[#EAD9AC] dark:border-[#4A3A12] py-2 text-[12px] font-semibold text-[#C97A16] dark:text-[#EDB33A] peer-checked:bg-[#FDF3E0] dark:peer-checked:bg-[#3A2B06]">{{ $isFr?'Brouillon':'Draft' }}</span></label>
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="status" value="in_review" class="peer sr-only"><span class="block text-center rounded-lg border border-[#EAE5D8] dark:border-[#262B21] py-2 text-[12px] font-semibold text-[#8A857A] dark:text-[#868778] peer-checked:bg-[#F5F3EE] dark:peer-checked:bg-[#1A1E16]">{{ $isFr?'En attente':'In review' }}</span></label>
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="status" value="published" class="peer sr-only"><span class="block text-center rounded-lg border border-[#CFE0D4] dark:border-[#39402F] py-2 text-[12px] font-semibold text-[#157A43] dark:text-[#339B56] peer-checked:bg-[#E2F3E8] dark:peer-checked:bg-[#0C3D1D]">{{ $isFr?'Publiée':'Published' }}</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Niveau d\'Accès':'Access level' }} *</label>
                                <div class="space-y-2">
                                    <label class="ui-check-row items-center cursor-pointer"><input type="radio" name="visibility" value="public" class="ui-check" checked><span class="text-[12.5px]"><b>Public</b> <span class="text-[#8A857A] dark:text-[#868778]">— {{ $isFr?'Visible par tous les visiteurs':'Visible to all visitors' }}</span></span></label>
                                    <label class="ui-check-row items-center cursor-pointer"><input type="radio" name="visibility" value="members" class="ui-check"><span class="text-[12.5px]"><b>{{ $isFr?'Membres uniquement':'Members only' }}</b></span></label>
                                    <label class="ui-check-row items-center cursor-pointer"><input type="radio" name="visibility" value="private" class="ui-check"><span class="text-[12.5px]"><b>{{ $isFr?'Privé':'Private' }}</b></span></label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#EFEBE2] dark:border-[#262B21] pt-4">
                            <a href="{{ route('admin.collections', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary ui-btn-lg">{{ $isFr?'Annuler':'Cancel' }}</a>
                            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg"><i data-lucide="check" class="w-4 h-4"></i>{{ $isFr?'Enregistrer et continuer':'Save and continue' }}</button>
                        </div>
                    </section>

                    {{-- Right rail --}}
                    <aside class="space-y-4">
                        <section class="ui-card">
                            <label class="{{ $labelCls }}">{{ $isFr?'Image de Couverture':'Cover Image' }} *</label>
                            <label class="mt-1 block border-2 border-dashed border-[#EAE5D8] dark:border-[#262B21] rounded-xl px-4 py-8 text-center cursor-pointer hover:border-[#C9942E] dark:hover:border-[#E9A81E]">
                                <input type="file" name="cover" accept="image/png,image/jpeg,image/webp" class="sr-only">
                                <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-[#B9B4A9] dark:text-[#868778]"></i>
                                <p class="mt-2 text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr?'Glissez-déposez une image ici':'Drag & drop an image here' }}</p>
                                <p class="text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr?'ou cliquez pour parcourir':'or click to browse' }}</p>
                                <p class="mt-2 text-[10px] text-[#A8A498] dark:text-[#868778]">{{ $isFr?'Formats : JPG, PNG, WEBP · 1200×800px (Max 2MB)':'Formats: JPG, PNG, WEBP · 1200×800px (Max 2MB)' }}</p>
                            </label>
                        </section>
                        <section class="ui-card">
                            <h2 class="ui-card-title">{{ $isFr?'Options de la Collection':'Collection options' }}</h2>
                            <div class="mt-3 space-y-3">
                                <label class="ui-check-row items-start cursor-pointer"><input type="checkbox" name="featured" checked class="ui-check mt-0.5"><span class="text-[12px]"><b class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'Collection mise en avant':'Featured collection' }}</b><span class="block text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $isFr?'Afficher cette collection sur la page d\'accueil':'Show this collection on the homepage' }}</span></span></label>
                                <label class="ui-check-row items-start cursor-pointer"><input type="checkbox" name="comments" class="ui-check mt-0.5"><span class="text-[12px]"><b class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'Autoriser les commentaires':'Allow comments' }}</b></span></label>
                                <label class="ui-check-row items-start cursor-pointer"><input type="checkbox" name="searchable" checked class="ui-check mt-0.5"><span class="text-[12px]"><b class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'Inclure dans la recherche':'Include in search' }}</b></span></label>
                                <label class="ui-check-row items-start cursor-pointer"><input type="checkbox" name="sponsored" class="ui-check mt-0.5"><span class="text-[12px]"><b class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'Collection sponsorisée':'Sponsored collection' }}</b></span></label>
                            </div>
                            <div class="mt-4"><label class="{{ $labelCls }}">{{ $isFr?'Ordre d\'affichage':'Display order' }}</label><input type="number" name="sort_order" value="0" class="{{ $inputCls }}"></div>
                        </section>
                        <section class="ui-card">
                            <h2 class="ui-card-title">{{ $isFr?'Informations Supplémentaires':'Additional Information' }}</h2>
                            <div class="mt-3 space-y-3">
                                <div><label class="{{ $labelCls }}">Tags</label><input type="text" name="tags" placeholder="{{ $isFr?'Ajouter des tags...':'Add tags...' }}" class="{{ $inputCls }}"></div>
                                <div><label class="{{ $labelCls }}">{{ $isFr?'Mots-clés SEO':'SEO keywords' }}</label><input type="text" name="seo_keywords" placeholder="{{ $isFr?'Ajouter des mots-clés...':'Add keywords...' }}" class="{{ $inputCls }}"></div>
                                <div><label class="{{ $labelCls }}">{{ $isFr?'Langue de la Collection':'Collection language' }}</label><select name="collection_lang" class="{{ $inputCls }} ui-select"><option>Français</option><option>English</option></select></div>
                            </div>
                        </section>
                    </aside>
                </div>
            </form>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
