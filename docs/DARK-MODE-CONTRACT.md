# Dark mode — the contract

One palette, defined once, consumed everywhere. Three agents build against this
file in parallel; none of them invents a colour.

## Strategy

Tailwind **`darkMode: 'class'`**, toggled by a `dark` class on `<html>`.

Not `media`: the platform must honour an explicit user choice, and a member who
picks light on a phone that is in dark mode has to get light.

The site loads Tailwind from a CDN and **46 views carry their own inline
`tailwind.config`**. Every one of them needs `darkMode: 'class'`, or dark
variants silently do nothing on that page. This is the single most likely way
for this work to look finished while being broken — a page renders fine in
light, nobody notices it never switches.

## The palette

Derived from the platform's own artwork rather than a generic slate ramp. The
designs already contain a dark language — the profile hero `#0E0A03`, its inset
trust panel `#070805`, the footer `#011E13`, the navbar `#002A0D` — and dark
mode is that language extended to the whole platform. A neutral grey dark mode
would read as a different product.

| Token | Light | Dark | Use |
|---|---|---|---|
| `bg` | `#FCF9F6` | `#0A0C09` | page |
| `surface` | `#FCFAF6` | `#12150F` | cards, panels |
| `surface-2` | `#FFFFFF` | `#1A1E16` | raised, inputs, table stripes |
| `inset` | `#F9F6EF` | `#070805` | inset panels, wells |
| `border` | `#E7E2D8` | `#262B21` | hairlines, card edges |
| `border-strong` | `#D5CEC0` | `#39402F` | inputs, dividers that must read |
| `ink` | `#1A1A17` | `#F3EFE7` | primary text |
| `ink-2` | `#57574E` | `#B4B5A6` | secondary text |
| `ink-3` | `#7C7C70` | `#868778` | muted, captions, placeholders |
| `brand` | `#14652F` | `#2E9250` | primary buttons, links |
| `brand-ink` | `#FFFFFF` | `#04150A` | text on `brand` |
| `brand-deep` | `#02411D` | `#0C3B1E` | nav, bottom bar |
| `gold` | `#E29A08` | `#E9A81E` | stars, rating bars, accents |
| `gold-ink` | `#7A4E02` | `#EDB33A` | gold-coloured *text* (never raw gold on dark) |
| `danger` | `#CC060E` | `#F0555C` | errors, destructive |
| `success-bg` | `#DFF3E4` | `#0C3D1D` | verified pills |
| `success-ink` | `#003712` | `#8BDCA6` | text on `success-bg` |

### Added by the public-pages pass

Two pairs the table above could not satisfy. Both were measured, not eyeballed.

| Token | Light | Dark | Use | Measured |
|---|---|---|---|---|
| `brand-link` | `#14652F` | `#339B56` | green **text** — links, secondary button labels | 4.80:1 on `surface-2`, 5.23:1 on `surface`, 5.58:1 on `bg` |
| `border-field` | `#D5CEC0` | `#68715B` | the boundary of an actual **control** (input, select, bordered button) | 3.30:1 on `surface-2`, 3.60:1 on `surface` |

Why: `brand #2E9250` is **4.31:1** on `surface-2` — under AA for body text — so the
button *fill* stays `#2E9250` (with `brand-ink` on it, since white on that fill is
only 3.93:1) and green *labels* lighten to `brand-link`. And `border-strong
#39402F` is **1.57:1** on the input fill, which fails WCAG 1.4.11's 3:1 for a
control boundary. `border #262B21` keeps its 1.27:1 for card hairlines, which are
decorative — a card is also identified by its fill (`surface` vs `bg`), so 1.4.11
does not apply to them.

## Non-negotiables

**1. Certificates never go dark.** Every view under the certificate routes is a
*document*: it is printed, it carries `@page A4` rules, and its colours are
specified per type in `config/certificate_types.php`. A dark certificate is
either a wrong document or a wasted toner cartridge. These views must render
light regardless of the toggle — force it, do not merely omit dark classes, or
an inherited `dark:` on a wrapper will leak in.

**2. No flash of the wrong theme.** The class must be on `<html>` *before first
paint*, from an inline blocking script in `<head>` — not from a deferred bundle,
not from Alpine, not from `DOMContentLoaded`. A white flash on every navigation
is worse than no dark mode.

**3. Contrast is verified, not eyeballed.** Every foreground/background pair in
the table above must meet **WCAG AA — 4.5:1 for body text, 3:1 for large text
and UI boundaries**. The values above were chosen to pass; if a page needs a
pair that is not in the table, compute the ratio before shipping it, and add the
token here rather than inlining a one-off hex.

**4. `dark:` variants only, never a second stylesheet.** One markup tree. A
parallel dark template drifts from the light one within a week.

**5. Images and artwork.** Photographs are not re-tinted. Logos use
`brand_asset()` — check whether a light-on-dark variant is needed rather than
filtering the existing mark. Never `filter: invert()` a brand mark.

**6. The ui-kit is the source of truth.** `pages/partials/ui-kit.blade.php`
defines every field, card and button, and `UiConsistencyTest` fails on drift.
Dark variants go into the ui-kit **first**; pages inherit them. A page that
hand-rolls its own dark card is drift and will fail the check.

## Toggle behaviour

- Default: follow `prefers-color-scheme` until the member chooses.
- Once chosen, persist and honour that choice everywhere.
- Persist to `localStorage`; the control appears in the site chrome and in the
  dashboard/admin shells.
- Respect `prefers-reduced-motion` — no cross-fade for anyone who asked for less.

## Files currently owned by other agents — DO NOT EDIT

Four agents are mid-flight on the artisan profile rebuild. Editing these will
lose their work:

- `resources/views/pages/businesses/show.blade.php`
- `resources/views/pages/businesses/partials/show-mobile.blade.php`
- the shared `directory-header` / `directory-footer` partials
- `app/Console/Commands/` and `public/images/demo/`

These get their dark variants in a follow-up pass once those agents land. Note
in your report that they are outstanding, so the gap is recorded rather than
assumed done.
