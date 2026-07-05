<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Services\ConversationService;
use App\Modules\Products\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessagingWebController extends Controller
{
    public function __construct(private readonly ConversationService $service) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    /**
     * Buyer sends a message to a business (optionally about a specific product).
     */
    public function send(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');

        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->input('return_to', '/')));
        }

        $data = $request->validate([
            'business_slug' => ['required', 'string', 'exists:businesses,slug'],
            'product_slug'  => ['nullable', 'string', 'exists:products,slug'],
            'body'          => ['required', 'string', 'max:2000'],
            'return_to'     => ['nullable', 'string'],
        ]);

        $buyer    = User::findOrFail($siacUser['id']);
        $business = Business::where('slug', $data['business_slug'])->firstOrFail();

        if ($business->user_id === $buyer->id) {
            return back()->withErrors(['body' => $lang === 'fr' ? 'Vous ne pouvez pas vous contacter vous-même.' : 'You cannot message your own business.']);
        }

        $product = null;
        if (! empty($data['product_slug'])) {
            $product = Product::where('slug', $data['product_slug'])->first();
        }

        $subject = $product
            ? ($lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr))
            : ($lang === 'fr' ? 'Nouveau message' : 'New message');

        $conversation = $this->service->findOrCreate($buyer, $business, $product?->id, $subject);
        $this->service->sendMessage($conversation, $buyer, $data['body']);

        $this->notifySeller($conversation, $buyer, $data['body']);

        return redirect($data['return_to'] ?? '/')
            ->with('success', $lang === 'fr' ? 'Message envoyé au vendeur.' : 'Message sent to the seller.');
    }

    /**
     * List conversations for the logged-in user (buyer or seller side).
     */
    public function inbox(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $userId = $siacUser['id'];

        $conversations = Conversation::where('buyer_id', $userId)
            ->orWhereHas('business', fn ($q) => $q->where('user_id', $userId))
            ->with(['buyer', 'business', 'product', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('pages.dashboard.inbox', compact('lang', 'siacUser', 'conversations', 'userId'));
    }

    /**
     * View + reply to a single conversation.
     */
    public function thread(Request $request, int $id)
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $user = User::findOrFail($siacUser['id']);
        $conversation = Conversation::with(['messages.sender', 'business', 'product', 'buyer'])->findOrFail($id);

        if (! $this->service->isParticipant($conversation, $user)) {
            abort(403);
        }

        $this->service->markRead($conversation, $user);

        return view('pages.dashboard.thread', compact('lang', 'siacUser', 'conversation', 'user'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $user = User::findOrFail($siacUser['id']);
        $conversation = Conversation::with('business')->findOrFail($id);

        if (! $this->service->isParticipant($conversation, $user)) {
            abort(403);
        }

        $this->service->sendMessage($conversation, $user, $data['body']);

        // Notify the other participant
        $isBuyer = $conversation->buyer_id === $user->id;
        if ($isBuyer) {
            $this->notifySeller($conversation, $user, $data['body']);
        } else {
            $this->notifyBuyer($conversation, $user, $data['body']);
        }

        return redirect()->route('messages.thread', ['id' => $id]);
    }

    private function notifySeller(Conversation $conversation, User $sender, string $body): void
    {
        $business = $conversation->business ?? $conversation->business()->first();
        if (! $business) {
            return;
        }

        \App\Modules\Notifications\Models\UserNotification::notify(
            $business->user_id,
            'new_message',
            'Nouveau message — ' . $conversation->subject,
            mb_substr($body, 0, 140),
            route('messages.thread', ['id' => $conversation->id])
        );

        if ($business->email) {
            Mail::raw(
                "Nouveau message de {$sender->name} concernant \"{$conversation->subject}\":\n\n{$body}\n\nRépondez depuis votre tableau de bord SIARC.",
                function ($message) use ($business, $conversation) {
                    $message->to($business->email)
                        ->subject('[SIARC] Nouveau message — ' . $conversation->subject);
                }
            );
        }
    }

    private function notifyBuyer(Conversation $conversation, User $sender, string $body): void
    {
        $buyer = $conversation->buyer ?? $conversation->buyer()->first();
        if (! $buyer) {
            return;
        }

        \App\Modules\Notifications\Models\UserNotification::notify(
            $buyer->id,
            'new_message',
            'Nouvelle réponse — ' . $conversation->subject,
            mb_substr($body, 0, 140),
            route('messages.thread', ['id' => $conversation->id])
        );

        if ($buyer->email) {
            Mail::raw(
                "Nouvelle réponse de {$sender->name} concernant \"{$conversation->subject}\":\n\n{$body}\n\nRépondez depuis votre tableau de bord SIARC.",
                function ($message) use ($buyer, $conversation) {
                    $message->to($buyer->email)
                        ->subject('[SIARC] Nouvelle réponse — ' . $conversation->subject);
                }
            );
        }
    }
}
