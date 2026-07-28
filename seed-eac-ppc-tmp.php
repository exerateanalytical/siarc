<?php
/*
 * Preview records for the export and provenance certificates, so both documents
 * can be built and screenshotted against a register that actually holds
 * something. Uses the existing coa-preview product and its unclaimed SIARC
 * business, which stays draft so nothing reaches the public directory.
 */

use App\Modules\Products\Models\Product;
use App\Support\ExportRegister;
use App\Support\ProvenanceDossier;
use App\Support\ProvenanceRegistry;
use Illuminate\Support\Facades\DB;

$product = Product::where('slug', 'coa-preview')->first();
if (! $product) { echo "no coa-preview product\n"; return; }

/* ── Provenance events, enough for a dossier with a real timeline ── */
if (! DB::table('provenance_events')->where('product_id', $product->id)->exists()) {
    ProvenanceDossier::record($product, 'exhibition', [
        'title' => 'SIARC — 9e édition', 'organisation' => 'SIARC', 'venue' => 'Palais des Congrès',
        'country' => 'CM', 'city' => 'Yaoundé', 'started_on' => '2026-07-27', 'ended_on' => '2026-08-05',
        'reference_no' => 'EXH-CM-2026-0001',
    ]);
    ProvenanceDossier::record($product, 'gallery_representation', [
        'title' => 'Représentation en galerie', 'organisation' => 'Heritage Gallery Douala',
        'country' => 'CM', 'city' => 'Douala', 'started_on' => '2026-07-28',
    ]);
    ProvenanceDossier::record($product, 'condition_report', [
        'title' => 'État avant expédition', 'organisation' => 'Heritage Gallery Douala',
        'country' => 'CM', 'started_on' => '2026-07-28', 'notes' => 'Excellent. Aucune restauration nécessaire.',
    ]);
    ProvenanceDossier::record($product, 'valuation', [
        'title' => 'Expertise assurantielle', 'organisation' => 'Cabinet Ekani',
        'country' => 'CM', 'started_on' => '2026-07-28',
        'valuation' => ['appraiser' => 'Cabinet Ekani', 'valued_on' => '2026-07-28',
                        'amount' => '1200000.00', 'currency' => 'XAF', 'purpose' => 'insurance'],
    ]);
    ProvenanceDossier::record($product, 'publication', [
        'title' => "L'art du masque Sawa", 'organisation' => 'Revue des Arts du Cameroun',
        'country' => 'CM', 'started_on' => '2026-07-20', 'reference_no' => 'Vol. 12',
    ]);
}

/* ── One export consignment, carried through to approved and shipped ── */
$c = DB::table('export_consignments')->where('product_id', $product->id)->first();

if (! $c) {
    $c = ExportRegister::open($product, [
        'name'    => 'Museum of World Cultures',
        'type'    => 'museum',
        'country' => 'FR',
        'city'    => 'Paris',
        'address' => '1 Culture Avenue, 75001 Paris',
    ], [
        'intended_purpose'              => 'museum_acquisition',
        'country_of_origin'             => 'CM',
        'origin_certificate_ref'        => 'COO-CM-2026-000125',
        'cultural_heritage_declaration' => 'compliant',
        'ethical_sourcing_declaration'  => 'compliant',
        'protected_materials'           => 'none',
        'export_permit_no'              => 'EXP-CM-2026-00125',
        'customs_declaration_no'        => 'DEC-CM-2026-7789',
        'inspection_status'             => 'approved',
        'inspected_at'                  => now()->subDays(2),
    ]);

    ExportRegister::recordCondition($product, [
        'inspected_at' => now()->subDays(2), 'inspector_name' => 'M. Ekani',
        'inspector_ref' => 'INS-CM-0031',
        'surface' => 'excellent', 'structural' => 'excellent', 'finish' => 'very_good',
        'preservation' => 'excellent', 'packaging' => 'excellent', 'overall' => 'excellent',
        'notes' => 'Aucun dommage constaté. Emballage caisse musée.',
        'report_ref' => 'CR-CM-2026-0031',
    ], $c->id);

    ExportRegister::approve($c->id);
    ExportRegister::ship($c->id, [
        'carrier' => 'DHL Express', 'service' => 'Air', 'awb_no' => 'AF654-2378-12345678',
        'tracking_no' => '7771234567891', 'flight_or_vessel' => 'AF 654',
        'port_of_exit' => 'Douala International', 'shipped_at' => now(),
        'expected_at' => now()->addDays(5), 'package_count' => 1, 'crate_ref' => 'CRATE-AH237-0031',
        'gross_weight_kg' => '9.400', 'net_weight_kg' => '2.350',
        'dimensions' => '60 x 40 x 30 cm',
        'shock_protection' => true, 'climate_protection' => true, 'humidity_protection' => true,
    ]);
    ExportRegister::issue($c->id);
    $c = DB::table('export_consignments')->find($c->id);
}

$r = ExportRegister::readiness($c->id);
$l = ProvenanceDossier::legacyIndex($product);

echo "product      : {$product->slug}\n";
echo "PRN          : " . ProvenanceRegistry::prnFor($product) . "\n";
echo "OLN          : " . ProvenanceRegistry::olnFor($product) . "\n";
echo "EAC ref      : {$c->certificate_no}\n";
echo "GECN         : {$c->gecn}\n";
echo "EAC status   : {$c->status}\n";
echo "readiness    : {$r['total']}/{$r['max']} ({$r['rating']})\n";
echo "legacy index : {$l['total']}/{$l['max']} ({$l['band']})\n";
echo "events       : " . count(ProvenanceDossier::events($product)) . "\n";
