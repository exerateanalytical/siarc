<?php

namespace Tests\Feature\Quotes;

use App\Jobs\SendNotificationEmail;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class QuoteFlowTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function actingAsWebUser(User $user, string $role = 'buyer'): static
    {
        return $this->withSession(['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name ?? 'Test User',
            'email'    => $user->email,
            'role'     => $role,
            'is_admin' => false,
        ]]);
    }

    public function test_buyer_can_submit_a_quote_request(): void
    {
        $buyer    = $this->makeUser();
        $business = $this->makeBusiness();

        $response = $this->actingAsWebUser($buyer)->post('/tableau-de-bord/demandes', [
            'business_slug' => $business->slug,
            'title'         => 'Mobilier en bois massif pour hôtel',
            'description'   => 'Nous recherchons des meubles en bois massif de haute qualité.',
            'message'       => 'Nous serions ravis de collaborer avec vous.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quote_requests', [
            'buyer_id'    => $buyer->id,
            'business_id' => $business->id,
            'title'       => 'Mobilier en bois massif pour hôtel',
            'status'      => 'pending',
        ]);

        $request = \DB::table('quote_requests')->first();
        $this->assertMatchesRegularExpression('/^RFQ-\d{4}-\d{6}$/', $request->reference);

        // The RFQ also opens a real conversation with the artisan
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_seller_can_send_a_priced_proposal_with_computed_totals(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);

        $rfq = \App\Modules\Quotes\Models\QuoteRequest::create([
            'buyer_id' => $buyer->id, 'business_id' => $business->id,
            'title' => 'Mobilier hôtel', 'status' => 'pending',
        ]);

        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/demandes/{$rfq->id}/proposition", [
                'items' => [
                    ['name' => 'Mobilier en bois massif', 'quantity' => 10, 'unit_price' => 180000, 'discount_pct' => 5],
                    ['name' => 'Chaise artisanale',       'quantity' => 15, 'unit_price' => 60000],
                ],
                'global_discount_pct' => 2,
                'delivery_fee'        => 250000,
                'insurance_fee'       => 150000,
                'payment_terms'       => '50% à la commande, 50% avant expédition',
            ])->assertRedirect();

        $proposal = \App\Modules\Quotes\Models\QuoteProposal::first();

        // Item math: 10×180000×0.95 = 1,710,000 ; 15×60000 = 900,000
        $this->assertSame(1710000 + 900000, (int) $proposal->subtotal);
        // Global discount 2% of subtotal
        $this->assertSame((int) round(2610000 * 0.02), (int) $proposal->discount_amount);
        // Tax 19.25% of (subtotal - discount)
        $taxable = 2610000 - (int) round(2610000 * 0.02);
        $this->assertSame((int) round($taxable * 0.1925), (int) $proposal->tax_amount);
        // Total = taxable + tax + fees
        $this->assertSame($taxable + (int) round($taxable * 0.1925) + 250000 + 150000, (int) $proposal->total);

        $this->assertMatchesRegularExpression('/^QUO-\d{4}-\d{6}$/', $proposal->reference);
        $this->assertSame('sent', $proposal->status);
        $this->assertSame('quoted', $rfq->fresh()->status);
    }

    public function test_stranger_cannot_send_a_proposal_for_someone_elses_rfq(): void
    {
        $rfq = \App\Modules\Quotes\Models\QuoteRequest::create([
            'buyer_id' => $this->makeUser()->id, 'business_id' => $this->makeBusiness()->id,
            'title' => 'X', 'status' => 'pending',
        ]);

        $this->actingAsWebUser($this->makeUser(), 'business_owner')
            ->post("/tableau-de-bord/demandes/{$rfq->id}/proposition", [
                'items' => [['name' => 'A', 'quantity' => 1, 'unit_price' => 100]],
            ])->assertStatus(403);
    }

    public function test_buyer_accepting_a_proposal_generates_purchase_order_and_invoice(): void
    {
        $buyer    = $this->makeUser();
        $business = $this->makeBusiness();

        $rfq = \App\Modules\Quotes\Models\QuoteRequest::create([
            'buyer_id' => $buyer->id, 'business_id' => $business->id,
            'title' => 'Mobilier', 'status' => 'quoted',
        ]);
        $proposal = \App\Modules\Quotes\Models\QuoteProposal::create([
            'quote_request_id' => $rfq->id, 'status' => 'sent', 'total' => 5368253,
        ]);

        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/propositions/{$proposal->id}/accepter")
            ->assertRedirect();

        $this->assertSame('accepted', $proposal->fresh()->status);
        $this->assertSame('accepted', $rfq->fresh()->status);

        $order = \App\Modules\Quotes\Models\PurchaseOrder::first();
        $this->assertNotNull($order);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame(5368253, (int) $order->total);
        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{5}$/', $order->reference);

        $invoice = \App\Modules\Quotes\Models\Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertSame('unpaid', $invoice->status);
        $this->assertSame(5368253, (int) $invoice->total);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{5}$/', $invoice->reference);
    }

    public function test_stranger_cannot_accept_someone_elses_proposal(): void
    {
        $rfq = \App\Modules\Quotes\Models\QuoteRequest::create([
            'buyer_id' => $this->makeUser()->id, 'business_id' => $this->makeBusiness()->id,
            'title' => 'X', 'status' => 'quoted',
        ]);
        $proposal = \App\Modules\Quotes\Models\QuoteProposal::create([
            'quote_request_id' => $rfq->id, 'status' => 'sent',
        ]);

        $this->actingAsWebUser($this->makeUser())
            ->post("/tableau-de-bord/propositions/{$proposal->id}/accepter")
            ->assertStatus(403);

        $this->assertSame('sent', $proposal->fresh()->status);
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_buyer_can_refuse_a_proposal(): void
    {
        $buyer = $this->makeUser();
        $rfq = \App\Modules\Quotes\Models\QuoteRequest::create([
            'buyer_id' => $buyer->id, 'business_id' => $this->makeBusiness()->id,
            'title' => 'X', 'status' => 'quoted',
        ]);
        $proposal = \App\Modules\Quotes\Models\QuoteProposal::create([
            'quote_request_id' => $rfq->id, 'status' => 'sent',
        ]);

        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/propositions/{$proposal->id}/refuser")
            ->assertRedirect();

        $this->assertSame('refused', $proposal->fresh()->status);
        $this->assertSame('refused', $rfq->fresh()->status);
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    /** An invoice with its chain, ready to be settled. */
    private function makeInvoice(\App\Modules\Auth\Models\User $buyer, \App\Modules\Businesses\Models\Business $business)
    {
        $rfq = \App\Modules\Quotes\Models\QuoteRequest::create([
            'buyer_id' => $buyer->id, 'business_id' => $business->id,
            'title' => 'X', 'status' => 'accepted',
        ]);
        $proposal = \App\Modules\Quotes\Models\QuoteProposal::create([
            'quote_request_id' => $rfq->id, 'status' => 'accepted', 'total' => 1000,
        ]);
        $order = \App\Modules\Quotes\Models\PurchaseOrder::create([
            'quote_proposal_id' => $proposal->id, 'status' => 'confirmed', 'total' => 1000,
        ]);

        return \App\Modules\Quotes\Models\Invoice::create([
            'purchase_order_id' => $order->id, 'status' => 'unpaid', 'total' => 1000,
        ]);
    }

    /**
     * Settlement happens off-platform, so "paid" is a claim, not a fact. Only
     * the artisan receiving the money may make that claim, and it is attributed
     * to them.
     */
    public function test_only_the_seller_can_record_a_payment(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $invoice  = $this->makeInvoice($buyer, $business);

        $payload = ['payment_method' => 'mobile_money', 'payment_reference' => 'MM-4471'];

        // A stranger cannot even see it
        $this->actingAsWebUser($this->makeUser())
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", $payload)
            ->assertStatus(403);

        // The buyer is a party, but they are not the one receiving the money
        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", $payload)
            ->assertSessionHasErrors('payment');
        $this->assertSame('unpaid', $invoice->fresh()->status);

        // The seller can, and the entry carries their name
        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", $payload)
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('mobile_money', $invoice->payment_method);
        $this->assertSame('MM-4471', $invoice->payment_reference);
        $this->assertSame($owner->id, $invoice->recorded_by);
        $this->assertNotNull($invoice->paid_at);
        $this->assertNull($invoice->confirmed_at, 'A fresh record must not count as confirmed.');
    }

    public function test_the_buyer_confirms_a_recorded_payment(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $invoice  = $this->makeInvoice($buyer, $business);

        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", ['payment_method' => 'cash']);

        // The seller cannot confirm their own claim
        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement/reponse", ['response' => 'confirm'])
            ->assertSessionHasErrors('payment');
        $this->assertNull($invoice->fresh()->confirmed_at);

        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement/reponse", ['response' => 'confirm'])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertNotNull($invoice->confirmed_at);
        $this->assertSame($buyer->id, $invoice->confirmed_by);
        $this->assertSame('paid', $invoice->status);
    }

    public function test_a_disputed_payment_returns_the_invoice_to_unpaid(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $invoice  = $this->makeInvoice($buyer, $business);

        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", ['payment_method' => 'bank_transfer']);

        // A dispute needs a reason
        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement/reponse", ['response' => 'dispute'])
            ->assertSessionHasErrors('dispute_reason');

        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement/reponse", [
                'response' => 'dispute', 'dispute_reason' => 'Rien reçu sur mon compte.',
            ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame('unpaid', $invoice->status, 'A disputed payment must stop reading as settled.');
        $this->assertNotNull($invoice->disputed_at);
        $this->assertSame('Rien reçu sur mon compte.', $invoice->dispute_reason);

        // Re-recording clears the dispute so the parties can try again
        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", [
                'payment_method' => 'bank_transfer', 'payment_reference' => 'VIR-889',
            ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNull($invoice->disputed_at);
    }

    public function test_a_payment_cannot_be_recorded_with_an_unknown_method(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $invoice  = $this->makeInvoice($buyer, $business);

        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/paiement", ['payment_method' => 'bitcoin'])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame('unpaid', $invoice->fresh()->status);
    }

    public function test_wired_pages_render_real_records_end_to_end(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);

        // Buyer creates the RFQ
        $this->actingAsWebUser($buyer)->post('/tableau-de-bord/demandes', [
            'business_slug' => $business->slug,
            'title'         => 'Mobilier en bois massif',
            'description'   => 'Commande pour un hôtel de Douala.',
        ]);
        $rfq = \App\Modules\Quotes\Models\QuoteRequest::first();

        // Seller opens the builder against the RFQ, then sends a proposal
        $this->actingAsWebUser($owner, 'business_owner')
            ->get("/tableau-de-bord/propositions/articles?rfq={$rfq->id}")
            ->assertOk()
            ->assertSee($rfq->reference);

        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/demandes/{$rfq->id}/proposition", [
                'items' => [['name' => 'Mobilier en bois massif', 'quantity' => 10, 'unit_price' => 180000, 'discount_pct' => 5]],
                'delivery_fee' => 250000,
            ]);
        $proposal = \App\Modules\Quotes\Models\QuoteProposal::first();

        // Seller sees the RFQ on the quote dashboard; buyer sees it on the listing
        $this->actingAsWebUser($owner, 'business_owner')
            ->get('/tableau-de-bord/devis')
            ->assertOk()
            ->assertSee($rfq->title);

        $this->actingAsWebUser($buyer)
            ->get('/tableau-de-bord/demandes')
            ->assertOk()
            ->assertSee($rfq->reference);

        $this->actingAsWebUser($buyer)
            ->get("/tableau-de-bord/propositions/detail?proposal={$proposal->id}")
            ->assertOk()
            ->assertSee($proposal->reference)
            ->assertSee(number_format($proposal->total));

        // A stranger is sent back to their own list. The page used to fall back
        // to demo content instead; redirecting is both honest and leak-free.
        $this->actingAsWebUser($this->makeUser())
            ->get("/tableau-de-bord/propositions/detail?proposal={$proposal->id}")
            ->assertRedirect();

        // Buyer accepts; PO and invoice pages render the generated records
        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/propositions/{$proposal->id}/accepter");

        $order   = \App\Modules\Quotes\Models\PurchaseOrder::first();
        $invoice = \App\Modules\Quotes\Models\Invoice::first();

        $this->actingAsWebUser($buyer)
            ->get("/tableau-de-bord/commandes/bon?po={$order->id}")
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee($proposal->reference)
            ->assertSee(number_format($proposal->total));

        $this->actingAsWebUser($buyer)
            ->get("/tableau-de-bord/factures/detail?invoice={$invoice->id}")
            ->assertOk()
            ->assertSee($invoice->reference)
            ->assertSee($order->reference);
    }

    /**
     * Notification email must not sit inside the request. It used to be a
     * synchronous SMTP round trip, so a slow relay made submitting an RFQ feel
     * broken even though the request had already succeeded.
     */
    public function test_notification_email_is_queued_not_sent_inline(): void
    {
        Queue::fake();

        $buyer = $this->makeUser();
        // The courtesy email only goes out if the shop published an address.
        $business = $this->makeBusiness(null, ['email' => 'atelier@example.test']);

        $this->actingAsWebUser($buyer)->post('/tableau-de-bord/demandes', [
            'business_slug' => $business->slug,
            'title'         => 'Commande de tabourets',
            'description'   => 'Vingt tabourets en bois pour un restaurant.',
        ])->assertRedirect();

        Queue::assertPushed(SendNotificationEmail::class);
    }

    /** Fulfilment belongs to the seller; the buyer may only pull out, and only in time. */
    public function test_buyer_can_cancel_an_order_but_not_advance_it(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $invoice  = $this->makeInvoice($buyer, $business);
        $order    = $invoice->purchaseOrder;

        // A buyer cannot push the order forward
        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/commandes/{$order->id}/statut", ['status' => 'shipped'])
            ->assertSessionHasErrors('status');
        $this->assertSame('confirmed', $order->fresh()->status);

        // But they can cancel while nothing has shipped
        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/commandes/{$order->id}/statut", [
                'status' => 'cancelled', 'reason' => 'Plus besoin.',
            ])->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_buyer_cannot_cancel_once_the_order_has_shipped(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $order    = $this->makeInvoice($buyer, $business)->purchaseOrder;
        $order->update(['status' => 'shipped']);

        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/commandes/{$order->id}/statut", ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');
        $this->assertSame('shipped', $order->fresh()->status);

        // The seller still can — they are the one holding the goods
        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/commandes/{$order->id}/statut", ['status' => 'cancelled'])
            ->assertRedirect();
        $this->assertSame('cancelled', $order->fresh()->status);
    }
}
