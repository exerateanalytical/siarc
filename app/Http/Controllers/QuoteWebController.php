<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationEmail;
use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Messaging\Services\ConversationService;
use App\Modules\Notifications\Models\UserNotification;
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

    /**
     * In-app notification now, email off the request path.
     *
     * The in-app record is what the recipient actually relies on, so it is
     * written synchronously. The email is a courtesy copy and used to be a
     * blocking SMTP round trip inside the POST — a slow relay made accepting a
     * quote feel broken.
     */
    private function notifyUser(string $userId, ?string $email, string $type, string $title, string $body, string $link): void
    {
        UserNotification::notify($userId, $type, $title, mb_substr($body, 0, 140), $link);

        if ($email) {
            // The link goes through as its own argument so the email renders a
            // real button rather than a bare URL pasted onto the message.
            SendNotificationEmail::dispatch(
                $email,
                '[Artisan Hub 237] ' . $title,
                $body,
                $link,
                $title,
                $this->lang(request()),
            );
        }
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

        $this->notifyUser(
            $business->user_id,
            $business->email,
            'quote_request',
            $lang === 'fr' ? 'Nouvelle demande de devis' : 'New quote request',
            ($lang === 'fr'
                ? "{$buyer->name} vous a envoyé une demande de devis « {$data['title']} »."
                : "{$buyer->name} sent you a quote request \"{$data['title']}\"."),
            route('dashboard.quotes', ['lang' => $lang])
        );

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
            'items.*.unit'          => ['nullable', 'string', 'max:40'],
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
                'unit'         => $item['unit'] ?? 'Pièces',
                'unit_price'   => $item['unit_price'],
                'discount_pct' => $item['discount_pct'] ?? 0,
            ]);
        }

        $proposal->recalculateTotals();
        $quoteRequest->update(['status' => 'quoted']);

        $buyer = $quoteRequest->buyer ?? User::find($quoteRequest->buyer_id);
        if ($buyer) {
            $this->notifyUser(
                $buyer->id,
                $buyer->email,
                'quote_proposal',
                $lang === 'fr' ? 'Proposition de devis reçue' : 'Quote proposal received',
                ($lang === 'fr'
                    ? "{$quoteRequest->business->name_fr} vous a envoyé une proposition pour « {$quoteRequest->title} »."
                    : "{$quoteRequest->business->name_fr} sent you a proposal for \"{$quoteRequest->title}\"."),
                route('quotes.index', ['lang' => $lang])
            );
        }

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
        $proposal = QuoteProposal::with('request.business')->findOrFail($proposalId);

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

        $business = $proposal->request->business;
        if ($business) {
            $this->notifyUser(
                $business->user_id,
                $business->email,
                'quote_accepted',
                $lang === 'fr' ? 'Devis accepté' : 'Quote accepted',
                ($lang === 'fr'
                    ? "Votre proposition pour « {$proposal->request->title} » a été acceptée — bon de commande {$order->reference} généré."
                    : "Your proposal for \"{$proposal->request->title}\" was accepted — purchase order {$order->reference} generated."),
                route('dashboard.quotes', ['lang' => $lang])
            );
        }

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

    /**
     * The order book, for both sides of the marketplace.
     *
     * A seller sees every purchase order raised against their business; a buyer
     * sees their own. Previously orders were only visible as the four most
     * recent rows on the dashboard, so anything older was unreachable.
     */
    public function orders(Request $request)
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode(route('orders.index', [], false)));
        }

        $lang = $this->lang($request);
        $business = Business::where('user_id', $siacUser['id'])->first();
        $isSeller = (bool) $business;
        $status   = $request->query('status');

        $orders = PurchaseOrder::query()
            ->with(['proposal.request.business', 'invoice'])
            ->whereHas('proposal.request', function ($q) use ($isSeller, $business, $siacUser) {
                $isSeller
                    ? $q->where('business_id', $business->id)
                    : $q->where('buyer_id', $siacUser['id']);
            })
            ->when(in_array($status, self::ORDER_STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.dashboard.orders', [
            'lang' => $lang, 'isSeller' => $isSeller, 'orders' => $orders,
            'status' => $status, 'business' => $business,
        ]);
    }

    /** Ordered lifecycle a seller can walk a purchase order through. */
    public const ORDER_STATUSES = ['confirmed', 'in_production', 'shipped', 'delivered', 'cancelled'];

    /** Seller advances a purchase order along its lifecycle. */
    public function updateOrderStatus(Request $request, int $orderId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang  = $this->lang($request);
        $isFr  = $lang === 'fr';
        $order = PurchaseOrder::with('proposal.request.business')->findOrFail($orderId);

        $quoteRequest = $order->proposal->request;
        $isSeller = $quoteRequest->business->user_id === $siacUser['id'];
        $isBuyer  = $quoteRequest->buyer_id === $siacUser['id'];
        $isAdmin  = ! empty($siacUser['is_admin']);

        abort_unless($isSeller || $isBuyer || $isAdmin, 403);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::ORDER_STATUSES)],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // The seller runs the fulfilment steps because they are the one doing
        // the work. A buyer can only cancel, and only while nothing has shipped
        // — after that it is a return, which is between the two of them.
        if (! $isSeller && ! $isAdmin) {
            if ($data['status'] !== 'cancelled') {
                return back()->withErrors(['status' => $isFr
                    ? 'Seul l\'artisan peut faire avancer une commande.'
                    : 'Only the artisan can advance an order.']);
            }
            if (in_array($order->status, ['shipped', 'delivered'], true)) {
                return back()->withErrors(['status' => $isFr
                    ? 'Cette commande est déjà expédiée — contactez l\'artisan pour convenir d\'un retour.'
                    : 'This order has already shipped — contact the artisan to arrange a return.']);
            }
        }

        $order->update(['status' => $data['status']]);

        $labels = [
            'confirmed'     => ['Confirmée', 'Confirmed'],
            'in_production' => ['En production', 'In production'],
            'shipped'       => ['Expédiée', 'Shipped'],
            'delivered'     => ['Livrée', 'Delivered'],
            'cancelled'     => ['Annulée', 'Cancelled'],
        ];
        $label  = $labels[$data['status']][$isFr ? 0 : 1];
        $reason = trim((string) ($data['reason'] ?? ''));
        $link   = route('quotes.po', ['lang' => $lang, 'po' => $order->id]);

        // Tell whoever did NOT make the change. An admin intervening tells both,
        // and says so — a silent staff edit to someone's order is worse than none.
        $buyer    = User::find($quoteRequest->buyer_id);
        $sellerId = $quoteRequest->business->user_id;

        $body = ($isFr
            ? "La commande {$order->reference} est maintenant : {$label}."
            : "Order {$order->reference} is now: {$label}.")
            . ($reason !== '' ? "\n\n" . ($isFr ? 'Motif : ' : 'Reason: ') . $reason : '')
            . ($isAdmin ? "\n\n" . ($isFr
                ? 'Cette modification a été effectuée par l\'équipe Artisan Hub 237.'
                : 'This change was made by the Artisan Hub 237 team.') : '');

        $title = $isFr ? 'Commande mise à jour' : 'Order updated';

        if (($isSeller || $isAdmin) && $buyer) {
            $this->notifyUser($buyer->id, $buyer->email, 'order_status', $title, $body, $link);
        }
        if ($isBuyer || $isAdmin) {
            $this->notifyUser($sellerId, $quoteRequest->business->email, 'order_status', $title, $body, $link);
        }

        return back()->with('success', $isFr
            ? "Commande {$order->reference} — statut : {$label}."
            : "Order {$order->reference} — status: {$label}.");
    }

    /** Mark the invoice of a purchase order as paid / unpaid. */
    /** Ways a buyer can settle. The platform handles none of them itself. */
    public const PAYMENT_METHODS = ['mobile_money', 'bank_transfer', 'cash', 'cheque', 'other'];

    /** Resolve the caller's relationship to an invoice, or 403. */
    private function invoiceParties(Invoice $invoice, array $siacUser): array
    {
        $request  = $invoice->purchaseOrder->proposal->request;
        $isBuyer  = $request->buyer_id === $siacUser['id'];
        $isSeller = $request->business->user_id === $siacUser['id'];
        $isAdmin  = ! empty($siacUser['is_admin']);

        abort_unless($isBuyer || $isSeller || $isAdmin, 403);

        return [$isBuyer, $isSeller, $isAdmin, $request];
    }

    /**
     * The seller records a payment they have received off-platform.
     *
     * Only the seller (or an admin) may do this: they are the one the money
     * reaches, so they are the only party in a position to state it arrived.
     * The entry is attributed and timestamped, and the buyer is notified so
     * they can confirm or dispute it.
     */
    public function recordPayment(Request $request, int $invoiceId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang    = $this->lang($request);
        $isFr    = $lang === 'fr';
        $invoice = Invoice::with('purchaseOrder.proposal.request.business')->findOrFail($invoiceId);
        [$isBuyer, $isSeller, $isAdmin, $quoteRequest] = $this->invoiceParties($invoice, $siacUser);

        if (! $isSeller && ! $isAdmin) {
            return back()->withErrors(['payment' => $isFr
                ? 'Seul l\'artisan qui reçoit le paiement peut l\'enregistrer.'
                : 'Only the artisan receiving the payment can record it.']);
        }

        $data = $request->validate([
            'payment_method'    => ['required', 'in:' . implode(',', self::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_note'      => ['nullable', 'string', 'max:500'],
            'paid_at'           => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $invoice->update([
            'status'            => 'paid',
            'paid_at'           => $data['paid_at'] ?? now(),
            'payment_method'    => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_note'      => $data['payment_note'] ?? null,
            'recorded_by'       => $siacUser['id'],
            // A fresh entry supersedes any earlier confirmation or dispute.
            'confirmed_at'      => null,
            'confirmed_by'      => null,
            'disputed_at'       => null,
            'dispute_reason'    => null,
        ]);

        $buyer = User::find($quoteRequest->buyer_id);
        if ($buyer) {
            $this->notifyUser(
                $buyer->id,
                $buyer->email,
                'payment_recorded',
                $isFr ? 'Paiement enregistré' : 'Payment recorded',
                ($isFr
                    ? "L'artisan a enregistré le paiement de la facture {$invoice->reference}. Confirmez-le s'il est exact."
                    : "The artisan recorded payment of invoice {$invoice->reference}. Confirm it if that is correct."),
                route('quotes.invoice', ['lang' => $lang, 'invoice' => $invoice->id])
            );
        }

        return back()->with('success', $isFr
            ? "Paiement enregistré. {$buyer?->name} sera invité à le confirmer."
            : "Payment recorded. {$buyer?->name} will be asked to confirm it.");
    }

    /**
     * The buyer confirms the recorded payment, or disputes it.
     *
     * This is the only signal on the platform that both sides agree money
     * changed hands — without it, "paid" is one party's word.
     */
    public function respondToPayment(Request $request, int $invoiceId): RedirectResponse
    {
        $siacUser = $this->webUser();
        if (! $siacUser) {
            return redirect('/login');
        }

        $lang    = $this->lang($request);
        $isFr    = $lang === 'fr';
        $invoice = Invoice::with('purchaseOrder.proposal.request.business')->findOrFail($invoiceId);
        [$isBuyer, $isSeller, $isAdmin, $quoteRequest] = $this->invoiceParties($invoice, $siacUser);

        if (! $isBuyer && ! $isAdmin) {
            return back()->withErrors(['payment' => $isFr
                ? 'Seul l\'acheteur peut confirmer ou contester ce paiement.'
                : 'Only the buyer can confirm or dispute this payment.']);
        }

        if ($invoice->status !== 'paid') {
            return back()->withErrors(['payment' => $isFr
                ? 'Aucun paiement n\'a encore été enregistré pour cette facture.'
                : 'No payment has been recorded on this invoice yet.']);
        }

        $data = $request->validate([
            'response'       => ['required', 'in:confirm,dispute'],
            'dispute_reason' => ['required_if:response,dispute', 'nullable', 'string', 'max:500'],
        ]);

        $confirming = $data['response'] === 'confirm';

        $invoice->update($confirming
            ? ['confirmed_at' => now(), 'confirmed_by' => $siacUser['id'], 'disputed_at' => null, 'dispute_reason' => null]
            // A dispute puts the invoice back to unpaid: the seller's claim
            // stands recorded, but the platform stops presenting it as settled.
            : ['status' => 'unpaid', 'disputed_at' => now(), 'dispute_reason' => $data['dispute_reason'],
               'confirmed_at' => null, 'confirmed_by' => null]);

        $business = $quoteRequest->business;
        if ($business) {
            $this->notifyUser(
                $business->user_id,
                $business->email,
                $confirming ? 'payment_confirmed' : 'payment_disputed',
                $confirming
                    ? ($isFr ? 'Paiement confirmé' : 'Payment confirmed')
                    : ($isFr ? 'Paiement contesté' : 'Payment disputed'),
                ($confirming
                    ? ($isFr
                        ? "L'acheteur a confirmé le paiement de la facture {$invoice->reference}."
                        : "The buyer confirmed payment of invoice {$invoice->reference}.")
                    : ($isFr
                        ? "L'acheteur conteste le paiement de la facture {$invoice->reference} : {$data['dispute_reason']}"
                        : "The buyer disputes payment of invoice {$invoice->reference}: {$data['dispute_reason']}")),
                route('quotes.invoice', ['lang' => $lang, 'invoice' => $invoice->id])
            );
        }

        return back()->with('success', $confirming
            ? ($isFr ? 'Paiement confirmé. Merci.' : 'Payment confirmed. Thank you.')
            : ($isFr ? 'Paiement contesté. L\'artisan a été prévenu.' : 'Payment disputed. The artisan has been notified.'));
    }
}
