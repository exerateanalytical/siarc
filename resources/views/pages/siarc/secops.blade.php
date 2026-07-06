@extends('layouts.siarc-secops')

@section('crumb'){{ $sCrumb ?? '' }}@endsection

@section('content')
@php
    // Body dispatch: siarc.admin.secops.lost.case → bodies/secops-lost-case.blade.php
    $rn = request()->route()?->getName() ?? '';
    $bodyKey = str_replace('.', '-', str_replace('siarc.admin.secops.', '', $rn));
@endphp
@includeIf('pages.siarc.bodies.secops-'.$bodyKey)
@endsection
