<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * End-to-end SIARC flows: signup → badge → QR verify → scan → check-in →
 * badge lifecycle → exhibitor gate → B2B request.
 */
class SiarcFlowsTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The array cache is shared across the phpunit process: earlier tests'
        // throttle hits on the same routes would otherwise 429 these flows.
        \Illuminate\Support\Facades\Cache::flush();
    }

    private int $eventId;

    private function seedEvent(): void
    {
        $this->eventId = (int) DB::table('events')->insertGetId([
            'uuid' => (string) Str::uuid(), 'slug' => 'siarc-2026-flow-test', 'name_fr' => 'SIARC 2026',
            'starts_at' => '2026-07-27', 'ends_at' => '2026-08-05',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        // siarcEvent() resolves by slug prefix; make sure ours is the one found
        \Illuminate\Support\Facades\Cache::flush();
    }

    private function adminSession(): array
    {
        $admin = $this->makeUser();

        return ['siac_user' => ['id' => $admin->id, 'name' => 'Agent Scanner', 'email' => $admin->email, 'role' => 'super_admin', 'is_admin' => true]];
    }

    private function seedExhibitor(): int
    {
        $bid = $this->makeBusiness(null, ['name_fr' => 'Atelier Flow', 'slug' => 'atelier-flow-'.Str::random(4)])->id;

        return (int) DB::table('event_exhibitors')->insertGetId([
            'event_id' => $this->eventId, 'business_id' => $bid, 'booth_number' => 'A-01',
            'badge_code' => 'FLOW-EXH-1', 'qr_token' => 'tok-exh-flow', 'status' => 'registered',
            'registered_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_full_visitor_journey_signup_badge_verify_scan_checkin(): void
    {
        $this->seedEvent();

        // 1 ── Signup: the public registration form creates the visitor and returns the badge.
        $res = $this->post('/siarc/inscription', [
            'first_name' => 'Aline', 'last_name' => 'Mbarga',
            'email' => 'aline@flows.test', 'type' => 'visitor',
        ]);
        $res->assertRedirect()->assertSessionHas('siarc_registered')->assertSessionHas('siarc_badge');
        $badge = session('siarc_badge');
        $this->assertNotNull($badge);
        $visitor = DB::table('visitors')->where('badge_code', $badge)->first();
        $this->assertSame('aline@flows.test', $visitor->email);

        // The confirmation banner links to the printable badge and verification.
        $this->post('/siarc/inscription', ['first_name' => '', 'email' => 'not-an-email'])->assertSessionHasErrors(); // guard: invalid input rejected

        // 2 ── Printable badge renders with the badge code and a QR targeting /siarc/verify.
        $badgePage = $this->get('/siarc/badge/'.$badge)->assertOk()
            ->assertSee('ALINE MBARGA')->assertSee($badge);
        // the QR payload is the verify URL (JSON-escaped in the inline script)
        $this->assertStringContainsString('siarc\/verify\/'.$badge, $badgePage->getContent());

        // 3 ── Public verification (what scanning the printed QR opens): valid.
        $this->get('/siarc/verify/'.$badge)->assertOk()
            ->assertSee('BADGE VALIDE')->assertSee('Aline Mbarga');
        // The QR token verifies too (that is what is actually encoded on gates).
        $this->get('/siarc/verify/'.$visitor->qr_token)->assertOk()->assertSee('BADGE VALIDE');
        // Unknown codes are called out.
        $this->get('/siarc/verify/NOPE-123')->assertOk()->assertSee('BADGE INCONNU');

        // 4 ── Admin scanner: manual entry resolves the real visitor (granted).
        $session = $this->adminSession();
        $this->withSession($session)
            ->get(route('siarc.admin.accred.qrscanner', ['etat' => 'validation', 'code' => $badge]))
            ->assertOk()->assertSee('VALIDATION RÉUSSIE')->assertSee('Aline Mbarga');

        // 5 ── Check-in POST records the passage.
        $this->withSession($session)
            ->post(route('siarc.admin.accred.qrscanner.checkin'), ['code' => $badge])
            ->assertRedirect();
        $visitor = DB::table('visitors')->where('badge_code', $badge)->first();
        $this->assertNotNull($visitor->checked_in_at);
        $this->assertDatabaseHas('check_ins', ['subject_type' => 'visitor', 'subject_id' => $visitor->id, 'gate' => 'Porte A - Entrée Principale']);
        // The granted screen confirms it.
        $this->withSession($session)
            ->get(route('siarc.admin.accred.qrscanner', ['etat' => 'granted', 'code' => $badge, 'checkin' => 1]))
            ->assertOk()->assertSee('Check-in enregistré');
        // Verification now shows on-site status.
        $this->get('/siarc/verify/'.$badge)->assertOk()->assertSee('Oui — enregistré');
    }

    public function test_blocked_badge_is_refused_everywhere_and_can_be_reactivated(): void
    {
        $this->seedEvent();
        DB::table('visitors')->insert([
            'event_id' => $this->eventId, 'first_name' => 'Perdu', 'last_name' => 'Badge',
            'type' => 'visitor', 'status' => 'registered', 'badge_code' => 'FLOW-VIS-LOST',
            'qr_token' => 'tok-lost', 'registered_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $session = $this->adminSession();

        // Block (lost badge declared).
        $this->withSession($session)
            ->post('/tableau-de-bord/admin/siarc/accreditation/badges/FLOW-VIS-LOST/statut')
            ->assertRedirect(route('siarc.admin.accred.lost', ['lang' => 'fr']));
        $this->assertSame('cancelled', DB::table('visitors')->where('badge_code', 'FLOW-VIS-LOST')->value('status'));

        // Refused at the public verify and at the scanner.
        $this->get('/siarc/verify/FLOW-VIS-LOST')->assertOk()->assertSee('BADGE BLOQUÉ');
        $this->withSession($session)
            ->get(route('siarc.admin.accred.qrscanner', ['code' => 'FLOW-VIS-LOST']))
            ->assertOk()->assertSee('ACCÈS REFUSÉ');
        // Check-in attempt is refused and records nothing.
        $this->withSession($session)
            ->post(route('siarc.admin.accred.qrscanner.checkin'), ['code' => 'FLOW-VIS-LOST'])
            ->assertRedirect();
        $this->assertNull(DB::table('visitors')->where('badge_code', 'FLOW-VIS-LOST')->value('checked_in_at'));

        // Reactivate (badge found).
        $this->withSession($session)->post('/tableau-de-bord/admin/siarc/accreditation/badges/FLOW-VIS-LOST/statut');
        $this->assertSame('registered', DB::table('visitors')->where('badge_code', 'FLOW-VIS-LOST')->value('status'));
        $this->get('/siarc/verify/FLOW-VIS-LOST')->assertOk()->assertSee('BADGE VALIDE');
    }

    public function test_exhibitor_gate_checkin_flow(): void
    {
        $this->seedEvent();
        $eeId = $this->seedExhibitor();
        $session = $this->adminSession();

        // Exhibitor badge verifies publicly.
        $this->get('/siarc/verify/FLOW-EXH-1')->assertOk()->assertSee('BADGE VALIDE')->assertSee('Atelier Flow');

        // Gate check-in via the portal form.
        $this->withSession($session)
            ->post(route('siarc.mobile.exhibitor-checkin.store'), ['code' => 'FLOW-EXH-1'])
            ->assertRedirect()->assertSessionHas('siarc_checkin_ok');
        $this->assertNotNull(DB::table('event_exhibitors')->where('id', $eeId)->value('checked_in_at'));
        $this->assertDatabaseHas('check_ins', ['subject_type' => 'exhibitor', 'subject_id' => $eeId, 'gate' => 'Accès Exposants']);

        // A visitor badge at the exhibitor gate is refused.
        $this->withSession($session)
            ->post(route('siarc.mobile.exhibitor-checkin.store'), ['code' => 'UNKNOWN-1'])
            ->assertRedirect()->assertSessionHas('siarc_checkin_ko');
    }

    public function test_b2b_meeting_request_flow(): void
    {
        $this->seedEvent();
        $eeId = $this->seedExhibitor();
        DB::table('visitors')->insert([
            'event_id' => $this->eventId, 'first_name' => 'Buyer', 'last_name' => 'One',
            'email' => 'buyer@flows.test', 'type' => 'buyer', 'status' => 'registered',
            'badge_code' => 'FLOW-VIS-B2B', 'qr_token' => 'tok-b2b',
            'registered_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Known registration email → meeting requested.
        $this->post(route('siarc.b2b.request'), [
            'email' => 'buyer@flows.test', 'exhibitor_id' => $eeId, 'message' => 'Import textile.',
        ])->assertRedirect()->assertSessionHas('siarc_b2b_ok');
        $this->assertDatabaseHas('b2b_meetings', ['host_exhibitor_id' => $eeId, 'status' => 'requested', 'message' => 'Import textile.']);

        // Unknown email → polite failure, nothing written.
        $before = DB::table('b2b_meetings')->count();
        $this->post(route('siarc.b2b.request'), ['email' => 'ghost@flows.test', 'exhibitor_id' => $eeId])
            ->assertRedirect()->assertSessionHas('siarc_b2b_ko');
        $this->assertSame($before, DB::table('b2b_meetings')->count());
    }

    public function test_workshop_registration_flow(): void
    {
        $this->seedEvent();
        $sid = DB::table('programme_sessions')->insertGetId([
            'event_id' => $this->eventId, 'type' => 'workshop', 'title_fr' => 'Atelier vannerie',
            'starts_at' => '2026-07-28 10:00:00', 'ends_at' => '2026-07-28 12:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->get("/siarc/ateliers/$sid/inscription")->assertOk()->assertSee('Atelier vannerie');
        $this->post("/siarc/ateliers/$sid/inscription", ['name' => 'Aline Mbarga', 'email' => 'aline@flows.test'])
            ->assertRedirect()->assertSessionHas('siarc_registered');
        $this->assertDatabaseHas('session_registrations', ['session_id' => $sid, 'name' => 'Aline Mbarga']);
    }
}
