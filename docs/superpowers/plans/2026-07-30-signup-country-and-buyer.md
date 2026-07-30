# Country at Signup and the Buyer Account — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let anyone choose their country when signing up, make the buyer account type selectable on both signup doors, and stop the two doors from ever disagreeing again.

**Architecture:** `users` gains a nullable `country_id`. The signup wizard stops hardcoding its own account-type list and reads `App\Support\AccountTypes` — the same source the fast form already uses — keeping only presentation (icon, blurb, perks) in the view, guarded by a test that fails if the two drift. A buyer submits at step 2 and never sees the review step. `BusinessWebController` prefills the country from the user and refuses to serve the shop form to a non-seller.

**Tech Stack:** Laravel 13, Blade, MySQL locally / MariaDB in production, PHPUnit via `php artisan test`, Tailwind (static build — new utility classes need `npm run build:css`).

**Spec:** `docs/superpowers/specs/2026-07-30-signup-country-and-buyer-design.md`

---

## Context the engineer needs

**Run everything from** `C:\laragon\www\artisanatcameroun`. PHP is not on PATH; prefix with:

```bash
export PATH="/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH"
```

**Two signup doors exist and they are different:**

| Door | Route | View | Account types |
|---|---|---|---|
| Wizard (desktop primary) | `/creer-mon-compte` | `resources/views/pages/onboarding.blade.php` | hardcoded array of 4 sellers, line 13 |
| Fast form (mobile) | `/inscription-rapide` | `resources/views/auth/quick-register.blade.php` | `AccountTypes::options($isFr)` — all 5, defaults to buyer |

Both POST to the same handler at `routes/web.php:1799`, which already accepts all five types.

**The wizard has three steps:** 1 account type (33%), 2 phone/email/password (66%), 3 review (100%). There is no trade question in it.

**`AccountTypes` API** (`app/Support/AccountTypes.php`):

```php
AccountTypes::keys()            // ['buyer','artisan','cooperative','pme','grande_entreprise']
AccountTypes::options(bool $isFr) // [[key, lucideIcon, label], ...] for all five
AccountTypes::role('buyer')     // 'buyer'    — sellers give 'business_owner'
AccountTypes::vendorType('buyer') // null     — sellers give 'artisan'|'cooperative'|'entreprise'
AccountTypes::isSeller('buyer') // false
AccountTypes::sellerKeys()      // the four seller keys
```

**After signup the user is NOT sent to a dashboard.** The handler redirects to
`/creer-mon-compte?submitted=1`, a "check your email" screen. Role-based landing
already works later: `/tableau-de-bord` (`routes/web.php:1936`) dispatches
`business_owner` → `/tableau-de-bord/entrepreneur`, everything else →
`/tableau-de-bord/acheteur`. **No redirect work is needed for buyers.**

**Testing model:** copy the shape of `tests/Feature/MultiCountrySignupTest.php` —
`RefreshDatabase`, `BuildsGalleryData`, and a `withSession(['siac_user' => [...]])`
helper for authenticated requests.

---

## File Structure

| File | Responsibility | Change |
|---|---|---|
| `database/migrations/2026_07_31_090000_add_country_to_users.php` | `users.country_id` + backfill | Create |
| `app/Modules/Auth/Models/User.php` | `country` relation, `country_id` fillable | Modify |
| `resources/views/pages/onboarding.blade.php` | Wizard: presentation map, country select, buyer submit | Modify |
| `resources/views/auth/quick-register.blade.php` | Fast form: country select | Modify |
| `routes/web.php` | Signup handler stores `country_id` | Modify |
| `app/Http/Controllers/BusinessWebController.php` | Seller guard + country prefill | Modify |
| `tests/Feature/SignupCountryAndBuyerTest.php` | All new behaviour | Create |
| `database/production/patches/2026-07-31-add-country-to-users.sql` | phpMyAdmin patch (no SSH on host) | Create |

**No new PHP class is introduced.** The spec explains why that matters: production
runs `setClassMapAuthoritative(true)`, so a new class would need the regenerated
`vendor/composer/autoload_*.php` shipped with it. Adding a relation method to an
existing class does not.

---

## Task 1: `users.country_id`

**Files:**
- Create: `database/migrations/2026_07_31_090000_add_country_to_users.php`
- Modify: `app/Modules/Auth/Models/User.php`
- Test: `tests/Feature/SignupCountryAndBuyerTest.php`

- [x] **Step 1: Write the failing test**

Create `tests/Feature/SignupCountryAndBuyerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Taxonomy\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Country at signup, and the buyer account type that the wizard never offered.
 *
 * The wizard hardcoded its own four-seller list while the fast form read the
 * shared one, so the same question had two answers depending on which door you
 * used. These tests pin the shared list, the country round trip, and the guard
 * that stops a buyer creating a shop they could not administer.
 */
class SignupCountryAndBuyerTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_users_can_carry_a_country(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'country_id'));

        $cm = Country::where('code', 'CM')->firstOrFail();
        $user = User::factory()->create(['country_id' => $cm->id]);

        $this->assertSame($cm->id, $user->fresh()->country->id);
    }
}
```

- [x] **Step 2: Run it and watch it fail**

```bash
export PATH="/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH"
php artisan test --filter=test_users_can_carry_a_country
```

Expected: FAIL — `Failed asserting that false is true` (the column does not exist).

If it instead fails with "Call to undefined method User::factory()", the project
has no user factory; replace `User::factory()->create([...])` with an explicit
`User::create([...])` copying the shape used in
`tests/Feature/MultiCountrySignupTest.php::makeOwner()`, plus `'country_id' => $cm->id`.

- [x] **Step 3: Write the migration**

Create `database/migrations/2026_07_31_090000_add_country_to_users.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The country a member states when they sign up.
 *
 * Nullable on purpose. Every account that exists today is a SIARC import from a
 * Cameroonian competition dataset, so those are backfilled to Cameroon as a
 * statement of fact — but a future row whose country is genuinely unknown must
 * be able to say so rather than silently claim Cameroon.
 *
 * `assigned_region_id` on this table is left alone: it is an admin assignment
 * for staff coverage and means something different.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('phone')
                ->constrained('countries')->nullOnDelete();
        });

        $cameroon = DB::table('countries')->where('code', 'CM')->value('id');

        if ($cameroon) {
            DB::table('users')->whereNull('country_id')->update(['country_id' => $cameroon]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
```

- [x] **Step 4: Add the relation to the User model**

In `app/Modules/Auth/Models/User.php`, add `'country_id'` to the `$fillable`
array, and add this method alongside the other relations:

```php
    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Taxonomy\Models\Country::class);
    }
```

- [x] **Step 5: Migrate and run the test**

```bash
php artisan migrate --force
php artisan test --filter=test_users_can_carry_a_country
```

Expected: PASS.

- [x] **Step 6: Confirm the backfill hit the real rows**

```bash
php -r 'require "vendor/autoload.php"; $a=require "bootstrap/app.php";
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo Illuminate\Support\Facades\DB::table("users")->whereNull("country_id")->count() . " users without a country (expect 0)\n";'
```

Expected: `0 users without a country (expect 0)`.

- [x] **Step 7: Commit**

```bash
git add database/migrations/2026_07_31_090000_add_country_to_users.php app/Modules/Auth/Models/User.php tests/Feature/SignupCountryAndBuyerTest.php
git commit -m "Give users a country of their own"
```

---

## Task 2: One source of truth for account types

The wizard's own list is the bug. This task makes the view render whatever
`AccountTypes` says exists, and fails loudly if presentation is missing.

**Files:**
- Modify: `resources/views/pages/onboarding.blade.php:13-60` (the `$accountTypes` array)
- Test: `tests/Feature/SignupCountryAndBuyerTest.php`

- [x] **Step 1: Write the failing tests**

Append to `SignupCountryAndBuyerTest`:

```php
    public function test_both_signup_doors_offer_every_account_type(): void
    {
        $wizard = $this->get('/creer-mon-compte')->assertOk()->getContent();
        $fast   = $this->get('/inscription-rapide')->assertOk()->getContent();

        foreach (\App\Support\AccountTypes::keys() as $key) {
            $this->assertStringContainsString(
                'value="' . $key . '"',
                $wizard,
                "The wizard does not offer the '{$key}' account type."
            );
            $this->assertStringContainsString(
                'value="' . $key . '"',
                $fast,
                "The fast form does not offer the '{$key}' account type."
            );
        }
    }

    public function test_the_wizard_offers_no_type_the_platform_does_not_know(): void
    {
        // Guards the reverse drift: a card left behind after a type is removed
        // would post a value the handler rejects.
        $wizard = $this->get('/creer-mon-compte')->assertOk()->getContent();

        preg_match_all('/name="account_type" value="([a-z_]+)"/', $wizard, $m);

        $this->assertNotEmpty($m[1], 'No account_type radios found — the markup changed.');
        foreach (array_unique($m[1]) as $offered) {
            $this->assertContains($offered, \App\Support\AccountTypes::keys(),
                "The wizard offers '{$offered}', which AccountTypes does not define.");
        }
    }
```

- [x] **Step 2: Run them and watch the first fail**

```bash
php artisan test --filter=SignupCountryAndBuyerTest
```

Expected: `test_both_signup_doors_offer_every_account_type` FAILS with
"The wizard does not offer the 'buyer' account type."

- [x] **Step 3: Rewrite the wizard's list as a presentation map**

In `resources/views/pages/onboarding.blade.php`, replace the whole
`$accountTypes = [ ... ];` array (starts line 13) with the block below. Keep every
existing icon filename, colour, title, description and perk list exactly as they
are — only the buyer entry is new, and the shape changes from a list to a map.

```php
    /*
     | Presentation for each account type. WHICH types exist is decided by
     | App\Support\AccountTypes and nothing else — this map only says how to
     | draw them. The two are kept in step by SignupCountryAndBuyerTest.
     |
     | This used to be a standalone list of four sellers, which is why the buyer
     | type was invisible on this door while the fast form offered it: the same
     | question had two different answers depending on how you arrived.
     */
    $typeArt = [
        'buyer' => [
            'ob-type-5.png', '#3565DE',
            $isFr ? 'Acheteur / Visiteur' : 'Buyer / Visitor',
            $isFr ? "Vous cherchez à acheter des créations artisanales ou à contacter des artisans."
                  : 'You are looking to buy craft creations or to contact artisans.',
            $isFr ? ['Contacter les artisans', 'Demander des devis', 'Enregistrer vos favoris', 'Suivre vos commandes']
                  : ['Contact artisans', 'Request quotes', 'Save your favourites', 'Track your orders'],
        ],
        'artisan' => [
            'ob-type-1.png', '#157A43',
            $isFr ? 'Artisan Individuel' : 'Individual Artisan',
            $isFr ? 'Vous êtes un artisan travaillant à titre individuel et souhaitant promouvoir vos créations.' : 'You are an artisan working individually and wishing to promote your creations.',
            $isFr ? ['Vitrine personnelle', 'Gestion de vos produits', 'Accès aux demandes de devis', 'Participation aux événements']
                  : ['Personal showcase', 'Manage your products', 'Access to quote requests', 'Participation in events'],
        ],
        'cooperative' => [
            'ob-type-2.png', '#FEB530',
            $isFr ? 'Coopérative / Groupement' : 'Cooperative / Group',
            $isFr ? "Vous représentez une coopérative ou un groupement d'artisans." : 'You represent a cooperative or a group of artisans.',
            $isFr ? ['Vitrine de la coopérative', 'Gestion des membres', 'Gestion collective des produits', "Accès aux marchés et appels d'offres"]
                  : ['Cooperative showcase', 'Member management', 'Collective product management', 'Access to markets and tenders'],
        ],
    ];
```

**Then copy the existing `pme` and `grande_entreprise` entries** out of the old
array into this map, keyed the same way — their icon, colour, title, description
and perks are already written in the file; move them verbatim, dropping the
leading key element from each list (it becomes the array key).

Immediately after the map, add:

```php
    /*
     | Render order: the shared list decides membership, this decides sequence.
     | Buyer sits first because it is the shortest commitment and the largest
     | future audience; artisan stays second because it is who signs up today.
     */
    $accountTypes = collect(\App\Support\AccountTypes::keys())
        ->map(function (string $key) use ($typeArt, $isFr) {
            // A type with no artwork is a bug, not something to render blank.
            abort_unless(isset($typeArt[$key]), 500, "No signup artwork for account type '{$key}'.");
            [$icon, $colour, $title, $desc, $perks] = $typeArt[$key];

            return [$key, $icon, $colour, $title, $desc, $perks];
        })
        ->values()
        ->all();
```

The existing `@foreach($accountTypes as $atIdx => [$atKey, $atIcon, $atColor, $atTitle, $atDesc, $atPerks])`
loops and the `$typeNames` / `$typeDescs` maps at lines 106-107 keep working
unchanged, because the element shape is identical.

- [x] **Step 4: Check the buyer artwork exists**

```bash
ls public/images/landing/ob-type-*.png
```

Expected: `ob-type-1.png` through `ob-type-4.png` at least. **If `ob-type-5.png`
does not exist**, change the buyer entry's icon to `ob-type-1.png` for now and
note it — a wrong-but-present image beats a broken one. Do not invent a file.

- [x] **Step 5: Run the tests**

```bash
php artisan view:clear && php artisan test --filter=SignupCountryAndBuyerTest
```

Expected: both account-type tests PASS.

- [x] **Step 6: Commit**

```bash
git add resources/views/pages/onboarding.blade.php tests/Feature/SignupCountryAndBuyerTest.php
git commit -m "Let the wizard read the account types the platform actually has"
```

---

## Task 3: A buyer submits at step 2

**Files:**
- Modify: `resources/views/pages/onboarding.blade.php` (step-2 buttons and the step JS)
- Test: `tests/Feature/SignupCountryAndBuyerTest.php`

- [x] **Step 1: Write the failing test**

```php
    public function test_signing_up_as_a_buyer_creates_no_business(): void
    {
        $response = $this->post('/creer-mon-compte', [
            'first_name'            => 'Awa',
            'last_name'             => 'Traore',
            'email'                 => 'awa.buyer@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'buyer',
        ]);

        $response->assertRedirect();

        $user = \App\Modules\Auth\Models\User::where('email', 'awa.buyer@example.test')->firstOrFail();

        $this->assertSame('buyer', $user->account_type);
        $this->assertSame(0, \App\Modules\Businesses\Models\Business::where('user_id', $user->id)->count());
    }
```

- [x] **Step 2: Run it**

```bash
php artisan test --filter=test_signing_up_as_a_buyer_creates_no_business
```

Expected: PASS already — the handler accepts `buyer` and creates no business.
This test is a regression guard, not a driver. If it FAILS, stop and read the
error: something in the handler assumes a seller, and that must be fixed before
the UI offers the option.

- [x] **Step 3: Make the wizard submit at step 2 for a buyer**

Find the step-2 "next" control in `resources/views/pages/onboarding.blade.php`
(the button that advances to the review step — search for `ob-step` handling in
the `<script>` block near line 876). Add, inside the same `<script>`:

```javascript
/* A buyer has nothing to review: the summary step lists a trade and a shop,
   neither of which they are creating. Send them straight to submit. */
function chosenAccountType() {
    var el = document.querySelector('input[name="account_type"]:checked');
    return el ? el.value : '';
}

function buyerSelected() {
    return chosenAccountType() === 'buyer';
}
```

Then in the handler that moves from step 2 to step 3, submit instead when
`buyerSelected()` is true. The exact call depends on the existing code — read it
before editing rather than pattern-matching, and keep the existing submit path
(the hidden `account_type` input at line 936 is populated by the existing
`form.querySelector('[name="account_type"]').value = ...` line).

- [x] **Step 4: Verify by hand in a browser**

```bash
php artisan view:clear
```

Open `http://artisanatcameroun.test/creer-mon-compte`, choose **Buyer**, fill
step 2, and confirm it submits without showing the review screen. Then repeat
choosing **Artisan** and confirm the review screen still appears.

This step is manual on purpose: the step transitions are client-side JavaScript
and a feature test cannot exercise them.

- [x] **Step 5: Run the full suite**

```bash
php artisan test
```

Expected: all green (669 + the new tests at time of writing).

- [x] **Step 6: Commit**

```bash
git add resources/views/pages/onboarding.blade.php tests/Feature/SignupCountryAndBuyerTest.php
git commit -m "Send a buyer straight to submit instead of a review of things they are not creating"
```

---

## Task 4: Country on both signup doors

Note the wizard's phone field currently hardcodes a Cameroon flag image and a
`+237` prefix (around line 388). The dial code must follow the chosen country.

**Files:**
- Modify: `resources/views/pages/onboarding.blade.php` (step 2, beside phone)
- Modify: `resources/views/auth/quick-register.blade.php`
- Modify: `routes/web.php:1803` (validation) and `:1855` (insert)
- Test: `tests/Feature/SignupCountryAndBuyerTest.php`

- [x] **Step 1: Write the failing test**

```php
    public function test_the_country_chosen_at_signup_is_stored(): void
    {
        $ci = Country::where('code', 'CI')->firstOrFail();

        $this->post('/creer-mon-compte', [
            'first_name'            => 'Kouadio',
            'last_name'             => 'Yao',
            'email'                 => 'kouadio.signup@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'artisan',
            'country_id'            => $ci->id,
        ]);

        $user = User::where('email', 'kouadio.signup@example.test')->firstOrFail();

        $this->assertSame($ci->id, $user->country_id);
    }

    public function test_an_unknown_country_is_rejected(): void
    {
        $this->post('/creer-mon-compte', [
            'first_name'            => 'Test',
            'last_name'             => 'Person',
            'email'                 => 'bad.country@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'artisan',
            'country_id'            => 999999,
        ])->assertSessionHasErrors('country_id');

        $this->assertSame(0, User::where('email', 'bad.country@example.test')->count());
    }
```

- [x] **Step 2: Run and watch it fail**

```bash
php artisan test --filter=test_the_country_chosen_at_signup_is_stored
```

Expected: FAIL — `null` does not match the Côte d'Ivoire id.

- [x] **Step 3: Accept and store it in the handler**

In `routes/web.php`, in the `$request->validate([...])` block at line 1803, add:

```php
        'country_id'            => ['nullable', 'exists:countries,id'],
```

In the `DB::table('users')->insert([...])` block at line 1855, add after `'phone'`:

```php
            'country_id'          => $data['country_id'] ?? null,
```

- [x] **Step 4: Add the select to the wizard**

In `resources/views/pages/onboarding.blade.php`, immediately **before** the
phone field's `<div>` on step 2, insert:

```blade
                          <div>
                              <label for="ob-country" class="{{ $labelCls }}">{{ $isFr ? 'Pays' : 'Country' }} <span class="ui-req">*</span></label>
                              <select id="ob-country" name="country_id" class="ui-field ui-select">
                                  @foreach(\App\Modules\Taxonomy\Models\Country::active()->get() as $c)
                                  <option value="{{ $c->id }}"
                                          data-dial="{{ $c->dial_code }}"
                                          @selected(old('country_id', $c->code === 'CM' ? $c->id : null) == $c->id)>{{ $c->flag_emoji }} {{ $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr) }}</option>
                                  @endforeach
                              </select>
                          </div>
```

Then make the phone prefix follow it. Replace the hardcoded flag image and
`+237` span (line ~388) with:

```blade
                                  <span id="ob-dial" class="shrink-0 text-[14px] text-[#1B1B18] dark:text-[#F3EFE7]">+237</span>
```

and add to the page's `<script>`:

```javascript
/* The phone prefix was a fixed "+237" beside a Cameroon flag, from when this
   was a one-country platform. It now follows the chosen country. */
(function () {
    var country = document.getElementById('ob-country');
    var dial = document.getElementById('ob-dial');
    if (!country || !dial) return;

    function syncDial() {
        var opt = country.options[country.selectedIndex];
        dial.textContent = '+' + (opt ? opt.getAttribute('data-dial') : '237');
    }

    country.addEventListener('change', syncDial);
    syncDial();
})();
```

- [x] **Step 5: Add the select to the fast form**

In `resources/views/auth/quick-register.blade.php`, after the account-type
block (which ends around line 50), insert:

```blade
                <div>
                    <label class="ui-label" for="qr-country">{{ $isFr ? 'Pays' : 'Country' }}</label>
                    <select id="qr-country" name="country_id" class="ui-field ui-select">
                        @foreach(\App\Modules\Taxonomy\Models\Country::active()->get() as $c)
                        <option value="{{ $c->id }}" @selected(old('country_id', $c->code === 'CM' ? $c->id : null) == $c->id)>{{ $c->flag_emoji }} {{ $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr) }}</option>
                        @endforeach
                    </select>
                </div>
```

If the fast form posts to a different handler than `/creer-mon-compte`, add the
same two lines (validation rule and insert column) there too. Check with:

```bash
grep -n 'action=' resources/views/auth/quick-register.blade.php
```

- [x] **Step 6: Show it on the review step**

The review step lists what will be saved, so it must list the country. Find the
review block (search for `Ce qui sera enregistré`, around line 451) and add a row
following the existing rows' markup, populated by the existing review JavaScript
that mirrors step-2 values.

- [x] **Step 7: Run the tests**

```bash
php artisan view:clear && php artisan test --filter=SignupCountryAndBuyerTest
```

Expected: all PASS.

- [x] **Step 8: Check the pages still render at phone width**

```bash
MSYS_NO_PATHCONV=1 node scripts/responsive-audit.cjs --routes /creer-mon-compte,/inscription-rapide --widths 320,390
```

Expected: `0 failing page/width combinations`.

- [x] **Step 9: Commit**

```bash
git add resources/views/pages/onboarding.blade.php resources/views/auth/quick-register.blade.php routes/web.php tests/Feature/SignupCountryAndBuyerTest.php
git commit -m "Ask for a country at signup, and let the dial code follow it"
```

---

## Task 5: Seller guard and country prefill on the business form

**Files:**
- Modify: `app/Http/Controllers/BusinessWebController.php` (`create`, `store`)
- Test: `tests/Feature/SignupCountryAndBuyerTest.php`

- [x] **Step 1: Write the failing tests**

```php
    public function test_a_buyer_cannot_reach_the_shop_form(): void
    {
        $buyer = User::factory()->create(['account_type' => 'buyer']);

        $this->withSession(['siac_user' => [
            'id' => $buyer->id, 'name' => $buyer->name, 'email' => $buyer->email,
            'role' => 'buyer', 'is_admin' => false,
        ]])
            ->get('/tableau-de-bord/entreprise/creer')
            ->assertRedirect('/tableau-de-bord/acheteur');

        $this->assertSame(0, \App\Modules\Businesses\Models\Business::where('user_id', $buyer->id)->count());
    }

    public function test_the_shop_form_is_prefilled_with_the_owners_country(): void
    {
        $ci = Country::where('code', 'CI')->firstOrFail();
        $seller = User::factory()->create(['account_type' => 'artisan', 'country_id' => $ci->id]);

        $html = $this->withSession(['siac_user' => [
            'id' => $seller->id, 'name' => $seller->name, 'email' => $seller->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]])
            ->get('/tableau-de-bord/entreprise/creer')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<option value="' . $ci->id . '"[^>]*selected/',
            $html,
            "The seller's own country is not preselected on the shop form."
        );
        // And its regions must already be loaded, not an empty list.
        $this->assertStringContainsString('Abidjan', $html);
    }
```

- [x] **Step 2: Run and watch both fail**

```bash
php artisan test --filter=SignupCountryAndBuyerTest
```

Expected: the guard test FAILS (200 instead of a redirect), the prefill test
FAILS (no `selected` on that option).

- [x] **Step 3: Add the guard**

In `app/Http/Controllers/BusinessWebController.php`, add this private helper
beside `requireUser`:

```php
    /**
     * A shop belongs to a seller. A buyer following a stale link here would get
     * a business whose owner has role `buyer`, and BusinessService would fall
     * back to vendor_type `artisan` — a shop its owner cannot administer.
     * Letting a buyer become a seller is a real need, but it is an account
     * upgrade with its own decisions, not a side effect of opening a form.
     */
    private function requireSeller(array $siacUser): ?RedirectResponse
    {
        $accountType = User::whereKey($siacUser['id'])->value('account_type');

        return \App\Support\AccountTypes::isSeller($accountType)
            ? null
            : redirect('/tableau-de-bord/acheteur');
    }
```

Then in **both** `create()` and `store()`, immediately after the existing
`if ($siacUser instanceof RedirectResponse) return $siacUser;` line, add:

```php
        if ($guard = $this->requireSeller($siacUser)) {
            return $guard;
        }
```

- [x] **Step 4: Add the prefill**

In `create()`, replace:

```php
        $countries = Country::active()->get();
        $regions = collect();
```

with:

```php
        $countries = Country::active()->get();

        // Start from the country they gave at signup, with its regions already
        // loaded, so an Ivorian artisan is not asked the same question twice.
        // It is a default, not a constraint — a person may register in one
        // country and trade from another, and the form still lets them change it.
        $ownerCountryId = User::whereKey($siacUser['id'])->value('country_id');
        $regions = $ownerCountryId
            ? Region::where('country_id', $ownerCountryId)->orderBy('sort_order')->orderBy('name_fr')->get()
            : collect();
```

Then pass it to the view by adding to the `return view(...)` array:

```php
            'prefillCountryId' => $ownerCountryId,
```

In `resources/views/pages/dashboard/business-form.blade.php`, change the country
`<option>` selected test from `$v('country_id') == $country->id` to:

```blade
{{ ($v('country_id') ?: ($prefillCountryId ?? null)) == $country->id ? 'selected' : '' }}
```

**`edit()` must also pass `prefillCountryId`** — add `'prefillCountryId' => null`
to its `compact(...)` call by switching to an explicit array, or the view will
error on an undefined variable when editing.

- [x] **Step 5: Run the tests**

```bash
php artisan view:clear && php artisan test --filter=SignupCountryAndBuyerTest
```

Expected: all PASS.

- [x] **Step 6: Run everything**

```bash
php artisan test
MSYS_NO_PATHCONV=1 node scripts/responsive-audit.cjs --widths 320,390
```

Expected: full suite green, `0 failing page/width combinations`.

- [x] **Step 7: Commit**

```bash
git add app/Http/Controllers/BusinessWebController.php resources/views/pages/dashboard/business-form.blade.php tests/Feature/SignupCountryAndBuyerTest.php
git commit -m "Keep buyers out of the shop form and prefill a seller's country"
```

---

## Task 6: The production database patch

The host has no SSH, so the migration cannot be run with artisan. This mirrors
`database/production/patches/2026-07-30-add-countries.sql`.

**Files:**
- Create: `database/production/patches/2026-07-31-add-country-to-users.sql`

- [x] **Step 1: Write the patch**

```sql
-- ============================================================================
--  Give users a country of their own
--  Run once, in phpMyAdmin, against artisan_arthubdb.
-- ============================================================================
--
--  Additive and nullable, so the currently-running code ignores it entirely —
--  safe to run before the new code is uploaded, which is the required order.
--
--  Every account that exists today is a SIARC import from a Cameroonian
--  dataset, so Cameroon is a statement of fact for them, not a guess.
--
--  MariaDB-safe: no CTEs, no procedures, no UPDATE ... JOIN.
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `users` ADD COLUMN `country_id` bigint unsigned NULL AFTER `phone`;

ALTER TABLE `users`
  ADD CONSTRAINT `users_country_id_foreign`
  FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

UPDATE `users`
SET `country_id` = (SELECT `id` FROM `countries` WHERE `code` = 'CM')
WHERE `country_id` IS NULL;

INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_07_31_090000_add_country_to_users',
        (SELECT * FROM (SELECT COALESCE(MAX(`batch`),0)+1 FROM `migrations`) AS b));

-- Expected: 0 and 0.
SELECT
  (SELECT COUNT(*) FROM `users` WHERE `country_id` IS NULL) AS `users_without_a_country_MUST_BE_0`,
  (SELECT COUNT(*) FROM `information_schema`.`columns`
    WHERE `table_schema` = DATABASE() AND `table_name` = 'users'
      AND `column_name` = 'country_id') - 1 AS `column_missing_MUST_BE_0`;
```

- [x] **Step 2: Test it against a scratch copy of production**

```bash
MYSQL="/c/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe"
"$MYSQL" -u root -e "DROP DATABASE IF EXISTS userctest; CREATE DATABASE userctest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"$MYSQL" -u root userctest < database/production/artisanhub237-production.sql
"$MYSQL" -u root --default-character-set=utf8mb4 userctest < database/production/patches/2026-07-30-add-countries.sql
"$MYSQL" -u root --default-character-set=utf8mb4 userctest < database/production/patches/2026-07-31-add-country-to-users.sql
```

Expected: the final SELECT prints `0` and `0`.

- [x] **Step 3: Confirm the patch produces the same schema as the migration**

```bash
"$MYSQL" -u root -N -B virtualdb   -e "SELECT column_name,column_type,is_nullable FROM information_schema.columns WHERE table_schema='virtualdb' AND table_name='users' ORDER BY column_name" | md5sum
"$MYSQL" -u root -N -B userctest -e "SELECT column_name,column_type,is_nullable FROM information_schema.columns WHERE table_schema='userctest' AND table_name='users' ORDER BY column_name" | md5sum
```

Expected: identical hashes. If they differ, the patch and the migration disagree
and the patch is wrong — fix the patch, not the migration.

- [x] **Step 4: Drop the scratch database**

```bash
"$MYSQL" -u root -e "DROP DATABASE IF EXISTS userctest;"
```

- [x] **Step 5: Commit**

```bash
git add database/production/patches/2026-07-31-add-country-to-users.sql
git commit -m "Add the phpMyAdmin patch for users.country_id"
```

---

## Task 7: Package for deployment

**Files:**
- Modify: the deployment package under `C:\Users\PC\Desktop\artisanhub237deploy\FRESH-REUPLOAD\`

- [x] **Step 1: Sync the changed code into the package**

```bash
D="C:/Users/PC/Desktop/artisanhub237deploy/FRESH-REUPLOAD"
S="C:/laragon/www/artisanatcameroun"
A="$D/1-UPLOAD-to-HOME-folder/artisanhub237app"

rm -rf "$A/resources/views"; cp -r "$S/resources/views" "$A/resources/views"
rm -rf "$A/app";            cp -r "$S/app"            "$A/app"
rm -rf "$A/routes";         cp -r "$S/routes"         "$A/routes"
rm -rf "$A/database/migrations"; cp -r "$S/database/migrations" "$A/database/migrations"
cp "$S/database/production/patches/2026-07-31-add-country-to-users.sql" "$D/DATABASE-PATCHES/"

diff -rq "$S/app" "$A/app" && diff -rq "$S/resources/views" "$A/resources/views" && echo "package matches source"
```

- [x] **Step 2: Confirm no new class was introduced**

```bash
git diff --name-only HEAD~6..HEAD -- app/ | grep -E "^app/.*\.php$"
```

For each file listed, confirm it existed before this plan started:

```bash
git log --oneline --diff-filter=A -- <path>
```

If any file is **new**, the regenerated autoloader must ship too — see the spec's
"autoloader trap" section, and add `vendor/composer/autoload_classmap.php` and
`autoload_static.php` to the package. If none are new, skip this.

- [x] **Step 3: Rebuild the home zip**

```bash
SEVEN="/c/Users/PC/scoop/shims/7z"
rm -f "$D/UPLOAD-into-HOME-folder.zip"
cd "$D/1-UPLOAD-to-HOME-folder" && "$SEVEN" a -tzip -mx5 "$D/UPLOAD-into-HOME-folder.zip" "artisanhub237app"
```

- [x] **Step 4: Verify the zip**

```bash
python -c "
import zipfile
z = zipfile.ZipFile(r'$D/UPLOAD-into-HOME-folder.zip')
n = z.namelist()
print('entries:', len(n), 'backslash paths:', sum(1 for x in n if chr(92) in x))
print('migration shipped:', any('2026_07_31_090000_add_country_to_users' in x for x in n))
"
```

Expected: backslash paths `0`, migration shipped `True`.

- [x] **Step 5: Write the upload instructions**

Create `$D/SIGNUP-UPDATE/README.txt` stating the required order:

1. phpMyAdmin: run `2026-07-31-add-country-to-users.sql`, confirm both counts are 0
2. Upload and extract `UPLOAD-into-HOME-folder.zip` into the home folder
3. Upload and extract `UPLOAD-into-public_html.zip` into `public_html`
4. Open `https://artisanhub237.com/_update-8c1bl.php`

- [x] **Step 6: Commit**

```bash
git add -A
git commit -m "Package the signup country and buyer release"
```

---

## Self-review notes

**Spec coverage:** every numbered goal maps to a task — goal 1 → Tasks 1 and 4,
goal 2 → Task 5, goal 3 → Task 2, goal 4 → Task 3, goal 5 → Task 2's drift tests.
All eight spec tests appear: 1-2 in Task 2, 3 in Task 3, 4 is covered by the
existing `/tableau-de-bord` role dispatch (documented in Context, no task
needed), 5 in Task 4, 6 in Task 5, 7 by the full-suite runs, 8 in Task 5.

**Known soft spots, flagged rather than hidden:**

- Task 2 Step 3 asks the engineer to move the `pme` and `grande_entreprise`
  entries by hand rather than reproducing 20 lines of existing French copy that
  must match byte-for-byte. Reproducing it risks a typo in text already on the
  live site.
- Task 3 Step 3 and Task 4 Step 6 depend on JavaScript whose exact shape must be
  read first. Both say so explicitly and neither can be pattern-matched safely.
- Task 3 Step 4 is a manual browser check because the step transitions are
  client-side and no feature test reaches them.
