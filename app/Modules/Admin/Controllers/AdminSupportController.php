<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSupportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['user', 'category'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->latest();

        $tickets = $query->paginate(max(1, min($request->integer('per_page', 25), 100)));

        return response()->json([
            'data' => collect($tickets->items())->map(fn ($t) => [
                'id'          => $t->id,
                'subject'     => $t->subject,
                'status'      => $t->status,
                'priority'    => $t->priority,
                'user_email'  => $t->user?->email,
                'reply_count' => $t->replies()->count(),
                'created_at'  => $t->created_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $tickets->total(), 'last_page' => $tickets->lastPage()],
        ]);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $ticket = SupportTicket::findOrFail($id);

        SupportTicketReply::create([
            'ticket_id'      => $ticket->id,
            'user_id'        => $request->user()->id,
            'body'           => $request->body,
            'is_staff_reply' => true,
        ]);

        $ticket->update(['status' => 'open']);

        return response()->json(['message' => 'Reply sent.']);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return response()->json(['message' => 'Ticket closed.']);
    }
}
