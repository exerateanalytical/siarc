@php
    // ══ Badge Preview — verbatim from the approved design ══
    $tplHref = route('siarc.admin.accred.templates', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-4">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">Badge Preview</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <a href="{{ $tplHref }}" class="hover:text-[#3B382F]">Badge Templates</a> <i data-lucide="chevron-right" class="w-3 h-3"></i> Template Visiteur Standard <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Aperçu</span></p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ $tplHref }}" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="external-link" class="w-4 h-4 text-[#8A857A]"></i>Changer de template</a>
        <button data-toast="Téléchargement du rendu en préparation…" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i>Télécharger</button>
        <button data-toast="Impression de test envoyée (démo)" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="printer" class="w-4 h-4"></i>Imprimer un test</button>
    </div>
</div>

{{-- info banner --}}
<div class="rounded-lg bg-[#EAF6EE] border border-[#CFE8D8] px-4 py-2.5 mb-5 flex items-center gap-2.5">
    <i data-lucide="info" class="w-4 h-4 text-[#157A43] shrink-0"></i>
    <p class="text-[12px] text-[#155B33]">Vous visualisez le rendu final du badge. Utilisez les options ci-dessous pour changer de vue ou tester les éléments.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[230px_1fr_360px] gap-5 items-start">

    {{-- ══ LEFT: view options ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Vues disponibles</p>
        <style>
            [data-tabs="badgeview"] .si-tab>span{border-color:#EFEDE6;background:#fff}
            [data-tabs="badgeview"] .si-tab.is-active>span{border-color:#CFE8D8;background:#EAF6EE}
            [data-tabs="badgeview"] .si-tab.is-active::after{display:none}
        </style>
        <div data-tabs="badgeview" class="space-y-2">
            <button data-tab="recto" class="si-tab is-active w-full !p-0">
                <span class="w-full flex items-center gap-2.5 rounded-lg border px-3 py-2.5 text-left">
                    <i data-lucide="layout-panel-top" class="w-4 h-4 text-[#157A43]"></i>
                    <span class="leading-tight"><span class="block text-[12.5px] font-semibold text-[#131313]">Vue recto</span><span class="block text-[10.5px] text-[#8A857A]">Face avant du badge</span></span>
                    <i data-lucide="x" class="w-3.5 h-3.5 text-[#8A857A] ml-auto"></i>
                </span>
            </button>
            <button data-tab="verso" class="si-tab w-full !p-0">
                <span class="w-full flex items-center gap-2.5 rounded-lg border px-3 py-2.5 text-left">
                    <i data-lucide="layout-panel-top" class="w-4 h-4 text-[#8A857A] rotate-180"></i>
                    <span class="leading-tight"><span class="block text-[12.5px] font-semibold text-[#3B382F]">Vue verso</span><span class="block text-[10.5px] text-[#8A857A]">Face arrière du badge</span></span>
                </span>
            </button>
        </div>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-2.5">Zoom</p>
        <div class="flex items-center gap-2">
            <button data-toast="Zoom −" class="w-9 h-9 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#3B382F] hover:bg-[#F5F3EE]"><i data-lucide="minus" class="w-4 h-4"></i></button>
            <span class="flex-1 text-center text-[13px] font-semibold text-[#131313] border border-[#EFEDE6] rounded-lg py-2">100%</span>
            <button data-toast="Zoom +" class="w-9 h-9 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#3B382F] hover:bg-[#F5F3EE]"><i data-lucide="plus" class="w-4 h-4"></i></button>
        </div>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-2.5">Affichage</p>
        <div class="space-y-2.5">
            @foreach([['Afficher la zone de découpe',true],['Afficher la zone de sécurité',true],['Afficher les repères',false]] as [$lbl,$on])
            <button data-toast="{{ $lbl }} — {{ $on ? 'désactivé' : 'activé' }} (démo)" class="w-full flex items-center justify-between gap-2 text-left">
                <span class="text-[12px] text-[#3B382F]">{{ $lbl }}</span>
                <span class="w-[34px] h-[19px] rounded-full relative shrink-0 transition-colors {{ $on ? 'bg-[#157A43]' : 'bg-[#D5D2CA]' }}"><span class="absolute top-[2px] {{ $on ? 'right-[2px]' : 'left-[2px]' }} w-[15px] h-[15px] rounded-full bg-white shadow"></span></span>
            </button>
            @endforeach
        </div>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-2.5">Arrière-plan</p>
        <div class="space-y-2">
            @foreach([['Blanc',true],['Transparent',false],['Motif',false]] as [$lbl,$on])
            <button data-toast="Arrière-plan : {{ $lbl }} (démo)" class="flex items-center gap-2.5 text-left">
                <span class="w-[15px] h-[15px] rounded-full border {{ $on ? 'border-[#157A43]' : 'border-[#C9C5BA]' }} flex items-center justify-center">@if($on)<span class="w-[7px] h-[7px] rounded-full bg-[#157A43]"></span>@endif</span>
                <span class="text-[12px] text-[#3B382F]">{{ $lbl }}</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- ══ CENTER: badge render ══ --}}
    <div class="flex flex-col items-center">
        <div data-panel="recto" data-tabs-for="badgeview" class="border-[1.5px] border-dashed border-[#E0A9A9] rounded-2xl p-4 bg-white/40">
            @include('pages.siarc.partials.accred-badge', ['bScale' => '318px'])
        </div>
        <div data-panel="verso" data-tabs-for="badgeview" hidden class="border-[1.5px] border-dashed border-[#E0A9A9] rounded-2xl p-4 bg-white/40">
            <div class="rounded-[14px] overflow-hidden bg-white shadow-[0_10px_28px_-10px_rgba(6,43,21,.45)] border border-black/5" style="width:318px">
                <div class="py-5 text-center" style="background:linear-gradient(160deg,#0B3A1E,#052A15)">
                    <p class="text-white/85 text-[11px] font-semibold tracking-[0.12em]">SIARC 2026 — VERSO</p>
                </div>
                <div class="px-6 py-6 text-[11.5px] text-[#3B382F] space-y-3">
                    <p class="font-bold text-[#131313]">Consignes du porteur</p>
                    <p>Ce badge est strictement personnel et doit être porté visiblement à tout moment dans l'enceinte du salon.</p>
                    <p>En cas de perte, contactez immédiatement le centre d'accréditation.</p>
                    <p class="font-semibold">Contact d'urgence : +237 6 78 90 12 34</p>
                    <p class="text-[#8A857A]">siarc2026.cm/verify</p>
                </div>
                <div class="py-3 text-center" style="background:linear-gradient(160deg,#0B3A1E,#052A15)">
                    <p class="text-[9px] font-bold text-white tracking-wide">27 JUILLET - 05 AOÛT 2026 · MUSÉE NATIONAL DE YAOUNDÉ</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-5 mt-4 text-[11px] text-[#8A857A]">
            <span class="flex items-center gap-1.5"><span class="w-6 border-t-2 border-dashed border-[#D96A6A]"></span>Zone de découpe (85.6 x 54 mm)</span>
            <span class="flex items-center gap-1.5"><span class="w-6 border-t-2 border-dashed border-[#4E9F6C]"></span>Zone de sécurité</span>
            <span class="flex items-center gap-1.5"><span class="w-6 border-t-2 border-dashed border-[#B4B0A6]"></span>Repères d'alignement</span>
        </div>
        <div class="flex items-center gap-2.5 mt-4">
            <button data-toast="Badge précédent (démo)" class="w-9 h-9 rounded-lg border border-[#EFEDE6] bg-white flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
            <span class="text-[12.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] bg-white rounded-lg px-4 py-2">Badge 1 sur 6</span>
            <button data-toast="Badge suivant (démo)" class="w-9 h-9 rounded-lg border border-[#EFEDE6] bg-white flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
        </div>
    </div>

    {{-- ══ RIGHT: badge details ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Détails du badge</p>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>Actif</span>
        </div>
        <dl class="space-y-2.5 text-[12px]">
            <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">Nom du template</dt><dd class="font-medium text-[#3B382F]">Template Visiteur Standard</dd></div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[#8A857A]">Type de badge</dt>
                <dd class="inline-flex items-center gap-1.5 font-medium text-[#157A43]"><span class="w-5 h-5 rounded-full bg-[#E2F3E8] flex items-center justify-center"><i data-lucide="user-round" class="w-3 h-3 text-[#157A43]"></i></span>Visiteur</dd>
            </div>
            @foreach([['Format','Vertical (85.6 x 54 mm)'],['Matériau','PVC'],['Épaisseur','0.76 mm'],['Coins','Arrondis (4 mm)']] as [$k,$v])
            <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#3B382F]">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[#8A857A]">Couleurs</dt>
                <dd class="flex items-center gap-1.5">@foreach(['#157A43','#E6B201','#C0010C','#131313'] as $c)<span class="w-4 h-4 rounded-full" style="background:{{ $c }}"></span>@endforeach</dd>
            </div>
        </dl>
        <p class="text-[11.5px] text-[#8A857A] mt-4 mb-2">Éléments inclus</p>
        <div class="flex flex-wrap gap-1.5">
            @foreach(['Photo','Nom','Organisation','Type','Pays','QR Code','ID Badge','Date','Code-barres'] as $chip)
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#157A43] bg-[#E8F5EC] border border-[#CFE8D8] rounded-md px-2 py-1"><i data-lucide="check-circle-2" class="w-3 h-3"></i>{{ $chip }}</span>
            @endforeach
        </div>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-2.5">Aperçu RFID</p>
        <div class="flex items-start gap-2.5">
            <span class="w-9 h-9 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="radio" class="w-4.5 h-4.5 text-[#157A43]"></i></span>
            <span class="text-[12px] leading-relaxed">
                <span class="block font-semibold text-[#157A43]">RFID activé</span>
                <span class="block text-[#3B382F]">Puce: NXP MIFARE DESFire EV2</span>
                <span class="block text-[#8A857A]">UID: 04 A3 B2 7F 91 6E 80</span>
            </span>
        </div>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-2.5">Actions rapides</p>
        <div class="space-y-1">
            @foreach([['pencil','Modifier le template'],['copy','Dupliquer le template'],['qr-code','Générer QR Code de test'],['printer','Imprimer un test']] as [$ic,$lbl])
            <button data-toast="{{ $lbl }} (démo)" class="w-full flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-[12.5px] text-[#3B382F] hover:bg-[#F5F3EE]"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-[#8A857A]"></i>{{ $lbl }}</button>
            @endforeach
        </div>
        <button data-toast="Désactivation réservée à la production" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2.5 hover:bg-[#FDF3F3]"><i data-lucide="power" class="w-4 h-4"></i>Désactiver le template</button>
    </div>
</div>
