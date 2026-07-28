<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Support\ProductCertificate;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the digital product passport — the living record the certificates are
 * snapshots of.
 *
 * Two things are being defended here and they pull in opposite directions.
 *
 * The first is completeness: the passport is the canonical record, so it has to
 * carry the identifiers, the maker's attribution and the state of the chain, or
 * it is not worth publishing at all.
 *
 * The second is restraint. `docs/ahts/20-conflicts.md` item 9 records that the
 * specification marks this record public while it holds the artisan's village
 * and GPS coordinates and the current owner's legal name and city. A permanent
 * public page tying a named craftsperson to their home coordinates is a safety
 * problem, and naming a private collector beside a valuation is how people get
 * targeted. Neither party agreed to that by registering a product. So the tests
 * below assert absence as hard as they assert presence — a regression that
 * starts printing a GPS pair must fail here, not in the field.
 *
 * The third defence is subtler: a page view must not be allowed to write. The
 * certificate's verification counter is a real statistic, and a passport that
 * called verify() on render would inflate it every time a crawler passed.
 */
class ProductPassportTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /**
     * A business carrying exactly the fields the specification would have
     * published and this page refuses to: coordinates, a street address, a
     * phone number and an email.
     */
    private function locatedBusiness(): Business
    {
        $business = $this->makeBusiness(null, ['name_fr' => 'Atelier Ngo Bassong']);

        $business->forceFill([
            'address_fr' => '14 rue du Marché, Quartier Bonabéri',
            'gps_lat'    => 4.0511111,
            'gps_lng'    => 9.7677777,
            'phone'      => '+237699112233',
            'email'      => 'atelier-prive@example.test',
        ])->save();

        return $business->fresh();
    }

    private function product(?Business $business = null): Product
    {
        $product = $this->makeProduct($business ?? $this->locatedBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    private function passport(Product $product, string $lang = 'fr')
    {
        return $this->get('/passeport/' . $product->slug . ($lang === 'fr' ? '' : '?lang=' . $lang));
    }

    /* ───────────────────────────── Reachability ────────────────────────── */

    public function test_a_published_product_renders_and_a_draft_does_not(): void
    {
        $product = $this->product();
        $this->passport($product)->assertOk();

        $draft = $this->makeProduct($this->locatedBusiness());
        $draft->update(['status' => 'draft']);

        $this->passport($draft->fresh())->assertNotFound();
    }

    public function test_both_languages_render(): void
    {
        $product = $this->product();

        $this->passport($product, 'fr')->assertOk();
        $this->passport($product, 'en')->assertOk();
    }

    /* ─────────────────────────── What it must show ─────────────────────── */

    public function test_it_shows_the_permanent_identifiers_and_names_the_maker(): void
    {
        $business = $this->locatedBusiness();
        $product  = $this->product($business);

        $html = $this->passport($product)->assertOk()->getContent();

        $this->assertStringContainsString(ProvenanceRegistry::prnFor($product->fresh()), $html);
        $this->assertStringContainsString(ProvenanceRegistry::olnFor($product->fresh()), $html);
        $this->assertStringContainsString(ProvenanceRegistry::ganFor($business), $html);

        // Attribution is the point of the platform, and the artisan opted into
        // it by registering the piece. The maker is named; only their location
        // is coarsened.
        $this->assertStringContainsString('Atelier Ngo Bassong', $html);
    }

    public function test_it_states_that_it_is_the_live_record_and_carries_a_last_updated_stamp(): void
    {
        $product = $this->product();

        $fr = $this->passport($product)->assertOk();
        $fr->assertSee('Dernière mise à jour', false);
        $fr->assertSee('instantané', false);

        $en = $this->passport($product, 'en')->assertOk();
        $en->assertSee('Last updated', false);
        $en->assertSee('snapshot', false);
    }

    /* ──────────────────────── What it must never show ──────────────────── */

    public function test_it_withholds_the_workshop_location_and_contact_details(): void
    {
        $business = $this->locatedBusiness();
        $product  = $this->product($business);

        $html = $this->passport($product)->assertOk()->getContent();

        // Coordinates, in every form the page could plausibly print them.
        $this->assertStringNotContainsString('4.0511111', $html);
        $this->assertStringNotContainsString('9.7677777', $html);
        $this->assertStringNotContainsString('4.05111', $html);
        $this->assertStringNotContainsString('9.76777', $html);

        // The street address, the village line within it, and the contacts.
        $this->assertStringNotContainsString('rue du Marché', $html);
        $this->assertStringNotContainsString('Bonabéri', $html);
        $this->assertStringNotContainsString('+237699112233', $html);
        $this->assertStringNotContainsString('atelier-prive@example.test', $html);
    }

    public function test_an_individual_owner_is_not_named_but_an_institution_is(): void
    {
        $individual = $this->product();

        ProvenanceRegistry::transfer($individual, [
            'legal_name'         => 'Marguerite Delacroix-Fontaine',
            'entity_type'        => 'individual',
            'country_code'       => 'FR',
            'address'            => '8 rue de Sèvres, Paris',
            'verification_level' => 'declared',
        ]);

        $html = $this->passport($individual)->assertOk()->getContent();

        // A private collector's legal name, home city and street never appear.
        $this->assertStringNotContainsString('Marguerite Delacroix-Fontaine', $html);
        $this->assertStringNotContainsString('rue de Sèvres', $html);

        // But the fact of the transfer, and the country, still do: withholding
        // the person must not amount to hiding that the piece changed hands.
        $this->assertStringContainsString('FR', $html);

        $institutional = $this->product();

        ProvenanceRegistry::transfer($institutional, [
            'legal_name'         => 'Musée National de Yaoundé',
            'entity_type'        => 'museum',
            'country_code'       => 'CM',
            'verification_level' => 'institution',
        ]);

        $this->passport($institutional)
            ->assertOk()
            ->assertSee('Musée National de Yaoundé', false);
    }

    /* ───────────────────────── A view must not write ───────────────────── */

    public function test_loading_the_page_does_not_increment_the_verification_counter(): void
    {
        $product = $this->product();
        $cert    = ProductCertificate::forProduct($product);

        $before = (int) DB::table('product_certificates')->where('id', $cert->id)->value('verification_count');

        $this->passport($product)->assertOk();
        $this->passport($product, 'en')->assertOk();

        $after = (int) DB::table('product_certificates')->where('id', $cert->id)->value('verification_count');

        $this->assertSame($before, $after, 'Rendering the passport must not count as a verification.');
    }

    /* ──────────────────────────── No overclaiming ──────────────────────── */

    public function test_it_claims_no_technology_the_platform_does_not_have(): void
    {
        $product = $this->product();

        foreach (['fr', 'en'] as $lang) {
            $html = $this->passport($product, $lang)->assertOk()->getContent();

            foreach (['Holographic', 'UV ', 'NFC', 'Blockchain', 'AI Fingerprint'] as $claim) {
                $this->assertStringNotContainsString($claim, $html, "Passport must not claim: {$claim}");
            }
        }
    }
}
