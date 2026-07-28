<?php

namespace Tests\Feature;

use App\Support\WorkshopRegister;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the printed Workshop Verification Certificate.
 *
 * The register beneath this document is already guarded by WorkshopRegisterTest;
 * what is guarded here is the one thing a register cannot protect itself from,
 * which is a view that reads it generously. Three failures matter more than the
 * rest, and each has its own test below.
 *
 * The first is arithmetic that does not match its source. The sheet prints a
 * quality total, and the only defensible total is the one assessment() returned
 * — not a percentage recomputed from a rounded figure, and certainly not the
 * artwork's 108/120, which is a number a designer typed.
 *
 * The second is the flattening of "unassessed" into a score. A dimension the
 * register could not judge comes back with a maximum of zero; rendering that as
 * 0/20 turns the platform's own missing data into a finding against the
 * workshop, and dropping it entirely hides that anything was left unlooked at.
 *
 * The third is the same failure on the compliance table, where it is worse: a
 * licence nobody checked printed beside a licence that passed, in the same
 * typography, is a document telling an importer that eight obligations were
 * verified when six were. Two of the eight rows here are unassessed on purpose.
 *
 * The remaining tests are the family's standing prohibitions, asserted on
 * visible text rather than HTML: no security claim the platform cannot keep, and
 * no drawn signature over an officer's name. The inspector is real and is
 * printed as a named text entry with their reference — that is evidence. A
 * signature block is theatre, and three of the four officers the artwork names
 * do not exist.
 */
class WorkshopCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /**
     * A verified workshop with a certificate, built exactly the way the seeded
     * preview is: measured infrastructure, a real inventory, eight compliance
     * rows of which two were deliberately never assessed, and one on-site
     * inspection that scored five of the seven dimensions.
     *
     * @return array{0:object,1:object}
     */
    private function certifiedWorkshop(): array
    {
        $w = WorkshopRegister::openFor($this->makeBusiness(), [
            'name'                  => 'Atelier de sculpture Bamoun',
            'total_area_m2'         => 420.5,
            'production_rooms'      => 3,
            'storage_areas'         => 2,
            'water_supply'          => 'mains',
            'electricity_supply'    => 'grid',
            'accessibility'         => 'good',
            'fire_safety_equipment' => true,
            'emergency_exits'       => true,
            'master_artisans'       => 4,
            'skilled_workers'       => 11,
            'apprentices'           => 6,
            'female_workers'        => 7,
            'youth_workers'         => 9,
            'renewable_materials'   => true,
            'waste_management'      => true,
            'recycling'             => true,
            'energy_efficiency'     => 'good',
        ]);

        foreach ([
            ['category' => 'major_machine', 'label' => 'Scie à ruban', 'quantity' => 1],
            ['category' => 'hand_tool',     'label' => 'Gouges',       'quantity' => 24],
            ['category' => 'safety',        'label' => 'Extincteurs',  'quantity' => 3],
        ] as $item) {
            WorkshopRegister::recordEquipment($w->id, $item);
        }

        WorkshopRegister::recordCompliance($w->id, [
            'kind'        => 'business_licence',
            'reference'   => 'RC/DLA/2019/B/1188',
            'issued_on'   => now()->subYears(2),
            'expires_on'  => now()->addYear(),
            'status'      => 'valid',
            'verified_at' => now()->subMonth(),
            'verified_by' => 'Marine T. Ekani',
        ]);

        // The two rows the platform recorded as obligations and never checked.
        WorkshopRegister::recordCompliance($w->id, ['kind' => 'insurance']);
        WorkshopRegister::recordCompliance($w->id, ['kind' => 'environmental']);

        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);

        $inspection = WorkshopRegister::recordInspection($w->id, [
            'reference'            => 'INS-2026-0412',
            'inspected_on'         => now()->subDays(9),
            'inspector_name'       => 'Marine T. Ekani',
            'inspector_ref'        => 'INSP-CM-0071',
            'method'               => 'on_site',
            'findings'             => 'Production area clean, extraction fitted, exits clear.',
            'infrastructure_score' => 17,
            'equipment_score'      => 16,
            'workforce_score'      => 18,
            'safety_score'         => 15,
            'documentation_score'  => 14,
            'outcome'              => 'passed',
            'next_due_on'          => now()->addYear(),
        ]);

        WorkshopRegister::verify($w->id, (int) $inspection->id, 4);

        $certificate = WorkshopRegister::issue($w->id, (int) $inspection->id);

        return [WorkshopRegister::find($w->id), $certificate];
    }

    /** Visible text only: a class name or a script string is not a claim. */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);

        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function fetch(string $gwn, string $lang = 'fr'): array
    {
        $html = $this->get('/certificat-atelier/' . $gwn . '?lang=' . $lang)->assertOk()->getContent();

        return [$html, $this->visibleText($html)];
    }

    /* ─────────────────────────── It exists, or it does not ──────────────── */

    public function test_a_verified_workshop_with_a_certificate_renders(): void
    {
        [$w] = $this->certifiedWorkshop();

        $this->get('/certificat-atelier/' . $w->gwn)->assertOk();
    }

    public function test_an_unknown_workshop_number_is_not_found(): void
    {
        $this->get('/certificat-atelier/AH237-GWN-CM-9999999999')->assertNotFound();
    }

    /**
     * A workshop with no certificate must 404 rather than render an empty
     * sheet. A blank certificate page is still a certificate page, and the URL
     * itself would read as an attestation.
     */
    public function test_a_workshop_with_no_certificate_is_not_found(): void
    {
        $w = WorkshopRegister::openFor($this->makeBusiness(), ['name' => 'Atelier sans certificat']);

        $this->get('/certificat-atelier/' . WorkshopRegister::gwnFor($w->id))->assertNotFound();
    }

    /* ───────────────────────────── What it must quote ───────────────────── */

    public function test_it_prints_the_workshop_number_the_certificate_number_and_the_level(): void
    {
        [$w, $c] = $this->certifiedWorkshop();
        [, $text] = $this->fetch($w->gwn);

        $this->assertStringContainsString($w->gwn, $text, 'The sheet does not print the workshop number.');
        $this->assertStringContainsString($c->certificate_no, $text, 'The sheet does not print its own certificate number.');
        $this->assertMatchesRegularExpression(
            '/(Niveau|Level)\s*' . (int) $c->level . '\b/u',
            $text,
            'The sheet does not print the verification level the register granted.'
        );
    }

    /**
     * The total is the register's, character for character. A view that
     * recomputes it, rounds it, or inherits the artwork's 108/120 is printing a
     * score no method returned.
     */
    public function test_the_quality_total_matches_the_register_exactly(): void
    {
        [$w] = $this->certifiedWorkshop();
        $a   = WorkshopRegister::assessment($w->id);

        [, $text] = $this->fetch($w->gwn);

        $this->assertStringContainsString(
            $a['total'] . '/' . $a['max'],
            $text,
            'The printed quality total is not the one assessment() returned.'
        );
        $this->assertStringNotContainsString('108/120', $text, 'The artwork\'s invented total has been printed.');

        $e = WorkshopRegister::exportReadiness($w->id);
        $this->assertStringContainsString($e['total'] . '/' . $e['max'], $text, 'The printed export readiness total is not the register\'s.');
    }

    /**
     * The dimension the register could not judge. `sustainability` is scored
     * here (the workshop declared practices), so the unassessed one is
     * `compliance`… whichever it turns out to be, the rule is the same: a
     * category with max 0 must be named, must be marked unassessed in words,
     * and must never appear as a fraction over twenty.
     */
    public function test_an_unassessed_dimension_reads_as_unassessed_and_not_as_a_zero(): void
    {
        $w = WorkshopRegister::openFor($this->makeBusiness(), ['name' => 'Atelier peu documenté']);

        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);

        $inspection = WorkshopRegister::recordInspection($w->id, [
            'inspected_on'   => now()->subDays(2),
            'inspector_name' => 'Marine T. Ekani',
            'inspector_ref'  => 'INSP-CM-0071',
            'method'         => 'on_site',
            'findings'       => 'Site visited; most of the facility could not be assessed.',
            'safety_score'   => 12,
            'outcome'        => 'passed',
            'next_due_on'    => now()->addYear(),
        ]);

        WorkshopRegister::verify($w->id, (int) $inspection->id, 2);
        WorkshopRegister::issue($w->id, (int) $inspection->id);

        $a = WorkshopRegister::assessment($w->id);

        $unassessed = array_keys(array_filter($a['categories'], fn ($c) => $c['max'] === 0));
        $this->assertNotEmpty($unassessed, 'This fixture is meant to leave dimensions unassessable.');

        [, $text] = $this->fetch(WorkshopRegister::gwnFor($w->id));

        $this->assertMatchesRegularExpression(
            '/(Non évalué|Not assessed|Jamais évalué|Never assessed)/iu',
            $text,
            'A dimension the register could not judge is not marked unassessed anywhere on the sheet.'
        );
        $this->assertStringNotContainsString('0/20', $text, 'An unassessed dimension has been printed as a zero score.');
        $this->assertStringNotContainsString('0 / 20', $text, 'An unassessed dimension has been printed as a zero score.');
    }

    /**
     * The most important distinction on the sheet. The two never-checked
     * licences must be marked in words as such, and must not be swept into a
     * count of what is valid.
     */
    public function test_an_unassessed_compliance_row_does_not_read_as_compliant(): void
    {
        [$w] = $this->certifiedWorkshop();
        [$html, $text] = $this->fetch($w->gwn);

        $this->assertMatchesRegularExpression(
            '/(Jamais évalué|Never assessed|Non évalué|Not assessed)/iu',
            $text,
            'The unassessed licences are not marked as unassessed.'
        );

        // Marked in the markup as its own state, so the row cannot be styled
        // identically to a valid one by accident later.
        $this->assertStringContainsString('data-compliance="unassessed"', $html);
        $this->assertStringContainsString('data-compliance="valid"', $html);

        $this->assertSame(
            2,
            substr_count($html, 'data-compliance="unassessed"'),
            'The two deliberately unassessed compliance rows are not both rendered as unassessed.'
        );
    }

    /* ─────────────────────── Claims the platform cannot keep ────────────── */

    public function test_it_makes_no_claim_the_platform_cannot_keep(): void
    {
        [$w] = $this->certifiedWorkshop();

        foreach (['fr', 'en'] as $lang) {
            [, $text] = $this->fetch($w->gwn, $lang);

            foreach ([
                'Holographic', 'Hologramme', 'UV ', 'Satellite', 'AI ', 'Fraud',
                'NFC', 'Blockchain', 'punishable', 'Invisible watermark',
            ] as $claim) {
                $this->assertStringNotContainsString(
                    $claim,
                    $text,
                    'The workshop certificate (' . $lang . ') claims "' . $claim . '", which nothing behind it supports.'
                );
            }
        }
    }

    /**
     * No signature block. The inspector must appear, because they are a real,
     * answerable person on a real report — but as a named text entry with a
     * reference, never as a ruled line waiting for a hand, and never beside
     * three officers who do not exist.
     */
    public function test_there_is_no_drawn_signature_block(): void
    {
        [$w] = $this->certifiedWorkshop();
        [, $text] = $this->fetch($w->gwn, 'en');

        foreach (['Regional Officer', 'AHCA Director', 'Workshop Owner Signature', 'Signature of'] as $phrase) {
            $this->assertStringNotContainsString($phrase, $text, 'A named signatory block has been printed.');
        }

        $this->assertStringContainsString('Marine T. Ekani', $text, 'The real inspector is not named.');
        $this->assertStringContainsString('INSP-CM-0071', $text, 'The inspector reference is not printed.');
    }

    public function test_both_languages_render(): void
    {
        [$w] = $this->certifiedWorkshop();

        [, $fr] = $this->fetch($w->gwn, 'fr');
        [, $en] = $this->fetch($w->gwn, 'en');

        $this->assertStringContainsString('CERTIFICAT DE VÉRIFICATION D\'ATELIER', $fr);
        $this->assertStringContainsString('WORKSHOP VERIFICATION CERTIFICATE', $en);
    }

    /**
     * A check the platform does not perform is absent from checks(), and an
     * absent check must render as neither a tick nor a cross. The register
     * carries no satellite, model or fraud keys; if one ever appears on the
     * sheet it can only have been invented by the view.
     */
    public function test_only_the_checks_the_register_performs_are_shown(): void
    {
        [$w] = $this->certifiedWorkshop();
        [$html] = $this->fetch($w->gwn);

        preg_match_all('/data-check="([a-z_]+)"/', $html, $m);

        $performed = array_keys(WorkshopRegister::checks($w->id));

        foreach (array_unique($m[1]) as $printed) {
            $this->assertContains(
                $printed,
                $performed,
                'The sheet shows a check named "' . $printed . '" that the register does not perform.'
            );
        }

        $this->assertNotEmpty($m[1], 'The sheet shows no checks at all.');
    }

    /** A null measurement is an em-dash or an absent row, never a nought. */
    public function test_an_unmeasured_field_is_never_printed_as_zero(): void
    {
        $w = WorkshopRegister::openFor($this->makeBusiness(), ['name' => 'Atelier non mesuré']);

        WorkshopRegister::submit($w->id);
        WorkshopRegister::beginInspection($w->id);

        $inspection = WorkshopRegister::recordInspection($w->id, [
            'inspected_on'   => now()->subDay(),
            'inspector_name' => 'Marine T. Ekani',
            'method'         => 'on_site',
            'safety_score'   => 11,
            'outcome'        => 'passed',
            'next_due_on'    => now()->addYear(),
        ]);

        WorkshopRegister::verify($w->id, (int) $inspection->id, 2);
        WorkshopRegister::issue($w->id, (int) $inspection->id);

        [, $text] = $this->fetch(WorkshopRegister::gwnFor($w->id));

        $this->assertStringNotContainsString('0 m²', $text, 'An unmeasured workshop reports a floor area of nothing.');
    }
}
