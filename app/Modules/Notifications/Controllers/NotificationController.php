<?php

namespace App\Modules\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Models\NotificationLog;
use App\Modules\Notifications\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $lang  = $request->header('Accept-Language', 'fr');
        $pick  = fn ($fr, $en) => ($lang === 'en' && $en) ? $en : $fr;

        $notifications = NotificationLog::where('user_id', $user->id)
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 30), 100)));

        return response()->json([
            'data' => collect($notifications->items())->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $pick($n->title_fr, $n->title_en),
                'body'       => $pick($n->body_fr, $n->body_en),
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'total'        => $notifications->total(),
                'unread_count' => NotificationLog::where('user_id', $user->id)->whereNull('read_at')->count(),
                'last_page'    => $notifications->lastPage(),
            ],
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->validate(['ids' => ['nullable', 'array'], 'ids.*' => ['integer']]);

        $query = NotificationLog::where('user_id', $request->user()->id)->whereNull('read_at');

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function preferences(Request $request): JsonResponse
    {
        $prefs = NotificationPreference::where('user_id', $request->user()->id)->get();

        return response()->json(['data' => $prefs->map(fn ($p) => [
            'type'           => $p->type,
            'email_enabled'  => $p->email_enabled,
            'push_enabled'   => $p->push_enabled,
            'in_app_enabled' => $p->in_app_enabled,
        ])]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'preferences'                  => ['required', 'array'],
            'preferences.*.type'           => ['required', 'string', 'max:100'],
            'preferences.*.email_enabled'  => ['boolean'],
            'preferences.*.push_enabled'   => ['boolean'],
            'preferences.*.in_app_enabled' => ['boolean'],
        ]);

        $userId = $request->user()->id;

        foreach ($request->preferences as $pref) {
            NotificationPreference::updateOrCreate(
                ['user_id' => $userId, 'type' => $pref['type']],
                [
                    'email_enabled'  => $pref['email_enabled'] ?? true,
                    'push_enabled'   => $pref['push_enabled'] ?? true,
                    'in_app_enabled' => $pref['in_app_enabled'] ?? true,
                ]
            );
        }

        return response()->json(['message' => 'Preferences updated.']);
    }
}
