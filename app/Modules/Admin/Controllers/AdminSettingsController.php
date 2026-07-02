<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        // Secret rows (encrypted API credentials) never leave the server
        $settings = DB::table('system_settings')->where('is_secret', false)->get(['key', 'value', 'type', 'group']);

        return response()->json(['data' => $settings->groupBy('group')]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings'            => ['required', 'array'],
            'settings.*.key'      => ['required', 'string', 'max:100'],
            'settings.*.value'    => ['required'],
        ]);

        foreach ($request->settings as $setting) {
            DB::table('system_settings')
                ->where('key', $setting['key'])
                ->where('is_secret', false) // credentials only change via the admin settings page
                ->update(['value' => $setting['value'], 'updated_at' => now()]);
        }

        \App\Modules\Admin\Services\SystemSettings::flush();

        return response()->json(['message' => 'Settings updated.']);
    }

    public function featureFlags(): JsonResponse
    {
        $flags = DB::table('feature_flags')->get(['name', 'is_enabled', 'description']);

        return response()->json(['data' => $flags]);
    }

    public function toggleFlag(Request $request, string $name): JsonResponse
    {
        $flag = DB::table('feature_flags')->where('name', $name)->first();
        if (! $flag) {
            return response()->json(['message' => 'Feature flag not found.'], 404);
        }

        DB::table('feature_flags')->where('name', $name)->update([
            'is_enabled' => ! $flag->is_enabled,
            'updated_at' => now(),
        ]);

        return response()->json(['is_enabled' => ! $flag->is_enabled]);
    }

    public function auditLog(Request $request): JsonResponse
    {
        $query = DB::table('audit_logs')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate($request->integer('per_page', 30));

        return response()->json([
            'data' => $logs->items(),
            'meta' => ['total' => $logs->total(), 'last_page' => $logs->lastPage()],
        ]);
    }
}
