<?php

namespace App\Support;

use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * The register of concerns raised against a registered piece.
 *
 * This exists so that the reassuring lines on a certificate — not reported
 * stolen, not under investigation, ownership undisputed — are lookups rather
 * than decoration. Before it, those ticks were printed unconditionally, which
 * meant the platform was vouching for a fact it had no way of knowing and no
 * way of ever withdrawing.
 *
 * What a clear result actually means, and the wording on the certificate has to
 * match it: nobody has reported anything to this register. That is a genuinely
 * useful negative — an Interpol-style check on a small workshop's output does
 * not otherwise exist anywhere — but it is not a statement that the piece is
 * clean, and the two must never be conflated. The platform is not an
 * investigator: a flag records that somebody made a claim, and points at the
 * paperwork they made it under.
 *
 * Flags are resolved or withdrawn, never deleted. A piece that was reported and
 * cleared has a history a buyer is entitled to see, and a register that can
 * quietly forget an accusation is one an accuser cannot trust either.
 */
class ProductFlags
{
    /** The concerns this register is able to hold. */
    public const FLAGS = ['reported_stolen', 'under_investigation', 'disputed_ownership', 'export_restricted'];

    /**
     * The certificate tick each flag governs.
     *
     * export_restricted is deliberately not in this map. Whether a piece may
     * lawfully leave the country depends on CITES schedules and cultural
     * heritage law that the platform holds no register of, so the absence of a
     * restriction here says nothing at all — printing a green "export ready"
     * off it would be inventing a clearance. The flag is still recorded and
     * still shown through active(), as a warning that can only fire in one
     * direction.
     */
    private const TICKS = [
        'not_reported_stolen'     => 'reported_stolen',
        'not_under_investigation' => 'under_investigation',
        'ownership_undisputed'    => 'disputed_ownership',
    ];

    /** Raises a flag and returns the stored row. */
    public static function raise(Product|int $product, string $flag, array $opts = []): object
    {
        $productId = self::id($product);

        if (! in_array($flag, self::FLAGS, true)) {
            throw new \InvalidArgumentException("Unknown product flag [{$flag}].");
        }

        $id = DB::table('product_flags')->insertGetId([
            'product_id'        => $productId,
            'flag'              => $flag,
            'raised_by_user_id' => $opts['raised_by_user_id'] ?? null,
            // The reference is what makes the flag checkable by somebody other
            // than us — a report number a buyer can quote to the authority that
            // issued it. Nullable, because a claim with no paperwork yet is
            // still worth recording, but it is a weaker thing.
            'reference'         => $opts['reference'] ?? null,
            'notes'             => $opts['notes'] ?? null,
            'status'            => 'active',
            'raised_at'         => $opts['raised_at'] ?? now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Raising and clearing a flag both change what every certificate for
        // this piece asserts, so both belong on the tamper-evident log.
        CertificationAuthority::appendToChain('prc', $productId, 'flag_raised', $flag, $opts['raised_by_user_id'] ?? null);

        return DB::table('product_flags')->find($id);
    }

    /** Closes a flag. The row stays; only its status moves. */
    public static function resolve(int $flagId, ?string $note = null): void
    {
        $row = DB::table('product_flags')->find($flagId);

        if (! $row || $row->status !== 'active') {
            return;
        }

        DB::table('product_flags')->where('id', $flagId)->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
            'notes'       => $note ? trim(($row->notes ? $row->notes . "\n" : '') . $note) : $row->notes,
            'updated_at'  => now(),
        ]);

        CertificationAuthority::appendToChain('prc', (int) $row->product_id, 'flag_resolved', $row->flag);
    }

    /** @return array<int,object> the live concerns, newest first */
    public static function active(Product|int $product): array
    {
        return DB::table('product_flags')
            ->where('product_id', self::id($product))
            ->where('status', 'active')
            ->orderByDesc('raised_at')
            ->get()->all();
    }

    /** True when nothing has been raised against this piece. */
    public static function isClear(Product|int $product): bool
    {
        return self::active($product) === [];
    }

    /**
     * The tick map a certificate renders.
     *
     * Only the three concerns this register is authoritative over appear. A key
     * that is absent means the platform cannot determine the answer — the
     * caller must render nothing at all for it, not an unticked box, because a
     * printed "no" is itself a claim and we are no more entitled to it than to
     * the "yes".
     *
     * @return array<string,bool>
     */
    public static function checks(Product|int $product): array
    {
        $raised = array_column(self::active($product), 'flag');

        $checks = [];

        foreach (self::TICKS as $tick => $flag) {
            $checks[$tick] = ! in_array($flag, $raised, true);
        }

        return $checks;
    }

    private static function id(Product|int $product): int
    {
        return $product instanceof Product ? (int) $product->id : $product;
    }
}
