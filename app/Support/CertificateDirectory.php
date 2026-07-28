<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * One lookup for every certificate the platform issues.
 *
 * The problem this exists to solve is small to describe and serious in effect.
 * Four registers issue numbered documents, and every one of them prints a
 * verification address on the page. Only one of those addresses used to resolve.
 * A holder scanning the code on an ownership transfer, an artisan verification
 * or an export certificate reached a page that told them no such certificate
 * existed — which is not merely unhelpful, it is the exact answer a forgery
 * should get. A verification address that cannot answer is worse than no
 * verification address at all, because it teaches the reader that the check
 * means nothing.
 *
 * Two design rules follow from that, and both cost something.
 *
 * The first is that type detection is by structure *then lookup*, never by
 * prefix alone. The shape of a reference only orders the candidates; a type is
 * claimed only once a row with that exact reference has been found. A number
 * mistyped by one digit therefore comes back `notfound`, rather than being
 * coerced into the register its prefix resembles and answered with somebody
 * else's real document. The cost is up to four indexed lookups on a miss, which
 * is the right price for never showing the wrong certificate.
 *
 * The second is that the PIN is compared here, with hash_equals, rather than
 * left to each register's own string comparison. The registers each do a
 * `strtoupper() !==`, which short-circuits on the first differing character;
 * over enough attempts that is a way to learn a PIN a character at a time. This
 * class checks the PIN itself and only then delegates with the PIN already
 * satisfied. Note the honest limit: constant-time comparison removes the leak
 * from the comparison, not from the lookup — a reference that exists costs a
 * row read that one which does not, does not. The `notfound` path performs a
 * matching comparison so that a wrong PIN and an unknown number at least do not
 * differ by a whole string compare, and rate limiting on the route does the
 * rest. Claiming more than that would be the kind of overstatement the rest of
 * this codebase refuses.
 *
 * Nothing here reimplements verification. Each type's own class remains the one
 * authority on what its status means; this is a switchboard, and its output is
 * a uniform shape the verification page can render without knowing which
 * register answered.
 */
class CertificateDirectory
{
    /** Which table and columns each type answers on. */
    private const REGISTERS = [
        'coa' => ['table' => 'product_certificates',  'columns' => ['certificate_no', 'uuid']],
        'otc' => ['table' => 'ownership_transfers',   'columns' => ['certificate_no', 'uuid']],
        'avc' => ['table' => 'artisan_verifications', 'columns' => ['certificate_no', 'uuid']],
        // The GECN is the consignment's other printed identity, and a customs
        // officer reading off the crate paperwork has that one, not the
        // certificate number.
        'eac' => ['table' => 'export_consignments',   'columns' => ['certificate_no', 'gecn', 'uuid']],
    ];

    /**
     * Shapes seen in circulation, used only to order the candidate registers.
     * A shape that matches is a hint, never a decision.
     */
    private const SHAPES = [
        'coa' => '/^AHC-COA-\d{4}-\d{4,}(-V\d+)?$/i',
        'otc' => '/^AH237-OTC-[A-Z]{2}-\d{4}-\d{4,}(-V\d+)?$/i',
        'avc' => '/^AH237-AVC-[A-Z]{2}-\d{4}-\d{4,}(-V\d+)?$/i',
        'eac' => '/^AH237-(EAC|GECN)-[A-Z]{2}-\d{4}-\d{4,}(-V\d+)?$/i',
    ];

    /** Plain names, because "OTC" on a verification page tells a buyer nothing. */
    public const NAMES = [
        'coa' => ['en' => 'Certificate of Authenticity',       'fr' => "Certificat d'authenticité"],
        'otc' => ['en' => 'Ownership Transfer Certificate',    'fr' => 'Certificat de transfert de propriété'],
        'avc' => ['en' => 'Artisan Verification Certificate',  'fr' => "Certificat de vérification d'artisan"],
        'eac' => ['en' => 'Export Authenticity Certificate',   'fr' => "Certificat d'authenticité à l'export"],
    ];

    /**
     * Resolves any certificate reference — a number or a UUID — to one shape.
     *
     * @return array{type:string, status:string, certificate:?object,
     *               subject:array{label:?string,url:?string},
     *               signature:array{state:string,kid:?string},
     *               issued_at:?string, expires_at:?string,
     *               verification_count:?int, document_url:?string}
     */
    public static function resolve(string $reference, ?string $pin = null): array
    {
        $reference = trim($reference);

        if ($reference === '' || mb_strlen($reference) > 64) {
            return self::notFound($pin);
        }

        foreach (self::candidates($reference) as $type) {
            $row = self::lookup($type, $reference);

            if (! $row) {
                continue;
            }

            // The reference is real and belongs to this register. From here the
            // answer is about this certificate, never about another type.
            if ($pin !== null && ! self::pinMatches($row, $pin)) {
                return self::miss('pin_mismatch', $type);
            }

            return match ($type) {
                'coa' => self::resolveCoa($row),
                'otc' => self::resolveOtc($row),
                'avc' => self::resolveAvc($row),
                'eac' => self::resolveEac($row),
            };
        }

        return self::notFound($pin);
    }

    /** The plain name of a type in the reader's language. */
    public static function name(string $type, string $lang = 'fr'): ?string
    {
        return self::NAMES[$type][$lang] ?? self::NAMES[$type]['en'] ?? null;
    }

    /* ─────────────────────────── Type detection ────────────────────────── */

    /**
     * Candidate registers, best guess first, then everything else.
     *
     * The fallback tail is what makes this structure-then-lookup rather than
     * prefix matching: a number whose shape we did not anticipate — an older
     * sequence width, a version suffix nobody documented — still resolves,
     * because the decision is made by a row existing and not by a regex.
     *
     * @return list<string>
     */
    private static function candidates(string $reference): array
    {
        $types = array_keys(self::REGISTERS);

        // A UUID says nothing about which register minted it, so all four are
        // equally likely and the order is arbitrary.
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $reference)) {
            return $types;
        }

        $preferred = [];

        foreach (self::SHAPES as $type => $shape) {
            if (preg_match($shape, $reference)) {
                $preferred[] = $type;
            }
        }

        return array_merge($preferred, array_values(array_diff($types, $preferred)));
    }

    private static function lookup(string $type, string $reference): ?object
    {
        $register = self::REGISTERS[$type];

        $query = DB::table($register['table']);

        // Grouped so the columns are OR'd against each other and nothing else;
        // an ungrouped orWhere here would be a way to widen a later filter.
        $query->where(function ($q) use ($register, $reference) {
            foreach ($register['columns'] as $column) {
                $q->orWhere($column, $reference);
            }
        });

        return $query->first() ?: null;
    }

    /* ──────────────────────────────── PIN ──────────────────────────────── */

    /**
     * Constant-time PIN check.
     *
     * Case and surrounding space are normalised first because the PIN gets read
     * aloud and copied off a printed sheet; the comparison itself is
     * hash_equals, which takes the same time whether the first character is
     * wrong or only the last.
     *
     * A row with no PIN on file cannot confirm one. That returns false rather
     * than true: a certificate issued before PINs existed must not accept any
     * PIN offered, and must not accept an empty one either.
     */
    private static function pinMatches(object $row, string $pin): bool
    {
        $stored = strtoupper(trim((string) ($row->verification_pin ?? '')));
        $given  = strtoupper(trim($pin));

        if ($stored === '') {
            // Still compared, so that "no PIN on file" and "wrong PIN" cost the
            // same. The comparison cannot succeed.
            hash_equals(str_repeat("\0", max(1, strlen($given))), $given);

            return false;
        }

        return hash_equals($stored, $given);
    }

    /* ──────────────────────────── Per register ─────────────────────────── */

    private static function resolveCoa(object $row): array
    {
        // Delegated with a null PIN because this class has already satisfied it;
        // passing it again would run the register's short-circuiting compare and
        // give back the leak we just removed.
        $result = ProductCertificate::verify($row->certificate_no, null);

        $cert    = $result['certificate'] ?? null;
        $product = $result['product'] ?? null;

        if (! $cert) {
            return self::miss('notfound', 'coa');
        }

        return self::shape('coa', $result['status'], $cert, [
            'signature'    => ProductCertificate::signatureState($cert),
            'subject'      => $product
                ? ['label' => $product->name_fr, 'url' => route('products.show', ['slug' => $product->slug])]
                : ['label' => null, 'url' => null],
            'document_url' => $product ? route('product.certificate', ['slug' => $product->slug]) : null,
        ]);
    }

    /**
     * The ownership transfer register has no verify() of its own, so the status
     * is read here from the columns ProvenanceRegistry maintains.
     *
     * Deliberately absent: a recomputation of the content hash. The hash is
     * taken over values that pass through the database on the way back —
     * timestamps most of all — and a re-derivation that disagreed with storage
     * over a date format would report a perfectly good certificate as
     * superseded. The register already marks a transfer superseded when a later
     * one is recorded, which is the fact that actually matters to a holder, and
     * the Ed25519 signature reported beside it is what detects a row that has
     * been altered. A false "superseded" is a real cost; this avoids it.
     */
    private static function resolveOtc(object $row): array
    {
        self::countVerification('ownership_transfers', $row);

        $status = match (true) {
            in_array($row->status, ['revoked', 'cancelled'], true) => 'revoked',
            $row->status === 'superseded'                          => 'superseded',
            default                                                => 'valid',
        };

        // A later transfer of the same piece means this document describes an
        // ownership the register has moved past, whatever its own column says.
        if ($status === 'valid' && DB::table('ownership_transfers')
                ->where('product_id', $row->product_id)
                ->where('id', '>', $row->id)
                ->exists()) {
            $status = 'superseded';
        }

        $product = Product::find($row->product_id);

        return self::shape('otc', $status, $row, [
            'signature'    => self::caState('otc', $row),
            'subject'      => $product
                ? ['label' => $product->name_fr, 'url' => route('products.show', ['slug' => $product->slug])]
                : ['label' => null, 'url' => null],
            'document_url' => route('ownership.transfer.certificate', ['ref' => $row->certificate_no]),
        ]);
    }

    private static function resolveAvc(object $row): array
    {
        $result = ArtisanVerification::verify($row->certificate_no, null);

        $cert     = $result['certificate'] ?? null;
        $business = $result['business'] ?? null;

        if (! $cert) {
            return self::miss('notfound', 'avc');
        }

        return self::shape('avc', self::withExpiry($result['status'], $cert), $cert, [
            'signature'    => ArtisanVerification::signatureState($cert),
            'subject'      => $business
                ? ['label' => $business->name_fr, 'url' => route('businesses.show', ['slug' => $business->slug])]
                : ['label' => null, 'url' => null],
            'document_url' => $business
                ? route('artisan.verification.certificate', ['slug' => $business->slug])
                : null,
        ]);
    }

    private static function resolveEac(object $row): array
    {
        $result = ExportRegister::verify($row->certificate_no, null);

        $c = $result['consignment'] ?? null;

        if (! $c) {
            return self::miss('notfound', 'eac');
        }

        $product = Product::find($c->product_id);

        return self::shape('eac', self::withExpiry($result['status'], $c), $c, [
            'signature'    => ExportRegister::signatureState($c),
            'subject'      => $product
                ? ['label' => $product->name_fr, 'url' => route('products.show', ['slug' => $product->slug])]
                : ['label' => null, 'url' => null],
            'document_url' => route('export.certificate', ['ref' => $c->certificate_no]),
        ]);
    }

    /* ────────────────────────────── Internals ──────────────────────────── */

    /**
     * Expiry outranks everything except revocation.
     *
     * An export certificate whose window has closed is not valid because a
     * status column still reads "approved" — the document says on its face when
     * it stops being good for, and the register must agree with the document.
     * Revocation still wins: a certificate withdrawn before it expired was
     * withdrawn, and that is the more serious thing to tell the holder.
     */
    private static function withExpiry(string $status, object $row): string
    {
        if ($status === 'revoked' || $status === 'pin_mismatch' || $status === 'notfound') {
            return $status;
        }

        $expires = $row->expires_at ?? null;

        return $expires && Carbon::parse($expires)->isPast() ? 'expired' : $status;
    }

    /** Ed25519 state for a register with no signatureState() of its own. */
    private static function caState(string $type, object $row): array
    {
        if (! ($row->ca_signature ?? null)) {
            return ['state' => 'unsigned', 'kid' => null];
        }

        $ok = CertificationAuthority::verifyCertificate(
            $type,
            $row->certificate_no,
            $row->content_hash,
            $row->issued_at ? Carbon::parse($row->issued_at)->toIso8601String() : null,
            $row->ca_signature
        );

        return ['state' => $ok ? 'valid' : 'invalid', 'kid' => $row->ca_kid ?? null];
    }

    /**
     * The counter the other registers keep in their own verify(). Kept here for
     * the transfer register so all four count the same way — a verification
     * count that only some documents accumulate is a misleading statistic on a
     * printed certificate.
     */
    private static function countVerification(string $table, object $row): void
    {
        DB::table($table)->where('id', $row->id)->update([
            'verification_count' => (int) ($row->verification_count ?? 0) + 1,
            'last_verified_at'   => now(),
        ]);

        $row->verification_count = (int) ($row->verification_count ?? 0) + 1;
    }

    private static function shape(string $type, string $status, object $cert, array $extra): array
    {
        // Re-read so the count shown is the one this visit produced, not the one
        // that was on the row before the register incremented it.
        $fresh = DB::table(self::REGISTERS[$type]['table'])->find($cert->id) ?: $cert;

        return array_merge([
            'type'               => $type,
            'status'             => $status,
            'certificate'        => $fresh,
            'subject'            => ['label' => null, 'url' => null],
            'signature'          => ['state' => 'unsigned', 'kid' => null],
            'issued_at'          => $fresh->issued_at ?? null,
            'expires_at'         => $fresh->expires_at ?? null,
            'verification_count' => isset($fresh->verification_count) ? (int) $fresh->verification_count : null,
            'document_url'       => null,
        ], $extra);
    }

    /**
     * An answer that names the type but hands back nothing else.
     *
     * Used for pin_mismatch: the reader is told the number exists and the PIN
     * does not match it — which is what they need in order to check both — and
     * is given none of the certificate's contents, which is the whole point of
     * asking for a PIN.
     */
    private static function miss(string $status, string $type): array
    {
        return [
            'type'               => $type,
            'status'             => $status,
            'certificate'        => null,
            'subject'            => ['label' => null, 'url' => null],
            'signature'          => ['state' => 'unknown', 'kid' => null],
            'issued_at'          => null,
            'expires_at'         => null,
            'verification_count' => null,
            'document_url'       => null,
        ];
    }

    /**
     * No register holds this reference.
     *
     * The type is `unknown` rather than the shape's best guess, because telling
     * a caller "this is an export certificate that does not exist" would confirm
     * the number format to somebody probing for one.
     */
    private static function notFound(?string $pin): array
    {
        if ($pin !== null) {
            // Matched against nothing, purely so the miss path performs the same
            // comparison the hit path does. See the class note on what this does
            // and does not buy.
            hash_equals(str_repeat("\0", max(1, strlen($pin))), $pin);
        }

        return self::miss('notfound', 'unknown');
    }
}
