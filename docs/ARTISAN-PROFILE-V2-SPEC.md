# Artisan profile v2 — measured design spec

Source artwork: `certificates/artisan profile v2 desktop.png` (1024×1536) and
`certificates/artisan mpbile profile v2.png` (864×1821 at 2×, i.e. 432×910.5 CSS).
Measured by GD band-scan (row-luma shifts) and point sampling. The first build
read these designs "proportionally" and produced a generic page; this spec exists
so the rebuild hits the artwork.

The honesty rules are unchanged and non-negotiable: no fabricated rating, review
count, sales figure, award or trust number, no exact GPS, no payment/shipping/
guarantee promises. **What changes is the visual fidelity — the palette, the dark
hero, the gold, the card geometry — which must match the artwork exactly.** An
empty state must be styled as richly as the filled state it replaces.

## Shared palette (sampled)

| Token | Value | Where |
|---|---|---|
| Page cream | `#FCF8F5` / `#FDF9F6` | mobile / desktop page bg |
| Hero black-brown | `#0E0A03` → `#070300` | mobile hero card, right-edge artwork fade |
| Deep green (nav/pills) | `#054821` / `#02411D` | verified pill, cart button |
| Desktop navbar green | `#0A3018` band (sampled `#032309`–`#1A360D`) | main nav strip |
| Hero green tint | `#106239` | desktop hero left glow |
| Star / accent gold | `#EDA817` | stars, trust labels |
| Deep gold (seal, sell) | `#C8860B` / `#C19B10` | SELL button, footer seal |
| Alert red | `#CC060E` | notification badge |
| Footer green-black | `#011E12` | desktop footer |
| Trust panel black | `#0B0B07` / `#070805` | inset trust cards, both designs |

## Mobile bands (CSS px = artwork/2)

| Band | y (CSS) | Notes |
|---|---|---|
| Status bar + app bar | 0–81 | white; menu, logo lockup, search, bell **with red badge only when a real unread count exists**, heart, cart-icon-as-quote link |
| Hero card | 81–317 | dark `#0E0A03`, r≈20, mask artwork right half fading in; portrait Ø118 with 3px gold ring + green VERIFIED pill overlapping bottom; flag+country, name+tick, title, 4 meta rows (icons gold), inset trust panel `#0B0B07` r≈14 split in two columns with gold uppercase labels |
| Action row | 322–367 | white card, 4 columns w/ vertical hairlines: Message, Visit Workshop (gold active), Call, Follow |
| Certificates | 375–512 | white card; header row w/ green shield icon + "View All →"; horizontal scroll of shield cards: **iridescent shield artwork** (CSS gradient treatment, no security caption), code, name, date, green Verified pill |
| Products | 521–695 | **4-col grid** (corrected 2026-07-28 — this line previously said 2-col and was wrong; the artwork plainly shows four cards across, verified on a magnified crop); image tile r≈14 with heart top-right; name, category, price bold, green cart-square (quote link) |
| Tabs + About | 702–853 | 5 tabs with icons, active green underlined; about prose + facts list (icon, label, value) |
| Bottom nav | 857–910 | deep green `#02411D`; 5 items, centre Verify = gold-ringed disc Ø62 raised ~30px with the brand mark |

## Desktop bands (artwork 1024 wide; build at 1280 container scaled proportionally)

| Band | y | Notes |
|---|---|---|
| Topbar | 0–64 | white; logo lockup left, search + "All" select centre, Explore/Map/Verify/Wishlist/Cart icon-links, avatar |
| Navbar | 64–96 | deep green strip; ARTISANS…ABOUT US left, gold **SELL ON ARTISANHUB237** button right |
| Breadcrumb | 96–128 | cream |
| Hero | 128–330 | full-width dark card: left→right = portrait (gold ring) · name block (VERIFIED pill above name, name+tick, title, gold-icon meta row, craft tag chips) · mask artwork fading · **floating trust card** overlapping the hero's right edge (`#070805`, r≈14): TRUST SCORE gold label, big figure, stars, count, then CONTACT (solid green), VISIT WORKSHOP (outline), FOLLOW (ghost) |
| Info row | 342–600 | 3 cards ≈ 4/5/3 ratio: identity table (gold icons, colon-aligned) · about + 4 stat tiles · workshop location card w/ **map-styled placeholder area** (no exact GPS, no pin at true coords) + social icon dots |
| Certificates | 620–770 | header + 6 shield cards in a row, same iridescent treatment, number, date, verified pill, download icon |
| Products | 780–1040 | header + 6-across grid; image, name, category, price, gold star **only when real reviews exist**, VERIFIED pill where a COA exists |
| Reviews / Stats / Achievements | 1060–1290 | 3 cards ≈ 5/4/3: reviews summary (big figure + bars — **empty-state styled identically**), statistics list (icon rows; untracked rows keep the icon and show "Not tracked"), achievements list (from `business_awards`, styled gold-icon rows; honest empty state) |
| Trust bar | 1300–1365 | cream band, 5 icon+title+caption columns — **true claims only** (verified artisans, checkable certificates, provenance recorded, artisan paid directly, support artisans) |
| Footer | 1380–1536 | `#011E12`; gold AHCA seal left + brand paragraph, QUICK LINKS ×2 cols, CONNECT (social dots, phone, email), **SCAN TO VERIFY THIS ARTISAN** QR (real, `qrcode.min.js`, encodes the artisan's real verification URL); bottom strip: copyright, language select, country |

## Iridescent shield treatment

The artwork's certificate shields are holographic-foil renderings. On screen this
is a **visual treatment**: layered conic/linear CSS gradients (violet→cyan→gold)
inside a shield-shaped clip, brand mark centred. It must never be captioned as a
hologram or security feature — `docs/PRINT-SECURITY-SPEC.md` governs claims.

## Desktop build measurements (as shipped, 1280 container)

- Hero card: r16, bg `#0E0A03`, left radial green glow `#106239` at 40% opacity,
  right-half CSS carve pattern (gold/umber repeating diagonals at .28 opacity)
  when no cover image exists; content min-height 252px, padding 28px, right
  padding 292px to clear the trust card.
- Portrait: Ø176, 3px `#C9942E` ring. VERIFIED pill: `#C8860B` rounded-full.
- Floating trust card: absolute right-20/top-20, hangs 24px past the hero
  bottom (`-bottom-6` with hero `mb-10`), w236, r13, bg `#070805`, 1px
  `#C9942E`/80 border, drop shadow; buttons pinned to the card foot
  (CONTACT solid `#14652F`, VISIT outline, FOLLOW rounded-full ghost).
- Identity rows: 128px label column, explicit `:` separator glyph, gold icons.
- Workshop map tile: 132px tall, r10, pure-CSS street grid (no coordinates in
  markup or URLs); VIEW ON MAP links to a maps *search of the coarse location
  string only*. Social channels render as Ø36 coloured discs.
- Iridescent shield: 52×60 clip-path shield, conic violet→cyan→gold + soft-light
  overlay + diagonal sheen (`.ap-shield` in show.blade.php); dimmed at
  saturate(.25)/opacity(.55) for a register that has issued nothing.
- Chrome: per the standing rule, the canonical `directory-header` /
  `directory-footer` are kept; the artwork's topbar/navbar/footer (and the
  footer QR) are NOT rebuilt in-page. Everything between breadcrumb and trust
  bar is replicated to the artwork.

## Verification checklist for the build

- Headless screenshot at 1280 (desktop) and 489-floor (mobile), read and compared
  band-by-band against the artwork.
- `ArtisanProfileFamilyTest`, `ArtisanProfilePageTest`, `ArtisanProfileMobileTest`,
  `UiConsistencyTest`, `BrandAssetTest` all green — none may be weakened.
- Both languages; `$lang` passed to every `ArtisanProfile` call.

---

# MEASURED TYPOGRAPHY, SPACING AND COLOUR — authoritative

Owner instruction 2026-07-28: *"same colors, same gaps, same fonts, font size,
gaps, spacing, styling, etc. everything. nothing left out."*

Everything below was measured off `certificates/artisan profile v2 desktop.png`
(1024 wide) by cap-height measurement on 4× magnified crops and by dominant-colour
sampling. **The site container is 1280, so artwork px × 1.25 = site px.** Both
columns are given. Do not round away from these numbers.

## Typeface

The artwork's face is **Poppins**, which `tailwind.config` already sets as the
site `sans`. Verified by rendering the artwork's own name string in Poppins,
Montserrat and Nunito Sans at the artwork's size and comparing letterforms
against a magnified crop. **The family is not the gap — the sizes, weights and
letter-spacing are.** Do not swap the font.

## Type scale

| Element | artwork px | **site px @1280** | weight | notes |
|---|---|---|---|---|
| Artisan name | 29.5 | **37** | 700 | tracking ≈ -0.01em |
| Title / subtitle under name | 14 | **17** | 400 | |
| VERIFIED pill text | 9 | **11** | 700 | uppercase, tracking .08em |
| Card section heading (IDENTITY, CERTIFICATES & VERIFICATIONS…) | 10 | **13** | 700 | uppercase, tracking .06em |
| Identity row label | 10.4 | **13** | 500 | uppercase |
| Identity row value | 10.4 | **13** | 600 | |
| Stat tile number | 15.6 | **19.5** | 700 | |
| Stat tile label | 7 | **9** | 400 | |
| Reviews mean figure | 29 | **36** | 700 | dark green |
| "Based on N reviews" | 8.5 | **10.6** | 400 | |
| Rating-bar label ("5 Stars") | 9.7 | **12** | 400 | |
| Product name | 9 | **11** | 600 | |
| Product category | 7.5 | **9** | 400 | |
| Product price | 11 | **14** | 700 | |

## Vertical rhythm

| Measure | artwork px | **site px @1280** |
|---|---|---|
| Identity row pitch (baseline to baseline) | 20.25 | **25** |
| Rating-bar row pitch | 19 | **24** |
| Rating-bar height | 5.5 | **7** (rounded-full) |
| Star glyph size | 10 | **12.5** |

## Horizontal grid — measured gutters

Found by column-projection: a gutter is a column that is page-cream for ≥93% of
the band's rows.

| Band | columns | gutter (artwork) | **gutter @1280** |
|---|---|---|---|
| Page margin | — | 20 each side | **25** (content 1230) |
| Products | 6 | 6 | **7.5** → `gap-2` |
| Certificates | 6 | ~13 | **16** → `gap-4` |
| Info row | 3 | 8 / 7 | **~10** → `gap-2.5` |

Info-row card widths at 1024: 323 / 339 / 307 — near-equal thirds with a
slightly wider middle card, **not** the 4/5/3 ratio assumed earlier.

## Colours — dominant-sampled, exact

| Token | Value |
|---|---|
| Page cream | `#FCF9F6` |
| Card surface | `#FCFAF6` — cards are *barely* lighter than the page; **the border does the separating, not the fill** |
| Trust-bar band | `#F9F6EF` |
| Navbar green | `#002A0D` |
| Footer green | `#011E13` |
| Hero black | `#0E0A03` |
| Floating trust panel | `#070805` |
| VERIFIED pill gold | `#D3B030` |
| Star gold / rating-bar fill | `#E29A08` |
| SELL button gold | `#925104` → `#996A0D` |
| Product VERIFIED badge green | `#003712` |
| Contact button green | `#14652F` |
| Mobile cart square / bottom nav | `#02411D` |
| Notification badge red | `#CC060E` |

A common error already made once on this page: painting card fills a brighter
white than the page. In the artwork they are within two levels of each other.

## Mobile type — the ÷2 conversion does not hold

Measured ink extents on the 864px mobile artwork (cap height ÷ 0.71 = font size):

| Element | cap px | font @864 | **font at 432 CSS (÷2)** |
|---|---|---|---|
| Hero name | 25 | 35 | **17.5** |
| Hero title | 13 | 18 | **9** |
| Section head (FEATURED PRODUCTS) | 12 | 16.5 | **8.2** |
| Product name | 9 | 12.5 | **6.2** |
| Product category | 8 | 11 | **5.5** |
| Tab label | 9 | 12.5 | **6.2** |

Body copy at 5.5–6.2px is not shippable — iOS and Android both put their minimum
around 11–12px, and this is a marketplace read by buyers on phones.

The desktop artwork does **not** have this problem: its 1024→1280 conversion
yields a 37px name and sensible body sizes throughout. The inconsistency is
specific to the mobile mockup, whose type is drawn roughly half the size its
frame implies. Preserving the *ratios* between elements (hero name ≈ 2.8× the
product name, section heads ≈ 1.3×) is therefore the transferable part; the
absolute sizes need a decision recorded here before the mobile view is final.
