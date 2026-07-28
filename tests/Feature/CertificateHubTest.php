<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Support\ArtisanVerification;
use App\Support\ExportRegister;
use App\Support\ProductCertificate;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the certificate hub — the index of every document the registry holds
 * for one product.
 *
 * The hub is the only page in the family whose job is to describe absence. Each
 * of the other certificates is issued because something happened; this one has
 * to state, for the five things that did not happen, that they did not happen,
 * and do it without ever tipping over into implying they did. So the assertions
 * are weighted towards the negative cases: a missing export consignment must
 * produce a sentence, not a blank; an unverified artisan must produce "not
 * issued" and not a crash and not a number.
 *
 * The fabrication assertions matter more here than anywhere else, because a hub
 * that invents a certificate number is worse than a certificate that invents
 * one — the reader has come to this page precisely to find out what exists.
 */
class CertificateHubTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product(): Product
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /** A business the platform has actually verified, so an AVC can exist. */
    private function verifiedBusiness(): Business
    {
        $business = $this->makeBusiness();
        $business->forceFill(['id_verified_at' => now()])->save();

        return $business->fresh();
    }

    private function hub(Product $product, string $lang = 'fr')
    {
        return $this->get('/certificats/' . $product->slug . '?lang=' . $lang);
    }

    /* ───────────────────────────── Reachability ────────────────────────── */

    public function test_a_published_product_renders_and_a_draft_does_not(): void
    {
        $this->hub($this->product())->assertOk();

        $draft = $this->product();
        $draft->update(['status' => 'draft']);

        $this->hub($draft->fresh())->assertNotFound();
    }

    public function test_both_languages_render(): void
    {
        $product = $this->product();

        // Escaped, not raw: the type names come through Blade's {{ }} and the
        // French apostrophe is an entity in the delivered HTML.
        $this->hub($product, 'fr')->assertOk()->assertSee("Certificat d'authenticité");
        $this->hub($product, 'en')->assertOk()->assertSee('Certificate of Authenticity');
    }

    /* ──────────────────────────── The whole set ────────────────────────── */

    public function test_all_six_certificate_types_are_listed(): void
    {
        $html = $this->hub($this->product(), 'en')->assertOk()->getContent();

        foreach (['COA', 'PRC', 'PPC', 'AVC', 'OTC', 'EAC'] as $code) {
            $this->assertStringContainsString(
                config("certificate_types.{$code}.name.en"),
                $html,
                "Certificate type [{$code}] is missing from the hub."
            );
        }
    }

    public function test_the_permanent_identifiers_appear(): void
    {
        $product = $this->product();

        $prn = ProvenanceRegistry::prnFor($product);
        $oln = ProvenanceRegistry::olnFor($product);
        $gan = ProvenanceRegistry::ganFor($product->business);

        $html = $this->hub($product->fresh(), 'en')->assertOk()->getContent();

        foreach ([$prn, $oln, $gan] as $identifier) {
            $this->assertStringContainsString($identifier, $html);
        }
    }

    /* ───────────────────────────── Presence ────────────────────────────── */

    public function test_an_issued_certificate_shows_its_real_number_and_links_to_its_document(): void
    {
        $product = $this->product();
        $coa     = ProductCertificate::forProduct($product);

        $html = $this->hub($product->fresh(), 'en')->assertOk()->getContent();

        $this->assertStringContainsString($coa->certificate_no, $html);
        $this->assertStringContainsString('/certificat/' . $product->slug, $html);
        // And its verification address, not only the document.
        $this->assertStringContainsString('/verifier?ref=' . $coa->certificate_no, $html);
    }

    public function test_an_issued_transfer_and_export_are_listed_with_their_own_numbers(): void
    {
        $product = $this->product();

        $transfer = ProvenanceRegistry::transfer($product->fresh(), [
            'legal_name'  => 'Heritage Gallery Douala',
            'entity_type' => 'gallery',
        ]);

        $consignment = ExportRegister::open($product->fresh(), ['name' => 'Musée du Quai Branly', 'country' => 'FR']);
        ExportRegister::issue($consignment->id);

        $html = $this->hub($product->fresh(), 'en')->assertOk()->getContent();

        $this->assertStringContainsString($transfer->certificate_no, $html);
        $this->assertStringContainsString('/certificat-transfert/' . $transfer->certificate_no, $html);
        $this->assertStringContainsString($consignment->certificate_no, $html);
        $this->assertStringContainsString('/certificat-export/' . $consignment->certificate_no, $html);
    }

    /* ───────────────────────────── Absence ─────────────────────────────── */

    public function test_a_type_with_no_record_renders_an_explicit_not_issued_state(): void
    {
        $product = $this->product();

        $html = $this->hub($product, 'en')->assertOk()->getContent();

        $this->assertStringContainsString('Not issued', $html);
        // The reason, not merely the state: an empty row teaches nothing.
        $this->assertStringContainsString('No export', $html);

        // And nothing that looks like an export or transfer number was minted.
        $this->assertStringNotContainsString('AH237-EAC', $html);
        $this->assertStringNotContainsString('AH237-OTC', $html);
    }

    public function test_an_unverified_artisan_does_not_crash_and_shows_the_avc_as_not_issued(): void
    {
        $product = $this->product();

        // Precondition: this is the ordinary case, not a broken fixture.
        $this->assertNull(ArtisanVerification::forBusiness($product->business));

        $html = $this->hub($product, 'en')->assertOk()->getContent();

        $this->assertStringContainsString('Artisan Verification Certificate', $html);
        $this->assertStringNotContainsString('AH237-AVC', $html);
        $this->assertStringNotContainsString('/certificat-artisan/', $html);
    }

    public function test_a_verified_artisan_shows_the_avc_with_its_number(): void
    {
        $business = $this->verifiedBusiness();
        $product  = $this->makeProduct($business, ['status' => 'published'])->fresh();

        $avc = ArtisanVerification::forBusiness($business);
        $this->assertNotNull($avc);

        $html = $this->hub($product, 'en')->assertOk()->getContent();

        $this->assertStringContainsString($avc->certificate_no, $html);
        $this->assertStringContainsString('/certificat-artisan/' . $business->slug, $html);
    }

    /* ──────────────────────────── Refusals ─────────────────────────────── */

    public function test_the_hub_claims_no_security_feature_the_platform_does_not_have(): void
    {
        $product = $this->product();
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Heritage Gallery Douala']);

        foreach (['fr', 'en'] as $lang) {
            $html = $this->hub($product->fresh(), $lang)->assertOk()->getContent();

            foreach (['Holographic', 'Hologramme', 'UV ', 'NFC', 'Blockchain', 'AI Fingerprint'] as $claim) {
                $this->assertStringNotContainsString(
                    $claim,
                    $html,
                    "The hub advertises [{$claim}], which this platform does not do."
                );
            }
        }
    }

    public function test_a_flag_the_platform_cannot_determine_is_neither_ticked_nor_crossed(): void
    {
        $product = $this->product();

        $html = $this->hub($product, 'en')->assertOk()->getContent();

        // export_restricted is deliberately outside ProductFlags::checks(), so
        // the hub must not report on it in either direction.
        $this->assertStringNotContainsString('Export restricted', $html);
        $this->assertStringNotContainsString('Export ready', $html);
    }

    public function test_the_ownership_chain_and_summary_counts_come_from_the_register(): void
    {
        $product = $this->product();
        ProvenanceRegistry::transfer($product->fresh(), [
            'legal_name'  => 'Heritage Gallery Douala',
            'entity_type' => 'gallery',
        ]);

        $chain = ProvenanceRegistry::chain($product->fresh());
        $this->assertCount(2, $chain);

        $html = $this->hub($product->fresh(), 'en')->assertOk()->getContent();

        $this->assertStringContainsString('Heritage Gallery Douala', $html);
        $this->assertStringContainsString((string) DB::table('product_ownerships')
            ->where('product_id', $product->id)->count(), $html);
    }
}
