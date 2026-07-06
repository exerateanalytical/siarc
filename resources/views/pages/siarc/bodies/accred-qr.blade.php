@php
    // ══ QR Code Generation — verbatim from the approved design ══

    $stats = [
        ['qr-code',       '#157A43', '#E2F3E8', '8 450', 'QR Codes générés',   'au total'],
        ['scan-line',     '#3565DE', '#E8EFFB', '1 250', "Générés aujourd'hui",'14.8% du total'],
        ['check-circle-2','#C97A16', '#FDF3E0', '98.6%', 'Taux de réussite',   'ce mois'],
        ['alert-circle',  '#7C4FE0', '#F0EAFB', '0.4%',  "Taux d'erreur",      'ce mois'],
    ];
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">QR Code Generation</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">QR Code Generation</span></p>
    </div>
    <button data-toast="Historique des générations à venir…" class="siarc-btn text-[13px] text-[#157A43] border border-[#CFE8D8] px-4 py-2.5 rounded-lg bg-white hover:bg-[#F4FAF6]"><i data-lucide="history" class="w-4 h-4"></i>Historique des générations</button>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$value,$label,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-center gap-3.5">
        <span class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[20px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11.5px] text-[#8A857A]">{{ $label }} <span class="text-[#B0AB9F]">{{ $sub }}</span></span>
        </span>
    </div>
    @endforeach
</div>

{{-- ══ TABS ══ --}}
<div data-tabs="qrtabs" class="flex items-center gap-7 border-b border-[#E6E3DB] mb-5 overflow-x-auto">
    @foreach([['qr-code','Génération Unique','unique',true],['layers','Génération en Lot','lot',false],['download','Par Importation','import',false],['refresh-ccw','API / Intégration','api',false]] as [$ic,$lbl,$key,$on])
    <button data-tab="{{ $key }}" class="si-tab {{ $on ? 'is-active' : '' }} inline-flex items-center gap-2"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $lbl }}</button>
    @endforeach
</div>

<div data-panel="unique" data-tabs-for="qrtabs" class="grid grid-cols-1 lg:grid-cols-[1fr_330px_300px] gap-5 items-start">

    {{-- ══ LEFT: generation form ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">1. Sélection du contenu</p>
        <label class="block text-[11px] text-[#8A857A] mb-1">Type de données à encoder</label>
        <select class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F] mb-4"><option>Badge Visiteur</option><option>Badge Exposant</option><option>Badge VIP</option><option>Badge Intervenant</option></select>
        <p class="text-[11px] text-[#8A857A] mb-2">Données du badge</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
            <span><label class="block text-[11px] text-[#8A857A] mb-1">ID Badge</label><input value="VIS-000356" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]"></span>
            <span><label class="block text-[11px] text-[#8A857A] mb-1">Nom complet</label><input value="Jean Paul Essomba" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]"></span>
            <span><label class="block text-[11px] text-[#8A857A] mb-1">Type de badge</label><select class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]"><option>Visiteur</option><option>Exposant</option><option>VIP</option></select></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
            <span><label class="block text-[11px] text-[#8A857A] mb-1">Pavillon / Zone</label><select class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]"><option>Pavillon Officiel</option><option>Pavillon Centre</option></select></span>
            <span><label class="block text-[11px] text-[#8A857A] mb-1">Niveau d'accès</label><select class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]"><option>Standard</option><option>Complet</option></select></span>
            <span><label class="block text-[11px] text-[#8A857A] mb-1">Date de validité</label><span class="flex items-center justify-between gap-2 text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">27/07/2026 - 05/08/2026<i data-lucide="calendar" class="w-4 h-4 text-[#B0AB9F] shrink-0"></i></span></span>
        </div>
        <button data-toast="Champs additionnels à venir…" class="w-full flex items-center justify-between text-[12px] text-[#3B382F] border-b border-[#EFEDE6] pb-3 mb-4">Données additionnelles <span class="text-[#8A857A]">(optionnel)</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i></button>

        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">2. Options du QR Code</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
            @foreach([['Format','QR Code'],['Correction d\'erreur','M (15%)'],['Taille (px)','512 x 512'],['Marges','Standard']] as [$k,$v])
            <span><label class="block text-[11px] text-[#8A857A] mb-1">{{ $k }}</label><select class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]"><option>{{ $v }}</option></select></span>
            @endforeach
        </div>
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2.5 mb-5">
            @foreach([['Inclure le logo SIARC',true],['Inclure l\'ID sous le QR Code',true],['Chiffrer les données',false]] as [$lbl,$on])
            <button data-toast="{{ $lbl }} — {{ $on ? 'désactivé' : 'activé' }} (démo)" class="inline-flex items-center gap-2.5">
                <span class="text-[12px] text-[#3B382F]">{{ $lbl }}</span>
                <span class="w-[34px] h-[19px] rounded-full relative shrink-0 {{ $on ? 'bg-[#157A43]' : 'bg-[#D5D2CA]' }}"><span class="absolute top-[2px] {{ $on ? 'right-[2px]' : 'left-[2px]' }} w-[15px] h-[15px] rounded-full bg-white shadow"></span></span>
            </button>
            @endforeach
        </div>

        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">3. Actions</p>
        <div class="flex flex-wrap items-center gap-2.5">
            <button data-toast="QR Code généré (démo)" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="qr-code" class="w-4 h-4"></i>Générer le QR Code</button>
            <button data-toast="Aperçu avant impression (démo)" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i>Aperçu avant impression</button>
            <button data-toast="Formulaire réinitialisé (démo)" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="rotate-ccw" class="w-4 h-4 text-[#8A857A]"></i>Réinitialiser</button>
        </div>
    </div>

    {{-- ══ MIDDLE: live preview ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Aperçu en temps réel</p>
        <div class="rounded-xl border border-[#EFEDE6] bg-[#FBFAF6] p-4 flex flex-col items-center">
            <img src="{{ asset('images/siarc/accred-qr-logo.png') }}" alt="QR VIS-000356" class="w-[168px] h-[168px] object-contain bg-white rounded-lg p-1.5">
            <p class="text-[14px] font-bold text-[#157A43] mt-2.5">VIS-000356</p>
            <span class="mt-2 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43] bg-[#E8F5EC] border border-[#CFE8D8] rounded-full px-3 py-1"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>Valide</span>
        </div>
        <dl class="mt-4 space-y-2 text-[12px] rounded-lg bg-[#FBFAF6] border border-[#EFEDE6] p-3.5">
            @foreach([['Format','QR Code'],['Taille','512 x 512 px'],["Correction d'erreur",'M (15%)'],['Données','284 octets'],['Dernière mise à jour','24 Mai 2026, 10:45']] as [$k,$v])
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#3B382F]">{{ $v }}</dd></div>
            @endforeach
        </dl>
        <button data-toast="Lisibilité testée : OK (démo)" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="scan-line" class="w-4 h-4"></i>Tester la lisibilité</button>
    </div>

    {{-- ══ RIGHT: download / quick use / integration ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-1.5">Télécharger le QR Code</p>
            <p class="text-[11px] text-[#8A857A] mb-3">Choisissez le format de fichier</p>
            <style>
                [data-tabs="qrfmt"] .si-tab>span{border-color:#EFEDE6;color:#3B382F;font-weight:500}
                [data-tabs="qrfmt"] .si-tab.is-active>span{border-color:#157A43;color:#157A43;font-weight:700}
                [data-tabs="qrfmt"] .si-tab.is-active::after{display:none}
            </style>
            <div data-tabs="qrfmt" class="grid grid-cols-4 gap-2 mb-3">
                @foreach(['PNG','SVG','PDF','EPS'] as $f)
                <button data-tab="{{ $f }}" class="si-tab {{ $loop->first ? 'is-active' : '' }} !p-0"><span class="block w-full rounded-lg border text-[12px] py-2 text-center">{{ $f }}</span></button>
                @endforeach
            </div>
            <div data-panel="PNG" data-tabs-for="qrfmt">
                <a href="{{ asset('images/siarc/accred-qr-logo.png') }}" download="SIARC-QR-VIS-000356.png" class="w-full siarc-btn siarc-btn-green justify-center text-[13px] px-3 py-2.5 rounded-lg mb-2"><i data-lucide="download" class="w-4 h-4"></i>Télécharger PNG</a>
            </div>
            @foreach(['SVG','PDF','EPS'] as $f)
            <div data-panel="{{ $f }}" data-tabs-for="qrfmt" hidden>
                <button data-toast="Export {{ $f }} en préparation…" class="w-full siarc-btn siarc-btn-green justify-center text-[13px] px-3 py-2.5 rounded-lg mb-2"><i data-lucide="download" class="w-4 h-4"></i>Télécharger {{ $f }}</button>
            </div>
            @endforeach
            <button data-toast="Archive ZIP en préparation…" class="w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="folder-archive" class="w-4 h-4"></i>Télécharger tout (ZIP)</button>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Utilisation rapide</p>
            <div class="space-y-2">
                @foreach([['printer','Ajouter à la Print Queue'],['printer','Imprimer maintenant'],['mail','Envoyer par e-mail'],['copy','Copier le QR Code']] as [$ic,$lbl])
                <button data-toast="{{ $lbl }} (démo)" class="w-full flex items-center gap-2.5 rounded-lg border border-[#EFEDE6] px-3 py-2.5 text-left text-[12.5px] text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-[#8A857A]"></i>{{ $lbl }}</button>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-1.5">Intégration</p>
            <p class="text-[11px] text-[#8A857A] mb-2.5">Utilisez ce code dans vos intégrations</p>
            <div class="flex items-center gap-2 mb-2.5">
                <input value="VIS-000356" readonly class="flex-1 text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-[#FBFAF6] text-[#3B382F]">
                <button data-toast="ID copié (démo)" class="w-9 h-9 rounded-lg border border-[#EFEDE6] flex items-center justify-center hover:bg-[#F5F3EE]"><i data-lucide="copy" class="w-4 h-4 text-[#8A857A]"></i></button>
            </div>
            <label class="block text-[11px] text-[#8A857A] mb-1">Lien direct (URL)</label>
            <div class="flex items-center gap-2">
                <input value="https://siarc2026.cm/verify/VIS-000356" readonly class="flex-1 min-w-0 text-[12px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-[#FBFAF6] text-[#3B382F]">
                <button data-toast="Lien copié (démo)" class="w-9 h-9 rounded-lg border border-[#EFEDE6] flex items-center justify-center hover:bg-[#F5F3EE] shrink-0"><i data-lucide="copy" class="w-4 h-4 text-[#8A857A]"></i></button>
            </div>
        </div>
    </div>
</div>

{{-- secondary tabs: informative placeholders that keep the tab bar functional --}}
@foreach([['lot','Génération en Lot','Générez jusqu\'à 500 QR Codes en une seule opération à partir de la liste des badges approuvés.'],['import','Par Importation','Importez un fichier CSV/XLSX contenant les identifiants de badges pour générer leurs QR Codes.'],['api','API / Intégration','Endpoint : POST https://siarc2026.cm/api/v1/qr — authentification par jeton, réponse PNG/SVG encodée en base64.']] as [$key,$title,$text])
<div data-panel="{{ $key }}" data-tabs-for="qrtabs" hidden>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-8 text-center max-w-[560px] mx-auto">
        <span class="w-12 h-12 rounded-xl bg-[#E2F3E8] flex items-center justify-center mx-auto mb-3"><i data-lucide="qr-code" class="w-6 h-6 text-[#157A43]"></i></span>
        <h3 class="text-[16px] font-bold text-[#131313]">{{ $title }}</h3>
        <p class="text-[12.5px] text-[#8A857A] mt-2 leading-relaxed">{{ $text }}</p>
    </div>
</div>
@endforeach
