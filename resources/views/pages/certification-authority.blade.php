@php
    $isFr = $lang === 'fr';
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $key = $jwks['keys'][0] ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr
        ? 'Clé publique et procédure de vérification des certificats émis par Artisan Hub 237.'
        : 'Public key and verification procedure for certificates issued by Artisan Hub 237.' }}">
    <title>{{ $isFr ? 'Autorité de certification' : 'Certification Authority' }} — Artisan Hub 237</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { leaf: '#164C28', deepfc: '#02301B', gold: '#E5A82E' },
            fontFamily: { sans: ['Poppins','system-ui','sans-serif'], serif: ['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        pre.code { background:#0C2A19; color:#DCEFE0; border-radius:10px; padding:14px 16px;
                   font-size:12px; line-height:1.6; overflow-x:auto; }
        pre.code .c { color:#8FB89A; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F5F3EE] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[880px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <nav class="flex items-center gap-2 text-[12.5px] mb-5" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Autorité de certification' : 'Certification Authority' }}</span>
    </nav>

    <header class="mb-7">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Vérification indépendante' : 'Independent verification' }}</span>
        <h1 class="mt-3 font-serif text-[26px] sm:text-[34px] font-bold text-[#02301B] leading-tight">
            {{ $isFr ? "Autorité de certification Artisan Hub 237" : 'Artisan Hub 237 Certification Authority' }}
        </h1>
        <p class="mt-3 text-[13.5px] text-[#3A3A35] leading-relaxed max-w-[640px]">
            {{ $isFr
               ? "Chaque certificat émis par cette plateforme porte une signature numérique Ed25519. La clé publique correspondante est publiée ci-dessous : elle permet à un musée, un assureur, une maison de ventes ou un service des douanes de vérifier un certificat par eux-mêmes, hors ligne, sans nous le demander ni nous croire sur parole."
               : 'Every certificate this platform issues carries an Ed25519 digital signature. The matching public key is published below, so a museum, insurer, auction house or customs office can verify a certificate themselves — offline, without asking us and taking our word for it.' }}
        </p>
    </header>

    {{-- ── The key ── --}}
    <section class="ui-card p-5 sm:p-6">
        <h2 class="ui-card-title">{{ $isFr ? 'Clé publique de signature' : 'Public signing key' }}</h2>

        @if($key)
        <dl class="ui-dl ui-dl--2 mt-3">
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Algorithme' : 'Algorithm' }}</dt>
                <dd class="ui-dd">EdDSA / Ed25519 <span class="text-[#8A857A]">(RFC 8032)</span></dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Identifiant de clé' : 'Key ID' }}</dt>
                <dd class="ui-dd font-mono text-[11.5px] break-all">{{ $kid }}</dd>
            </div>
        </dl>
        <div class="mt-4">
            <p class="ui-dt">{{ $isFr ? 'Clé publique (base64url)' : 'Public key (base64url)' }}</p>
            <p class="mt-1 font-mono text-[11.5px] text-[#55524A] break-all">{{ $key['x'] }}</p>
        </div>
        <a href="{{ url('/.well-known/jwks.json') }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
            <i data-lucide="key-round" class="w-4 h-4"></i>
            {{ $isFr ? 'Obtenir le JWKS' : 'Fetch the JWKS' }}
        </a>
        <p class="ui-hint mt-2">
            {{ $isFr
               ? "Format JOSE standard (RFC 8037) : kty OKP, crv Ed25519. Épinglez cette clé une fois ; elle ne change qu'à l'occasion d'une cérémonie de clé annoncée."
               : 'Standard JOSE form (RFC 8037): kty OKP, crv Ed25519. Pin this key once — it changes only at an announced key ceremony.' }}
        </p>
        @else
        <div class="ui-alert ui-alert-warn mt-3">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <span>{{ $isFr
                ? "Aucune clé de signature n'est installée sur ce serveur. Les certificats restent horodatés et empreintés, mais ne portent pas de signature vérifiable par un tiers."
                : 'No signing key is installed on this server. Certificates remain time-stamped and hashed, but carry no third-party-verifiable signature.' }}</span>
        </div>
        @endif
    </section>

    {{-- ── How to verify ── --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Vérifier un certificat vous-même' : 'Verify a certificate yourself' }}</h2>
        <p class="mt-2 text-[13px] text-[#3A3A35] leading-relaxed">
            {{ $isFr
               ? "Le message signé est reconstruit à partir des champs imprimés sur le certificat, séparés par des retours à la ligne, dans cet ordre exact :"
               : 'The signed message is rebuilt from the fields printed on the certificate, newline-separated, in exactly this order:' }}
        </p>
<pre class="code mt-3">ah237.certificate.v1
&lt;{{ $isFr ? 'type' : 'type' }}&gt;            <span class="c">{{ $isFr ? '# coa, otc, prc ou avc' : '# coa, otc, prc or avc' }}</span>
&lt;{{ $isFr ? 'numéro de certificat' : 'certificate number' }}&gt;
&lt;{{ $isFr ? 'empreinte du contenu' : 'content hash' }}&gt;
&lt;{{ $isFr ? "date d'émission ISO 8601" : 'issue date, ISO 8601' }}&gt;</pre>

        <p class="mt-4 text-[13px] text-[#3A3A35] leading-relaxed">
            {{ $isFr ? 'Puis vérifiez la signature détachée avec la clé publique. Par exemple :' : 'Then check the detached signature against the public key. For example:' }}
        </p>
<pre class="code mt-3"><span class="c"># Python, with PyNaCl</span>
from nacl.signing import VerifyKey
import base64

def b64u(s): return base64.urlsafe_b64decode(s + '=' * (-len(s) % 4))

key = VerifyKey(b64u("{{ $key['x'] ?? '&lt;public key&gt;' }}"))
payload = "\n".join([
    "ah237.certificate.v1", "coa",
    certificate_number, content_hash, issued_at_iso8601,
]).encode()

key.verify(payload, b64u(signature))   <span class="c"># raises if invalid</span></pre>

        <p class="ui-hint mt-3">
            {{ $isFr
               ? "Les empreintes complètes et la date d'émission exacte figurent sur la page de vérification de chaque certificat."
               : 'The full hashes and the exact issue date are shown on each certificate’s verification page.' }}
        </p>
    </section>

    {{-- ── The log ── --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? "Journal infalsifiable des certificats" : 'Tamper-evident certificate log' }}</h2>
        <p class="mt-2 text-[13px] text-[#3A3A35] leading-relaxed">
            {{ $isFr
               ? "Chaque événement du cycle de vie d'un certificat — émission, approbation, révocation, vérification — est ajouté à une chaîne de hachage où chaque entrée engage la précédente. Modifier ou supprimer une entrée passée casse toutes les suivantes."
               : 'Every certificate lifecycle event — issued, approved, revoked, verified — is appended to a hash chain in which each entry commits to the one before it. Altering or removing any past entry breaks every link after it.' }}
        </p>

        <dl class="ui-dl ui-dl--2 mt-4">
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Entrées vérifiées' : 'Entries verified' }}</dt>
                <dd class="ui-dd">{{ number_format($chain['checked']) }}</dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? 'État de la chaîne' : 'Chain state' }}</dt>
                <dd class="ui-dd">
                    @if($chain['ok'])
                        <span class="ui-pill ui-pill-ok">{{ $isFr ? 'Intacte' : 'Intact' }}</span>
                    @else
                        <span class="ui-pill ui-pill-danger">{{ $isFr ? 'Rompue' : 'Broken' }}</span>
                        <span class="text-[#8A857A]">#{{ $chain['broken_at'] }}</span>
                    @endif
                </dd>
            </div>
        </dl>

        @if($head)
        <div class="mt-4">
            <p class="ui-dt">{{ $isFr ? 'Tête de chaîne actuelle' : 'Current chain head' }}</p>
            <p class="mt-1 font-mono text-[10.5px] leading-relaxed text-[#55524A] break-all">{{ $head }}</p>
            <p class="ui-hint mt-1">
                {{ $isFr
                   ? "Notez cette valeur : si l'historique est réécrit plus tard, elle ne correspondra plus."
                   : 'Note this value: if history is rewritten later, it will no longer match.' }}
            </p>
        </div>
        @endif

        {{-- Saying what it is not is what makes the rest believable. A single
             operator holding the whole log could rewrite all of it and recompute
             the chain; what this defeats is silent alteration. --}}
        <div class="ui-alert ui-alert-warn mt-4">
            <i data-lucide="info" class="w-4 h-4"></i>
            <span>{{ $isFr
                ? "Cette chaîne n'est pas une blockchain et ne prétend pas l'être. Elle est tenue par cette plateforme. Un opérateur disposant de l'ensemble du journal pourrait en théorie le réécrire intégralement et recalculer la chaîne ; ce qu'elle empêche, c'est la modification silencieuse d'un historique déjà publié."
                : 'This chain is not a blockchain and does not claim to be. It is kept by this platform. An operator holding the whole log could in principle rewrite all of it and recompute the chain; what it defeats is silent alteration of history already published.' }}</span>
        </div>
    </section>

    <p class="mt-6 text-[12px] text-[#6F6B60] leading-relaxed">
        {{ $isFr
           ? "Artisan Hub 237 est une entreprise privée. Ses certificats attestent d'enregistrements effectués sur la plateforme ; ils ne remplacent ni un titre de propriété délivré par une autorité publique, ni des documents douaniers, ni aucun droit de propriété intellectuelle."
           : 'Artisan Hub 237 is a private company. Its certificates attest to records made on the platform; they replace neither a title issued by a public authority, nor customs documents, nor any intellectual property right.' }}
    </p>
</main>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
