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
            // Carried over from what they chose at signup, so the directory's
            // artisan / cooperative / business facets reflect the member's own
            // answer instead of everyone defaulting to `artisan`.
            'vendor_type'      => \App\Support\AccountTypes::vendorType($user->account_type) ?? 'artisan',
            'industry_id'      => $data['industry_id'],
            // Country is denormalised onto the business so the public
            // directory can filter on one indexed column. Derived from the
            // region when the form did not send it, so a record can never end
            // up in a region of one country and the directory of another.
            'country_id'       => $data['country_id'] ?? self::countryOfRegion($data['region_id'] ?? null),
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
            'ownership_type'   => $data['ownership_type'] ?? 'private',
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

    /**
     * The country a region belongs to, or null.
     *
     * Used so a business never carries a region of one country and a country
     * column saying another — the public directory groups on the country
     * column, so a mismatch would list an artisan under the wrong flag.
     */
    private static function countryOfRegion(?int $regionId): ?int
    {
        if (! $regionId) {
            return null;
        }

        return \App\Modules\Taxonomy\Models\Region::whereKey($regionId)->value('country_id');
    }

    public function update(Business $business, array $data): Business
    {
        // Keep the two in step however the caller filled the form in. A region
        // change with no country change is the ordinary case on edit, and it
        // must move the business to the right country rather than leave it.
        if (array_key_exists('region_id', $data)) {
            $derived = self::countryOfRegion($data['region_id'] ?? null);
            if ($derived !== null) {
                $data['country_id'] = $derived;
            }
        }

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

    /**
     * Put a business in front of the public.
     *
     * This is the single choke point through which a business reaches
     * `published`, which is why the fee gate lives here rather than in each
     * controller: a gate a caller has to remember to ask about is a gate that
     * eventually gets forgotten, and the thing forgotten would be a free
     * listing.
     *
     * What the subscription buys is exactly this line and nothing more. It does
     * not buy ownership of the record — an artisan who has not paid still owns
     * their profile, can correct it and can ask for it to be removed. That
     * separation is what lets the imported SIARC artisans, who never signed up,
     * take control of their own data for nothing; see App\Support\PlatformFees.
     *
     * It throws rather than returning the business unchanged because a caller
     * that ignored a silent refusal would tell a member their shop is live when
     * it is not, and they would wait for orders that cannot come.
     */
    public function publish(Business $business): Business
    {
        if (\App\Support\PlatformFees::requiresPaymentToPublish($business)) {
            throw new \DomainException(
                "Business {$business->id} has no active subscription; publication is what the subscription buys, "
                . 'so it stays a draft until a reviewer confirms a payment.'
            );
        }

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
