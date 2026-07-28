<?php

namespace Tests\Feature;

use App\Support\ProvenanceDossier;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the lifetime register behind the provenance dossier.
 *
 * Two things are being defended here. The first is that the dossier reports and
 * never decorates: an undocumented piece must come back empty — empty journey,
 * zeroed counts, no placeholder country — rather than plausible. The second is
 * the Legacy Index, which is a number printed on a document about somebody's
 * work; it has to move only when real rows move it, stay inside its own bounds,
 * and be able to say why.
 */
class ProvenanceDossierTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product()
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /* ─────────────────────────── Nothing documented ────────────────────── */

    public function test_an_undocumented_product_reports_nothing_it_cannot_show(): void
    {
        $product = $this->product();

        $timeline = ProvenanceDossier::timeline($product);
        $types    = array_column($timeline, 'type');

        // Registration and the maker's founding ownership are real rows, so they
        // may appear. Nothing else may.
        $this->assertNotEmpty($timeline);
        foreach ($types as $type) {
            $this->assertContains($type, ['registration', 'ownership'], "Undocumented product produced a {$type} entry.");
        }

        $summary = ProvenanceDossier::summary($product);
        foreach (['exhibitions', 'restorations', 'museums', 'galleries', 'publications', 'valuations'] as $key) {
            $this->assertSame(0, $summary[$key], "{$key} was not zero on an undocumented product.");
        }
        $this->assertSame(0, $summary['ownership_changes']);
    }

    public function test_the_journey_is_empty_rather_than_a_placeholder_country(): void
    {
        $product = $this->product();

        // The maker's ownership row genuinely records CM, so a journey of one
        // country is honest. What must never happen is a country appearing for
        // a product whose rows carry none.
        DB::table('product_ownerships')->where('product_id', $product->id)->update(['country_code' => null]);
        ProvenanceRegistry::currentOwner($product);
        DB::table('product_ownerships')->where('product_id', $product->id)->update(['country_code' => null]);

        $this->assertSame([], ProvenanceDossier::journey($product));
    }

    /* ───────────────────────────── Recording ───────────────────────────── */

    public function test_recorded_events_appear_in_the_timeline_in_date_order(): void
    {
        $product = $this->product();

        ProvenanceDossier::record($product, 'valuation', [
            'title' => 'Appraisal for insurance', 'started_on' => '2026-05-01',
            'appraiser' => 'M. Fotso', 'valued_on' => '2026-05-01',
            'amount' => '1250000.50', 'currency' => 'XAF', 'purpose' => 'insurance',
        ]);
        ProvenanceDossier::record($product, 'exhibition', [
            'title' => 'Formes du Sud', 'organisation' => 'Institut français', 'country' => 'CM',
            'started_on' => '2026-01-10', 'ended_on' => '2026-02-10',
        ]);
        ProvenanceDossier::record($product, 'restoration', [
            'title' => 'Surface consolidation', 'started_on' => '2026-03-15',
            'restorer' => 'Atelier Nkolbisson', 'performed_on' => '2026-03-15',
            'description' => 'Consolidated a split in the base.', 'materials_used' => 'Hide glue',
        ]);

        $dates = array_values(array_filter(array_column(ProvenanceDossier::timeline($product), 'date')));
        $sorted = $dates;
        sort($sorted);
        $this->assertSame($sorted, $dates, 'The timeline came back out of chronological order.');

        $types = array_column(ProvenanceDossier::timeline($product), 'type');
        $this->assertContains('exhibition', $types);
        $this->assertContains('restoration', $types);
        $this->assertContains('valuation', $types);

        $summary = ProvenanceDossier::summary($product);
        $this->assertSame(1, $summary['exhibitions']);
        $this->assertSame(1, $summary['restorations']);
        $this->assertSame(1, $summary['valuations']);
        $this->assertSame(0, $summary['museums']);

        $this->assertCount(1, ProvenanceDossier::byType($product, 'exhibition'));
    }

    public function test_a_valuation_keeps_its_currency_and_its_exact_amount(): void
    {
        $product = $this->product();

        $event = ProvenanceDossier::record($product, 'valuation', [
            'title' => 'Estate appraisal', 'started_on' => '2026-04-02',
            'appraiser' => 'Cabinet Mbarga', 'valued_on' => '2026-04-02',
            'amount' => '1250000.50', 'currency' => 'XAF', 'purpose' => 'estate',
        ]);

        $row = DB::table('provenance_valuations')->where('provenance_event_id', $event->id)->first();

        $this->assertSame('XAF', $row->currency);
        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $row->currency);
        // Compared as decimal rather than float: the point of the column type is
        // that the last centime survives the round trip.
        $this->assertSame(0, bccomp('1250000.50', (string) $row->amount, 2));
    }

    public function test_evidence_is_private_until_someone_decides_otherwise(): void
    {
        $product = $this->product();

        $id = DB::table('provenance_evidence')->insertGetId([
            'uuid'       => (string) \Illuminate\Support\Str::uuid(),
            'product_id' => $product->id,
            'kind'       => 'purchase_agreement',
            'title'      => 'Acte de vente 2026-04',
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, (int) DB::table('provenance_evidence')->where('id', $id)->value('is_public'));
    }

    /* ──────────────────────────── Legacy Index ─────────────────────────── */

    public function test_a_bare_product_does_not_score_highly_and_says_what_it_cannot_assess(): void
    {
        $product = $this->product();

        $index = ProvenanceDossier::legacyIndex($product);

        $this->assertLessThan(
            0.75 * $index['max'],
            $index['total'],
            'An undocumented product scored as though it were documented.'
        );

        // Nothing is known about exhibition history, conservation or valuation,
        // so those must be declared unassessed rather than quietly scored full.
        foreach (['public_record', 'conservation', 'valuation'] as $key) {
            $this->assertSame(0, $index['categories'][$key]['max'], "{$key} was scored despite having nothing to score.");
            $this->assertSame(0, $index['categories'][$key]['score']);
            $this->assertStringContainsStringIgnoringCase('not', $index['categories'][$key]['basis']);
        }

        $this->assertSame(0, $index['categories']['evidence']['score']);
    }

    public function test_the_index_rises_and_its_reasoning_changes_when_documentation_is_added(): void
    {
        $product = $this->product();
        $before  = ProvenanceDossier::legacyIndex($product);

        $exhibition = ProvenanceDossier::record($product, 'exhibition', [
            'title' => 'Formes du Sud', 'organisation' => 'Institut français',
            'country' => 'CM', 'city' => 'Yaoundé', 'reference_no' => 'IFC-2026-118',
            'started_on' => '2026-01-10', 'ended_on' => '2026-02-10',
        ]);

        DB::table('provenance_evidence')->insert([
            'uuid'                => (string) \Illuminate\Support\Str::uuid(),
            'product_id'          => $product->id,
            'provenance_event_id' => $exhibition->id,
            'kind'                => 'exhibition_document',
            'title'               => 'Catalogue entry, p.44',
            'content_hash'        => str_repeat('a', 64),
            'recorded_at'         => now(),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $after = ProvenanceDossier::legacyIndex($product);

        $this->assertGreaterThan($before['total'], $after['total']);
        $this->assertGreaterThan(0, $after['categories']['public_record']['max']);
        $this->assertGreaterThan(0, $after['categories']['evidence']['score']);

        // The document shows its working, so the working must actually change.
        $this->assertNotSame(
            $before['categories']['public_record']['basis'],
            $after['categories']['public_record']['basis']
        );
        $this->assertNotSame(
            $before['categories']['evidence']['basis'],
            $after['categories']['evidence']['basis']
        );
    }

    public function test_the_index_stays_inside_its_own_bounds(): void
    {
        $product = $this->product();

        // Every kind of documentation the register can hold, piled on.
        for ($i = 0; $i < 6; $i++) {
            ProvenanceDossier::record($product, 'exhibition', [
                'title' => "Show {$i}", 'organisation' => 'Museum', 'country' => 'FR',
                'reference_no' => "REF-{$i}", 'started_on' => '2026-0' . ($i + 1) . '-01',
            ]);
            ProvenanceDossier::record($product, 'restoration', [
                'title' => "Work {$i}", 'restorer' => 'Atelier', 'performed_on' => '2026-06-01',
                'description' => 'Done.', 'materials_used' => 'Glue',
                'before_images' => ['a.jpg'], 'after_images' => ['b.jpg'],
            ]);
            ProvenanceDossier::record($product, 'valuation', [
                'title' => "Appraisal {$i}", 'appraiser' => 'Cabinet', 'appraiser_ref' => 'X1',
                'valued_on' => '2026-06-01', 'amount' => '100.00', 'currency' => 'EUR',
            ]);
            DB::table('provenance_evidence')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(), 'product_id' => $product->id,
                'kind' => 'research', 'title' => "Doc {$i}", 'content_hash' => str_repeat('b', 64),
                'recorded_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            ProvenanceRegistry::transfer($product->fresh(), [
                'legal_name' => "Holder {$i}", 'entity_type' => 'gallery', 'country_code' => 'FR',
            ]);
        }

        $index = ProvenanceDossier::legacyIndex($product);

        $this->assertLessThanOrEqual($index['max'], $index['total']);
        $this->assertGreaterThanOrEqual(0, $index['total']);
        $this->assertNotSame('', $index['band']);

        foreach ($index['categories'] as $key => $category) {
            $this->assertLessThanOrEqual($category['max'], $category['score'], "{$key} exceeded its own maximum.");
            $this->assertGreaterThanOrEqual(0, $category['score']);
        }

        $this->assertSame(
            array_sum(array_column($index['categories'], 'max')),
            $index['max'],
            'The printed maximum is not the sum of the categories it is made of.'
        );
    }

    public function test_the_journey_lists_each_country_once_with_the_reason_it_was_entered(): void
    {
        $product = $this->product();

        ProvenanceRegistry::transfer($product->fresh(), [
            'legal_name' => 'Galerie de Paris', 'entity_type' => 'gallery', 'country_code' => 'FR',
        ], ['transferred_at' => '2026-03-01']);

        ProvenanceDossier::record($product, 'exhibition', [
            'title' => 'Biennale', 'organisation' => 'Museu', 'country' => 'PT', 'started_on' => '2026-04-01',
        ]);
        ProvenanceDossier::record($product, 'loan', [
            'title' => 'Second Paris loan', 'country' => 'FR', 'started_on' => '2026-05-01',
        ]);

        $journey = ProvenanceDossier::journey($product);
        $countries = array_column($journey, 'country');

        $this->assertSame($countries, array_values(array_unique($countries)), 'A country was listed twice.');
        $this->assertContains('FR', $countries);
        $this->assertContains('PT', $countries);

        foreach ($journey as $leg) {
            $this->assertArrayHasKey('reason', $leg);
            $this->assertNotSame('', (string) $leg['reason']);
        }
    }
}
