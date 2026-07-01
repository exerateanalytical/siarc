<?php

namespace App\Modules\Messaging\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\Business;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private readonly ConversationService $service) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhereHas('business', fn ($bq) => $bq->where('user_id', $user->id));
            })
            ->with(['buyer', 'business', 'product', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate($request->integer('per_page', 20));

        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $items = collect($conversations->items())->map(fn ($c) => [
            'id'              => $c->id,
            'subject'         => $c->subject,
            'status'          => $c->status,
            'last_message_at' => $c->last_message_at?->toIso8601String(),
            'unread_count'    => $c->unreadCountFor($user),
            'product'         => $c->product ? [
                'slug' => $c->product->slug,
                'name' => $pick($c->product->name_fr, $c->product->name_en),
            ] : null,
            'business'        => $c->business ? [
                'slug' => $c->business->slug,
                'name' => $pick($c->business->name_fr, $c->business->name_en),
            ] : null,
            'latest_message'  => $c->latestMessage ? [
                'body'       => mb_substr($c->latestMessage->body, 0, 80),
                'sender_id'  => $c->latestMessage->sender_id,
                'created_at' => $c->latestMessage->created_at?->toIso8601String(),
            ] : null,
        ]);

        return response()->json([
            'data' => $items,
            'meta' => ['total' => $conversations->total(), 'last_page' => $conversations->lastPage()],
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'business_slug' => ['required', 'string', 'exists:businesses,slug'],
            'product_slug'  => ['nullable', 'string', 'exists:products,slug'],
            'subject'       => ['required', 'string', 'max:255'],
            'body'          => ['required', 'string', 'max:2000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $user     = $request->user();
        $business = Business::where('slug', $request->business_slug)->firstOrFail();

        if ($business->user_id === $user->id) {
            return response()->json(['message' => 'Cannot message your own business.'], 422);
        }

        $productId = null;
        if ($request->product_slug) {
            $product   = \App\Modules\Products\Models\Product::where('slug', $request->product_slug)->firstOrFail();
            $productId = $product->id;
        }

        $conversation = $this->service->findOrCreate($user, $business, $productId, $request->subject);
        $message      = $this->service->sendMessage($conversation, $user, $request->body, $request->file('attachments', []));

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'message_id'      => $message->id,
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user         = $request->user();
        $conversation = Conversation::with(['messages.sender', 'messages.attachments', 'business', 'product', 'buyer'])
            ->findOrFail($id);

        if (! $this->service->isParticipant($conversation, $user)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->service->markRead($conversation, $user);

        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return response()->json([
            'data' => [
                'id'      => $conversation->id,
                'subject' => $conversation->subject,
                'status'  => $conversation->status,
                'product' => $conversation->product ? [
                    'slug' => $conversation->product->slug,
                    'name' => $pick($conversation->product->name_fr, $conversation->product->name_en),
                ] : null,
                'business' => [
                    'slug' => $conversation->business?->slug,
                    'name' => $pick($conversation->business?->name_fr, $conversation->business?->name_en),
                ],
                'messages' => $conversation->messages->map(fn ($m) => [
                    'id'          => $m->id,
                    'sender_id'   => $m->sender_id,
                    'sender_name' => $m->sender?->name,
                    'body'        => $m->body,
                    'read_at'     => $m->read_at?->toIso8601String(),
                    'created_at'  => $m->created_at?->toIso8601String(),
                    'attachments' => $m->attachments->map(fn ($a) => [
                        'original_filename' => $a->original_filename,
                        'url'               => $a->url,
                    ]),
                ]),
            ],
        ]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'body'          => ['required', 'string', 'max:2000'],
            'attachments'   => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $user         = $request->user();
        $conversation = Conversation::findOrFail($id);

        if (! $this->service->isParticipant($conversation, $user)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $message = $this->service->sendMessage($conversation, $user, $request->body, $request->file('attachments', []));

        return response()->json([
            'data' => [
                'id'         => $message->id,
                'body'       => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
            ],
        ], 201);
    }
}
