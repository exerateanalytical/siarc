<?php

namespace App\Modules\Businesses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\BusinessDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $docs = $business->documents()->latest()->get()->map(fn ($d) => [
            'id'                => $d->id,
            'document_type'     => $d->document_type,
            'original_filename' => $d->original_filename,
            'file_size'         => $d->file_size,
            'is_verified'       => $d->is_verified,
            'verified_at'       => $d->verified_at?->toIso8601String(),
            'expires_at'        => $d->expires_at?->toIso8601String(),
            'created_at'        => $d->created_at?->toIso8601String(),
        ]);

        return response()->json(['data' => $docs]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'file'          => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'expires_at'    => ['nullable', 'date'],
        ]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $file  = $request->file('file');
        $path  = "businesses/{$business->slug}/documents/" . Str::uuid() . '.' . $file->getClientOriginalExtension();
        Storage::disk('s3')->put($path, $file->getContent(), 'private');

        $doc = BusinessDocument::create([
            'business_id'       => $business->id,
            'document_type'     => $request->document_type,
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
            'expires_at'        => $request->expires_at,
        ]);

        return response()->json(['data' => ['id' => $doc->id, 'document_type' => $doc->document_type]], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $doc = $business->documents()->findOrFail($id);
        Storage::disk('s3')->delete($doc->file_path);
        $doc->delete();

        return response()->json(null, 204);
    }
}
