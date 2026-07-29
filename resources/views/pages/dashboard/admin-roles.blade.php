@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'roles';
    $pageTitle = $isFr ? 'Rôles & Permissions' : 'Roles & Permissions';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Paramètres' : 'Settings', route('admin.settings')], [$isFr ? 'Rôles & Permissions' : 'Roles & Permissions', null]];

    // Tabs (Rôles active; others land on real destinations)
    $roleTabs = [
        ['roles',       'users',       $isFr ? 'Rôles' : 'Roles',                 route('admin.roles', ['lang' => $lang]), true],
        ['permissions', 'shield-check',$isFr ? 'Permissions' : 'Permissions',     route('admin.roles', ['lang' => $lang, 'tab' => 'permissions']), false],
        ['groups',      'layout-grid', $isFr ? 'Groupes de permissions' : 'Permission groups', route('admin.roles', ['lang' => $lang, 'tab' => 'groups']), false],
        ['log',         'scroll-text', $isFr ? 'Journal des accès' : 'Access log', route('admin.audit-log', ['lang' => $lang]), false],
    ];

    $statCards = [
        ['grid-2x2',     '#157A43', '#E8F2EC', $stats['roles'],       $isFr ? 'Rôles Totaux' : 'Total Roles',            $isFr ? '↑ 1 nouveau ce mois' : '↑ 1 new this month'],
        ['users',        '#3565DE', '#E8EFFB', $stats['users'],       $isFr ? 'Utilisateurs Avec Rôle' : 'Users With Role', $isFr ? 'attribués' : 'assigned'],
        ['shield-check', '#C97A16', '#FDF3E0', $stats['permissions'], $isFr ? 'Permissions Totales' : 'Total Permissions', ($isFr ? 'Organisées en ' : 'Across ') . $stats['modules'] . ($isFr ? ' modules' : ' modules')],
        ['lock',         '#7C4FE0', '#F0EAFB', $stats['system'],      $isFr ? 'Rôles Système' : 'System Roles',          $isFr ? 'Non modifiables' : 'Not editable'],
        ['sliders-horizontal', '#0E9F9F', '#E4F5F5', $stats['permissions'], $isFr ? 'Permissions Personnalisées' : 'Custom Permissions', $isFr ? 'Créées par l\'administrateur' : 'Created by the admin'],
    ];

    $actionLabels = [
        'view'     => [$isFr ? 'Voir' : 'View'],
        'create'   => [$isFr ? 'Créer' : 'Create'],
        'edit'     => [$isFr ? 'Modifier' : 'Edit'],
        'delete'   => [$isFr ? 'Supprimer' : 'Delete'],
        'export'   => [$isFr ? 'Exporter' : 'Export'],
        'settings' => [$isFr ? 'Paramètres' : 'Settings'],
    ];
@endphp

@section('content')

            {{-- Header --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="bg-white dark:bg-[#12150F] border border-[#EFEBE2] dark:border-[#262B21] rounded-xl px-4 py-2.5 flex items-center gap-2 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">
                    <i data-lucide="info" class="w-4 h-4 text-[#C9942E] dark:text-[#EDB33A]"></i>{{ $isFr ? 'Gérez les rôles d\'utilisateur et attribuez des permissions' : 'Manage user roles and assign permissions' }}
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 flex items-center gap-6 border-b border-[#EFEBE2] dark:border-[#262B21]">
                @foreach($roleTabs as [$tKey, $tIcon, $tLabel, $tUrl, $tActive])
                <a href="{{ $tUrl }}" class="flex items-center gap-2 pb-3 text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] dark:text-[#339B56] border-b-2 border-[#14652F] dark:border-[#2E9250] ' : 'text-[#8A857A] dark:text-[#868778] hover:text-[#3B382F] dark:hover:text-[#B4B5A6] ' }}">
                    <i data-lucide="{{ $tIcon }}" class="w-4 h-4"></i>{{ $tLabel }}
                </a>
                @endforeach
            </div>

            {{-- Stat cards --}}
            <section class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($statCards as [$sIcon, $sColor, $sTile, $sValue, $sLabel, $sSub])
                <div class="ui-card">
                    <span class="w-[42px] h-[42px] rounded-xl flex items-center justify-center" style="background-color: {{ $sTile }}">
                        <i data-lucide="{{ $sIcon }}" class="w-[20px] h-[20px]" style="color: {{ $sColor }};stroke-width:1.8"></i>
                    </span>
                    <p class="mt-3 text-[24px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">{{ number_format($sValue) }}</p>
                    <p class="mt-1 text-[14px] md:text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $sLabel }}</p>
                    <p class="mt-0.5 text-[13px] md:text-[11px] text-[#6F6B60] dark:text-[#868778]">{{ $sSub }}</p>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[380px_1fr] gap-5 items-start">
                {{-- Role list --}}
                <section class="ui-card">
                    <div class="flex items-center justify-between">
                        <h2 class="ui-card-title">{{ $isFr ? 'Liste des Rôles' : 'Roles List' }}</h2>
                        <a href="{{ route('admin.users', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Ajouter un rôle' : 'Add a role' }}
                        </a>
                    </div>
                    <div class="mt-4 space-y-2.5">
                        @foreach($roles as $r)
                        @php $active = $selected && $r->id === $selected->id; @endphp
                        <a href="{{ route('admin.roles', ['lang' => $lang, 'role' => $r->name]) }}" class="flex items-center gap-3 rounded-xl border px-3.5 py-3 transition-colors {{ $active ? 'border-[#14652F] dark:border-[#2E9250] bg-[#F3F9F4] dark:bg-[#12150F] relative' : 'border-[#EFEBE2] dark:border-[#262B21] hover:bg-[#FAFAF8] dark:hover:bg-[#242A1E] ' }}">
                            @if($active)<span class="absolute left-0 top-3 bottom-3 w-[3px] rounded-full bg-[#14652F] dark:bg-[#2E9250]"></span>@endif
                            <span class="w-[38px] h-[38px] rounded-lg bg-[#F3F0E6] dark:bg-[#1A1E16] flex items-center justify-center shrink-0"><i data-lucide="{{ $r->icon }}" class="w-[18px] h-[18px] text-[#14652F] dark:text-[#339B56]"></i></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $isFr ? $r->fr : $r->en }}</span>
                                <span class="block text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $r->user_count }} {{ $isFr ? 'utilisateurs' : 'users' }}</span>
                            </span>
                            <span class="shrink-0 rounded-md px-2 py-0.5 text-[12px] md:text-[10.5px] font-semibold {{ $r->is_system ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#F0EAFB] dark:bg-[#1E1733] text-[#7C4FE0] dark:text-[#BCA4F0] ' }}">{{ $r->is_system ? ($isFr ? 'Système' : 'System') : ($isFr ? 'Personnalisé' : 'Custom') }}</span>
                        </a>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Affichage de' : 'Showing' }} 1 {{ $isFr ? 'à' : 'to' }} {{ $roles->count() }} {{ $isFr ? 'sur' : 'of' }} {{ $roles->count() }} {{ $isFr ? 'rôles' : 'roles' }}</p>
                </section>

                {{-- Permission matrix --}}
                <section class="ui-card">
                    <form method="POST" action="{{ route('admin.roles.update', ['id' => $selected->id]) }}">
                        @csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="ui-card-title">{{ $isFr ? 'Permissions du rôle' : 'Role permissions' }} : {{ $isFr ? $selected->fr : $selected->en }}</h2>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.roles', ['lang' => $lang, 'role' => $selected->name]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="copy" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Dupliquer le rôle' : 'Duplicate role' }}</a>
                            </div>
                        </div>

                        <div class="ui-table-wrap mt-4">
                            <table class="ui-table min-w-[720px]">
                                <thead>
                                    <tr class="border-b border-[#EFEBE2] dark:border-[#262B21]">
                                        <th>Module / Permission</th>
                                        @foreach($actions as $act)
                                        <th class="text-center">{{ $actionLabels[$act][0] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modules as $modKey => [$modFr, $modEn])
                                    <tr>
                                        <td>
                                            <div class="flex items-start gap-2.5">
                                                <i data-lucide="folder" class="w-4 h-4 mt-0.5 text-[#C9942E] dark:text-[#EDB33A] shrink-0"></i>
                                                <span>
                                                    <span class="block text-[15px] md:text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? $modFr : $modEn }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        @foreach($actions as $act)
                                        @php $pname = $modKey . '.' . $act; $checked = $selectedPerms->has($pname); @endphp
                                        <td class="text-center">
                                            <input type="checkbox" name="perms[]" value="{{ $pname }}" @checked($checked) class="ui-check">
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#EFEBE2] dark:border-[#262B21] pt-4">
                            <div class="flex flex-wrap items-center gap-4 text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#14652F] dark:bg-[#2E9250]"></span>{{ $isFr ? 'Autorisé' : 'Allowed' }}</span>
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-[#EAE5D8] dark:border-[#262B21]"></span>{{ $isFr ? 'Non autorisé' : 'Not allowed' }}</span>
                            </div>
                            <button type="submit" class="ui-btn ui-btn-primary">
                                <i data-lucide="check" class="w-4 h-4"></i>{{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <p class="mt-6 text-center text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
