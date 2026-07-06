<?php

/**
 * SIARC salon / exhibition-operations routes. Loaded (with the "web" group) from
 * bootstrap/app.php. Every page is wired to the real salon data model and renders
 * through the shared pages.siarc.admin / pages.siarc.public scaffold until the
 * final design mockups are applied. Helper functions (requireAdmin, webLang,
 * siarcEvent, requireAuth, webUser) live in app/Support/route_helpers.php.
 */

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Status → badge tone helper (kept as a closure passed via `use`, not a global fn,
// so route:cache stays safe).
$tone = function (string $status): array {
    $map = [
        'pending' => 'gold', 'confirmed' => 'green', 'cancelled' => 'red', 'declined' => 'red',
        'allocated' => 'green', 'available' => 'grey', 'reserved' => 'blue',
        'checked_in' => 'green', 'registered' => 'blue', 'completed' => 'green', 'requested' => 'gold',
        'vip' => 'purple', 'buyer' => 'blue', 'press' => 'gold', 'staff' => 'grey', 'visitor' => 'grey',
    ];
    return ['badge' => ucfirst($status), 'tone' => $map[$status] ?? 'grey'];
};

// ───────────────────────────── ADMIN — SIARC Core ─────────────────────────────

Route::get('/tableau-de-bord/admin/siarc/apercu', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $e = siarcEvent(); $eid = $e?->id ?? 0;
    $exh = DB::table('event_exhibitors')->where('event_id', $eid);
    $vis = DB::table('visitors')->where('event_id', $eid);
    $stands = DB::table('stands')->where('event_id', $eid);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => $fr ? 'Tableau de bord SIARC' : 'SIARC Dashboard',
        'sIntro' => ($e?->name_fr ?? 'SIARC 2026') . ' · ' . ($e?->location_fr ?? '') ,
        'sStats' => [
            ['store', '#157A43', '#E2F3E8', $exh->count(), $fr ? 'Exposants' : 'Exhibitors', $exh->clone()->where('status', 'confirmed')->count() . ($fr ? ' confirmés' : ' confirmed')],
            ['layout-grid', '#3565DE', '#E8EFFB', DB::table('pavilions')->where('event_id', $eid)->count(), $fr ? 'Pavillons' : 'Pavilions', null],
            ['grid-3x3', '#7C4FE0', '#F0EAFB', $stands->clone()->where('status', 'allocated')->count() . '/' . $stands->count(), $fr ? 'Stands alloués' : 'Stands allocated', null],
            ['users-round', '#C97A16', '#FDF3E0', $vis->count(), $fr ? 'Visiteurs' : 'Visitors', $vis->clone()->where('status', 'checked_in')->count() . ($fr ? ' présents' : ' checked-in')],
            ['handshake', '#0D9488', '#E1F4F1', DB::table('b2b_meetings')->where('event_id', $eid)->count(), $fr ? 'Rendez-vous B2B' : 'B2B meetings', null],
        ],
        'sCards' => [
            ['title' => $fr ? 'Exposants' : 'Exhibitors', 'sub' => $fr ? 'Gérer les exposants' : 'Manage exhibitors', 'icon' => 'store', 'tone' => 'green', 'href' => route('siarc.admin.exhibitors')],
            ['title' => $fr ? 'Pavillons' : 'Pavilions', 'sub' => $fr ? 'Zones du salon' : 'Salon zones', 'icon' => 'layout-grid', 'tone' => 'blue', 'href' => route('siarc.admin.pavilions')],
            ['title' => $fr ? 'Plan interactif' : 'Floor plan', 'sub' => $fr ? 'Plan des stands' : 'Stand map', 'icon' => 'map', 'tone' => 'purple', 'href' => route('siarc.admin.floorplan')],
            ['title' => $fr ? 'Allocation des stands' : 'Stand allocation', 'sub' => $fr ? 'Attribuer les stands' : 'Assign stands', 'icon' => 'grid-3x3', 'tone' => 'gold', 'href' => route('siarc.admin.stands')],
            ['title' => $fr ? 'Visiteurs' : 'Visitors', 'sub' => $fr ? 'Inscriptions & badges' : 'Registration & badges', 'icon' => 'users-round', 'tone' => 'gold', 'href' => route('siarc.admin.visitors')],
            ['title' => $fr ? 'Contrôle d\'accès' : 'Entry control', 'sub' => 'Check-in', 'icon' => 'scan-line', 'tone' => 'green', 'href' => route('siarc.admin.entry')],
            ['title' => 'B2B', 'sub' => $fr ? 'Rendez-vous d\'affaires' : 'Business meetings', 'icon' => 'handshake', 'tone' => 'blue', 'href' => route('siarc.admin.b2b')],
            ['title' => $fr ? 'Programme' : 'Programme', 'sub' => $fr ? 'Sessions & ateliers' : 'Sessions & workshops', 'icon' => 'calendar-days', 'tone' => 'purple', 'href' => route('siarc.admin.programme')],
            ['title' => $fr ? 'Intervenants' : 'Speakers', 'sub' => $fr ? 'Gérer les intervenants' : 'Manage speakers', 'icon' => 'mic', 'tone' => 'green', 'href' => route('siarc.admin.speakers')],
            ['title' => $fr ? 'Analytique' : 'Analytics', 'sub' => $fr ? 'Statistiques du salon' : 'Salon statistics', 'icon' => 'bar-chart-3', 'tone' => 'blue', 'href' => route('siarc.admin.analytics')],
            ['title' => $fr ? 'Suivi en direct' : 'Live monitoring', 'sub' => $fr ? 'Affluence en temps réel' : 'Real-time attendance', 'icon' => 'activity', 'tone' => 'red', 'href' => route('siarc.admin.live')],
            ['title' => $fr ? 'Incidents' : 'Incidents', 'sub' => $fr ? 'Via les tickets support' : 'Via support tickets', 'icon' => 'triangle-alert', 'tone' => 'gold', 'href' => route('siarc.admin.incidents')],
        ],
    ]);
})->name('siarc.admin.dashboard');

Route::get('/tableau-de-bord/admin/siarc/exposants', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('event_exhibitors as ee')
        ->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->leftJoin('pavilions as p', 'p.id', '=', 'ee.pavilion_id')
        ->where('ee.event_id', $eid)->orderBy('ee.id')
        ->get(['ee.id', 'b.name_fr', 'p.name_fr as pavilion', 'ee.booth_number', 'ee.status', 'ee.checked_in_at']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-exh', 'sTitle' => $fr ? 'Gestion des Exposants' : 'Exhibitors Management',
        'sIntro' => $fr ? 'Chaque exposant réutilise sa fiche entreprise (vendeur) existante ; seules les données du salon sont ajoutées.' : 'Each exhibitor reuses its existing business (vendor) record; only salon data is added.',
        'sStats' => [
            ['store', '#157A43', '#E2F3E8', $rows->count(), $fr ? 'Exposants' : 'Exhibitors', null],
            ['check-circle-2', '#157A43', '#E2F3E8', $rows->where('status', 'confirmed')->count(), $fr ? 'Confirmés' : 'Confirmed', null],
            ['clock', '#C97A16', '#FDF3E0', $rows->where('status', 'pending')->count(), $fr ? 'En attente' : 'Pending', null],
            ['scan-line', '#3565DE', '#E8EFFB', $rows->whereNotNull('checked_in_at')->count(), $fr ? 'Enregistrés' : 'Checked-in', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Exposants du SIARC 2026' : 'SIARC 2026 exhibitors',
            'cols' => [$fr ? 'Exposant' : 'Exhibitor', $fr ? 'Pavillon' : 'Pavilion', $fr ? 'Stand' : 'Booth', $fr ? 'Statut' : 'Status', 'Check-in'],
            'rows' => $rows->map(fn ($x) => [
                'href' => route('siarc.admin.exhibitor', $x->id),
                'cells' => [$x->name_fr ?? '—', $x->pavilion ?? '—', $x->booth_number ?? '—', $tone($x->status), $x->checked_in_at ? '✓' : '—'],
            ])->all(),
        ]],
        'sLinks' => [
            ['label' => $fr ? 'Allocation des stands' : 'Stand allocation', 'href' => route('siarc.admin.stands'), 'icon' => 'grid-3x3'],
            ['label' => $fr ? 'Badges' : 'Badges', 'href' => route('siarc.admin.badges'), 'icon' => 'id-card'],
            ['label' => $fr ? 'Annuaire public' : 'Public directory', 'href' => route('siarc.exhibitors', ['lang' => $lang]), 'icon' => 'external-link'],
        ],
    ]);
})->name('siarc.admin.exhibitors');

Route::get('/tableau-de-bord/admin/siarc/exposants/{id}', function (Request $r, $id) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $x = DB::table('event_exhibitors as ee')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->leftJoin('pavilions as p', 'p.id', '=', 'ee.pavilion_id')->where('ee.id', $id)
        ->first(['ee.*', 'b.name_fr', 'b.slug as business_slug', 'p.name_fr as pavilion']);
    abort_if(! $x, 404);
    $stand = DB::table('stands')->where('exhibitor_id', $id)->first();
    $meetings = DB::table('b2b_meetings')->where('host_exhibitor_id', $id)->count();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-exh', 'sTitle' => $fr ? 'Détail de l\'exposant' : 'Exhibitor detail',
        'sIntro' => $fr ? 'Fiche exposant (données salon) reliée à la fiche entreprise/vendeur.' : 'Exhibitor record (salon data) linked to the business/vendor record.',
        'sStats' => [
            ['layout-grid', '#3565DE', '#E8EFFB', $x->pavilion ?? '—', $fr ? 'Pavillon' : 'Pavilion', null],
            ['grid-3x3', '#7C4FE0', '#F0EAFB', $stand->code ?? ($x->booth_number ?? '—'), 'Stand', null],
            ['id-card', '#C97A16', '#FDF3E0', $x->badge_code ?? '—', 'Badge', null],
            ['handshake', '#0D9488', '#E1F4F1', $meetings, 'B2B', null],
        ],
        'sLinks' => array_values(array_filter([
            $x->business_slug ? ['label' => $fr ? 'Fiche vendeur publique' : 'Public vendor page', 'href' => route('businesses.show', ['slug' => $x->business_slug, 'lang' => $lang]), 'icon' => 'external-link'] : null,
            ['label' => $fr ? 'Tous les exposants' : 'All exhibitors', 'href' => route('siarc.admin.exhibitors'), 'icon' => 'arrow-left'],
        ])),
    ]);
})->name('siarc.admin.exhibitor');

Route::get('/tableau-de-bord/admin/siarc/pavillons', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $pavs = DB::table('pavilions')->where('event_id', $eid)->orderBy('sort_order')->get();
    $rows = $pavs->map(function ($p) {
        $stands = DB::table('stands')->where('pavilion_id', $p->id);
        return ['href' => route('siarc.admin.pavilion', $p->id), 'cells' => [
            $p->name_fr, $p->code ?? '—', $stands->count(), $stands->clone()->where('status', 'allocated')->count(),
            DB::table('event_exhibitors')->where('pavilion_id', $p->id)->count(),
        ]];
    })->all();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-plan', 'sTitle' => $fr ? 'Gestion des Pavillons' : 'Pavilion Management',
        'sIntro' => $fr ? 'Les zones physiques du salon (réutilise le modèle des centres d\'artisanat).' : 'The physical zones of the salon (reuses the craft-centre pattern).',
        'sStats' => [
            ['layout-grid', '#3565DE', '#E8EFFB', $pavs->count(), $fr ? 'Pavillons' : 'Pavilions', null],
            ['grid-3x3', '#7C4FE0', '#F0EAFB', DB::table('stands')->where('event_id', $eid)->count(), 'Stands', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Pavillons' : 'Pavilions',
            'cols' => [$fr ? 'Pavillon' : 'Pavilion', 'Code', 'Stands', $fr ? 'Alloués' : 'Allocated', $fr ? 'Exposants' : 'Exhibitors'],
            'rows' => $rows,
        ]],
        'sLinks' => [['label' => $fr ? 'Plan interactif' : 'Floor plan', 'href' => route('siarc.admin.floorplan'), 'icon' => 'map'], ['label' => $fr ? 'Explorateur public' : 'Public explorer', 'href' => route('siarc.pavilions', ['lang' => $lang]), 'icon' => 'external-link']],
    ]);
})->name('siarc.admin.pavilions');

Route::get('/tableau-de-bord/admin/siarc/pavillons/{id}', function (Request $r, $id) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $p = DB::table('pavilions')->where('id', $id)->first();
    abort_if(! $p, 404);
    $stands = DB::table('stands as s')->leftJoin('event_exhibitors as ee', 'ee.id', '=', 's.exhibitor_id')
        ->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')->where('s.pavilion_id', $id)->orderBy('s.code')
        ->get(['s.id', 's.code', 's.status', 'b.name_fr']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-plan', 'sTitle' => $fr ? 'Détail du pavillon' : 'Pavilion detail',
        'sStats' => [
            ['grid-3x3', '#7C4FE0', '#F0EAFB', $stands->count(), 'Stands', null],
            ['check-circle-2', '#157A43', '#E2F3E8', $stands->where('status', 'allocated')->count(), $fr ? 'Alloués' : 'Allocated', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Stands du pavillon' : 'Pavilion stands',
            'cols' => ['Stand', $fr ? 'Statut' : 'Status', $fr ? 'Exposant' : 'Exhibitor'],
            'rows' => $stands->map(fn ($s) => ['href' => route('siarc.admin.stand', $s->id), 'cells' => [$s->code, $tone($s->status), $s->name_fr ?? '—']])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Tous les pavillons' : 'All pavilions', 'href' => route('siarc.admin.pavilions'), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.admin.pavilion');

Route::get('/tableau-de-bord/admin/siarc/plan', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $pavs = DB::table('pavilions')->where('event_id', $eid)->orderBy('sort_order')->get()->keyBy('id');
    $stands = DB::table('stands as s')->leftJoin('event_exhibitors as ee', 'ee.id', '=', 's.exhibitor_id')
        ->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')->where('s.event_id', $eid)
        ->get(['s.id', 's.code', 's.status', 's.pos_x', 's.pos_y', 's.pos_w', 's.pos_h', 's.pavilion_id', 'b.name_fr']);
    return view('pages.siarc.floorplan', compact('lang', 'fr', 'pavs', 'stands'));
})->name('siarc.admin.floorplan');

Route::get('/tableau-de-bord/admin/siarc/stands', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('stands as s')->leftJoin('pavilions as p', 'p.id', '=', 's.pavilion_id')
        ->leftJoin('event_exhibitors as ee', 'ee.id', '=', 's.exhibitor_id')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->where('s.event_id', $eid)->orderBy('s.code')->get(['s.id', 's.code', 'p.name_fr as pavilion', 's.status', 'b.name_fr as exhibitor']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-plan', 'sTitle' => $fr ? 'Allocation des Stands' : 'Stand Allocation',
        'sIntro' => $fr ? 'Attribuez les stands disponibles aux exposants confirmés.' : 'Assign available stands to confirmed exhibitors.',
        'sStats' => [
            ['grid-3x3', '#7C4FE0', '#F0EAFB', $rows->count(), 'Stands', null],
            ['check-circle-2', '#157A43', '#E2F3E8', $rows->where('status', 'allocated')->count(), $fr ? 'Alloués' : 'Allocated', null],
            ['circle-dashed', '#6F6B60', '#F1F1EF', $rows->where('status', 'available')->count(), $fr ? 'Disponibles' : 'Available', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Stands' : 'Stands',
            'cols' => ['Stand', $fr ? 'Pavillon' : 'Pavilion', $fr ? 'Statut' : 'Status', $fr ? 'Exposant' : 'Exhibitor'],
            'rows' => $rows->map(fn ($s) => ['href' => route('siarc.admin.stand', $s->id), 'cells' => [$s->code, $s->pavilion ?? '—', $tone($s->status), $s->exhibitor ?? '—']])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Plan interactif' : 'Floor plan', 'href' => route('siarc.admin.floorplan'), 'icon' => 'map']],
    ]);
})->name('siarc.admin.stands');

Route::get('/tableau-de-bord/admin/siarc/stands/{id}', function (Request $r, $id) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $s = DB::table('stands as s')->leftJoin('pavilions as p', 'p.id', '=', 's.pavilion_id')
        ->leftJoin('event_exhibitors as ee', 'ee.id', '=', 's.exhibitor_id')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->where('s.id', $id)->first(['s.*', 'p.name_fr as pavilion', 'b.name_fr as exhibitor', 'ee.id as exhibitor_id']);
    abort_if(! $s, 404);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-plan', 'sTitle' => ($fr ? 'Stand · ' : 'Stand · ') . $s->code,
        'sStats' => [
            ['layout-grid', '#3565DE', '#E8EFFB', $s->pavilion ?? '—', $fr ? 'Pavillon' : 'Pavilion', null],
            ['ruler', '#7C4FE0', '#F0EAFB', ($s->size_sqm ?? '—') . ' m²', $fr ? 'Surface' : 'Size', null],
            ['tag', '#C97A16', '#FDF3E0', number_format((float) ($s->price ?? 0), 0, ',', ' ') . ' FCFA', $fr ? 'Prix' : 'Price', null],
            ['store', '#157A43', '#E2F3E8', $s->exhibitor ?? ($fr ? 'Libre' : 'Free'), $fr ? 'Exposant' : 'Exhibitor', null],
        ],
        'sLinks' => array_values(array_filter([
            $s->exhibitor_id ? ['label' => $fr ? 'Voir l\'exposant' : 'View exhibitor', 'href' => route('siarc.admin.exhibitor', $s->exhibitor_id), 'icon' => 'store'] : null,
            ['label' => $fr ? 'Allocation des stands' : 'Stand allocation', 'href' => route('siarc.admin.stands'), 'icon' => 'arrow-left'],
        ])),
    ]);
})->name('siarc.admin.stand');

// ─────────────────────────── ADMIN — Registration ───────────────────────────

Route::get('/tableau-de-bord/admin/siarc/visiteurs', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $q = DB::table('visitors')->where('event_id', $eid);
    if ($t = $r->query('type')) $q->where('type', $t);
    $rows = $q->orderByDesc('id')->limit(200)->get();
    $all = DB::table('visitors')->where('event_id', $eid);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => $fr ? 'Visiteurs' : 'Visitors',
        'sStats' => [
            ['users-round', '#C97A16', '#FDF3E0', $all->count(), $fr ? 'Inscrits' : 'Registered', null],
            ['scan-line', '#157A43', '#E2F3E8', $all->clone()->where('status', 'checked_in')->count(), $fr ? 'Présents' : 'Checked-in', null],
            ['star', '#7C4FE0', '#F0EAFB', $all->clone()->where('type', 'vip')->count(), 'VIP', null],
            ['briefcase', '#3565DE', '#E8EFFB', $all->clone()->where('type', 'buyer')->count(), $fr ? 'Acheteurs' : 'Buyers', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Inscriptions' : 'Registrations',
            'cols' => [$fr ? 'Nom' : 'Name', 'Type', 'Badge', $fr ? 'Statut' : 'Status'],
            'rows' => $rows->map(fn ($v) => ['href' => route('siarc.admin.visitor', $v->id), 'cells' => [trim($v->first_name . ' ' . $v->last_name), $tone($v->type), $v->badge_code ?? '—', $tone($v->status)]])->all(),
        ]],
        'sLinks' => [
            ['label' => $fr ? 'Contrôle d\'accès' : 'Entry control', 'href' => route('siarc.admin.entry'), 'icon' => 'scan-line'],
            ['label' => 'Badges', 'href' => route('siarc.admin.badges'), 'icon' => 'id-card'],
            ['label' => 'VIP', 'href' => route('siarc.admin.vip'), 'icon' => 'star'],
            ['label' => $fr ? 'Inscription publique' : 'Public registration', 'href' => route('siarc.register', ['lang' => $lang]), 'icon' => 'external-link'],
        ],
    ]);
})->name('siarc.admin.visitors');

Route::get('/tableau-de-bord/admin/siarc/visiteurs/{id}', function (Request $r, $id) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $v = DB::table('visitors')->where('id', $id)->first();
    abort_if(! $v, 404);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => ($fr ? 'Visiteur · ' : 'Visitor · ') . trim($v->first_name . ' ' . $v->last_name),
        'sStats' => [
            ['tag', '#3565DE', '#E8EFFB', ucfirst($v->type), 'Type', null],
            ['id-card', '#C97A16', '#FDF3E0', $v->badge_code ?? '—', 'Badge', null],
            ['scan-line', '#157A43', '#E2F3E8', $v->checked_in_at ? ($fr ? 'Présent' : 'Checked-in') : ($fr ? 'Attendu' : 'Expected'), $fr ? 'Accès' : 'Access', null],
            ['globe', '#7C4FE0', '#F0EAFB', $v->country ?? '—', $fr ? 'Pays' : 'Country', null],
        ],
        'sLinks' => [['label' => $fr ? 'Tous les visiteurs' : 'All visitors', 'href' => route('siarc.admin.visitors'), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.admin.visitor');

Route::get('/tableau-de-bord/admin/siarc/controle', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $vis = DB::table('visitors')->where('event_id', $eid);
    $recent = DB::table('check_ins')->where('event_id', $eid)->orderByDesc('id')->limit(50)->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => $fr ? 'Contrôle d\'accès' : 'Entry Control',
        'sStats' => [
            ['scan-line', '#157A43', '#E2F3E8', $vis->clone()->where('status', 'checked_in')->count(), $fr ? 'Entrées' : 'Check-ins', null],
            ['users-round', '#C97A16', '#FDF3E0', $vis->count(), $fr ? 'Inscrits' : 'Registered', null],
            ['door-open', '#3565DE', '#E8EFFB', $recent->count(), $fr ? 'Scans récents' : 'Recent scans', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Derniers passages' : 'Recent entries',
            'cols' => ['Type', 'ID', $fr ? 'Porte' : 'Gate', $fr ? 'Heure' : 'Time'],
            'rows' => $recent->map(fn ($c) => ['cells' => [$c->subject_type, '#' . $c->subject_id, $c->gate ?? '—', (string) $c->scanned_at]])->all(),
            'empty' => $fr ? 'Aucun passage enregistré.' : 'No entries recorded.',
        ]],
        'sLinks' => [['label' => 'Check-in QR', 'href' => route('siarc.admin.checkin'), 'icon' => 'qr-code'], ['label' => $fr ? 'Scanner mobile' : 'Mobile scanner', 'href' => route('siarc.mobile.scanner'), 'icon' => 'smartphone']],
    ]);
})->name('siarc.admin.entry');

Route::get('/tableau-de-bord/admin/siarc/badges', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $vis = DB::table('visitors')->where('event_id', $eid)->whereNotNull('badge_code')->orderByDesc('id')->limit(100)->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => $fr ? 'Impression des Badges' : 'Badge Printing',
        'sIntro' => $fr ? 'Réutilise le générateur de certificats/QR existant pour produire les badges nominatifs.' : 'Reuses the existing certificate/QR generator to produce named badges.',
        'sStats' => [['id-card', '#C97A16', '#FDF3E0', DB::table('visitors')->where('event_id', $eid)->whereNotNull('badge_code')->count(), $fr ? 'Badges visiteurs' : 'Visitor badges', null], ['id-card', '#157A43', '#E2F3E8', DB::table('event_exhibitors')->where('event_id', $eid)->whereNotNull('badge_code')->count(), $fr ? 'Badges exposants' : 'Exhibitor badges', null]],
        'sTables' => [[
            'title' => $fr ? 'Badges à imprimer' : 'Badges to print',
            'cols' => [$fr ? 'Nom' : 'Name', 'Badge', 'Type'],
            'rows' => $vis->map(fn ($v) => ['cells' => [trim($v->first_name . ' ' . $v->last_name), $v->badge_code, ucfirst($v->type)]])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Vérifier un certificat' : 'Verify a certificate', 'href' => route('certificate.verify'), 'icon' => 'shield-check']],
    ]);
})->name('siarc.admin.badges');

Route::get('/tableau-de-bord/admin/siarc/checkin', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $recent = DB::table('check_ins')->where('event_id', $eid)->orderByDesc('id')->limit(30)->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => $fr ? 'Check-in QR' : 'QR Check-in',
        'sIntro' => $fr ? 'Scannez ou saisissez un code badge pour enregistrer une entrée (réutilise la vérification QR des certificats).' : 'Scan or enter a badge code to record entry (reuses the certificate QR verification).',
        'sStats' => [['qr-code', '#157A43', '#E2F3E8', $recent->count(), $fr ? 'Scans récents' : 'Recent scans', null]],
        'sTables' => [[
            'title' => $fr ? 'Derniers scans' : 'Recent scans',
            'cols' => ['Type', 'ID', $fr ? 'Porte' : 'Gate', $fr ? 'Heure' : 'Time'],
            'rows' => $recent->map(fn ($c) => ['cells' => [$c->subject_type, '#' . $c->subject_id, $c->gate ?? '—', (string) $c->scanned_at]])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Contrôle d\'accès' : 'Entry control', 'href' => route('siarc.admin.entry'), 'icon' => 'door-open']],
    ]);
})->name('siarc.admin.checkin');

// ─────────────────────────────── ADMIN — B2B ────────────────────────────────

Route::get('/tableau-de-bord/admin/siarc/b2b', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('b2b_meetings as m')->leftJoin('visitors as v', 'v.id', '=', 'm.requester_visitor_id')
        ->leftJoin('event_exhibitors as ee', 'ee.id', '=', 'm.host_exhibitor_id')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->where('m.event_id', $eid)->orderByDesc('m.scheduled_at')->get(['m.id', 'v.first_name', 'v.last_name', 'b.name_fr as host', 'm.scheduled_at', 'm.status']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-b2b', 'sTitle' => 'Business Matchmaking',
        'sIntro' => $fr ? 'S\'appuie sur la messagerie et le moteur de devis (RFQ) existants.' : 'Built on the existing messaging + quote (RFQ) engine.',
        'sStats' => [
            ['handshake', '#0D9488', '#E1F4F1', $rows->count(), $fr ? 'Rendez-vous' : 'Meetings', null],
            ['check-circle-2', '#157A43', '#E2F3E8', $rows->where('status', 'confirmed')->count(), $fr ? 'Confirmés' : 'Confirmed', null],
            ['clock', '#C97A16', '#FDF3E0', $rows->where('status', 'requested')->count(), $fr ? 'Demandés' : 'Requested', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Rendez-vous d\'affaires' : 'Business meetings',
            'cols' => [$fr ? 'Demandeur' : 'Requester', $fr ? 'Exposant hôte' : 'Host exhibitor', $fr ? 'Horaire' : 'Time', $fr ? 'Statut' : 'Status'],
            'rows' => $rows->map(fn ($m) => ['href' => route('siarc.admin.meeting', $m->id), 'cells' => [trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? '')) ?: '—', $m->host ?? '—', (string) $m->scheduled_at, $tone($m->status)]])->all(),
        ]],
        'sLinks' => [['label' => 'Matchmaking', 'href' => route('siarc.admin.matchmaking'), 'icon' => 'sparkles'], ['label' => $fr ? 'Messagerie' : 'Messaging', 'href' => route('messages.inbox'), 'icon' => 'mail']],
    ]);
})->name('siarc.admin.b2b');

Route::get('/tableau-de-bord/admin/siarc/b2b/{id}', function (Request $r, $id) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $m = DB::table('b2b_meetings as m')->leftJoin('visitors as v', 'v.id', '=', 'm.requester_visitor_id')
        ->leftJoin('event_exhibitors as ee', 'ee.id', '=', 'm.host_exhibitor_id')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->where('m.id', $id)->first(['m.*', 'v.first_name', 'v.last_name', 'b.name_fr as host']);
    abort_if(! $m, 404);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-b2b', 'sTitle' => $fr ? 'Rendez-vous B2B' : 'B2B Meeting',
        'sStats' => [
            ['user', '#3565DE', '#E8EFFB', trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? '')) ?: '—', $fr ? 'Demandeur' : 'Requester', null],
            ['store', '#157A43', '#E2F3E8', $m->host ?? '—', $fr ? 'Hôte' : 'Host', null],
            ['calendar-clock', '#C97A16', '#FDF3E0', (string) $m->scheduled_at, $fr ? 'Horaire' : 'Time', null],
            ['circle-dot', '#7C4FE0', '#F0EAFB', ucfirst($m->status), $fr ? 'Statut' : 'Status', null],
        ],
        'sLinks' => [['label' => $fr ? 'Tous les rendez-vous' : 'All meetings', 'href' => route('siarc.admin.b2b'), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.admin.meeting');

Route::get('/tableau-de-bord/admin/siarc/matchmaking', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $exhBySector = DB::table('event_exhibitors as ee')->join('businesses as b', 'b.id', '=', 'ee.business_id')
        ->leftJoin('industries as i', 'i.id', '=', 'b.industry_id')->where('ee.event_id', $eid)
        ->groupBy('i.name_fr')->selectRaw('i.name_fr as sector, count(*) as c')->orderByDesc('c')->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-b2b', 'sTitle' => $fr ? 'Matchmaking d\'affaires' : 'Business Matchmaking',
        'sIntro' => $fr ? 'Met en relation acheteurs et exposants selon la nomenclature officielle des métiers.' : 'Connects buyers and exhibitors using the official trades taxonomy.',
        'sStats' => [
            ['store', '#157A43', '#E2F3E8', DB::table('event_exhibitors')->where('event_id', $eid)->count(), $fr ? 'Exposants' : 'Exhibitors', null],
            ['briefcase', '#3565DE', '#E8EFFB', DB::table('visitors')->where('event_id', $eid)->where('type', 'buyer')->count(), $fr ? 'Acheteurs' : 'Buyers', null],
            ['handshake', '#0D9488', '#E1F4F1', DB::table('b2b_meetings')->where('event_id', $eid)->count(), 'B2B', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Exposants par métier' : 'Exhibitors by trade',
            'cols' => [$fr ? 'Métier' : 'Trade', $fr ? 'Exposants' : 'Exhibitors'],
            'rows' => $exhBySector->map(fn ($s) => ['cells' => [$s->sector ?? '—', $s->c]])->all(),
        ]],
        'sLinks' => [['label' => 'B2B', 'href' => route('siarc.admin.b2b'), 'icon' => 'handshake']],
    ]);
})->name('siarc.admin.matchmaking');

// ──────────────────────────── ADMIN — Programme ─────────────────────────────

Route::get('/tableau-de-bord/admin/siarc/programme', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('programme_sessions as s')->leftJoin('speakers as sp', 'sp.id', '=', 's.speaker_id')
        ->leftJoin('pavilions as p', 'p.id', '=', 's.pavilion_id')->where('s.event_id', $eid)->orderBy('s.starts_at')
        ->get(['s.id', 's.title_fr', 's.type', 's.starts_at', 'p.name_fr as pavilion', 'sp.name as speaker']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-prog', 'sTitle' => $fr ? 'Programme & Activités' : 'Programme & Activities',
        'sStats' => [
            ['calendar-days', '#7C4FE0', '#F0EAFB', $rows->count(), 'Sessions', null],
            ['presentation', '#C97A16', '#FDF3E0', $rows->where('type', 'workshop')->count(), $fr ? 'Ateliers' : 'Workshops', null],
            ['mic', '#157A43', '#E2F3E8', DB::table('speakers')->where('event_id', $eid)->count(), $fr ? 'Intervenants' : 'Speakers', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Sessions du programme' : 'Programme sessions',
            'cols' => [$fr ? 'Titre' : 'Title', 'Type', $fr ? 'Horaire' : 'Time', $fr ? 'Pavillon' : 'Pavilion', $fr ? 'Intervenant' : 'Speaker'],
            'rows' => $rows->map(fn ($s) => ['href' => $s->type === 'workshop' ? route('siarc.admin.workshop', $s->id) : route('siarc.admin.session', $s->id), 'cells' => [$s->title_fr, ['badge' => ucfirst($s->type), 'tone' => $s->type === 'workshop' ? 'gold' : 'purple'], (string) $s->starts_at, $s->pavilion ?? '—', $s->speaker ?? '—']])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Intervenants' : 'Speakers', 'href' => route('siarc.admin.speakers'), 'icon' => 'mic'], ['label' => $fr ? 'Calendrier' : 'Calendar', 'href' => route('siarc.admin.calendar'), 'icon' => 'calendar'], ['label' => $fr ? 'Programme public' : 'Public schedule', 'href' => route('siarc.programme', ['lang' => $lang]), 'icon' => 'external-link']],
    ]);
})->name('siarc.admin.programme');

Route::get('/tableau-de-bord/admin/siarc/programme/{id}', function (Request $r, $id) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $s = DB::table('programme_sessions as s')->leftJoin('pavilions as p', 'p.id', '=', 's.pavilion_id')->where('s.id', $id)->first(['s.*', 'p.name_fr as pavilion']);
    abort_if(! $s, 404);
    $speakers = DB::table('session_speaker as ss')->join('speakers as sp', 'sp.id', '=', 'ss.speaker_id')->where('ss.session_id', $id)->pluck('sp.name');
    $regs = DB::table('session_registrations')->where('session_id', $id)->count();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-prog', 'sTitle' => $fr ? 'Détail de la session' : 'Session detail',
        'sStats' => [
            ['tag', '#7C4FE0', '#F0EAFB', ucfirst($s->type), 'Type', null],
            ['calendar-clock', '#C97A16', '#FDF3E0', (string) $s->starts_at, $fr ? 'Début' : 'Start', null],
            ['layout-grid', '#3565DE', '#E8EFFB', $s->pavilion ?? ($s->room ?? '—'), $fr ? 'Lieu' : 'Location', null],
            ['users', '#157A43', '#E2F3E8', $regs, $fr ? 'Inscrits' : 'Registered', null],
        ],
        'sIntro' => $fr ? 'Intervenant(s) : ' . ($speakers->implode(', ') ?: '—') : 'Speaker(s): ' . ($speakers->implode(', ') ?: '—'),
        'sLinks' => [['label' => $fr ? 'Tout le programme' : 'Full programme', 'href' => route('siarc.admin.programme'), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.admin.session');

Route::get('/tableau-de-bord/admin/siarc/ateliers/{id}', function (Request $r, $id) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $s = DB::table('programme_sessions')->where('id', $id)->first();
    abort_if(! $s, 404);
    $regs = DB::table('session_registrations')->where('session_id', $id)->orderByDesc('id')->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-prog', 'sTitle' => $fr ? 'Détail de l\'atelier' : 'Workshop detail',
        'sStats' => [
            ['users', '#157A43', '#E2F3E8', $regs->count() . '/' . ($s->capacity ?? '∞'), $fr ? 'Inscriptions' : 'Registrations', null],
            ['calendar-clock', '#C97A16', '#FDF3E0', (string) $s->starts_at, $fr ? 'Horaire' : 'Time', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Participants inscrits' : 'Registered participants',
            'cols' => [$fr ? 'Nom' : 'Name', 'Email', $fr ? 'Inscrit le' : 'Registered'],
            'rows' => $regs->map(fn ($x) => ['cells' => [$x->name ?? '—', $x->email ?? '—', (string) $x->registered_at]])->all(),
            'empty' => $fr ? 'Aucune inscription.' : 'No registrations.',
        ]],
        'sLinks' => [['label' => $fr ? 'Inscription publique' : 'Public registration', 'href' => route('siarc.workshop.register', ['id' => $id, 'lang' => $lang]), 'icon' => 'external-link']],
    ]);
})->name('siarc.admin.workshop');

Route::get('/tableau-de-bord/admin/siarc/intervenants', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('speakers')->where('event_id', $eid)->orderBy('sort_order')->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-prog', 'sTitle' => $fr ? 'Gestion des Intervenants' : 'Speaker Management',
        'sStats' => [['mic', '#157A43', '#E2F3E8', $rows->count(), $fr ? 'Intervenants' : 'Speakers', null], ['star', '#C97A16', '#FDF3E0', $rows->where('is_featured', true)->count(), $fr ? 'À la une' : 'Featured', null]],
        'sTables' => [[
            'title' => $fr ? 'Intervenants' : 'Speakers',
            'cols' => [$fr ? 'Nom' : 'Name', $fr ? 'Rôle' : 'Role', $fr ? 'Organisation' : 'Organization'],
            'rows' => $rows->map(fn ($s) => ['href' => route('siarc.admin.speaker', $s->id), 'cells' => [$s->name, $s->role_fr ?? '—', $s->organization ?? '—']])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Annuaire public' : 'Public directory', 'href' => route('siarc.speakers', ['lang' => $lang]), 'icon' => 'external-link']],
    ]);
})->name('siarc.admin.speakers');

Route::get('/tableau-de-bord/admin/siarc/intervenants/{id}', function (Request $r, $id) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    $s = DB::table('speakers')->where('id', $id)->first();
    abort_if(! $s, 404);
    $sessions = DB::table('session_speaker as ss')->join('programme_sessions as p', 'p.id', '=', 'ss.session_id')->where('ss.speaker_id', $id)->get(['p.id', 'p.title_fr', 'p.starts_at']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-prog', 'sTitle' => $fr ? 'Détails de l\'intervenant' : 'Speaker details',
        'sIntro' => ($s->role_fr ?? '') . ($s->organization ? ' — ' . $s->organization : ''),
        'sTables' => [[
            'title' => $fr ? 'Interventions' : 'Sessions',
            'cols' => [$fr ? 'Session' : 'Session', $fr ? 'Horaire' : 'Time'],
            'rows' => $sessions->map(fn ($x) => ['href' => route('siarc.admin.session', $x->id), 'cells' => [$x->title_fr, (string) $x->starts_at]])->all(),
            'empty' => $fr ? 'Aucune intervention programmée.' : 'No sessions scheduled.',
        ]],
        'sLinks' => [['label' => $fr ? 'Tous les intervenants' : 'All speakers', 'href' => route('siarc.admin.speakers'), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.admin.speaker');

// ──────────────────────────── ADMIN — Operations ────────────────────────────

Route::get('/tableau-de-bord/admin/siarc/calendrier', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $sessions = DB::table('programme_sessions')->where('event_id', $eid)->orderBy('starts_at')->get();
    $byDay = $sessions->groupBy(fn ($s) => $s->starts_at ? Carbon::parse($s->starts_at)->format('Y-m-d') : '—');
    $tables = [];
    foreach ($byDay as $day => $daySessions) {
        $tables[] = [
            'title' => $day !== '—' ? Carbon::parse($day)->translatedFormat('l d F Y') : ($fr ? 'Non planifié' : 'Unscheduled'),
            'cols' => [$fr ? 'Heure' : 'Time', $fr ? 'Session' : 'Session', 'Type'],
            'rows' => $daySessions->map(fn ($s) => ['cells' => [$s->starts_at ? Carbon::parse($s->starts_at)->format('H:i') : '—', $s->title_fr, ucfirst($s->type)]])->all(),
        ];
    }
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-prog', 'sTitle' => $fr ? 'Calendrier des événements' : 'Event Calendar',
        'sStats' => [['calendar', '#3565DE', '#E8EFFB', $byDay->count(), $fr ? 'Jours' : 'Days', null], ['calendar-days', '#7C4FE0', '#F0EAFB', $sessions->count(), 'Sessions', null]],
        'sTables' => $tables ?: [['title' => 'Sessions', 'cols' => ['—'], 'rows' => [], 'empty' => $fr ? 'Aucune session.' : 'No sessions.']],
    ]);
})->name('siarc.admin.calendar');

Route::get('/tableau-de-bord/admin/siarc/analytique', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => $fr ? 'Analytique du Salon' : 'Event Analytics',
        'sIntro' => $fr ? 'Vue analytique dédiée au SIARC (réutilise le moteur d\'analytique de la plateforme).' : 'SIARC-scoped analytics (reuses the platform analytics engine).',
        'sStats' => [
            ['store', '#157A43', '#E2F3E8', DB::table('event_exhibitors')->where('event_id', $eid)->count(), $fr ? 'Exposants' : 'Exhibitors', null],
            ['users-round', '#C97A16', '#FDF3E0', DB::table('visitors')->where('event_id', $eid)->count(), $fr ? 'Visiteurs' : 'Visitors', null],
            ['scan-line', '#3565DE', '#E8EFFB', DB::table('visitors')->where('event_id', $eid)->where('status', 'checked_in')->count(), $fr ? 'Présents' : 'Checked-in', null],
            ['calendar-days', '#7C4FE0', '#F0EAFB', DB::table('programme_sessions')->where('event_id', $eid)->count(), 'Sessions', null],
            ['handshake', '#0D9488', '#E1F4F1', DB::table('b2b_meetings')->where('event_id', $eid)->count(), 'B2B', null],
        ],
        'sLinks' => [['label' => $fr ? 'Fréquentation' : 'Attendance', 'href' => route('siarc.admin.attendance'), 'icon' => 'trending-up'], ['label' => $fr ? 'Rapports' : 'Reports', 'href' => route('siarc.admin.reports'), 'icon' => 'file-text'], ['label' => $fr ? 'Analytique plateforme' : 'Platform analytics', 'href' => route('admin.analytics'), 'icon' => 'bar-chart-3']],
    ]);
})->name('siarc.admin.analytics');

Route::get('/tableau-de-bord/admin/siarc/frequentation', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $byGate = DB::table('check_ins')->where('event_id', $eid)->groupBy('gate')->selectRaw('gate, count(*) as c')->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => $fr ? 'Analyse de Fréquentation' : 'Attendance Analytics',
        'sStats' => [
            ['scan-line', '#157A43', '#E2F3E8', DB::table('check_ins')->where('event_id', $eid)->count(), $fr ? 'Passages' : 'Check-ins', null],
            ['door-open', '#3565DE', '#E8EFFB', $byGate->count(), $fr ? 'Portes actives' : 'Active gates', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Passages par porte' : 'Check-ins by gate',
            'cols' => [$fr ? 'Porte' : 'Gate', $fr ? 'Passages' : 'Check-ins'],
            'rows' => $byGate->map(fn ($g) => ['cells' => [$g->gate ?? '—', $g->c]])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Analytique' : 'Analytics', 'href' => route('siarc.admin.analytics'), 'icon' => 'bar-chart-3']],
    ]);
})->name('siarc.admin.attendance');

Route::get('/tableau-de-bord/admin/siarc/direct', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $recent = DB::table('check_ins')->where('event_id', $eid)->orderByDesc('id')->limit(20)->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => 'Live Event Monitoring',
        'sIntro' => $fr ? 'Affluence en temps réel (l\'infrastructure websocket Reverb est configurée).' : 'Real-time attendance (Reverb websocket infrastructure is configured).',
        'sStats' => [
            ['activity', '#DC2626', '#FDE8E8', DB::table('visitors')->where('event_id', $eid)->where('status', 'checked_in')->count(), $fr ? 'Présents' : 'On site', null],
            ['calendar-check', '#157A43', '#E2F3E8', DB::table('programme_sessions')->where('event_id', $eid)->count(), 'Sessions', null],
        ],
        'sTables' => [[
            'title' => $fr ? 'Flux des entrées' : 'Live entry feed',
            'cols' => ['Type', 'ID', $fr ? 'Porte' : 'Gate', $fr ? 'Heure' : 'Time'],
            'rows' => $recent->map(fn ($c) => ['cells' => [$c->subject_type, '#' . $c->subject_id, $c->gate ?? '—', (string) $c->scanned_at]])->all(),
        ]],
    ]);
})->name('siarc.admin.live');

Route::get('/tableau-de-bord/admin/siarc/rapports', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => $fr ? 'Rapports SIARC' : 'SIARC Reports',
        'sTables' => [[
            'title' => $fr ? 'Synthèse du salon' : 'Salon summary',
            'cols' => [$fr ? 'Indicateur' : 'Indicator', $fr ? 'Valeur' : 'Value'],
            'rows' => collect([
                [$fr ? 'Exposants' : 'Exhibitors', DB::table('event_exhibitors')->where('event_id', $eid)->count()],
                [$fr ? 'Pavillons' : 'Pavilions', DB::table('pavilions')->where('event_id', $eid)->count()],
                [$fr ? 'Stands alloués' : 'Stands allocated', DB::table('stands')->where('event_id', $eid)->where('status', 'allocated')->count()],
                [$fr ? 'Visiteurs inscrits' : 'Visitors registered', DB::table('visitors')->where('event_id', $eid)->count()],
                [$fr ? 'Entrées enregistrées' : 'Check-ins', DB::table('check_ins')->where('event_id', $eid)->count()],
                [$fr ? 'Rendez-vous B2B' : 'B2B meetings', DB::table('b2b_meetings')->where('event_id', $eid)->count()],
                ['Sessions', DB::table('programme_sessions')->where('event_id', $eid)->count()],
            ])->map(fn ($x) => ['cells' => $x])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Exporter' : 'Export', 'href' => route('admin.exports'), 'icon' => 'download'], ['label' => $fr ? 'Rapports plateforme' : 'Platform reports', 'href' => route('admin.reports'), 'icon' => 'file-text']],
    ]);
})->name('siarc.admin.reports');

Route::get('/tableau-de-bord/admin/siarc/incidents', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => $fr ? 'Gestion des Incidents' : 'Incident Management',
        'sIntro' => $fr ? 'Les incidents du salon sont suivis via le système de tickets support existant.' : 'Salon incidents are tracked through the existing support-ticket system.',
        'sLinks' => [['label' => $fr ? 'Ouvrir les tickets support' : 'Open support tickets', 'href' => route('admin.support'), 'icon' => 'life-buoy']],
    ]);
})->name('siarc.admin.incidents');

Route::get('/tableau-de-bord/admin/siarc/vip', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('visitors')->where('event_id', $eid)->where('type', 'vip')->orderByDesc('id')->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => $fr ? 'Gestion VIP' : 'VIP Management',
        'sStats' => [['star', '#7C4FE0', '#F0EAFB', $rows->count(), 'VIP', null], ['scan-line', '#157A43', '#E2F3E8', $rows->where('status', 'checked_in')->count(), $fr ? 'Présents' : 'Checked-in', null]],
        'sTables' => [[
            'title' => 'VIP',
            'cols' => [$fr ? 'Nom' : 'Name', $fr ? 'Organisation' : 'Organization', 'Badge', $fr ? 'Statut' : 'Status'],
            'rows' => $rows->map(fn ($v) => ['href' => route('siarc.admin.visitor', $v->id), 'cells' => [trim($v->first_name . ' ' . $v->last_name), $v->organization ?? '—', $v->badge_code ?? '—', $tone($v->status)]])->all(),
        ]],
    ]);
})->name('siarc.admin.vip');

// ───────────────────────────── ADMIN — Mobile ops ───────────────────────────

Route::get('/tableau-de-bord/siarc/scanner', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $recent = DB::table('check_ins')->where('event_id', $eid)->orderByDesc('id')->limit(15)->get();
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-vis', 'sTitle' => $fr ? 'Scanner du Personnel' : 'Staff Scanner',
        'sIntro' => $fr ? 'Interface mobile de scan des badges (réutilise la vérification QR des certificats).' : 'Mobile badge-scan interface (reuses the certificate QR verification).',
        'sStats' => [['qr-code', '#157A43', '#E2F3E8', $recent->count(), $fr ? 'Derniers scans' : 'Recent scans', null]],
        'sTables' => [[
            'title' => $fr ? 'Scans récents' : 'Recent scans',
            'cols' => ['Type', 'ID', $fr ? 'Heure' : 'Time'],
            'rows' => $recent->map(fn ($c) => ['cells' => [$c->subject_type, '#' . $c->subject_id, (string) $c->scanned_at]])->all(),
        ]],
        'sLinks' => [['label' => $fr ? 'Check-in exposants' : 'Exhibitor check-in', 'href' => route('siarc.mobile.exhibitor-checkin'), 'icon' => 'store']],
    ]);
})->name('siarc.mobile.scanner');

Route::get('/tableau-de-bord/siarc/exposant-checkin', function (Request $r) use ($tone) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $rows = DB::table('event_exhibitors as ee')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->where('ee.event_id', $eid)->orderByDesc('ee.id')->get(['ee.id', 'b.name_fr', 'ee.checked_in_at']);
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc-exh', 'sTitle' => $fr ? 'Check-in des Exposants' : 'Exhibitor Check-in',
        'sForm' => [
            'action' => route('siarc.mobile.exhibitor-checkin.store'),
            'label' => $fr ? 'Badge exposant (code ou QR)' : 'Exhibitor badge (code or QR)',
            'placeholder' => 'SIARC-EXH-0011', 'button' => $fr ? 'Enregistrer le check-in' : 'Record check-in',
        ],
        'sStats' => [['store', '#157A43', '#E2F3E8', $rows->count(), $fr ? 'Exposants' : 'Exhibitors', null], ['scan-line', '#3565DE', '#E8EFFB', $rows->whereNotNull('checked_in_at')->count(), $fr ? 'Enregistrés' : 'Checked-in', null]],
        'sTables' => [[
            'title' => $fr ? 'Exposants' : 'Exhibitors',
            'cols' => [$fr ? 'Exposant' : 'Exhibitor', 'Check-in'],
            'rows' => $rows->map(fn ($x) => ['href' => route('siarc.admin.exhibitor', $x->id), 'cells' => [$x->name_fr ?? '—', $x->checked_in_at ? ['badge' => '✓', 'tone' => 'green'] : ['badge' => '—', 'tone' => 'grey']]])->all(),
        ]],
    ]);
})->name('siarc.mobile.exhibitor-checkin');

// ───────────────────────────── PUBLIC — Website ─────────────────────────────

Route::get('/siarc', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sTitle' => $fr ? 'SIARC 2026' : 'SIARC 2026', 'sPending' => true,
        'sIntro' => $fr ? 'Le Salon International de l\'Artisanat du Cameroun — exposants, pavillons, programme et rencontres d\'affaires.' : 'The International Craft Fair of Cameroon — exhibitors, pavilions, programme and business meetings.',
        'sCards' => [
            ['title' => $fr ? 'Exposants' : 'Exhibitors', 'sub' => DB::table('event_exhibitors')->where('event_id', $eid)->count() . ($fr ? ' exposants' : ' exhibitors'), 'icon' => 'store', 'tone' => 'green', 'href' => route('siarc.exhibitors', ['lang' => $lang])],
            ['title' => $fr ? 'Pavillons' : 'Pavilions', 'sub' => DB::table('pavilions')->where('event_id', $eid)->count() . ($fr ? ' pavillons' : ' pavilions'), 'icon' => 'layout-grid', 'tone' => 'blue', 'href' => route('siarc.pavilions', ['lang' => $lang])],
            ['title' => $fr ? 'Programme' : 'Programme', 'sub' => DB::table('programme_sessions')->where('event_id', $eid)->count() . ' sessions', 'icon' => 'calendar-days', 'tone' => 'purple', 'href' => route('siarc.programme', ['lang' => $lang])],
            ['title' => $fr ? 'Intervenants' : 'Speakers', 'sub' => DB::table('speakers')->where('event_id', $eid)->count() . ($fr ? ' intervenants' : ' speakers'), 'icon' => 'mic', 'tone' => 'gold', 'href' => route('siarc.speakers', ['lang' => $lang])],
            ['title' => $fr ? 'S\'inscrire' : 'Register', 'sub' => $fr ? 'Visiteur / acheteur' : 'Visitor / buyer', 'icon' => 'ticket', 'tone' => 'green', 'href' => route('siarc.register', ['lang' => $lang])],
        ],
    ]);
})->name('siarc.home');

Route::get('/siarc/exposants', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $pavId = (int) $r->query('pavilion', 0);
    $pav = $pavId ? DB::table('pavilions')->where('id', $pavId)->where('event_id', $eid)->first() : null;
    $exh = DB::table('event_exhibitors as ee')->join('businesses as b', 'b.id', '=', 'ee.business_id')
        ->leftJoin('pavilions as p', 'p.id', '=', 'ee.pavilion_id')->where('ee.event_id', $eid)->where('ee.status', 'confirmed')
        ->when($pav, fn ($q) => $q->where('ee.pavilion_id', $pav->id))
        ->orderBy('b.name_fr')->get(['b.name_fr', 'b.slug', 'p.name_fr as pavilion', 'ee.booth_number']);
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => $fr ? 'Exposants' : 'Exhibitors',
        'sTitle' => $pav ? (($fr ? 'Exposants · ' : 'Exhibitors · ') . $pav->name_fr) : ($fr ? 'Annuaire des Exposants' : 'Exhibitors Directory'),
        'sIntro' => $pav
            ? ($fr ? 'Exposants du pavillon « ' . $pav->name_fr . ' ».' : 'Exhibitors in the ' . ($pav->name_en ?? $pav->name_fr) . ' pavilion.')
            : ($fr ? 'Les artisans et entreprises présents au SIARC 2026.' : 'The artisans and businesses at SIARC 2026.'),
        'sCards' => $exh->map(fn ($x) => ['title' => $x->name_fr, 'sub' => ($x->pavilion ?? '') . ($x->booth_number ? ' · ' . $x->booth_number : ''), 'icon' => 'store', 'tone' => 'green', 'href' => route('siarc.exhibitor', ['slug' => $x->slug, 'lang' => $lang])])->all(),
        'sLinks' => $pav ? [['label' => $fr ? 'Tous les exposants' : 'All exhibitors', 'href' => route('siarc.exhibitors', ['lang' => $lang]), 'icon' => 'arrow-left'], ['label' => $fr ? 'Tous les pavillons' : 'All pavilions', 'href' => route('siarc.pavilions', ['lang' => $lang]), 'icon' => 'layout-grid']] : null,
        'sPending' => true,
    ]);
})->name('siarc.exhibitors');

Route::get('/siarc/exposants/{slug}', function (Request $r, $slug) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $b = DB::table('businesses')->where('slug', $slug)->whereNull('deleted_at')->first();
    abort_if(! $b, 404);
    $ee = DB::table('event_exhibitors as ee')->leftJoin('pavilions as p', 'p.id', '=', 'ee.pavilion_id')
        ->where('ee.event_id', $eid)->where('ee.business_id', $b->id)->first(['ee.booth_number', 'p.name_fr as pavilion']);
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => $b->name_fr, 'sTitle' => $b->name_fr,
        'sIntro' => $fr ? 'Exposant au SIARC 2026 — fiche reliée à la boutique en ligne du vendeur.' : 'SIARC 2026 exhibitor — linked to the vendor\'s online storefront.',
        'sStats' => [
            ['layout-grid', '#3565DE', '#E8EFFB', $ee->pavilion ?? '—', $fr ? 'Pavillon' : 'Pavilion', null],
            ['store', '#157A43', '#E2F3E8', $ee->booth_number ?? '—', 'Stand', null],
        ],
        'sLinks' => [['label' => $fr ? 'Boutique du vendeur' : 'Vendor storefront', 'href' => route('businesses.show', ['slug' => $slug, 'lang' => $lang]), 'icon' => 'store'], ['label' => $fr ? 'Tous les exposants' : 'All exhibitors', 'href' => route('siarc.exhibitors', ['lang' => $lang]), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.exhibitor');

Route::get('/siarc/pavillons', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $pavs = DB::table('pavilions')->where('event_id', $eid)->orderBy('sort_order')->get();
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => $fr ? 'Pavillons' : 'Pavilions',
        'sTitle' => $fr ? 'Explorateur des Pavillons' : 'Pavilion Explorer',
        'sCards' => $pavs->map(fn ($p) => ['title' => $fr ? $p->name_fr : ($p->name_en ?? $p->name_fr), 'sub' => DB::table('event_exhibitors')->where('pavilion_id', $p->id)->count() . ($fr ? ' exposants' : ' exhibitors'), 'icon' => $p->icon ?? 'layout-grid', 'tone' => 'blue', 'href' => route('siarc.exhibitors', ['lang' => $lang, 'pavilion' => $p->id])])->all(),
        'sPending' => true,
    ]);
})->name('siarc.pavilions');

Route::get('/siarc/programme', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $sessions = DB::table('programme_sessions')->where('event_id', $eid)->orderBy('starts_at')->get();
    $byDay = $sessions->groupBy(fn ($s) => $s->starts_at ? Carbon::parse($s->starts_at)->format('Y-m-d') : '—');
    $tables = [];
    foreach ($byDay as $day => $daySessions) {
        $tables[] = [
            'title' => $day !== '—' ? Carbon::parse($day)->translatedFormat('l d F') : ($fr ? 'À planifier' : 'To be scheduled'),
            'cols' => [$fr ? 'Activité' : 'Activity', $fr ? 'Heure' : 'Time', 'Type', $fr ? 'Lieu' : 'Venue'],
            'rows' => $daySessions->map(fn ($s) => [
                'href' => ($s->type === 'workshop' || $s->registration_required) ? route('siarc.workshop.register', ['id' => $s->id, 'lang' => $lang]) : null,
                'cells' => [$fr ? $s->title_fr : ($s->title_en ?? $s->title_fr), $s->starts_at ? Carbon::parse($s->starts_at)->format('H:i') : '—', ucfirst($s->type), $s->room ?? '—'],
            ])->all(),
        ];
    }
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => 'Programme', 'sTitle' => $fr ? 'Programme du Salon' : 'Programme Schedule',
        'sIntro' => $fr ? 'Cliquez sur un atelier pour vous y inscrire.' : 'Click a workshop to register for it.',
        'sTables' => $tables ?: [['title' => 'Programme', 'cols' => ['—'], 'rows' => [], 'empty' => $fr ? 'Programme à venir.' : 'Programme coming soon.']],
        'sPending' => true,
    ]);
})->name('siarc.programme');

Route::get('/siarc/intervenants', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $speakers = DB::table('speakers')->where('event_id', $eid)->orderBy('sort_order')->get();
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => $fr ? 'Intervenants' : 'Speakers', 'sTitle' => $fr ? 'Nos Intervenants' : 'Speaker Directory',
        'sCards' => $speakers->map(fn ($s) => ['title' => $s->name, 'sub' => trim(($s->role_fr ?? '') . ($s->organization ? ' · ' . $s->organization : '')), 'icon' => 'mic', 'tone' => 'gold', 'href' => route('siarc.speaker', ['id' => $s->id, 'lang' => $lang]), 'badge' => $s->is_featured ? ($fr ? 'À la une' : 'Featured') : null])->all(),
        'sPending' => true,
    ]);
})->name('siarc.speakers');

Route::get('/siarc/intervenants/{id}', function (Request $r, $id) {
    $lang = webLang($r); $fr = $lang === 'fr';
    $s = DB::table('speakers')->where('id', $id)->first();
    abort_if(! $s, 404);
    $sessions = DB::table('session_speaker as ss')->join('programme_sessions as p', 'p.id', '=', 'ss.session_id')->where('ss.speaker_id', $id)->get(['p.title_fr', 'p.starts_at', 'p.room']);
    return view('pages.siarc.public', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => $s->name, 'sTitle' => $s->name,
        'sIntro' => trim(($s->role_fr ?? '') . ($s->organization ? ' — ' . $s->organization : '')) . '. ' . ($s->bio_fr ?? ''),
        'sTables' => [[
            'title' => $fr ? 'Interventions' : 'Sessions',
            'cols' => [$fr ? 'Activité' : 'Session', $fr ? 'Horaire' : 'Time', $fr ? 'Lieu' : 'Venue'],
            'rows' => $sessions->map(fn ($x) => ['cells' => [$x->title_fr, (string) $x->starts_at, $x->room ?? '—']])->all(),
            'empty' => $fr ? 'Aucune intervention.' : 'No sessions.',
        ]],
        'sLinks' => [['label' => $fr ? 'Tous les intervenants' : 'All speakers', 'href' => route('siarc.speakers', ['lang' => $lang]), 'icon' => 'arrow-left']],
    ]);
})->name('siarc.speaker');

Route::get('/siarc/inscription', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr';
    return view('pages.siarc.register', ['lang' => $lang]);
})->name('siarc.register');

Route::post('/siarc/inscription', function (Request $r) {
    $data = $r->validate([
        'first_name' => 'required|string|max:120', 'last_name' => 'nullable|string|max:120',
        'email' => 'nullable|email|max:190', 'phone' => 'nullable|string|max:60',
        'organization' => 'nullable|string|max:190', 'type' => 'nullable|in:visitor,buyer,press',
    ]);
    $eid = siarcEvent()?->id;
    $badge = null;
    if ($eid) {
        $n = DB::table('visitors')->where('event_id', $eid)->count() + 1;
        $badge = 'SIARC-VIS-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
        $visitorId = DB::table('visitors')->insertGetId($data + [
            'event_id' => $eid, 'type' => $data['type'] ?? 'visitor', 'status' => 'registered',
            'badge_code' => $badge,
            'qr_token' => \Illuminate\Support\Str::random(40), 'registered_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        session(['siarc_visitor' => $visitorId]); // the registrant's personal space
    }
    return redirect()->route('siarc.register', ['lang' => webLang($r)])
        ->with($eid ? 'siarc_registered' : 'siarc_error', true)
        ->with('siarc_badge', $badge);
})->name('siarc.register.store')->middleware('throttle:10,1');

Route::get('/siarc/ateliers/{id}/inscription', function (Request $r, $id) {
    $lang = webLang($r); $fr = $lang === 'fr';
    $s = DB::table('programme_sessions')->where('id', $id)->first();
    abort_if(! $s, 404);
    return view('pages.siarc.register', ['lang' => $lang, 'workshop' => $s]);
})->name('siarc.workshop.register');

Route::post('/siarc/ateliers/{id}/inscription', function (Request $r, $id) {
    abort_if(! DB::table('programme_sessions')->where('id', $id)->exists(), 404);
    $data = $r->validate(['name' => 'required|string|max:160', 'email' => 'nullable|email|max:190']);
    DB::table('session_registrations')->insert($data + ['session_id' => $id, 'registered_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    return redirect()->route('siarc.workshop.register', ['id' => $id, 'lang' => webLang($r)])->with('siarc_registered', true);
})->name('siarc.workshop.register.store')->middleware('throttle:10,1');

Route::get('/tableau-de-bord/siarc', function (Request $r) {
    $lang = webLang($r); $fr = $lang === 'fr'; $eid = siarcEvent()?->id ?? 0;
    $mine = null;
    if ($vid = session('siarc_visitor')) {
        $mine = DB::table('visitors')->where('id', $vid)->first();
    }
    if (! $mine) {
        if (! session('siac_user')) {
            // SIARC-only visitors sign in with the email + badge code from their registration.
            return view('pages.siarc.visitor-access', ['lang' => $lang]);
        }
        $u = webUser();
        $mine = ($u && ! empty($u->email))
            ? DB::table('visitors')->where('event_id', $eid)->where('email', $u->email)->first()
            : null;
    }
    return view('pages.siarc.portal', [
        'lang' => $lang, 'sNavActive' => 'siarc', 'sCrumb' => $fr ? 'Mon espace SIARC' : 'My SIARC', 'sTitle' => $fr ? 'Mon espace SIARC 2026' : 'My SIARC 2026',
        'sIntro' => $fr ? 'Votre badge, vos rendez-vous et vos inscriptions aux ateliers.' : 'Your badge, meetings and workshop registrations.',
        'sVisitor' => $mine,
        'sStats' => [
            ['id-card', '#C97A16', '#FDF3E0', $mine->badge_code ?? ($fr ? 'À générer' : 'To generate'), 'Badge', null],
            ['scan-line', '#157A43', '#E2F3E8', ($mine && $mine->checked_in_at) ? ($fr ? 'Présent' : 'Checked-in') : ($fr ? 'Attendu' : 'Expected'), $fr ? 'Accès' : 'Access', null],
        ],
        'sLinks' => [
            ['label' => $fr ? 'Programme' : 'Programme', 'href' => route('siarc.programme', ['lang' => $lang]), 'icon' => 'calendar-days'],
            ['label' => $fr ? 'Exposants' : 'Exhibitors', 'href' => route('siarc.exhibitors', ['lang' => $lang]), 'icon' => 'store'],
            ['label' => $fr ? 'S\'inscrire' : 'Register', 'href' => route('siarc.register', ['lang' => $lang]), 'icon' => 'ticket'],
        ],
    ]);
})->name('siarc.visitor.dashboard');

// ───────────────────────── ADMIN — Platform mode ────────────────────────────

Route::get('/tableau-de-bord/admin/siarc/mode', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $lang = webLang($r); $fr = $lang === 'fr';
    return view('pages.siarc.admin', [
        'lang' => $lang, 'sActive' => 'siarc', 'sTitle' => $fr ? 'Mode de la plateforme' : 'Platform mode',
    ]);
})->name('siarc.admin.mode');

Route::post('/tableau-de-bord/admin/siarc/mode', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    siarcSetStandalone($r->boolean('standalone'));
    return redirect()->route('siarc.admin.mode', ['lang' => webLang($r)])->with('siarc_mode_saved', true);
})->name('siarc.admin.mode.set')->middleware('throttle:20,1');

// ─────────────────── ADMIN — Accréditation (badge system) ───────────────────
// Eight approved designs (Badge Templates / Types de Badges / Badge Preview /
// Print Queue / Bulk Printing / QR Code Generation / RFID Support / RFID Card
// Detail) rendered pixel-faithful through the pages.siarc.accred scaffold.

foreach ([
    'templates'  => ['badge-templates',    'Badge Templates'],
    'types'      => ['types-de-badges',    'Types de Badges'],
    'preview'    => ['apercu-badge',       'Badge Preview'],
    'queue'      => ['file-impression',    'Print Queue'],
    'bulk'       => ['impression-lot',     'Bulk Printing'],
    'qr'         => ['generation-qr',      'QR Code Generation'],
    'rfid'       => ['rfid',               'RFID Support'],
] as $key => [$slug, $title]) {
    Route::get('/tableau-de-bord/admin/siarc/accreditation/'.$slug, function (Request $r) use ($title) {
        if ($x = requireAdmin($r)) return $x;
        return view('pages.siarc.accred', ['lang' => webLang($r), 'sTitle' => $title]);
    })->name('siarc.admin.accred.'.$key);
}

Route::get('/tableau-de-bord/admin/siarc/accreditation/rfid/{uid}', function (Request $r, $uid) {
    if ($x = requireAdmin($r)) return $x;
    return view('pages.siarc.accred', ['lang' => webLang($r), 'sTitle' => 'RFID Card Detail', 'rfidUid' => $uid]);
})->name('siarc.admin.accred.rfid.card');

// ───────────────── Printable badges (visitor / exhibitor / VIP / speaker) ────
Route::get('/siarc/badge/{code}', function (Request $r, $code) {
    $lang = webLang($r); $eid = siarcEvent()?->id ?? 0;
    $v = DB::table('visitors')->where('badge_code', $code)->first();
    $x = $v ? null : DB::table('event_exhibitors as ee')
        ->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->leftJoin('pavilions as p', 'p.id', '=', 'ee.pavilion_id')
        ->where('ee.badge_code', $code)
        ->first(['ee.*', 'b.name_fr as company', 'b.slug as company_slug', 'p.name_fr as pavilion']);
    $s = ($v || $x) ? null : DB::table('speakers')->where('id', str_replace('SPK-', '', $code))->first();
    abort_if(! $v && ! $x && ! $s, 404);
    $type = $v ? (in_array($v->type, ['vip']) ? 'vip' : 'visitor') : ($x ? 'exhibitor' : 'speaker');
    $session = $s ? DB::table('programme_sessions as ps')
        ->leftJoin('session_speaker as ss', 'ss.session_id', '=', 'ps.id')
        ->where(fn ($q) => $q->where('ss.speaker_id', $s->id)->orWhere('ps.speaker_id', $s->id))
        ->first(['ps.title_fr', 'ps.room as venue_fr', 'ps.starts_at']) : null;
    return view('pages.siarc.badge', [
        'lang' => $lang, 'type' => $type, 'v' => $v, 'x' => $x, 's' => $s, 'session' => $session,
    ]);
})->name('siarc.badge.print');

// ── Accreditation operations (readers / gates / rules / monitoring / badge
//    lifecycle / sync) — spec-driven pages, see config/siarc_accred_ops.php.
foreach ([
    'readers'     => 'lecteurs',
    'reader'      => 'lecteurs/detail',
    'gates'       => 'portes',
    'gate'        => 'portes/detail',
    'rules'       => 'regles-acces',
    'rule'        => 'regles-acces/detail',
    'monitor'     => 'monitoring-acces',
    'failures'    => 'echecs-acces',
    'override'    => 'override-manuel',
    'lost'        => 'badges-perdus',
    'activation'  => 'activation-badges',
    'replace'     => 'remplacement-badge',
    'revocations' => 'revocations',
    'health'      => 'sante-lecteurs',
    'sync'        => 'synchronisation',
] as $key => $slug) {
    Route::get('/tableau-de-bord/admin/siarc/accreditation/'.$slug, function (Request $r) use ($key) {
        if ($x = requireAdmin($r)) return $x;
        $title = (config('siarc_accred_ops')['siarc.admin.accred.'.$key]['title'] ?? 'Accréditation');
        return view('pages.siarc.accred', ['lang' => webLang($r), 'sTitle' => $title]);
    })->name('siarc.admin.accred.'.$key);
}

// ── Accreditation: RFID write flow, reprint history, QR scanner ─────────────
Route::get('/tableau-de-bord/admin/siarc/accreditation/rfid-ecriture', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    return view('pages.siarc.accred', ['lang' => webLang($r), 'sTitle' => 'RFID Write Data Flow']);
})->name('siarc.admin.accred.rfid.write');

Route::get('/tableau-de-bord/admin/siarc/accreditation/reimpressions', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    return view('pages.siarc.accred', ['lang' => webLang($r), 'sTitle' => 'Reprint History']);
})->name('siarc.admin.accred.reprints');

Route::get('/tableau-de-bord/admin/siarc/accreditation/qr-scanner', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $state = $r->query('etat', 'camera');
    $scan = null;
    // A real code (manual entry or camera hand-off) resolves against the badge tables.
    if ($code = trim((string) $r->query('code', ''))) {
        $holder = siarcResolveBadge($code);
        $scan = ['input' => $code, 'holder' => $holder];
        if (! in_array($state, ['validation'])) {
            $state = $holder && ! $holder['blocked'] ? ($r->query('etat') === 'validation' ? 'validation' : 'granted') : 'refused';
        }
        if ($state === 'validation' && (! $holder || $holder['blocked'])) $state = 'refused';
    }
    return view('pages.siarc.accred', ['lang' => webLang($r), 'sTitle' => 'QR Scanner', 'qrState' => $state, 'qrScan' => $scan, 'qrCheckin' => (bool) $r->query('checkin')]);
})->name('siarc.admin.accred.qrscanner');

// ─────────────────── ADMIN — Security Operations module ─────────────────────
// Ten approved designs (Overview / Crowd Alerts / Incidents / Lost Persons +
// detail / Medical Emergency + detail / Fire Alerts + detail / Police Request
// Details) rendered through the pages.siarc.secops scaffold.
foreach ([
    'overview'     => ['apercu',            'Security Operations',       null],
    'crowd'        => ['foule',             'Crowd Alerts',              'Crowd Alerts'],
    'incidents'    => ['incidents',         'Incidents',                 'Incidents'],
    'lost'         => ['personnes-perdues', 'Lost Persons',              'Lost Persons'],
    'lost.case'    => ['personnes-perdues/dossier', 'Lost Person Detail','Lost Persons » Lost Person Detail'],
    'medical'      => ['urgences-medicales','Medical Emergency',         'Medical Emergency'],
    'medical.case' => ['urgences-medicales/dossier','Medical Emergency Detail','Medical Emergency » Emergency Detail'],
    'fire'         => ['incendies',         'Fire Alerts',               'Fire Alerts'],
    'fire.case'    => ['incendies/dossier', 'Fire Alert Detail',         'Fire Alerts » Fire Alert Detail'],
    'police.case'  => ['police/dossier',    'Police Request Details',    'Police Requests » Request Details'],
] as $key => [$slug, $title, $crumb]) {
    Route::get('/tableau-de-bord/admin/siarc/securite/'.$slug, function (Request $r) use ($title, $crumb) {
        if ($x = requireAdmin($r)) return $x;
        return view('pages.siarc.secops', ['lang' => webLang($r), 'sTitle' => $title, 'sCrumb' => $crumb]);
    })->name('siarc.admin.secops.'.$key);
}

// ── Security operations kiosks (tablet devices at the gates) ─────────────────
Route::get('/tableau-de-bord/admin/siarc/securite/kiosque-scanner', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    return view('pages.siarc.kiosk-staff-scanner', ['lang' => webLang($r)]);
})->name('siarc.admin.secops.kiosk.scanner');

Route::get('/tableau-de-bord/admin/siarc/securite/kiosque-checkin', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    return view('pages.siarc.kiosk-visitor-checkin', ['lang' => webLang($r)]);
})->name('siarc.admin.secops.kiosk.checkin');

// ── PUBLIC — Pavilion profile (the explorer's ten design pavilions) ──────────
Route::get('/siarc/pavillons/{slug}', function (Request $r, $slug) {
    $lang = webLang($r); $eid = siarcEvent()?->id ?? 0;
    $pavs = [
        'cameroun'         => ['img'=>'pav-cameroun.png',        'flag'=>'🇨🇲','badge'=>'Pays',       'name'=>'Pavillon Cameroun',            'stands'=>'48','exhib'=>'126','desc'=>"Découvrez le meilleur de l'artisanat camerounais dans un espace dédié à la diversité culturelle nationale."],
        'maroc'            => ['img'=>'pav-maroc.png',           'flag'=>'🇲🇦','badge'=>'Pays',       'name'=>'Pavillon Maroc',               'stands'=>'32','exhib'=>'84', 'desc'=>"L'excellence marocaine à travers l'artisanat traditionnel, les tapis, la céramique et bien plus."],
        'senegal'          => ['img'=>'pav-senegal.png',         'flag'=>'🇸🇳','badge'=>'Pays',       'name'=>'Pavillon Sénégal',             'stands'=>'28','exhib'=>'71', 'desc'=>"L'art sénégalais à l'honneur : textiles, bijoux, cuir, sculpture et design contemporain."],
        'innovation'       => ['img'=>'pav-innovation.png',      'flag'=>null,'badge'=>'Thématique', 'name'=>'Pavillon Innovation & Design', 'stands'=>'20','exhib'=>'53', 'desc'=>"Un espace dédié aux artisans innovants et aux créations design tournées vers l'avenir."],
        'cotedivoire'      => ['img'=>'pav-cotedivoire.png',     'flag'=>'🇨🇮','badge'=>'Pays',       'name'=>"Pavillon Côte d'Ivoire",       'stands'=>'24','exhib'=>'62', 'desc'=>"La richesse artisanale ivoirienne : tissage, sculpture sur bois et créations contemporaines."],
        'tunisie'          => ['img'=>'pav-tunisie.png',         'flag'=>'🇹🇳','badge'=>'Pays',       'name'=>'Pavillon Tunisie',             'stands'=>'18','exhib'=>'45', 'desc'=>"L'artisanat tunisien entre héritage méditerranéen et savoir-faire millénaire."],
        'artisanat-monde'  => ['img'=>'pav-artisanat-monde.png', 'flag'=>null,'badge'=>'Thématique', 'name'=>'Pavillon Artisanat du Monde',  'stands'=>'30','exhib'=>'88', 'desc'=>"Un tour du monde de l'artisanat : créations et savoir-faire des cinq continents."],
        'jeunes'           => ['img'=>'pav-jeunes.png',          'flag'=>null,'badge'=>'Thématique', 'name'=>'Pavillon Jeunes Artisans',     'stands'=>'16','exhib'=>'48', 'desc'=>"La nouvelle génération d'artisans présente ses créations et son regard sur le métier."],
        'afrique-centrale' => ['img'=>'pav-afrique-centrale.png','flag'=>'🌍','badge'=>'Régional',   'name'=>'Pavillon Afrique Centrale',    'stands'=>'22','exhib'=>'55', 'desc'=>"Les artisans d'Afrique Centrale réunis autour d'un patrimoine commun et vivant."],
        'diaspora'         => ['img'=>'pav-diaspora.png',        'flag'=>null,'badge'=>'Régional',   'name'=>'Pavillon Diaspora Africaine',  'stands'=>'14','exhib'=>'33', 'desc'=>"La créativité de la diaspora africaine, entre racines et influences du monde entier."],
    ];
    abort_if(! isset($pavs[$slug]), 404);
    $p = $pavs[$slug];
    // Back the profile with a real DB pavilion when one matches by slug/name.
    $p['dbId'] = DB::table('pavilions')->where('event_id', $eid)
        ->where(fn ($q) => $q->where('slug', $slug)->orWhere('name_fr', 'like', '%'.str_replace('Pavillon ', '', $p['name']).'%'))
        ->value('id');
    $p['others'] = collect($pavs)->except($slug)->take(4)
        ->map(fn ($o, $s) => [$s, $o['name'], $o['img']])->values()->all();
    return view('pages.siarc.public', ['lang' => $lang, 'sTitle' => $p['name'], 'pavPublic' => $p]);
})->name('siarc.pavilion');

// ═══════════════ End-to-end flows: verify / scan / check-in / lifecycle ══════

// Resolve a badge code or QR token to its holder (visitor, exhibitor or speaker).
if (! function_exists('siarcResolveBadge')) {
    function siarcResolveBadge(string $code): ?array
    {
        $v = DB::table('visitors')->where('badge_code', $code)->orWhere('qr_token', $code)->first();
        if ($v) return ['kind' => 'visitor', 'row' => $v, 'name' => trim($v->first_name.' '.$v->last_name),
            'type' => $v->type === 'vip' ? 'VIP' : ucfirst($v->type), 'code' => $v->badge_code,
            'blocked' => in_array($v->status, ['blocked', 'cancelled']), 'checked_in' => (bool) $v->checked_in_at];
        $x = DB::table('event_exhibitors as ee')->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
            ->where('ee.badge_code', $code)->orWhere('ee.qr_token', $code)
            ->first(['ee.*', 'b.name_fr as company']);
        if ($x) return ['kind' => 'exhibitor', 'row' => $x, 'name' => $x->company ?? 'Exposant',
            'type' => 'Exposant', 'code' => $x->badge_code,
            'blocked' => in_array($x->status, ['blocked', 'cancelled']), 'checked_in' => (bool) $x->checked_in_at];
        if (preg_match('/^SPK-0*(\d+)$/i', $code, $m)) {
            $s = DB::table('speakers')->where('id', $m[1])->first();
            if ($s) return ['kind' => 'speaker', 'row' => $s, 'name' => $s->name,
                'type' => 'Intervenant', 'code' => strtoupper($code), 'blocked' => false, 'checked_in' => false];
        }
        return null;
    }
}

// ── Public badge verification (target of every printed badge QR) ────────────
Route::get('/siarc/verify/{code}', function (Request $r, $code) {
    $lang = webLang($r);
    $holder = siarcResolveBadge($code);
    $state = ! $holder ? 'unknown' : ($holder['blocked'] ? 'blocked' : 'valid');
    return view('pages.siarc.verify', ['lang' => $lang, 'code' => $code, 'holder' => $holder, 'state' => $state]);
})->name('siarc.verify');

// ── Admin scanner check-in (records the passage) ────────────────────────────
Route::post('/tableau-de-bord/admin/siarc/accreditation/qr-scanner/checkin', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $data = $r->validate(['code' => 'required|string|max:120']);
    $holder = siarcResolveBadge($data['code']);
    if (! $holder || $holder['blocked']) {
        return redirect()->route('siarc.admin.accred.qrscanner', ['lang' => webLang($r), 'etat' => 'refused', 'code' => $data['code']]);
    }
    $eid = siarcEvent()?->id ?? 0;
    $table = $holder['kind'] === 'exhibitor' ? 'event_exhibitors' : 'visitors';
    if (in_array($holder['kind'], ['visitor', 'exhibitor'])) {
        DB::table($table)->where('id', $holder['row']->id)->update(['checked_in_at' => now(), 'updated_at' => now()]);
        DB::table('check_ins')->insert([
            'event_id' => $eid, 'subject_type' => $holder['kind'], 'subject_id' => $holder['row']->id,
            'gate' => 'Porte A - Entrée Principale', 'scanned_by' => session('siac_user')['name'] ?? 'Scanner',
            'scanned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    return redirect()->route('siarc.admin.accred.qrscanner', ['lang' => webLang($r), 'etat' => 'granted', 'code' => $data['code'], 'checkin' => 1]);
})->name('siarc.admin.accred.qrscanner.checkin')->middleware('throttle:60,1');

// ── Badge lifecycle: block / unblock (lost & blocked badge management) ──────
Route::post('/tableau-de-bord/admin/siarc/accreditation/badges/{code}/statut', function (Request $r, $code) {
    if ($x = requireAdmin($r)) return $x;
    $holder = siarcResolveBadge($code);
    abort_if(! $holder || ! in_array($holder['kind'], ['visitor', 'exhibitor']), 404);
    $table = $holder['kind'] === 'exhibitor' ? 'event_exhibitors' : 'visitors';
    $new = $holder['blocked'] ? 'registered' : 'cancelled'; // schema enum: registered / checked_in / cancelled
    DB::table($table)->where('id', $holder['row']->id)->update(['status' => $new, 'updated_at' => now()]);
    return redirect()->route('siarc.admin.accred.lost', ['lang' => webLang($r)])
        ->with('siarc_badge_status', [$holder['code'], $new]);
})->name('siarc.admin.accred.badge.status')->middleware('throttle:30,1');

// ── Exhibitor gate check-in (portal device) ─────────────────────────────────
Route::post('/tableau-de-bord/siarc/exposant-checkin', function (Request $r) {
    if ($x = requireAdmin($r)) return $x;
    $data = $r->validate(['code' => 'required|string|max:120']);
    $holder = siarcResolveBadge($data['code']);
    $ok = $holder && $holder['kind'] === 'exhibitor' && ! $holder['blocked'];
    if ($ok) {
        DB::table('event_exhibitors')->where('id', $holder['row']->id)->update(['checked_in_at' => now(), 'updated_at' => now()]);
        DB::table('check_ins')->insert([
            'event_id' => siarcEvent()?->id ?? 0, 'subject_type' => 'exhibitor', 'subject_id' => $holder['row']->id,
            'gate' => 'Accès Exposants', 'scanned_by' => session('siac_user')['name'] ?? 'Portail',
            'scanned_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    return redirect()->route('siarc.mobile.exhibitor-checkin', ['lang' => webLang($r)])
        ->with($ok ? 'siarc_checkin_ok' : 'siarc_checkin_ko', $holder['name'] ?? $data['code']);
})->name('siarc.mobile.exhibitor-checkin.store')->middleware('throttle:60,1');

// ── Visitor B2B meeting request (public form on the visitor dashboard) ──────
Route::post('/siarc/b2b/demande', function (Request $r) {
    $data = $r->validate([
        'email' => 'required|email|max:190',
        'exhibitor_id' => 'required|integer|exists:event_exhibitors,id',
        'message' => 'nullable|string|max:500',
    ]);
    $eid = siarcEvent()?->id ?? 0;
    $visitorId = DB::table('visitors')->where('event_id', $eid)->where('email', $data['email'])->value('id');
    if (! $visitorId) {
        return redirect()->route('siarc.visitor.dashboard', ['lang' => webLang($r)])
            ->with('siarc_b2b_ko', true)->withInput();
    }
    DB::table('b2b_meetings')->insert([
        'event_id' => $eid, 'requester_visitor_id' => $visitorId, 'host_exhibitor_id' => $data['exhibitor_id'],
        'scheduled_at' => now()->addDay()->setTime(10, 0), 'duration_min' => 30,
        'location' => 'Espace B2B — Musée National', 'status' => 'requested',
        'message' => $data['message'] ?? null, 'created_at' => now(), 'updated_at' => now(),
    ]);
    return redirect()->route('siarc.visitor.dashboard', ['lang' => webLang($r)])->with('siarc_b2b_ok', true);
})->name('siarc.b2b.request')->middleware('throttle:10,1');

// ── SIARC visitor access: email + badge code open the personal space ─────────
Route::post('/tableau-de-bord/siarc/acces', function (Request $r) {
    $data = $r->validate(['email' => 'required|email', 'badge_code' => 'required|string|max:60']);
    $v = DB::table('visitors')->where('email', strtolower(trim($data['email'])))
        ->where('badge_code', trim($data['badge_code']))->first();
    if (! $v) {
        return redirect()->route('siarc.visitor.dashboard', ['lang' => webLang($r)])
            ->with('siarc_access_ko', true)->withInput();
    }
    session(['siarc_visitor' => $v->id]);
    return redirect()->route('siarc.visitor.dashboard', ['lang' => webLang($r)]);
})->name('siarc.visitor.access')->middleware('throttle:15,1');

// ── SIARC demo logins (presentation): one click opens the matching space ────
Route::post('/tableau-de-bord/siarc/acces-demo/{key}', function (Request $r, string $key) {
    abort_unless(config('app.demo_login', true), 404);
    if ($key === 'admin') {
        $u = DB::table('users')->whereNull('deleted_at')->where('email', 'admin@artisanatcameroun.cm')->first();
        abort_if(! $u, 404);
        establishSiacSession($u, $r);
        return redirect()->route('siarc.admin.dashboard', ['lang' => webLang($r)]);
    }
    if ($key === 'visitor') {
        $eid = siarcEvent()?->id ?? 0;
        $v = DB::table('visitors')->where('event_id', $eid)->where('email', 'visiteur.demo@siarc2026.cm')->first();
        if (! $v) {
            $id = DB::table('visitors')->insertGetId([
                'event_id' => $eid, 'first_name' => 'Visiteur', 'last_name' => 'Démo',
                'email' => 'visiteur.demo@siarc2026.cm', 'type' => 'visitor', 'status' => 'registered',
                'badge_code' => 'SIARC-VIS-DEMO', 'qr_token' => \Illuminate\Support\Str::random(40),
                'registered_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        } else {
            $id = $v->id;
        }
        session(['siarc_visitor' => $id]);
        return redirect()->route('siarc.visitor.dashboard', ['lang' => webLang($r)]);
    }
    abort(404);
})->name('siarc.demo.login')->middleware('throttle:30,1');
