@extends('layouts.siarc-portal')

@section('content')
@php
    // Body dispatch — same convention as the public/admin scaffolds.
    $isFr = ($lang ?? 'fr') === 'fr';
    $rn = request()->route()?->getName() ?? '';
    $bodyKey = str_replace('.', '-', str_replace(['siarc.admin.','siarc.'], ['admin.','pub.'], $rn));
    $bodyView = 'pages.siarc.bodies.'.$bodyKey;
@endphp
<div class="max-w-[1280px] mx-auto">
    @if(view()->exists($bodyView))
        @include($bodyView)
    @else
        @include('pages.siarc._blocks')
    @endif
</div>
@endsection
