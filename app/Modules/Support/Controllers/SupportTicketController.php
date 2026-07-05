<?php

namespace App\Modules\Support\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Support\Models\SupportCategory;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $cats = SupportCategory::orderBy('sort_order')->get();

        return response()->json(['data' => $cats->map(fn ($c) => [
            'id'   => $c->id,
            'name' => $pick($c->name_fr, $c->name_en),
        ])]);
    }

    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 15), 100)));

        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return response()->json([
            'data' => collect($tickets->items())->map(fn ($t) => [
                'id'           => $t->id,
                'subject'      => $t->subject,
                'status'       => $t->status,
                'priority'     => $t->priority,
                'category'     => $t->category ? $pick($t->category->name_fr, $t->category->name_en) : null,
                'reply_count'  => $t->replies()->count(),
                'created_at'   => $t->created_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $tickets->total(), 'last_page' => $tickets->lastPage()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:support_categories,id'],
            'subject'     => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::create([
            'user_id'     => $request->user()->id,
            'category_id' => $request->category_id,
            'subject'     => $request->subject,
            'status'      => 'open',
            'priority'    => 'normal',
        ]);

        SupportTicketReply::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => $request->user()->id,
            'body_fr'        => $request->body,
            'is_staff'       => false,
        ]);

        return response()->json(['data' => ['id' => $ticket->id, 'status' => $ticket->status]], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->with(['replies.user', 'category'])
            ->findOrFail($id);

        $lang = $request->header('Accept-Language', 'fr');
        $pick = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        return response()->json(['data' => [
            'id'       => $ticket->id,
            'subject'  => $ticket->subject,
            'status'   => $ticket->status,
            'priority' => $ticket->priority,
            'category' => $ticket->category ? $pick($ticket->category->name_fr, $ticket->category->name_en) : null,
            'replies'  => $ticket->replies->map(fn ($r) => [
                'id'             => $r->id,
                'body'           => $r->body_fr,
                'is_staff_reply' => $r->is_staff,
                'author'         => $r->user?->name,
                'created_at'     => $r->created_at?->toIso8601String(),
            ]),
            'created_at' => $ticket->created_at?->toIso8601String(),
        ]]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->findOrFail($id);

        $reply = SupportTicketReply::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => $request->user()->id,
            'body_fr'        => $request->body,
            'is_staff'       => false,
        ]);

        $ticket->update(['status' => 'in_progress']);

        return response()->json(['data' => ['id' => $reply->id, 'created_at' => $reply->created_at?->toIso8601String()]], 201);
    }
}
