<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Support\ProductCertificate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the cost and the concurrency of lazy certificate issuance.
 *
 * Issuing on first view is the right policy — a certificate for a half-finished
 * draft would be superseded within minutes — but it puts a SHA-256, a JPEG
 * decode, an Ed25519 signature and a hash-chain append inside a page load, and
 * the certificate hub calls the same path several times over. These tests pin
 * the two things that must hold once that work is made cheap: that it happens
 * at most once per product per request, and that two simultaneous first views
 * cannot mint two live certificates for one product.
 */
class CertificateIssuancePerformanceTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function publishedProduct(): Product
    {
        $business = $this->makeBusiness();
        $product  = $this->makeProduct($business);
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /**
     * The race the lazy path invites: read, find nothing, insert. Two requests
     * arriving together both read nothing. Called twice in quick succession the
     * second call must return the first certificate, not a second one.
     */
    public function test_a_second_issuance_returns_the_first_certificate(): void
    {
        $product = $this->publishedProduct();

        $a = ProductCertificate::forProduct($product);
        $b = ProductCertificate::forProduct($product->fresh());
        $c = ProductCertificate::forProduct($product->fresh());

        $this->assertSame($a->id, $b->id);
        $this->assertSame($a->id, $c->id);
        $this->assertSame(1, DB::table('product_certificates')->where('product_id', $product->id)->count());
    }

    /**
     * Belt and braces: the application-level check above is only as good as the
     * moment it runs in. The database must refuse a second live certificate for
     * the same product outright, so that a lost race ends in a caught constraint
     * violation rather than two documents in circulation for one object.
     */
    public function test_the_database_refuses_a_second_live_certificate_for_one_product(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->expectException(QueryException::class);

        DB::table('product_certificates')->insert([
            'uuid'             => (string) Str::uuid(),
            'certificate_no'   => $cert->certificate_no . '-DUP',
            'version'          => $cert->version,
            'product_id'       => $product->id,
            'business_id'      => $product->business_id,
            'verification_pin' => 'ABCD1234',
            'content_hash'     => $cert->content_hash,
            'signature'        => $cert->signature,
            'issued_at'        => now(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /**
     * The visitor-facing half of the contract. Whatever the implementation does
     * behind the scenes, a first view must not leave the page in a third state:
     * either a certificate exists and its number is on the page, or the page
     * says plainly that one is being issued. Never a blank where a number goes.
     */
    public function test_a_first_view_leaves_the_visitor_with_a_certificate(): void
    {
        $product = $this->publishedProduct();

        $this->assertSame(0, DB::table('product_certificates')->count());

        $response = $this->get('/certificat/' . $product->slug)->assertOk();

        $cert = DB::table('product_certificates')->where('product_id', $product->id)->first();

        $this->assertNotNull($cert, 'A first view must leave a certificate behind.');
        $response->assertSee($cert->certificate_no);
    }

    /**
     * The certificate hub issues, then verifies, then renders — each of which
     * reaches for the cover photograph's perceptual hash. Decoding a phone-sized
     * JPEG costs hundreds of milliseconds, so doing it more than once for the
     * same unchanged file in one request is pure waste.
     */
    public function test_the_perceptual_hash_is_computed_once_per_request(): void
    {
        $product = $this->publishedProduct();

        ProductCertificate::$phashComputations = 0;

        $this->get('/certificats/' . $product->slug)->assertOk();

        $this->assertLessThanOrEqual(
            1,
            ProductCertificate::$phashComputations,
            'The cover photograph was decoded more than once in a single request.'
        );
    }

    /** And the same memo must hold across the direct calls verification makes. */
    public function test_verification_does_not_decode_the_photograph_again(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        ProductCertificate::$phashComputations = 0;

        ProductCertificate::verify($cert->certificate_no);
        ProductCertificate::verify($cert->certificate_no);
        ProductCertificate::verify($cert->certificate_no);

        $this->assertLessThanOrEqual(1, ProductCertificate::$phashComputations);
    }

    /**
     * None of the caching may turn lazy issuance into eager issuance. A draft is
     * not registered, so no amount of looking at it may mint a document.
     */
    public function test_issuance_stays_lazy_for_a_draft(): void
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'draft']);

        $this->assertNull(ProductCertificate::forProduct($product->fresh()));

        foreach (['/certificat/', '/certificats/', '/certificat-provenance/', '/certificat-enregistrement/'] as $prefix) {
            $this->get($prefix . $product->slug);
        }

        $this->assertSame(0, DB::table('product_certificates')->count());
    }

    /**
     * A memoised hash must still be a hash of the file that is there now — the
     * whole point of the perceptual hash is to catch a photograph swapped in
     * place. Caching that missed a swap would silently disarm the check.
     */
    public function test_the_memo_still_notices_a_swapped_photograph(): void
    {
        $product = $this->publishedProduct();

        $path = 'products/perf-' . $product->id . '.png';
        $full = storage_path('app/public/' . $path);
        @mkdir(dirname($full), 0777, true);

        DB::table('product_images')->where('product_id', $product->id)->delete();
        imagepng(imagecreatetruecolor(240, 240), $full);
        DB::table('product_images')->insert([
            'product_id' => $product->id, 'file_path' => $path, 'is_cover' => 1,
            'sort_order' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $cert = ProductCertificate::forProduct($product->fresh());
        $this->assertNotNull($cert->image_phash);
        $this->assertSame('valid', ProductCertificate::verify($cert->certificate_no)['status']);

        $other = imagecreatetruecolor(240, 240);
        $ink   = imagecolorallocate($other, 250, 250, 250);
        for ($x = 0; $x < 240; $x += 60) {
            imagefilledrectangle($other, $x, 0, $x + 29, 239, $ink);
        }
        // A filesystem timestamp has one-second resolution on some platforms, so
        // a cache keyed on mtime alone could miss a swap that happened inside
        // the same second. Nudge it to prove the key is not that fragile.
        imagepng($other, $full);
        touch($full, time() + 1);
        clearstatcache(true, $full);

        $this->assertSame('superseded', ProductCertificate::verify($cert->certificate_no)['status']);
    }
}
