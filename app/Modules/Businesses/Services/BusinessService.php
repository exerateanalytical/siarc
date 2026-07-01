<?php

namespace App\Modules\Businesses\Services;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\BusinessSocialLink;
use App\Modules\Businesses\Models\BusinessTag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class BusinessService
{
    public function __construct(private readonly ImageUploadService $imageUpload) {}

    public function create(User $user, array $data): Business
    {
        $business = Business::create([
            'user_id'          => $user->id,
            'industry_id'      => $data['industry_id'],
            'region_id'        => $data['region_id'] ?? null,
            'city_id'          => $data['city_id'] ?? null,
            'name_fr'          => $data['name_fr'],
            'name_en'          => $data['name_en'] ?? null,
            'tagline_fr'       => $data['tagline_fr'] ?? null,
            'tagline_en'       => $data['tagline_en'] ?? null,
            'description_fr'   => $data['description_fr'] ?? null,
            'description_en'   => $data['description_en'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'whatsapp'         => $data['whatsapp'] ?? null,
            'email'            => $data['email'] ?? null,
            'website'          => $data['website'] ?? null,
            'address_fr'       => $data['address_fr'] ?? null,
            'address_en'       => $data['address_en'] ?? null,
            'gps_lat'          => $data['gps_lat'] ?? null,
            'gps_lng'          => $data['gps_lng'] ?? null,
            'year_established' => $data['year_established'] ?? null,
            'employee_count'   => $data['employee_count'] ?? null,
            'ownership_type'   => $data['ownership_type'] ?? null,
            'export_countries' => $data['export_countries'] ?? null,
            'languages_spoken' => $data['languages_spoken'] ?? null,
            'verification_tier' => 'unverified',
            'status'           => 'draft',
        ]);

        $user->assignRole('business_owner');

        if (! empty($data['social_links'])) {
            $this->syncSocialLinks($business, $data['social_links']);
        }

        if (! empty($data['tags'])) {
            $this->syncTags($business, $data['tags']);
        }

        return $business->fresh(['industry', 'region', 'city', 'socialLinks', 'tags']);
    }

    public function update(Business $business, array $data): Business
    {
        $business->update(Arr::except($data, ['logo', 'cover_image', 'social_links', 'tags']));

        if (! empty($data['social_links'])) {
            $this->syncSocialLinks($business, $data['social_links']);
        }

        if (array_key_exists('tags', $data)) {
            $this->syncTags($business, $data['tags'] ?? []);
        }

        return $business->fresh(['industry', 'region', 'city', 'socialLinks', 'tags']);
    }

    public function updateLogo(Business $business, UploadedFile $file): Business
    {
        if ($business->logo) {
            $this->imageUpload->delete($business->logo);
        }
        $path = $this->imageUpload->uploadLogo($file, $business->slug);
        $business->update(['logo' => $path]);
        return $business;
    }

    public function updateCover(Business $business, UploadedFile $file): Business
    {
        if ($business->cover_image) {
            $this->imageUpload->delete($business->cover_image);
        }
        $path = $this->imageUpload->uploadCover($file, $business->slug);
        $business->update(['cover_image' => $path]);
        return $business;
    }

    public function publish(Business $business): Business
    {
        $business->update(['status' => 'published']);
        return $business;
    }

    private function syncSocialLinks(Business $business, array $links): void
    {
        $business->socialLinks()->delete();
        foreach ($links as $link) {
            BusinessSocialLink::create([
                'business_id' => $business->id,
                'platform'    => $link['platform'],
                'url'         => $link['url'],
            ]);
        }
    }

    private function syncTags(Business $business, array $tags): void
    {
        $business->tags()->delete();
        foreach (array_unique(array_slice($tags, 0, 15)) as $tag) {
            BusinessTag::create([
                'business_id' => $business->id,
                'tag'         => mb_strtolower(trim($tag)),
            ]);
        }
    }
}
