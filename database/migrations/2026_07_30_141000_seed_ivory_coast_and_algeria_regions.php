<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The administrative regions an Ivorian or Algerian artisan picks at signup.
 *
 * Côte d'Ivoire: the 14 districts (12 ordinary plus the two autonomous ones,
 * Abidjan and Yamoussoukro). The country also has 31 régions one level down,
 * but districts are the level a person names when asked where they are, and it
 * matches how Cameroon's ten regions are used here.
 *
 * Algeria: the 58 wilayas. That is the country's real first-level division and
 * the only one Algerians use; there is no coarser level to fall back on. The
 * ten wilayas from 49 to 58 were promoted from délégations in 2019.
 *
 * `code` follows what each country actually uses: CI districts have no official
 * numeric code so an ISO-style two-letter one is used, while Algerian wilayas
 * are universally known by their number — it is on every licence plate — so the
 * zero-padded number is the honest code.
 *
 * Written as a migration rather than a seeder because production is updated by
 * running migrations; a seeder would never run on the live site.
 */
return new class extends Migration
{
    /** Côte d'Ivoire — 14 districts. */
    private const IVORY_COAST = [
        ['AB', 'Abidjan', 'Abidjan'],
        ['BS', 'Bas-Sassandra', 'Bas-Sassandra'],
        ['CO', 'Comoé', 'Comoé'],
        ['DE', 'Denguélé', 'Denguélé'],
        ['GD', 'Gôh-Djiboua', 'Gôh-Djiboua'],
        ['LC', 'Lacs', 'Lakes'],
        ['LG', 'Lagunes', 'Lagunes'],
        ['MO', 'Montagnes', 'Mountains'],
        ['SB', 'Sassandra-Marahoué', 'Sassandra-Marahoué'],
        ['SM', 'Savanes', 'Savanes'],
        ['VB', 'Vallée du Bandama', 'Bandama Valley'],
        ['WO', 'Woroba', 'Woroba'],
        ['YA', 'Yamoussoukro', 'Yamoussoukro'],
        ['ZA', 'Zanzan', 'Zanzan'],
    ];

    /** Algeria — the 58 wilayas, in official numeric order. */
    private const ALGERIA = [
        ['01', 'Adrar'], ['02', 'Chlef'], ['03', 'Laghouat'], ['04', 'Oum El Bouaghi'],
        ['05', 'Batna'], ['06', 'Béjaïa'], ['07', 'Biskra'], ['08', 'Béchar'],
        ['09', 'Blida'], ['10', 'Bouira'], ['11', 'Tamanrasset'], ['12', 'Tébessa'],
        ['13', 'Tlemcen'], ['14', 'Tiaret'], ['15', 'Tizi Ouzou'], ['16', 'Alger'],
        ['17', 'Djelfa'], ['18', 'Jijel'], ['19', 'Sétif'], ['20', 'Saïda'],
        ['21', 'Skikda'], ['22', 'Sidi Bel Abbès'], ['23', 'Annaba'], ['24', 'Guelma'],
        ['25', 'Constantine'], ['26', 'Médéa'], ['27', 'Mostaganem'], ['28', "M'Sila"],
        ['29', 'Mascara'], ['30', 'Ouargla'], ['31', 'Oran'], ['32', 'El Bayadh'],
        ['33', 'Illizi'], ['34', 'Bordj Bou Arréridj'], ['35', 'Boumerdès'], ['36', 'El Tarf'],
        ['37', 'Tindouf'], ['38', 'Tissemsilt'], ['39', 'El Oued'], ['40', 'Khenchela'],
        ['41', 'Souk Ahras'], ['42', 'Tipaza'], ['43', 'Mila'], ['44', 'Aïn Defla'],
        ['45', 'Naâma'], ['46', 'Aïn Témouchent'], ['47', 'Ghardaïa'], ['48', 'Relizane'],
        ['49', 'Timimoun'], ['50', 'Bordj Badji Mokhtar'], ['51', 'Ouled Djellal'],
        ['52', 'Béni Abbès'], ['53', 'In Salah'], ['54', 'In Guezzam'], ['55', 'Touggourt'],
        ['56', 'Djanet'], ['57', "El M'Ghair"], ['58', 'El Meniaa'],
    ];

    public function up(): void
    {
        $now = now();

        $ci = (int) DB::table('countries')->where('code', 'CI')->value('id');
        $dz = (int) DB::table('countries')->where('code', 'DZ')->value('id');

        if (! $ci || ! $dz) {
            // The country migration must have run first. Fail loudly rather
            // than silently attaching regions to country_id 0.
            throw new RuntimeException('Countries CI/DZ are missing — run the countries migration first.');
        }

        $rows = [];
        $order = 0;

        foreach (self::IVORY_COAST as [$code, $fr, $en]) {
            $rows[] = [
                'country_id' => $ci, 'code' => $code,
                'name_fr' => $fr, 'name_en' => $en,
                'is_active' => true, 'sort_order' => ++$order,
                'created_at' => $now, 'updated_at' => $now,
            ];
        }

        $order = 0;
        foreach (self::ALGERIA as [$code, $name]) {
            $rows[] = [
                'country_id' => $dz, 'code' => $code,
                // Wilaya names are proper nouns and are written the same way in
                // both languages; there is no English form to invent.
                'name_fr' => $name, 'name_en' => $name,
                'is_active' => true, 'sort_order' => ++$order,
                'created_at' => $now, 'updated_at' => $now,
            ];
        }

        // Idempotent: skip anything already present, so re-running cannot
        // duplicate a region if this migration is ever replayed.
        foreach (array_chunk($rows, 50) as $chunk) {
            foreach ($chunk as $row) {
                $exists = DB::table('regions')
                    ->where('country_id', $row['country_id'])
                    ->where('code', $row['code'])
                    ->exists();
                if (! $exists) {
                    DB::table('regions')->insert($row);
                }
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('countries')->whereIn('code', ['CI', 'DZ'])->pluck('id');
        // Only regions with no businesses attached, so a rollback can never
        // orphan a real artisan's profile.
        DB::table('regions')
            ->whereIn('country_id', $ids)
            ->whereNotIn('id', function ($q) {
                $q->select('region_id')->from('businesses')->whereNotNull('region_id');
            })
            ->delete();
    }
};
