@extends('layouts.siarc-accred')

@section('content')
@php
    // Body dispatch — same convention as the other SIARC scaffolds.
    // siarc.admin.accred.templates → bodies/accred-templates.blade.php
    $isFr = ($lang ?? 'fr') === 'fr';
    $rn = request()->route()?->getName() ?? '';
    $bodyKey = str_replace('.', '-', str_replace('siarc.admin.accred.', '', $rn));
    $bodyView = 'pages.siarc.bodies.accred-'.$bodyKey;
@endphp
@includeIf($bodyView)
@endsection
