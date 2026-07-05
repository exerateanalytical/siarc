@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $siacUser = session('siac_user');
    $bizName = $isFr ? $business->name_fr : ($business->name_en ?? $business->name_fr);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Contacter' : 'Contact' }} {{ $bizName }} — SIARC</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config={theme:{extend:{colors:{leaf:'#164C28',gold:'#C9942E'},fontFamily:{sans:['Poppins','system-ui','sans-serif'],serif:['"Playfair Display"','Georgia','serif']}}}}</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FEFDFC] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<div class="max-w-[680px] mx-auto px-4 sm:px-6 pt-8 pb-16">
    <nav class="flex items-center gap-2 text-[13px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" class="text-[#166534] hover:underline">{{ $bizName }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
        <span class="text-[#6F6B60]">{{ $isFr ? 'Nouveau message' : 'New message' }}</span>
    </nav>

    <h1 class="font-serif text-[28px] sm:text-[32px] font-bold text-[#1D1B16] leading-tight">{{ $isFr ? 'Contacter l\'artisan' : 'Contact the artisan' }}</h1>
    <p class="mt-1.5 text-[14px] text-[#55524A]">{{ $isFr ? 'Votre message à' : 'Your message to' }} <span class="font-semibold text-[#1D1B16]">{{ $bizName }}</span></p>
    <div class="mt-2.5 h-[3.5px] w-[100px] bg-gradient-to-r from-[#D9991F] via-[#E9C989] to-transparent rounded-full mb-6"></div>

    @if($errors->any())
    <div class="mb-5 flex items-start gap-2.5 bg-[#FDECEC] border border-[#F3C7C7] rounded-xl px-4 py-3 text-[13px] text-[#B4231E]">
        <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('messages.send') }}" class="bg-white border border-[#ECE9E2] rounded-2xl p-6 space-y-4">
        @csrf
        <input type="hidden" name="lang" value="{{ $lang }}">
        <input type="hidden" name="business_slug" value="{{ $business->slug }}">
        @if($product)<input type="hidden" name="product_slug" value="{{ $product->slug }}">@endif
        <input type="hidden" name="return_to" value="{{ route('messages.inbox') }}">

        @if($product)
        <div class="flex items-center gap-2 bg-[#F6F7F6] border border-[#EAE7DE] rounded-lg px-3.5 py-2.5 text-[12.5px] text-[#3B382F]">
            <i data-lucide="package" class="w-4 h-4 text-[#157A43]"></i>
            {{ $isFr ? 'À propos du produit :' : 'About the product:' }} <span class="font-semibold">{{ $product->name_fr }}</span>
        </div>
        @endif

        <div>
            <label for="body" class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Votre message' : 'Your message' }} *</label>
            <textarea id="body" name="body" rows="6" required maxlength="2000"
                placeholder="{{ $isFr ? 'Bonjour, je suis intéressé(e) par votre travail...' : 'Hello, I am interested in your work...' }}"
                class="w-full bg-white border border-[#E3E3E1] rounded-lg px-3.5 py-3 text-[13.5px] leading-relaxed focus:outline-none focus:border-gold">{{ old('body') }}</textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="h-[46px] px-6 bg-[#02331C] hover:bg-leaf text-white text-[13.5px] font-semibold rounded-lg inline-flex items-center gap-2 transition-colors">
                <i data-lucide="send" class="w-4 h-4"></i>{{ $isFr ? 'Envoyer le message' : 'Send message' }}
            </button>
            <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" class="text-[13px] font-medium text-[#6F6B60] hover:text-[#1D1B16]">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
        </div>
    </form>
</div>

@include('pages.partials.directory-footer')
<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn'); const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
