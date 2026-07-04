<?php

namespace Tests\Feature\Quotes;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_participants_can_toggle_invoice_payment_and_strangers_cannot(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);

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
        $invoice = \App\Modules\Quotes\Models\Invoice::create([
            'purchase_order_id' => $order->id, 'status' => 'unpaid', 'total' => 1000,
        ]);

        // A stranger is rejected
        $this->actingAsWebUser($this->makeUser())
            ->post("/tableau-de-bord/factures/{$invoice->id}/basculer")
            ->assertStatus(403);

        // The seller can mark it paid
        $this->actingAsWebUser($owner, 'business_owner')
            ->post("/tableau-de-bord/factures/{$invoice->id}/basculer")
            ->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);

        // The buyer can toggle it back
        $this->actingAsWebUser($buyer)
            ->post("/tableau-de-bord/factures/{$invoice->id}/basculer")
            ->assertRedirect();
        $this->assertSame('unpaid', $invoice->fresh()->status);
    }
}
