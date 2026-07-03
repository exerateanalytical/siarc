<?php

namespace Database\Seeders;

use App\Modules\Businesses\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds "Art Bois Nature" (Yaoundé, Centre), the artisan referenced by the
 * quotation-flow designs ("create un demande.png" summary rail and the
 * "quote propositions.png" listing), so its profile links open a real,
 * admin-editable business. Idempotent — safe to re-run.
 */
class DesignQuoteVendorSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = Business::where('status', 'published')->orderBy('id')->value('user_id');
        if (! $ownerId) {
            $this->command?->warn('No published business found to borrow an owner from — aborting.');
            return;
        }

        $slug = 'art-bois-nature';
        $relPath = "businesses/{$slug}/cover.png";
        $absPath = storage_path("app/public/{$relPath}");
        $landing = public_path('images/landing/qb-artbois.png');
        if (! File::exists($absPath) && File::exists($landing)) {
            File::ensureDirectoryExists(dirname($absPath));
            File::copy($landing, $absPath);
        }

        Business::withTrashed()->updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'           => $ownerId,
                'industry_id'       => \DB::table('industries')->where('slug', 'bois-sculpture')->value('id'),
                'region_id'         => 1,
                'city_id'           => 1,
                'name_fr'           => 'Art Bois Nature',
                'name_en'           => 'Art Bois Nature',
                'tagline_fr'        => 'Mobilier et objets en bois massif façonnés à la main.',
                'tagline_en'        => 'Solid-wood furniture and objects shaped by hand.',
                'description_fr'    => 'Mobilier et objets en bois massif façonnés à la main.',
                'description_en'    => 'Solid-wood furniture and objects shaped by hand.',
                'cover_image'       => $relPath,
                'ownership_type'    => 'private',
                'verification_tier' => 'verified',
                'status'            => 'published',
                'deleted_at'        => null,
            ]
        );

        $this->command?->info('Design quote vendor seeded: Art Bois Nature');
    }
}
