@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $isFr = $lang === 'fr';

    // Consumed by the shared directory header and footer partials; without them
    // those partials fatal, so they are set before anything else happens.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $c     = $consignment;
    $biz   = $product->business;
    $maker = $biz?->user;
    $name  = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $cover = $product->images->sortBy('sort_order')->firstWhere('is_cover', true)
             ?? $product->images->sortBy('sort_order')->first();

    $issued  = $c->issued_at ? Carbon::parse($c->issued_at) : null;
    $expires = $c->expires_at ? Carbon::parse($c->expires_at) : null;

    $verifyUrl   = route('product.certificate.verify', ['ref' => $c->certificate_no, 'lang' => $lang]);
    $verifyShort = route('product.certificate.verify.short', ['ref' => $c->certificate_no]);

    /* ─────────────────────────── Status, told honestly ───────────────────
       The artwork prints a green "ACTIVE" chip and an expiry date beneath it,
       with nothing connecting the two. Here the expiry wins: a certificate
       whose expires_at has passed reads expired whatever the status column
       says, because the column records where the consignment got to and the
       date records whether this document may still be relied on. Getting that
       precedence the wrong way round would let a lapsed sheet keep a green
       chip, which is precisely the state somebody would present at a border. */
    $expired = $expires && $expires->isPast();

    $statusMeta = [
        'draft'        => [$isFr ? 'BROUILLON' : 'DRAFT',       '#6B6659', 'file-text'],
        'submitted'    => [$isFr ? 'DÉPOSÉ'    : 'SUBMITTED',   '#8A5F14', 'send'],
        'under_review' => [$isFr ? 'EN EXAMEN' : 'UNDER REVIEW','#8A5F14', 'search'],
        'approved'     => [$isFr ? 'APPROUVÉ'  : 'APPROVED',    '#0F7A34', 'check-circle-2'],
        'shipped'      => [$isFr ? 'EXPÉDIÉ'   : 'SHIPPED',     '#0F7A34', 'plane'],
        'delivered'    => [$isFr ? 'LIVRÉ'     : 'DELIVERED',   '#0F7A34', 'package-check'],
        'rejected'     => [$isFr ? 'REFUSÉ'    : 'REJECTED',    '#B4141B', 'x-circle'],
        'revoked'      => [$isFr ? 'RÉVOQUÉ'   : 'REVOKED',     '#B4141B', 'shield-off'],
        'cancelled'    => [$isFr ? 'ANNULÉ'    : 'CANCELLED',   '#B4141B', 'ban'],
    ][$c->status] ?? [Str::upper($c->status), '#6B6659', 'help-circle'];

    if ($expired) {
        $statusMeta = [$isFr ? 'EXPIRÉ' : 'EXPIRED', '#B4141B', 'clock-alert'];
    }

    /* ───────────────────────────── Vocabularies ─────────────────────────── */
    $entityLabels = [
        'individual' => $isFr ? 'Particulier'      : 'Individual',
        'company'    => $isFr ? 'Société'          : 'Company',
        'gallery'    => $isFr ? 'Galerie'          : 'Gallery',
        'museum'     => $isFr ? 'Musée'            : 'Museum',
        'government' => $isFr ? 'Organisme public' : 'Government body',
        'foundation' => $isFr ? 'Fondation'        : 'Foundation',
        'dealer'     => $isFr ? 'Marchand d\'art'  : 'Art dealer',
        'collector'  => $isFr ? 'Collectionneur'   : 'Collector',
    ];
    $entityIcons = [
        'individual' => 'user-round', 'company' => 'building-2', 'gallery' => 'palette',
        'museum' => 'landmark', 'government' => 'building', 'foundation' => 'heart-handshake',
        'dealer' => 'store', 'collector' => 'gem',
    ];
    $levelLabels = [
        'unverified'  => $isFr ? 'Non vérifié'          : 'Unverified',
        'declared'    => $isFr ? 'Déclaré'              : 'Declared',
        'verified'    => $isFr ? 'Identité vérifiée'    : 'Identity verified',
        'institution' => $isFr ? 'Institution vérifiée' : 'Verified institution',
    ];
    $purposeLabels = [
        'sale'        => $isFr ? 'Vente'                   : 'Sale',
        'exhibition'  => $isFr ? 'Exposition'              : 'Exhibition',
        'loan'        => $isFr ? 'Prêt'                    : 'Loan',
        'acquisition' => $isFr ? 'Acquisition muséale'     : 'Museum acquisition',
        'donation'    => $isFr ? 'Donation'                : 'Donation',
        'personal'    => $isFr ? 'Usage personnel'         : 'Personal use',
        'restoration' => $isFr ? 'Restauration'            : 'Restoration',
        'repatriation'=> $isFr ? 'Restitution'             : 'Repatriation',
    ];
    /* Declaration values are printed as what they are: a statement by a named
       party, timestamped by us. "Compliant" here never means we checked. */
    $declLabels = [
        'compliant'      => $isFr ? 'Conforme (déclaré)'      : 'Compliant (declared)',
        'not_applicable' => $isFr ? 'Sans objet (déclaré)'    : 'Not applicable (declared)',
        'pending'        => $isFr ? 'En attente'              : 'Pending',
        'restricted'     => $isFr ? 'Restreint'               : 'Restricted',
    ];
    $materialLabels = [
        'none'        => $isFr ? 'Aucun matériau protégé'   : 'No protected materials',
        'cites_listed'=> $isFr ? 'Inscrit à la CITES'       : 'CITES-listed',
        'restricted'  => $isFr ? 'Matériaux restreints'     : 'Restricted materials',
        'unassessed'  => $isFr ? 'Jamais évalué'            : 'Never assessed',
    ];

    /* ────────────────────────────── Readiness ────────────────────────────
       The register hands back a max of 0 for anything it could not judge, and
       the single most important thing this view does is refuse to flatten that
       into a score. A category with max 0 prints the word instead of a
       fraction, in its own muted colour, and still prints its basis — "nothing
       has been packed yet" is the finding, and a reader who sees 0/10 would
       conclude the crate failed an inspection nobody carried out. */
    $catLabels = [
        'authenticity'  => $isFr ? 'Authenticité'   : 'Authenticity',
        'provenance'    => $isFr ? 'Provenance'     : 'Provenance',
        'documentation' => $isFr ? 'Documentation'  : 'Documentation',
        'ownership'     => $isFr ? 'Propriété'      : 'Ownership',
        'packaging'     => $isFr ? 'Emballage'      : 'Packaging',
        // Deliberately "cover" rather than the insurer's word: the register
        // reads cover off a transfer record and holds no policy of its own.
        'insurance'     => $isFr ? 'Couverture'     : 'Cover',
        'compliance'    => $isFr ? 'Conformité'     : 'Compliance',
        'logistics'     => $isFr ? 'Logistique'     : 'Logistics',
    ];
    $ratingLabels = [
        'excellent'  => $isFr ? 'EXCELLENT'    : 'EXCELLENT',
        'very good'  => $isFr ? 'TRÈS BON'     : 'VERY GOOD',
        'good'       => $isFr ? 'BON'          : 'GOOD',
        'fair'       => $isFr ? 'PASSABLE'     : 'FAIR',
        'poor'       => $isFr ? 'FAIBLE'       : 'POOR',
        'unassessed' => $isFr ? 'NON ÉVALUÉ'   : 'NOT ASSESSED',
    ];
    $unassessedWord = $isFr ? 'Non évalué' : 'Not assessed';

    $readyPct   = $readiness['max'] > 0 ? round($readiness['total'] / $readiness['max'] * 100) : null;
    $ratingWord = $ratingLabels[$readiness['rating']] ?? Str::upper($readiness['rating']);

    // Colour follows the band the register computed, not the raw percentage, so
    // the dial and the word can never disagree with each other.
    $ratingColour = match ($readiness['rating']) {
        'excellent', 'very good' => '#0F7A34',
        'good'                   => '#4E7A15',
        'fair'                   => '#A8802A',
        'poor'                   => '#B4141B',
        default                  => '#6B6659',
    };

    /* ──────────────────────────────── Risk ───────────────────────────────
       Four levels, four appearances. `unassessed` is given a slate chip and a
       dashed ring — visibly not the green tick `low` gets — because the whole
       reason the register defaults to unassessed is that "nobody looked" and
       "we looked and it was clean" must not print alike. */
    $riskLabels = [
        'counterfeit'   => $isFr ? 'Contrefaçon'                : 'Counterfeit',
        'ownership'     => $isFr ? 'Propriété'                  : 'Ownership',
        'compliance'    => $isFr ? 'Conformité'                 : 'Compliance',
        'transit'       => $isFr ? 'Transit'                    : 'Transit',
        'environmental' => $isFr ? 'Conditions de conservation' : 'Environmental',
        'insurance'     => $isFr ? 'Couverture et indemnisation': 'Cover and indemnity',
    ];
    $riskMeta = [
        'low'        => [$isFr ? 'Faible'     : 'Low',          '#0F7A34', 'check-circle-2'],
        'medium'     => [$isFr ? 'Moyen'      : 'Medium',       '#A8802A', 'alert-triangle'],
        'high'       => [$isFr ? 'Élevé'      : 'High',         '#B4141B', 'alert-octagon'],
        'unassessed' => [$unassessedWord,                       '#5A6B7E', 'circle-dashed'],
    ];

    /* ─────────────────────────────── Row sets ────────────────────────────
       Every list below is filtered on filled(). A label printed against a
       blank on an export document reads as "none declared", which is a fact
       nobody recorded, and on a customs desk that difference matters. */
    $rows = fn (array $set) => collect($set)->filter(fn ($r) => filled($r[1]))->values();

    $identityRows = $rows([
        [$isFr ? 'N° de certificat d\'export' : 'Export certificate no.', $c->certificate_no, true],
        [$isFr ? 'UUID du certificat' : 'Certificate UUID', $c->uuid, true],
        [$isFr ? 'Version' : 'Certificate version', $c->version, true],
        [$isFr ? 'N° global de consignation (GECN)' : 'Global export consignment no. (GECN)', $c->gecn, true],
        [$isFr ? 'N° de registre produit (PRN)' : 'Product registry no. (PRN)', $prn, true],
        [$isFr ? 'N° de registre de propriété (OLN)' : 'Ownership ledger no. (OLN)', $oln, true],
        [$isFr ? 'UUID du produit' : 'Product UUID', $product->uuid, true],
        [$isFr ? 'Certificat d\'authenticité' : 'Certificate of authenticity', $coa?->certificate_no, true],
        [$isFr ? 'Empreinte du certificat (SHA-256)' : 'Certificate hash (SHA-256)', $c->content_hash, true],
        [$isFr ? 'Émis le (UTC)' : 'Issued at (UTC)', $issued?->clone()->utc()->format('Y-m-d\TH:i:s\Z'), true],
        [$isFr ? 'Expire le (UTC)' : 'Expires at (UTC)', $expires?->clone()->utc()->format('Y-m-d\TH:i:s\Z'), true],
    ]);

    $exporterRows = $exporter ? $rows([
        [$isFr ? 'Réf. exportateur' : 'Exporter reference', $exporter->uuid, true],
        [$isFr ? 'Raison sociale' : 'Legal name', $exporter->legal_name, false],
        [$isFr ? 'Nom commercial' : 'Trading name', $exporter->trading_name, false],
        [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)', $exporter->country, true],
        [$isFr ? 'Adresse' : 'Address', $exporter->address, false],
        [$isFr ? 'Courriel' : 'Email', $exporter->contact_email, false],
        [$isFr ? 'Téléphone' : 'Telephone', $exporter->contact_phone, false],
        // Prints only if a licence was actually recorded. The register creates
        // exporters with this column null on purpose, and an "Export licence:
        // —" line would read as a licence nobody holds.
        [$isFr ? 'N° de licence d\'exportation' : 'Export licence no.', $exporter->export_licence_no ?? null, true],
    ]) : collect();

    $ownerRows = $owner ? $rows([
        [$isFr ? 'Réf. propriétaire' : 'Owner reference', $owner->owner_ref, true],
        [$isFr ? 'Raison sociale / nom' : 'Legal name', $owner->legal_name, false],
        [$isFr ? 'Type d\'entité' : 'Entity type', $entityLabels[$owner->entity_type] ?? $owner->entity_type, false],
        [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)', $owner->country_code, true],
        [$isFr ? 'Propriétaire depuis' : 'Owner since', $owner->owned_from ? Carbon::parse($owner->owned_from)->format('Y-m-d') : null, true],
        [$isFr ? 'Niveau de vérification' : 'Verification level', $levelLabels[$owner->verification_level] ?? $owner->verification_level, false],
        [$isFr ? 'Rang dans la chaîne' : 'Position in chain', '#' . $owner->sequence, true],
    ]) : collect();

    $importerRows = $rows([
        [$isFr ? 'Institution / destinataire' : 'Institution / consignee', $c->importer_name, false],
        [$isFr ? 'Type' : 'Type', $entityLabels[$c->importer_type] ?? $c->importer_type, false],
        [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)', $c->importer_country, true],
        [$isFr ? 'Ville' : 'City', $c->importer_city, false],
        [$isFr ? 'Adresse' : 'Address', $c->importer_address, false],
        [$isFr ? 'Objet de l\'exportation' : 'Intended purpose', $purposeLabels[$c->intended_purpose] ?? $c->intended_purpose, false],
    ]);

    $creatorRows = $rows([
        [$isFr ? 'Nom de l\'artisan' : 'Artisan name', $maker?->name, false],
        [$isFr ? 'Atelier' : 'Workshop', $biz?->name_fr, false],
        [$isFr ? 'Région' : 'Region', $biz?->region?->name_fr, false],
        [$isFr ? 'Pays' : 'Country', $biz ? ($isFr ? 'Cameroun (CM)' : 'Cameroon (CM)') : null, false],
    ]);

    /* Product specifications: the attribute rows the artisan actually filled
       in, plus the few columns the products table holds. The artwork's fixed
       seven-row table (primary material, weight, fragility rating…) is not a
       schema this platform has, so the rows follow the data. */
    $specRows = $rows(collect([
        [$isFr ? 'Nom du produit' : 'Product name', $name, false],
        [$isFr ? 'Catégorie' : 'Category', $product->category
            ? ($isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr)) : null, false],
        ['SKU', $product->sku, true],
        [$isFr ? 'Année de création' : 'Year created', $product->created_at?->format('Y'), true],
    ])->concat($product->attributes
        ->filter(fn ($a) => $a->template && filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->map(fn ($a) => [
            $isFr ? $a->template->name_fr : ($a->template->name_en ?: $a->template->name_fr),
            trim(($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)) . ' ' . ($a->unit ?? '')),
            false,
        ])->values())->all());

    $complianceRows = $rows([
        [$isFr ? 'Pays d\'origine (ISO 3166-1)' : 'Country of origin (ISO 3166-1)', $c->country_of_origin, true],
        [$isFr ? 'Réf. du certificat d\'origine' : 'Certificate of origin ref.', $c->origin_certificate_ref, true],
        [$isFr ? 'Déclaration de patrimoine culturel' : 'Cultural heritage declaration',
            $c->cultural_heritage_declaration ? ($declLabels[$c->cultural_heritage_declaration] ?? $c->cultural_heritage_declaration) : null, false],
        [$isFr ? 'Déclaration d\'approvisionnement éthique' : 'Ethical sourcing declaration',
            $c->ethical_sourcing_declaration ? ($declLabels[$c->ethical_sourcing_declaration] ?? $c->ethical_sourcing_declaration) : null, false],
        [$isFr ? 'Matériaux protégés' : 'Protected materials',
            $c->protected_materials ? ($materialLabels[$c->protected_materials] ?? $c->protected_materials) : null, false],
        [$isFr ? 'N° de permis d\'exportation' : 'Export permit number', $c->export_permit_no, true],
        [$isFr ? 'Statut d\'inspection' : 'Inspection status', $c->inspection_status, false],
        [$isFr ? 'Date d\'inspection' : 'Inspection date', $c->inspected_at ? Carbon::parse($c->inspected_at)->format('Y-m-d') : null, true],
    ]);

    /* The two declarations that were never made get their own explicit line,
       because a compliance panel that simply omits them is indistinguishable
       from one where everything was in order. */
    $missingDeclarations = collect([
        $c->cultural_heritage_declaration === null
            ? ($isFr ? 'Aucune déclaration de patrimoine culturel n\'a été faite.' : 'No cultural heritage declaration has been made.') : null,
        $c->ethical_sourcing_declaration === null
            ? ($isFr ? 'Aucune déclaration d\'approvisionnement éthique n\'a été faite.' : 'No ethical sourcing declaration has been made.') : null,
        $c->protected_materials === 'unassessed'
            ? ($isFr ? 'Les matériaux protégés n\'ont jamais été évalués.' : 'Protected materials have never been assessed.') : null,
    ])->filter()->values();

    $shipmentRows = $shipment ? $rows([
        [$isFr ? 'Transporteur' : 'Carrier', $shipment->carrier, false],
        [$isFr ? 'Service' : 'Service', $shipment->service, false],
        [$isFr ? 'Lettre de transport aérien (LTA)' : 'Air waybill (AWB)', $shipment->awb_no, true],
        [$isFr ? 'Connaissement' : 'Bill of lading', $shipment->bill_of_lading_no, true],
        [$isFr ? 'N° de suivi' : 'Tracking number', $shipment->tracking_no, true],
        [$isFr ? 'Vol / navire' : 'Flight or vessel', $shipment->flight_or_vessel, false],
        [$isFr ? 'Point de sortie' : 'Port of exit', $shipment->port_of_exit, false],
        [$isFr ? 'Expédié le' : 'Shipped at', $shipment->shipped_at ? Carbon::parse($shipment->shipped_at)->format('Y-m-d') : null, true],
        [$isFr ? 'Arrivée prévue' : 'Expected arrival', $shipment->expected_at ? Carbon::parse($shipment->expected_at)->format('Y-m-d') : null, true],
        [$isFr ? 'Livré le' : 'Delivered at', $shipment->delivered_at ? Carbon::parse($shipment->delivered_at)->format('Y-m-d') : null, true],
        [$isFr ? 'Nombre de colis' : 'Package count', $shipment->package_count, true],
        [$isFr ? 'Réf. de caisse' : 'Crate reference', $shipment->crate_ref, true],
        [$isFr ? 'Poids brut (kg)' : 'Gross weight (kg)', $shipment->gross_weight_kg, true],
        [$isFr ? 'Poids net (kg)' : 'Net weight (kg)', $shipment->net_weight_kg, true],
        [$isFr ? 'Dimensions' : 'Dimensions', $shipment->dimensions, true],
    ]) : collect();

    /* Crate protections are printed as three explicit yes/no lines rather than
       as a list of what is present. "Climate control: not recorded" is the
       useful reading; silently listing only the protections that exist lets an
       absent one pass for an unmentioned detail. */
    $protections = $shipment ? collect([
        [$isFr ? 'Protection contre les chocs' : 'Shock protection', (bool) $shipment->shock_protection],
        [$isFr ? 'Régulation climatique' : 'Climate control', (bool) $shipment->climate_protection],
        [$isFr ? 'Régulation hygrométrique' : 'Humidity control', (bool) $shipment->humidity_protection],
    ]) : collect();

    $conditionRows = $condition ? $rows([
        [$isFr ? 'Inspecté le' : 'Inspected at', $condition->inspected_at ? Carbon::parse($condition->inspected_at)->format('Y-m-d') : null, true],
        [$isFr ? 'Inspecteur' : 'Inspector', $condition->inspector_name, false],
        [$isFr ? 'Réf. de l\'inspecteur' : 'Inspector reference', $condition->inspector_ref, true],
        [$isFr ? 'Réf. du constat' : 'Report reference', $condition->report_ref, true],
        [$isFr ? 'Surface' : 'Surface', $condition->surface, false],
        [$isFr ? 'Structure' : 'Structural', $condition->structural, false],
        [$isFr ? 'Finition' : 'Finish', $condition->finish, false],
        [$isFr ? 'Conservation' : 'Preservation', $condition->preservation, false],
        [$isFr ? 'Emballage' : 'Packaging', $condition->packaging, false],
        [$isFr ? 'État général' : 'Overall', $condition->overall, false],
        [$isFr ? 'Notes' : 'Notes', $condition->notes, false],
    ]) : collect();

    /* ─────────────────────────── Eligibility ticks ───────────────────────
       $flags carries only the concerns this register is authoritative over. A
       key it does not carry is "cannot be determined" and is never a tick. */
    $flagLabels = [
        'not_reported_stolen'     => $isFr ? 'Aucun signalement de vol au registre' : 'No theft report on this register',
        'not_under_investigation' => $isFr ? 'Aucune enquête signalée au registre'  : 'No investigation reported to this register',
        'ownership_undisputed'    => $isFr ? 'Aucune contestation de propriété'     : 'No ownership dispute recorded',
    ];

    $checks = collect();
    foreach ($flags as $key => $ok) {
        if (isset($flagLabels[$key])) $checks->push([$flagLabels[$key], (bool) $ok]);
    }
    if ($coa) $checks->push([$isFr ? 'Certificat d\'authenticité émis' : 'Certificate of authenticity issued', true]);
    $checks->push([$isFr ? 'Créateur d\'origine au registre' : 'Original creator on the register',
        (bool) collect($chain)->first()?->is_original_creator]);
    $checks->push([$isFr ? 'Détenteur actuel identifié' : 'Current holder identified', (bool) $owner]);
    $checks->push([$isFr ? 'Consignation approuvée' : 'Consignment approved',
        in_array($c->status, ['approved', 'shipped', 'delivered'], true)]);
    $checks->push([$isFr ? 'Certificat signé par l\'autorité' : 'Signed by the certification authority', filled($c->ca_signature)]);
    $checks->push([$isFr ? 'Certificat en cours de validité' : 'Certificate in force', ! $expired && ! in_array($c->status, ['rejected', 'revoked', 'cancelled'], true)]);

    /* ────────────────────────────── Audit trail ─────────────────────────── */
    $eventLabels = [
        'opened'    => $isFr ? 'Consignation ouverte'  : 'Consignment opened',
        'approved'  => $isFr ? 'Exportation approuvée' : 'Export approved',
        'rejected'  => $isFr ? 'Exportation refusée'   : 'Export rejected',
        'shipped'   => $isFr ? 'Expédition confirmée'  : 'Shipment confirmed',
        'delivered' => $isFr ? 'Livraison confirmée'   : 'Delivery confirmed',
        'issued'    => $isFr ? 'Certificat émis'       : 'Certificate issued',
        'verified'  => $isFr ? 'Certificat vérifié'    : 'Certificate verified',
    ];

    /* Related documents. Each line is a reference this register actually holds;
       the artwork lists six document types with "Active" chips whether or not
       anything was issued, which turns an empty register into a filing
       cabinet. */
    $related = $rows([
        [$isFr ? 'Certificat d\'authenticité (COA)' : 'Certificate of authenticity (COA)', $coa?->certificate_no, true],
        [$isFr ? 'Certificat d\'exportation (EAC)' : 'Export certificate (EAC)', $c->certificate_no, true],
        [$isFr ? 'Consignation (GECN)' : 'Consignment (GECN)', $c->gecn, true],
        [$isFr ? 'Registre produit (PRN)' : 'Product registry (PRN)', $prn, true],
        [$isFr ? 'Registre de propriété (OLN)' : 'Ownership ledger (OLN)', $oln, true],
        [$isFr ? 'Permis d\'exportation' : 'Export permit', $c->export_permit_no, true],
        [$isFr ? 'Certificat d\'origine' : 'Certificate of origin', $c->origin_certificate_ref, true],
    ]);

    /* ─────────────────────── The security legend, page 3 ─────────────────
       Only what a reader can check on a screen. The artwork's fourteen badges
       include a rainbow holographic shield, an embossed gold seal, ultraviolet
       artwork, a ghost watermark, a latent image, an invisible watermark and
       an NFC chip — every one of them a property of a physical print run or a
       piece of hardware, none of which a web page or a home printout carries.
       Naming them here would be the single most damaging thing on the sheet,
       because they are exactly the features a reader would "check" by looking
       at a picture of them. What is left is genuinely verifiable, and the
       physical measures are specified separately in docs/PRINT-SECURITY-SPEC.md
       for whoever eventually commissions a security printer. */
    $securityLegend = collect([
        ['pen-tool',    $isFr ? 'Signature Ed25519 (EdDSA)' : 'Ed25519 (EdDSA) signature',
            $isFr ? 'Vérifiable avec la clé publiée' : 'Checkable against the published key'],
        ['fingerprint', $isFr ? 'Empreinte SHA-256' : 'SHA-256 content hash',
            $isFr ? 'Calculée sur les faits certifiés' : 'Computed over the certified facts'],
        ['key-round',   $isFr ? 'Clé publique publiée' : 'Published public key', '/.well-known/jwks.json'],
        ['hash',        $isFr ? 'UUID du certificat' : 'Certificate UUID',
            $isFr ? 'Identifiant unique, RFC 4122' : 'Unique identifier, RFC 4122'],
        ['list-ordered',$isFr ? 'Numérotation de série' : 'Serial numbering',
            $isFr ? 'Séquence continue au registre' : 'One unbroken sequence on the register'],
        ['qr-code',     $isFr ? 'Vérification par QR' : 'QR verification',
            $isFr ? 'Renvoie au registre en ligne' : 'Resolves to the online register'],
        ['link-2',      $isFr ? 'Journal chaîné par empreintes' : 'Hash-chained event log',
            $isFr ? 'Chaque entrée référence la précédente' : 'Each entry carries its predecessor\'s digest'],
        ['spline',      $isFr ? 'Guilloché' : 'Guilloché',
            $isFr ? 'Tracé hypotrochoïde, vectoriel' : 'Hypotrochoid line work, vector'],
        ['type',        $isFr ? 'Microtexte' : 'Microtext',
            $isFr ? 'Texte réel de 1,6 px, lisible au zoom' : 'Real 1.6px type, resolves on zoom'],
        ['grid-2x2',    $isFr ? 'Trame anti-copie' : 'Anti-copy screen',
            $isFr ? 'Se dégrade à la reproduction' : 'Degrades under reproduction'],
    ]);

    $standards = [
        $isFr ? 'ISO 8601 — dates et heures'       : 'ISO 8601 date and time',
        $isFr ? 'ISO 3166-1 — codes pays'          : 'ISO 3166-1 country codes',
        $isFr ? 'ISO 4217 — codes devise'          : 'ISO 4217 currency codes',
        $isFr ? 'RFC 4122 — identifiants UUID'     : 'RFC 4122 UUID identifiers',
        $isFr ? 'SHA-256 — empreinte du contenu'   : 'SHA-256 content hash',
        $isFr ? 'Ed25519 (EdDSA) — signature'      : 'Ed25519 (EdDSA) signature',
        $isFr ? 'ISO/IEC 16388 — code-barres Code 39' : 'ISO/IEC 16388 Code 39 barcode',
    ];

    /* ── Code 39, encoded for real ────────────────────────────────────────
       The artwork prints a barcode block under the QR. Decorative bars would
       have been quicker and a scanner would catch the lie: a machine-readable
       mark that resolves to noise is worse than no mark. Code 39 carries the
       certificate number exactly, needs no check digit, and every character of
       an EAC number is inside its alphabet. Anything unencodable drops the
       barcode entirely rather than printing a partial symbol. */
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
    $barText = Str::upper($c->certificate_no);
    $barBars = []; $barX = 0; $narrow = 1.0; $wide = 2.6; $gap = 1.0;
    foreach (str_split('*' . $barText . '*') as $ch) {
        $pattern = $c39[$ch] ?? null;
        if (! $pattern) { $barBars = []; break; }
        foreach (str_split($pattern) as $i => $el) {
            $w = $el === 'w' ? $wide : $narrow;
            if ($i % 2 === 0) { $barBars[] = [$barX, $w]; }
            $barX += $w;
        }
        $barX += $gap;
    }
    $barWidth = $barX;

    $serial        = Str::afterLast($c->certificate_no, '-');
    $provenanceUrl = Str::after(route('products.show', ['slug' => $product->slug, 'lang' => $lang]), '://');

    $pageOf = fn (int $n) => $isFr ? "Page {$n} sur 3" : "Page {$n} of 3";

    /* ───────────────────────────── The sheet foot ────────────────────────
       Built once and echoed on all three sheets rather than written out three
       times. Not for brevity: three hand-maintained copies of a footer is how
       page 3 ends up saying "Page 2 of 3" six months from now, and the one
       thing a multi-page dossier cannot afford is a sheet that misidentifies
       itself. The microtext strip is the real 1.6px type from the security
       sheet; it is captioned nowhere, because a line of small print on a screen
       proves nothing and only the fact that it is genuine <text> is claimed —
       in the legend on page 3, where it can be qualified. */
    $sheetFoot = function (int $n) use ($c, $serial, $isFr, $pageOf) {
        $no    = e($c->certificate_no);
        $ser   = e($serial);
        $page  = e($pageOf($n));
        $strap = e($isFr
            ? 'Document numérique — l\'exemplaire de référence est celui vérifié en ligne'
            : 'A digital record — the authoritative copy is the one verified online');
        $issuer = e($isFr ? 'ArtisanHub237 · Autorité de certification' : 'ArtisanHub237 · Certification Authority');

        return <<<HTML
        <div style="margin:9px 20px 0;">
            <svg width="921" height="6" viewBox="0 0 1000 6" preserveAspectRatio="none" aria-hidden="true">
                <g transform="translate(0,1)"><use href="#coaMicrotextLine"/></g>
            </svg>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin:4px 20px 0;">
            <span class="mono" style="width:104px;flex:none;font-size:12px;font-weight:700;color:#B4141B;letter-spacing:.05em;">{$ser}</span>
            <div style="flex:1;height:22px;border-radius:5px;border:1px solid #C9942E;
                        background:linear-gradient(180deg,#0B3159,#06264A);display:flex;align-items:center;justify-content:center;">
                <span style="font-size:9.5px;font-weight:700;letter-spacing:.06em;color:#F0DFB4;text-transform:uppercase;">{$strap}</span>
            </div>
            <span class="mono" style="width:186px;flex:none;text-align:right;font-size:8px;color:#3F4759;word-break:break-all;">{$no}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 20px 0;padding-bottom:11px;">
            <span style="font-size:8px;font-weight:600;letter-spacing:.06em;color:#5A6273;text-transform:uppercase;">{$issuer}</span>
            <span style="font-size:8.5px;font-weight:700;letter-spacing:.09em;color:#8A5F14;text-transform:uppercase;">{$page}</span>
        </div>
        HTML;
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Certificat d\'authenticité à l\'exportation' : 'Export Authenticity Certificate' }} — {{ $name }}">
    <title>{{ $isFr ? 'Certificat d\'authenticité à l\'exportation' : 'Export Authenticity Certificate' }} — {{ $c->certificate_no }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* ────────────────────────────────────────────────────────────────
           Three sheets, one canvas. Each sheet is drawn at the artwork's own
           1024px width and the whole stack is scaled as a single unit by the
           script at the foot of the page, exactly as the single-sheet
           certificates are. Scaling per sheet would let two pages of the same
           dossier end up at different sizes on a narrow screen, which is the
           one thing a multi-page document must not do.
           ──────────────────────────────────────────────────────────────── */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .coa-page { width: 1024px; transform-origin: top left; font-size: 12px; line-height: 1.3; color: #14203A; }
        .coa-page, .coa-page * { font-size: revert-layer; }

        /* One sheet. The dark bezel is the artwork's navy; the ornamental gold
           band sits between it and the cream sheet, painted across the whole
           frame and covered in the middle by the opaque page, which is what
           makes the four runs meet exactly at the corners. */
        .eac-sheet   { background:#041B33; padding:9px; border-radius:18px; position:relative;
                       margin-bottom:26px; }
        .eac-sheet:last-of-type { margin-bottom:0; }
        .eac-frame {
            padding:22px; position:relative; border-radius:10px;
            background:
                repeating-radial-gradient(circle at 13px 11px, rgba(0,0,0,0) 0 4.6px, #C9942E 4.6px 5.8px, rgba(0,0,0,0) 5.8px 13px),
                repeating-radial-gradient(circle at 0 11px,   rgba(0,0,0,0) 0 4.6px, #A87C22 4.6px 5.6px, rgba(0,0,0,0) 5.6px 13px),
                #08294B;
            background-size:26px 22px, 26px 22px, auto;
            box-shadow: inset 0 0 0 1.2px #C9942E;
        }
        .eac-paper { width:961px; position:relative; background:#FCF6EC; box-shadow:0 0 0 1.2px #C9942E; }
        .eac-guilloche { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:.42; z-index:0; }
        .eac-paper > *:not(.eac-guilloche) { position:relative; z-index:1; }

        /* Section cards. The EAC artwork heads every block with a navy bar and
           a letter — A, B, C — and the letters are the whole navigation system
           of a three-page dossier: "see C on page 2" is only usable if the bars
           carry them, so they are rendered rather than dropped for tidiness. */
        .sc      { border:1px solid #C9BFA6; border-radius:7px; background:#FFFDF7; overflow:hidden; }
        .sc-head { display:flex; align-items:center; gap:7px; height:22px; padding:0 9px;
                   background:linear-gradient(180deg,#0B3159,#06264A); }
        .sc-head .ltr { width:14px; height:14px; border-radius:3px; flex:none; background:#C9942E; color:#06264A;
                        font-size:9px; font-weight:800; display:flex; align-items:center; justify-content:center; }
        .sc-head h2  { font-size:9.5px; font-weight:700; letter-spacing:.075em; text-transform:uppercase; color:#F4EAD4; }
        .sc-body { padding:8px 10px 9px; }

        .kv     { display:grid; grid-template-columns:var(--kv,140px) 1fr; column-gap:9px;
                  padding:2px 0; border-bottom:1px solid #EFE7D5; align-items:baseline; }
        .kv:last-child { border-bottom:0; }
        .kv dt  { font-size:8.5px; font-weight:500; color:#4E5769; }
        .kv dd  { font-size:8.5px; font-weight:600; color:#14203A; word-break:break-word; }
        .mono   { font-family:ui-monospace,'SFMono-Regular',Consolas,monospace; letter-spacing:-.2px; }
        .tick   { display:flex; align-items:flex-start; gap:6px; padding:2.2px 0; font-size:9px; color:#1B2740; }
        .tick svg { width:11px; height:11px; flex:none; margin-top:1px; }
        .note   { font-size:8px; line-height:1.45; color:#5A6273; }

        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .coa-fit { overflow:visible; }
            /* One sheet, one A4 page. Without this the three run together and
               the page numbers printed on them become wrong, which is worse
               than no page numbers at all. */
            .eac-sheet { break-after: page; page-break-after: always; margin-bottom:0; }
            .eac-sheet:last-of-type { break-after: auto; page-break-after: auto; }
            @page { size: A4 portrait; margin: 5mm; }
        }
    </style>
</head>
<body class="bg-[#E7E9EE] text-[#14203A] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>

@include('pages.partials.coa-ornaments')
@include('pages.partials.coa-security')

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="eacGoldRule" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#C9942E" stop-opacity="0"/>
            <stop offset="25%" stop-color="#C9942E"/>
            <stop offset="55%" stop-color="#F0D493"/>
            <stop offset="85%" stop-color="#C9942E"/>
            <stop offset="1" stop-color="#C9942E" stop-opacity="0"/>
        </linearGradient>
    </defs>
</svg>

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28] truncate max-w-[180px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificat d\'exportation' : 'Export certificate' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.14)] rounded-[6px]">
    <article class="coa-page">

    {{-- ══════════════════════════ SHEET 1 ══════════════════════════════ --}}
    <section class="eac-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'EAC'])
    <div class="eac-frame">
    <div class="eac-paper">

        <svg class="eac-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,20)" opacity=".5"><use href="#coaGuillocheRosette"/></g>
            <g transform="translate(0,180) scale(1,.9)" opacity=".35"><use href="#coaGuillocheRosette"/></g>
        </svg>

        {{-- ══ Header ══ --}}
        <div style="padding:12px 96px 0;text-align:center;">
            <img src="{{ brand_asset('full') }}" alt="ArtisanHub237"
                 style="height:62px;width:auto;max-width:100%;margin:0 auto;display:block;">
            <p style="margin-top:2px;font-size:10px;font-weight:600;letter-spacing:.26em;color:#4E5769;">
                {{ $isFr ? 'RELIER L\'ARTISANAT AUTHENTIQUE' : 'CONNECTING AUTHENTIC CRAFTSMANSHIP' }}
            </p>
            <h1 style="margin-top:8px;font-family:'Playfair Display',Georgia,serif;font-size:37px;line-height:1;
                       font-weight:700;color:#0A1A33;letter-spacing:.01em;display:inline-block;
                       transform:scaleX(.92);transform-origin:center;white-space:nowrap;">
                {{ $isFr ? 'CERTIFICAT D\'AUTHENTICITÉ À L\'EXPORTATION' : 'EXPORT AUTHENTICITY CERTIFICATE' }}
            </h1>
            <div style="margin-top:5px;display:flex;align-items:center;justify-content:center;gap:12px;">
                <svg width="220" height="3" aria-hidden="true"><rect width="220" height="3" fill="url(#eacGoldRule)"/></svg>
                <span style="font-family:'Playfair Display',Georgia,serif;font-size:25px;font-weight:600;color:#0A1A33;">(EAC)</span>
                <svg width="220" height="3" aria-hidden="true"><rect width="220" height="3" fill="url(#eacGoldRule)"/></svg>
            </div>
            {{-- The artwork's strap reads "THIS CERTIFICATE CONFIRMS AUTHENTICITY,
                 PROVENANCE & EXPORT ELIGIBILITY". Two of those three are ours to
                 confirm; eligibility to export turns on CITES schedules and
                 heritage law no private register holds, so the line says what
                 the document actually is. --}}
            <p style="margin:8px auto 0;display:inline-block;padding:5px 18px;border-radius:5px;
                      background:linear-gradient(180deg,#0B3159,#06264A);border:1px solid #C9942E;
                      font-size:9.5px;font-weight:700;letter-spacing:.1em;color:#F0DFB4;text-transform:uppercase;">
                {{ $isFr
                   ? 'Registre d\'authenticité, de provenance et de déclarations d\'exportation'
                   : 'A register of authenticity, provenance and export declarations' }}
            </p>
        </div>

        {{-- Sheet identity strip. Repeated on all three sheets: a page that
             arrives on its own must still say which dossier it belongs to. --}}
        <div style="display:flex;align-items:center;gap:10px;margin:11px 20px 0;padding:5px 12px;border-radius:5px;
                    background:#0A2E55;border:1px solid #C9942E;">
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;letter-spacing:.04em;">{{ $c->certificate_no }}</span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span style="font-size:9px;font-weight:700;letter-spacing:.12em;color:#C9942E;text-transform:uppercase;">
                {{ $pageOf(1) }} — {{ $isFr ? 'Certificat' : 'Certificate' }}
            </span>
        </div>

        {{-- ══ A · Certificate identity · QR · the piece ══ --}}
        <div style="display:grid;grid-template-columns:452px 232px 245px;gap:8px;margin:9px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">A</span><h2>{{ $isFr ? 'Identité du certificat' : 'Certificate identity' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:186px;">
                        @foreach($identityRows as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                        <div class="kv">
                            <dt>{{ $isFr ? 'Statut du certificat' : 'Certificate status' }}</dt>
                            <dd style="display:flex;align-items:center;gap:5px;color:{{ $statusMeta[1] }};font-weight:700;">
                                <i data-lucide="{{ $statusMeta[2] }}" style="width:11px;height:11px;flex:none;"></i>{{ $statusMeta[0] }}
                            </dd>
                        </div>
                    </dl>
                    @if($c->rejected_reason)
                    <p class="note" style="margin-top:6px;color:#B4141B;">
                        {{ $isFr ? 'Motif du refus' : 'Reason for rejection' }} : {{ $c->rejected_reason }}
                    </p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">B</span><h2>{{ $isFr ? 'Vérification' : 'Verification' }}</h2></div>
                <div class="sc-body" style="text-align:center;">
                    <div id="eac-qr" style="margin:2px auto 0;width:126px;height:126px;"></div>
                    <p style="margin-top:6px;font-size:8.5px;font-weight:700;letter-spacing:.07em;color:#4E5769;text-transform:uppercase;">
                        {{ $isFr ? 'Scanner pour vérifier' : 'Scan to verify' }}
                    </p>
                    <p class="mono" style="margin-top:4px;font-size:8.5px;color:#0B4A2A;word-break:break-all;">{{ $verifyShort }}</p>
                    @if($c->verification_pin)
                    <p style="margin-top:6px;font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#4E5769;">{{ $isFr ? 'CODE DE VÉRIFICATION' : 'VERIFICATION CODE' }}</p>
                    <p class="mono" style="font-size:13px;font-weight:700;letter-spacing:.14em;color:#14203A;">{{ $c->verification_pin }}</p>
                    @endif
                    @if($barBars !== [])
                    {{-- A genuine Code 39 encoding of the certificate number. --}}
                    <svg style="margin:7px auto 0;display:block;" width="200" height="28"
                         viewBox="0 0 {{ round($barWidth, 2) }} 28" preserveAspectRatio="none" role="img" aria-label="{{ $barText }}">
                        @foreach($barBars as [$bx, $bw])
                        <rect x="{{ round($bx, 3) }}" y="0" width="{{ round($bw, 3) }}" height="28" fill="#111"/>
                        @endforeach
                    </svg>
                    <p class="mono" style="margin-top:3px;font-size:7.5px;letter-spacing:.03em;color:#4E5769;">{{ $barText }}</p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">C</span><h2>{{ $isFr ? 'La pièce' : 'The piece' }}</h2></div>
                <div class="sc-body">
                    <div style="height:150px;border:1px solid #DDD2BA;border-radius:5px;background:#F4EEE1;
                                display:flex;align-items:center;justify-content:center;padding:5px;overflow:hidden;">
                        @if($cover)
                        <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}" style="max-width:100%;max-height:100%;object-fit:contain;">
                        @else
                        <i data-lucide="image-off" style="width:22px;height:22px;color:#A8A296;"></i>
                        @endif
                    </div>
                    <p style="margin-top:6px;font-size:10px;font-weight:700;color:#14203A;line-height:1.3;">{{ $name }}</p>
                    <dl style="margin-top:4px;--kv:88px;">
                        <div class="kv"><dt>PRN</dt><dd class="mono">{{ $prn }}</dd></div>
                        <div class="kv"><dt>OLN</dt><dd class="mono">{{ $oln }}</dd></div>
                        @if($coa?->image_phash)
                        <div class="kv"><dt>{{ $isFr ? 'Empreinte image' : 'Image hash' }}</dt><dd class="mono">{{ Str::upper($coa->image_phash) }}</dd></div>
                        @endif
                    </dl>
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr ? 'Image déposée par l\'artisan sur ArtisanHub237.' : 'Image submitted by the artisan on ArtisanHub237.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ D–G · the four parties ══ --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:8px 20px 0;align-items:stretch;">
            @foreach([
                ['D', 'user-round', $isFr ? 'Créateur d\'origine' : 'Original creator', $creatorRows],
                ['E', 'building-2', $isFr ? 'Exportateur' : 'Exporter', $exporterRows],
                ['F', 'landmark',   $isFr ? 'Propriétaire actuel' : 'Current owner', $ownerRows],
                ['G', 'globe',      $isFr ? 'Destination (importateur)' : 'Destination (importer)', $importerRows],
            ] as [$ltr, $ico, $title, $set])
            <section class="sc">
                <div class="sc-head"><span class="ltr">{{ $ltr }}</span><h2>{{ $title }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:92px;">
                        @forelse($set as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @empty
                        <p class="note">{{ $isFr ? 'Aucune donnée au registre.' : 'Nothing on the register.' }}</p>
                        @endforelse
                    </dl>
                    {{-- The artwork closes each of these panels with a
                         handwritten signature. No specimen signatures exist
                         anywhere in this platform, and drawing one beside a
                         named person's name is a forgery with extra steps, so
                         each party is a text entry with its register reference
                         and nothing is signed by hand. --}}
                </div>
            </section>
            @endforeach
        </div>

        {{-- ══ H · readiness · I · eligibility · J · timeline ══ --}}
        <div style="display:grid;grid-template-columns:392px 268px 261px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">H</span><h2>{{ $isFr ? 'Score de préparation à l\'export' : 'Export readiness score' }}</h2></div>
                <div class="sc-body">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <svg width="112" height="68" viewBox="0 0 112 68" aria-hidden="true" style="flex:none;">
                            <path d="M10 60a46 46 0 0 1 92 0" fill="none" stroke="#E4DBC6" stroke-width="11" stroke-linecap="round"/>
                            @if($readyPct !== null)
                            {{-- 144 is the arc length of the half-circle above;
                                 the dash is the fraction actually scored. --}}
                            <path d="M10 60a46 46 0 0 1 92 0" fill="none" stroke="{{ $ratingColour }}" stroke-width="11"
                                  stroke-linecap="round" stroke-dasharray="{{ round(144.5 * $readyPct / 100, 1) }} 400"/>
                            @endif
                            <text x="56" y="52" text-anchor="middle" font-family="Poppins,sans-serif" font-size="21" font-weight="700"
                                  fill="{{ $ratingColour }}">{{ $readyPct !== null ? $readyPct . '%' : '—' }}</text>
                        </svg>
                        <div style="min-width:0;">
                            <p style="font-size:12px;font-weight:800;letter-spacing:.05em;color:{{ $ratingColour }};">{{ $ratingWord }}</p>
                            <p class="mono" style="margin-top:2px;font-size:10px;font-weight:700;color:#14203A;">
                                {{ $readiness['total'] }} / {{ $readiness['max'] }}
                            </p>
                            {{-- The denominator is what could be assessed, not
                                 the theoretical total, and saying so is the
                                 difference between an honest percentage and a
                                 flattering average over an absence. --}}
                            <p class="note" style="margin-top:3px;">
                                {{ $isFr
                                   ? 'Pourcentage calculé sur les seules catégories évaluables. Les catégories non évaluées sont listées ci-dessous et exclues du dénominateur.'
                                   : 'Scored over the categories that could be assessed. Unassessed categories are listed below and left out of the denominator.' }}
                            </p>
                        </div>
                    </div>

                    <div style="margin-top:7px;border-top:1px solid #EFE7D5;">
                        @foreach($readiness['categories'] as $key => $cat)
                        @php $isUnassessed = $cat['max'] <= 0; @endphp
                        <div style="display:grid;grid-template-columns:1fr 66px;gap:8px;padding:3.5px 0;
                                    border-bottom:1px solid #F2EBDA;align-items:baseline;">
                            <div style="min-width:0;">
                                <p style="font-size:9px;font-weight:700;color:#14203A;">{{ $catLabels[$key] ?? $key }}</p>
                                {{-- The basis is not a footnote. It is what
                                     makes a wrong score arguable instead of
                                     merely disappointing. --}}
                                <p class="note" style="margin-top:1px;">{{ $cat['basis'] }}</p>
                            </div>
                            @if($isUnassessed)
                            <span style="justify-self:end;font-size:8.5px;font-weight:700;color:#5A6B7E;
                                         border:1px dashed #97A6B8;border-radius:4px;padding:1px 5px;white-space:nowrap;">
                                {{ $unassessedWord }}
                            </span>
                            @else
                            <span class="mono" style="justify-self:end;font-size:10px;font-weight:700;
                                        color:{{ $cat['score'] === $cat['max'] ? '#0F7A34' : ($cat['score'] === 0 ? '#B4141B' : '#8A5F14') }};">
                                {{ $cat['score'] }} / {{ $cat['max'] }}
                            </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">I</span><h2>{{ $isFr ? 'Statut d\'éligibilité' : 'Eligibility status' }}</h2></div>
                <div class="sc-body">
                    @foreach($checks as [$label, $ok])
                    <div class="tick">
                        <i data-lucide="{{ $ok ? 'check-circle-2' : 'alert-circle' }}" style="color:{{ $ok ? '#0F7A34' : '#B4141B' }};"></i>
                        <span>{{ $label }}</span>
                    </div>
                    @endforeach
                    <p class="note" style="margin-top:7px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Ces contrôles portent sur ce que ce registre contient. Une ligne au vert signifie qu\'aucun signalement n\'a été reçu, non qu\'une enquête a été menée. ArtisanHub237 ne délivre aucune autorisation d\'exportation.'
                           : 'These checks describe what this register holds. A green line means nothing has been reported to it, not that an investigation was carried out. ArtisanHub237 issues no export clearance.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">J</span><h2>{{ $isFr ? 'Chronologie de l\'export' : 'Export timeline' }}</h2></div>
                <div class="sc-body">
                    {{-- Every line is a stored certificate_events row. The
                         artwork shows eight tidy milestones ending in a future
                         arrival date; a register prints the steps that happened,
                         however few, and never one that has not. --}}
                    @forelse($trail as $ti => $event)
                    <div style="display:flex;gap:7px;padding:3px 0;{{ $ti ? 'border-top:1px solid #F2EBDA;' : '' }}">
                        <i data-lucide="circle-check-big" style="width:11px;height:11px;flex:none;margin-top:2px;color:#0F7A34;"></i>
                        <div style="min-width:0;">
                            <p style="font-size:9px;font-weight:600;color:#14203A;">{{ $eventLabels[$event->event] ?? $event->event }}</p>
                            <p class="mono" style="font-size:8px;color:#5A6273;">{{ Carbon::parse($event->occurred_at)->utc()->format('Y-m-d\TH:i:s\Z') }}</p>
                            @if($event->note)<p class="note">{{ $event->note }}</p>@endif
                        </div>
                    </div>
                    @empty
                    <p class="note">{{ $isFr ? 'Aucun événement consigné.' : 'No events recorded.' }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ══ K · parties, as text ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">K</span><h2>{{ $isFr ? 'Parties à l\'exportation' : 'Parties to the export' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
                {{-- The artwork gives five named people a handwritten signature
                     and a date apiece. The register holds no signatures at all —
                     it holds who the parties were and the reference each is
                     filed under. Rendering a scrawl beside a real person's name
                     is exactly the thing a provenance register exists to make
                     harder, so the names stand alone. --}}
                @foreach(array_filter([
                    [$isFr ? 'Artisan (créateur)' : 'Artisan (creator)', $maker?->name ?: $biz?->name_fr, $biz?->slug],
                    [$isFr ? 'Exportateur' : 'Exporter', $exporter?->legal_name, $exporter?->uuid],
                    [$isFr ? 'Propriétaire actuel' : 'Current owner', $owner?->legal_name, $owner?->owner_ref],
                    [$isFr ? 'Destinataire' : 'Consignee', $c->importer_name, $c->importer_country],
                ], fn ($p) => filled($p[1])) as [$pTitle, $pName, $pRef])
                <div>
                    <p style="font-size:8.5px;font-weight:700;letter-spacing:.04em;color:#4E5769;text-transform:uppercase;">{{ $pTitle }}</p>
                    <p style="margin-top:6px;font-size:10.5px;font-weight:600;color:#14203A;">{{ $pName }}</p>
                    @if(filled($pRef))<p class="mono" style="margin-top:2px;font-size:8.5px;color:#5A6273;word-break:break-all;">{{ $pRef }}</p>@endif
                    <div style="margin-top:8px;height:1px;background:#D7C9A8;"></div>
                    <p class="note" style="margin-top:4px;">
                        {{ $isFr ? 'Inscrit au registre' : 'Entered on the register' }}
                        <span class="mono">{{ $issued?->format('Y-m-d') ?? Carbon::parse($c->created_at)->format('Y-m-d') }}</span>
                    </p>
                </div>
                @endforeach
            </div>
        </section>

        {!! $sheetFoot(1) !!}
    </div>
    </div>
    </section>

    {{-- ══════════════════════════ SHEET 2 ══════════════════════════════ --}}
    <section class="eac-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'EAC'])
    <div class="eac-frame">
    <div class="eac-paper">

        <svg class="eac-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,60)" opacity=".4"><use href="#coaGuillocheRosette"/></g>
        </svg>

        <div style="display:flex;align-items:center;gap:10px;margin:14px 20px 0;padding:6px 12px;border-radius:5px;
                    background:linear-gradient(180deg,#0B3159,#06264A);border:1px solid #C9942E;">
            <img src="{{ brand_asset('mark') }}" alt="" style="height:20px;width:auto;flex:none;">
            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;color:#F4EAD4;text-transform:uppercase;">
                {{ $isFr ? 'Dossier d\'exportation' : 'Export dossier' }}
            </span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;">{{ $c->certificate_no }}</span>
            <span style="font-size:9px;font-weight:700;letter-spacing:.12em;color:#C9942E;text-transform:uppercase;">{{ $pageOf(2) }}</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:9px 20px 0;align-items:start;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">A</span><h2>{{ $isFr ? 'Spécifications du produit' : 'Product specifications' }}</h2></div>
                <div class="sc-body"><dl style="--kv:150px;">
                    @foreach($specRows as [$k, $v, $m])
                    <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                </dl></div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">B</span><h2>{{ $isFr ? 'Aperçu de la provenance' : 'Provenance snapshot' }}</h2></div>
                <div class="sc-body">
                    @foreach($chain as $ci => $node)
                    <div style="display:flex;gap:7px;padding:3px 0;{{ $ci ? 'border-top:1px solid #F2EBDA;' : '' }}">
                        <span style="width:20px;height:20px;flex:none;border-radius:50%;display:flex;align-items:center;justify-content:center;
                                     background:{{ $node->owned_until ? '#EFE7D5' : 'linear-gradient(160deg,#0B3159,#06264A)' }};
                                     color:{{ $node->owned_until ? '#7A6428' : '#E9CE8B' }};border:1px solid {{ $node->owned_until ? '#D7C9A8' : '#C9942E' }};">
                            <i data-lucide="{{ $entityIcons[$node->entity_type] ?? 'user-round' }}" style="width:11px;height:11px;"></i>
                        </span>
                        <div style="min-width:0;flex:1;">
                            <p style="font-size:9px;font-weight:600;color:#14203A;">
                                {{ $node->sequence }}. {{ $node->legal_name }}
                                @if(! $node->owned_until)<b style="color:#0F7A34;">· {{ $isFr ? 'détenteur actuel' : 'current holder' }}</b>@endif
                            </p>
                            <p class="note">
                                {{ $node->is_original_creator ? ($isFr ? 'Artisan d\'origine' : 'Original artisan') : ($entityLabels[$node->entity_type] ?? $node->entity_type) }}
                                @if($node->country_code) · {{ $node->country_code }}@endif
                                · {{ $isFr ? 'depuis' : 'since' }} {{ Carbon::parse($node->owned_from)->format('Y-m-d') }}
                                @if($node->owned_until) · {{ $isFr ? 'jusqu\'au' : 'until' }} {{ Carbon::parse($node->owned_until)->format('Y-m-d') }}@endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                    <div style="margin-top:7px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        <p class="note">{{ $isFr ? 'Historique complet' : 'Full provenance history' }}</p>
                        <p class="mono" style="font-size:8.5px;color:#0B4A2A;word-break:break-all;">{{ $provenanceUrl }}</p>
                    </div>
                </div>
            </section>
        </div>

        {{-- ══ C · export compliance ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">C</span><h2>{{ $isFr ? 'Conformité à l\'exportation' : 'Export compliance' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <dl style="--kv:190px;">
                    @foreach($complianceRows as [$k, $v, $m])
                    <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                    @if($c->customs_declaration_no)
                    {{-- Recorded, not endorsed. A customs endorsement is an act
                         by a state official; what the register holds is a number
                         somebody typed in, and that is how it prints. No stamp
                         is drawn and no officer is named. --}}
                    <div class="kv"><dt>{{ $isFr ? 'N° de déclaration en douane' : 'Customs declaration number' }}</dt>
                        <dd class="mono">{{ $c->customs_declaration_no }}</dd></div>
                    @endif
                </dl>
                <div>
                    @if($missingDeclarations->isNotEmpty())
                    <p style="font-size:9px;font-weight:700;color:#B4141B;">{{ $isFr ? 'Déclarations manquantes' : 'Declarations not made' }}</p>
                    @foreach($missingDeclarations as $line)
                    <div class="tick"><i data-lucide="alert-circle" style="color:#B4141B;"></i><span>{{ $line }}</span></div>
                    @endforeach
                    @endif
                    <p class="note" style="margin-top:7px;">
                        {{ $isFr
                           ? 'Chaque valeur ci-dessus est une déclaration faite par une partie identifiée et horodatée par ArtisanHub237. Ce n\'est ni une vérification, ni un dédouanement, ni une autorisation de sortie du territoire.'
                           : 'Every value above is a declaration made by an identified party and timestamped by ArtisanHub237. It is not a verification, a customs clearance, or permission for the piece to leave the country.' }}
                    </p>
                    @if($c->customs_declaration_no)
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr
                           ? 'La déclaration en douane est reproduite telle que déclarée. ArtisanHub237 n\'appose aucun visa douanier.'
                           : 'The customs declaration is reproduced as declared. ArtisanHub237 endorses nothing.' }}
                    </p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ══ D · shipping and logistics — only when a booking exists ══ --}}
        @if($shipmentRows->isNotEmpty() || $protections->isNotEmpty())
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">D</span><h2>{{ $isFr ? 'Expédition et logistique' : 'Shipping and logistics' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 300px;gap:14px;">
                <dl style="--kv:190px;">
                    @foreach($shipmentRows as [$k, $v, $m])
                    <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                </dl>
                <div>
                    <p style="font-size:9px;font-weight:700;color:#14203A;">{{ $isFr ? 'Protections de la caisse' : 'Crate protections' }}</p>
                    @foreach($protections as [$pLabel, $pOn])
                    <div class="tick">
                        <i data-lucide="{{ $pOn ? 'check-circle-2' : 'circle-dashed' }}" style="color:{{ $pOn ? '#0F7A34' : '#5A6B7E' }};"></i>
                        <span>{{ $pLabel }} — {{ $pOn ? ($isFr ? 'déclarée' : 'declared') : ($isFr ? 'non enregistrée' : 'not recorded') }}</span>
                    </div>
                    @endforeach
                    <p class="note" style="margin-top:6px;">
                        {{ $isFr
                           ? 'Une protection non enregistrée n\'est pas une protection absente : c\'est une protection que personne n\'a déclarée.'
                           : 'A protection that is not recorded is not necessarily absent: it is one nobody declared.' }}
                    </p>
                </div>
            </div>
        </section>
        @endif

        {{-- ══ E · condition report — only when an inspection exists ══ --}}
        @if($conditionRows->isNotEmpty())
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">E</span><h2>{{ $isFr ? 'Constat d\'état' : 'Condition report' }}</h2></div>
            <div class="sc-body"><dl style="--kv:190px;">
                @foreach($conditionRows as [$k, $v, $m])
                <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                @endforeach
            </dl></div>
        </section>
        @endif

        {{-- ══ F · risk ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">F</span><h2>{{ $isFr ? 'Évaluation des risques' : 'Risk assessment' }}</h2></div>
            <div class="sc-body">
                @foreach($risk as $key => $line)
                @php $meta = $riskMeta[$line['level']] ?? $riskMeta['unassessed']; @endphp
                <div style="display:grid;grid-template-columns:150px 96px 1fr;gap:10px;padding:4px 0;
                            {{ $loop->first ? '' : 'border-top:1px solid #F2EBDA;' }}align-items:baseline;">
                    <span style="font-size:9px;font-weight:700;color:#14203A;">{{ $riskLabels[$key] ?? $key }}</span>
                    {{-- Four levels, four appearances. `unassessed` gets a slate
                         chip with a dashed edge rather than the green tick
                         `low` gets: "nobody looked" and "we looked and it was
                         clean" must never print alike, and this is the row
                         where that distinction does the most work. --}}
                    <span style="display:inline-flex;align-items:center;gap:4px;justify-self:start;font-size:8.5px;font-weight:700;
                                 color:{{ $meta[1] }};border:1px {{ $line['level'] === 'unassessed' ? 'dashed' : 'solid' }} {{ $meta[1] }};
                                 border-radius:4px;padding:1px 6px;white-space:nowrap;">
                        <i data-lucide="{{ $meta[2] }}" style="width:10px;height:10px;"></i>{{ $meta[0] }}
                    </span>
                    <span class="note">{{ $line['basis'] }}</span>
                </div>
                @endforeach
                <p class="note" style="margin-top:7px;padding-top:6px;border-top:1px solid #EFE7D5;">
                    {{ $isFr
                       ? 'Un risque « non évalué » n\'est pas un risque faible : c\'est un risque que ce registre n\'a pas les éléments de mesurer. La valeur par défaut est « non évalué », jamais « faible ».'
                       : 'An “unassessed” risk is not a low risk: it is one this register holds nothing to measure. The default is unassessed, never low.' }}
                </p>
            </div>
        </section>

        {!! $sheetFoot(2) !!}
    </div>
    </div>
    </section>

    {{-- ══════════════════════════ SHEET 3 ══════════════════════════════ --}}
    <section class="eac-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'EAC'])
    <div class="eac-frame">
    <div class="eac-paper">

        <svg class="eac-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,60)" opacity=".4"><use href="#coaGuillocheRosette"/></g>
        </svg>

        <div style="display:flex;align-items:center;gap:10px;margin:14px 20px 0;padding:6px 12px;border-radius:5px;
                    background:linear-gradient(180deg,#0B3159,#06264A);border:1px solid #C9942E;">
            <img src="{{ brand_asset('mark') }}" alt="" style="height:20px;width:auto;flex:none;">
            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;color:#F4EAD4;text-transform:uppercase;">
                {{ $isFr ? 'Vérification et conformité' : 'Verification and compliance' }}
            </span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;">{{ $c->certificate_no }}</span>
            <span style="font-size:9px;font-weight:700;letter-spacing:.12em;color:#C9942E;text-transform:uppercase;">{{ $pageOf(3) }}</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:9px 20px 0;align-items:start;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">A</span><h2>{{ $isFr ? 'Documents liés au registre' : 'Related documents on the register' }}</h2></div>
                <div class="sc-body"><dl style="--kv:200px;">
                    @foreach($related as [$k, $v, $m])
                    <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                </dl>
                <p class="note" style="margin-top:6px;">
                    {{ $isFr
                       ? 'Seules les références réellement détenues par ce registre sont listées. Une ligne absente signifie qu\'aucun document de ce type n\'a été enregistré.'
                       : 'Only references this register actually holds are listed. A missing line means no document of that kind has been recorded.' }}
                </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">B</span><h2>{{ $isFr ? 'Journal chaîné (événements clés)' : 'Audit trail (hash-chained)' }}</h2></div>
                <div class="sc-body">
                    @forelse($trail as $ti => $event)
                    <div style="display:grid;grid-template-columns:1fr 128px;gap:8px;padding:3px 0;
                                {{ $ti ? 'border-top:1px solid #F2EBDA;' : '' }}align-items:baseline;">
                        <div style="min-width:0;">
                            <p style="font-size:9px;font-weight:600;color:#14203A;">{{ $eventLabels[$event->event] ?? $event->event }}</p>
                            <p class="mono" style="font-size:7.5px;color:#8A8264;word-break:break-all;">{{ Str::limit($event->entry_hash, 40, '…') }}</p>
                        </div>
                        <span class="mono" style="font-size:8px;color:#5A6273;">{{ Carbon::parse($event->occurred_at)->utc()->format('Y-m-d H:i:s') }} UTC</span>
                    </div>
                    @empty
                    <p class="note">{{ $isFr ? 'Aucun événement consigné.' : 'No events recorded.' }}</p>
                    @endforelse
                    <p class="note" style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Chaque entrée porte l\'empreinte de la précédente : une suppression ou une modification invalide toutes les suivantes.'
                           : 'Each entry carries its predecessor\'s digest: a deletion or an edit invalidates every entry after it.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ C · the signature block ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">C</span><h2>{{ $isFr ? 'Signature de l\'autorité de certification' : 'Certification authority signature' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <p style="font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#4E5769;">{{ $isFr ? 'EMPREINTE DU CONTENU (SHA-256)' : 'CONTENT HASH (SHA-256)' }}</p>
                    <p class="mono" style="margin-top:4px;font-size:9px;line-height:1.5;word-break:break-all;">{{ $c->content_hash }}</p>
                    @if($c->signature)
                    <p style="margin-top:7px;font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#4E5769;">{{ $isFr ? 'SCEAU INTERNE (HMAC)' : 'INTERNAL SEAL (HMAC)' }}</p>
                    <p class="mono" style="margin-top:3px;font-size:8px;line-height:1.5;word-break:break-all;color:#5A6273;">{{ $c->signature }}</p>
                    @endif
                </div>
                <div>
                    @if($c->ca_signature)
                    {{-- The artwork draws a flourish here. What is printed
                         instead is the signature itself: a detached Ed25519
                         signature anybody can check against the published key
                         without asking this platform anything at all. --}}
                    <p style="font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#4E5769;">{{ $isFr ? 'SIGNATURE NUMÉRIQUE (AC)' : 'DIGITAL SIGNATURE (CA)' }}</p>
                    <p class="mono" style="margin-top:4px;font-size:8.5px;line-height:1.5;word-break:break-all;">{{ $c->ca_signature }}</p>
                    <p style="margin-top:4px;font-size:8.5px;color:#4E5769;">
                        EdDSA · Ed25519 · <span class="mono">kid {{ $c->ca_kid }}</span>
                    </p>
                    <p class="mono" style="margin-top:2px;font-size:8.5px;color:#0B4A2A;">/.well-known/jwks.json</p>
                    @else
                    <p class="note">{{ $isFr ? 'Ce certificat n\'a pas encore été signé par l\'autorité de certification.' : 'This certificate has not yet been signed by the certification authority.' }}</p>
                    @endif
                    <dl style="margin-top:7px;--kv:150px;">
                        @if($c->last_verified_at)
                        <div class="kv"><dt>{{ $isFr ? 'Dernière vérification' : 'Last verified' }}</dt>
                            <dd class="mono">{{ Carbon::parse($c->last_verified_at)->utc()->format('Y-m-d H:i') }} UTC</dd></div>
                        @endif
                        <div class="kv"><dt>{{ $isFr ? 'Nombre de vérifications' : 'Verification count' }}</dt>
                            <dd class="mono">{{ (int) $c->verification_count }}</dd></div>
                    </dl>
                </div>
            </div>
        </section>

        {{-- ══ D · security legend · E · standards ══ --}}
        <div style="display:grid;grid-template-columns:1fr 320px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">D</span><h2>{{ $isFr ? 'Éléments vérifiables' : 'Verifiable features' }}</h2></div>
                <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:4px 14px;">
                    @foreach($securityLegend as [$sIcon, $sTitle, $sNote])
                    <div style="display:flex;gap:7px;padding:3px 0;">
                        <span style="width:22px;height:22px;flex:none;border-radius:50%;border:1px solid #C9942E;
                                     background:#F6EEDC;color:#8A5F14;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $sIcon }}" style="width:12px;height:12px;"></i>
                        </span>
                        <div style="min-width:0;">
                            <p style="font-size:9px;font-weight:700;color:#14203A;">{{ $sTitle }}</p>
                            <p class="note" style="word-break:break-word;">{{ $sNote }}</p>
                        </div>
                    </div>
                    @endforeach
                    <p class="note" style="grid-column:1 / -1;margin-top:5px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Cette liste ne contient que ce qu\'un lecteur peut vérifier sur un écran ou une impression ordinaire. Les mesures qui exigent une imprimerie de sécurité — encres, films, gaufrage, papier filigrané — ne figurent pas ici et sont spécifiées séparément.'
                           : 'This list contains only what a reader can check on a screen or an ordinary printout. Measures that need a security printer — inks, films, embossing, watermarked stock — are not claimed here and are specified separately.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">E</span><h2>{{ $isFr ? 'Normes appliquées' : 'Standards applied' }}</h2></div>
                <div class="sc-body">
                    @foreach($standards as $line)
                    <div class="tick"><i data-lucide="check-circle-2" style="color:#0F7A34;"></i><span>{{ $line }}</span></div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ══ F · what this document is, and is not ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">F</span><h2>{{ $isFr ? 'Portée et limites' : 'Scope and limits' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <p class="note">
                    {{ $isFr
                       ? 'ArtisanHub237 est une entreprise privée. Elle n\'est ni un registre public, ni une autorité douanière, ni un organisme de licence à l\'exportation. Elle horodate et signe ce que des parties identifiées déclarent, conserve la trace de chaque modification, et n\'est partie à aucune transaction : elle n\'encaisse aucun paiement et n\'expédie rien.'
                       : 'ArtisanHub237 is a private company. It is not a public register, a customs authority or an export licensing body. It timestamps and signs what identified parties declare and keeps a trace of every change. It is party to no transaction: it collects no payments and ships nothing.' }}
                </p>
                <p class="note">
                    {{ $isFr
                       ? 'Ce dossier compte trois pages ; une page isolée est incomplète. L\'exemplaire de référence est celui qui est vérifié en ligne à l\'adresse ci-dessus : en cas de divergence entre cette impression et le registre, c\'est le registre qui fait foi. Aucune déclaration ci-dessus ne dispense des formalités douanières, fiscales ou d\'importation applicables.'
                       : 'This dossier runs to three pages; a single page is incomplete. The authoritative copy is the one verified online at the address above: where this printout and the register disagree, the register is what counts. Nothing declared above replaces the customs, tax or import formalities that apply.' }}
                </p>
            </div>
        </section>

        {!! $sheetFoot(3) !!}
    </div>
    </div>
    </section>

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
        <a href="{{ $verifyUrl }}" class="ui-btn ui-btn-secondary">
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
        var box = document.getElementById('eac-qr');
        if (box && window.QRCode) {
            new QRCode(box, {
                text: @json($verifyShort),
                width: 126, height: 126,
                colorDark: '#0A2E55', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();

    /* All three sheets are drawn at the artwork's 1024px width and scaled as a
       single unit, so the dossier never rearranges — it only gets smaller, and
       page 2 can never end up at a different size from page 1. The wrapper has
       to be given the scaled height by hand, because a transform does not
       change the space an element reserves. */
    (function () {
        var fit  = document.querySelector('.coa-fit');
        var page = document.querySelector('.coa-page');
        if (!fit || !page) return;

        var MIN_SCALE = 0.62;

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
        document.querySelectorAll('.coa-page img').forEach(function (img) {
            if (!img.complete) img.addEventListener('load', apply);
        });
        window.addEventListener('beforeprint', apply);
    })();
</script>
</body>
</html>
