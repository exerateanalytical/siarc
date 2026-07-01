<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Support\Models\SupportCategory;
use App\Modules\Support\Models\SupportTicket;
use App\Modules\Support\Models\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportWebController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function requireUser(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }
        return $siacUser;
    }

    private function requireAdmin(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || empty($siacUser['is_admin'])) {
            return redirect('/login');
        }
        return $siacUser;
    }

    /**
     * My tickets (buyer/business owner side).
     */
    public function index(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $tickets = SupportTicket::where('user_id', $siacUser['id'])->with('category')->latest()->get();
        $categories = SupportCategory::orderBy('sort_order')->get();

        return view('pages.dashboard.support-index', compact('lang', 'siacUser', 'tickets', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:support_categories,id'],
            'subject'     => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string', 'max:3000'],
        ]);

        $ticket = SupportTicket::create([
            'user_id'     => $siacUser['id'],
            'category_id' => $data['category_id'] ?? null,
            'subject_fr'  => $data['subject'],
            'subject_en'  => $data['subject'],
            'status'      => 'open',
            'priority'    => 'medium',
        ]);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $siacUser['id'],
            'body_fr'   => $data['body'],
            'body_en'   => $data['body'],
            'is_staff'  => false,
        ]);

        return redirect()->route('support.show', ['id' => $ticket->id])
            ->with('success', $lang === 'fr' ? 'Ticket créé.' : 'Ticket created.');
    }

    public function show(Request $request, int $id)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $ticket = SupportTicket::with(['replies.user', 'category'])->findOrFail($id);

        if ($ticket->user_id !== $siacUser['id'] && empty($siacUser['is_admin'])) {
            abort(403);
        }

        return view('pages.dashboard.support-show', compact('lang', 'siacUser', 'ticket'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $ticket = SupportTicket::findOrFail($id);
        if ($ticket->user_id !== $siacUser['id'] && empty($siacUser['is_admin'])) {
            abort(403);
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);
        $isStaff = ! empty($siacUser['is_admin']);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $siacUser['id'],
            'body_fr'   => $data['body'],
            'body_en'   => $data['body'],
            'is_staff'  => $isStaff,
        ]);

        $ticket->update(['status' => $isStaff ? 'in_progress' : 'open']);

        if ($isStaff) {
            \App\Modules\Notifications\Models\UserNotification::notify(
                $ticket->user_id, 'ticket_reply',
                $lang === 'fr' ? 'Réponse au support' : 'Support reply',
                $lang === 'fr' ? 'Le support a répondu à votre ticket.' : 'Support replied to your ticket.',
                route('support.show', ['id' => $ticket->id])
            );
        }

        return redirect()->route('support.show', ['id' => $id]);
    }

    /**
     * Admin ticket list.
     */
    public function adminIndex(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $status = $request->get('status', 'open');
        $tickets = SupportTicket::with(['user', 'category'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return view('pages.dashboard.admin-support', compact('lang', 'tickets', 'status'));
    }

    public function close(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => 'closed', 'resolved_at' => now()]);

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'support.ticket_closed', 'support_ticket', $ticket->id);

        return back()->with('success', $lang === 'fr' ? 'Ticket fermé.' : 'Ticket closed.');
    }
}
