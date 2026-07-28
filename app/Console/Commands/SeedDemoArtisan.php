<?php

namespace App\Console\Commands;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Builds the one artisan the two profile mockups depict, as real rows.
 *
 * WHY THIS EXISTS. `certificates/artisan profile v2 desktop.png` and
 * `certificates/artisan mpbile profile v2.png` show a full profile: six pieces,
 * a hundred and twenty-eight reviews, eight distinctions, a review histogram, a
 * workshop. The database holds one product and nothing else, so every slot on
 * the page renders its empty state and the built page reads as a skeleton. This
 * command fills the slots from the artwork so the template can be looked at.
 *
 * WHAT IT IS NOT. It is not a fixture generator, not a factory, and it is not
 * wired into `db:seed` — a fresh install must never grow a fabricated artisan on
 * its own. Everything it writes hangs off a single business carrying
 * `is_demo = 1`, so one predicate separates it from the 510 imported SIARC
 * profiles, which are records of living people and are never touched here.
 *
 * Jean Paul Mbatchou does not exist. The awards, the exhibition history and the
 * hundred and twenty-eight reviewers are inventions of the mockup. That is
 * exactly why the flag is on the row and why `--purge` is part of the same
 * command rather than a note in a README: the record has to be as easy to
 * remove as it was to create, and removing it has to be provable.
 *
 * NO MAIL, EVER. `Mail::fake()` is the first statement in `handle()`, before a
 * single row is read. 128 reviewer accounts are created here and every one of
 * them is written with `email = NULL` and an unusable random password — the same
 * rule the SIARC import follows. That is two independent guarantees: even if a
 * model observer fired a notification, there would be no address to send it to,
 * and the mailer is captured anyway.
 *
 * IDEMPOTENCE. A run purges the previous record first, then rebuilds. There is
 * no upsert path and no partial-update path, because either would let a field
 * that was renamed in the artwork survive in the database as a ghost of the
 * previous run.
 */
class SeedDemoArtisan extends Command
{
    protected $signature = 'demo:artisan
                            {--purge : Remove the demo artisan and every row created with it}';

    protected $description = 'Create (or purge) the demonstration artisan the profile mockups depict';

    /** The slug is the handle everything else is found by, on create and on purge. */
    private const SLUG = 'jean-paul-mbatchou';

    /** Where the cropped mockup artwork is served from, relative to public/. */
    private const IMAGE_DIR = 'images/demo';

    /**
     * Photographs cropped out of the two mockup PNGs.
     *
     * The design's own renders are the only images that can be used: any other
     * photograph of a carving would be a real artisan's work attached to an
     * invented name. Boxes were found by scanning the PNGs for the runs of
     * non-background pixels that bound each card, not by eye — see the report in
     * the commit. The mobile export is 864px wide (2× a 432px viewport) and
     * carries the largest crops, so it supplies the portrait, the hero carving
     * and four of the six pieces; the desktop export supplies the two pieces the
     * mobile design omits, the reviewer's avatar and the photo attached to her
     * review.
     *
     * [source, x, y, width, height, output basename, upscale]
     */
    private const CROPS = [
        'portrait'        => ['mobile',   45,  181, 208, 187, 'jean-paul-mbatchou-portrait.png',  3],
        'cover'           => ['mobile',  622,  168, 224, 312, 'mbatchou-hero-carving.png',        3],
        'heritage-mask'   => ['mobile',   32, 1087, 189, 178, 'heritage-fang-mask.png',           3],
        'royal-couple'    => ['mobile',  235, 1087, 190, 178, 'royal-couple-sculpture.png',       3],
        'warrior'         => ['mobile',  438, 1087, 189, 178, 'warrior-figure.png',               3],
        'mother-child'    => ['mobile',  641, 1087, 190, 178, 'mother-and-child-sculpture.png',   3],
        'ancestral'       => ['desktop', 180,  797, 151, 150, 'ancestral-wisdom-statue.png',      4],
        'spirit-guardian' => ['desktop', 498,  797, 151, 150, 'spirit-guardian-mask.png',         4],
        'reviewer-avatar' => ['desktop',  33, 1214,  38,  36, 'marie-t-ekassi-avatar.png',       10],
        'review-photo'    => ['desktop', 358, 1231,  41,  51, 'review-heritage-fang-mask.png',    8],
    ];

    /**
     * The six pieces, in the order the desktop artwork lays them out.
     *
     * Prices are read straight off the cards. FCFA is XAF; the cards print no
     * unit, so none is stored.
     *
     * [name_en, name_fr, category slug, price, crop key]
     */
    private const PRODUCTS = [
        ['Heritage Fang Mask',       'Masque Fang du patrimoine',    'masques',            180000, 'heritage-mask'],
        ['Ancestral Wisdom Statue',  'Statue de la sagesse ancestrale', 'wood-sculpture',  250000, 'ancestral'],
        ['Royal Couple Sculpture',   'Sculpture du couple royal',    'heritage-collection', 320000, 'royal-couple'],
        ['Spirit Guardian Mask',     "Masque du gardien des esprits", 'masques',            150000, 'spirit-guardian'],
        ['Warrior Figure',           'Figure du guerrier',           'wood-sculpture',      200000, 'warrior'],
        ['Mother & Child Sculpture', "Sculpture de la mère et l'enfant", 'heritage-collection', 280000, 'mother-child'],
    ];

    /**
     * The exact histogram drawn in the desktop artwork.
     *
     * Worth recording that these bars do not produce the 4.9 the same card
     * prints beside them: 104·5 + 18·4 + 4·3 + 1·2 + 1·1 = 607, over 128, is
     * 4.7. The artwork is internally inconsistent and the bars are the more
     * specific statement, so the bars win and `ArtisanProfile::reviews()`
     * computes 4.7 from them. Writing 128 rows whose mean is 4.9 would require
     * more five-star reviews than the histogram shows, i.e. contradicting the
     * drawing in order to match a number printed next to it.
     */
    private const RATING_DISTRIBUTION = [5 => 104, 4 => 18, 3 => 4, 2 => 1, 1 => 1];

    /**
     * The four distinctions the artwork names, with the second line of each card
     * stored verbatim in `issuer` — that is the only field the awards table has
     * for it, and `ArtisanProfile::awards()` reports it unedited as the
     * artisan's own claim.
     *
     * The artwork's statistics card says eight awards but names four; the other
     * four here carry no support in the artwork at all and are flagged as such
     * in the command's own output.
     *
     * [title_en, title_fr, issuer, year, in the artwork?]
     */
    private const AWARDS = [
        ['SIARC Excellence Award',          "Prix d'excellence SIARC",              'Best Wood Sculptor - Centre Region', 2024, true],
        ['National Craft Excellence Award', "Prix national d'excellence artisanale", 'Ministry of Arts & Culture',        2023, true],
        ['UNESCO Craft Recognition',        'Reconnaissance artisanale UNESCO',      'Intangible Cultural Heritage',      2022, true],
        ['African Heritage Expo',           'Expo du patrimoine africain',           'Paris, France',                     2023, true],
        ['SIARC Excellence Award',          "Prix d'excellence SIARC",              'Best Wood Sculptor - Centre Region', 2021, false],
        ['National Craft Excellence Award', "Prix national d'excellence artisanale", 'Ministry of Arts & Culture',        2020, false],
        ['African Heritage Expo',           'Expo du patrimoine africain',           'Dakar, Senegal',                    2019, false],
        ['UNESCO Craft Recognition',        'Reconnaissance artisanale UNESCO',      'Intangible Cultural Heritage',      2018, false],
    ];

    /**
     * Twelve exhibitions — the figure the statistics card prints — and six
     * export events, which between them name eighteen distinct countries, the
     * "Countries Reached" figure on the same card.
     *
     * Two things about this are worth saying plainly. Twelve events cannot name
     * eighteen countries, one country per row, which is why the six export
     * events exist. And `ArtisanProfile::countriesReached()` does not read the
     * provenance register: it counts destinations on export consignments,
     * ownership transfers and later owners' records. So the profile will still
     * report zero countries reached until those registers hold rows, and
     * fabricating consignments and changes of ownership — each of which is a
     * signed, hash-chained assertion elsewhere in this codebase — is a much
     * larger claim than an exhibition line and is deliberately not made here.
     *
     * [type, title, organisation, venue, city, country, year]
     */
    private const PROVENANCE = [
        ['exhibition', 'SIARC 2026',                        'SIARC',                          "Palais des Congrès",        'Yaoundé',      'CM', 2026],
        ['exhibition', 'African Heritage Expo',             'African Heritage Expo',          'Espace Champerret',         'Paris',        'FR', 2023],
        ['exhibition', 'Dak’Art Off',                       "Biennale de Dakar",              'Galerie Nationale',         'Dakar',        'SN', 2022],
        ['exhibition', 'Salon National de l’Artisanat',     "Maison de l'Artisan",            "Maison de l'Artisan",       'Marrakech',    'MA', 2022],
        ['exhibition', 'Cape Town Art Fair',                'Cape Town Art Fair',             'CTICC',                     'Cape Town',    'ZA', 2021],
        ['exhibition', 'African Art Now',                   'African Art Now',                'Brooklyn Expo Center',      'New York',     'US', 2021],
        ['exhibition', 'Kunst und Handwerk Afrika',         'Kunst und Handwerk Afrika',      'Messe Frankfurt',           'Frankfurt',    'DE', 2020],
        ['exhibition', 'London Craft Week',                 'London Craft Week',              'Somerset House',            'London',       'GB', 2020],
        ['exhibition', 'Africa Museum Craft Days',          'AfricaMuseum',                   'AfricaMuseum',              'Tervuren',     'BE', 2019],
        ['exhibition', "Marché des Arts d'Abidjan",         "Marché des Arts",                'Palais de la Culture',      'Abidjan',      'CI', 2019],
        ['exhibition', 'Accra Craft Fair',                  'Accra Craft Fair',               'Accra International Centre', 'Accra',       'GH', 2018],
        ['exhibition', 'Montreal African Art Week',         'African Art Week',               'Place Bonaventure',         'Montreal',     'CA', 2018],
        ['export',     'Consignment to Lagos gallery',      'Terra Kulture',                  null,                        'Lagos',        'NG', 2024],
        ['export',     'Consignment to Nairobi gallery',    'Nairobi Gallery',                null,                        'Nairobi',      'KE', 2023],
        ['export',     'Consignment to Madrid collector',   null,                             null,                        'Madrid',       'ES', 2023],
        ['export',     'Consignment to Milan gallery',      'Galleria Africana',              null,                        'Milan',        'IT', 2022],
        ['export',     'Consignment to Amsterdam gallery',  'Afrika Galerie',                 null,                        'Amsterdam',    'NL', 2021],
        ['export',     'Consignment to Dubai collector',    null,                             null,                        'Dubai',        'AE', 2021],
    ];

    /** The five craft chips on the hero, verbatim. */
    private const TAGS = ['Wood Carving', 'Traditional Sculpture', 'Heritage Art', 'Masks', 'Statues'];

    /**
     * Reviewer names.
     *
     * Only one reviewer is named in the artwork — Marie T. Ekassi, whose review
     * is the single one with a body. The other 127 are rating-only rows and need
     * a name for the list; they are drawn deterministically from these pools so
     * that two runs produce the same 127 people and a diff of the database is
     * empty. They are inventions, like everything else on this record.
     */
    private const FIRST_NAMES = [
        'Marie', 'Jean', 'Paul', 'Estelle', 'Blaise', 'Nadège', 'Serge', 'Aline', 'Hervé', 'Chantal',
        'Guy', 'Sylvie', 'Armand', 'Brigitte', 'Éric', 'Sandrine', 'Roger', 'Pauline', 'Thierry', 'Odile',
        'Franck', 'Clarisse', 'Didier', 'Solange', 'Michel', 'Yvette', 'Alain', 'Josiane', 'Bertrand', 'Rachel',
    ];

    private const SURNAMES = [
        'Ekassi', 'Mbarga', 'Ngassa', 'Fotso', 'Njoya', 'Etoundi', 'Tchoumi', 'Bikoi', 'Manga', 'Nkeng',
        'Sone', 'Abanda', 'Owona', 'Kamdem', 'Essomba', 'Ndongo', 'Tabi', 'Bello', 'Nana', 'Eyenga',
        'Mvondo', 'Talla', 'Ateba', 'Mefire', 'Ngando', 'Djoumessi', 'Ewane', 'Bekolo', 'Nyemb', 'Foning',
    ];

    private array $created = [];

    public function handle(): int
    {
        // First statement, before anything reads or writes. A model observer
        // that fires a notification is captured, not delivered.
        Mail::fake();

        if ($this->option('purge')) {
            $removed = DB::transaction(fn () => $this->purge());
            $this->reportCounts('Purged', $removed);

            return self::SUCCESS;
        }

        // A rebuild, not an upsert: the previous record goes first so no field
        // renamed in the artwork can survive as a ghost of the last run.
        DB::transaction(fn () => $this->purge());

        $images = $this->writeImages();
        if ($images === null) {
            return self::FAILURE;
        }

        DB::transaction(fn () => $this->build($images));

        $this->reportCounts('Created', $this->created);
        $this->newLine();
        $this->warn('This is a DEMO record (businesses.is_demo = 1). Remove it with: php artisan demo:artisan --purge');
        $this->line('  Four of the eight awards, all 18 provenance events and 127 of the 128');
        $this->line('  reviewer names have no support in the artwork and are inventions.');

        return self::SUCCESS;
    }

    /* ─────────────────────────────── Images ────────────────────────────── */

    /**
     * Crops the mockups' own photographs out of the two PNGs.
     *
     * Written twice on purpose. `public/images/demo/` is the canonical location
     * and is served directly; `storage/app/public/demo/` is where the copy the
     * database points at lives, because the profile views resolve an image path
     * through `asset('storage/'.$path)` and a path under `public/images` would
     * 404 there. Both are removed by `--purge`.
     *
     * Returns the stored paths keyed by crop, or null if GD or the artwork is
     * missing — in which case nothing is written at all, since a half-imaged
     * record is worse than none.
     */
    private function writeImages(): ?array
    {
        if (! function_exists('imagecreatefrompng')) {
            $this->error('GD is not available; the mockup artwork cannot be cropped.');

            return null;
        }

        $sources = [
            'mobile'  => base_path('certificates/artisan mpbile profile v2.png'),
            'desktop' => base_path('certificates/artisan profile v2 desktop.png'),
        ];

        foreach ($sources as $label => $path) {
            if (! is_file($path)) {
                $this->error("Missing {$label} mockup: {$path}");

                return null;
            }
        }

        $handles = array_map(fn ($p) => imagecreatefrompng($p), $sources);
        $dirs    = [public_path(self::IMAGE_DIR), storage_path('app/public/demo')];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        $paths = [];

        foreach (self::CROPS as $key => [$src, $x, $y, $w, $h, $file, $scale]) {
            $dst = imagecreatetruecolor($w * $scale, $h * $scale);
            imagecopyresampled($dst, $handles[$src], 0, 0, $x, $y, $w * $scale, $h * $scale, $w, $h);

            foreach ($dirs as $dir) {
                imagepng($dst, $dir . DIRECTORY_SEPARATOR . $file);
            }

            imagedestroy($dst);

            // What the database stores: relative to the public storage disk, so
            // both profile views resolve it.
            $paths[$key] = 'demo/' . $file;
        }

        foreach ($handles as $h) {
            imagedestroy($h);
        }

        $this->created['image files'] = count(self::CROPS) * 2;

        return $paths;
    }

    /* ──────────────────────────────── Build ────────────────────────────── */

    private function build(array $images): void
    {
        $regionId   = DB::table('regions')->where('name_en', 'Centre')->value('id');
        $cityId     = $this->odzaCityId($regionId);
        // "Sculpteur/décorateur sur tous matériaux" in the official taxonomy —
        // matched loosely because the row's accents vary by import.
        $industryId = DB::table('industries')->where('level', 4)
            ->where('name_fr', 'like', 'Sculpteur%')->orderBy('id')->value('id');

        $owner = $this->makeOwner();
        $business = $this->makeBusiness($owner, $images, $regionId, $cityId, $industryId);

        $this->makeWorkshop($business, $regionId, $cityId);
        $this->makeTags($business);
        $this->makeSocialLinks($business);
        $this->makeAwards($business);

        $productIds = $this->makeProducts($business, $images);
        $this->makeProvenance($productIds);
        $this->makeReviews($business, $images);
    }

    /**
     * Odza is a district of Yaoundé and the artwork's stated location. The
     * cities table stops at commune level and has no row for it, so one is
     * created — a real place, correctly attached to the Centre region — rather
     * than printing "Yaoundé" under a design that says Odza. `--purge` removes
     * it again if nothing else has come to reference it.
     */
    private function odzaCityId(?int $regionId): ?int
    {
        if (! $regionId) {
            return null;
        }

        $existing = DB::table('cities')->where('slug', 'odza')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $this->created['cities'] = 1;

        return (int) DB::table('cities')->insertGetId([
            'region_id'  => $regionId,
            'name_fr'    => 'Odza',
            'name_en'    => 'Odza',
            'slug'       => 'odza',
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeOwner(): User
    {
        $user = User::create([
            'name'     => 'Jean Paul Mbatchou',
            'email'    => null,                        // unmailable by construction
            'phone'    => null,                        // the number lives on the business
            'password' => Hash::make(Str::random(48)), // unusable
            'status'   => 'active',
            'account_type' => 'artisan',
            'language_preference' => 'en',
            'is_email_verified'   => 0,
            'is_phone_verified'   => 0,
        ]);

        $roleId = DB::table('roles')->where('name', 'business_owner')->where('guard_name', 'sanctum')->value('id');
        if ($roleId) {
            DB::table('model_has_roles')->updateOrInsert([
                'role_id' => $roleId, 'model_type' => User::class, 'model_id' => $user->id,
            ], []);
        }

        $this->created['users'] = 1;

        return $user;
    }

    private function makeBusiness(User $owner, array $images, ?int $regionId, ?int $cityId, ?int $industryId): Business
    {
        /*
         * The three About paragraphs, transcribed from the desktop artwork.
         */
        $aboutEn = implode("\n\n", [
            'I am a passionate wood sculptor from Cameroon with over 18 years of experience creating authentic traditional and contemporary African art. My work is inspired by our rich cultural heritage, ancestral stories, and the beauty of African identity.',
            'I specialize in hand-carved masks, statues, figurines, and heritage pieces using sustainable wood and traditional techniques passed down through generations.',
            'My mission is to preserve our cultural heritage and share the beauty of African craftsmanship with the world.',
        ]);

        $aboutFr = implode("\n\n", [
            "Je suis un sculpteur sur bois passionné, originaire du Cameroun, avec plus de 18 ans d'expérience dans la création d'un art africain traditionnel et contemporain authentique. Mon travail s'inspire de notre riche patrimoine culturel, des récits ancestraux et de la beauté de l'identité africaine.",
            "Je suis spécialisé dans les masques, statues, figurines et pièces patrimoniales sculptés à la main, à partir de bois durable et selon des techniques traditionnelles transmises de génération en génération.",
            "Ma mission est de préserver notre patrimoine culturel et de partager la beauté de l'artisanat africain avec le monde.",
        ]);

        $business = new Business([
            'user_id'        => $owner->id,
            'industry_id'    => $industryId,
            'region_id'      => $regionId,
            'city_id'        => $cityId,
            'name_fr'        => 'Jean Paul Mbatchou',
            'name_en'        => 'Jean Paul Mbatchou',
            'tagline_en'     => 'Master Wood Sculptor & Cultural Heritage Artist',
            'tagline_fr'     => 'Maître sculpteur sur bois et artiste du patrimoine culturel',
            'description_en' => $aboutEn,
            'description_fr' => $aboutFr,
            'logo'           => $images['portrait'],
            'cover_image'    => $images['cover'],
            'phone'          => '+237 6 91 23 45 67',
            'whatsapp'       => '+237 6 91 23 45 67',
            'email'          => 'info@mbatchouwoodstudio.com',
            'address_en'     => 'Odza, Yaoundé, Centre Region',
            'address_fr'     => 'Odza, Yaoundé, Région du Centre',
            // The coordinates printed on the artwork's map card, verbatim.
            'gps_lat'        => 4.0480,
            'gps_lng'        => 9.7679,
            // "18+ Years Experience" is arithmetic on this and nothing else.
            'year_established' => 2008,
            'ownership_type' => 'private',
            'vendor_type'    => 'artisan',
            'source_metier'  => 'Wood Sculpture & Traditional Art',
            'languages_spoken' => ['English', 'French', 'Duala'],
            'verification_tier' => 'verified',
            'status'         => 'published',
            'is_demo'        => true,
        ]);

        $business->slug = self::SLUG;
        // "Member since July 2026" — read off both mockups.
        $business->created_at = Carbon::create(2026, 7, 1, 9, 0, 0);
        $business->updated_at = $business->created_at;
        $business->save();

        $this->created['businesses'] = 1;

        return $business;
    }

    /**
     * The workshop the artwork names.
     *
     * `businesses` has no field for a trading name distinct from the artisan's
     * own, so "Mbatchou Wood Studio" lives here, which is also where
     * `ArtisanProfile::identity()` reads the workshop name and the country from.
     * The inspection date is the one the artwork prints on its WORKSHOP VISITS
     * row and on the Workshop Verification Certificate card: 15/06/2026.
     */
    private function makeWorkshop(Business $business, ?int $regionId, ?int $cityId): void
    {
        DB::table('workshops')->insert([
            'uuid'          => (string) Str::uuid(),
            'business_id'   => $business->id,
            'name'          => 'Mbatchou Wood Studio',
            'workshop_type' => 'Wood carving studio',
            'legal_status'  => 'sole_trader',
            'established_on' => '2008-01-15',
            'country'       => 'CM',
            'region_id'     => $regionId,
            'city_id'       => $cityId,
            'address'       => 'Odza, Yaoundé',
            'gps_lat'       => 4.0480,
            'gps_lng'       => 9.7679,
            'status'        => 'verified',
            'verification_level' => 5,
            'verified_at'   => '2026-06-15 10:00:00',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->created['workshops'] = 1;
    }

    private function makeTags(Business $business): void
    {
        $rows = array_map(fn ($tag) => [
            'business_id' => $business->id,
            'tag'         => $tag,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], self::TAGS);

        DB::table('business_tags')->insert($rows);
        $this->created['business_tags'] = count($rows);
    }

    /**
     * The six channels the hero and footer show.
     *
     * The URLs point at handles that do not exist, which is the honest state of
     * a fabricated profile; they are stored because the column is `NOT NULL` and
     * a social block with no destination would render as five dead discs anyway.
     */
    private function makeSocialLinks(Business $business): void
    {
        $handles = [
            'facebook'  => 'https://www.facebook.com/mbatchouwoodstudio',
            'instagram' => 'https://www.instagram.com/mbatchouwoodstudio',
            'whatsapp'  => 'https://wa.me/237691234567',
            'youtube'   => 'https://www.youtube.com/@mbatchouwoodstudio',
            'tiktok'    => 'https://www.tiktok.com/@mbatchouwoodstudio',
            'linkedin'  => 'https://www.linkedin.com/in/mbatchouwoodstudio',
        ];

        $rows = [];
        foreach ($handles as $platform => $url) {
            $rows[] = [
                'business_id' => $business->id,
                'platform'    => $platform,
                'url'         => $url,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        DB::table('business_social_links')->insert($rows);
        $this->created['business_social_links'] = count($rows);
    }

    private function makeAwards(Business $business): void
    {
        $rows = [];
        foreach (self::AWARDS as [$titleEn, $titleFr, $issuer, $year, $inArtwork]) {
            $rows[] = [
                'business_id' => $business->id,
                'title_fr'    => $titleFr,
                'title_en'    => $titleEn,
                'issuer'      => $issuer,
                'year'        => $year,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        DB::table('business_awards')->insert($rows);
        $this->created['business_awards'] = count($rows);
    }

    /**
     * The six pieces and their photographs.
     *
     * Two of the three categories the cards print — "Wood Sculpture" and
     * "Heritage Collection" — have no row in `product_categories`, and the
     * profile reads a piece's subtitle from that table, so they are created.
     * They are ordinary craft categories, not inventions of the mockup, and
     * `--purge` drops them again if no other product has started using them.
     */
    private function makeProducts(Business $business, array $images): array
    {
        $categories = [
            'masques'             => DB::table('product_categories')->where('slug', 'masques')->value('id'),
            'wood-sculpture'      => $this->category('wood-sculpture', 'Wood Sculpture', 'Sculpture sur bois'),
            'heritage-collection' => $this->category('heritage-collection', 'Heritage Collection', 'Collection patrimoine'),
        ];

        $ids = [];
        foreach (self::PRODUCTS as $i => [$nameEn, $nameFr, $catSlug, $price, $crop]) {
            $productId = DB::table('products')->insertGetId([
                'uuid'        => (string) Str::uuid(),
                'slug'        => Str::slug($nameEn) . '-mbatchou',
                'business_id' => $business->id,
                'category_id' => $categories[$catSlug] ?? null,
                'origin_region_id' => $business->region_id,
                'name_en'     => $nameEn,
                'name_fr'     => $nameFr,
                'product_type' => $catSlug === 'masques' ? 'Traditional Masks'
                    : ($catSlug === 'wood-sculpture' ? 'Wood Sculpture' : 'Heritage Collection'),
                'price_type'  => 'retail',
                'price_amount' => $price,
                'price_currency' => 'XAF',
                'accepted_currencies' => 'XAF',
                'is_available' => 1,
                'is_retail'   => 1,
                'status'      => 'published',
                'sort_order'  => $i + 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'category'   => 'main',
                'file_path'  => $images[$crop],
                'caption_en' => $nameEn,
                'caption_fr' => $nameFr,
                'is_cover'   => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ids[] = $productId;
        }

        // The photo attached to Marie T. Ekassi's review in the artwork. There
        // is no image column on `business_reviews`, and her review names the
        // mask, so it is stored as a second photograph of that piece rather than
        // being dropped.
        DB::table('product_images')->insert([
            'product_id' => $ids[0],
            'category'   => 'other',
            'file_path'  => $images['review-photo'],
            'caption_en' => 'Heritage Fang Mask, photographed by a buyer',
            'caption_fr' => "Masque Fang du patrimoine, photographié par un acheteur",
            'is_cover'   => 0,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->created['products'] = count($ids);
        $this->created['product_images'] = count($ids) + 1;

        return $ids;
    }

    private function category(string $slug, string $nameEn, string $nameFr): int
    {
        $existing = DB::table('product_categories')->where('slug', $slug)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $this->created['product_categories'] = ($this->created['product_categories'] ?? 0) + 1;

        return (int) DB::table('product_categories')->insertGetId([
            'slug'       => $slug,
            'name_en'    => $nameEn,
            'name_fr'    => $nameFr,
            'depth'      => 0,
            'sort_order' => 0,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeProvenance(array $productIds): void
    {
        $rows = [];
        foreach (self::PROVENANCE as $i => [$type, $title, $org, $venue, $city, $country, $year]) {
            $rows[] = [
                'uuid'         => (string) Str::uuid(),
                'product_id'   => $productIds[$i % count($productIds)],
                'type'         => $type,
                'title'        => $title,
                'organisation' => $org,
                'venue'        => $venue,
                'country'      => $country,
                'city'         => $city,
                'started_on'   => sprintf('%d-05-01', $year),
                'ended_on'     => sprintf('%d-05-12', $year),
                'evidence_count' => 0,
                'is_verified'  => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        DB::table('provenance_events')->insert($rows);

        $this->created['provenance_events'] = count($rows);
        $this->created['  of type exhibition'] = count(array_filter($rows, fn ($r) => $r['type'] === 'exhibition'));
        $this->created['  distinct countries'] = count(array_unique(array_column($rows, 'country')));
    }

    /**
     * 128 published reviews in exactly the histogram the artwork draws.
     *
     * Every reviewer is a fresh account with `email = NULL` and a random
     * unusable password — the SIARC rule, for the same reason: an account nobody
     * can sign into and nobody can email cannot become a channel to a real
     * person by accident.
     */
    private function makeReviews(Business $business, array $images): void
    {
        $named = User::create([
            'name'     => 'Marie T. Ekassi',
            'email'    => null,
            'phone'    => null,
            'password' => Hash::make(Str::random(48)),
            'avatar'   => $images['reviewer-avatar'],
            'status'   => 'active',
            'account_type' => 'buyer',
            'language_preference' => 'en',
            'is_email_verified'   => 0,
            'is_phone_verified'   => 0,
        ]);

        // The one review the artwork writes out, with its date and its badge.
        $reviews = [[
            'reviewer_id' => $named->id,
            'business_id' => $business->id,
            'rating'      => 5,
            'title'       => null,
            'body'        => 'Exceptional craftsmanship! The mask is even more beautiful in person. '
                           . 'Authentic, well-packaged, and delivered on time.',
            'is_verified_contact' => 1,
            'status'      => 'published',
            'published_at' => '2026-07-25 12:00:00',
            'created_at'  => '2026-07-25 12:00:00',
            'updated_at'  => '2026-07-25 12:00:00',
        ]];

        // The histogram, less the one review already written above.
        $remaining = self::RATING_DISTRIBUTION;
        $remaining[5]--;

        $users = [];
        $n = 0;
        foreach ($remaining as $rating => $count) {
            for ($i = 0; $i < $count; $i++, $n++) {
                $name = self::FIRST_NAMES[$n % count(self::FIRST_NAMES)] . ' '
                      . self::SURNAMES[intdiv($n, count(self::FIRST_NAMES)) * 7 % count(self::SURNAMES)];

                $id = (string) Str::uuid();
                $users[] = [
                    'id'       => $id,
                    'name'     => $name,
                    'email'    => null,
                    'phone'    => null,
                    'password' => Hash::make(Str::random(48)),
                    'status'   => 'active',
                    'account_type' => 'buyer',
                    'language_preference' => 'fr',
                    'is_email_verified' => 0,
                    'is_phone_verified' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Spread the dates back from the artwork's newest review.
                $at = Carbon::create(2026, 7, 25)->subDays(2 + $n * 3);

                $reviews[] = [
                    'reviewer_id' => $id,
                    'business_id' => $business->id,
                    'rating'      => $rating,
                    'title'       => null,
                    'body'        => null,
                    'is_verified_contact' => 0,
                    'status'      => 'published',
                    'published_at' => $at,
                    'created_at'  => $at,
                    'updated_at'  => $at,
                ];
            }
        }

        foreach (array_chunk($users, 50) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        foreach (array_chunk($reviews, 50) as $chunk) {
            DB::table('business_reviews')->insert($chunk);
        }

        $this->created['users'] = ($this->created['users'] ?? 0) + 1 + count($users);
        $this->created['business_reviews'] = count($reviews);
    }

    /* ─────────────────────────────── Purge ─────────────────────────────── */

    /**
     * Removes the demo artisan and everything created with it.
     *
     * Deliberately not `Business::forceDelete()` with cascades trusted: the
     * point of this method is that a reviewer can read it and see every table
     * that was written to. It works off the demo flag and the slug, never off a
     * name, so it cannot reach a claimed profile that happens to share a name.
     *
     * Reviewer accounts are removed only when nothing else references them —
     * a user who has since reviewed another shop is left alone.
     */
    private function purge(): array
    {
        $removed = [];

        $business = Business::withTrashed()
            ->where(fn ($q) => $q->where('is_demo', 1)->orWhere('slug', self::SLUG))
            ->first();

        if (! $business) {
            $this->removeImages($removed);

            return $removed;
        }

        $productIds = DB::table('products')->where('business_id', $business->id)->pluck('id')->all();

        if ($productIds !== []) {
            $removed['provenance_events'] = DB::table('provenance_events')->whereIn('product_id', $productIds)->delete();
            $removed['product_images']    = DB::table('product_images')->whereIn('product_id', $productIds)->delete();
            $removed['products']          = DB::table('products')->whereIn('id', $productIds)->delete();
        }

        $reviewerIds = DB::table('business_reviews')->where('business_id', $business->id)
            ->pluck('reviewer_id')->unique()->all();

        $removed['business_reviews'] = DB::table('business_reviews')->where('business_id', $business->id)->delete();

        if ($reviewerIds !== []) {
            // Only accounts that exist for this record and nothing else.
            $stillUsed = DB::table('business_reviews')->whereIn('reviewer_id', $reviewerIds)
                ->pluck('reviewer_id')->unique()->all();
            $owners = DB::table('businesses')->whereIn('user_id', $reviewerIds)->pluck('user_id')->all();

            $deletable = array_diff($reviewerIds, $stillUsed, $owners);
            if ($deletable !== []) {
                DB::table('model_has_roles')->whereIn('model_id', $deletable)->delete();
                $removed['users'] = DB::table('users')->whereIn('id', $deletable)->delete();
            }
        }

        $removed['business_awards']       = DB::table('business_awards')->where('business_id', $business->id)->delete();
        $removed['business_tags']         = DB::table('business_tags')->where('business_id', $business->id)->delete();
        $removed['business_social_links'] = DB::table('business_social_links')->where('business_id', $business->id)->delete();
        $removed['business_gallery']      = DB::table('business_gallery')->where('business_id', $business->id)->delete();

        $workshopIds = DB::table('workshops')->where('business_id', $business->id)->pluck('id')->all();
        if ($workshopIds !== []) {
            DB::table('workshop_certificates')->whereIn('workshop_id', $workshopIds)->delete();
            $removed['workshops'] = DB::table('workshops')->whereIn('id', $workshopIds)->delete();
        }

        $ownerId = $business->user_id;
        $removed['businesses'] = DB::table('businesses')->where('id', $business->id)->delete();

        if ($ownerId && ! DB::table('businesses')->where('user_id', $ownerId)->exists()) {
            DB::table('model_has_roles')->where('model_id', $ownerId)->delete();
            $removed['users'] = ($removed['users'] ?? 0) + DB::table('users')->where('id', $ownerId)->delete();
        }

        // Taxonomy rows created for this record, dropped only if unreferenced.
        foreach (['wood-sculpture', 'heritage-collection'] as $slug) {
            $id = DB::table('product_categories')->where('slug', $slug)->value('id');
            if ($id && ! DB::table('products')->where('category_id', $id)->exists()) {
                $removed['product_categories'] = ($removed['product_categories'] ?? 0)
                    + DB::table('product_categories')->where('id', $id)->delete();
            }
        }

        $odza = DB::table('cities')->where('slug', 'odza')->value('id');
        if ($odza
            && ! DB::table('businesses')->where('city_id', $odza)->exists()
            && ! DB::table('workshops')->where('city_id', $odza)->exists()) {
            $removed['cities'] = DB::table('cities')->where('id', $odza)->delete();
        }

        $this->removeImages($removed);

        return array_filter($removed);
    }

    private function removeImages(array &$removed): void
    {
        $count = 0;

        foreach ([public_path(self::IMAGE_DIR), storage_path('app/public/demo')] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            foreach (self::CROPS as [, , , , , $file]) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_file($path) && unlink($path)) {
                    $count++;
                }
            }

            if (count((array) scandir($dir)) === 2) {
                rmdir($dir);
            }
        }

        if ($count) {
            $removed['image files'] = $count;
        }
    }

    /* ─────────────────────────────── Output ────────────────────────────── */

    private function reportCounts(string $verb, array $counts): void
    {
        if ($counts === []) {
            $this->info('Nothing to do — no demo artisan is present.');

            return;
        }

        $this->newLine();
        $this->info($verb . ':');
        foreach ($counts as $table => $n) {
            $this->line(sprintf('  %-24s %d', $table, $n));
        }
    }
}
