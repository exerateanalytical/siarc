<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\VerificationApplication;
use App\Modules\Businesses\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBusinessController extends Controller
{
    public function __construct(private readonly VerificationService $verificationService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Business::withTrashed()
            ->with(['industry', 'region', 'user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tier')) {
            $query->where('verification_tier', $request->tier);
        }
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('name_fr', 'like', $search)->orWhere('name_en', 'like', $search));
        }

        $businesses = $query->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => collect($businesses->items())->map(fn ($b) => [
                'id'                => $b->id,
                'slug'              => $b->slug,
                'name'              => $b->name_fr,
                'user_email'        => $b->user?->email,
                'verification_tier' => $b->verification_tier,
                'status'            => $b->status,
                'industry'          => $b->industry?->name_fr,
                'created_at'        => $b->created_at?->toIso8601String(),
                'deleted_at'        => $b->deleted_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $businesses->total(), 'last_page' => $businesses->lastPage()],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => ['required', 'in:draft,published,suspended,rejected']]);

        $business = Business::withTrashed()->findOrFail($id);
        $business->update(['status' => $request->status]);

        return response()->json(['status' => $business->status]);
    }

    public function verificationQueue(Request $request): JsonResponse
    {
        $applications = VerificationApplication::with(['business', 'documents'])
            ->where('status', 'pending')
            ->oldest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => collect($applications->items())->map(fn ($a) => [
                'id'             => $a->id,
                'business'       => ['id' => $a->business->id, 'name' => $a->business->name_fr, 'slug' => $a->business->slug],
                'current_tier'   => $a->current_tier,
                'requested_tier' => $a->requested_tier,
                'documents'      => $a->documents->map(fn ($d) => [
                    'document_type'     => $d->document_type,
                    'original_filename' => $d->original_filename,
                ]),
                'submitted_at'   => $a->submitted_at?->toIso8601String(),
            ]),
            'meta' => ['total' => $applications->total()],
        ]);
    }

    public function approveVerification(Request $request, int $applicationId): JsonResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        $application = VerificationApplication::where('status', 'pending')->findOrFail($applicationId);
        $this->verificationService->approve($application, $request->user(), $request->notes);

        return response()->json(['message' => 'Verification approved.', 'new_tier' => $application->requested_tier]);
    }

    public function rejectVerification(Request $request, int $applicationId): JsonResponse
    {
        $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $application = VerificationApplication::where('status', 'pending')->findOrFail($applicationId);
        $this->verificationService->reject($application, $request->user(), $request->notes);

        return response()->json(['message' => 'Verification rejected.']);
    }

    public function featureBusiness(Request $request, int $id): JsonResponse
    {
        $request->validate(['featured_until' => ['nullable', 'date', 'after:today']]);

        $business = Business::findOrFail($id);
        $business->update([
            'is_featured'    => true,
            'featured_until' => $request->featured_until,
        ]);

        return response()->json(['message' => 'Business featured.']);
    }

    public function unfeatureBusiness(int $id): JsonResponse
    {
        $business = Business::findOrFail($id);
        $business->update(['is_featured' => false, 'featured_until' => null]);

        return response()->json(['message' => 'Business unfeatured.']);
    }
}
