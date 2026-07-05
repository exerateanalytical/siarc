@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'collections';
    $pageTitle = $isFr?'Ajouter une Collection':'Add a Collection';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr?'Collections Héritage':'Heritage Collections', route('admin.collections', ['lang'=>$lang])], [$isFr?'Ajouter une Collection':'Add a Collection', null]];
    $inputCls = 'w-full h-[44px] border border-[#E5E3E0] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-[#14532D] placeholder-[#A8A498]';
    $labelCls = 'block text-[12.5px] font-semibold text-[#1B1B18] mb-1.5';
    $tabs = [['file-text', $isFr?'Informations Générales':'General Information', true],['image', $isFr?'Médias & Galerie':'Media & Gallery', false],['layers', $isFr?'Éléments de la Collection':'Collection Items', false],['settings', $isFr?'Paramètres & SEO':'Settings & SEO', false],['upload-cloud', 'Publication', false]];
@endphp

@section('content')
            <form method="POST" action="{{ route('admin.collections.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                {{-- Title + actions --}}
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="mt-0.5 text-[12.5px] text-[#6F6B60]"><a href="{{ route('dashboard.admin') }}" class="hover:text-[#157A43]">{{ $isFr?'Accueil':'Home' }}</a> <span class="mx-1">/</span> <a href="{{ route('admin.collections', ['lang'=>$lang]) }}" class="hover:text-[#157A43]">{{ $isFr?'Collections Héritage':'Heritage Collections' }}</a> <span class="mx-1">/</span> <span class="text-[#1B1B18]">{{ $isFr?'Ajouter une Collection':'Add a Collection' }}</span></p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('admin.collections', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour à la liste':'Back to list' }}</a>
                        <button type="submit" name="status" value="draft" class="inline-flex items-center gap-2 bg-white border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#14652F]"><i data-lucide="save" class="w-4 h-4"></i>{{ $isFr?'Enregistrer le brouillon':'Save draft' }}</button>
                    </div>
                </div>

                @if($errors->any())<div class="mt-4 bg-[#FDE8E8] border border-[#F5C9C9] rounded-xl px-4 py-3 text-[12.5px] text-[#B42025]">{{ $errors->first() }}</div>@endif

                {{-- Tabs --}}
                <div class="mt-4 flex items-center gap-6 border-b border-[#EAE7DE] overflow-x-auto">
                    @foreach($tabs as [$tIcon, $tLabel, $tActive])
                    <span class="flex items-center gap-2 pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A]' }}"><i data-lucide="{{ $tIcon }}" class="w-4 h-4"></i>{{ $tLabel }}</span>
                    @endforeach
                </div>

                <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_330px] gap-5 items-start">
                    {{-- Form --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr?'Informations Générales':'General Information' }}</h2>
                        <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $isFr?'Renseignez les informations principales de la collection':'Enter the collection\'s main information' }}</p>

                        <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="lg:col-span-1">
                                <label class="{{ $labelCls }}">{{ $isFr?'Titre de la Collection':'Collection title' }} *</label>
                                <input type="text" name="name_fr" required maxlength="150" value="{{ old('name_fr') }}" placeholder="{{ $isFr?'Ex: Masques Traditionnels du Cameroun':'Ex: Traditional Masks of Cameroon' }}" class="{{ $inputCls }}">
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Région':'Region' }} *</label>
                                <select name="region_fr" class="{{ $inputCls }} bg-white cursor-pointer">
                                    <option value="">{{ $isFr?'Sélectionner une région':'Select a region' }}</option>
                                    @foreach($regions as $r)<option value="{{ $r->name_fr }}" @selected(old('region_fr')===$r->name_fr)>{{ $r->name_fr }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">Slug (URL) *</label>
                                <div class="flex items-center border border-[#E5E3E0] rounded-lg overflow-hidden h-[44px]"><span class="px-3 bg-[#F5F3EE] text-[12px] text-[#8A857A] h-full flex items-center border-r border-[#E5E3E0]">collections/</span><input type="text" name="slug" value="{{ old('slug') }}" placeholder="{{ $isFr?'ex: masques-traditionnels-cameroun':'ex: traditional-masks' }}" class="flex-1 min-w-0 px-3 text-[13px] focus:outline-none"></div>
                                <p class="mt-1 text-[10.5px] text-[#8A857A]">{{ $isFr?'Lettres minuscules, chiffres et tirets uniquement.':'Lowercase letters, numbers and hyphens only.' }}</p>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Centre d\'Artisanat':'Craft Centre' }}</label>
                                <select name="centre" class="{{ $inputCls }} bg-white cursor-pointer">
                                    <option value="">{{ $isFr?'Sélectionner un centre':'Select a centre' }}</option>
                                    @foreach($centres as $c)<option value="{{ $c->name_fr }}">{{ $c->name_fr }}</option>@endforeach
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="{{ $labelCls }}">{{ $isFr?'Description Détaillée':'Detailed description' }} *</label>
                                <div class="border border-[#E5E3E0] rounded-lg overflow-hidden">
                                    <div class="flex items-center gap-1 border-b border-[#F0EFEA] px-2 py-1.5 text-[#8A857A]">@foreach(['bold','italic','underline','list','link','quote'] as $tb)<span class="w-7 h-7 rounded flex items-center justify-center hover:bg-[#F5F3EE]"><i data-lucide="{{ $tb }}" class="w-3.5 h-3.5"></i></span>@endforeach</div>
                                    <textarea name="description_fr" rows="6" maxlength="5000" placeholder="{{ $isFr?'Décrivez en détail l\'histoire, la signification et l\'importance de cette collection...':'Describe in detail the history, meaning and importance of this collection...' }}" class="w-full px-3.5 py-3 text-[13px] focus:outline-none resize-y">{{ old('description_fr') }}</textarea>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Catégorie Principale':'Main category' }} *</label>
                                <select name="category_fr" class="{{ $inputCls }} bg-white cursor-pointer">
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
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="status" value="draft" class="peer sr-only" checked><span class="block text-center rounded-lg border border-[#EAD9AC] py-2 text-[12px] font-semibold text-[#C97A16] peer-checked:bg-[#FDF3E0]">{{ $isFr?'Brouillon':'Draft' }}</span></label>
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="status" value="in_review" class="peer sr-only"><span class="block text-center rounded-lg border border-[#E9E4D8] py-2 text-[12px] font-semibold text-[#8A857A] peer-checked:bg-[#F5F3EE]">{{ $isFr?'En attente':'In review' }}</span></label>
                                    <label class="flex-1 cursor-pointer"><input type="radio" name="status" value="published" class="peer sr-only"><span class="block text-center rounded-lg border border-[#CFE0D4] py-2 text-[12px] font-semibold text-[#157A43] peer-checked:bg-[#E2F3E8]">{{ $isFr?'Publiée':'Published' }}</span></label>
                                </div>
                            </div>
                            <div>
                                <label class="{{ $labelCls }}">{{ $isFr?'Niveau d\'Accès':'Access level' }} *</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" name="visibility" value="public" class="w-4 h-4 text-[#14652F]" checked><span class="text-[12.5px]"><b>Public</b> <span class="text-[#8A857A]">— {{ $isFr?'Visible par tous les visiteurs':'Visible to all visitors' }}</span></span></label>
                                    <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" name="visibility" value="members" class="w-4 h-4 text-[#14652F]"><span class="text-[12.5px]"><b>{{ $isFr?'Membres uniquement':'Members only' }}</b></span></label>
                                    <label class="flex items-center gap-2.5 cursor-pointer"><input type="radio" name="visibility" value="private" class="w-4 h-4 text-[#14652F]"><span class="text-[12.5px]"><b>{{ $isFr?'Privé':'Private' }}</b></span></label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#F0F1F0] pt-4">
                            <a href="{{ route('admin.collections', ['lang'=>$lang]) }}" class="px-5 h-[42px] inline-flex items-center border border-[#E5E3E0] hover:border-[#14532D] rounded-lg text-[13px] font-semibold text-[#3B382F]">{{ $isFr?'Annuler':'Cancel' }}</a>
                            <button type="submit" class="px-6 h-[42px] inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg text-[13px] font-semibold text-white transition-colors"><i data-lucide="check" class="w-4 h-4"></i>{{ $isFr?'Enregistrer et continuer':'Save and continue' }}</button>
                        </div>
                    </section>

                    {{-- Right rail --}}
                    <aside class="space-y-4">
                        <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                            <label class="{{ $labelCls }}">{{ $isFr?'Image de Couverture':'Cover Image' }} *</label>
                            <label class="mt-1 block border-2 border-dashed border-[#E0DED6] rounded-xl px-4 py-8 text-center cursor-pointer hover:border-[#C9942E]">
                                <input type="file" name="cover" accept="image/png,image/jpeg,image/webp" class="sr-only">
                                <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-[#B9B4A9]"></i>
                                <p class="mt-2 text-[12px] font-semibold text-[#3B382F]">{{ $isFr?'Glissez-déposez une image ici':'Drag & drop an image here' }}</p>
                                <p class="text-[11px] text-[#8A857A]">{{ $isFr?'ou cliquez pour parcourir':'or click to browse' }}</p>
                                <p class="mt-2 text-[10px] text-[#A8A498]">{{ $isFr?'Formats : JPG, PNG, WEBP · 1200×800px (Max 2MB)':'Formats: JPG, PNG, WEBP · 1200×800px (Max 2MB)' }}</p>
                            </label>
                        </section>
                        <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                            <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Options de la Collection':'Collection options' }}</h2>
                            <div class="mt-3 space-y-3">
                                <label class="flex items-start gap-2.5 cursor-pointer"><input type="checkbox" name="featured" checked class="w-4 h-4 mt-0.5 rounded text-[#14652F]"><span class="text-[12px]"><b class="text-[#1B1B18]">{{ $isFr?'Collection mise en avant':'Featured collection' }}</b><span class="block text-[10.5px] text-[#8A857A]">{{ $isFr?'Afficher cette collection sur la page d\'accueil':'Show this collection on the homepage' }}</span></span></label>
                                <label class="flex items-start gap-2.5 cursor-pointer"><input type="checkbox" name="comments" class="w-4 h-4 mt-0.5 rounded text-[#14652F]"><span class="text-[12px]"><b class="text-[#1B1B18]">{{ $isFr?'Autoriser les commentaires':'Allow comments' }}</b></span></label>
                                <label class="flex items-start gap-2.5 cursor-pointer"><input type="checkbox" name="searchable" checked class="w-4 h-4 mt-0.5 rounded text-[#14652F]"><span class="text-[12px]"><b class="text-[#1B1B18]">{{ $isFr?'Inclure dans la recherche':'Include in search' }}</b></span></label>
                                <label class="flex items-start gap-2.5 cursor-pointer"><input type="checkbox" name="sponsored" class="w-4 h-4 mt-0.5 rounded text-[#14652F]"><span class="text-[12px]"><b class="text-[#1B1B18]">{{ $isFr?'Collection sponsorisée':'Sponsored collection' }}</b></span></label>
                            </div>
                            <div class="mt-4"><label class="{{ $labelCls }}">{{ $isFr?'Ordre d\'affichage':'Display order' }}</label><input type="number" name="sort_order" value="0" class="{{ $inputCls }}"></div>
                        </section>
                        <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                            <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Informations Supplémentaires':'Additional Information' }}</h2>
                            <div class="mt-3 space-y-3">
                                <div><label class="{{ $labelCls }}">Tags</label><input type="text" name="tags" placeholder="{{ $isFr?'Ajouter des tags...':'Add tags...' }}" class="{{ $inputCls }}"></div>
                                <div><label class="{{ $labelCls }}">{{ $isFr?'Mots-clés SEO':'SEO keywords' }}</label><input type="text" name="seo_keywords" placeholder="{{ $isFr?'Ajouter des mots-clés...':'Add keywords...' }}" class="{{ $inputCls }}"></div>
                                <div><label class="{{ $labelCls }}">{{ $isFr?'Langue de la Collection':'Collection language' }}</label><select name="collection_lang" class="{{ $inputCls }} bg-white cursor-pointer"><option>Français</option><option>English</option></select></div>
                            </div>
                        </section>
                    </aside>
                </div>
            </form>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
@endsection
