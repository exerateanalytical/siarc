# Artisan profile — design spec

The two designs are separate documents, not two widths of one document, and are
built as siblings. `pages/businesses/show.blade.php` is the desktop page and
includes `pages/businesses/partials/show-mobile.blade.php` inside a `lg:hidden`
wrapper; the desktop half sits in `hidden lg:block`. Both halves are present in
the DOM at once, which matters when writing assertions.

---

## Desktop

Source: `certificates/artisan profile v2 desktop.png`, 1024 × 1536.
Implemented by `resources/views/pages/businesses/show.blade.php`.
Route: `FrontendController::businessShow` → `/galerie/entreprises/{slug}`.
Test: `tests/Feature/ArtisanProfilePageTest.php` — 12 tests, 75 assertions.

### Why this one was not measured with GD

The usual scan used for every certificate in this repo (see
`docs/COA-DESIGN-SPEC.md`) does not work on this PNG. The design is a
photographic composite with heavy film grain: a single-pixel row scan across the
card band returned **926 colour runs in 1024 pixels**, and the nominally flat
cream page background varies between `#F8F3E8` and `#FDFAF6` within a few pixels.
There are no clean edges to snap to.

That is a real difference in kind from the certificates, which are flat vector
posters on a fixed canvas. This page is also not a fixed canvas — it is a
responsive document whose container stretches — so the figures below are read
proportionally and expressed as a grid rather than as absolute offsets.

### Canvas and grid

| Property | PNG (1024 px) | Built |
|---|---|---|
| Content gutter | 20 px each side (≈2%) | `px-4 sm:px-6` |
| Content width | 984 px | `max-w-[1240px]` |
| Section gap | 20 – 24 px | `mt-5` … `mt-7` |
| Card border | 1 px warm grey | `--ui-border-card` `#EFEBE2` via `ui-card` |

The container is widened from 984 px because the PNG is a 1024 px-wide
*rendering* of a desktop page, not a 1024 px-wide page. Holding the design's
width on a 1440 px monitor would centre a narrow column in a sea of cream.

### Bands, in the design's order

| # | Band | PNG columns | Built |
|---|---|---|---|
| 1 | Breadcrumb | full | `flex flex-wrap` |
| 2 | Hero | portrait / name / trust panel | `flex`, panel `w-[228px]` |
| 3 | Identity · About · Workshop | 340 / 340 / 300 | `grid-cols-12` → 4 / 5 / 3 |
| 4 | Certificates strip | 6 across | `xl:grid-cols-5` (five registers) |
| 5 | Products | 6 across | `xl:grid-cols-6` |
| 6 | Reviews · Statistics · Achievements | 400 / 280 / 330 | 5 / 4 / 3 of 12 |
| 7 | Trust bar | 5 across, cream band | `xl:grid-cols-5`, `#F8F3E7` |
| 8 | Footer | — | `pages.partials.directory-footer` |

### Palette

| Role | Built |
|---|---|
| Hero ground | `#17130C` |
| Gold — eyebrows, portrait ring, section glyphs | `#C9942E` |
| Trust panel ground | `#1E1809`, 1px `#C9942E` |
| Craft tag | `#231B0E` fill, `#EBC989` text, `#6B5426` border |
| Page ground | `#FFFDF9` |
| Trust bar band | `#F8F3E7`, border `#EFE7D4` |
| Stated absence (`.ap-absent`) | `#A8A296`, italic |

### Type

| Element | PNG | Built |
|---|---|---|
| H1 artisan name | ~34 px bold | `text-[34px] font-bold` |
| Section eyebrow | ~13 px caps tracked | `.ap-sec-title`, 13 px / `.055em` |
| Card body | ~12.5 px | `text-[12.5px]`, `leading-[1.75]` on prose |
| Table label | ~11 px caps grey | `text-[11px] uppercase #8A857A` |
| Certificate number | ~10.5 px mono | `font-mono text-[10.5px]` |
| Trust score figure | ~38 px bold | `text-[38px] font-bold` |

Fields, buttons, cards and pills all come from `pages/partials/ui-kit.blade.php`.
This page defines exactly three classes of its own — `.ap-sec-title`,
`.ap-sec-link`, `.ap-absent` — and no field or button styling, so
`UiConsistencyTest` stays green.

### Data

Every figure comes from `App\Support\ArtisanProfile`, through one guarded
closure: a missing or mid-edit class degrades the page to "nothing is known"
rather than throwing a 500 at a visitor, and the fallbacks are the same empty
shapes the class returns for a shop with no data — emptier, never wronger.

Each statistic is `['value','basis','known']` and renders in one of two ways,
decided once at the top of the template so no block can drift:

- **known** — the figure, `basis` on the `title` attribute;
- **not known** — *Not tracked* / *Non suivi* in `.ap-absent`, `basis` on `title`.

**Never zero.** A counter reading `0` is a statement about this artisan's
business; a counter the platform does not keep is a statement about the platform.
They look identical on a screenshot and are entirely different claims.

Every call passes `$lang`. These methods return prose — why a figure is unknown,
the name of a certificate, why a piece carries no price — and that prose is most
of what a reader reads, so a dropped argument surfaces as French sentences on the
English page. Asserted, including Carbon's `translatedFormat`, which follows the
application locale rather than the page's and leaked "juillet" on first build.

### Trust bar — three of five design panels removed

`config/legal.php` is the authority contradicting the design, and is quoted at
each point of removal in the template itself.

| Design panel | Why it is gone |
|---|---|
| **SECURE PAYMENTS** — "100% secure transactions" | The platform does not receive the price of the sale and offers no escrow; settlement is direct between buyer and seller. It describes a transaction the platform never sees. |
| **BUYER PROTECTION** — "Money-back guarantee" | The operator is not party to the sale, holds no funds, and has already written down that it *cannot recover funds paid outside the platform*. The most damaging line on the page, because a buyer relies on it at exactly the moment they decide to send money to a stranger. |
| **WORLDWIDE SHIPPING** — "Safe & reliable delivery" | "Nous ne fabriquons, ne stockons, n'inspectons et n'expédions aucun produit." |

The band is kept — it does real work, closing the page on why any of this is
trustworthy — and refilled with five things that are true and checkable:

1. **Verified artisans** — identity and trade documents are received and checked
   before a profile is marked verified.
2. **Checkable certificates** — every number shown can be verified
   independently, without going through the artisan.
3. **Provenance recorded** — pieces on the register keep a dated history.
4. **The artisan is paid directly** — the sale is a contract between you and the
   artisan; the platform is not a party to it, does not receive the price and
   holds no funds. *(The honest counterpart of "secure payments": not a
   guarantee, a statement of who holds the money, which is what a buyer needs.)*
5. **Support artisans** — contact and negotiation happen directly.

Followed by one plain sentence rather than left implied by five reassuring icons:
the company is private with no ministerial affiliation, and verification is a
document check — not a warranty of the goods, of an order being fulfilled, or a
financial guarantee. It links to the disclaimer.

### Everything else the design claims that this page does not

| Design element | Rendered instead |
|---|---|
| Trust score `4.9 / 5` and five stars beside the face | `trustScore()`'s figure over its own maximum, with the full points-and-basis breakdown reachable in a `<details>`. Unknown reads *Not yet assessed* — not `0`, which would read as an assessment the artisan failed. |
| `(128 Reviews)`, "Based on 128 reviews", distribution `104/18/4/1/1` | `business_reviews` is empty: no mean, no stars, no bars. A stated absence plus the register's reason. The bars and star row exist in the template and light up the moment rows exist. |
| Star rating under every product card (`4.9 (26)`) | Nothing. Reviews attach to a **business**, never a product; there is no product-level rating in the schema, and spreading the shop's mean across its pieces would rate work nobody rated. Replaced by a *Certified* pill when the piece has a certificate of authenticity on register. |
| `128 Products Sold`, `96 Happy Customers`, `98% Response Rate`, `Last Active: Today`, repeat buyers | *Not tracked*, each with the register's reason on hover. The platform records no orders, no customers and no message response times. |
| `18 Countries Reached` | Rendered from `countries_reached`, which **is** measurable — zero there is a real count of pieces that have travelled, not a gap. The distinction is asserted in the test. |
| `18+ Years Experience` as a fixed string | Arithmetic from `year_established`, or the row is absent. |
| SIARC Excellence Award, National Craft Excellence Award (ministry), UNESCO Craft Recognition, African Heritage Expo | `business_awards` is empty. These are honours conferred by real external bodies of which the platform keeps no register; printing one invents a national distinction for a named person. Empty state naming no organisation. This project has already had external honours stripped from its certificates once. |
| Exact GPS `4.0480° N, 9.7679° E` and a pinned street map | Never rendered — not as text, not in a map URL, not in a data attribute. Town and region only. A workshop is usually somebody's home; the passport already withholds these and a public profile is the more exposed surface of the two. See `docs/ahts/20-conflicts.md` item 9. |
| Phone `+237 6 91 23 45 67`, email `info@mbatchouwoodstudio.com` | Only what the business record holds. A plausible fabricated number sends a buyer to a stranger. |
| Three paragraphs of first-person copy about Bamoun heritage | The artisan's own description, or a stated absence. The design's copy would print word-for-word on every artisan, most of whom are not Bamoun and none of whom wrote it. |
| Six certificate shields, all issued, all green | The five issuing registers. A register that has issued nothing says so in its own words — a greyed shield and a certificate never applied for look alike and mean different things. |
| Hero background artwork (carved masks) | Only this shop's own `cover_image`. Stock artwork would put another artisan's work behind this artisan's face. |
| Twelve-row identity table, every row filled | A row exists only if the register holds the value. Nationality and "workshop visits" have no column on `businesses` at all. A row reading "—" implies we asked and were refused. |
| Featured products topped up from other vendors | Only this artisan's own published pieces. The listing controller's top-up is reasonable on a directory page and is misattribution under a personal portrait. |

### Note on the test's untracked-counter assertion

The page uses two counter shapes — a tile with the figure above its caption, and
a table row with the figure after its label — so a directional text window cannot
tell one counter's value from its neighbour's. The first two attempts failed for
exactly that reason. The assertion is therefore made on the enclosing element:
an untracked figure must carry `.ap-absent` and contain **no digit at all**.

---

## Mobile

Source: `certificates/artisan mpbile profile v2.png`, 864 × 1821 px at 2×, i.e.
**432 × 910.5 CSS px**. All figures below are CSS px, measured off the PNG with
GD colour scans (vertical scan at x = 30, horizontal scans across each band).

Implemented by `resources/views/pages/businesses/partials/show-mobile.blade.php`.

### Palette

| Token | Value | Where |
|---|---|---|
| page background | `#FDFAF6` | body behind the cards |
| card surface | `#FFFFFF`, 1px `#EFEBE2`, radius 14 | every panel |
| hero | `#0B0B0A` | dark hero card |
| bottom nav | `#05311A` (sampled `#002C10`–`#063 31B`) | fixed nav |
| gold | `#E3A33D` | portrait ring, hero meta icons, verify disc |
| green | `#157A43` / `#14652F` | action icons, active tab, cart-slot button |
| verified pill | `#0F4824` fill, gold hairline border | over the portrait |

### Vertical bands (measured → achieved)

| Band | PNG (CSS px) | Built |
|---|---|---|
| status bar | 0 – 40 | live clock + signal/wifi/battery, same height |
| top app bar | 40 – 80 | 36px logo, 24px icons |
| hero card | 81.5 – 316.5 (h 235) | `min-height: 235px`, radius 14, 10px side gutter |
| action row | 322 – 367 | 4 equal columns, 1px dividers |
| certificates | 375 – 512 | header 12/12/8, 103px shields in an x-scroll strip |
| featured products | 521 – 695 | 99px cards, 99px square images, x-scroll strip |
| tabs + about | 702 – 853 | 5 tabs, 2px active underline, 2-column About panel |
| bottom nav | 857.5 – 910.5 (h 53) | fixed, 5 columns, centre disc 62px raised 30px |

### Hero internals

- Portrait circle Ø 118, 3px gold ring, left gutter 10 + 12 padding.
- VERIFIED ARTISAN pill overlaps the portrait's foot, centred on it.
- Name 21px/800; title 13px; meta rows 12.5px on a 17px pitch with 15px gold icons.
- Trust panel: radius 10, `rgba(0,0,0,.55)` on a `rgba(227,163,61,.55)` hairline,
  two columns split by a gold hairline.

### Deviations from the PNG, and why

- **Trust panel width.** The design's panel is 268px wide and centred inside the
  hero (x 83 – 351). It is built full-width inside the card padding instead. The
  design fits "92 /100" and "4.9 /5" in that space; the honest content is two
  sentences of prose, which does not set in 134px columns.
- **Cart icon removed** from the app bar. There is no cart table and the operator
  is not party to the sale.
- **Notification badge** is drawn only from the reader's unread `user_notifications`
  rows; the design's hardcoded `3` is gone.
- **Per-product rating removed.** The design prints `4.9 (28)` under each card.
  Reviews attach to a business in this schema, never to a product.
- **Product cart button** is a quote-request link, which is the transaction the
  platform actually carries.
- **ACHIEVEMENTS → DISTINCTIONS / AWARDS**, backed by `business_awards`, which is
  empty; the design's SIARC and UNESCO honours are not rendered.
- **STATS** prints `['value','basis','known']`; a `known => false` statistic reads
  "Non suivi / Not tracked" with its basis, never `0`.
- **Coarse location only**: `gps_lat` / `gps_lng` are never emitted.
- Sections whose register is empty print a sentence saying so rather than
  disappearing, so the reader can tell "nothing on file" from "not loaded".

### Harness note

Headless Chrome on this machine floors the viewport at ~489px, so a
`--window-size=430` run lays out at 489 and crops. Screenshot at 489 to judge.
