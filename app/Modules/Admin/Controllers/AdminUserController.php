<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::withTrashed()->with('roles')->latest();

        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('email', 'like', $search));
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(max(1, min($request->integer('per_page', 25), 100)));

        return response()->json([
            'data' => collect($users->items())->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone,
                'roles'      => $u->roles->pluck('name'),
                'status'     => $u->status,
                'created_at' => $u->created_at?->toIso8601String(),
                'deleted_at' => $u->deleted_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $users->total(), 'last_page' => $users->lastPage()],
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:active,suspended,banned']]);

        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot change your own status.'], 422);
        }

        $user->update(['status' => $request->status]);

        return response()->json(['status' => $user->status]);
    }

    public function assignRole(Request $request, string $id): JsonResponse
    {
        $request->validate(['role' => ['required', 'string', 'exists:roles,name']]);

        $user = User::findOrFail($id);
        $user->assignRole($request->role);

        return response()->json(['roles' => $user->getRoleNames()]);
    }

    public function removeRole(Request $request, string $id): JsonResponse
    {
        $request->validate(['role' => ['required', 'string']]);

        $user = User::findOrFail($id);
        $user->removeRole($request->role);

        return response()->json(['roles' => $user->getRoleNames()]);
    }
}
