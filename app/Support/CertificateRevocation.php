<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Withdrawing a certificate, and saying so where anyone can read it.
 *
 * Each of the five registers already had somewhere to record that a document
 * had been withdrawn, and none of them had a way to do it. There was no
 * vocabulary for why, and — the part that actually matters — nowhere a holder
 * could look a number up. Every certificate the platform prints tells its
 * reader to check the live register before relying on the sheet in their hand.
 * If a forged or withdrawn number cannot be found to be withdrawn, that
 * instruction is decoration.
 *
 * Three decisions shape everything below.
 *
 * A revocation is written in one transaction or not at all. It touches three
 * places: the revocation row, the certificate's own status column, and the
 * authority's hash chain. A revocation present in the list but not on the
 * certificate lets the document keep verifying as valid; present on the
 * certificate but not in the list is a withdrawal nobody can discover. Both
 * are worse than the revocation never having been attempted, so they are not
 * reachable states.
 *
 * The public list is built from a fixed set of fields, and the private ones
 * are not in the shape it returns. Not hidden by the template — absent. The
 * reason note may name a complainant, an investigator or a docket; the
 * revoking officer is a named employee. A view that cannot receive them cannot
 * publish them by mistake, and templates are edited by people in a hurry.
 * `fraud` and `forgery`, published beside a number that leads to a named
 * artisan's work, are already about as grave a public statement as this
 * platform makes. Nothing is added to them.
 *
 * Nothing here is ever deleted. A revocation entered in error is reinstated,
 * which is a second recorded event on the same row, because someone who saw
 * the number on the public list is entitled to find out what happened to it.
 * Deleting the row would leave them looking at a certificate that verifies
 * fine and no explanation of what they read last week.
 */
class CertificateRevocation
{
    /**
     * How each register records that one of its documents is withdrawn.
     *
     * They disagree, because they were built at different times: the
     * certificate of authenticity has a revoked_at timestamp, the other four
     * have a status enum. That disagreement is contained here rather than
     * pushed onto callers, who have a number and a reason and no business
     * knowing which shape the register happens to use.
     */
    private const REGISTERS = [
        'coa' => [
            'table'  => 'product_certificates',
            'mode'   => 'timestamp',
            'reason' => 'revoked_reason',
            'live'   => null,
        ],
        'otc' => [
            'table'  => 'ownership_transfers',
            'mode'   => 'status',
            'reason' => 'revoked_reason',
            'live'   => 'active',
        ],
        'avc' => [
            'table'  => 'artisan_verifications',
            'mode'   => 'status',
            'reason' => 'revoked_reason',
            'live'   => 'active',
        ],
        'eac' => [
            'table'  => 'export_consignments',
            'mode'   => 'status',
            // The export register has rejected_reason, which means something
            // else entirely — a consignment refused at review, not a
            // certificate withdrawn after issue. Writing a revocation reason
            // into it would rewrite the consignment's history, so this type
            // keeps its reason only in the revocation row.
            'reason' => null,
            'live'   => 'approved',
        ],
        'wvc' => [
            'table'  => 'workshop_certificates',
            'mode'   => 'status',
            'reason' => 'revoked_reason',
            'live'   => 'active',
        ],
    ];

    /**
     * The whole vocabulary. An enum rather than free text because each of these
     * is published, and a public statement about somebody's work has to come
     * from a fixed list that was reviewed once, not from whatever an operator
     * typed at the time.
     */
    public const REASONS = [
        'fraud'                 => ['fr' => 'Fraude',                          'en' => 'Fraud'],
        'forgery'               => ['fr' => 'Falsification du document',       'en' => 'Forged document'],
        'administrative_error'  => ['fr' => 'Erreur administrative',           'en' => 'Administrative error'],
        'court_order'           => ['fr' => 'Décision de justice',             'en' => 'Court order'],
        'owner_request'         => ['fr' => 'À la demande du titulaire',       'en' => 'At the holder’s request'],
        'security_breach'       => ['fr' => 'Incident de sécurité',            'en' => 'Security incident'],
        'superseded_by_reissue' => ['fr' => 'Remplacé par une réémission',     'en' => 'Superseded by a reissue'],
        'other'                 => ['fr' => 'Autre motif',                     'en' => 'Other reason'],
    ];

    /* ───────────────────────────── Revoking ────────────────────────────── */

    /**
     * Withdraws a certificate: the row, the register and the chain, together.
     *
     * Options: `certificate_no` (read from the register when omitted), `note`
     * — private, never published — `actor`, `is_public`, `published_at`.
     *
     * Revoking an already-revoked certificate returns the existing row rather
     * than raising. An operator repeating the instruction means the same thing
     * the first time and the second; a second row would put the same number on
     * the public list twice and imply two separate withdrawals.
     */
    public static function revoke(string $type, int $id, string $reason, array $opts = []): object
    {
        $register = self::REGISTERS[$type] ?? throw new InvalidArgumentException("Unknown certificate type [{$type}].");

        if (! array_key_exists($reason, self::REASONS)) {
            throw new InvalidArgumentException("Unknown revocation reason [{$reason}].");
        }

        if ($existing = self::rowFor($type, $id)) {
            return $existing;
        }

        $certificate = DB::table($register['table'])->find($id)
            ?? throw new InvalidArgumentException("No {$type} certificate with id {$id}.");

        $number = $opts['certificate_no'] ?? ($certificate->certificate_no ?? null);

        if (! $number) {
            // Without the number the entry cannot be looked up, which is the
            // one thing the public list is for. Better to refuse than to
            // publish an unsearchable line.
            throw new InvalidArgumentException("Cannot revoke {$type} {$id}: it has no certificate number to publish.");
        }

        $now       = now();
        $isPublic  = array_key_exists('is_public', $opts) ? (bool) $opts['is_public'] : true;
        $published = $opts['published_at'] ?? ($isPublic ? $now : null);

        return DB::transaction(function () use ($type, $id, $reason, $opts, $register, $certificate, $number, $now, $isPublic, $published) {
            // updateOrInsert rather than insert, because of the one row that is
            // physically present and not live: a certificate revoked, reinstated,
            // and then revoked again for a real reason. The unique key holds one
            // row per certificate; the reversal and the re-revocation are both
            // in the chain, which is where the history belongs.
            DB::table('certificate_revocations')->updateOrInsert(
                ['certificate_type' => $type, 'certificate_id' => $id],
                [
                    'uuid'               => (string) Str::uuid(),
                    'certificate_no'     => $number,
                    'reason'             => $reason,
                    'reason_note'        => $opts['note'] ?? null,
                    'revoked_at'         => $now,
                    'revoked_by_user_id' => $opts['actor'] ?? null,
                    'is_public'          => $isPublic,
                    'published_at'       => $published,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]
            );

            self::markRegister($type, $register, $certificate, $reason, $now);

            // The prior status, kept so that reinstating an administrative
            // error restores what was actually there rather than a plausible
            // guess. It lives in the chain rather than in a column because it
            // is a fact about an event, and because a value the public list
            // never reads has no business in the revocation row.
            if ($register['mode'] === 'status') {
                CertificationAuthority::appendToChain($type, $id, 'revocation_prior_status', (string) ($certificate->status ?? ''), $opts['actor'] ?? null);
            }

            CertificationAuthority::appendToChain($type, $id, 'revoked', $reason, $opts['actor'] ?? null);

            return self::rowFor($type, $id);
        });
    }

    /** Writes the withdrawal into the register's own columns. */
    private static function markRegister(string $type, array $register, object $certificate, string $reason, Carbon $now): void
    {
        $update = ['updated_at' => $now];

        if ($register['mode'] === 'timestamp') {
            $update['revoked_at'] = $now;
        } else {
            $update['status'] = 'revoked';
        }

        // The plain-words reason, because this column is what the certificate's
        // own document view prints. The private note stays out of it: that view
        // is public too.
        if ($register['reason']) {
            $update[$register['reason']] = self::reasonLabel($reason, 'fr');
        }

        DB::table($register['table'])->where('id', $certificate->id)->update($update);
    }

    /* ───────────────────────────── Reinstating ─────────────────────────── */

    /**
     * Reverses a revocation without erasing it.
     *
     * An administrative error has to be reversible — a certificate withdrawn
     * because somebody keyed the wrong number should not stay dead. But the
     * reversal is itself an event on the record, not a tidying-up: the row
     * stays, the chain gains an entry, and the note explaining the reversal is
     * kept where the revocation's own note is kept, which is out of public
     * view.
     */
    public static function reinstate(int $revocationId, string $note, ?string $actor = null): void
    {
        $revocation = DB::table('certificate_revocations')->find($revocationId)
            ?? throw new InvalidArgumentException("No revocation with id {$revocationId}.");

        $register = self::REGISTERS[$revocation->certificate_type];

        DB::transaction(function () use ($revocation, $register, $note, $actor) {
            $update = ['updated_at' => now()];

            if ($register['mode'] === 'timestamp') {
                $update['revoked_at'] = null;
            } else {
                $update['status'] = self::priorStatus($revocation) ?? $register['live'];
            }

            if ($register['reason']) {
                $update[$register['reason']] = null;
            }

            DB::table($register['table'])->where('id', $revocation->certificate_id)->update($update);

            // Withdrawn from the list, kept in the record. A reader who saw the
            // number published gets a truthful answer about it from the audit
            // trail; they do not keep seeing a certificate advertised as
            // revoked when it is not.
            DB::table('certificate_revocations')->where('id', $revocation->id)->update([
                'is_public'    => false,
                'published_at' => null,
                'updated_at'   => now(),
            ]);

            CertificationAuthority::appendToChain(
                $revocation->certificate_type,
                (int) $revocation->certificate_id,
                'reinstated',
                $note,
                $actor
            );
        });
    }

    /** The status the certificate held before it was revoked, if the chain kept it. */
    private static function priorStatus(object $revocation): ?string
    {
        $note = DB::table('certificate_events')
            ->where('certificate_type', $revocation->certificate_type)
            ->where('certificate_id', $revocation->certificate_id)
            ->where('event', 'revocation_prior_status')
            ->orderByDesc('id')
            ->value('note');

        return $note !== null && $note !== '' ? $note : null;
    }

    /* ────────────────────────────── Reading ────────────────────────────── */

    /** Whether this certificate stands revoked right now. */
    public static function isRevoked(string $type, int $id): bool
    {
        return self::rowFor($type, $id) !== null;
    }

    /**
     * The live revocation for a certificate, or null.
     *
     * "Live" excludes a revocation that has been reinstated, which is why this
     * is not a bare row lookup: the row survives the reversal by design.
     */
    private static function rowFor(string $type, int $id): ?object
    {
        $query = DB::table('certificate_revocations')
            ->where('certificate_type', $type)
            ->where('certificate_id', $id);

        return self::whereStillRevoked($query)->first() ?: null;
    }

    /**
     * Narrows a query to revocations that have not been reversed.
     *
     * The reversal is not a column on the row — the row is deliberately left
     * as it was — so liveness is read from the chain: whichever of `revoked`
     * and `reinstated` was appended last is the certificate's current
     * condition. Ordering by the event id rather than by time is on purpose. A
     * revocation and its reversal minutes apart share a timestamp to the
     * second often enough that a time comparison would sometimes get the order
     * backwards, and getting it backwards means publishing a withdrawal that
     * was taken back.
     */
    private static function whereStillRevoked($query)
    {
        return $query->whereRaw(
            "(select ce.event from certificate_events ce"
            . " where ce.certificate_type = certificate_revocations.certificate_type"
            . " and ce.certificate_id = certificate_revocations.certificate_id"
            . " and ce.event in ('revoked', 'reinstated')"
            . " order by ce.id desc limit 1) = 'revoked'"
        );
    }

    /**
     * Looks a printed number up.
     *
     * Public shape, because this is what a holder reading a certificate calls.
     * It answers about the number, not about the person it belongs to.
     */
    public static function forCertificateNo(string $no): ?object
    {
        $no = trim($no);

        if ($no === '' || mb_strlen($no) > 64) {
            return null;
        }

        $row = DB::table('certificate_revocations')
            ->where('certificate_no', $no)
            ->orderByDesc('revoked_at')
            ->first();

        return $row && self::isRevoked($row->certificate_type, (int) $row->certificate_id)
            ? self::publicShape($row)
            : null;
    }

    /**
     * The published entries, newest first.
     *
     * Filters: `q` (a certificate number, exact or a fragment), `type`,
     * `reason`, `limit`.
     *
     * Everything it returns is safe to render. That is the contract — the four
     * fields below are the whole of it, and the private columns are dropped
     * here rather than left for a template to remember not to print.
     *
     * @return list<object>
     */
    public static function publicList(array $filters = []): array
    {
        $query = DB::table('certificate_revocations')
            ->where('is_public', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        self::whereStillRevoked($query);

        $q = trim((string) ($filters['q'] ?? ''));

        if ($q !== '' && mb_strlen($q) <= 64) {
            // Escaped, so a number pasted with a stray % or _ searches for
            // itself rather than for everything.
            $escaped = addcslashes($q, '%_\\');
            $query->where('certificate_no', 'like', "%{$escaped}%");
        }

        if (isset($filters['type']) && isset(self::REGISTERS[$filters['type']])) {
            $query->where('certificate_type', $filters['type']);
        }

        if (isset($filters['reason']) && isset(self::REASONS[$filters['reason']])) {
            $query->where('reason', $filters['reason']);
        }

        $rows = $query->orderByDesc('revoked_at')->orderByDesc('id')
            ->limit(max(1, min(500, (int) ($filters['limit'] ?? 200))))
            ->get();

        return $rows->map(fn ($row) => self::publicShape($row))->values()->all();
    }

    /**
     * The four public fields, and nothing that could identify a person.
     *
     * No reason_note, no revoking officer, no subject, no link to the artisan.
     * A reader with the number in their hand has everything they need to know
     * that the sheet is no good; a reader without one learns nothing about
     * anybody.
     */
    private static function publicShape(object $row): object
    {
        return (object) [
            'certificate_no'   => $row->certificate_no,
            'certificate_type' => $row->certificate_type,
            'reason'           => $row->reason,
            'revoked_at'       => $row->revoked_at,
        ];
    }

    /* ────────────────────────────── Labels ─────────────────────────────── */

    /** The reason in plain words. "security_breach" tells a buyer nothing. */
    public static function reasonLabel(string $reason, string $lang = 'fr'): string
    {
        return self::REASONS[$reason][$lang] ?? self::REASONS[$reason]['en'] ?? $reason;
    }

    /** The document's plain name, borrowed from the directory that already names four of them. */
    public static function typeLabel(string $type, string $lang = 'fr'): string
    {
        if ($type === 'wvc') {
            return $lang === 'fr' ? "Certificat de vérification d'atelier" : 'Workshop Verification Certificate';
        }

        return CertificateDirectory::name($type, $lang) ?? strtoupper($type);
    }
}
