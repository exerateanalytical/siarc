<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Support\ProductCertificate;
use App\Support\ProductFlags;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the Product Registration Certificate.
 *
 * The artwork this page replicates lists a column of security features the
 * platform does not have — a holographic seal, UV reactive ink, invisible
 * fibres, an AI fingerprint, a C2PA provenance reference. Rendering any of them
 * would be the certificate asserting a measure that was never taken, which is
 * worse than a plainer document: a buyer reads a printed claim as a checked
 * one. These tests exist so that nobody restores those rows later while tidying
 * the page back towards the design.
 *
 * The second property defended here is the same one the flag register enforces:
 * an absent key in the tick map means undeterminable, and undeterminable must
 * render as nothing at all rather than as a reassuring green tick.
 */
class ProductRegistrationCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function publishedProduct(): Product
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    private function url(Product $product, string $lang = 'fr'): string
    {
        return '/certificat-enregistrement/' . $product->slug . '?lang=' . $lang;
    }

    public function test_it_renders_for_a_published_product(): void
    {
        $product = $this->publishedProduct();

        $this->get($this->url($product))->assertOk();
    }

    public function test_a_draft_product_has_no_registration_certificate(): void
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'draft']);

        $this->get($this->url($product->fresh()))->assertNotFound();
    }

    public function test_it_prints_the_registry_number_and_the_certificate_number(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);
        $prn     = ProvenanceRegistry::prnFor($product);

        $this->get($this->url($product))
            ->assertOk()
            ->assertSee($prn)
            ->assertSee($cert->certificate_no);
    }

    public function test_both_languages_render(): void
    {
        $product = $this->publishedProduct();

        $this->get($this->url($product, 'fr'))->assertOk();
        $this->get($this->url($product, 'en'))->assertOk();
    }

    public function test_it_names_the_issuing_platform(): void
    {
        $product = $this->publishedProduct();
        $body    = $this->get($this->url($product))->assertOk()->getContent();

        $this->assertTrue(
            str_contains($body, 'Artisan Hub 237') || str_contains($body, 'ArtisanHub237'),
            'The certificate must say who issued it.'
        );
    }

    /**
     * The list of things the platform must never claim on this document. Each
     * one is either a physical print property that cannot exist on a screen, or
     * a system that was never built, or an authority the platform does not hold.
     */
    public function test_it_claims_nothing_the_platform_cannot_back(): void
    {
        $product = $this->publishedProduct();

        foreach (['fr', 'en'] as $lang) {
            $body = $this->get($this->url($product, $lang))->assertOk()->getContent();

            foreach ([
                'AI Fingerprint', 'AI Visual', 'Empreinte IA',
                'UV ', 'Holographic', 'Hologramme', 'Holographique',
                'Invisible Watermark', 'Filigrane invisible',
                'Blockchain', 'C2PA',
                'SIARC', 'UNESCO', 'MINAC',
            ] as $forbidden) {
                $this->assertStringNotContainsString(
                    $forbidden,
                    $body,
                    "[{$lang}] The certificate must not print \"{$forbidden}\"."
                );
            }
        }
    }

    /**
     * A raised flag removes its key's reassurance. It must not degrade into an
     * unticked box either — the document simply stops speaking to that point,
     * and says instead that a concern is on file.
     */
    public function test_a_flag_removes_its_tick_rather_than_printing_a_denial(): void
    {
        $product = $this->publishedProduct();

        $before = $this->get($this->url($product, 'en'))->assertOk()->getContent();
        $this->assertStringContainsString('Not reported stolen', $before);

        ProductFlags::raise($product, 'reported_stolen');

        $after = $this->get($this->url($product->fresh(), 'en'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Not reported stolen', $after);
    }

    /**
     * export_restricted is deliberately absent from the tick map, because the
     * platform holds no CITES or heritage-law register and the absence of a
     * flag says nothing. It must therefore never appear as a cleared row.
     */
    public function test_an_undeterminable_check_is_not_rendered_at_all(): void
    {
        $product = $this->publishedProduct();

        $this->assertArrayNotHasKey('export_ready', ProductFlags::checks($product));

        $body = $this->get($this->url($product, 'en'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Export ready', $body);
        $this->assertStringNotContainsString('Export cleared', $body);
    }

    /**
     * The watermark row only exists when a watermark really was applied. The
     * seeded certificates carry no watermark_ref, so the row must be absent.
     */
    public function test_the_watermark_row_is_absent_without_a_watermark(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->assertNull($cert->watermark_ref);

        $body = $this->get($this->url($product, 'en'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Watermark', $body);
    }
}
