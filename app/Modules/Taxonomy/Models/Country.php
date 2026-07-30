<?php

namespace App\Modules\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A country the platform accepts artisans from.
 *
 * The platform launched Cameroon-only, with `regions` holding the ten
 * Cameroonian regions and nothing recording which country they belonged to.
 * Côte d'Ivoire and Algeria were added on 2026-07-30; see the migration
 * 2026_07_30_140000_add_countries_for_multi_country_signup.
 *
 * Currency lives here because it is a property of the country, not of the
 * platform: Cameroon uses XAF, Côte d'Ivoire XOF, Algeria DZD. Products carry
 * their own `price_currency`, so this only supplies the default a new listing
 * starts from.
 */
class Country extends Model
{
    protected $fillable = [
        'code', 'code3', 'name_fr', 'name_en', 'dial_code',
        'currency_code', 'currency_symbol', 'flag_emoji',
        'default_lang', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class)->orderBy('sort_order')->orderBy('name_fr');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /** The name in the requested language, falling back to French. */
    public function name(string $lang = 'fr'): string
    {
        return $lang === 'en' ? ($this->name_en ?: $this->name_fr) : $this->name_fr;
    }

    /** Countries open for signup, in display order. Cameroon first. */
    public static function active()
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_fr');
    }
}
