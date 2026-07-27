<?php

namespace App\Support;

/**
 * The one definition of the account kinds the platform offers.
 *
 * Quick signup used to offer two (Buyer / Artisan) and the onboarding wizard
 * four (Individual Artisan / Cooperative / SME / Large Enterprise), so the same
 * question had two different answers depending on which door you came through.
 * Both now read this list.
 */
final class AccountTypes
{
    /** key => [icon, fr, en, vendor_type] — vendor_type is null for buyers. */
    public const TYPES = [
        'buyer' => [
            'shopping-bag',
            'Acheteur / Visiteur',
            'Buyer / Visitor',
            null,
        ],
        'artisan' => [
            'hammer',
            'Artisan Individuel',
            'Individual Artisan',
            'artisan',
        ],
        'cooperative' => [
            'users',
            'Coopérative / Groupement',
            'Cooperative / Group',
            'cooperative',
        ],
        'pme' => [
            'store',
            'PME / Entreprise',
            'SME / Business',
            'entreprise',
        ],
        'grande_entreprise' => [
            'building-2',
            'Grande Entreprise',
            'Large Enterprise',
            'entreprise',
        ],
    ];

    /** Everything except 'buyer' — the kinds that get a shop. */
    public static function sellerKeys(): array
    {
        return array_keys(array_filter(self::TYPES, fn ($t) => $t[3] !== null));
    }

    public static function keys(): array
    {
        return array_keys(self::TYPES);
    }

    public static function isSeller(?string $key): bool
    {
        return $key !== null && in_array($key, self::sellerKeys(), true);
    }

    /** The role a given account kind maps to. */
    public static function role(?string $key): string
    {
        return self::isSeller($key) ? 'business_owner' : 'buyer';
    }

    /**
     * The shop's vendor_type for this account kind. The directory facets on
     * three values, so both business sizes land on `entreprise` — the finer
     * distinction stays on the user record.
     */
    public static function vendorType(?string $key): ?string
    {
        return self::TYPES[$key][3] ?? null;
    }

    public static function label(?string $key, bool $isFr): ?string
    {
        if (! isset(self::TYPES[$key])) {
            return null;
        }
        return $isFr ? self::TYPES[$key][1] : self::TYPES[$key][2];
    }

    /** [key, icon, label] for rendering a chooser. */
    public static function options(bool $isFr): array
    {
        return array_map(
            fn ($key) => [$key, self::TYPES[$key][0], self::label($key, $isFr)],
            self::keys()
        );
    }
}
