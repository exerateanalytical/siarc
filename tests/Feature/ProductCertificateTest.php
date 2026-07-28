<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Support\ProductCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the product Certificate of Authenticity.
 *
 * A certificate is a public claim about someone else's work, so the rules that
 * matter are about what it may and may not assert: it must not be issued for an
 * unpublished draft, it must stop reading "valid" once the record it describes
 * has changed, and it must never imply a physical link between the object and
 * the record that the platform cannot actually establish.
 */
class ProductCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function publishedProduct(): Product
    {
        $business = $this->makeBusiness();
        $product  = $this->makeProduct($business);
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    public function test_a_draft_product_gets_no_certificate(): void
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'draft']);

        $this->assertNull(ProductCertificate::forProduct($product->fresh()));
        $this->get('/certificat/' . $product->slug)->assertNotFound();
        $this->assertSame(0, DB::table('product_certificates')->count());
    }

    public function test_the_certificate_is_issued_once_and_reused(): void
    {
        $product = $this->publishedProduct();

        $first  = ProductCertificate::forProduct($product);
        $second = ProductCertificate::forProduct($product->fresh());

        $this->assertSame($first->id, $second->id, 'A second view must not mint a second certificate.');
        $this->assertSame(1, DB::table('product_certificates')->count());
        $this->assertMatchesRegularExpression('/^AHC-COA-\d{4}-\d{9}$/', $first->certificate_no);
    }

    public function test_the_certificate_page_renders_for_the_public(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->get('/certificat/' . $product->slug)
            ->assertOk()
            ->assertSee($cert->certificate_no)
            ->assertSee($cert->content_hash);
    }

    /**
     * The whole value of the hash: a buyer must be told when the record moved on
     * after issue, rather than shown a green tick over stale details.
     */
    public function test_editing_the_product_supersedes_the_certificate(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->assertSame('valid', ProductCertificate::verify($cert->certificate_no)['status']);

        $product->update(['description_fr' => 'Une description entièrement différente.']);

        $this->assertSame('superseded', ProductCertificate::verify($cert->certificate_no)['status']);
    }

    public function test_verification_distinguishes_its_failure_modes(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        // A reference nobody issued is not the same as a wrong PIN, and a buyer
        // needs to tell them apart.
        $this->assertSame('notfound', ProductCertificate::verify('AHC-COA-2026-999999999')['status']);
        $this->assertSame('pin_mismatch', ProductCertificate::verify($cert->certificate_no, 'NOPE1234')['status']);
        $this->assertSame('valid', ProductCertificate::verify($cert->certificate_no, $cert->verification_pin)['status']);
    }

    public function test_a_revoked_certificate_never_reads_as_valid(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        DB::table('product_certificates')->where('id', $cert->id)
            ->update(['revoked_at' => now(), 'revoked_reason' => 'Test']);

        $this->assertSame('revoked', ProductCertificate::verify($cert->certificate_no)['status']);
    }

    public function test_verification_is_counted(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        ProductCertificate::verify($cert->certificate_no);
        ProductCertificate::verify($cert->certificate_no);

        $row = DB::table('product_certificates')->find($cert->id);
        $this->assertSame(2, $row->verification_count);
        $this->assertNotNull($row->last_verified_at);
    }

    /**
     * The specification this was built from asks for AI fingerprints, invisible
     * watermarks and cryptographic signatures. None exist, so the certificate
     * must not name them — an empty field under such a heading reads as a
     * guarantee nobody made.
     */
    public function test_the_certificate_claims_nothing_the_platform_cannot_back(): void
    {
        $product = $this->publishedProduct();
        $html = $this->get('/certificat/' . $product->slug)->assertOk()->getContent();

        foreach ([
            'Empreinte visuelle IA', 'AI Visual Fingerprint',
            'Filigrane invisible', 'Invisible Watermark',
            'Signature numérique', 'Digital Signature',
            'Blockchain', 'Perceptual Image Hash',
        ] as $unbacked) {
            $this->assertStringNotContainsString($unbacked, $html, "The certificate claims \"{$unbacked}\", which the platform does not implement.");
        }

        // And it states its own limit plainly, in both languages.
        $this->assertStringContainsString('ne prouve pas', $html);
        $this->get('/certificat/' . $product->slug . '?lang=en')
            ->assertOk()
            ->assertSee('does not prove');
    }
}
