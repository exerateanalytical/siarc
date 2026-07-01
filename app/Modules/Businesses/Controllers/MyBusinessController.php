<?php

namespace App\Modules\Businesses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Requests\CreateBusinessRequest;
use App\Modules\Businesses\Requests\UpdateBusinessRequest;
use App\Modules\Businesses\Resources\BusinessResource;
use App\Modules\Businesses\Services\BusinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyBusinessController extends Controller
{
    public function __construct(private readonly BusinessService $service) {}

    public function show(Request $request): JsonResponse
    {
        $business = $request->user()->business;

        if (! $business) {
            return response()->json(['message' => __('No business profile found.')], 404);
        }

        $business->load(['industry', 'region', 'city', 'gallery', 'socialLinks', 'certifications', 'awards', 'tags']);

        return response()->json(['data' => new BusinessResource($business)]);
    }

    public function store(CreateBusinessRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->business) {
            return response()->json(['message' => 'You already have a business profile.'], 422);
        }

        $business = $this->service->create($user, $request->validated());

        return response()->json(['data' => new BusinessResource($business)], 201);
    }

    public function update(UpdateBusinessRequest $request): JsonResponse
    {
        $business = $request->user()->business;

        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $this->authorize('update', $business);
        $business = $this->service->update($business, $request->validated());

        return response()->json(['data' => new BusinessResource($business)]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate(['logo' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp']]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $business = $this->service->updateLogo($business, $request->file('logo'));

        return response()->json(['logo_url' => $business->logo_url]);
    }

    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate(['cover' => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp']]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $business = $this->service->updateCover($business, $request->file('cover'));

        return response()->json(['cover_url' => $business->cover_url]);
    }

    public function publish(Request $request): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $business = $this->service->publish($business);

        return response()->json(['status' => $business->status]);
    }
}
