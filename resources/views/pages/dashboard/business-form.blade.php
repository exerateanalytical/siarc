@extends('layouts.dashboard')

@php
$isEdit = (bool) $business;
$action = $isEdit ? route('business.update') : route('business.store');
$v = fn ($field, $default = '') => old($field, $isEdit ? ($business->{$field} ?? $default) : $default);
$isFr = $lang === 'fr';
// One name/tagline/description field, in whichever language the artisan is
// using right now — never a French+English pair to type twice (the owner's
// note: "most of these artisans are uneducated"). It reads from whichever
// column is filled (falls back to the other one), matching the display
// convention used everywhere else on the site, and old() still wins on a
// failed validation round-trip.
$vLang = function ($frField, $enField, $key) use ($isFr, $isEdit, $business) {
    $stored = $isEdit ? ($isFr ? ($business->{$frField} ?: $business->{$enField}) : ($business->{$enField} ?: $business->{$frField})) : '';
    return old($key, $stored ?? '');
};
$pageTitle = $isEdit ? ($lang === 'fr' ? 'Modifier mon entreprise' : 'Edit my business') : ($lang === 'fr' ? 'Créer mon entreprise' : 'Create my business');

$fileCls = 'ui-file';
@endphp

@section('content')
<div class="max-w-2xl">

    {{-- "Set up your shop" is one journey in two pages: business details, then
         verification. Not a data-model merge — every field and validation rule
         below is unchanged — just one continuous path instead of two forms an
         artisan had to discover separately. --}}
    @unless($isEdit)
    <div class="mb-4 flex items-center gap-2 text-[12.5px] font-semibold text-[#6F6B60] dark:text-[#868778]">
        <span class="inline-flex items-center gap-1.5 text-[#14532D] dark:text-[#339B56]">
            <span class="w-5 h-5 rounded-full bg-[#14532D] dark:bg-[#339B56] text-white dark:text-[#0A0C09] flex items-center justify-center text-[11px] font-bold">1</span>
            {{ $lang === 'fr' ? 'Détails de l\'entreprise' : 'Business details' }}
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        <span class="inline-flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full border border-[#D9D2C4] dark:border-[#262B21] flex items-center justify-center text-[11px] font-bold">2</span>
            {{ $lang === 'fr' ? 'Vérification' : 'Verification' }}
        </span>
        <span class="ml-auto">{{ $lang === 'fr' ? 'Étape 1 sur 2' : 'Step 1 of 2' }}</span>
    </div>
    @endunless

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}
    </div>
    @endif

    @if($isEdit && $business && $business->verification_tier === 'unverified')
    <a href="{{ route('verification.show') }}" class="mb-4 flex items-center justify-between gap-3 ui-card hover:border-[#14532D] hover:dark:border-[#339B56] transition-colors">
        <span class="flex items-center gap-3 min-w-0">
            <span class="w-9 h-9 shrink-0 rounded-full bg-[#EBF4ED] dark:bg-[#0C3D1D] flex items-center justify-center">
                <i data-lucide="badge-check" class="w-[18px] h-[18px] text-[#14532D] dark:text-[#339B56]"></i>
            </span>
            <span class="min-w-0">
                <span class="block text-[13px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $lang === 'fr' ? 'Étape suivante : faire vérifier votre profil' : 'Next step: get your profile verified' }}</span>
                <span class="block text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $lang === 'fr' ? 'Déposez vos documents pour obtenir le badge vérifié.' : 'Upload your documents to get the verified badge.' }}</span>
            </span>
        </span>
        <i data-lucide="arrow-right" class="w-4 h-4 shrink-0 text-[#14532D] dark:text-[#339B56]"></i>
    </a>
    @endif

    @if($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <div>
            <p class="font-semibold mb-1">{{ $lang === 'fr' ? 'Merci de corriger les erreurs suivantes :' : 'Please fix the following errors:' }}</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <!-- Identity -->
        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Identité' : 'Identity' }}</h2>

            <div class="mb-4">
                <label class="ui-label">{{ $lang === 'fr' ? "Nom de l'entreprise" : 'Business name' }} <span class="ui-req">*</span></label>
                <input name="business_name" required value="{{ $vLang('name_fr', 'name_en', 'business_name') }}" class="ui-field">
            </div>

            <div class="mb-4">
                <label class="ui-label">{{ $lang === 'fr' ? 'Slogan' : 'Tagline' }}</label>
                <input name="business_tagline" value="{{ $vLang('tagline_fr', 'tagline_en', 'business_tagline') }}" maxlength="255" class="ui-field">
            </div>

            <div>
                <label class="ui-label">{{ $lang === 'fr' ? 'Description' : 'Description' }}</label>
                <textarea name="business_description" rows="4" class="ui-field ui-textarea">{{ $vLang('description_fr', 'description_en', 'business_description') }}</textarea>
            </div>
        </div>

        <!-- Classification -->
        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Secteur & Localisation' : 'Industry & Location' }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Votre métier' : 'Your trade' }} <span class="ui-req">*</span></label>
                    {{-- Grouped by corps de métier. A flat list of all 424 taxonomy
                         rows was unusable on the first required field a new artisan
                         meets; optgroup is native, so it works on Android too. --}}
                    <select name="industry_id" required class="ui-field ui-select">
                        <option value="">{{ $lang === 'fr' ? 'Choisir votre métier...' : 'Choose your trade...' }}</option>
                        @foreach($industries as $groupName => $trades)
                        <optgroup label="{{ $groupName }}">
                            @foreach($trades as $ind)
                            <option value="{{ $ind->id }}" {{ old('industry_id', $isEdit ? ($business->industry_id ?? '') : ($prefillIndustryId ?? '')) == $ind->id ? 'selected' : '' }}>{{ $lang === 'fr' ? $ind->name_fr : ($ind->name_en ?: $ind->name_fr) }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                    <p class="ui-hint">{{ $lang === 'fr' ? 'Nomenclature officielle des métiers de l\'artisanat.' : 'Official craft trade nomenclature.' }}</p>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Pays' : 'Country' }}</label>
                    <select name="country_id" id="country-select" class="ui-field ui-select">
                        <option value="">{{ $lang === 'fr' ? 'Choisir...' : 'Choose...' }}</option>
                        @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ $v('country_id') == $country->id ? 'selected' : '' }}>{{ $country->flag_emoji }} {{ $lang === 'fr' ? $country->name_fr : $country->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Région' : 'Region' }}</label>
                    <select name="region_id" id="region-select" class="ui-field ui-select">
                        <option value="">{{ $v('country_id') ? ($lang === 'fr' ? 'Choisir...' : 'Choose...') : ($lang === 'fr' ? 'Choisir un pays d\'abord' : 'Choose a country first') }}</option>
                        @foreach($regions as $region)
                        <option value="{{ $region->id }}" {{ $v('region_id') == $region->id ? 'selected' : '' }}>{{ $lang === 'fr' ? $region->name_fr : $region->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Ville' : 'City' }}</label>
                    <select name="city_id" id="city-select" class="ui-field ui-select">
                        <option value="">{{ $lang === 'fr' ? 'Choisir une région d\'abord' : 'Choose a region first' }}</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ $v('city_id') == $city->id ? 'selected' : '' }}>{{ $lang === 'fr' ? $city->name_fr : $city->name_en }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Contact' : 'Contact' }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Téléphone' : 'Phone' }}</label>
                    <input name="phone" value="{{ $v('phone', $owner->phone ?? '') }}" placeholder="+237 6XX XX XX XX" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">WhatsApp</label>
                    <input name="whatsapp" value="{{ $v('whatsapp') }}" placeholder="+237 6XX XX XX XX" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">Email</label>
                    <input type="email" name="email" value="{{ $v('email', $owner->email ?? '') }}" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Site web' : 'Website' }}</label>
                    <input type="url" name="website" value="{{ $v('website') }}" placeholder="https://" class="ui-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="ui-label">{{ $lang === 'fr' ? 'Adresse' : 'Address' }}</label>
                    <input name="address_fr" value="{{ $v('address_fr') }}" class="ui-field">
                </div>
            </div>
        </div>

        <!-- About -->
        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'À propos' : 'About' }}</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Année de création' : 'Year established' }}</label>
                    <input type="number" name="year_established" value="{{ $v('year_established') }}" min="1900" max="{{ date('Y') }}" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Nombre d\'employés' : 'Employee count' }}</label>
                    <input type="number" name="employee_count" value="{{ $v('employee_count') }}" min="0" class="ui-field">
                </div>
            </div>
        </div>

        <!-- Media -->
        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Logo & Couverture' : 'Logo & Cover' }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Logo' : 'Logo' }}</label>
                    @if($isEdit && $business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="" class="w-16 h-16 rounded-lg object-cover mb-2 border border-[#EFEBE2] dark:border-[#262B21]">
                    @endif
                    <input type="file" name="logo" accept="image/*" class="{{ $fileCls }}">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Photo de couverture' : 'Cover photo' }}</label>
                    @if($isEdit && $business->cover_image)
                    <img src="{{ asset('storage/' . $business->cover_image) }}" alt="" class="w-full h-16 rounded-lg object-cover mb-2 border border-[#EFEBE2] dark:border-[#262B21]">
                    @endif
                    <input type="file" name="cover_image" accept="image/*" class="{{ $fileCls }}">
                </div>
            </div>
        </div>

        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
            <i data-lucide="{{ $isEdit ? 'save' : 'rocket' }}" class="w-4 h-4"></i>
            {{ $isEdit ? ($lang === 'fr' ? 'Enregistrer les modifications' : 'Save changes') : ($lang === 'fr' ? 'Créer et publier mon entreprise' : 'Create and publish my business') }}
        </button>
    </form>
</div>

<script>
/* Country -> region. Added when Côte d'Ivoire and Algeria were opened for
   signup: the region list is no longer a single fixed set of ten, so it has to
   be fetched for whichever country is chosen. Changing country also clears the
   city, because a city belongs to a region that no longer applies. */
document.getElementById('country-select').addEventListener('change', function () {
    var countryId = this.value;
    var regionSelect = document.getElementById('region-select');
    var citySelect = document.getElementById('city-select');

    citySelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Choisir une région d\'abord" : "Choose a region first" }}</option>';

    if (!countryId) {
        regionSelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Choisir un pays d\'abord" : "Choose a country first" }}</option>';
        return;
    }

    regionSelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Chargement..." : "Loading..." }}</option>';
    fetch('/api-interne/regions/' + countryId)
        .then(function (r) { return r.json(); })
        .then(function (regions) {
            regionSelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Choisir..." : "Choose..." }}</option>';
            regions.forEach(function (region) {
                var opt = document.createElement('option');
                opt.value = region.id;
                opt.textContent = '{{ $lang }}' === 'fr' ? region.name_fr : (region.name_en || region.name_fr);
                regionSelect.appendChild(opt);
            });
        })
        .catch(function () {
            /* Leave the field usable rather than stuck on "Chargement...". */
            regionSelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Erreur de chargement — réessayez" : "Could not load — try again" }}</option>';
        });
});

document.getElementById('region-select').addEventListener('change', function () {
    var regionId = this.value;
    var citySelect = document.getElementById('city-select');
    citySelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Chargement..." : "Loading..." }}</option>';
    if (!regionId) {
        citySelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Choisir une région d\'abord" : "Choose a region first" }}</option>';
        return;
    }
    fetch('/api-interne/villes/' + regionId)
        .then(function (r) { return r.json(); })
        .then(function (cities) {
            citySelect.innerHTML = '<option value="">{{ $lang === "fr" ? "Choisir..." : "Choose..." }}</option>';
            cities.forEach(function (city) {
                var opt = document.createElement('option');
                opt.value = city.id;
                opt.textContent = '{{ $lang }}' === 'fr' ? city.name_fr : (city.name_en || city.name_fr);
                citySelect.appendChild(opt);
            });
        });
});
</script>
@endsection
