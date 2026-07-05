@extends('layouts.admin')

@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $adminActive = $sActive ?? 'siarc';
    $pageTitle = $sTitle ?? 'SIARC 2026';
    $pageSubtitle = $sSubtitle ?? ($isFr ? 'Salon International de l\'Artisanat du Cameroun' : 'International Craft Fair of Cameroon');
@endphp

@section('content')
<div class="max-w-[1400px]">
    @include('pages.siarc._blocks')
</div>
@endsection
