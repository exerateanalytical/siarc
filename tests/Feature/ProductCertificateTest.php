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

        // The certificate is a poster, so it prints the leading characters of
        // the fingerprint rather than all sixty-four across the page.
        $this->get('/certificat/' . $product->slug)
            ->assertOk()
            ->assertSee($cert->certificate_no)
            ->assertSee(strtoupper(substr($cert->content_hash, 0, 20)));
    }

    /**
     * Truncating the fingerprint on the poster must not mean the full value is
     * nowhere public — two people comparing copies of a certificate need
     * something complete to compare. It lives on the verification page.
     */
    public function test_the_full_fingerprints_are_published_for_checking(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->get('/verifier?ref=' . $cert->certificate_no)
            ->assertOk()
            ->assertSee($cert->content_hash)
            ->assertSee($cert->signature);
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
            'Empreinte visuelle IA', 'AI Visual Fingerprint', 'AI Image Fingerprint',
            'Filigrane', 'Watermark',
            'Blockchain', 'NFC', 'Hologramme', 'Hologram',
            'infalsifiable', 'tamper-proof',
        ] as $unbacked) {
            $this->assertStringNotContainsString($unbacked, $html, "The certificate claims \"{$unbacked}\", which the platform does not implement.");
        }

        // And it states its own limit plainly, in both languages.
        $this->assertStringContainsString('ne prouve pas', $html);
        $this->get('/certificat/' . $product->slug . '?lang=en')
            ->assertOk()
            ->assertSee('does not prove');
    }

    /**
     * The perceptual hash and the signature were removed from the "unbacked"
     * list above because they are now implemented. That is only defensible if
     * they actually compute — a constant, or a value derived from the row id,
     * would be exactly the sort of decorative field the list exists to keep
     * off the page. These two tests are what earn that removal.
     */
    public function test_the_perceptual_hash_is_computed_from_the_photograph(): void
    {
        $product = $this->publishedProduct();

        // Two visibly different pictures must not hash alike, and the same
        // picture re-encoded must still hash the same.
        $black = $this->storeImage($product, imagecreatetruecolor(240, 240));

        // Vertical stripes, not a left-to-right gradient: a difference hash
        // compares each pixel with its right-hand neighbour, so a monotonic
        // ramp and a flat fill both reduce to all-zero bits. Stripes give the
        // alternation the hash is actually built to see.
        $stripes = imagecreatetruecolor(240, 240);
        $white   = imagecolorallocate($stripes, 255, 255, 255);
        for ($x = 0; $x < 240; $x += 60) {
            imagefilledrectangle($stripes, $x, 0, $x + 29, 239, $white);
        }

        $first = ProductCertificate::perceptualHash($product->fresh());
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', (string) $first);

        // Re-encode the very same picture at a different size: the hash holds.
        $wider = imagecreatetruecolor(480, 480);
        imagecopyresampled($wider, $black, 0, 0, 0, 0, 480, 480, 240, 240);
        $this->replaceImage($product, $wider);
        $this->assertSame($first, ProductCertificate::perceptualHash($product->fresh()));

        // A different picture: the hash moves.
        $this->replaceImage($product, $stripes);
        $this->assertNotSame($first, ProductCertificate::perceptualHash($product->fresh()));
    }

    public function test_the_signature_is_keyed_and_not_reproducible_without_the_key(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->assertSame(
            ProductCertificate::signatureFor($cert->certificate_no, $cert->content_hash, $cert->image_phash),
            $cert->signature,
            'The stored signature must recompute from the certified values.'
        );

        // Everything the signature covers is printed on the page. Only the key
        // is not — so an identical input under a different key must not match.
        $forged = hash_hmac('sha256', implode('|', [$cert->certificate_no, $cert->content_hash, $cert->image_phash ?? '']), 'not-the-app-key');
        $this->assertNotSame($cert->signature, $forged);
    }

    /**
     * A replaced photograph keeps its file path, so the content hash alone
     * would not notice. This is the case the perceptual hash exists for.
     */
    public function test_swapping_the_photograph_in_place_supersedes_the_certificate(): void
    {
        $product = $this->publishedProduct();
        $this->storeImage($product, imagecreatetruecolor(240, 240));

        $cert = ProductCertificate::forProduct($product->fresh());
        $this->assertNotNull($cert->image_phash);
        $this->assertSame('valid', ProductCertificate::verify($cert->certificate_no)['status']);

        // Structure, not a flat fill: any two uniform images differ in no
        // left-to-right comparison and so legitimately share a hash.
        $other = imagecreatetruecolor(240, 240);
        $ink   = imagecolorallocate($other, 250, 250, 250);
        for ($x = 0; $x < 240; $x += 60) {
            imagefilledrectangle($other, $x, 0, $x + 29, 239, $ink);
        }
        $this->replaceImage($product, $other);

        // Same product row, same file path, different picture.
        $this->assertSame($cert->content_hash, ProductCertificate::hashFor($product->fresh()));
        $this->assertSame('superseded', ProductCertificate::verify($cert->certificate_no)['status']);
    }

    /** Attaches a cover image to the product and writes the file to disk. */
    private function storeImage(Product $product, \GdImage $gd): \GdImage
    {
        // The product fixture ships its own cover; this one must replace it,
        // or the hash under test would be taken from a file we never wrote.
        DB::table('product_images')->where('product_id', $product->id)->delete();

        $path = 'products/test-' . $product->id . '.png';
        $full = storage_path('app/public/' . $path);

        @mkdir(dirname($full), 0777, true);
        imagepng($gd, $full);

        DB::table('product_images')->insert([
            'product_id' => $product->id,
            'file_path'  => $path,
            'is_cover'   => 1,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $gd;
    }

    /** Overwrites the file behind the existing cover, leaving the row alone. */
    private function replaceImage(Product $product, \GdImage $gd): void
    {
        imagepng($gd, storage_path('app/public/products/test-' . $product->id . '.png'));
    }

    /**
     * A certificate issued by Artisan Hub 237 must speak only for Artisan Hub
     * 237. Naming another organisation on it — a competition the artisan
     * entered, a ministry, a former brand — reads as that body having endorsed
     * the product, which none of them has.
     */
    public function test_the_certificate_names_no_other_organisation(): void
    {
        $business = $this->makeBusiness();
        $business->update(['siarc_code' => 'AD-1', 'status' => 'published']);

        $product = $this->makeProduct($business);
        $product->update(['status' => 'published']);

        foreach (['', '?lang=en'] as $query) {
            $html = $this->get('/certificat/' . $product->slug . $query)->assertOk()->getContent();

            foreach ([
                'SIARC', 'SIAC', 'GVN', 'GVNAC', 'AHCA',
                'MINAC', 'MINCOMMERCE', 'UNESCO', 'Chambre des',
                'Galerie Virtuelle',
            ] as $foreign) {
                $this->assertStringNotContainsString(
                    $foreign,
                    $html,
                    "The certificate names \"{$foreign}\" — it must carry this platform's identifiers only."
                );
            }

            // The artisan's reference is the platform's own, not an imported one.
            $this->assertStringNotContainsString('AD-1', $html);
            $this->assertStringContainsString('Artisan Hub 237', $html);
        }
    }
}
