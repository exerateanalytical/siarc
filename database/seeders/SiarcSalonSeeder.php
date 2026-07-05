<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Demo data for the SIARC salon module so every wired page renders with real
 * content while the final designs are produced. Idempotent: re-running is a no-op
 * once pavilions exist for the SIARC event.
 */
class SiarcSalonSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1) The SIARC event (reuse if present — admin-siarc looks up slug LIKE 'siarc%').
        $event = DB::table('events')->where('slug', 'like', 'siarc%')->first();
        if (! $event) {
            $id = DB::table('events')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'slug' => 'siarc-2026',
                'name_fr' => 'SIARC 2026 — Salon International de l\'Artisanat du Cameroun',
                'name_en' => 'SIARC 2026 — International Craft Fair of Cameroon',
                'description_fr' => 'La grande vitrine nationale de l\'artisanat camerounais.',
                'description_en' => 'The great national showcase of Cameroonian craftsmanship.',
                'location_fr' => 'Palais des Congrès, Yaoundé',
                'location_en' => 'Palais des Congrès, Yaoundé',
                'starts_at' => '2026-11-15 09:00:00',
                'ends_at' => '2026-11-22 18:00:00',
                'is_published' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $event = DB::table('events')->where('id', $id)->first();
        }
        $eid = $event->id;
        $start = Carbon::parse($event->starts_at);

        // Idempotent: wipe the salon-owned tables for this event and reseed. We do
        // NOT delete event_exhibitors (they may be real) — those are firstOrCreate'd.
        DB::table('b2b_meetings')->where('event_id', $eid)->delete();
        DB::table('programme_sessions')->where('event_id', $eid)->pluck('id')->each(function ($sid) {
            DB::table('session_speaker')->where('session_id', $sid)->delete();
            DB::table('session_registrations')->where('session_id', $sid)->delete();
        });
        DB::table('programme_sessions')->where('event_id', $eid)->delete();
        DB::table('speakers')->where('event_id', $eid)->delete();
        DB::table('check_ins')->where('event_id', $eid)->delete();
        DB::table('visitors')->where('event_id', $eid)->delete();
        DB::table('stands')->where('event_id', $eid)->delete();
        DB::table('pavilions')->where('event_id', $eid)->delete();

        // 2) Pavilions
        $pavDefs = [
            ['P1', 'Pavillon des Arts & Décoration', 'Arts & Decoration Pavilion', '#0F4824', 'palette'],
            ['P2', 'Pavillon Mode, Textile & Cuir', 'Fashion, Textile & Leather', '#8A6D1F', 'shirt'],
            ['P3', 'Pavillon Agroalimentaire', 'Agri-food Pavilion', '#C97A16', 'wheat'],
            ['P4', 'Pavillon Bois & Mobilier', 'Wood & Furniture Pavilion', '#157A43', 'armchair'],
            ['P5', 'Pavillon Innovation & Services', 'Innovation & Services', '#3565DE', 'lightbulb'],
        ];
        $pavIds = [];
        foreach ($pavDefs as $i => [$code, $fr, $en, $color, $icon]) {
            $pavIds[] = DB::table('pavilions')->insertGetId([
                'event_id' => $eid, 'code' => $code, 'slug' => Str::slug($fr),
                'name_fr' => $fr, 'name_en' => $en,
                'description_fr' => 'Espace dédié aux exposants de cette filière.',
                'color' => $color, 'icon' => $icon, 'capacity' => 24,
                'sort_order' => $i + 1, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // 3) Stands (a grid per pavilion) — some will be allocated below.
        $standIds = [];
        foreach ($pavIds as $p => $pid) {
            for ($n = 1; $n <= 8; $n++) {
                $col = ($n - 1) % 4;
                $row = intdiv($n - 1, 4);
                $standIds[] = DB::table('stands')->insertGetId([
                    'event_id' => $eid, 'pavilion_id' => $pid,
                    'code' => chr(65 + $p) . '-' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                    'size_sqm' => 9.00, 'price' => 150000,
                    'status' => 'available',
                    'pos_x' => 40 + $col * 90, 'pos_y' => 40 + $row * 70 + $p * 170,
                    'pos_w' => 70, 'pos_h' => 50,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // 4) Exhibitors — reuse existing businesses, allocate to a stand + pavilion.
        $businesses = DB::table('businesses')->whereNull('deleted_at')->orderBy('id')->limit(14)->get(['id']);
        $exhIds = [];
        foreach ($businesses as $i => $b) {
            $pid = $pavIds[$i % count($pavIds)];
            $fields = [
                'pavilion_id' => $pid,
                'booth_number' => 'B' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'badge_code' => 'SIARC-EXH-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'qr_token' => Str::random(40),
                'status' => $i % 5 === 0 ? 'pending' : 'confirmed',
                'registered_at' => $now, 'updated_at' => $now,
                'checked_in_at' => $i % 3 === 0 ? $now : null,
            ];
            $existing = DB::table('event_exhibitors')->where('event_id', $eid)->where('business_id', $b->id)->first();
            if ($existing) {
                DB::table('event_exhibitors')->where('id', $existing->id)->update($fields);
                $exhId = $existing->id;
            } else {
                $exhId = DB::table('event_exhibitors')->insertGetId($fields + ['event_id' => $eid, 'business_id' => $b->id, 'created_at' => $now]);
            }
            $exhIds[] = $exhId;
            if (isset($standIds[$i])) {
                DB::table('stands')->where('id', $standIds[$i])->update(['exhibitor_id' => $exhId, 'status' => 'allocated']);
            }
        }

        // 5) Speakers
        $spkDefs = [
            ['Dr. Aïssatou Bello', 'Directrice, Artisanat & Patrimoine', 'MINPMEESA'],
            ['Jean-Marc Etoa', 'Fondateur, Coopérative Bamiléké', 'CoopArt'],
            ['Fatimatou Njoya', 'Créatrice de mode', 'Njoya Couture'],
            ['Serge Kamdem', 'Expert e-commerce artisanal', 'DigitalCraft'],
            ['Marie Ngo Bell', 'Maître céramiste', 'Terre de Sawa'],
            ['Ibrahim Moussa', 'Sculpteur sur bois', 'Atelier Nord'],
        ];
        $spkIds = [];
        foreach ($spkDefs as $i => [$name, $role, $org]) {
            $spkIds[] = DB::table('speakers')->insertGetId([
                'event_id' => $eid, 'name' => $name, 'role_fr' => $role, 'organization' => $org,
                'bio_fr' => 'Intervenant·e reconnu·e du secteur artisanal camerounais.',
                'is_featured' => $i < 3, 'sort_order' => $i + 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // 6) Programme sessions
        $sesDefs = [
            ['keynote', 'Cérémonie d\'ouverture officielle', 0, 9],
            ['keynote', 'L\'artisanat, moteur de l\'économie nationale', 0, 11],
            ['workshop', 'Atelier : Techniques de poterie traditionnelle', 1, 10],
            ['workshop', 'Atelier : Vendre son artisanat en ligne', 1, 14],
            ['panel', 'Table ronde : Financer sa coopérative', 2, 10],
            ['session', 'Le label « Artisanat Authentique »', 2, 15],
            ['workshop', 'Atelier : Design textile & motifs Ndop', 3, 11],
            ['ceremony', 'Nuit des Trophées de l\'Artisanat', 6, 19],
        ];
        $sesIds = [];
        foreach ($sesDefs as $i => [$type, $title, $dayOffset, $hour]) {
            $starts = $start->copy()->addDays($dayOffset)->setTime($hour, 0);
            $sid = DB::table('programme_sessions')->insertGetId([
                'event_id' => $eid, 'pavilion_id' => $pavIds[$i % count($pavIds)],
                'speaker_id' => $spkIds[$i % count($spkIds)],
                'type' => $type, 'title_fr' => $title,
                'description_fr' => 'Session inscrite au programme officiel du SIARC 2026.',
                'starts_at' => $starts, 'ends_at' => $starts->copy()->addMinutes(90),
                'room' => 'Salle ' . (($i % 3) + 1),
                'capacity' => $type === 'workshop' ? 30 : 200,
                'registration_required' => $type === 'workshop',
                'sort_order' => $i + 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $sesIds[] = $sid;
            DB::table('session_speaker')->insert([
                'session_id' => $sid, 'speaker_id' => $spkIds[$i % count($spkIds)],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // 7) Visitors
        $types = ['visitor', 'visitor', 'buyer', 'visitor', 'vip', 'press', 'buyer', 'visitor'];
        for ($i = 1; $i <= 48; $i++) {
            $type = $types[$i % count($types)];
            $checked = $i % 4 === 0;
            $vid = DB::table('visitors')->insertGetId([
                'event_id' => $eid,
                'first_name' => 'Visiteur', 'last_name' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'email' => 'visiteur' . $i . '@example.cm', 'organization' => $type === 'buyer' ? 'Acheteur Intl.' : null,
                'country' => $i % 6 === 0 ? 'France' : 'Cameroun',
                'type' => $type, 'badge_code' => 'SIARC-VIS-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'qr_token' => Str::random(40), 'status' => $checked ? 'checked_in' : 'registered',
                'registered_at' => $now, 'checked_in_at' => $checked ? $now : null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
            if ($checked) {
                DB::table('check_ins')->insert([
                    'event_id' => $eid, 'subject_type' => 'visitor', 'subject_id' => $vid,
                    'gate' => 'Entrée ' . (($i % 3) + 1), 'scanned_at' => $now,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // 8) B2B meetings
        $visitorRows = empty($exhIds) ? [] : DB::table('visitors')->where('event_id', $eid)->whereIn('type', ['buyer', 'vip'])->limit(12)->pluck('id')->all();
        $statuses = ['requested', 'confirmed', 'confirmed', 'completed', 'declined'];
        foreach ($visitorRows as $i => $visId) {
            if (! isset($exhIds[$i % count($exhIds)])) continue;
            DB::table('b2b_meetings')->insert([
                'event_id' => $eid, 'requester_visitor_id' => $visId,
                'host_exhibitor_id' => $exhIds[$i % count($exhIds)],
                'stand_id' => $standIds[$i % count($standIds)] ?? null,
                'scheduled_at' => $start->copy()->addDays(1 + $i % 5)->setTime(10 + ($i % 6), 0),
                'duration_min' => 30, 'location' => 'Espace B2B',
                'status' => $statuses[$i % count($statuses)],
                'message' => 'Demande de rendez-vous d\'affaires au SIARC 2026.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $this->command?->info('SIARC salon demo data seeded.');
    }
}
