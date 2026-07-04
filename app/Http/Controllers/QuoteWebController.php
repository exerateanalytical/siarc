<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Messaging\Services\ConversationService;
use App\Modules\Quotes\Models\Invoice;
use App\Modules\Quotes\Models\PurchaseOrder;
use App\Modules\Quotes\Models\QuoteProposal;
use App\Modules\Quotes\Models\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Quotation system write-endpoints: RFQ → proposal → acceptance →
 * purchase order → invoice. Read views are the replica pages.
 */
class QuoteWebController extends Controller
{
    public function __construct(private readonly ConversationService $conversations) {}

    private function lang(Request $request): string
    {
        $lang = $request->query('lang', $request->cookie('lang', 'fr'));
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function webUser(): ?array
    {
        return session('siac_user');
    }

    /** Buyer submits an RFQ: creates the quote request AND opens a real conversation. */
    public function storeRequest(Request $request): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode(route('quotes.create', [], false)));
        }

        $lang = $this->lang($request);

        $data = $request->validate([
            'business_slug' => ['required', 'string', 'exists:businesses,slug'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'max:2000'],
            'message'       => ['nullable', 'string', 'max:500'],
            'desired_response_date' => ['nullable', 'date'],
        ]);

        $buyer    = User::findOrFail($siacUser['id']);
        $business = Business::where('slug', $data['business_slug'])->firstOrFail();

        if ($business->user_id === $buyer->id) {
            return back()->withErrors(['title' => $lang === 'fr'
                ? 'Vous ne pouvez pas vous demander un devis à vous-même.'
                : 'You cannot request a quote from yourself.']);
        }

        $conversation = $this->conversations->findOrCreate($buyer, $business, null, $data['title']);
        $body = trim(($lang === 'fr' ? 'Demande de devis — ' : 'Quote request — ') . $data['title']
            . "\n\n" . $data['description']
            . (empty($data['message']) ? '' : "\n\n" . $data['message']));
        $this->conversations->sendMessage($conversation, $buyer, mb_substr($body, 0, 2000));

        $quoteRequest = QuoteRequest::create([
            'buyer_id'        => $buyer->id,
            'business_id'     => $business->id,
            'conversation_id' => $conversation->id,
            'title'           => $data['title'],
            'description'     => $data['description'],
            'message'         => $data['message'] ?? null,
            'desired_response_date' => $data['desired_response_date'] ?? null,
            'status'          => 'pending',
        ]);

        return redirect()->route('quotes.index', ['lang' => $lang])
            ->with('success', $lang === 'fr'
                ? "Votre demande de devis {$quoteRequest->reference} a été envoyée à {$business->name_fr}."
                : "Your quote request {$quoteRequest->reference} was sent to {$business->name_fr}.");
    }

    /** Seller answers an RFQ with a priced proposal (items as parallel arrays). */
    public function storeProposal(Request $request, int $quoteRequestId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang = $this->lang($request);
        $quoteRequest = QuoteRequest::with('business')->findOrFail($quoteRequestId);

        if ($quoteRequest->business->user_id !== $siacUser['id'] && empty($siacUser['is_admin'])) {
            abort(403);
        }

        $data = $request->validate([
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.name'          => ['required', 'string', 'max:255'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.unit_price'    => ['required', 'integer', 'min:0'],
            'items.*.discount_pct'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.description'   => ['nullable', 'string', 'max:500'],
            'global_discount_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delivery_fee'          => ['nullable', 'integer', 'min:0'],
            'insurance_fee'         => ['nullable', 'integer', 'min:0'],
            'incoterms'             => ['nullable', 'string', 'max:60'],
            'delivery_location'     => ['nullable', 'string', 'max:120'],
            'production_delay'      => ['nullable', 'string', 'max:60'],
            'delivery_delay'        => ['nullable', 'string', 'max:60'],
            'payment_terms'         => ['nullable', 'string', 'max:255'],
            'valid_until'           => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
        ]);

        $proposal = QuoteProposal::create([
            'quote_request_id'    => $quoteRequest->id,
            'version'             => $quoteRequest->proposals()->count() + 1,
            'status'              => 'sent',
            'global_discount_pct' => $data['global_discount_pct'] ?? 0,
            'delivery_fee'        => $data['delivery_fee'] ?? 0,
            'insurance_fee'       => $data['insurance_fee'] ?? 0,
            'incoterms'           => $data['incoterms'] ?? null,
            'delivery_location'   => $data['delivery_location'] ?? null,
            'production_delay'    => $data['production_delay'] ?? null,
            'delivery_delay'      => $data['delivery_delay'] ?? null,
            'payment_terms'       => $data['payment_terms'] ?? null,
            'valid_until'         => $data['valid_until'] ?? now()->addDays(30),
            'notes'               => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $proposal->items()->create([
                'name'         => $item['name'],
                'description'  => $item['description'] ?? null,
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'discount_pct' => $item['discount_pct'] ?? 0,
            ]);
        }

        $proposal->recalculateTotals();
        $quoteRequest->update(['status' => 'quoted']);

        return redirect()->route('dashboard.quotes', ['lang' => $lang])
            ->with('success', $lang === 'fr'
                ? "Proposition {$proposal->reference} envoyée."
                : "Proposal {$proposal->reference} sent.");
    }

    /** Buyer accepts a proposal: purchase order + invoice are generated. */
    public function acceptProposal(Request $request, int $proposalId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang = $this->lang($request);
        $proposal = QuoteProposal::with('request')->findOrFail($proposalId);

        if ($proposal->request->buyer_id !== $siacUser['id'] && empty($siacUser['is_admin'])) {
            abort(403);
        }

        if ($proposal->status === 'accepted') {
            return redirect()->route('quotes.po', ['lang' => $lang, 'po' => $proposal->purchaseOrder?->id]);
        }

        $proposal->update(['status' => 'accepted']);
        $proposal->request->update(['status' => 'accepted']);

        $order = PurchaseOrder::create([
            'quote_proposal_id'      => $proposal->id,
            'status'                 => 'confirmed',
            'total'                  => $proposal->total,
            'expected_delivery_date' => now()->addDays(30),
        ]);

        Invoice::create([
            'purchase_order_id' => $order->id,
            'status'            => 'unpaid',
            'total'             => $order->total,
            'due_date'          => now()->addDays(14),
        ]);

        return redirect()->route('quotes.po', ['lang' => $lang, 'po' => $order->id])
            ->with('success', $lang === 'fr'
                ? "Proposition acceptée — bon de commande {$order->reference} généré."
                : "Proposal accepted — purchase order {$order->reference} generated.");
    }

    /** Buyer refuses a proposal. */
    public function refuseProposal(Request $request, int $proposalId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang = $this->lang($request);
        $proposal = QuoteProposal::with('request')->findOrFail($proposalId);

        if ($proposal->request->buyer_id !== $siacUser['id'] && empty($siacUser['is_admin'])) {
            abort(403);
        }

        $proposal->update(['status' => 'refused']);
        $proposal->request->update(['status' => 'refused']);

        return redirect()->route('quotes.index', ['lang' => $lang])
            ->with('success', $lang === 'fr' ? 'Proposition refusée.' : 'Proposal refused.');
    }

    /** Mark the invoice of a purchase order as paid / unpaid. */
    public function toggleInvoice(Request $request, int $invoiceId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang = $this->lang($request);
        $invoice = Invoice::with('purchaseOrder.proposal.request.business')->findOrFail($invoiceId);
        $order   = $invoice->purchaseOrder;

        $isBuyer  = $order->proposal->request->buyer_id === $siacUser['id'];
        $isSeller = $order->proposal->request->business->user_id === $siacUser['id'];
        if (! $isBuyer && ! $isSeller && empty($siacUser['is_admin'])) {
            abort(403);
        }

        $paid = $invoice->status !== 'paid';
        $invoice->update([
            'status'         => $paid ? 'paid' : 'unpaid',
            'paid_at'        => $paid ? now() : null,
            'payment_method' => $paid ? ($request->input('payment_method', 'Virement bancaire')) : null,
        ]);

        return back()->with('success', $lang === 'fr'
            ? ($paid ? 'Facture marquée comme payée.' : 'Facture marquée comme impayée.')
            : ($paid ? 'Invoice marked as paid.' : 'Invoice marked as unpaid.'));
    }
}
