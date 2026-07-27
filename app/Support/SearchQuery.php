<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared free-text search behaviour for the public directory.
 *
 * The platform deploys to shared hosting, so there is no Scout/Elasticsearch
 * to lean on: everything here has to be expressible as plain SQL that both
 * MySQL (production) and SQLite (test suite) understand.
 *
 * Two things this buys over the previous `LIKE '%q%'` calls:
 *
 *  - Multi-word queries. "poterie bafoussam" used to be matched literally as
 *    one string and returned nothing; each term is now required to match
 *    somewhere (AND of ORs), which is what a user expects.
 *  - Relevance. A shop *named* the query outranks one that merely mentions it
 *    in a description.
 *
 * Accents are deliberately NOT stripped here: the production tables are
 * utf8mb4_unicode_ci, a collation that already folds both case and accents,
 * so "ceramique" matches "Céramique" for free. Doing it in PHP as well would
 * mean maintaining a transliteration table for no gain.
 *
 * Every comparison uses LIKE rather than `=` even for the exact tier, because
 * SQLite's `=` is case-sensitive while its LIKE is not; a wildcard-free LIKE
 * is an equality test that behaves the same on both engines.
 */
class SearchQuery
{
    /** Cap on terms per query — a pathological query must not fan out into 100 OR groups. */
    private const MAX_TERMS = 6;

    /**
     * The field sets every public search shares, so the directory, the gallery
     * search page and the JSON API all answer the same query the same way.
     */
    public const BUSINESS_NAMES     = ['name_fr', 'name_en'];
    public const BUSINESS_SECONDARY = ['tagline_fr', 'tagline_en', 'description_fr', 'description_en'];
    public const BUSINESS_COLUMNS   = [...self::BUSINESS_NAMES, ...self::BUSINESS_SECONDARY];
    public const BUSINESS_RELATIONS = [
        'industry' => ['name_fr', 'name_en'],
        'city'     => ['name_fr', 'name_en'],
        'region'   => ['name_fr', 'name_en', 'code'],
    ];

    public const PRODUCT_NAMES     = ['name_fr', 'name_en'];
    public const PRODUCT_SECONDARY = ['description_fr', 'description_en', 'sku', 'brand'];
    public const PRODUCT_COLUMNS   = [...self::PRODUCT_NAMES, ...self::PRODUCT_SECONDARY];
    public const PRODUCT_RELATIONS = [
        'category' => ['name_fr', 'name_en'],
        'business' => ['name_fr', 'name_en'],
    ];

    /** Split a raw query into distinct, non-trivial terms. */
    public static function terms(string $q): array
    {
        $parts = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_values(array_unique(array_filter($parts, fn ($t) => mb_strlen($t) >= 2)));

        // A single short token ("pi") is still worth searching for; only drop
        // short tokens when the query has longer ones to work with.
        if ($parts === []) {
            $whole = trim($q);
            return $whole === '' ? [] : [$whole];
        }

        return array_slice($parts, 0, self::MAX_TERMS);
    }

    /**
     * Escape char for LIKE patterns. Deliberately not a backslash: MySQL treats
     * a backslash as an escape inside string literals too, so `ESCAPE '\'` is a
     * parse error there while SQLite has no default escape char at all. '!' is
     * spelled the same way by both engines.
     */
    private const LIKE_ESCAPE = '!';

    /** Neutralise LIKE metacharacters so a user typing "100%" doesn't match everything. */
    public static function escapeLike(string $value): string
    {
        return str_replace(
            [self::LIKE_ESCAPE, '%', '_'],
            [self::LIKE_ESCAPE . self::LIKE_ESCAPE, self::LIKE_ESCAPE . '%', self::LIKE_ESCAPE . '_'],
            $value
        );
    }

    /**
     * Require every term to match at least one of $columns, or one of the
     * $relations (['relationName' => ['col', ...]]).
     */
    public static function apply(Builder $query, string $q, array $columns, array $relations = []): Builder
    {
        $terms = self::terms($q);
        if ($terms === [] || $columns === []) {
            return $query;
        }

        $driver = $query->getConnection()->getDriverName();

        return $query->where(function ($outer) use ($terms, $columns, $relations, $driver) {
            foreach ($terms as $term) {
                $like = '%' . self::escapeLike($term) . '%';

                $outer->where(function ($group) use ($like, $columns, $relations, $driver) {
                    foreach ($columns as $column) {
                        $group->orWhereRaw(self::likeExpression($column, $driver), [$like]);
                    }
                    foreach ($relations as $relation => $relColumns) {
                        $group->orWhereHas($relation, function ($rel) use ($relColumns, $like, $driver) {
                            $rel->where(function ($w) use ($relColumns, $like, $driver) {
                                // Qualified: `name_fr` exists on businesses and on
                                // cities/regions alike, and the whereHas subquery
                                // has both tables in scope.
                                $table = $w->getModel()->getTable();
                                foreach ($relColumns as $relColumn) {
                                    $w->orWhereRaw(self::likeExpression("{$table}.{$relColumn}", $driver), [$like]);
                                }
                            });
                        });
                    }
                });
            }
        });
    }

    /**
     * Rank rows: exact name, then name-prefix, then name-contains, then a hit
     * in the secondary columns (tagline/description). Applied before any other
     * ordering so relevance wins and the caller's sort breaks ties.
     */
    public static function orderByRelevance(Builder $query, string $q, array $nameColumns, array $secondaryColumns = []): Builder
    {
        $terms = self::terms($q);
        if ($terms === [] || $nameColumns === []) {
            return $query;
        }

        // Rank on the query as a whole: "poterie bafoussam" should reward a
        // shop actually called that, not one called "Poterie" in another town.
        $phrase = self::escapeLike(implode(' ', $terms));
        $driver = $query->getConnection()->getDriverName();

        $sql      = 'CASE ';
        $bindings = [];

        // 0 exact name · 1 name starts with · 2 name contains the whole phrase
        foreach ([[$phrase, 0], [$phrase . '%', 1], ['%' . $phrase . '%', 2]] as [$pattern, $rank]) {
            $sql .= 'WHEN ' . self::orLike($nameColumns, $driver) . " THEN {$rank} ";
            foreach ($nameColumns as $ignored) {
                $bindings[] = $pattern;
            }
        }

        // 3 — every term appears in the name, just not adjacently
        // ("poterie bafoussam" vs "Poterie d'art de Bafoussam").
        if (count($terms) > 1) {
            $clauses = [];
            foreach ($terms as $term) {
                $clauses[] = '(' . self::orLike($nameColumns, $driver) . ')';
                foreach ($nameColumns as $ignored) {
                    $bindings[] = '%' . self::escapeLike($term) . '%';
                }
            }
            $sql .= 'WHEN ' . implode(' AND ', $clauses) . ' THEN 3 ';
        }

        if ($secondaryColumns !== []) {
            $sql .= 'WHEN ' . self::orLike($secondaryColumns, $driver) . ' THEN 4 ';
            foreach ($secondaryColumns as $ignored) {
                $bindings[] = '%' . $phrase . '%';
            }
        }

        $sql .= 'ELSE 5 END';

        return $query->orderByRaw($sql, $bindings);
    }

    private static function orLike(array $columns, string $driver): string
    {
        return implode(' OR ', array_map(fn ($c) => self::likeExpression($c, $driver), $columns));
    }

    private static function likeExpression(string $column, string $driver): string
    {
        return self::wrap($column, $driver) . " LIKE ? ESCAPE '" . self::LIKE_ESCAPE . "'";
    }

    /** Quote an identifier (possibly `table.column`) for the active driver. */
    private static function wrap(string $column, string $driver): string
    {
        $quote = $driver === 'mysql' ? '`' : '"';

        return implode('.', array_map(
            fn ($part) => $quote . str_replace($quote, $quote . $quote, $part) . $quote,
            explode('.', $column)
        ));
    }
}
