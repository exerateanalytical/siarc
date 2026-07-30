@php
    $isFr = $lang === 'fr';

    $buyerName = $siacUser['name'] ?? '';
    $buyerEmail = $siacUser['email'] ?? '';
    $buyerPhone = $siacUser['phone'] ?? '';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // [num, title, sub]
    $steps = $isFr ? [
        ['1', 'Informations',          'Détails généraux'],
        ['2', 'Articles',              'Produits demandés'],
        ['3', 'Détails & Conditions',  'Livraison, paiement...'],
        ['4', 'Aperçu',                'Vérification'],
        ['5', 'Envoyer',               'Confirmation'],
    ] : [
        ['1', 'Information',           'General details'],
        ['2', 'Items',                 'Requested products'],
        ['3', 'Details & Conditions',  'Delivery, payment...'],
        ['4', 'Preview',               'Verification'],
        ['5', 'Send',                  'Confirmation'],
    ];

    $fieldCls = 'ui-field';
    $labelCls = 'ui-label';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Créer une demande de devis — Artisan Hub 237' : 'Create a quote request — Artisan Hub 237' }}</title>

    @include('pages.partials.icons')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qb-sidebar { display: none; }
        #qb-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; background: #fff; }
        @media (min-width: 1024px) { #qb-sidebar, #qb-sidebar.open { display: block; position: static; width: 264px; overflow-y: visible; } }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#F7F8F7] dark:bg-[#12150F] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        <!-- Title row -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Créer une demande de devis' : 'Create a quote request' }}</h1>
                <p class="mt-1 text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Remplissez les informations ci-dessous pour demander un devis personnalisé à un artisan ou une entreprise.' : 'Fill in the information below to request a personalised quote from an artisan or business.' }}</p>
            </div>
            <a href="{{ route('dashboard.buyer', ['lang' => $lang]) }}" class="shrink-0 ui-btn ui-btn-secondary">
                <i data-lucide="x" class="w-4 h-4"></i>
                {{ $isFr ? 'Quitter sans enregistrer' : 'Leave without saving' }}
            </a>
        </div>

        <!-- Stepper -->
        <div class="mt-5 ui-card overflow-x-auto">
            <div class="flex items-center gap-3 min-w-[860px]">
                @foreach($steps as $stIdx => [$stNum, $stTitle, $stSub])
                <div class="flex items-center gap-3.5 shrink-0">
                    <span class="w-[36px] h-[36px] shrink-0 rounded-full flex items-center justify-center text-[14px] font-bold {{ $stIdx === 0 ? 'bg-[#0E5A2D] dark:bg-[#2E9250] text-white dark:text-[#04150A]' : 'bg-white dark:bg-[#12150F] border border-[#D9DDD9] dark:border-[#39402F] text-[#55524A] dark:text-[#B4B5A6] ' }}">{{ $stNum }}</span>
                    <span>
                        <span class="block text-[13px] font-bold {{ $stIdx === 0 ? 'text-[#14652F] dark:text-[#339B56] ' : 'text-[#1B1B18] dark:text-[#F3EFE7] ' }}">{{ $stTitle }}</span>
                        <span class="block mt-0.5 text-[11.5px] text-[#6F6B60] dark:text-[#868778]">{{ $stSub }}</span>
                    </span>
                </div>
                @if($stIdx < count($steps) - 1)
                <span class="flex-1 min-w-[36px] h-px {{ $stIdx === 0 ? 'bg-[#157A43] dark:bg-[#2E9250] ' : 'bg-[#E0E4E0] dark:bg-[#1A1E16] ' }}"></span>
                @endif
                @endforeach
            </div>
        </div>

        <div class="mt-5 flex flex-col xl:flex-row gap-5 items-start">

            <!-- Form column -->
            <form id="rfq-form" method="POST" action="{{ route('quotes.store') }}" class="flex-1 min-w-0">
                @csrf
                <input type="hidden" name="business_slug" value="{{ $quoteVendor->slug }}">
                @if($errors->any())
                <div class="mb-4 ui-alert ui-alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="ui-card">
                    <h2 class="ui-card-title text-[#14652F] dark:text-[#339B56]">{{ $isFr ? 'Informations de l\'acheteur' : 'Buyer information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-5 gap-y-5">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="ui-req">*</span></label>
                            <input type="text" id="rfq-name" value="{{ $buyerName }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Email <span class="ui-req">*</span></label>
                            <input type="email" id="rfq-email" value="{{ $buyerEmail }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Téléphone' : 'Phone' }} <span class="ui-req">*</span></label>
                            <div class="ui-field-group">
                                <img src="{{ asset('images/landing/qb-flag.png') }}" alt="" class="w-[24px] h-[16px] shrink-0 rounded-sm object-cover">
                                <input type="text" id="rfq-phone" value="{{ $buyerPhone }}" class="ui-field-bare">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Entreprise' : 'Company' }}</label>
                            <input type="text" id="rfq-company" value="" placeholder="{{ $isFr ? 'Nom de votre entreprise (facultatif)' : 'Your company name (optional)' }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Fonction' : 'Role' }}</label>
                            <input type="text" value="" placeholder="{{ $isFr ? 'Votre fonction (facultatif)' : 'Your role (optional)' }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Pays' : 'Country' }} <span class="ui-req">*</span></label>
                            {{-- Grouped with the flag, so the chevron stays markup here rather than
                                 the ui-select background image. --}}
                            <div class="ui-field-group">
                                <img src="{{ asset('images/landing/qb-flag.png') }}" alt="" class="w-[24px] h-[16px] shrink-0 rounded-sm object-cover">
                                <select class="ui-field-bare appearance-none cursor-pointer">
                                    <option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option>
                                    <option>France</option>
                                    <option>{{ $isFr ? 'Autre' : 'Other' }}</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-8 ui-card-title text-[#14652F] dark:text-[#339B56]">{{ $isFr ? 'Informations générales' : 'General information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-5 gap-y-5">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Référence de la demande' : 'Request reference' }} <span class="ui-req">*</span> <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $isFr ? 'Auto-générée' : 'Auto-generated' }})</span></label>
                            {{-- The reference is assigned on save; there is nothing to show yet. --}}
                            <input type="text" value="" readonly placeholder="—" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Titre de la demande' : 'Request title' }} <span class="ui-req">*</span></label>
                            <input type="text" id="rfq-title" name="title" required maxlength="255" placeholder="{{ $isFr ? 'Ex. Mobilier en bois massif pour hôtel' : 'E.g. Solid wood furniture for a hotel' }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Date souhaitée de réponse' : 'Desired response date' }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $isFr ? 'optionnel' : 'optional' }})</span></label>
                            <div class="ui-field-group">
                                <i data-lucide="calendar" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                                <input type="date" name="desired_response_date" min="{{ now()->toDateString() }}" class="ui-field-bare">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="{{ $labelCls }}">{{ $isFr ? 'Description détaillée de votre besoin' : 'Detailed description of your need' }} <span class="ui-req">*</span></label>
                        <p class="-mt-1 mb-2 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Décrivez précisément votre projet, les spécifications, les matériaux souhaités, les finitions, etc.' : 'Describe your project precisely: specifications, desired materials, finishes, etc.' }}</p>
                        <div class="relative">
                            <textarea id="rfq-desc" name="description" required rows="4" maxlength="2000" placeholder="{{ $isFr ? 'Ex. Nous recherchons des meubles en bois massif de haute qualité...' : 'E.g. We are looking for high-quality solid wood furniture...' }}" class="ui-field ui-textarea"></textarea>
                            <span id="rfq-desc-count" class="absolute bottom-3 right-4 text-[11.5px] text-[#8A857A] dark:text-[#868778]">0 / 2000</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="{{ $labelCls }}">{{ $isFr ? 'Joindre des fichiers' : 'Attach files' }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $isFr ? 'optionnel' : 'optional' }})</span></label>
                        <p class="-mt-1 mb-3 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Ajoutez des plans, images de référence, cahier des charges ou tout document utile.' : 'Add plans, reference images, specifications or any useful document.' }}</p>
                        <div class="flex flex-col lg:flex-row gap-4 items-stretch">
                            <div class="flex-1 border-2 border-dashed border-[#CFE0D4] dark:border-[#39402F] rounded-xl px-5 py-8 flex flex-wrap items-center justify-center gap-4">
                                <span class="flex items-center gap-3 text-[13.5px] text-[#3B382F] dark:text-[#B4B5A6]">
                                    <i data-lucide="cloud-upload" class="w-6 h-6 text-[#55524A] dark:text-[#B4B5A6]" style="stroke-width:1.5"></i>
                                    {{ $isFr ? 'Glissez-déposez vos fichiers ici ou' : 'Drag and drop your files here or' }}
                                </span>
                                <label class="ui-btn ui-btn-secondary cursor-pointer">
                                    {{ $isFr ? 'Choisir des fichiers' : 'Choose files' }}
                                    <input type="file" id="rfq-files" multiple class="hidden">
                                </label>
                            </div>
                            {{-- Empty until the buyer picks files; the change handler clones the
                                 template below once per real attachment. --}}
                            <div id="rfq-file-list" class="lg:w-[380px] shrink-0 space-y-2.5"></div>
                            <template id="rfq-file-tpl">
                                <div class="rfq-file flex items-center gap-3.5">
                                    <div class="flex-1 min-w-0 flex items-center gap-3.5 bg-white dark:bg-[#12150F] border border-[#EFF0EF] dark:border-[#262B21] rounded-xl shadow-sm px-3.5 py-2.5">
                                        <img src="{{ asset('images/landing/qb-file-1.png') }}" alt="" class="w-[30px] h-[30px] shrink-0" aria-hidden="true">
                                        <span class="flex-1 min-w-0">
                                            <span class="rfq-file-name block text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap overflow-hidden text-ellipsis"></span>
                                            <span class="rfq-file-size block text-[11.5px] text-[#6F6B60] dark:text-[#868778]"></span>
                                        </span>
                                        <button type="button" class="rfq-file-del shrink-0 text-[#3B382F] dark:text-[#B4B5A6] hover:text-[#DC2626] dark:hover:text-[#F0555C]"><i data-lucide="x" class="w-4 h-4"></i></button>
                                    </div>
                                    <button type="button" onclick="document.getElementById('rfq-files').click()" class="shrink-0 w-[38px] h-[38px] rounded-full bg-white dark:bg-[#12150F] border border-[#EFF0EF] dark:border-[#262B21] shadow-sm flex items-center justify-center text-[#3B5BDB] dark:text-[#8FB6F5] hover:border-[#3B5BDB] dark:hover:border-[#8FB6F5]">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Bottom actions -->
                <div class="mt-4 ui-card flex flex-wrap items-center justify-between gap-3">
                    <button type="button" id="rfq-draft" class="ui-btn ui-btn-secondary">
                        <i data-lucide="file-text" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                        <span id="rfq-draft-label">{{ $isFr ? 'Enregistrer comme brouillon' : 'Save as draft' }}</span>
                    </button>
                    <button type="submit" class="ui-btn ui-btn-primary">
                        {{ $isFr ? 'Étape suivante' : 'Next step' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <!-- Summary rail -->
            <aside class="w-full xl:w-[330px] shrink-0 ui-card">
                <h2 class="ui-card-title">{{ $isFr ? 'Résumé de la demande' : 'Request summary' }}</h2>
                <dl class="mt-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Articles demandés' : 'Requested items' }}</dt>
                        <dd class="text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">0 article{{ $isFr ? '' : 's' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Quantité totale' : 'Total quantity' }}</dt>
                        <dd class="text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">0</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Date de réponse souhaitée' : 'Desired response date' }}</dt>
                        <dd class="text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? '25 Mai 2024' : '25 May 2024' }}</dd>
                    </div>
                </dl>

                <p class="mt-6 text-[12.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</p>
                <div class="mt-2.5 border border-[#EDEEED] dark:border-[#262B21] rounded-xl px-4 py-3.5">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ $quoteVendor->logo ? asset('storage/'.$quoteVendor->logo) : asset('images/landing/qb-artbois.png') }}" alt="" class="w-[46px] h-[46px] shrink-0 rounded-lg object-cover">
                        <div class="min-w-0">
                            <p class="text-[13.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? $quoteVendor->name_fr : ($quoteVendor->name_en ?? $quoteVendor->name_fr) }}</p>
                            @if($quoteVendor->city_name ?? null)<p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $quoteVendor->city_name }}</p>@endif
                            @if(in_array($quoteVendor->verification_tier, ['verified', 'certified']))
                            <p class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43] dark:text-[#339B56]">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                                {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}
                            </p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end">
                        <a href="{{ $vendorUrl }}" class="inline-flex items-center gap-2 text-[12.5px] font-bold text-[#14652F] dark:text-[#339B56] hover:text-[#14532D] dark:hover:text-[#339B56]">
                            {{ $isFr ? 'Voir le profil' : 'View the profile' }}
                            <i data-lucide="square-arrow-out-up-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <div class="mt-5 border-t border-[#F0F1F0] dark:border-[#262B21] pt-5">
                    <p class="text-[12.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Message pour l\'artisan' : 'Message for the artisan' }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $isFr ? 'optionnel' : 'optional' }})</span></p>
                    <p class="mt-1 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Ajouter un message personnalisé à votre demande.' : 'Add a personalised message to your request.' }}</p>
                    <div class="relative mt-2.5">
                        <textarea id="rfq-msg" name="message" rows="5" maxlength="500" placeholder="{{ $isFr ? 'Ex. Nous serions ravis de collaborer avec vous sur ce projet...' : 'E.g. We would be delighted to work with you on this project...' }}" class="ui-field ui-textarea"></textarea>
                        <span id="rfq-msg-count" class="absolute bottom-3 right-4 text-[11.5px] text-[#8A857A] dark:text-[#868778]">0 / 500</span>
                    </div>
                </div>

                <div class="mt-5 bg-[#E9F3EC] dark:bg-[#1A1E16] rounded-xl px-4 py-4">
                    <p class="flex items-center gap-2.5 text-[13px] font-bold text-[#14652F] dark:text-[#339B56]">
                        <i data-lucide="lightbulb" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Conseil' : 'Tip' }}
                    </p>
                    <p class="mt-2 text-[12px] text-[#3B382F] dark:text-[#B4B5A6] leading-relaxed">{{ $isFr ? 'Plus votre demande est détaillée, plus les propositions reçues seront précises et adaptées à vos besoins.' : 'The more detailed your request, the more precise and tailored the proposals you receive will be.' }}</p>
                </div>
            </aside>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

    // Live character counters
    function bindCount(taId, countId, max) {
        const ta = document.getElementById(taId), c = document.getElementById(countId);
        const upd = () => c.textContent = ta.value.length + ' / ' + max;
        ta.addEventListener('input', upd); upd();
    }
    bindCount('rfq-desc', 'rfq-desc-count', 2000);
    bindCount('rfq-msg', 'rfq-msg-count', 500);

    // File list: remove rows, add picked files (client-side visual list per the design)
    function bindDel(scope) {
        (scope || document).querySelectorAll('.rfq-file-del').forEach(b => b.addEventListener('click', () => b.closest('.rfq-file').remove()));
    }
    bindDel();
    document.getElementById('rfq-files').addEventListener('change', function () {
        const list = document.getElementById('rfq-file-list');
        const tpl = document.getElementById('rfq-file-tpl');
        Array.from(this.files).forEach(f => {
            const row = tpl.content.firstElementChild.cloneNode(true);
            row.querySelector('.rfq-file-name').textContent = f.name;
            row.querySelector('.rfq-file-size').textContent = (f.size / 1048576).toFixed(1) + ' MB';
            list.appendChild(row);
            bindDel(row);
            lucide.createIcons();
        });
    });

    // Draft: stored locally (no RFQ backend), with visible confirmation
    document.getElementById('rfq-draft').addEventListener('click', () => {
        localStorage.setItem('rfqDraft', JSON.stringify({
            title: document.getElementById('rfq-title').value,
            desc: document.getElementById('rfq-desc').value,
            msg: document.getElementById('rfq-msg').value,
        }));
        document.getElementById('rfq-draft-label').textContent = @json($isFr ? 'Brouillon enregistré ✓' : 'Draft saved ✓');
    });
    const draft = localStorage.getItem('rfqDraft');
    if (draft) {
        try {
            const d = JSON.parse(draft);
            if (d.title) document.getElementById('rfq-title').value = d.title;
            if (d.desc) { document.getElementById('rfq-desc').value = d.desc; }
            if (d.msg) { document.getElementById('rfq-msg').value = d.msg; }
            document.getElementById('rfq-desc').dispatchEvent(new Event('input'));
            document.getElementById('rfq-msg').dispatchEvent(new Event('input'));
        } catch (e) {}
    }

    // "Étape suivante" exits into the REAL flow: the RFQ becomes a real
    // conversation with the artisan (messages.send), like the product-page enquiry.
    document.getElementById('rfq-form').addEventListener('submit', function () {
        const parts = [
            @json($isFr ? 'Demande de devis — ' : 'Quote request — ') + document.getElementById('rfq-title').value,
            document.getElementById('rfq-desc').value,
            document.getElementById('rfq-msg').value,
            (function () {
                // Only include the contact details the buyer actually filled in.
                const v = id => (document.getElementById(id).value || '').trim();
                const company = v('rfq-company');
                const bits = [v('rfq-name') + (company ? ' (' + company + ')' : ''), v('rfq-email'), v('rfq-phone')]
                    .map(s => s.trim()).filter(Boolean);
                return bits.length ? @json($isFr ? 'Contact : ' : 'Contact: ') + bits.join(' — ') : '';
            })(),
        ].filter(Boolean);
        document.getElementById('rfq-body').value = parts.join('\n\n').slice(0, 2000);
        localStorage.removeItem('rfqDraft');
    });
</script>
</body>
</html>
