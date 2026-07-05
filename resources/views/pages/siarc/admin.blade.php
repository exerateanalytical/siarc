@extends('layouts.siarc-admin')

@section('content')
@php
    // Body dispatch: a per-route bespoke body under pages/siarc/bodies/ takes over;
    // otherwise render the generic block scaffold.
    $isFr = ($lang ?? 'fr') === 'fr';
    $rn = request()->route()?->getName() ?? '';
    $bodyKey = str_replace('.', '-', str_replace(['siarc.admin.','siarc.'], ['admin.','pub.'], $rn));
    $bodyView = 'pages.siarc.bodies.'.$bodyKey;
@endphp
<div class="max-w-[1480px] mx-auto">
    @if(view()->exists($bodyView))
        @include($bodyView)
    @else
        @include('pages.siarc._blocks')
    @endif
</div>
@endsection
