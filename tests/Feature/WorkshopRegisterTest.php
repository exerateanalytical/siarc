<?php

namespace Tests\Feature;

use App\Support\CertificationAuthority;
use App\Support\WorkshopRegister;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the register behind the Workshop Verification Certificate.
 *
 * The certificate asserts that a production facility was inspected. Almost every
 * way that document can be wrong is a way of asserting more than the register
 * holds, so these tests are written to catch generosity rather than to confirm
 * the happy path:
 *
 *  - a workshop cannot become verified without an inspection that passed, and
 *    cannot be certified before it is verified, because a certificate issued off
 *    an unverified workshop is exactly the mock the design started as;
 *  - a bare workshop must score badly and must say which dimensions it could not
 *    judge at all, rather than averaging an absence into a flattering number;
 *  - a workshop nobody measured must not report zero square metres;
 *  - compliance rows default to unassessed, never valid.
 */
class WorkshopRegisterTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function workshop(array $data = []): object
    {
        return WorkshopRegister::openFor($this->makeBusiness(), array_merge([
            'name' => 'Atelier de sculpture Bamoun',
        ], $data));
    }

    /** A workshop taken all the way to verified through a real passed inspection. */
    private function verifiedWorkshop(): array
    {
        $w = $this->workshop();

        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);

        $inspection = WorkshopRegister::recordInspection($w->id, [
            'inspected_on'         => now()->subDays(3),
            'inspector_name'       => 'Inspecteur, Chambre des Métiers',
            'inspector_ref'        => 'CM-INS-2026-0412',
            'method'               => 'on_site',
            'findings'             => 'Production area clean, extraction fitted, exits clear.',
            'infrastructure_score' => 16,
            'safety_score'         => 14,
            'outcome'              => 'passed',
            'next_due_on'          => now()->addYear(),
        ]);

        WorkshopRegister::verify($w->id, (int) $inspection->id);

        return [WorkshopRegister::find($w->id), $inspection];
    }

    /* ──────────────────────────── Identifiers ──────────────────────────── */

    public function test_the_workshop_number_is_assigned_once_and_survives_a_renamed_business(): void
    {
        $business = $this->makeBusiness();
        $w = WorkshopRegister::openFor($business, ['name' => 'Atelier Foumban']);

        $gwn = WorkshopRegister::gwnFor($w->id);

        $this->assertMatchesRegularExpression('/^AH237-GWN-CM-\d{10}$/', $gwn);

        $business->update(['name_fr' => 'Coopérative Foumban Réunie']);
        DB::table('workshops')->where('id', $w->id)->update(['name' => 'Atelier Foumban II']);

        $this->assertSame($gwn, WorkshopRegister::gwnFor($w->id));
        $this->assertSame($gwn, WorkshopRegister::find($w->id)->gwn);
    }

    public function test_opening_a_workshop_carries_over_what_the_business_knows_and_invents_nothing_else(): void
    {
        $business = $this->makeBusiness(null, [
            'address_fr'       => 'Quartier Njissé, Foumban',
            'gps_lat'          => 5.7256000,
            'gps_lng'          => 10.8998000,
            'year_established' => 1998,
        ]);

        $w = WorkshopRegister::openFor($business, ['name' => 'Atelier Njissé']);

        $this->assertSame('draft', $w->status);
        $this->assertSame('Quartier Njissé, Foumban', $w->address);
        $this->assertSame('1998-01-01', (string) $w->established_on);

        // Nothing was measured, so nothing is measured.
        $this->assertNull($w->total_area_m2);
        $this->assertNull($w->production_rooms);
        $this->assertNull($w->master_artisans);
        $this->assertNull($w->fire_safety_equipment);
        $this->assertNull($w->verification_level);
        $this->assertNull($w->verified_at);
        // The business's own coordinates are a claim until someone stands there.
        $this->assertNull($w->geo_verified_at);
    }

    /* ────────────────────────── State transitions ──────────────────────── */

    public function test_a_workshop_cannot_be_verified_without_a_passed_inspection(): void
    {
        $w = $this->workshop();
        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);

        $failed = WorkshopRegister::recordInspection($w->id, [
            'inspected_on'   => now(),
            'inspector_name' => 'Inspecteur',
            'outcome'        => 'failed',
        ]);

        $this->expectException(\DomainException::class);
        WorkshopRegister::verify($w->id, (int) $failed->id);
    }

    public function test_a_workshop_cannot_be_verified_straight_out_of_draft(): void
    {
        $w = $this->workshop();

        $inspection = WorkshopRegister::recordInspection($w->id, [
            'inspected_on'   => now(),
            'inspector_name' => 'Inspecteur',
            'outcome'        => 'passed',
        ]);

        $this->expectException(\DomainException::class);
        WorkshopRegister::verify($w->id, (int) $inspection->id);
    }

    public function test_an_unverified_workshop_gets_no_certificate(): void
    {
        $w = $this->workshop();
        WorkshopRegister::submit($w->id);

        $this->expectException(\DomainException::class);
        WorkshopRegister::issue($w->id);
    }

    public function test_a_revoked_workshop_cannot_be_verified_again_without_reinspection(): void
    {
        [$w] = $this->verifiedWorkshop();

        WorkshopRegister::revoke($w->id, 'Site found abandoned.');

        $this->assertSame('revoked', WorkshopRegister::find($w->id)->status);

        $this->expectException(\DomainException::class);
        WorkshopRegister::submit($w->id);
    }

    public function test_suspension_is_legal_from_verified_and_recorded(): void
    {
        [$w] = $this->verifiedWorkshop();

        WorkshopRegister::suspend($w->id, 'Fire extinguisher expired.');

        $this->assertSame('suspended', WorkshopRegister::find($w->id)->status);
    }

    public function test_the_verification_level_never_exceeds_what_the_evidence_supports(): void
    {
        $w = $this->workshop();
        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);

        // A document review with conditions attached is thin evidence; asking
        // for the top tier off it must not produce the top tier.
        $inspection = WorkshopRegister::recordInspection($w->id, [
            'inspected_on'   => now(),
            'inspector_name' => 'Inspecteur',
            'method'         => 'document_review',
            'outcome'        => 'passed_with_conditions',
        ]);

        $w = WorkshopRegister::verify($w->id, (int) $inspection->id, 5);

        $this->assertLessThan(5, (int) $w->verification_level);
        $this->assertGreaterThanOrEqual(1, (int) $w->verification_level);
    }

    /* ─────────────────────── Numbering and signing ─────────────────────── */

    public function test_a_certificate_is_numbered_hashed_and_signed_by_the_authority(): void
    {
        [$w] = $this->verifiedWorkshop();

        $c = WorkshopRegister::issue($w->id);

        $this->assertMatchesRegularExpression('/^AH237-WVC-CM-\d{4}-\d{10}$/', $c->certificate_no);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $c->content_hash);
        $this->assertNotEmpty($c->verification_pin);
        $this->assertNotNull($c->issued_at);
        // The inspection had a next_due_on, so the certificate lapses with it.
        $this->assertNotNull($c->expires_at);

        if (CertificationAuthority::isConfigured()) {
            $issuedAt = \Illuminate\Support\Carbon::parse($c->issued_at)->toIso8601String();

            $this->assertTrue(CertificationAuthority::verifyCertificate(
                'wvc', $c->certificate_no, $c->content_hash, $issuedAt, $c->ca_signature
            ));

            // The type is inside the signed payload: a workshop certificate must
            // not verify as an artisan verification certificate.
            $this->assertFalse(CertificationAuthority::verifyCertificate(
                'avc', $c->certificate_no, $c->content_hash, $issuedAt, $c->ca_signature
            ));
        }
    }

    public function test_verification_finds_the_certificate_and_counts_the_look_up(): void
    {
        [$w] = $this->verifiedWorkshop();
        $c = WorkshopRegister::issue($w->id);

        $this->assertSame('valid', WorkshopRegister::verify($c->certificate_no, $c->verification_pin)['status']);
        $this->assertSame('pin_mismatch', WorkshopRegister::verify($c->certificate_no, 'WRONGPIN')['status']);
        $this->assertSame('notfound', WorkshopRegister::verify('AH237-WVC-CM-2026-0000999999')['status']);

        $this->assertSame(1, (int) DB::table('workshop_certificates')->where('id', $c->id)->value('verification_count'));
    }

    public function test_an_expired_certificate_reports_expired_even_though_its_column_says_active(): void
    {
        [$w] = $this->verifiedWorkshop();
        $c = WorkshopRegister::issue($w->id);

        DB::table('workshop_certificates')->where('id', $c->id)
            ->update(['expires_at' => now()->subDay(), 'status' => 'active']);

        $result = WorkshopRegister::verify($c->certificate_no);

        $this->assertSame('expired', $result['status']);
    }

    public function test_a_revoked_certificate_reports_revoked(): void
    {
        [$w] = $this->verifiedWorkshop();
        $c = WorkshopRegister::issue($w->id);

        DB::table('workshop_certificates')->where('id', $c->id)->update(['status' => 'revoked']);

        $this->assertSame('revoked', WorkshopRegister::verify($c->certificate_no)['status']);
    }

    public function test_the_audit_trail_records_issuance(): void
    {
        [$w] = $this->verifiedWorkshop();
        $c = WorkshopRegister::issue($w->id);

        $trail = DB::table('certificate_events')
            ->where('certificate_type', 'wvc')->where('certificate_id', $c->id)
            ->orderBy('id')->get();

        $this->assertNotEmpty($trail);
        $this->assertContains('issued', $trail->pluck('event')->all());
    }

    /* ───────────────────────────── Assessment ──────────────────────────── */

    public function test_a_bare_workshop_does_not_score_well_and_names_what_it_cannot_judge(): void
    {
        $w = $this->workshop();

        $a = WorkshopRegister::assessment($w->id);

        $this->assertNotSame('excellent', $a['rating']);

        // Nothing has been recorded, so these four have nothing to assess. They
        // must not contribute full marks, and must not contribute a silent zero
        // out of a maximum either.
        foreach (['equipment', 'compliance', 'sustainability', 'documentation'] as $key) {
            $this->assertSame(0, $a['categories'][$key]['max'], "[{$key}] claimed something to assess when the register holds nothing.");
            $this->assertSame(0, $a['categories'][$key]['score']);
            $this->assertNotEmpty($a['categories'][$key]['basis']);
        }

        if ($a['max'] > 0) {
            $this->assertLessThan(100, (int) round($a['total'] / $a['max'] * 100));
        }
    }

    public function test_a_workshop_with_no_measured_floor_area_does_not_report_zero_square_metres(): void
    {
        $w = $this->workshop();

        $this->assertNull(WorkshopRegister::find($w->id)->total_area_m2);

        $a = WorkshopRegister::assessment($w->id);

        $this->assertSame(0, $a['categories']['infrastructure']['max']);
        $this->assertStringNotContainsString('0 m²', $a['categories']['infrastructure']['basis']);
        $this->assertStringNotContainsString('0 m2', $a['categories']['infrastructure']['basis']);
    }

    public function test_real_equipment_compliance_and_an_inspection_raise_the_score_and_change_the_basis(): void
    {
        $w = $this->workshop();

        $before = WorkshopRegister::assessment($w->id);

        DB::table('workshops')->where('id', $w->id)->update([
            'total_area_m2'         => 240.50,
            'production_rooms'      => 3,
            'drying_areas'          => 1,
            'packaging_areas'       => 1,
            'water_supply'          => 'Mains, with a 2000L reserve',
            'electricity_supply'    => 'Grid, with generator backup',
            'accessibility'         => 'good',
            'fire_safety_equipment' => true,
            'emergency_exits'       => true,
            'master_artisans'       => 2,
            'skilled_workers'       => 6,
            'apprentices'           => 4,
            'renewable_materials'   => true,
            'waste_management'      => true,
            'energy_efficiency'     => 'good',
        ]);

        WorkshopRegister::recordEquipment($w->id, [
            'category' => 'major_machine', 'label' => 'Band saw', 'quantity' => 1,
        ]);
        WorkshopRegister::recordEquipment($w->id, [
            'category' => 'safety', 'label' => 'Dust extraction hood', 'quantity' => 2,
        ]);

        WorkshopRegister::recordCompliance($w->id, [
            'kind'        => 'business_licence',
            'reference'   => 'RC/FBN/2019/B/221',
            'status'      => 'valid',
            'expires_on'  => now()->addYear(),
            'verified_by' => 'Chambre des Métiers',
            'verified_at' => now(),
        ]);

        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);
        WorkshopRegister::recordInspection($w->id, [
            'inspected_on'         => now(),
            'inspector_name'       => 'Inspecteur, Chambre des Métiers',
            'method'               => 'on_site',
            'findings'             => 'Compliant.',
            'documentation_score'  => 15,
            'infrastructure_score' => 17,
            'outcome'              => 'passed',
        ]);

        $after = WorkshopRegister::assessment($w->id);

        foreach (['infrastructure', 'equipment', 'compliance', 'sustainability', 'documentation'] as $key) {
            $this->assertGreaterThan(0, $after['categories'][$key]['max'], "[{$key}] stayed unassessable after real data arrived.");
            $this->assertGreaterThan($before['categories'][$key]['score'], $after['categories'][$key]['score']);
            $this->assertNotSame(
                $before['categories'][$key]['basis'],
                $after['categories'][$key]['basis'],
                "[{$key}] gave the same reason for two different scores."
            );
        }

        $this->assertGreaterThan($before['total'], $after['total']);
        $this->assertGreaterThan($before['max'], $after['max']);
    }

    /* ───────────────────────────── Compliance ──────────────────────────── */

    public function test_a_compliance_row_defaults_to_unassessed_never_valid(): void
    {
        $w = $this->workshop();

        $row = WorkshopRegister::recordCompliance($w->id, ['kind' => 'fire_safety']);

        $this->assertSame('unassessed', $row->status);
        $this->assertNotSame('valid', $row->status);
        $this->assertNull($row->verified_at);
        $this->assertNull($row->verified_by);
    }

    /* ──────────────────────── Checks and readiness ─────────────────────── */

    public function test_a_check_the_platform_never_performs_is_absent_rather_than_false(): void
    {
        $w = $this->workshop();

        $checks = WorkshopRegister::checks($w->id);

        // The design's "AI & satellite verification" panel has nothing behind
        // it, so those keys must not exist in either direction.
        foreach (['satellite_verified', 'ai_image_match', 'fraud_risk'] as $absent) {
            $this->assertArrayNotHasKey($absent, $checks);
        }

        // A geolocation nobody stood at is not a performed check either.
        $this->assertArrayNotHasKey('site_geolocated', $checks);

        foreach ($checks as $key => $value) {
            $this->assertIsBool($value, "[{$key}] is in the check map but is not a yes/no answer.");
        }
    }

    public function test_export_readiness_is_unassessable_before_anything_is_recorded(): void
    {
        $w = $this->workshop();

        $r = WorkshopRegister::exportReadiness($w->id);

        foreach (['packaging', 'shipping_experience', 'customs', 'traceability'] as $key) {
            $this->assertArrayHasKey($key, $r['categories']);
            $this->assertNotEmpty($r['categories'][$key]['basis']);
        }

        $this->assertSame(0, $r['categories']['packaging']['max']);
        $this->assertSame('unassessed', $r['rating']);
    }
}
