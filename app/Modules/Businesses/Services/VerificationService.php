<?php

namespace App\Modules\Businesses\Services;

use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\VerificationApplication;
use App\Modules\Businesses\Models\VerificationDocument;
use App\Modules\Notifications\Models\UserNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationService
{
    public function apply(Business $business, string $requestedTier, array $documents): VerificationApplication
    {
        $application = VerificationApplication::create([
            'business_id'    => $business->id,
            'tier_requested' => $requestedTier,
            'status'         => 'submitted',
            'submitted_at'   => now(),
        ]);

        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        foreach ($documents as $docData) {
            /** @var UploadedFile $file */
            $file = $docData['file'];
            $path = "businesses/{$business->slug}/verification/" . Str::uuid() . '.' . $file->getClientOriginalExtension();
            Storage::disk($disk)->put($path, $file->getContent());

            VerificationDocument::create([
                'application_id' => $application->id,
                'type'           => $docData['document_type'],
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'status'         => 'pending',
            ]);
        }

        return $application->load('documents');
    }

    public function approve(VerificationApplication $application, \App\Modules\Auth\Models\User $admin, ?string $notes = null): void
    {
        $application->update([
            'status'         => 'approved',
            'reviewer_notes' => $notes,
            'reviewer_id'    => $admin->id,
            'reviewed_at'    => now(),
        ]);

        $application->business->update(['verification_tier' => $application->tier_requested]);

        UserNotification::notify(
            $application->business->user_id,
            'verification_approved',
            'Vérification approuvée',
            "Votre demande de niveau {$application->tier_requested} a été approuvée.",
            route('verification.show')
        );
    }

    public function reject(VerificationApplication $application, \App\Modules\Auth\Models\User $admin, string $notes): void
    {
        $application->update([
            'status'         => 'rejected',
            'reviewer_notes' => $notes,
            'reviewer_id'    => $admin->id,
            'reviewed_at'    => now(),
        ]);

        UserNotification::notify(
            $application->business->user_id,
            'verification_rejected',
            'Vérification rejetée',
            $notes,
            route('verification.show')
        );
    }
}
