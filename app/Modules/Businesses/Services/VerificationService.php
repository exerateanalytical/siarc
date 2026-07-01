<?php

namespace App\Modules\Businesses\Services;

use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\VerificationApplication;
use App\Modules\Businesses\Models\VerificationDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationService
{
    public function apply(Business $business, string $requestedTier, array $documents): VerificationApplication
    {
        // Cancel any pending application
        $business->verificationApplications()
                 ->where('status', 'pending')
                 ->update(['status' => 'cancelled']);

        $application = VerificationApplication::create([
            'business_id'    => $business->id,
            'requested_tier' => $requestedTier,
            'current_tier'   => $business->verification_tier,
            'status'         => 'pending',
            'submitted_at'   => now(),
        ]);

        foreach ($documents as $docData) {
            /** @var UploadedFile $file */
            $file = $docData['file'];
            $path = "businesses/{$business->slug}/verification/" . Str::uuid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('s3')->put($path, $file->getContent(), 'private');

            VerificationDocument::create([
                'verification_application_id' => $application->id,
                'document_type'               => $docData['document_type'],
                'file_path'                   => $path,
                'original_filename'           => $file->getClientOriginalName(),
                'file_size'                   => $file->getSize(),
            ]);
        }

        return $application->load('documents');
    }

    public function approve(VerificationApplication $application, \App\Modules\Auth\Models\User $admin, ?string $notes = null): void
    {
        $application->update([
            'status'      => 'approved',
            'admin_notes' => $notes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $application->business->update(['verification_tier' => $application->requested_tier]);
    }

    public function reject(VerificationApplication $application, \App\Modules\Auth\Models\User $admin, string $notes): void
    {
        $application->update([
            'status'      => 'rejected',
            'admin_notes' => $notes,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
    }
}
