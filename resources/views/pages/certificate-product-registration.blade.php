@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $isFr = $lang === 'fr';

    // Consumed by the shared directory header and footer partials.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $biz      = $product->business;
    $artisan  = $biz?->user;
    $name     = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $nameAlt  = $isFr ? $product->name_en : $product->name_fr;
    $desc     = $isFr ? $product->description_fr : ($product->description_en ?: $product->description_fr);
    $bizName  = $isFr ? $biz?->name_fr : ($biz?->name_en ?: $biz?->name_fr);
    $bizAddr  = $isFr ? $biz?->address_fr : ($biz?->address_en ?: $biz?->address_fr);

    $images = $product->images->sortBy('sort_order')->values();
    $cover  = $images->firstWhere('is_cover', true) ?? $images->first();

    $issued   = Carbon::parse($certificate->issued_at);
    $issuedUtc = $issued->clone()->utc();
    $verifyShort = route('product.certificate.verify.short', ['ref' => $certificate->certificate_no]);

    $country  = $isFr ? 'Cameroun' : 'Cameroon';
    $region   = $isFr ? $biz?->region?->name_fr : ($biz?->region?->name_en ?: $biz?->region?->name_fr);
    $city     = $isFr ? $biz?->city?->name_fr : ($biz?->city?->name_en ?: $biz?->city?->name_fr);

    /* ─────────────────────────────────────────────────────────────────────
       Every row on this sheet is built the same way: a label, a value, and a
       filter that drops the pair when the value is null. That is not tidiness,
       it is the whole point of the document. A certificate prints facts, and a
       reader has no way of telling a blank line from a measured zero — an empty
       "Dimensions" row reads as a dimension that was taken and found to be
       nothing. So a value comes out of the database or its row does not exist.

       The artwork this replicates carries a column of security features the
       platform does not have: a holographic seal, UV reactive ink, invisible
       fibres, an AI fingerprint, an invisible watermark, a C2PA reference. Some
       of them are physical print properties that cannot exist on a screen at
       all; the rest were never built. They are gone, and what stands in their
       place is the arithmetic the platform really performs — a SHA-256 over the
       certified facts, a perceptual hash over the photograph, a signature, a
       hash-chained log. Those are checkable by a stranger, which is more than
       any of the omitted rows could have offered.
       ───────────────────────────────────────────────────────────────────── */
    $rows = fn (array $list) => collect($list)->filter(fn ($r) => filled($r[1] ?? null))->values();

    // registered_at is not cast on the model, so it can arrive as a raw string.
    $registeredAtRaw = $product->registered_at ?: $product->created_at;
    $registeredAt    = $registeredAtRaw ? Carbon::parse($registeredAtRaw) : null;

    $productRef = 'AHC-PRD-' . $issued->format('Y') . '-' . str_pad((string) $product->id, 8, '0', STR_PAD_LEFT);
    $status     = $certificate->revoked_at ? 'revoked' : 'active';

    /* The Ed25519 signature and its key id only exist for certificates issued
       after the certification authority was stood up. Older rows carry the
       HMAC signature and nothing else, so the block reports whichever one is
       actually on the record rather than implying the stronger of the two. */
    $caSigned = filled($certificate->ca_signature ?? null);
    $caKid    = $certificate->ca_kid ?? null;

    $identityRows = $rows([
        [$isFr ? 'Numéro de certificat' : 'Certificate number', $certificate->certificate_no, 'gold'],
        [$isFr ? 'Numéro de registre produit (PRN)' : 'Product registry number (PRN)', $prn, 'gold'],
        [$isFr ? 'Registre de propriété (OLN)' : 'Ownership ledger number (OLN)', $oln, 'gold'],
        [$isFr ? 'Identifiant produit' : 'Product ID', $productRef, null],
        [$isFr ? 'UUID du produit' : 'Product UUID', $product->uuid, null],
        [$isFr ? 'UUID du certificat' : 'Certificate UUID', $certificate->uuid, null],
        [$isFr ? 'Date d\'émission (UTC)' : 'Issue date (UTC)', $issuedUtc->format('Y-m-d'), null],
        [$isFr ? 'Heure d\'émission (UTC)' : 'Issue time (UTC)', $issuedUtc->format('H:i:s'), null],
        [$isFr ? 'Version du certificat' : 'Certificate version', number_format((float) $certificate->version, 1), null],
    ]);

    $identityTail = $rows([
        [$isFr ? 'URL de vérification' : 'Verification URL', $verifyShort, null],
        [$isFr ? 'Empreinte du certificat (SHA-256)' : 'Certificate hash (SHA-256)', $certificate->content_hash, null],
    ]);

    /* ── Code 39, encoded for real ────────────────────────────────────────
       The artwork puts a barcode under the QR panel. Drawing decorative bars
       would have been quicker and would have been a lie a scanner could catch:
       a machine-readable mark that reads as noise is worse than none. Code 39
       carries the registry number exactly, needs no check digit, and every
       character of a PRN is inside its alphabet. */
    $c39 = [
        '0'=>'nnnwwnwnn','1'=>'wnnwnnnnw','2'=>'nnwwnnnnw','3'=>'wnwwnnnnn','4'=>'nnnwwnnnw',
        '5'=>'wnnwwnnnn','6'=>'nnwwwnnnn','7'=>'nnnwnnwnw','8'=>'wnnwnnwnn','9'=>'nnwwnnwnn',
        'A'=>'wnnnnwnnw','B'=>'nnwnnwnnw','C'=>'wnwnnwnnn','D'=>'nnnnwwnnw','E'=>'wnnnwwnnn',
        'F'=>'nnwnwwnnn','G'=>'nnnnnwwnw','H'=>'wnnnnwwnn','I'=>'nnwnnwwnn','J'=>'nnnnwwwnn',
        'K'=>'wnnnnnnww','L'=>'nnwnnnnww','M'=>'wnwnnnnwn','N'=>'nnnnwnnww','O'=>'wnnnwnnwn',
        'P'=>'nnwnwnnwn','Q'=>'nnnnnnwww','R'=>'wnnnnnwwn','S'=>'nnwnnnwwn','T'=>'nnnnwnwwn',
        'U'=>'wwnnnnnnw','V'=>'nwwnnnnnw','W'=>'wwwnnnnnn','X'=>'nwnnwnnnw','Y'=>'wwnnwnnnn',
        'Z'=>'nwwnwnnnn','-'=>'nwnnnnwnw','.'=>'wwnnnnwnn',' '=>'nwwnnnwnn','$'=>'nwnwnwnnn',
        '/'=>'nwnwnnnwn','+'=>'nwnnnwnwn','%'=>'nnnwnwnwn','*'=>'nwnnwnwnn',
    ];
    $barText = Str::upper($prn);
    $barBars = [];                                  // [x, width] of every dark bar
    $barX = 0; $narrow = 1.0; $wide = 2.6; $gap = 1.0;
    foreach (str_split('*' . $barText . '*') as $ch) {
        $pattern = $c39[$ch] ?? null;
        if (! $pattern) { $barBars = []; break; }    // unencodable → no barcode at all
        foreach (str_split($pattern) as $i => $el) {
            $w = $el === 'w' ? $wide : $narrow;
            if ($i % 2 === 0) { $barBars[] = [$barX, $w]; }
            $barX += $w;
        }
        $barX += $gap;
    }
    $barWidth = $barX;

    /* ── The features column ──────────────────────────────────────────────
       Same slots as the artwork, filled with the measures that were actually
       taken. Each line names something a third party can re-run themselves. */
    $features = collect([
        ['hash',        $isFr ? 'EMPREINTE SHA-256' : 'SHA-256 CONTENT HASH', true],
        ['fingerprint', $isFr ? 'HACHAGE PERCEPTUEL' : 'PERCEPTUAL IMAGE HASH', filled($certificate->image_phash)],
        ['pen-tool',    $caSigned ? ($isFr ? 'SIGNATURE ED25519' : 'ED25519 SIGNATURE') : ($isFr ? 'SIGNATURE HMAC' : 'HMAC SIGNATURE'), $caSigned || filled($certificate->signature)],
        ['key-round',   $isFr ? 'CLÉ PUBLIQUE PUBLIÉE' : 'PUBLISHED PUBLIC KEY', $caSigned],
        ['link-2',      $isFr ? 'JOURNAL CHAÎNÉ' : 'HASH-CHAINED LOG', true],
        ['qr-code',     $isFr ? 'VÉRIFICATION QR' : 'QR VERIFICATION', true],
        ['scan-line',   $isFr ? 'CODE-BARRES CODE 39' : 'CODE 39 BARCODE', $barBars !== []],
        ['lock',        $isFr ? 'CODE DE VÉRIFICATION' : 'VERIFICATION PIN', filled($certificate->verification_pin)],
        ['clock',       $isFr ? 'HORODATAGE ISO 8601' : 'ISO 8601 TIMESTAMPS', true],
        ['badge-check', $isFr ? 'NUMÉRO PERMANENT' : 'PERMANENT REGISTRY NUMBER', true],
    ])->filter(fn ($f) => $f[2])->values();

    /* Sections 2 – 7 */
    $productRows = $rows([
        [$isFr ? 'Nom du produit' : 'Product name', $name],
        [$isFr ? 'Autre langue' : 'Name (other language)', $nameAlt && $nameAlt !== $name ? $nameAlt : null],
        [$isFr ? 'Catégorie' : 'Category', $product->category ? ($isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr)) : null],
        [$isFr ? 'Type' : 'Product type', $product->product_type],
        ['SKU', $product->sku],
        [$isFr ? 'Marque' : 'Brand', $product->brand],
        [$isFr ? 'Nom local' : 'Local name', $product->local_names],
        [$isFr ? 'Pays d\'origine' : 'Country of origin', $biz ? $country . ' (CM)' : null],
        [$isFr ? 'Année d\'enregistrement' : 'Year registered', $registeredAt?->format('Y')],
        [$isFr ? 'Certificat d\'authenticité' : 'Certificate of authenticity', $certificate->certificate_no],
    ]);

    $creatorRows = $rows([
        [$isFr ? 'Numéro artisan (GAN)' : 'Artisan number (GAN)', $biz ? \App\Support\ProvenanceRegistry::ganFor($biz) : null],
        [$isFr ? 'Nom de l\'artisan' : 'Artisan name', $artisan?->name],
        [$isFr ? 'Atelier' : 'Workshop', $bizName],
        [$isFr ? 'Pays' : 'Country', $biz ? $country : null],
        [$isFr ? 'Région' : 'Region', $region],
        [$isFr ? 'Ville' : 'City', $city],
        [$isFr ? 'Établi en' : 'Established', $biz?->year_established],
        [$isFr ? 'Profil public' : 'Public profile', $biz?->slug ? Str::after(route('businesses.show', ['slug' => $biz->slug]), '://') : null],
    ]);

    /* The tier is the workshop's own standing on the platform, never asserted
       independently of it, and never dressed up as a level it did not reach. */
    $tierLabel = [
        'certified' => $isFr ? 'Atelier certifié' : 'Certified workshop',
        'verified'  => $isFr ? 'Atelier vérifié' : 'Verified workshop',
    ][$biz?->verification_tier] ?? ($isFr ? 'Enregistré, non vérifié' : 'Registered, not verified');
    $tierOk = in_array($biz?->verification_tier, ['verified', 'certified'], true);

    $workshopRows = $rows([
        [$isFr ? 'Identifiant atelier' : 'Workshop ID', $biz?->uuid ? Str::upper(Str::before($biz->uuid, '-')) : null],
        [$isFr ? 'Nom de l\'atelier' : 'Workshop name', $bizName],
        [$isFr ? 'Adresse' : 'Address', $bizAddr],
        [$isFr ? 'Coordonnées GPS' : 'GPS coordinates', $biz?->gps_lat && $biz?->gps_lng ? number_format((float) $biz->gps_lat, 4) . '° N, ' . number_format((float) $biz->gps_lng, 4) . '° E' : null],
        [$isFr ? 'Type de vendeur' : 'Vendor type', $biz?->vendor_type],
        [$isFr ? 'Vérification' : 'Verification', $tierLabel],
        [$isFr ? 'Référence de certification' : 'Certification reference', $biz?->certificate_no],
    ]);

    $descRows = $rows([
        [$isFr ? 'Description officielle' : 'Official description', $desc],
        [$isFr ? 'Notes de qualité' : 'Quality notes', $product->quality_notes],
        [$isFr ? 'Conservation' : 'Storage conditions', $product->storage_conditions],
        [$isFr ? 'Conditionnement' : 'Packaging', $product->packaging_type],
    ]);

    $specRows = $product->attributes
        ->filter(fn ($a) => $a->template && filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->map(fn ($a) => [
            $isFr ? $a->template->name_fr : ($a->template->name_en ?: $a->template->name_fr),
            trim(($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)) . ' ' . ($a->unit ?? '')),
        ])->values();

    $timelineRows = $rows([
        [$isFr ? 'Date d\'enregistrement' : 'Registration date', $registeredAt?->format('Y-m-d')],
        [$isFr ? 'Dernière mise à jour' : 'Record last updated', $product->updated_at?->format('Y-m-d')],
        [$isFr ? 'Certificat émis' : 'Certificate issued', $issuedUtc->format('Y-m-d H:i') . ' UTC'],
        [$isFr ? 'Dernière vérification' : 'Last verified', $certificate->last_verified_at ? Carbon::parse($certificate->last_verified_at)->utc()->format('Y-m-d H:i') . ' UTC' : null],
        [$isFr ? 'Nombre de vérifications' : 'Verification count', (string) ($certificate->verification_count ?? 0)],
    ]);

    /* Section 9. No AI fingerprint — there is no model. No watermark row unless
       a watermark reference was really stored. No provenance-manifest reference
       at all: nothing in this codebase writes one. */
    $digitalRows = $rows([
        [$isFr ? 'Empreinte du contenu (SHA-256)' : 'Content hash (SHA-256)', $certificate->content_hash],
        [$isFr ? 'Hachage perceptuel (image)' : 'Perceptual image hash', $certificate->image_phash ? Str::upper($certificate->image_phash) : null],
        [$isFr ? 'Hachage DCT' : 'DCT hash', $certificate->fp_dct ? Str::upper($certificate->fp_dct) : null],
        [$isFr ? 'Hachage par blocs' : 'Block hash', $certificate->fp_block ? Str::upper($certificate->fp_block) : null],
        [$isFr ? 'Identifiant d\'empreinte' : 'Fingerprint ID', $certificate->fp_id],
        [$isFr ? 'Signature (HMAC-SHA-256)' : 'Signature (HMAC-SHA-256)', $certificate->signature ? Str::upper(Str::limit($certificate->signature, 40, '')) : null],
        [$isFr ? 'Signature (Ed25519)' : 'Signature (Ed25519)', $caSigned ? Str::limit($certificate->ca_signature, 40, '') : null],
        [$isFr ? 'Identifiant de clé' : 'Key ID', $caKid],
        [$isFr ? 'Code de vérification' : 'Verification PIN', $certificate->verification_pin],
        [$isFr ? 'Calculée le' : 'Computed at', $issuedUtc->format('Y-m-d H:i') . ' UTC'],
    ]);

    $ownershipRows = $rows([
        [$isFr ? 'Détenteur actuel' : 'Current owner', $owner?->legal_name],
        [$isFr ? 'Référence' : 'Owner reference', $owner?->owner_ref],
        [$isFr ? 'Type d\'entité' : 'Entity type', $owner?->entity_type],
        [$isFr ? 'Pays' : 'Country', $owner?->country_code],
        [$isFr ? 'Propriétaire depuis' : 'Owner since', $owner?->owned_from ? Carbon::parse($owner->owned_from)->format('Y-m-d') : null],
        [$isFr ? 'Créateur d\'origine' : 'Original creator', $owner ? ($owner->is_original_creator ? ($isFr ? 'Oui' : 'Yes') : ($isFr ? 'Non' : 'No')) : null],
        [$isFr ? 'Niveau de vérification' : 'Verification level', $owner?->verification_level],
    ]);

    /* Section 12. These are stored fields on the product record, entered by the
       artisan — declared, not audited, and the card says so. Anything the
       artisan left blank simply is not here. */
    $sustainRows = $rows([
        [$isFr ? 'Source d\'énergie' : 'Energy source', $product->energy_source],
        [$isFr ? 'Usage de l\'eau' : 'Water usage', $product->water_usage],
        [$isFr ? 'Empreinte carbone' : 'Carbon footprint', $product->carbon_footprint],
        [$isFr ? 'Gestion des déchets' : 'Waste management', $product->waste_management],
        [$isFr ? 'Certifications' : 'Certifications', $product->environmental_certifications],
    ]);

    $geoRows = $rows([
        [$isFr ? 'Pays d\'origine' : 'Country of origin', $biz ? $country : null],
        [$isFr ? 'Code ISO 3166-1' : 'ISO 3166-1 code', $biz ? 'CM' : null],
        [$isFr ? 'Région' : 'Region', $region],
        [$isFr ? 'Ville' : 'City', $city],
        [$isFr ? 'Coordonnées' : 'Coordinates', $product->gps_lat && $product->gps_lng ? number_format((float) $product->gps_lat, 4) . ', ' . number_format((float) $product->gps_lng, 4) : null],
    ]);

    /* Sections 15 and 20 in the artwork list certificates "to be issued" and a
       column of compliance ticks. A certificate that has not been issued is not
       a fact about this product, and "compliant" against a standard nobody
       audited us on is a claim we cannot make. What is left is the certificates
       that exist and the standards this document demonstrably uses. */
    $verification = $biz ? \App\Support\ArtisanVerification::forBusiness($biz) : null;

    $relatedRows = $rows([
        [$isFr ? 'Certificat d\'authenticité (COA)' : 'Certificate of authenticity (COA)', $certificate->certificate_no],
        [$isFr ? 'Vérification artisan (AVC)' : 'Artisan verification (AVC)', $verification->certificate_no ?? null],
    ]);

    $standards = [
        ['ISO 8601', $isFr ? 'Horodatage' : 'Timestamps'],
        ['ISO 3166-1', $isFr ? 'Code pays' : 'Country code'],
        ['SHA-256 (FIPS 180-4)', $isFr ? 'Empreinte du contenu' : 'Content hash'],
        ['HMAC (RFC 2104)', $isFr ? 'Signature du certificat' : 'Certificate signature'],
    ];
    if ($caSigned) {
        $standards[] = ['Ed25519 (RFC 8032)', $isFr ? 'Signature de l\'autorité' : 'Authority signature'];
        $standards[] = ['JWK (RFC 7517)', $isFr ? 'Clé publique publiée' : 'Published public key'];
    }
    $standards[] = ['ISO/IEC 16388', $isFr ? 'Code-barres Code 39' : 'Code 39 barcode'];
    $standards[] = ['ISO/IEC 18004', $isFr ? 'Code QR' : 'QR code'];

    /* Section 18. Only the keys the register actually holds. A key that is not
       in the map means the platform cannot determine the answer, and an
       undeterminable answer must render as nothing — not as an unticked box,
       because a printed "no" is a claim we are no more entitled to than the
       "yes". */
    $checkLabels = [
        'not_reported_stolen'     => $isFr ? 'Aucun signalement de vol' : 'Not reported stolen',
        'not_under_investigation' => $isFr ? 'Aucune enquête en cours' : 'Not under investigation',
        'ownership_undisputed'    => $isFr ? 'Propriété non contestée' : 'Ownership undisputed',
    ];
    $activeFlags = \App\Support\ProductFlags::active($product);
    $allClear    = collect($flags)->every(fn ($v) => $v === true);

    $auditRows = $rows([
        [$isFr ? 'Produit enregistré' : 'Product registered', $registeredAt ? $registeredAt->clone()->utc()->format('Y-m-d H:i') . ' UTC' : null],
        [$isFr ? 'Certificat émis' : 'Certificate issued', $issuedUtc->format('Y-m-d H:i') . ' UTC'],
        [$isFr ? 'Registre de propriété ouvert' : 'Ownership ledger opened', $owner?->created_at ? Carbon::parse($owner->created_at)->utc()->format('Y-m-d H:i') . ' UTC' : null],
        [$isFr ? 'Dernière vérification' : 'Last verification', $certificate->last_verified_at ? Carbon::parse($certificate->last_verified_at)->utc()->format('Y-m-d H:i') . ' UTC' : null],
        [$isFr ? 'Vérifications enregistrées' : 'Verifications recorded', (string) ($certificate->verification_count ?? 0)],
        [$isFr ? 'Concerns actives' : 'Active concerns', (string) count($activeFlags)],
    ]);

    $imgLabels = $isFr
        ? ['VUE 1', 'VUE 2', 'VUE 3', 'VUE 4', 'VUE 5', 'VUE 6', 'VUE 7']
        : ['VIEW 1', 'VIEW 2', 'VIEW 3', 'VIEW 4', 'VIEW 5', 'VIEW 6', 'VIEW 7'];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Certificat d\'enregistrement produit' : 'Product registration certificate' }} — {{ $name }}">
    <title>{{ $isFr ? 'Certificat d\'enregistrement produit' : 'Product registration certificate' }} — {{ $name }}</title>

    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* ────────────────────────────────────────────────────────────────
           Drawn at the artwork's own 1024×1536 canvas and then scaled as a
           single unit. Every number below is a source pixel measured off
           `certificates/Official product registration certificate.png` and
           written down in docs/PRC-DESIGN-SPEC.md. Scaling rather than
           reflowing is what keeps the proportions: a reader at 380px wide
           sees the same document, smaller, not a different one. The site-wide
           mobile type floor is therefore switched off inside the sheet.
           ──────────────────────────────────────────────────────────────── */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .prc-page {
            width: 1024px;
            transform-origin: top left;
            font-size: 12px;
            line-height: 1.3;
            color: #1D1B16;
            background: #04120A;
            padding: 7px;
        }
        .prc-page, .prc-page * { font-size: revert-layer; }

        /* The frame: a deep green band carrying the kente lattice, with the
           registry wordmark set vertically down the left edge as in the
           artwork. 47px each side, 21px top and bottom — the average of the
           artwork's slightly uneven left and right bands, because a frame that
           is visibly wider on one side reads as a printing fault. */
        .prc-frame {
            position: relative;
            padding: 21px 47px;
            background: radial-gradient(120% 60% at 50% 0%, #0D4325, #041A0E 72%) #062816;
            box-shadow: inset 0 0 0 2px #C9A24B;
        }
        .prc-band { position: absolute; top: 0; bottom: 0; width: 47px; overflow: hidden; }
        .prc-band.l { left: 0; }
        .prc-band.r { right: 0; }
        .prc-vtext {
            position: absolute; left: 50%; top: 50%;
            transform: translate(-50%, -50%) rotate(180deg);
            writing-mode: vertical-rl; white-space: nowrap;
            font-size: 13px; font-weight: 700; letter-spacing: .34em; color: #E8C878;
        }

        .prc-cream {
            background: #FAF5E9; position: relative; padding: 0 13px;
            box-shadow: inset 0 0 0 1px #C9A24B;
        }

        /* Cards. The artwork's cards are cream on cream, edged in a thin gold
           rule with a numbered green tab overhanging the top-left corner. */
        .pc { position: relative; border: 1px solid #C9A24B; border-radius: 8px; background: #FDFAF1; }
        .pc-head {
            position: absolute; top: -12px; left: 10px; height: 24px; padding: 0 12px;
            display: flex; align-items: center; gap: 7px;
            background: linear-gradient(180deg, #10502C, #06301A);
            border: 1px solid #C9A24B; border-radius: 7px; color: #F6E4B0;
            font-size: 11.5px; font-weight: 700; letter-spacing: .045em; text-transform: uppercase;
            white-space: nowrap;
        }
        .pc-head .n { color: #E8C878; font-weight: 800; }
        .pc-body { padding: 19px 11px 7px; }

        /* Label · colon · value, the artwork's three-column row. */
        .pr { display: grid; grid-template-columns: var(--pk, 118px) 7px 1fr; column-gap: 4px; padding: .9px 0; align-items: baseline; }
        .pr dt { font-size: 9.5px; font-weight: 500; color: #3A362D; }
        .pr .cl { font-size: 9.5px; color: #8A8373; }
        .pr dd { font-size: 9.5px; font-weight: 500; color: #1D1B16; word-break: break-word; }
        .mono { font-family: ui-monospace, 'SFMono-Regular', Consolas, monospace; letter-spacing: -.25px; }
        .pr dd.gold-val { color: #8A1F14; font-weight: 600; }

        .tick { display: flex; align-items: center; gap: 5px; font-size: 9px; padding: .8px 0; }
        .tick svg { width: 10px; height: 10px; color: #0C7A3E; flex: none; }

        .prc-foot {
            margin: 0 -13px; height: 58px; position: relative; overflow: hidden;
            background: linear-gradient(180deg, #0B3E23, #041A0E);
            border-top: 2px solid #C9A24B;
            display: grid; grid-template-columns: 400px 1fr; align-items: stretch;
        }
        .prc-strip {
            margin: 0 -13px; background: #030F08; color: #D8C89A;
            padding: 4px 0 5px; text-align: center;
            font-size: 8px; letter-spacing: .05em; line-height: 1.45; text-transform: uppercase;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .coa-fit { overflow: visible; }
            @page { size: A4 portrait; margin: 6mm; }
        }
    </style>
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#EFEADF] text-[#1D1B16] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>
@include('pages.partials.coa-ornaments')

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28] truncate max-w-[180px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Enregistrement' : 'Registration' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.14)] rounded-[4px]">
    <article class="prc-page cert-band-host">
    {{-- The family's classification band. It replaces the artwork's left kente
         strip rather than sitting beside it: the two occupy the same 47px
         gutter, and a sheet carrying both would have a vertical wordmark and a
         vertical document name a few pixels apart, which reads as a mistake.
         The right-hand strip stays, so the frame is still a frame. --}}
    @include('pages.partials.certificate-band', ['code' => 'PRC'])
    <div class="prc-frame">

        <div class="prc-band r">
            <svg width="47" height="1522" aria-hidden="true"><rect width="47" height="1522" fill="url(#coaKenteDark)" opacity=".55"/></svg>
        </div>

    <div class="prc-cream">

        {{-- ══ Header (measured y 28–258) ══ --}}
        <div style="position:relative;height:247px;">

            {{-- Wax seal, artwork x 65–180 / y 95–195 --}}
            <svg style="position:absolute;left:0;top:66px;" width="112" height="112" viewBox="0 0 194 194" aria-hidden="true">
                <use href="#coaSealTeeth"/>
                <circle cx="97" cy="97" r="90" fill="url(#coaSealFace)"/>
                <circle cx="97" cy="97" r="72" fill="none" stroke="#FBEEC6" stroke-width="2.5" opacity=".7"/>
                <text font-family="Poppins,sans-serif" font-size="15" font-weight="800" fill="#4A360B" letter-spacing="3">
                    <textPath href="#coaSealTop" startOffset="50%" text-anchor="middle">ARTISANHUB237</textPath>
                </text>
                <text font-family="Poppins,sans-serif" font-size="12" font-weight="700" fill="#4A360B" letter-spacing="2">
                    <textPath href="#coaSealBot" startOffset="50%" text-anchor="middle">{{ $isFr ? 'REGISTRE' : 'REGISTRY' }}</textPath>
                </text>
                <image href="{{ brand_asset('mark') }}" x="58" y="58" width="78" height="78" preserveAspectRatio="xMidYMid meet"/>
            </svg>

            {{-- Cameroon silhouette, artwork's right-hand watermark --}}
            <svg style="position:absolute;right:8px;top:34px;opacity:.16;" width="128" height="146" viewBox="0 0 60 68" aria-hidden="true">
                <use href="#coaCameroon" fill="#8A6A22"/>
            </svg>

            <div style="text-align:center;padding-top:12px;position:relative;">
                <img src="{{ brand_asset('full') }}" alt="Artisan Hub 237"
                     style="height:74px;width:auto;max-width:100%;margin:0 auto;display:block;">
                <p style="margin-top:-2px;font-size:11px;font-weight:600;letter-spacing:.215em;color:#5C574B;">
                    {{ $isFr ? 'AU SERVICE DE L\'ARTISANAT AUTHENTIQUE' : 'CONNECTING AUTHENTIC CRAFTSMANSHIP' }}
                </p>

                {{-- Measured: cap-height 32px across x 218–800 (582px). Playfair
                     at that cap-height runs far wider, so the line is compressed
                     to the artwork's width rather than shrunk, which would have
                     taken a third of the cap-height with it. --}}
                <h1 style="margin-top:10px;font-family:'Playfair Display',Georgia,serif;font-size:45px;line-height:1;
                           font-weight:800;color:#0A3A22;letter-spacing:.004em;white-space:nowrap;
                           display:inline-block;transform:scaleX(.70);transform-origin:center;">
                    {{ $isFr ? "CERTIFICAT D'ENREGISTREMENT PRODUIT" : 'PRODUCT REGISTRATION CERTIFICATE' }}
                </h1>

                <p style="margin-top:4px;">
                    <span style="display:inline-block;height:22px;line-height:22px;padding:0 26px;border-radius:3px;
                                 background:linear-gradient(180deg,#0F4E2C,#052B16);border:1px solid #C9A24B;
                                 color:#F3DFA8;font-size:10.5px;font-weight:700;letter-spacing:.05em;">
                        {{ $isFr
                           ? 'PRC — ENREGISTREMENT OFFICIEL AU REGISTRE PRODUIT ARTISANHUB237'
                           : 'PRC — OFFICIAL REGISTRATION IN THE ARTISANHUB237 PRODUCT REGISTRY' }}
                    </span>
                </p>

                <p style="margin:8px auto 0;max-width:500px;font-size:11px;line-height:1.45;color:#3F3C34;">
                    {{ $isFr
                       ? 'Ce certificat confirme que le produit artisanal décrit ci-dessous a été enregistré au registre produit ArtisanHub237 et qu\'une identité numérique permanente lui a été attribuée.'
                       : 'This certificate confirms that the handcrafted product described below has been registered in the ArtisanHub237 Product Registry and assigned a permanent digital identity.' }}
                </p>
            </div>
        </div>

        {{-- ══ Row A — §1 certificate identity (x 78–822) + features (x 831–968) ══ --}}
        <div style="display:grid;grid-template-columns:744px 137px;gap:9px;align-items:start;">

            <section class="pc">
                <div class="pc-head"><span class="n">1.</span>{{ $isFr ? 'Identité du certificat' : 'Certificate identity' }}</div>
                <div class="pc-body" style="display:grid;grid-template-columns:1fr 220px;gap:14px;">
                    <div>
                        <dl style="--pk:172px;line-height:1.25;">
                            @foreach($identityRows as [$k, $v, $tone])
                            <div class="pr">
                                <dt>{{ $k }}</dt><span class="cl">:</span>
                                <dd class="mono {{ $tone === 'gold' ? 'gold-val' : '' }}">{{ $v }}</dd>
                            </div>
                            @endforeach
                            <div class="pr">
                                <dt>{{ $isFr ? 'Statut du certificat' : 'Certificate status' }}</dt><span class="cl">:</span>
                                <dd style="display:flex;align-items:center;gap:5px;">
                                    <i data-lucide="{{ $status === 'active' ? 'check-circle-2' : 'shield-off' }}"
                                       style="width:12px;height:12px;color:{{ $status === 'active' ? '#0C7A3E' : '#A11A12' }};flex:none;"></i>
                                    <b style="color:{{ $status === 'active' ? '#0C7A3E' : '#A11A12' }};letter-spacing:.04em;">
                                        {{ $status === 'active' ? ($isFr ? 'ACTIF' : 'ACTIVE') : ($isFr ? 'RÉVOQUÉ' : 'REVOKED') }}
                                    </b>
                                </dd>
                            </div>
                            @foreach($identityTail as [$k, $v])
                            <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd class="mono" style="word-break:break-all;">{{ $v }}</dd></div>
                            @endforeach
                        </dl>

                        {{-- The artwork signs this block with a handwritten flourish.
                             Nobody signs these by hand — they are minted by a program
                             — so what stands here is the signature that genuinely
                             exists, named for the algorithm that produced it. --}}
                        <div style="margin-top:8px;display:grid;grid-template-columns:172px 7px 1fr;column-gap:4px;">
                            <p style="font-size:10px;color:#3A362D;">{{ $isFr ? 'Signature numérique' : 'Digital signature' }}</p>
                            <span class="cl" style="font-size:10px;color:#8A8373;">:</span>
                            <div>
                                @if($caSigned)
                                <p class="mono" style="font-size:9.5px;word-break:break-all;color:#1D1B16;">{{ $certificate->ca_signature }}</p>
                                <p style="margin-top:3px;font-size:9px;color:#5A554A;">
                                    Ed25519 · kid {{ $caKid }} ·
                                    <a href="{{ route('ca.jwks') }}" style="color:#2A6B3E;">/.well-known/jwks.json</a>
                                </p>
                                @elseif($certificate->signature)
                                <p class="mono" style="font-size:9.5px;word-break:break-all;color:#1D1B16;">{{ Str::upper($certificate->signature) }}</p>
                                <p style="margin-top:3px;font-size:9px;color:#5A554A;">
                                    {{ $isFr
                                       ? 'HMAC-SHA-256 calculé à l\'émission et recalculé à chaque vérification.'
                                       : 'HMAC-SHA-256, computed at issue and recomputed on every verification.' }}
                                </p>
                                @endif
                                <p style="margin-top:6px;font-size:9.5px;color:#3F3C34;">
                                    {{ $isFr ? 'Autorité de certification ArtisanHub237' : 'ArtisanHub237 Certification Authority' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        {{-- QR panel, artwork x 645–805 / y 285–440 --}}
                        <div style="border:1px solid #C9A24B;border-radius:7px;padding:7px;text-align:center;background:#FFFDF6;">
                            <p style="font-size:10px;font-weight:700;letter-spacing:.05em;color:#0A3A22;">
                                {{ $isFr ? 'CODE QR — VÉRIFIER' : 'QR CODE — VERIFY' }}
                            </p>
                            <div id="prc-qr" style="margin:5px auto 0;width:112px;height:112px;"></div>
                            <p style="margin-top:6px;font-size:10px;font-weight:700;letter-spacing:.05em;color:#0A3A22;">
                                {{ $isFr ? 'SCANNER POUR VÉRIFIER' : 'SCAN TO VERIFY' }}
                            </p>
                        </div>

                        @if($barBars !== [])
                        {{-- A genuine Code 39 encoding of the registry number. --}}
                        <div style="margin-top:9px;border:1px solid #C9A24B;border-radius:7px;padding:6px 8px;text-align:center;background:#FFFDF6;">
                            <p style="font-size:9.5px;font-weight:700;letter-spacing:.08em;color:#0A3A22;">{{ $isFr ? 'CODE-BARRES' : 'BARCODE' }}</p>
                            <svg style="margin:5px auto 0;display:block;" width="196" height="32"
                                 viewBox="0 0 {{ round($barWidth, 2) }} 32" preserveAspectRatio="none" role="img"
                                 aria-label="{{ $barText }}">
                                @foreach($barBars as [$bx, $bw])
                                <rect x="{{ round($bx, 3) }}" y="0" width="{{ round($bw, 3) }}" height="32" fill="#111"/>
                                @endforeach
                            </svg>
                            <p class="mono" style="margin-top:4px;font-size:9px;letter-spacing:.04em;">{{ $barText }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="pc" style="min-height:290px;">
                <div class="pc-head" style="font-size:10px;left:8px;padding:0 8px;">{{ $isFr ? 'Éléments vérifiables' : 'Verifiable features' }}</div>
                <div class="pc-body" style="padding:19px 8px 8px;">
                    @foreach($features as [$fIcon, $fLabel, $_])
                    <div style="display:flex;align-items:center;gap:6px;padding:2.2px 0;">
                        <span style="width:19px;height:19px;flex:none;border-radius:50%;background:linear-gradient(135deg,#F0D79A,#C9942E);
                                     display:flex;align-items:center;justify-content:center;color:#3A2A08;">
                            <i data-lucide="{{ $fIcon }}" style="width:11px;height:11px;"></i>
                        </span>
                        <span style="font-size:8.5px;font-weight:600;letter-spacing:.03em;color:#3A362D;line-height:1.2;">{{ $fLabel }}</span>
                    </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ══ Row B — §2 product · §3 creator · §4 workshop (y 560–832) ══ --}}
        <div style="display:grid;grid-template-columns:306px 283px 283px;gap:9px;margin-top:19px;align-items:start;">

            <section class="pc">
                <div class="pc-head"><span class="n">2.</span>{{ $isFr ? 'Identité du produit' : 'Product identity' }}</div>
                <div class="pc-body" style="display:grid;grid-template-columns:96px 1fr;gap:9px;">
                    <div style="border:1px solid #E0D3AE;border-radius:5px;height:216px;display:flex;align-items:center;justify-content:center;padding:4px;background:#fff;">
                        @if($cover)
                        <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}" style="max-width:100%;max-height:100%;object-fit:contain;">
                        @else
                        <i data-lucide="image-off" style="width:22px;height:22px;color:#B4AE9F;"></i>
                        @endif
                    </div>
                    <dl style="--pk:88px;">
                        @foreach($productRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head"><span class="n">3.</span>{{ $isFr ? 'Créateur' : 'Creator information' }}</div>
                <div class="pc-body">
                    <dl style="--pk:104px;">
                        @foreach($creatorRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ $v }}</dd></div>
                        @endforeach
                        <div class="pr">
                            <dt>{{ $isFr ? 'Standing' : 'Standing' }}</dt><span class="cl">:</span>
                            <dd style="display:flex;align-items:center;gap:5px;">
                                <i data-lucide="{{ $tierOk ? 'check-circle-2' : 'circle-dashed' }}"
                                   style="width:12px;height:12px;color:{{ $tierOk ? '#0C7A3E' : '#8A857A' }};flex:none;"></i>{{ $tierLabel }}
                            </dd>
                        </div>
                    </dl>
                    {{-- The artwork ends this block with the artisan's handwritten
                         signature. The platform holds no specimen signature and
                         never asked for one, so drawing anything here would be
                         forging the maker's hand. --}}
                    <p style="margin-top:9px;font-size:9px;line-height:1.4;color:#6B6659;">
                        {{ $isFr
                           ? 'Créateur déclaré par l\'atelier au moment de l\'enregistrement et lié au numéro artisan ci-dessus.'
                           : 'Creator declared by the workshop at registration and bound to the artisan number above.' }}
                    </p>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head"><span class="n">4.</span>{{ $isFr ? 'Atelier' : 'Workshop information' }}</div>
                <div class="pc-body">
                    <dl style="--pk:112px;">
                        @foreach($workshopRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @if($biz?->cover_image)
                    <img src="{{ asset('storage/' . $biz->cover_image) }}" alt="{{ $bizName }}"
                         style="margin-top:9px;width:100%;height:92px;object-fit:cover;border:1px solid #E0D3AE;border-radius:5px;">
                    @endif
                </div>
            </section>
        </div>

        {{-- ══ Row C — §5 description · §6 specifications · §7 timeline (y 833–985) ══ --}}
        <div style="display:grid;grid-template-columns:306px 283px 283px;gap:9px;margin-top:19px;align-items:start;">

            <section class="pc">
                <div class="pc-head"><span class="n">5.</span>{{ $isFr ? 'Description du produit' : 'Product description' }}</div>
                <div class="pc-body">
                    @if($descRows->isEmpty())
                    <p style="font-size:10px;color:#8A857A;">{{ $isFr ? 'Aucune description enregistrée.' : 'No description on record.' }}</p>
                    @else
                    <dl style="--pk:96px;">
                        @foreach($descRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ Str::limit($v, 240) }}</dd></div>
                        @endforeach
                    </dl>
                    @endif
                </div>
            </section>

            <section class="pc">
                <div class="pc-head"><span class="n">6.</span>{{ $isFr ? 'Caractéristiques' : 'Physical specifications' }}</div>
                <div class="pc-body">
                    @if($specRows->isEmpty())
                    {{-- A blank spec table on a certificate reads as "measured and
                         found to be nothing". Saying nothing was recorded is the
                         only honest form of an empty block. --}}
                    <p style="font-size:10px;color:#8A857A;">
                        {{ $isFr ? 'Aucune caractéristique n\'a été enregistrée pour ce produit.' : 'No physical specifications were recorded for this product.' }}
                    </p>
                    @else
                    <dl style="--pk:104px;">
                        @foreach($specRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @endif
                </div>
            </section>

            <section class="pc">
                <div class="pc-head"><span class="n">7.</span>{{ $isFr ? 'Chronologie' : 'Registration timeline' }}</div>
                <div class="pc-body">
                    <dl style="--pk:112px;">
                        @foreach($timelineRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd class="mono">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>
        </div>

        {{-- ══ Row D — §8 gallery · §9 digital identity · §10/§11 right column ══ --}}
        <div style="display:grid;grid-template-columns:482px 220px 170px;gap:9px;margin-top:19px;align-items:start;">

            <section class="pc" style="min-height:150px;">
                <div class="pc-head"><span class="n">8.</span>{{ $isFr ? 'Galerie d\'images' : 'Images gallery' }}</div>
                <div class="pc-body">
                    @if($images->isEmpty())
                    <p style="font-size:10px;color:#8A857A;">{{ $isFr ? 'Aucune photographie enregistrée.' : 'No photographs on record.' }}</p>
                    @else
                    <div style="display:grid;grid-template-columns:repeat({{ min(7, $images->count()) }},1fr);gap:5px;">
                        @foreach($images->take(7) as $i => $im)
                        <div>
                            <p style="text-align:center;font-size:8px;font-weight:700;letter-spacing:.05em;color:#3A362D;">{{ $imgLabels[$i] ?? '' }}</p>
                            <div style="margin-top:3px;border:1px solid #E0D3AE;border-radius:4px;height:74px;display:flex;align-items:center;justify-content:center;background:#fff;overflow:hidden;">
                                <img src="{{ asset('storage/' . $im->file_path) }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </section>

            <section class="pc">
                <div class="pc-head"><span class="n">9.</span>{{ $isFr ? 'Identité numérique' : 'Digital identity' }}</div>
                <div class="pc-body">
                    <dl style="--pk:76px;font-size:8px;">
                        @foreach($digitalRows as [$k, $v])
                        <div class="pr"><dt>{{ $k }}</dt><span class="cl">:</span><dd class="mono" style="word-break:break-all;font-size:8px;">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <div style="display:flex;flex-direction:column;gap:19px;">
                <section class="pc">
                    <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">10.</span>{{ $isFr ? 'Propriété' : 'Ownership' }}</div>
                    <div class="pc-body" style="padding:19px 9px 8px;">
                        <dl style="--pk:64px;">
                            @foreach($ownershipRows as [$k, $v])
                            <div class="pr"><dt style="font-size:8px;">{{ $k }}</dt><span class="cl">:</span><dd style="font-size:8px;">{{ $v }}</dd></div>
                            @endforeach
                        </dl>
                    </div>
                </section>

                @if($sustainRows->isNotEmpty())
                <section class="pc">
                    <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">11.</span>{{ $isFr ? 'Durabilité' : 'Sustainability' }}</div>
                    <div class="pc-body" style="padding:19px 9px 8px;">
                        <dl style="--pk:64px;">
                            @foreach($sustainRows as [$k, $v])
                            <div class="pr"><dt style="font-size:8px;">{{ $k }}</dt><span class="cl">:</span><dd style="font-size:8px;">{{ $v }}</dd></div>
                            @endforeach
                        </dl>
                        <p style="margin-top:6px;font-size:8.5px;color:#8A857A;line-height:1.35;">
                            {{ $isFr ? 'Déclaré par l\'atelier, non audité.' : 'Declared by the workshop, not audited.' }}
                        </p>
                    </div>
                </section>
                @endif
            </div>
        </div>

        {{-- ══ Row E — §12 geography · §13 rights · §14 related · §15 statement ══ --}}
        <div style="display:grid;grid-template-columns:167px 180px 226px 290px;gap:9px;margin-top:19px;align-items:start;">

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">12.</span>{{ $isFr ? 'Géographie' : 'Geographic identity' }}</div>
                <div class="pc-body" style="padding:19px 9px 8px;">
                    <dl style="--pk:72px;">
                        @foreach($geoRows as [$k, $v])
                        <div class="pr"><dt style="font-size:8px;">{{ $k }}</dt><span class="cl">:</span><dd style="font-size:8px;">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">13.</span>{{ $isFr ? 'Droits' : 'Rights' }}</div>
                <div class="pc-body" style="padding:19px 9px 8px;">
                    {{-- The artwork asserts a copyright claim, a rights holder, a
                         design registration and a licensing status. The platform
                         registers none of those and no office has examined any of
                         them, so what is left is the one true statement: who
                         declared the work, and what registration does not do. --}}
                    <dl style="--pk:64px;">
                        @if($artisan?->name || $bizName)
                        <div class="pr"><dt style="font-size:8px;">{{ $isFr ? 'Déclaré par' : 'Declared by' }}</dt><span class="cl">:</span><dd style="font-size:8px;">{{ $artisan?->name ?: $bizName }}</dd></div>
                        @endif
                        <div class="pr"><dt style="font-size:8px;">{{ $isFr ? 'Portée' : 'Scope' }}</dt><span class="cl">:</span><dd style="font-size:8px;">{{ $isFr ? 'Enregistrement au registre' : 'Registry record only' }}</dd></div>
                    </dl>
                    <p style="margin-top:7px;font-size:9px;line-height:1.4;color:#5A554A;">
                        {{ $isFr
                           ? 'L\'enregistrement ne confère ni droit d\'auteur, ni marque, ni dessin ou modèle, et ne remplace aucune protection légale.'
                           : 'Registration confers no copyright, trademark or design right, and replaces no legal protection.' }}
                    </p>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">14.</span>{{ $isFr ? 'Certificats liés' : 'Related certificates' }}</div>
                <div class="pc-body" style="padding:19px 9px 8px;">
                    <dl style="--pk:104px;">
                        @foreach($relatedRows as [$k, $v])
                        <div class="pr"><dt style="font-size:8px;">{{ $k }}</dt><span class="cl">:</span><dd class="mono" style="font-size:9px;">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    {{-- The artwork lists four more certificates as "to be issued".
                         A certificate that does not exist is not a fact about this
                         product, and printing its name next to a promise is how a
                         reader ends up believing one is on the way. --}}
                </div>
            </section>

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">15.</span>{{ $isFr ? 'Déclaration' : 'Registration statement' }}</div>
                <div class="pc-body" style="padding:19px 11px 9px;">
                    <p style="font-size:8px;line-height:1.4;color:#3F3C34;">
                        {{ $isFr
                           ? 'Ce certificat d\'enregistrement atteste que le produit artisanal identifié ci-dessus a été enregistré au registre produit ArtisanHub237. L\'enregistrement lui attribue une identité numérique permanente et un dossier officiel au sein de l\'écosystème ArtisanHub237.'
                           : 'This Product Registration Certificate confirms that the handcrafted product identified above has been registered in the ArtisanHub237 Product Registry. Registration assigns the product a permanent digital identity and an official record within the ArtisanHub237 ecosystem.' }}
                    </p>
                    <p style="margin-top:4px;font-size:8px;line-height:1.4;color:#3F3C34;">
                        {{ $isFr
                           ? 'ArtisanHub237 est une entreprise privée. Ce document n\'est pas un titre de propriété, ne prouve pas qu\'un objet physique donné est celui décrit, et ne confère aucun droit de propriété intellectuelle.'
                           : 'ArtisanHub237 is a private company. This document is not a title of ownership, does not prove that a given physical object is the one described, and confers no intellectual property rights.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ Row F — §16 audit · §17 verification · §18 authority · §19 standards ══ --}}
        <div style="display:grid;grid-template-columns:165px 180px 342px 176px;gap:9px;margin-top:19px;align-items:start;">

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">16.</span>{{ $isFr ? 'Journal' : 'Audit trail' }}</div>
                <div class="pc-body" style="padding:19px 9px 8px;">
                    <dl style="--pk:72px;line-height:1.25;">
                        @foreach($auditRows as [$k, $v])
                        <div class="pr"><dt style="font-size:7.5px;">{{ $k }}</dt><span class="cl">:</span><dd class="mono" style="font-size:7px;">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">17.</span>{{ $isFr ? 'Vérification' : 'Verification result' }}</div>
                <div class="pc-body" style="padding:19px 9px 8px;">
                    <p style="font-size:11px;font-weight:800;letter-spacing:.03em;color:{{ $status === 'active' ? '#0C7A3E' : '#A11A12' }};">
                        {{ $status === 'active'
                           ? ($isFr ? 'ENREGISTREMENT VALIDE' : 'VALID REGISTRATION')
                           : ($isFr ? 'ENREGISTREMENT RÉVOQUÉ' : 'REGISTRATION REVOKED') }}
                    </p>
                    <div style="margin-top:6px;">
                        {{-- Only the keys the register holds. An absent key means the
                             platform cannot determine the answer, and it renders as
                             nothing at all — an unticked box would be a printed "no"
                             we are no more entitled to than the "yes". --}}
                        @foreach($flags as $key => $ok)
                            @if($ok && isset($checkLabels[$key]))
                            <div class="tick"><i data-lucide="check-circle-2"></i><span>{{ $checkLabels[$key] }}</span></div>
                            @endif
                        @endforeach
                    </div>
                    @if(! $allClear)
                    <p style="margin-top:5px;font-size:8px;line-height:1.35;color:#A11A12;">
                        {{ $isFr
                           ? 'Une ou plusieurs préoccupations sont enregistrées contre ce produit. Consultez la page de vérification.'
                           : 'One or more concerns are on file against this product. See the verification page.' }}
                    </p>
                    @endif
                    <p style="margin-top:4px;font-size:7.5px;line-height:1.3;color:#8A857A;">
                        {{ $isFr
                           ? 'Une ligne cochée signifie qu\'aucun signalement n\'a été reçu par ce registre, et non que le produit a été enquêté.'
                           : 'A ticked line means nothing has been reported to this registry — not that the product was investigated.' }}
                    </p>
                    <p class="mono" style="margin-top:5px;font-size:8px;color:#3A362D;">
                        {{ $isFr ? 'État au' : 'Status as of' }} {{ now()->utc()->format('Y-m-d H:i') }} UTC
                    </p>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">18.</span>{{ $isFr ? 'Autorité émettrice' : 'Issuing authority' }}</div>
                <div class="pc-body" style="padding:19px 11px 9px;display:grid;grid-template-columns:1fr 100px;gap:10px;align-items:center;">
                    <div>
                        <p style="font-family:'Playfair Display',Georgia,serif;font-size:14px;color:#0A3A22;">
                            {{ $isFr ? 'Autorité de certification ArtisanHub237' : 'ArtisanHub237 Certification Authority' }}
                        </p>
                        <div style="margin-top:5px;width:190px;height:1px;background:#C4BCA6;"></div>
                        <p style="margin-top:5px;font-size:8.5px;line-height:1.4;color:#3F3C34;">
                            {{ $isFr
                               ? 'Ce certificat est produit par un programme, pas signé à la main. Sa validité se vérifie en recalculant l\'empreinte du contenu et en contrôlant la signature ci-dessus.'
                               : 'This certificate is minted by a program, not signed by hand. Its validity is checked by recomputing the content hash and verifying the signature shown above.' }}
                        </p>
                        @if($caSigned)
                        <p style="margin-top:5px;font-size:9px;color:#5A554A;">
                            {{ $isFr ? 'Clé publique' : 'Public key' }} :
                            <a href="{{ route('ca.jwks') }}" style="color:#2A6B3E;">/.well-known/jwks.json</a>
                        </p>
                        @endif
                        <p class="mono" style="margin-top:5px;font-size:9px;color:#3A362D;">
                            {{ $isFr ? 'Émis le' : 'Issued' }} {{ $issuedUtc->format('Y-m-d') }} · PIN {{ $certificate->verification_pin }}
                        </p>
                    </div>
                    <svg width="96" height="96" viewBox="0 0 194 194" aria-hidden="true" style="justify-self:center;">
                        <use href="#coaSealTeeth"/>
                        <circle cx="97" cy="97" r="90" fill="url(#coaSealFace)"/>
                        <circle cx="97" cy="97" r="60" fill="#FDFBF3"/>
                        <circle cx="97" cy="97" r="60" fill="none" stroke="#9C6E1B" stroke-width="2.5" opacity=".6"/>
                        <text font-family="Poppins,sans-serif" font-size="14" font-weight="800" fill="#4A360B" letter-spacing="3">
                            <textPath href="#coaSealTop" startOffset="50%" text-anchor="middle">ARTISANHUB237</textPath>
                        </text>
                        <text font-family="Poppins,sans-serif" font-size="10" font-weight="700" fill="#4A360B" letter-spacing="2">
                            <textPath href="#coaSealBot" startOffset="50%" text-anchor="middle">{{ $isFr ? 'CERTIFIÉ · CAMEROUN' : 'CERTIFIED · CAMEROON' }}</textPath>
                        </text>
                        <image href="{{ brand_asset('mark') }}" x="60" y="60" width="74" height="74" preserveAspectRatio="xMidYMid meet"/>
                    </svg>
                </div>
            </section>

            <section class="pc">
                <div class="pc-head" style="font-size:10px;padding:0 8px;"><span class="n">19.</span>{{ $isFr ? 'Normes utilisées' : 'Standards used' }}</div>
                <div class="pc-body" style="padding:19px 9px 8px;">
                    {{-- The artwork's compliance column ticks each standard as
                         "compliant". Nobody audited this platform against any of
                         them, so the honest version names the standard and says
                         where this document uses it — a claim the reader can check
                         on the page in front of them. --}}
                    @foreach($standards as [$std, $where])
                    <p style="padding:1.4px 0;font-size:8.5px;line-height:1.3;color:#1D1B16;">
                        <b style="font-weight:600;">{{ $std }}</b>
                        <span style="color:#6B6659;"> — {{ $where }}</span>
                    </p>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ══ Footer band (measured y 1424–1512) ══ --}}
        <div style="height:8px;"></div>
        <footer class="prc-foot">
            <svg style="position:absolute;left:0;top:0;" width="1024" height="58" aria-hidden="true">
                <rect width="1024" height="58" fill="url(#coaKenteDark)" opacity=".35"/>
            </svg>
            <div style="position:relative;display:flex;align-items:center;gap:14px;padding-left:26px;">
                <svg width="42" height="42" viewBox="0 0 60 40" aria-hidden="true">
                    <rect x="0" y="0" width="20" height="40" fill="#0F7A34"/>
                    <rect x="20" y="0" width="20" height="40" fill="#C8102E"/>
                    <rect x="40" y="0" width="20" height="40" fill="#E5A82E"/>
                    <path d="M30 12l2.2 6.6h7l-5.6 4.1 2.1 6.7-5.7-4.2-5.7 4.2 2.1-6.7-5.6-4.1h7z" fill="#F7DC7A"/>
                </svg>
                <p style="font-size:9.5px;font-weight:600;letter-spacing:.05em;color:#EDE3C8;line-height:1.45;">
                    {{ $isFr ? 'ENRACINÉ DANS LE PATRIMOINE.' : 'ROOTED IN HERITAGE.' }}<br>
                    {{ $isFr ? 'PORTÉ PAR LA TECHNOLOGIE.' : 'POWERED BY TECHNOLOGY.' }}<br>
                    {{ $isFr ? 'RECONNU DANS LE MONDE.' : 'TRUSTED BY THE WORLD.' }}
                </p>
            </div>
            <div style="position:relative;display:grid;grid-template-columns:1fr 216px;align-items:center;">
                <div style="text-align:center;">
                    <p style="font-size:15px;font-weight:800;letter-spacing:.04em;color:#F0C463;white-space:nowrap;">
                        {{ $isFr ? 'ENREGISTRÉ • AUTHENTIFIÉ • PRÉSERVÉ' : 'REGISTERED • AUTHENTICATED • PRESERVED' }}
                    </p>
                    <p style="margin-top:4px;font-size:10.5px;font-weight:600;letter-spacing:.03em;color:#EDE3C8;white-space:nowrap;">
                        {{ $isFr ? 'UN ENREGISTREMENT. UNE CONFIANCE DURABLE.' : 'ONE REGISTRATION. A LIFETIME OF TRUST.' }}
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:11px;padding-right:22px;justify-content:flex-end;">
                    <div style="text-align:right;color:#EDE3C8;">
                        <p style="font-size:11.5px;font-weight:700;letter-spacing:.05em;">ARTISANHUB237</p>
                        <p style="font-size:9.5px;letter-spacing:.03em;opacity:.9;">{{ $isFr ? 'AUTORITÉ DE CERTIFICATION' : 'CERTIFICATION AUTHORITY' }}</p>
                        <p style="font-size:9.5px;opacity:.9;">www.artisanhub237.com</p>
                    </div>
                    <div id="prc-qr-foot" style="width:42px;height:42px;background:#fff;padding:2px;border-radius:3px;"></div>
                </div>
            </div>
        </footer>

        <div class="prc-strip">
            {{ $isFr
               ? 'Ce certificat est un enregistrement numérique ; il se vérifie en ligne.'
               : 'This certificate is a digital record and is verified online.' }}<br>
            {{ $isFr
               ? 'Toute altération de ce document en annule la valeur probante : la copie faisant foi est celle du registre.'
               : 'Any alteration to this document destroys its evidential value: the copy of record is the one held by the registry.' }}
        </div>
    </div>
    </div>
    </article>
    </div>
    <p class="coa-hint no-print items-center justify-center gap-1.5 mt-2 text-[12px] text-[#6F6B60]">
        <i data-lucide="move-horizontal" class="w-3.5 h-3.5"></i>
        {{ $isFr ? 'Faites glisser pour voir tout le certificat, ou imprimez-le.' : 'Swipe to see the whole certificate, or print it.' }}
    </p>

    <div class="no-print mt-5 flex flex-wrap gap-2.5 justify-center">
        <button type="button" onclick="window.print()" class="ui-btn ui-btn-primary">
            <i data-lucide="printer" class="w-4 h-4"></i>
            {{ $isFr ? 'Imprimer / Enregistrer en PDF' : 'Print / Save as PDF' }}
        </button>
        <a href="{{ $verifyShort }}" class="ui-btn ui-btn-secondary">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            {{ $isFr ? 'Vérifier ce certificat' : 'Verify this certificate' }}
        </a>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="ui-btn ui-btn-secondary">
            {{ $isFr ? 'Retour au produit' : 'Back to the product' }}
        </a>
    </div>
</main>

<div class="no-print">@include('pages.partials.directory-footer')</div>

<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    lucide.createIcons();

    (function () {
        if (!window.QRCode) return;
        var url = @json($verifyShort);
        var main = document.getElementById('prc-qr');
        if (main) new QRCode(main, { text: url, width: 112, height: 112, colorDark: '#0A3A22', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
        var foot = document.getElementById('prc-qr-foot');
        if (foot) new QRCode(foot, { text: url, width: 38, height: 38, colorDark: '#08240F', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.L });
    })();

    /* Drawn at the artwork's own 1024px width and scaled to fit, so the layout
       never rearranges — it only gets smaller. The wrapper is given the scaled
       height by hand, because a transform does not affect the space an element
       reserves. */
    (function () {
        var fit  = document.querySelector('.coa-fit');
        var page = document.querySelector('.prc-page');
        if (!fit || !page) return;

        /* This sheet carries 9px body copy at full size. Below roughly two
           thirds it stops being a document and becomes a picture of one, so it
           is held at a legible scale and the frame becomes pannable, which is
           how anyone reads a certificate on a phone anyway. */
        var MIN_SCALE = 0.66;

        function apply() {
            var w = fit.getBoundingClientRect().width;
            var k = Math.max(MIN_SCALE, Math.min(1, w / 1024));

            page.style.transform = k < 1 ? 'scale(' + k + ')' : '';
            fit.style.height = (page.offsetHeight * k) + 'px';

            var scaled = 1024 * k;
            fit.style.overflowX = scaled > w + 1 ? 'auto' : 'hidden';
            fit.classList.toggle('coa-pannable', scaled > w + 1);
        }

        apply();
        new ResizeObserver(apply).observe(fit);
        window.addEventListener('load', apply);
        document.querySelectorAll('.prc-page img').forEach(function (img) {
            if (!img.complete) img.addEventListener('load', apply);
        });
        window.addEventListener('beforeprint', apply);
    })();
</script>
</body>
</html>
