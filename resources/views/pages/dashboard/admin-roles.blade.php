@php
    $isFr = $lang === 'fr';
    $adminActive = 'roles';

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
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Rôles & Permissions — Administration' : 'Roles & Permissions — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')

        <main class="px-5 lg:px-7 pb-8">

            @if(session('success'))
            <div class="mt-1 mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]">
                <i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>{{ session('success') }}
            </div>
            @endif

            {{-- Header --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-[21px] font-bold text-[#1B1B18]">{{ $isFr ? 'Rôles & Permissions' : 'Roles & Permissions' }}</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">
                        <a href="{{ route('dashboard.admin') }}" class="hover:text-[#157A43]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                        <span class="mx-1">/</span><a href="{{ route('admin.settings') }}" class="hover:text-[#157A43]">{{ $isFr ? 'Paramètres' : 'Settings' }}</a>
                        <span class="mx-1">/</span><span class="text-[#1B1B18]">{{ $isFr ? 'Rôles & Permissions' : 'Roles & Permissions' }}</span>
                    </p>
                </div>
                <div class="bg-white border border-[#EFF0EF] rounded-xl px-4 py-2.5 flex items-center gap-2 text-[12px] text-[#6F6B60]">
                    <i data-lucide="info" class="w-4 h-4 text-[#C9942E]"></i>{{ $isFr ? 'Gérez les rôles d\'utilisateur et attribuez des permissions' : 'Manage user roles and assign permissions' }}
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 flex items-center gap-6 border-b border-[#EAE7DE]">
                @foreach($roleTabs as [$tKey, $tIcon, $tLabel, $tUrl, $tActive])
                <a href="{{ $tUrl }}" class="flex items-center gap-2 pb-3 text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                    <i data-lucide="{{ $tIcon }}" class="w-4 h-4"></i>{{ $tLabel }}
                </a>
                @endforeach
            </div>

            {{-- Stat cards --}}
            <section class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($statCards as [$sIcon, $sColor, $sTile, $sValue, $sLabel, $sSub])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4">
                    <span class="w-[42px] h-[42px] rounded-xl flex items-center justify-center" style="background-color: {{ $sTile }}">
                        <i data-lucide="{{ $sIcon }}" class="w-[20px] h-[20px]" style="color: {{ $sColor }};stroke-width:1.8"></i>
                    </span>
                    <p class="mt-3 text-[24px] font-bold text-[#1B1B18] leading-none">{{ number_format($sValue) }}</p>
                    <p class="mt-1 text-[12px] font-semibold text-[#3B382F]">{{ $sLabel }}</p>
                    <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ $sSub }}</p>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[380px_1fr] gap-5 items-start">
                {{-- Role list --}}
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Liste des Rôles' : 'Roles List' }}</h2>
                        <a href="{{ route('admin.users', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-3 py-2 text-[11.5px] font-semibold text-white transition-colors">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Ajouter un rôle' : 'Add a role' }}
                        </a>
                    </div>
                    <div class="mt-4 space-y-2.5">
                        @foreach($roles as $r)
                        @php $active = $selected && $r->id === $selected->id; @endphp
                        <a href="{{ route('admin.roles', ['lang' => $lang, 'role' => $r->name]) }}" class="flex items-center gap-3 rounded-xl border px-3.5 py-3 transition-colors {{ $active ? 'border-[#14652F] bg-[#F3F9F4] relative' : 'border-[#EFF0EF] hover:bg-[#FAFAF8]' }}">
                            @if($active)<span class="absolute left-0 top-3 bottom-3 w-[3px] rounded-full bg-[#14652F]"></span>@endif
                            <span class="w-[38px] h-[38px] rounded-lg bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="{{ $r->icon }}" class="w-[18px] h-[18px] text-[#14652F]"></i></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13px] font-bold text-[#1B1B18] truncate">{{ $isFr ? $r->fr : $r->en }}</span>
                                <span class="block text-[11px] text-[#8A857A]">{{ $r->user_count }} {{ $isFr ? 'utilisateurs' : 'users' }}</span>
                            </span>
                            <span class="shrink-0 rounded-md px-2 py-0.5 text-[10.5px] font-semibold {{ $r->is_system ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#F0EAFB] text-[#7C4FE0]' }}">{{ $r->is_system ? ($isFr ? 'Système' : 'System') : ($isFr ? 'Personnalisé' : 'Custom') }}</span>
                        </a>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Affichage de' : 'Showing' }} 1 {{ $isFr ? 'à' : 'to' }} {{ $roles->count() }} {{ $isFr ? 'sur' : 'of' }} {{ $roles->count() }} {{ $isFr ? 'rôles' : 'roles' }}</p>
                </section>

                {{-- Permission matrix --}}
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <form method="POST" action="{{ route('admin.roles.update', ['id' => $selected->id]) }}">
                        @csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Permissions du rôle' : 'Role permissions' }} : {{ $isFr ? $selected->fr : $selected->en }}</h2>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.roles', ['lang' => $lang, 'role' => $selected->name]) }}" class="inline-flex items-center gap-1.5 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-3 py-2 text-[11.5px] font-semibold text-[#3B382F]"><i data-lucide="copy" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Dupliquer le rôle' : 'Duplicate role' }}</a>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[720px]">
                                <thead>
                                    <tr class="text-left border-b border-[#F0F1F0]">
                                        <th class="pb-3 pr-3 text-[11px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Module / Permission</th>
                                        @foreach($actions as $act)
                                        <th class="pb-3 px-2 text-center text-[11px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $actionLabels[$act][0] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F4F5F4]">
                                    @foreach($modules as $modKey => [$modFr, $modEn])
                                    <tr>
                                        <td class="py-3.5 pr-3">
                                            <div class="flex items-start gap-2.5">
                                                <i data-lucide="folder" class="w-4 h-4 mt-0.5 text-[#C9942E] shrink-0"></i>
                                                <span>
                                                    <span class="block text-[12.5px] font-semibold text-[#1B1B18]">{{ $isFr ? $modFr : $modEn }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        @foreach($actions as $act)
                                        @php $pname = $modKey . '.' . $act; $checked = $selectedPerms->has($pname); @endphp
                                        <td class="py-3.5 px-2 text-center">
                                            <input type="checkbox" name="perms[]" value="{{ $pname }}" @checked($checked) class="w-4 h-4 rounded border-[#CFC9BF] text-[#14652F] focus:ring-[#14652F]/40 cursor-pointer">
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#F0F1F0] pt-4">
                            <div class="flex flex-wrap items-center gap-4 text-[11.5px] text-[#6F6B60]">
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#14652F]"></span>{{ $isFr ? 'Autorisé' : 'Allowed' }}</span>
                                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm border border-[#CFC9BF]"></span>{{ $isFr ? 'Non autorisé' : 'Not allowed' }}</span>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-5 py-2.5 text-[12.5px] font-semibold text-white transition-colors">
                                <i data-lucide="check" class="w-4 h-4"></i>{{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
