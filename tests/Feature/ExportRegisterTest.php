<?php

namespace Tests\Feature;

use App\Support\CertificationAuthority;
use App\Support\ExportRegister;
use App\Support\ProductCertificate;
use App\Support\ProductFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the register behind the Export Authenticity Certificate.
 *
 * The document this backs makes two kinds of claim, and they fail differently.
 * The first kind is a state claim — approved, shipped, delivered — and the
 * failure mode is a consignment reaching a state it was never entitled to, so
 * the illegal transitions are asserted to throw rather than merely be unusual.
 *
 * The second kind is the scored claim: the artwork prints "98% EXCELLENT" across
 * eight readiness categories. The failure mode there is subtler and worse — a
 * number that looks computed but is really typed in. So the tests assert the
 * opposite of a good score: that a consignment holding nothing scores nothing,
 * that categories with nothing to assess come back unassessed rather than full,
 * and that the stated basis moves when the underlying facts move.
 */
class ExportRegisterTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product()
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /** A product that has been certified, which is the normal export case. */
    private function certifiedProduct()
    {
        $product = $this->product();
        ProductCertificate::forProduct($product);

        return $product->fresh();
    }

    private function importer(): array
    {
        return [
            'name'    => 'Musée du quai Branly',
            'type'    => 'museum',
            'country' => 'FR',
            'city'    => 'Paris',
            'address' => '37 quai Branly, 75007 Paris',
        ];
    }

    /* ─────────────────────────── State transitions ─────────────────────── */

    public function test_a_consignment_cannot_be_shipped_before_it_is_approved(): void
    {
        $c = ExportRegister::open($this->certifiedProduct(), $this->importer());

        $this->expectException(\DomainException::class);
        ExportRegister::ship($c->id, ['carrier' => 'DHL', 'awb_no' => '123-45678901']);
    }

    public function test_a_consignment_cannot_be_delivered_before_it_is_shipped(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());
        ExportRegister::approve($c->id);

        $this->expectException(\DomainException::class);
        ExportRegister::deliver($c->id);
    }

    public function test_a_piece_reported_stolen_cannot_be_approved_for_export(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());

        ProductFlags::raise($product, 'reported_stolen', ['reference' => 'PV-2026-0091']);

        $this->expectException(\DomainException::class);
        ExportRegister::approve($c->id);
    }

    public function test_a_piece_with_no_certificate_of_authenticity_cannot_be_approved(): void
    {
        // Deliberately not certified: nothing has been issued for this product.
        $c = ExportRegister::open($this->product(), $this->importer());

        $this->assertSame(0, DB::table('product_certificates')->count());

        $this->expectException(\DomainException::class);
        ExportRegister::approve($c->id);
    }

    public function test_the_happy_path_walks_draft_to_delivered(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());

        $this->assertSame('draft', $c->status);

        ExportRegister::approve($c->id);
        $this->assertSame('approved', ExportRegister::find($c->id)->status);

        ExportRegister::ship($c->id, ['carrier' => 'DHL Express', 'awb_no' => '123-45678901']);
        $this->assertSame('shipped', ExportRegister::find($c->id)->status);

        ExportRegister::deliver($c->id);
        $this->assertSame('delivered', ExportRegister::find($c->id)->status);
    }

    /* ───────────────────────── Numbering and signing ───────────────────── */

    public function test_a_consignment_is_numbered_hashed_and_signed_by_the_authority(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());
        ExportRegister::approve($c->id);
        $c = ExportRegister::issue($c->id);

        $this->assertMatchesRegularExpression('/^AH237-EAC-CM-\d{4}-\d{12}$/', $c->certificate_no);
        $this->assertMatchesRegularExpression('/^AH237-GECN-CM-\d{4}-\d{12}$/', $c->gecn);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $c->content_hash);
        $this->assertNotEmpty($c->verification_pin);
        $this->assertNotNull($c->issued_at);

        // Only meaningful where the authority actually holds a signing key; a
        // machine without one must still issue, unsigned, rather than refuse.
        if (CertificationAuthority::isConfigured()) {
            $issuedAt = \Illuminate\Support\Carbon::parse($c->issued_at)->toIso8601String();

            $this->assertTrue(CertificationAuthority::verifyCertificate(
                'eac', $c->certificate_no, $c->content_hash, $issuedAt, $c->ca_signature
            ));

            // The certificate type is inside the signed payload, so an export
            // certificate must not verify as an ownership transfer one.
            $this->assertFalse(CertificationAuthority::verifyCertificate(
                'otc', $c->certificate_no, $c->content_hash, $issuedAt, $c->ca_signature
            ));
        }
    }

    public function test_verification_finds_the_certificate_and_counts_the_look_up(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());
        ExportRegister::approve($c->id);
        $c = ExportRegister::issue($c->id);

        $result = ExportRegister::verify($c->certificate_no, $c->verification_pin);
        $this->assertSame('valid', $result['status']);

        $this->assertSame('pin_mismatch', ExportRegister::verify($c->certificate_no, 'WRONGPIN')['status']);
        $this->assertSame('notfound', ExportRegister::verify('AH237-EAC-CM-2026-000000999999')['status']);

        $this->assertSame(1, (int) ExportRegister::find($c->id)->verification_count);
    }

    public function test_the_audit_trail_records_issuance(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());
        ExportRegister::approve($c->id);
        $c = ExportRegister::issue($c->id);

        $trail = DB::table('certificate_events')
            ->where('certificate_type', 'eac')->where('certificate_id', $c->id)
            ->orderBy('id')->get();

        $this->assertNotEmpty($trail);
        $this->assertContains('issued', $trail->pluck('event')->all());
    }

    /* ──────────────────────────── Readiness ────────────────────────────── */

    public function test_a_bare_consignment_is_not_ready_and_says_which_parts_it_cannot_judge(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());

        $r = ExportRegister::readiness($c->id);

        $this->assertLessThan(100, (int) round($r['total'] / max($r['max'], 1) * 100));

        // Nothing has been packed, insured or booked, so those three cannot be
        // scored at all — they must not quietly contribute full marks.
        foreach (['packaging', 'insurance', 'logistics'] as $key) {
            $this->assertSame(0, $r['categories'][$key]['max'], "[{$key}] claimed something to assess when the register holds nothing.");
            $this->assertSame(0, $r['categories'][$key]['score']);
            $this->assertNotEmpty($r['categories'][$key]['basis']);
        }

        // Compliance is different: an unassessed declaration is a real failing,
        // not an absence, because the piece cannot lawfully leave without one.
        $this->assertGreaterThan(0, $r['categories']['compliance']['max']);
        $this->assertSame(0, $r['categories']['compliance']['score']);

        $this->assertNotSame('EXCELLENT', strtoupper($r['rating']));
    }

    public function test_real_packaging_insurance_and_logistics_raise_their_categories_and_change_the_basis(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());
        ExportRegister::approve($c->id);

        $before = ExportRegister::readiness($c->id);

        ExportRegister::ship($c->id, [
            'carrier'             => 'DHL Express',
            'service'             => 'Fine Art Priority',
            'awb_no'              => '123-45678901',
            'tracking_no'         => 'DHL9981772',
            'port_of_exit'        => 'Douala International Airport',
            'expected_at'         => now()->addDays(6),
            'package_count'       => 1,
            'crate_ref'           => 'CRT-2026-0091',
            'gross_weight_kg'     => 18.400,
            'net_weight_kg'       => 12.150,
            'dimensions'          => '80 x 40 x 40 cm',
            'shock_protection'    => true,
            'climate_protection'  => true,
            'humidity_protection' => true,
        ]);

        DB::table('ownership_transfers')->insert([
            'uuid'               => (string) \Illuminate\Support\Str::uuid(),
            'certificate_no'     => 'AH237-OTC-CM-2026-000000000777',
            'product_id'         => $product->id,
            'status'             => 'active',
            'insurer_name'       => 'Chartis Fine Art',
            'insurance_policy_no' => 'FA-2026-88120',
            'insurance_value'    => 4800,
            'insurance_currency' => 'EUR',
            'coverage_start'     => now()->subDay(),
            'coverage_end'       => now()->addMonth(),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $after = ExportRegister::readiness($c->id);

        foreach (['packaging', 'insurance', 'logistics'] as $key) {
            $this->assertGreaterThan(0, $after['categories'][$key]['max'], "[{$key}] stayed unassessable after real data arrived.");
            $this->assertGreaterThan($before['categories'][$key]['score'], $after['categories'][$key]['score']);
            $this->assertNotSame(
                $before['categories'][$key]['basis'],
                $after['categories'][$key]['basis'],
                "[{$key}] gave the same reason for two different scores."
            );
        }

        $this->assertGreaterThan($before['total'], $after['total']);
    }

    public function test_paperwork_and_a_condition_report_each_lift_the_documentation_score(): void
    {
        $product = $this->certifiedProduct();

        $bare = ExportRegister::readiness(ExportRegister::open($product, $this->importer())->id);
        $this->assertSame(0, $bare['categories']['documentation']['score']);

        $c = ExportRegister::open($product, $this->importer(), [
            'origin_certificate_ref' => 'COO-CM-2026-4412',
            'export_permit_no'       => 'EXP-CM-2026-0091',
            'customs_declaration_no' => 'D6-2026-778120',
        ]);

        $withPapers = ExportRegister::readiness($c->id);
        $this->assertGreaterThan(0, $withPapers['categories']['documentation']['score']);
        // Named in both languages rather than in whichever one the app locale
        // happens to be. The basis is written for the reader now, and a check
        // that only ever saw English would not notice the French going missing.
        $this->assertStringContainsString(
            'permit',
            strtolower(ExportRegister::readiness($c->id, 'en')['categories']['documentation']['basis'])
        );
        $this->assertStringContainsString(
            'permis d\'exportation',
            strtolower(ExportRegister::readiness($c->id, 'fr')['categories']['documentation']['basis'])
        );

        // Still short of full marks: nobody has inspected the object.
        $this->assertLessThan(
            $withPapers['categories']['documentation']['max'],
            $withPapers['categories']['documentation']['score']
        );

        ExportRegister::recordCondition($product, [
            'inspector_name' => 'Registrar, Douala Art Handling',
            'inspector_ref'  => 'DAH-2026-0412',
            'surface'        => 'excellent',
            'structural'     => 'excellent',
            'overall'        => 'excellent',
        ], $c->id);

        $withReport = ExportRegister::readiness($c->id);

        $this->assertGreaterThan(
            $withPapers['categories']['documentation']['score'],
            $withReport['categories']['documentation']['score']
        );
        $this->assertStringContainsString(
            'condition report',
            ExportRegister::readiness($c->id, 'en')['categories']['documentation']['basis']
        );
        $this->assertStringContainsString(
            'constat d\'état',
            ExportRegister::readiness($c->id, 'fr')['categories']['documentation']['basis']
        );
    }

    /* ────────────────────────────── Risk ───────────────────────────────── */

    public function test_compliance_risk_is_unassessed_rather_than_low_when_nothing_was_assessed(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());

        $risk = ExportRegister::risk($c->id);

        $this->assertSame('unassessed', $risk['compliance']['level']);
        $this->assertNotSame('low', $risk['compliance']['level']);
        $this->assertNotEmpty($risk['compliance']['basis']);

        // Transit and environmental cannot be judged with no shipment booked.
        $this->assertSame('unassessed', $risk['transit']['level']);
        $this->assertSame('unassessed', $risk['environmental']['level']);
        $this->assertSame('unassessed', $risk['insurance']['level']);
    }

    public function test_a_stolen_report_drives_counterfeit_and_ownership_risk_up(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer());

        ProductFlags::raise($product, 'reported_stolen', ['reference' => 'PV-2026-0091']);
        ProductFlags::raise($product, 'disputed_ownership');

        $risk = ExportRegister::risk($c->id);

        $this->assertSame('high', $risk['counterfeit']['level']);
        $this->assertSame('high', $risk['ownership']['level']);
    }

    public function test_declared_compliance_with_no_protected_materials_reads_as_low(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer(), [
            'cultural_heritage_declaration' => 'compliant',
            'ethical_sourcing_declaration'  => 'compliant',
            'protected_materials'           => 'none',
            'export_permit_no'              => 'EXP-CM-2026-0091',
        ]);

        $risk = ExportRegister::risk($c->id);

        $this->assertSame('low', $risk['compliance']['level']);
    }

    public function test_cites_listed_material_without_a_permit_is_high_compliance_risk(): void
    {
        $product = $this->certifiedProduct();
        $c = ExportRegister::open($product, $this->importer(), [
            'cultural_heritage_declaration' => 'compliant',
            'ethical_sourcing_declaration'  => 'compliant',
            'protected_materials'           => 'cites_listed',
        ]);

        $risk = ExportRegister::risk($c->id);

        $this->assertSame('high', $risk['compliance']['level']);
    }

    /* ──────────────────────────── Exporters ────────────────────────────── */

    public function test_an_exporter_is_created_once_per_business(): void
    {
        $business = $this->makeBusiness();

        $a = ExportRegister::exporterFor($business);
        $b = ExportRegister::exporterFor($business);

        $this->assertSame($a->id, $b->id);
        $this->assertSame($business->id, (int) $a->business_id);
        $this->assertSame(1, DB::table('exporters')->count());

        // Never verified on creation: the platform has checked no licence.
        $this->assertNull($a->verified_at);
    }

    /* ────────────────────────────── Language ───────────────────────────── */

    /*
     * Readiness and risk both print a sentence per line explaining themselves,
     * and those sentences were English on a French certificate. The tests
     * below hold the translation to two conditions: that the French is French,
     * and that none of it moved a number. The second is the one that would do
     * real damage — a consignment whose readiness percentage or risk level
     * depended on which language the page was requested in would be a document
     * that says two different things about the same crate.
     */

    /** A shipped consignment, so every readiness category has something to judge. */
    private function shippedConsignment(): int
    {
        $product = $this->certifiedProduct();

        $c = ExportRegister::open($product, $this->importer(), [
            'origin_certificate_ref'        => 'CO-2026-1',
            'export_permit_no'              => 'PERM-9',
            'customs_declaration_no'        => 'DEC-4',
            'cultural_heritage_declaration' => 'compliant',
            'ethical_sourcing_declaration'  => 'compliant',
            'protected_materials'           => 'none',
        ]);

        ExportRegister::recordCondition($product, ['inspector_name' => 'A. Mbarga', 'overall' => 'good']);
        ExportRegister::approve($c->id);
        ExportRegister::ship($c->id, [
            'carrier' => 'DHL', 'awb_no' => '123-45678901', 'tracking_no' => 'TRK-1',
            'port_of_exit' => 'Douala', 'crate_ref' => 'CR-1',
            'shock_protection' => true, 'climate_protection' => true,
        ]);

        return (int) $c->id;
    }

    public function test_every_readiness_basis_is_written_in_the_readers_language(): void
    {
        $id = $this->shippedConsignment();

        $fr = ExportRegister::readiness($id, 'fr');
        $en = ExportRegister::readiness($id, 'en');

        foreach ($fr['categories'] as $key => $cat) {
            $this->assertNotSame(
                $en['categories'][$key]['basis'],
                $cat['basis'],
                "The {$key} basis is identical in both languages, so one of them is untranslated."
            );

            foreach ([' the ', ' has been ', ' no '] as $marker) {
                $this->assertStringNotContainsString(
                    $marker,
                    ' ' . $cat['basis'] . ' ',
                    "The French {$key} basis still contains the English fragment [{$marker}]."
                );
            }
        }
    }

    public function test_every_risk_basis_is_written_in_the_readers_language(): void
    {
        $id = $this->shippedConsignment();

        $fr = ExportRegister::risk($id, 'fr');
        $en = ExportRegister::risk($id, 'en');

        foreach ($fr as $key => $line) {
            $this->assertNotSame($en[$key]['basis'], $line['basis'], "The {$key} risk basis is untranslated.");

            foreach ([' the ', ' has been ', ' no '] as $marker) {
                $this->assertStringNotContainsString(
                    $marker,
                    ' ' . $line['basis'] . ' ',
                    "The French {$key} risk basis still contains the English fragment [{$marker}]."
                );
            }
        }
    }

    public function test_translating_the_reasoning_does_not_move_a_single_number(): void
    {
        $id = $this->shippedConsignment();

        $fr = ExportRegister::readiness($id, 'fr');
        $en = ExportRegister::readiness($id, 'en');

        // The important one. Same crate, same score, same rating, whichever
        // sheet the border officer is holding.
        $this->assertSame($en['total'], $fr['total']);
        $this->assertSame($en['max'], $fr['max']);
        $this->assertSame($en['rating'], $fr['rating']);

        foreach ($en['categories'] as $key => $cat) {
            $this->assertSame($cat['score'], $fr['categories'][$key]['score'], "The {$key} score moved with the language.");
            $this->assertSame($cat['max'], $fr['categories'][$key]['max'], "The {$key} maximum moved with the language.");
        }

        $frRisk = ExportRegister::risk($id, 'fr');
        foreach (ExportRegister::risk($id, 'en') as $key => $line) {
            $this->assertSame($line['level'], $frRisk[$key]['level'], "The {$key} risk level moved with the language.");
        }
    }

    public function test_an_unassessed_category_is_unassessed_in_both_languages(): void
    {
        // A bare draft: nothing packed, no carrier, no cover declared — the
        // three categories that drop out of the denominator.
        $c = ExportRegister::open($this->certifiedProduct(), $this->importer());

        $fr = ExportRegister::readiness($c->id, 'fr');
        $en = ExportRegister::readiness($c->id, 'en');

        foreach (['packaging', 'insurance', 'logistics'] as $key) {
            $this->assertSame(0, $en['categories'][$key]['max'], "{$key} was assessed in English on a bare draft.");
            $this->assertSame(0, $fr['categories'][$key]['max'], "{$key} was assessed in French on a bare draft.");
            $this->assertSame(0, $fr['categories'][$key]['score']);
        }

        $this->assertSame($en['max'], $fr['max']);
        $this->assertSame($en['rating'], $fr['rating']);

        // And the risk lines that cannot be judged stay unassessed, never low.
        $frRisk = ExportRegister::risk($c->id, 'fr');
        foreach (['transit', 'environmental', 'insurance'] as $key) {
            $this->assertSame('unassessed', $frRisk[$key]['level'], "{$key} risk was judged in French with nothing to judge.");
        }
    }

    public function test_omitting_the_language_returns_what_it_returned_before(): void
    {
        $id = $this->shippedConsignment();

        $this->assertSame(ExportRegister::readiness($id, app()->getLocale()), ExportRegister::readiness($id));
        $this->assertSame(ExportRegister::risk($id, app()->getLocale()), ExportRegister::risk($id));

        // The rating is a key the views translate themselves, so it must not
        // follow the language under any circumstances.
        $this->assertSame(ExportRegister::rating(19, 20), ExportRegister::rating(19, 20, 'fr'));
    }
}
