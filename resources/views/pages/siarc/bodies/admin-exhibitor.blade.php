@php
    use Illuminate\Support\Facades\Route;
    $lang = $lang ?? app()->getLocale();
    $h = fn ($name, $params = []) => Route::has($name) ? route($name, $params) : '#';

    $img = fn ($slug) => asset('images/siarc/'.$slug.'.png');

    // Country flag emoji chips for "Marchés d'intérêt"
    $markets = [
        ['🇫🇷', 'France'],
        ['🇧🇪', 'Belgique'],
        ['🇺🇸', 'États-Unis'],
        ['🇨🇦', 'Canada'],
        ['🇲🇦', 'Maroc'],
    ];
@endphp

{{-- ════════════════════ TOP ROW : PROFILE + HERO GALLERY + RIGHT RAIL ════════════════════ --}}
<div class="grid xl:grid-cols-3 gap-5 mb-5 siarc-in">

    {{-- ── Profile + gallery card ── --}}
    <div class="xl:col-span-2 siarc-card siarc-shadow p-6">
        <div class="flex flex-col lg:flex-row gap-6">

            {{-- logo tile --}}
            <div class="w-[110px] h-[110px] rounded-2xl overflow-hidden border border-[#ECEAE3] bg-[#F7F5F0] shrink-0">
                <img src="{{ $img('exhibitor-logo') }}" alt="Art Bois Précieux" class="w-full h-full object-contain">
            </div>

            {{-- identity block --}}
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-display text-[24px] font-extrabold text-[#1A1712] leading-tight tracking-tight">Art Bois Précieux</h1>
                    <span class="inline-flex items-center text-[11.5px] font-semibold rounded-full px-3 py-1" style="color:#157A43;background:#E2F3E8">Confirmé</span>
                </div>

                <p class="text-[13.5px] text-[#55524A] mt-1.5">Art Bois Précieux SARL</p>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-3">
                    <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#55524A]">
                        <i data-lucide="briefcase" class="w-4 h-4 text-[#8A857A]"></i>Bois &amp; Sculpture
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#55524A]">
                        <span class="text-[13px] leading-none">🇨🇲</span>Cameroun<span class="text-[#C9C4B8]">•</span>Région du Centre
                    </span>
                </div>

                <p class="mt-4 text-[13px] text-[#55524A] leading-relaxed max-w-[430px]">Spécialisés dans la sculpture et l'ameublement en bois précieux, nous valorisons le savoir-faire artisanal camerounais à travers des créations uniques et durables.</p>

                {{-- social + website --}}
                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="w-8 h-8 rounded-full flex items-center justify-center text-white" style="background:#1877F2" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                    </a>
                    <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="w-8 h-8 rounded-full flex items-center justify-center text-white" style="background:linear-gradient(45deg,#F58529,#DD2A7B,#8134AF)" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 3.68a6.16 6.16 0 100 12.32 6.16 6.16 0 000-12.32zm0 10.16a4 4 0 110-8 4 4 0 010 8zm7.85-10.4a1.44 1.44 0 11-2.88 0 1.44 1.44 0 012.88 0z"/></svg>
                    </a>
                    <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="w-8 h-8 rounded-full flex items-center justify-center text-white" style="background:#25D366" aria-label="WhatsApp">
                        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.22 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.05 21.5h-.01a9.42 9.42 0 01-4.8-1.32l-.34-.2-3.57.94.95-3.48-.22-.36a9.4 9.4 0 01-1.44-5.02c0-5.2 4.23-9.42 9.43-9.42 2.52 0 4.88.98 6.66 2.76a9.36 9.36 0 012.75 6.67c0 5.2-4.23 9.42-9.42 9.42zM20.52 3.48A11.35 11.35 0 0012.05.99C5.82.99.77 6.04.77 12.27c0 2 .52 3.95 1.51 5.67L.68 23.51l5.71-1.5a11.3 11.3 0 005.66 1.44h.01c6.23 0 11.28-5.05 11.28-11.28 0-3.01-1.17-5.84-3.3-7.97z"/></svg>
                    </a>
                    <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green">
                        <i data-lucide="globe" class="w-4 h-4"></i>www.artboisprecieux.cm
                    </a>
                </div>
            </div>

            {{-- gallery --}}
            <div class="w-full lg:w-[300px] shrink-0">
                <div class="rounded-xl overflow-hidden bg-[#1A1410] aspect-[16/8.5]">
                    <img src="{{ $img('exhibitor-hero') }}" alt="Créations en bois précieux" class="w-full h-full object-cover">
                </div>
                <div class="grid grid-cols-6 gap-1.5 mt-1.5">
                    @foreach([1,2,3,4,5] as $t)
                    <div class="rounded-md overflow-hidden bg-[#1A1410] aspect-square">
                        <img src="{{ $img('exhibitor-thumb-'.$t) }}" alt="" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                    <div class="rounded-md bg-[#2A2119] text-white/90 text-[12px] font-semibold flex items-center justify-center aspect-square">+8</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right rail : Actions rapides + Statut ── --}}
    <div class="siarc-card siarc-shadow p-5">
        <p class="text-[13.5px] font-bold text-[#1A1712] mb-3">Actions rapides</p>
        <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="siarc-btn siarc-btn-green w-full justify-center px-4 py-2.5 text-[12.5px] mb-2.5">
            <i data-lucide="square-pen" class="w-4 h-4"></i>Modifier l'exposant
        </a>
        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ $h('siarc.admin.stands') }}" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] justify-center px-3 py-2.5 text-[12px] hover:bg-[#FBFAF6]">
                <i data-lucide="grid-3x3" class="w-4 h-4"></i>Assigner un stand
            </a>
            <button type="button" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] justify-center px-3 py-2.5 text-[12px] hover:bg-[#FBFAF6]">
                <i data-lucide="ellipsis" class="w-4 h-4"></i>Plus
            </button>
        </div>

        <div class="h-px bg-[#ECEAE3] my-4"></div>

        <p class="text-[13.5px] font-bold text-[#1A1712] mb-3">Statut de l'exposant</p>
        <dl class="space-y-3">
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">Statut</dt>
                <dd><span class="inline-flex items-center text-[11px] font-semibold rounded-full px-2.5 py-0.5" style="color:#157A43;background:#E2F3E8">Confirmé</span></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">Date d'inscription</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712]">15 Mai 2026</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">Référence</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712]">EXP-2026-00045</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">Type d'exposant</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712]">Entreprise</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">Paiement</dt>
                <dd><span class="inline-flex items-center text-[11px] font-semibold rounded-full px-2.5 py-0.5" style="color:#157A43;background:#E2F3E8">Payé intégralement</span></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">Dernière mise à jour</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712]">Il y a 2 heures</dd>
            </div>
        </dl>
    </div>
</div>

{{-- ════════════════════ TABS ════════════════════ --}}
<div class="siarc-card siarc-shadow px-2 sm:px-4 mb-5 overflow-x-auto">
    <div class="flex items-center gap-1 min-w-max">
        @php
            $tabs = [
                ['home',           'Aperçu', true],
                ['clipboard-list', 'Informations', false],
                ['store',          'Stand & Pavillon', false],
                ['tag',            'Produits', false],
                ['file-text',      'Documents', false],
                ['id-card',        'Accréditations', false],
                ['activity',       'Activités', false],
                ['clock',          'Historique', false],
            ];
        @endphp
        @foreach($tabs as [$icon, $label, $active])
        <button type="button" class="inline-flex items-center gap-2 text-[13px] font-semibold px-3.5 py-4 whitespace-nowrap {{ $active ? 'text-siarc-green border-b-2 border-siarc-green' : 'text-[#8A857A] border-b-2 border-transparent' }}">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>{{ $label }}
        </button>
        @endforeach
    </div>
</div>

{{-- ════════════════════ STAT TILES ════════════════════ --}}
@php
    $stats = [
        ['grid-3x3',    '#7C4FE0', '#F1ECFB', 'Stand assigné',    'C-12',  'Pavillon Centre'],
        ['layout-grid', '#3565DE', '#E9EFFB', 'Surface',          '18 m²', '3m x 6m'],
        ['tag',         '#0D9488', '#E4F4F1', 'Produits exposés', '24',    'Catégories principales'],
        ['id-card',     '#C97A16', '#FBF0DE', 'Accréditations',   '5',     'Personnel accrédité'],
        ['eye',         '#157A43', '#E2F3E8', 'Visites du profil','356',   "Depuis l'inscription"],
        ['handshake',   '#C0010C', '#FCE7E7', 'Réunions B2B',     '12',    'Planifiées'],
    ];
@endphp
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
    @foreach($stats as [$sIcon, $sColor, $sTile, $sLabel, $sValue, $sSub])
    <div class="siarc-card siarc-shadow p-4">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $sTile }}">
            <i data-lucide="{{ $sIcon }}" class="w-5 h-5" style="color:{{ $sColor }}"></i>
        </span>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $sLabel }}</p>
        <p class="text-[22px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $sValue }}</p>
        <p class="text-[11px] text-[#B0AB9F] mt-0.5">{{ $sSub }}</p>
    </div>
    @endforeach
</div>

{{-- ════════════════════ CONTENT GRID : 3 columns ════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    {{-- ── LEFT : Informations sur l'entreprise ── --}}
    <div class="lg:col-span-4 siarc-card siarc-shadow p-6">
        <h3 class="text-[15px] font-bold text-[#1A1712] mb-5">Informations sur l'entreprise</h3>
        <dl class="space-y-3.5">
            @php
                $info = [
                    ['Raison sociale', 'Art Bois Précieux SARL'],
                    ['Représentant',   'Paul Tchameni'],
                    ['Fonction',       'Directeur Général'],
                    ['Téléphone',      '+237 6 98 76 54 32'],
                    ['Email',          'contact@artboisprecieux.cm'],
                ];
            @endphp
            @foreach($info as [$k, $v])
            <div class="flex items-start justify-between gap-4">
                <dt class="text-[12.5px] text-[#8A857A] shrink-0">{{ $k }}</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712] text-right">{{ $v }}</dd>
            </div>
            @endforeach
            <div class="flex items-start justify-between gap-4">
                <dt class="text-[12.5px] text-[#8A857A] shrink-0">Adresse</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712] text-right leading-relaxed">Yaoundé, Quartier Odza<br>BP 1254 Yaoundé, Cameroun</dd>
            </div>

            <div class="h-px bg-[#ECEAE3] my-1"></div>

            <div class="flex items-start justify-between gap-4">
                <dt class="text-[12.5px] text-[#8A857A] shrink-0">Année de création</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712] text-right">2015</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
                <dt class="text-[12.5px] text-[#8A857A] shrink-0">Effectif</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712] text-right">15 - 25 employés</dd>
            </div>
        </dl>

        <div class="mt-5">
            <p class="text-[12.5px] text-[#8A857A] mb-1.5">Description</p>
            <p class="text-[12.5px] text-[#55524A] leading-relaxed">Nous créons des œuvres et meubles en bois précieux 100% camerounais : sculptures traditionnelles, masques, statues, mobilier et objets décoratifs.</p>
        </div>
    </div>

    {{-- ── CENTER : Catégories & Produits phares + Marchés d'intérêt ── --}}
    <div class="lg:col-span-4 space-y-5">
        <div class="siarc-card siarc-shadow p-6">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Catégories &amp; Produits phares</h3>

            <div class="flex flex-wrap items-center gap-2 mb-5">
                @foreach(['Bois & Sculpture', 'Mobilier artisanal', 'Arts & Traditions'] as $cat)
                <span class="inline-flex items-center text-[12px] font-semibold rounded-full px-3 py-1.5" style="color:#157A43;background:#E2F3E8">{{ $cat }}</span>
                @endforeach
            </div>

            @php
                $products = [
                    ['exhibitor-prod-1', 'Sculpture Fang',        '250,000'],
                    ['exhibitor-prod-2', 'Masque Bamiléké',       '180,000'],
                    ['exhibitor-prod-3', 'Table basse en bois',   '350,000'],
                    ['exhibitor-prod-4', 'Statue traditionnelle', '200,000'],
                ];
            @endphp
            <div class="grid grid-cols-2 gap-4">
                @foreach($products as [$pImg, $pName, $pPrice])
                <div>
                    <div class="rounded-xl overflow-hidden bg-[#1A1410] aspect-[4/3.4]">
                        <img src="{{ $img($pImg) }}" alt="{{ $pName }}" class="w-full h-full object-cover">
                    </div>
                    <p class="text-[12.5px] font-semibold text-[#1A1712] mt-2">{{ $pName }}</p>
                    <p class="text-[12px] font-bold text-[#1A1712] mt-0.5">{{ $pPrice }} <span class="text-siarc-green">FCFA</span></p>
                </div>
                @endforeach
            </div>

            <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="flex items-center justify-between rounded-xl border border-[#ECEAE3] px-4 py-3 mt-5 hover:bg-[#FBFAF6] transition-colors">
                <span class="text-[12.5px] font-semibold text-[#3B382F]">Voir tous les produits (24)</span>
                <i data-lucide="arrow-right" class="w-4 h-4 text-[#8A857A]"></i>
            </a>
        </div>

        <div class="siarc-card siarc-shadow p-6">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Marchés d'intérêt</h3>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                @foreach($markets as [$flag, $country])
                <span class="inline-flex items-center gap-2 text-[12.5px] font-medium text-[#3B382F]">
                    <span class="text-[15px] leading-none">{{ $flag }}</span>{{ $country }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── RIGHT : Documents + Notes internes + Activité récente ── --}}
    <div class="lg:col-span-4 space-y-5">

        {{-- Documents --}}
        <div class="siarc-card siarc-shadow p-6">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Documents</h3>
            @php
                $docs = [
                    ['Dossier d\'inscription',            'PDF • 2.4 Mo'],
                    ['Certificat de registre de commerce','PDF • 1.1 Mo'],
                    ['Attestation de paiement',           'PDF • 0.8 Mo'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($docs as [$dName, $dMeta])
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-[#F1ECFB] flex items-center justify-center shrink-0"><i data-lucide="file-text" class="w-4 h-4 text-[#7C4FE0]"></i></span>
                    <span class="text-[12.5px] font-semibold text-[#1A1712] flex-1 min-w-0 truncate">{{ $dName }}</span>
                    <span class="text-[11.5px] text-[#8A857A] shrink-0">{{ $dMeta }}</span>
                    <i data-lucide="download" class="w-4 h-4 text-[#8A857A] shrink-0"></i>
                </div>
                @endforeach
            </div>
            <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-4">
                Voir tous les documents <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Notes internes --}}
        <div class="siarc-card siarc-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-[#1A1712]">Notes internes</h3>
                <button type="button" class="text-[#8A857A] hover:text-[#1A1712]"><i data-lucide="square-pen" class="w-4 h-4"></i></button>
            </div>
            <div class="rounded-xl px-4 py-3.5" style="background:#FDF6E7;border:1px solid #F3E6C4">
                <p class="text-[12.5px] text-[#55524A] leading-relaxed">Exposant sérieux avec de belles pièces de qualité. Souhaite participer à la conférence sur l'export. À suivre pour le programme B2B.</p>
                <p class="text-[11.5px] text-[#8A857A] mt-2">Par Marie Claire <span class="text-[#C9C4B8]">•</span> Il y a 1 jour</p>
            </div>
        </div>

        {{-- Activité récente --}}
        <div class="siarc-card siarc-shadow p-6">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Activité récente</h3>
            @php
                $activity = [
                    ['banknote',       '#157A43', '#E2F3E8', 'Paiement confirmé',            'Montant: 450,000 FCFA', 'Il y a 1 jour'],
                    ['grid-3x3',       '#7C4FE0', '#F1ECFB', 'Stand C-12 assigné',           'Pavillon Centre',       'Il y a 3 jours'],
                    ['check-circle-2', '#3565DE', '#E9EFFB', "Dossier d'inscription approuvé",'Par Admin',            'Il y a 5 jours'],
                ];
            @endphp
            <div class="space-y-4">
                @foreach($activity as [$aIcon, $aColor, $aTile, $aTitle, $aMeta, $aTime])
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $aTile }}"><i data-lucide="{{ $aIcon }}" class="w-4 h-4" style="color:{{ $aColor }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12.5px] font-semibold text-[#1A1712]">{{ $aTitle }}</p>
                        <p class="text-[11.5px] text-[#8A857A] mt-0.5">{{ $aMeta }}</p>
                    </div>
                    <span class="text-[11.5px] text-[#B0AB9F] shrink-0">{{ $aTime }}</span>
                </div>
                @endforeach
            </div>
            <a href="{{ $h('siarc.admin.exhibitor', ['id' => request()->route()?->parameter('id')]) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-4">
                Voir toute l'activité <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</div>
