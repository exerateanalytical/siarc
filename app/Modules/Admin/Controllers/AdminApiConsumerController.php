<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApiProduct\Models\ApiConsumer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiConsumerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $consumers = ApiConsumer::with(['user', 'keys'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        return response()->json([
            'data' => collect($consumers->items())->map(fn ($c) => [
                'id'         => $c->id,
                'app_name'   => $c->app_name,
                'use_case'   => $c->use_case,
                'user_email' => $c->user?->email,
                'status'     => $c->status,
                'keys_count' => $c->keys->count(),
                'created_at' => $c->created_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $consumers->total()],
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $consumer = ApiConsumer::where('status', 'pending')->findOrFail($id);
        $consumer->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);

        return response()->json(['message' => 'API consumer approved.']);
    }

    public function reject(int $id): JsonResponse
    {
        $consumer = ApiConsumer::where('status', 'pending')->findOrFail($id);
        $consumer->update(['status' => 'rejected']);

        return response()->json(['message' => 'API consumer rejected.']);
    }
}
