@php
    $isFr = $lang === 'fr';

    $buyerName = $siacUser['name'] ?? 'Jean Dupont';
    $buyerEmail = $siacUser['email'] ?? 'jean.dupont@achatpro.com';
    $buyerPhone = $siacUser['phone'] ?? '+237 6 12 34 56 78';
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

    // [icon crop, fileName, size]
    $designFiles = [
        ['qb-file-1.png', 'plan-chambres.pdf',      '2.4 MB'],
        ['qb-file-2.png', 'image-reference-1.jpg',  '1.8 MB'],
        ['qb-file-3.png', 'cahier-des-charges.pdf', '3.1 MB'],
    ];

    $fieldCls = 'w-full h-[48px] border border-[#E5E7E5] rounded-lg px-4 text-[13.5px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition';
    $labelCls = 'block text-[12.5px] font-semibold text-[#3B382F] mb-2';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Créer une demande de devis — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Create a quote request — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } }
        }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qb-sidebar { display: none; }
        #qb-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; background: #fff; }
        @media (min-width: 1024px) { #qb-sidebar, #qb-sidebar.open { display: block; position: static; width: 264px; overflow-y: visible; } }
    </style>
</head>
<body class="bg-[#F7F8F7] text-[#1B1B18] antialiased">

@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        <!-- Title row -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Créer une demande de devis' : 'Create a quote request' }}</h1>
                <p class="mt-1 text-[13px] text-[#55524A]">{{ $isFr ? 'Remplissez les informations ci-dessous pour demander un devis personnalisé à un artisan ou une entreprise.' : 'Fill in the information below to request a personalised quote from an artisan or business.' }}</p>
            </div>
            <a href="{{ route('dashboard.buyer', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
                {{ $isFr ? 'Quitter sans enregistrer' : 'Leave without saving' }}
            </a>
        </div>

        <!-- Stepper -->
        <div class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-4 overflow-x-auto">
            <div class="flex items-center gap-3 min-w-[860px]">
                @foreach($steps as $stIdx => [$stNum, $stTitle, $stSub])
                <div class="flex items-center gap-3.5 shrink-0">
                    <span class="w-[36px] h-[36px] shrink-0 rounded-full flex items-center justify-center text-[14px] font-bold {{ $stIdx === 0 ? 'bg-[#0E5A2D] text-white' : 'bg-white border border-[#D9DDD9] text-[#55524A]' }}">{{ $stNum }}</span>
                    <span>
                        <span class="block text-[13px] font-bold {{ $stIdx === 0 ? 'text-[#14652F]' : 'text-[#1B1B18]' }}">{{ $stTitle }}</span>
                        <span class="block mt-0.5 text-[11.5px] text-[#6F6B60]">{{ $stSub }}</span>
                    </span>
                </div>
                @if($stIdx < count($steps) - 1)
                <span class="flex-1 min-w-[36px] h-px {{ $stIdx === 0 ? 'bg-[#157A43]' : 'bg-[#E0E4E0]' }}"></span>
                @endif
                @endforeach
            </div>
        </div>

        <div class="mt-5 flex flex-col xl:flex-row gap-5 items-start">

            <!-- Form column -->
            <form id="rfq-form" method="POST" action="{{ route('messages.send') }}" class="flex-1 min-w-0">
                @csrf
                <input type="hidden" name="business_slug" value="{{ $quoteVendor->slug ?? 'art-bois-nature' }}">
                <input type="hidden" name="return_to" value="{{ route('messages.inbox', ['lang' => $lang]) }}">
                <input type="hidden" name="body" id="rfq-body" value="">

                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-6">
                    <h2 class="text-[15.5px] font-bold text-[#14652F]">{{ $isFr ? 'Informations de l\'acheteur' : 'Buyer information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-5 gap-y-5">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-[#DC2626]">*</span></label>
                            <input type="text" id="rfq-name" value="{{ $buyerName }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Email <span class="text-[#DC2626]">*</span></label>
                            <input type="email" id="rfq-email" value="{{ $buyerEmail }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Téléphone' : 'Phone' }} <span class="text-[#DC2626]">*</span></label>
                            <div class="flex items-center gap-3 h-[48px] border border-[#E5E7E5] rounded-lg px-4 focus-within:border-[#14532D]">
                                <img src="{{ asset('images/landing/qb-flag.png') }}" alt="" class="w-[24px] h-[16px] shrink-0 rounded-sm object-cover">
                                <input type="text" id="rfq-phone" value="{{ $buyerPhone }}" class="flex-1 min-w-0 text-[13.5px] focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Entreprise' : 'Company' }}</label>
                            <input type="text" id="rfq-company" value="Achat Pro SARL" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Fonction' : 'Role' }}</label>
                            <input type="text" value="{{ $isFr ? 'Responsable des achats' : 'Purchasing manager' }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Pays' : 'Country' }} <span class="text-[#DC2626]">*</span></label>
                            <div class="relative flex items-center gap-3 h-[48px] border border-[#E5E7E5] rounded-lg px-4">
                                <img src="{{ asset('images/landing/qb-flag.png') }}" alt="" class="w-[24px] h-[16px] shrink-0 rounded-sm object-cover">
                                <select class="flex-1 min-w-0 bg-transparent text-[13.5px] appearance-none cursor-pointer focus:outline-none">
                                    <option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option>
                                    <option>France</option>
                                    <option>{{ $isFr ? 'Autre' : 'Other' }}</option>
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 text-[#8A857A] pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-8 text-[15.5px] font-bold text-[#14652F]">{{ $isFr ? 'Informations générales' : 'General information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-5 gap-y-5">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Référence de la demande' : 'Request reference' }} <span class="text-[#DC2626]">*</span> <span class="font-normal text-[#8A857A]">({{ $isFr ? 'Auto-générée' : 'Auto-generated' }})</span></label>
                            <input type="text" value="RFQ-2024-000189" readonly class="w-full h-[48px] bg-[#F7F8F7] border border-[#EDEEED] rounded-lg px-4 text-[13.5px] text-[#3B382F] focus:outline-none">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Titre de la demande' : 'Request title' }} <span class="text-[#DC2626]">*</span></label>
                            <input type="text" id="rfq-title" value="{{ $isFr ? 'Mobilier en bois massif pour hôtel' : 'Solid wood furniture for a hotel' }}" class="{{ $fieldCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Date souhaitée de réponse' : 'Desired response date' }} <span class="text-[#DC2626]">*</span></label>
                            <div class="flex items-center gap-3 h-[48px] border border-[#E5E7E5] rounded-lg px-4 focus-within:border-[#14532D]">
                                <i data-lucide="calendar" class="w-[17px] h-[17px] shrink-0 text-[#55524A]" style="stroke-width:1.7"></i>
                                <input type="text" value="{{ $isFr ? '25 Mai 2024' : '25 May 2024' }}" class="flex-1 min-w-0 text-[13.5px] focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="{{ $labelCls }}">{{ $isFr ? 'Description détaillée de votre besoin' : 'Detailed description of your need' }} <span class="text-[#DC2626]">*</span></label>
                        <p class="-mt-1 mb-2 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Décrivez précisément votre projet, les spécifications, les matériaux souhaités, les finitions, etc.' : 'Describe your project precisely: specifications, desired materials, finishes, etc.' }}</p>
                        <div class="relative">
                            <textarea id="rfq-desc" rows="4" maxlength="2000" class="w-full border border-[#E5E7E5] rounded-lg px-4 py-3 text-[13.5px] text-[#1B1B18] leading-relaxed focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition resize-y">{{ $isFr ? "Nous recherchons des meubles en bois massif de haute qualité pour l'aménagement de 20 chambres d'hôtel. Style moderne avec une touche traditionnelle camerounaise. Finition vernie naturelle." : 'We are looking for high-quality solid wood furniture to fit out 20 hotel rooms. Modern style with a traditional Cameroonian touch. Natural varnished finish.' }}</textarea>
                            <span id="rfq-desc-count" class="absolute bottom-3 right-4 text-[11.5px] text-[#8A857A]">168 / 2000</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="{{ $labelCls }}">{{ $isFr ? 'Joindre des fichiers' : 'Attach files' }} <span class="font-normal text-[#8A857A]">({{ $isFr ? 'optionnel' : 'optional' }})</span></label>
                        <p class="-mt-1 mb-3 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Ajoutez des plans, images de référence, cahier des charges ou tout document utile.' : 'Add plans, reference images, specifications or any useful document.' }}</p>
                        <div class="flex flex-col lg:flex-row gap-4 items-stretch">
                            <div class="flex-1 border-2 border-dashed border-[#CFE0D4] rounded-xl px-5 py-8 flex flex-wrap items-center justify-center gap-4">
                                <span class="flex items-center gap-3 text-[13.5px] text-[#3B382F]">
                                    <i data-lucide="cloud-upload" class="w-6 h-6 text-[#55524A]" style="stroke-width:1.5"></i>
                                    {{ $isFr ? 'Glissez-déposez vos fichiers ici ou' : 'Drag and drop your files here or' }}
                                </span>
                                <label class="cursor-pointer border border-[#9DBFA9] hover:border-[#14652F] rounded-lg px-5 py-2.5 text-[13px] font-semibold text-[#14652F] transition-colors">
                                    {{ $isFr ? 'Choisir des fichiers' : 'Choose files' }}
                                    <input type="file" id="rfq-files" multiple class="hidden">
                                </label>
                            </div>
                            <div id="rfq-file-list" class="lg:w-[380px] shrink-0 space-y-2.5">
                                @foreach($designFiles as [$dfIcon, $dfName, $dfSize])
                                <div class="rfq-file flex items-center gap-3.5">
                                    <div class="flex-1 min-w-0 flex items-center gap-3.5 bg-white border border-[#EFF0EF] rounded-xl shadow-sm px-3.5 py-2.5">
                                        <img src="{{ asset('images/landing/' . $dfIcon) }}" alt="" class="w-[30px] h-[30px] shrink-0" aria-hidden="true">
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-[12.5px] font-bold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ $dfName }}</span>
                                            <span class="block text-[11.5px] text-[#6F6B60]">{{ $dfSize }}</span>
                                        </span>
                                        <button type="button" class="rfq-file-del shrink-0 text-[#3B382F] hover:text-[#DC2626]"><i data-lucide="x" class="w-4 h-4"></i></button>
                                    </div>
                                    <button type="button" onclick="document.getElementById('rfq-files').click()" class="shrink-0 w-[38px] h-[38px] rounded-full bg-white border border-[#EFF0EF] shadow-sm flex items-center justify-center text-[#3B5BDB] hover:border-[#3B5BDB]">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom actions -->
                <div class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" id="rfq-draft" class="inline-flex items-center gap-2.5 border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-5 py-3 text-[13.5px] font-semibold text-[#1B1B18] transition-colors">
                        <i data-lucide="file-text" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                        <span id="rfq-draft-label">{{ $isFr ? 'Enregistrer comme brouillon' : 'Save as draft' }}</span>
                    </button>
                    <button type="submit" class="inline-flex items-center gap-3 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-6 py-3 text-[13.5px] font-semibold text-white transition-colors">
                        {{ $isFr ? 'Étape suivante' : 'Next step' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <!-- Summary rail -->
            <aside class="w-full xl:w-[330px] shrink-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de la demande' : 'Request summary' }}</h2>
                <dl class="mt-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Articles demandés' : 'Requested items' }}</dt>
                        <dd class="text-[12.5px] text-[#55524A]">0 article{{ $isFr ? '' : 's' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Quantité totale' : 'Total quantity' }}</dt>
                        <dd class="text-[12.5px] text-[#55524A]">0</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Date de réponse souhaitée' : 'Desired response date' }}</dt>
                        <dd class="text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? '25 Mai 2024' : '25 May 2024' }}</dd>
                    </div>
                </dl>

                <p class="mt-6 text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</p>
                <div class="mt-2.5 border border-[#EDEEED] rounded-xl px-4 py-3.5">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ asset('images/landing/qb-artbois.png') }}" alt="" class="w-[46px] h-[46px] shrink-0 rounded-lg object-cover">
                        <div class="min-w-0">
                            <p class="text-[13.5px] font-bold text-[#1B1B18]">Art Bois Nature</p>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Yaoundé, Centre' : 'Yaounde, Centre' }}</p>
                            <p class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                                {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end">
                        <a href="{{ $vendorUrl }}" class="inline-flex items-center gap-2 text-[12.5px] font-bold text-[#14652F] hover:text-[#14532D]">
                            {{ $isFr ? 'Voir le profil' : 'View the profile' }}
                            <i data-lucide="square-arrow-out-up-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <div class="mt-5 border-t border-[#F0F1F0] pt-5">
                    <p class="text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Message pour l\'artisan' : 'Message for the artisan' }} <span class="font-normal text-[#8A857A]">({{ $isFr ? 'optionnel' : 'optional' }})</span></p>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Ajouter un message personnalisé à votre demande.' : 'Add a personalised message to your request.' }}</p>
                    <div class="relative mt-2.5">
                        <textarea id="rfq-msg" rows="5" maxlength="500" class="w-full border border-[#E5E7E5] rounded-lg px-4 py-3 text-[13px] text-[#1B1B18] leading-relaxed focus:outline-none focus:border-[#14532D] resize-y">{{ $isFr ? "Nous serions ravis de collaborer avec vous sur ce projet. N'hésitez pas à nous contacter pour toute question." : 'We would be delighted to work with you on this project. Feel free to contact us with any questions.' }}</textarea>
                        <span id="rfq-msg-count" class="absolute bottom-3 right-4 text-[11.5px] text-[#8A857A]">92 / 500</span>
                    </div>
                </div>

                <div class="mt-5 bg-[#E9F3EC] rounded-xl px-4 py-4">
                    <p class="flex items-center gap-2.5 text-[13px] font-bold text-[#14652F]">
                        <i data-lucide="lightbulb" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Conseil' : 'Tip' }}
                    </p>
                    <p class="mt-2 text-[12px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'Plus votre demande est détaillée, plus les propositions reçues seront précises et adaptées à vos besoins.' : 'The more detailed your request, the more precise and tailored the proposals you receive will be.' }}</p>
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
        Array.from(this.files).forEach(f => {
            const row = list.querySelector('.rfq-file')?.cloneNode(true);
            if (!row) return;
            row.querySelector('.font-bold').textContent = f.name;
            row.querySelector('.text-\\[11\\.5px\\]').textContent = (f.size / 1048576).toFixed(1) + ' MB';
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
            @json($isFr ? 'Contact : ' : 'Contact: ') + document.getElementById('rfq-name').value
                + ' (' + document.getElementById('rfq-company').value + ') — '
                + document.getElementById('rfq-email').value + ' — ' + document.getElementById('rfq-phone').value,
        ].filter(Boolean);
        document.getElementById('rfq-body').value = parts.join('\n\n').slice(0, 2000);
        localStorage.removeItem('rfqDraft');
    });
</script>
</body>
</html>
