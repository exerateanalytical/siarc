<?php

namespace App\Modules\Businesses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(private readonly VerificationService $service) {}

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'requested_tier'              => ['required', 'in:basic,verified,certified'],
            'documents'                   => ['required', 'array', 'min:1', 'max:10'],
            'documents.*.document_type'   => ['required', 'string', 'max:100'],
            'documents.*.file'            => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $application = $this->service->apply($business, $request->requested_tier, $request->documents);

        return response()->json([
            'data' => [
                'id'             => $application->id,
                'status'         => $application->status,
                'requested_tier' => $application->requested_tier,
                'submitted_at'   => $application->submitted_at->toIso8601String(),
            ],
        ], 201);
    }

    public function status(Request $request): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $applications = $business->verificationApplications()->latest()->take(5)->get();

        return response()->json([
            'data' => [
                'current_tier' => $business->verification_tier,
                'applications' => $applications->map(fn ($a) => [
                    'id'             => $a->id,
                    'requested_tier' => $a->requested_tier,
                    'status'         => $a->status,
                    'submitted_at'   => $a->submitted_at?->toIso8601String(),
                    'reviewed_at'    => $a->reviewed_at?->toIso8601String(),
                    'admin_notes'    => $a->admin_notes,
                ]),
            ],
        ]);
    }
}
