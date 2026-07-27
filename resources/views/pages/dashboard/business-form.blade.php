@extends('layouts.dashboard')

@php
$isEdit = (bool) $business;
$action = $isEdit ? route('business.update') : route('business.store');
$v = fn ($field, $default = '') => old($field, $isEdit ? ($business->{$field} ?? $default) : $default);
$pageTitle = $isEdit ? ($lang === 'fr' ? 'Modifier mon entreprise' : 'Edit my business') : ($lang === 'fr' ? 'Créer mon entreprise' : 'Create my business');

$fileCls = 'ui-file';
@endphp

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}
    </div>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Nom (français)' : 'Name (French)' }} <span class="ui-req">*</span></label>
                    <input name="name_fr" required value="{{ $v('name_fr') }}" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Nom (anglais)' : 'Name (English)' }}</label>
                    <input name="name_en" value="{{ $v('name_en') }}" class="ui-field">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Slogan (français)' : 'Tagline (French)' }}</label>
                    <input name="tagline_fr" value="{{ $v('tagline_fr') }}" maxlength="255" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Slogan (anglais)' : 'Tagline (English)' }}</label>
                    <input name="tagline_en" value="{{ $v('tagline_en') }}" maxlength="255" class="ui-field">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Description (français)' : 'Description (French)' }}</label>
                    <textarea name="description_fr" rows="4" class="ui-field ui-textarea">{{ $v('description_fr') }}</textarea>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Description (anglais)' : 'Description (English)' }}</label>
                    <textarea name="description_en" rows="4" class="ui-field ui-textarea">{{ $v('description_en') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Classification -->
        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Secteur & Localisation' : 'Industry & Location' }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Secteur' : 'Industry' }} <span class="ui-req">*</span></label>
                    <select name="industry_id" required class="ui-field ui-select">
                        <option value="">{{ $lang === 'fr' ? 'Choisir...' : 'Choose...' }}</option>
                        @foreach($industries as $ind)
                        <option value="{{ $ind->id }}" {{ $v('industry_id') == $ind->id ? 'selected' : '' }}>{{ $lang === 'fr' ? $ind->name_fr : $ind->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Région' : 'Region' }}</label>
                    <select name="region_id" id="region-select" class="ui-field ui-select">
                        <option value="">{{ $lang === 'fr' ? 'Choisir...' : 'Choose...' }}</option>
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
                    <input name="phone" value="{{ $v('phone') }}" placeholder="+237 6XX XX XX XX" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">WhatsApp</label>
                    <input name="whatsapp" value="{{ $v('whatsapp') }}" placeholder="+237 6XX XX XX XX" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">Email</label>
                    <input type="email" name="email" value="{{ $v('email') }}" class="ui-field">
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
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="" class="w-16 h-16 rounded-lg object-cover mb-2 border border-[#EFEBE2]">
                    @endif
                    <input type="file" name="logo" accept="image/*" class="{{ $fileCls }}">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Photo de couverture' : 'Cover photo' }}</label>
                    @if($isEdit && $business->cover_image)
                    <img src="{{ asset('storage/' . $business->cover_image) }}" alt="" class="w-full h-16 rounded-lg object-cover mb-2 border border-[#EFEBE2]">
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
