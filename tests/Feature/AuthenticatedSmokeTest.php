<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Quotes\Models\Invoice;
use App\Modules\Quotes\Models\PurchaseOrder;
use App\Modules\Quotes\Models\QuoteProposal;
use App\Modules\Quotes\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Every authenticated surface, loaded as the role that actually uses it.
 *
 * RouteSmokeTest only proves guests don't get a 500 — for dashboard pages that
 * just means the login redirect works, so a broken seller page could ship
 * unnoticed. This walks the same routes while signed in, with real records in
 * the database, which is the only way an undefined variable inside an
 * `@if($business)` branch ever surfaces.
 */
class AuthenticatedSmokeTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function asWebUser(User $user, string $role, bool $isAdmin = false): static
    {
        return $this->withSession(['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name ?? 'Test User',
            'email'    => $user->email,
            'role'     => $role,
            'is_admin' => $isAdmin,
        ]]);
    }

    /** Load each URL and fail loudly on a server error or a rendered exception. */
    private function sweep(array $urls, User $user, string $role, bool $isAdmin = false): void
    {
        $failures = [];

        foreach ($urls as $url) {
            $response = $this->asWebUser($user, $role, $isAdmin)->get($url);
            $status   = $response->getStatusCode();

            if ($status >= 500) {
                $failures[] = "{$url} → {$status}";
                continue;
            }

            // A 200 that contains Laravel's error page still means a broken view.
            if ($status === 200 && preg_match('/(Undefined variable|Undefined array key|Call to undefined|Too few arguments|htmlspecialchars\(\): Argument)/', $response->getContent(), $m)) {
                $failures[] = "{$url} → renders \"{$m[1]}\"";
            }
        }

        $this->assertSame([], $failures, "[{$role}] broken pages:\n  " . implode("\n  ", $failures));
    }

    // ─────────────────────────────────────────────────────────────
    // Seller
    // ─────────────────────────────────────────────────────────────

    public function test_every_seller_page_renders_with_a_shop(): void
    {
        $seller   = $this->makeUser();
        $business = $this->makeBusiness($seller);
        $this->makeProduct($business);

        $this->sweep([
            '/tableau-de-bord/entrepreneur',
            '/tableau-de-bord/produits',
            '/tableau-de-bord/produits?status=published',
            '/tableau-de-bord/produits?status=draft',
            '/tableau-de-bord/produits?q=produit',
            '/tableau-de-bord/produits/nouveau',
            '/tableau-de-bord/devis',
            '/tableau-de-bord/commandes',
            '/tableau-de-bord/commandes?status=confirmed',
            '/tableau-de-bord/messages',
            '/tableau-de-bord/entreprise/modifier',
            '/tableau-de-bord/entreprise/verification',
            '/tableau-de-bord/profil',
            '/tableau-de-bord/securite',
            '/tableau-de-bord/notifications',
            '/tableau-de-bord/support',
            '/tableau-de-bord/sauvegardes',
        ], $seller, 'business_owner');
    }

    /** A brand-new seller has no business yet — the empty state must not blow up. */
    public function test_seller_pages_survive_having_no_shop_yet(): void
    {
        $seller = $this->makeUser();

        $this->sweep([
            '/tableau-de-bord/entrepreneur',
            '/tableau-de-bord/devis',
            '/tableau-de-bord/commandes',
            '/tableau-de-bord/profil',
        ], $seller, 'business_owner');
    }

    public function test_seller_product_edit_page_renders(): void
    {
        $seller   = $this->makeUser();
        $business = $this->makeBusiness($seller);
        $product  = $this->makeProduct($business);

        $this->sweep(["/tableau-de-bord/produits/{$product->slug}/modifier"], $seller, 'business_owner');
    }

    // ─────────────────────────────────────────────────────────────
    // Buyer
    // ─────────────────────────────────────────────────────────────

    public function test_every_buyer_page_renders(): void
    {
        $buyer    = $this->makeUser();
        $business = $this->makeBusiness();

        QuoteRequest::create([
            'buyer_id'    => $buyer->id,
            'business_id' => $business->id,
            'title'       => 'Demande de test',
            'description' => 'Description de test',
            'status'      => 'pending',
        ]);

        $this->sweep([
            '/tableau-de-bord/acheteur',
            '/tableau-de-bord/demandes',
            '/tableau-de-bord/demandes?tab=demandes',
            '/tableau-de-bord/demandes?tab=propositions',
            '/tableau-de-bord/demandes?tab=acceptees',
            '/tableau-de-bord/commandes',
            '/tableau-de-bord/messages',
            '/tableau-de-bord/sauvegardes',
            '/tableau-de-bord/profil',
            '/tableau-de-bord/securite',
            '/tableau-de-bord/notifications',
            '/tableau-de-bord/support',
        ], $buyer, 'buyer');
    }

    public function test_buyer_can_open_an_unquoted_request(): void
    {
        $buyer = $this->makeUser();
        $rfq   = QuoteRequest::create([
            'buyer_id'    => $buyer->id,
            'business_id' => $this->makeBusiness()->id,
            'title'       => 'Sans proposition',
            'description' => 'Aucune proposition reçue pour le moment',
            'status'      => 'pending',
        ]);

        $this->asWebUser($buyer, 'buyer')
            ->get("/tableau-de-bord/demandes/detail?rfq={$rfq->id}")
            ->assertOk()
            ->assertSee('Sans proposition');
    }

    // ─────────────────────────────────────────────────────────────
    // Quote-flow record pages
    // ─────────────────────────────────────────────────────────────

    public function test_quote_record_pages_render_for_both_parties(): void
    {
        $buyer    = $this->makeUser();
        $seller   = $this->makeUser();
        $business = $this->makeBusiness($seller);

        $rfq = QuoteRequest::create([
            'buyer_id'    => $buyer->id,
            'business_id' => $business->id,
            'title'       => 'Commande de test',
            'description' => 'Description',
            'status'      => 'quoted',
        ]);

        $proposal = QuoteProposal::create([
            'quote_request_id' => $rfq->id,
            'version'          => 1,
            'status'           => 'sent',
        ]);
        $proposal->items()->create([
            'name' => 'Article', 'quantity' => 2, 'unit_price' => 50000, 'unit' => 'Pièces',
        ]);
        $proposal->recalculateTotals();

        $order = PurchaseOrder::create([
            'quote_proposal_id' => $proposal->id,
            'status'            => 'confirmed',
            'total'             => $proposal->total,
        ]);
        $invoice = Invoice::create([
            'purchase_order_id' => $order->id,
            'status'            => 'unpaid',
            'total'             => $order->total,
        ]);

        // Buyer side
        $this->sweep([
            "/tableau-de-bord/propositions/detail?proposal={$proposal->id}",
            "/tableau-de-bord/commandes/bon?po={$order->id}",
            "/tableau-de-bord/factures/detail?invoice={$invoice->id}",
        ], $buyer, 'buyer');

        // Seller side, including the proposal builder bound to the real RFQ
        $this->sweep([
            "/tableau-de-bord/propositions/articles?rfq={$rfq->id}",
            "/tableau-de-bord/commandes/bon?po={$order->id}",
        ], $seller, 'business_owner');
    }

    /**
     * Without a record these pages have nothing honest to show, so they must
     * redirect rather than fall back to invented demo content.
     */
    public function test_quote_record_pages_redirect_when_no_record_is_given(): void
    {
        $buyer = $this->makeUser();

        foreach ([
            '/tableau-de-bord/propositions/detail',
            '/tableau-de-bord/commandes/bon',
            '/tableau-de-bord/factures/detail',
            '/tableau-de-bord/propositions/articles',
        ] as $url) {
            $this->asWebUser($buyer, 'buyer')->get($url)->assertRedirect();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Admin
    // ─────────────────────────────────────────────────────────────

    public function test_every_admin_page_renders(): void
    {
        $admin = $this->makeUser();
        $this->makeProduct($this->makeBusiness());

        $urls = [];
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! in_array('GET', $route->methods())) continue;
            if (str_contains($uri, '{')) continue;
            if (! str_starts_with($uri, 'tableau-de-bord/admin')) continue;
            $urls[] = '/' . $uri;
        }

        $this->assertNotEmpty($urls, 'No admin routes discovered — the sweep would be vacuous.');
        $this->sweep($urls, $admin, 'admin', true);
    }

    // ─────────────────────────────────────────────────────────────
    // Public
    // ─────────────────────────────────────────────────────────────

    public function test_legal_documents_all_render_in_both_languages(): void
    {
        $slugs = array_keys(config('legal.documents'));
        $this->assertNotEmpty($slugs);

        foreach ($slugs as $slug) {
            foreach (['fr', 'en'] as $lang) {
                $this->get("/legal/{$slug}?lang={$lang}")
                    ->assertOk()
                    ->assertSee(config("legal.documents.{$slug}.title.{$lang}"));
            }
        }

        $this->get('/legal/does-not-exist')->assertNotFound();
    }

    public function test_public_pages_carry_the_private_operator_disclosure(): void
    {
        $this->get('/legal/avertissement?lang=en')->assertSee('private company', false);
        $this->get('/legal/avertissement?lang=fr')->assertSee('société privée', false);

        // The disclosure also rides in the footer of every public page.
        $this->get('/?lang=fr')->assertSee('plateforme privée et indépendante', false);
    }
}
