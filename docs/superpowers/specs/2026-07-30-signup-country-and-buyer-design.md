# Country at signup, and the buyer account that was never offered

**Date:** 2026-07-30
**Status:** approved, ready for implementation planning

## The problem

Two defects reported after the multi-country release went live.

**1. Signup does not ask for a country.** `resources/views/pages/onboarding.blade.php`
contains no mention of country, region or `pays`. Country was added to the
business form only, which a seller reaches *after* signing up, and which a buyer
never reaches at all. Someone signing up from Abidjan cannot say so.

**2. The buyer account type is invisible.** The platform has five account types
in `App\Support\AccountTypes::TYPES` — `buyer`, `artisan`, `cooperative`, `pme`,
`grande_entreprise` — and the signup POST handler already accepts all five
(`'account_type' => ['nullable', 'in:' . implode(',', AccountTypes::keys())]`).
Only the UI omits `buyer`.

### Root cause of the second defect

The platform has two signup doors and they disagree about what exists.

- `/inscription-rapide` (`resources/views/auth/quick-register.blade.php`) builds
  its options from `AccountTypes::options($isFr)` — the shared list. It includes
  `buyer` and defaults to it.
- `/creer-mon-compte` (`resources/views/pages/onboarding.blade.php`) ignores that
  helper and hardcodes its own four-seller array at line 13 of the view.

The fast form carries this comment:

> Signup used to offer two kinds here while the wizard offered four, so the same
> question had two different answers depending on which door you came through.

That divergence was fixed on one side and left on the other. This is the same
bug reappearing, so the fix must remove the possibility rather than patch the
symptom.

### Supporting facts

| Fact | Value |
|---|---|
| Accounts today | 512, **all** `account_type = artisan` (SIARC imports) |
| Buyers ever registered | 0 — the option has never been offered |
| `users` location columns | `phone`, `language_preference`, `assigned_region_id` (an admin assignment, not the user's own country) |
| Wizard steps | 1 account type (33%) → 2 phone/email/password (66%) → 3 review (100%) |
| Trade question in the wizard | none — it lives on the fast form and the business form |
| Buyer dashboard | exists: `/tableau-de-bord/acheteur`, `pages/dashboard/buyer.blade.php` |

The wizard having no trade step matters: a buyer already has nothing
seller-specific to skip, so "buyer signs up with no extra steps" is close to free.

## Goals

1. Country is chosen at signup, by everyone, and stored on the user.
2. A seller's business form is prefilled from it — the question is asked once.
3. The buyer account type is selectable on both signup doors.
4. Choosing buyer submits after the details step and lands on the buyer dashboard.
5. The two doors can never again disagree about which account types exist.

## Non-goals

- Changing what a buyer can *do* once signed in. The buyer dashboard exists and
  is out of scope.
- Per-country currency display for buyers. The data will be there; using it is
  a later change.
- Touching the trade/industry question. Unrelated.
- Arabic, or anything else about Algeria beyond the country record.

## Design

### 1. Schema

Add `users.country_id`, nullable, FK to `countries`, `ON DELETE SET NULL`.

Nullable rather than defaulted-and-required because a null country is honest for
the 512 imported artisans who never told us anything: they were imported from a
Cameroonian competition dataset, so they are backfilled to Cameroon, but any
future row whose country is genuinely unknown must be able to say so rather than
silently claim Cameroon.

`assigned_region_id` is left alone. It is an admin assignment for staff coverage
and means something different; conflating them would be a subtle data bug.

### 2. One source of truth for account types

`AccountTypes` already answers *which* types exist. The wizard will read it
instead of its own array.

Presentation stays in the view — the wizard's cards carry a PNG icon, a blurb and
four perks each, which do not belong in a support class. The view keeps a
presentation map keyed by account-type key, and iterates
`AccountTypes::options()` to decide *what* to render.

A missing presentation entry must fail loudly at build time, not render a blank
card. A test asserts that the wizard's presentation map covers exactly
`AccountTypes::keys()` — so adding a sixth type in future breaks the test until
the wizard is updated, instead of quietly omitting it as happened here.

### 3. Buyer flow

Selecting **Buyer** in the wizard:

- hides nothing on step 2 (it already asks only phone, email, password — all of
  which a buyer needs),
- skips step 3, the review screen, and submits directly,
- redirects to `/tableau-de-bord/acheteur` instead of the business form.

Email verification is unchanged and identical for all account types. A buyer is
a real account and must confirm their address like anyone else.

`AccountTypes::role('buyer')` returns `buyer` and
`AccountTypes::vendorType('buyer')` returns null, so signup creates no business
record. Verified against the running code, not assumed.

**A buyer must not be able to reach the shop-creation form.** Today
`BusinessWebController::create()` redirects only a user who *already has* a
business; a user with none is shown the form regardless of account type. Nobody
has ever hit this, because no buyer account has ever existed. Once buyers can
sign up, one following a stale link would create a shop while their
`account_type` stayed `buyer` and their role stayed `buyer` — and
`BusinessService::create()` would fall back to `vendor_type = 'artisan'`. The
result is a shop its owner cannot administer.

So `create()` and `store()` gain a guard: a user whose account type is not a
seller is redirected to the buyer dashboard. Letting a buyer become a seller is
a real need, but it is an account-upgrade feature with its own decisions
(what happens to their role, whether they keep buyer history) and is explicitly
out of scope here. Blocking the inconsistent state is in scope; building the
upgrade path is not.

### 4. Country on the forms

Both doors get a country select, defaulting to Cameroon, populated from
`Country::active()`.

- Wizard: on step 2, beside phone. Country belongs with the contact details, not
  with the account-type choice, and putting it on step 1 would make the
  five-card grid taller on the step that is already the longest.
- Fast form: beside the existing fields.
- The review screen (step 3) lists the chosen country, since it lists what will
  be saved.

### 5. Prefilling the business form

`BusinessWebController::create()` currently seeds an empty country. It will seed
the owner's `country_id` and, when one is present, the matching region list —
so an Ivorian artisan arrives at the business form with Côte d'Ivoire already
selected and the fourteen districts already loaded.

The user's country is a default, not a constraint: a seller may still change it
on the business form, because a person can register from one country and trade
from another. `BusinessService` already derives `country_id` from the chosen
region, so the business record stays internally consistent regardless.

## Data migration

```
users.country_id = (Cameroon) WHERE country_id IS NULL
```

All 512 existing accounts are SIARC imports from a Cameroonian dataset, so
Cameroon is a statement of fact for them, not a guess.

## Testing

Feature tests, following `MultiCountrySignupTest`:

1. Both signup doors offer exactly the account types `AccountTypes::keys()` lists.
2. The wizard's presentation map covers every key — the drift guard.
3. Signing up as a buyer creates a user with `account_type = buyer`, role
   `buyer`, and **no** business record.
4. A buyer is redirected to the buyer dashboard, not the business form.
5. Signing up with a country stores it on the user.
6. A seller whose user has a country reaches the business form with that country
   preselected and its regions loaded.
7. Existing seller signup still works unchanged — the regression guard.
8. A buyer requesting the shop-creation form is redirected to the buyer
   dashboard, and a POST to it creates no business.

The full suite (669 at time of writing) and the 105-route responsive audit must
stay green.

## Deployment

Ordered so the site works after every step:

1. **Database first.** `users.country_id` added and backfilled, via a phpMyAdmin
   SQL patch, because the host has no SSH. Additive and nullable, so the running
   code ignores it.
2. **Code.** Views, `AccountTypes` usage, controller prefill.
3. **Cache rebuild** via the existing update page.

### The autoloader trap

This change adds **no new PHP class**, so the regenerated
`vendor/composer/autoload_*.php` is *not* required. That is worth stating
explicitly: on 2026-07-30 a release that did add one new class shipped without
the regenerated class list, and production — which runs
`setClassMapAuthoritative(true)` — refused to load the file even though it was on
disk. If any step of the implementation introduces a new class, the regenerated
autoloader files must ship with it.

## Risks

| Risk | Mitigation |
|---|---|
| The wizard's JavaScript couples the mobile `<select>` and the desktop cards; adding a fifth breaks the pairing | The JS iterates `input[name="account_type"]` generically, so it should hold — verified by test, not by reading |
| A buyer reaches the business form via a stale link and creates a shop they cannot administer | New guard in `create()`/`store()` rejects non-seller account types; covered by test 8 |
| Backfilling the wrong country onto real users | Only rows with `country_id IS NULL` are touched, and every existing account is a known Cameroonian import |
| Step 3 skipping breaks the shared submit path | The submit handler is unchanged; only which step submits differs. Covered by the existing-seller regression test |
