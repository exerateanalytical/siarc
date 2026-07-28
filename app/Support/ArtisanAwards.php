<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * The distinctions an outside body has given an artisan.
 *
 * Read the row for what it is. An award names somebody else — a chamber of
 * trades, a festival jury, a regional council — and says that they honoured
 * this artisan. Storing it here is the platform repeating that claim in its own
 * voice, on a page a buyer will read as the platform's word. So a row in this
 * table is an assertion about a third party, and it is recorded from evidence
 * by a reviewer who looked at the evidence. It is never self-declared.
 *
 * That rule is not abstract caution. This project has already had to strip
 * invented UNESCO and ministry honours off certificates once: text that named
 * real institutions, that nobody at those institutions had ever agreed to, and
 * that looked entirely credible because it was printed beside genuine data. The
 * only defence that held was making the honour impossible to write without a
 * named issuer and a named recorder who is not the beneficiary.
 *
 * Hence the two refusals below. An award with no issuer is a compliment, not an
 * award, and there is nowhere for a doubting reader to go. An award recorded by
 * the artisan who receives it is a self-portrait. Both throw.
 *
 * evidence_url and reference are nullable and should not be: they are the only
 * things that make the claim checkable by somebody other than us. They are
 * nullable because a reviewer holding a paper certificate with no URL still
 * needs to record it, and a row with a named issuer and a named recorder is
 * already far better than what came before. A reviewer who leaves both empty is
 * asking the reader to take the platform's word for it, and should know that.
 */
class ArtisanAwards
{
    /**
     * Records one distinction against one artisan.
     *
     * `recorded_by` is the account of the reviewer who read the evidence. It is
     * required, and it must not be the artisan.
     */
    public static function record(Business|int $business, array $data): object
    {
        $businessId = self::businessId($business);
        $model      = $business instanceof Business ? $business : Business::find($businessId);

        if (! $model) {
            throw new DomainException("No business [{$businessId}] to record an award against.");
        }

        $recordedBy = trim((string) ($data['recorded_by'] ?? ''));
        if ($recordedBy === '') {
            throw new DomainException('An award must name the reviewer who recorded it from evidence.');
        }

        // The artisan is the beneficiary of this row. They do not get to write it.
        if ((string) $model->user_id === $recordedBy) {
            throw new DomainException('An award cannot be recorded by the artisan who receives it; it is an outside body’s statement, not a self-description.');
        }

        $titleFr = trim((string) ($data['title_fr'] ?? ''));
        if ($titleFr === '') {
            throw new DomainException('An award needs a title.');
        }

        // Without a named body there is nothing for a reader to check, and the
        // platform ends up asserting a distinction on its own authority.
        $issuer = trim((string) ($data['issuer'] ?? ''));
        if ($issuer === '') {
            throw new DomainException('An award must name the body that gave it.');
        }

        $year = $data['year'] ?? null;
        $year = $year === null || $year === '' ? null : (int) $year;
        if ($year !== null && ($year < 1900 || $year > (int) date('Y') + 1)) {
            throw new DomainException("An award year of [{$year}] is not a year this register will accept.");
        }

        $id = DB::table('business_awards')->insertGetId([
            'business_id'  => $businessId,
            'title_fr'     => mb_substr($titleFr, 0, 255),
            'title_en'     => self::trimToNull($data['title_en'] ?? null, 255),
            'issuer'       => mb_substr($issuer, 0, 255),
            'year'         => $year,
            'evidence_url' => self::trimToNull($data['evidence_url'] ?? null, 500),
            'reference'    => self::trimToNull($data['reference'] ?? null, 120),
            'recorded_by'  => $recordedBy,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return DB::table('business_awards')->find($id);
    }

    /**
     * The distinctions on an artisan's profile, in the reader's language.
     *
     * The English title falls back to the French one rather than disappearing:
     * an award has a name in the language it was given in, and translating it
     * silently would be inventing a second, official-looking title.
     */
    public static function forBusiness(Business|int $business, ?string $lang = 'fr'): array
    {
        $rows = DB::table('business_awards')
            ->where('business_id', self::businessId($business))
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get();

        $isEn = $lang === 'en';

        return $rows->map(fn ($row) => [
            'id'           => (int) $row->id,
            'title'        => $isEn ? ($row->title_en ?: $row->title_fr) : $row->title_fr,
            'issuer'       => $row->issuer,
            'year'         => $row->year !== null ? (int) $row->year : null,
            'evidence_url' => $row->evidence_url,
            'reference'    => $row->reference,
            'recorded_by'  => $row->recorded_by,
        ])->all();
    }

    /** Removes a record. Awards are corrections of fact, so a wrong one is deleted rather than annotated. */
    public static function remove(int $id): void
    {
        DB::table('business_awards')->where('id', $id)->delete();
    }

    private static function businessId(Business|int $business): int
    {
        return (int) ($business instanceof Business ? $business->id : $business);
    }

    private static function trimToNull(?string $value, int $max): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
