# Certificate of Authenticity — measured design spec

Source: `certificates/certificate of authenticity.png`, **1024 × 1536** (2:3).

Everything below was measured off the PNG by scanning for card strokes, fill
colours and text bounding boxes (`docs/` has no image tooling; the scans were
one-off scripts). All values are in source pixels, so the implementation renders
at a fixed 1024px canvas and scales the whole sheet as one — that is what keeps
it pixel-accurate at any viewport instead of reflowing into a different document.

## Frame

| Element | Measurement |
|---|---|
| Outer green border | 16px, `#023415` |
| Gold rule | ~6px inset at x 17–22, `#E5A82E`→`#F9D588` |
| Inner cream page | x 27–997, `#FEFDF7` |
| Kente ornament | top and bottom edges, corners heavier |

## Header — y 27–314

| Element | Box |
|---|---|
| Logo lockup | x 28–790, y 27–230 |
| Tagline "AUTHENTIC. CERTIFIED. CAMEROONIAN." | under wordmark, ~11px, tracking .22em |
| QR card | x 803–941, y 75–235 |
| Status badge | x 802–941, y 236–314, dark green, radius 10 |

## Title block

| Element | Box | Type |
|---|---|---|
| CERTIFICATE OF AUTHENTICITY | x 100–706, y 248–280 | serif 800, cap-height 32 → ~46px |
| Gold ribbon | x 200–624, y 285–320 (h 35) | notched ends |
| Intro, 2 lines | y 335–364 | ~15px, centred |

## Meta strip — card x 46–976, y 386–499 (h 113)

Three columns, icon-left, at x **76 / 403 / 630**. Row 1 y 396–440, row 2 y 452–492.
Labels cap-height 7 (~10px, uppercase, tracked); values cap-height 9 (~13px).

## Body row — y 514–913

| Column | Box |
|---|---|
| Product information | x 45–378 (w 333), header band y 514–547 (h 33) |
| Photograph | x 388–650 (w 262); main y 517–822, thumbnails y 830–913 |
| Creator | x 657–978, y 517–737, header bottom y 549 |
| Digital identity | x 657–978, y 745–913, header bottom y 771 |

Row grid: label x 61–137, value x 182 (left column); label x 675, value x 789 (right column).

## Ownership row — y 925–1053

| Card | Box |
|---|---|
| Ownership information | x 45–343 |
| Ownership history | x 352–750, header bottom y 960; table columns x 371 / 457 / 550 / 640 |
| Product status | x 758–978 |

## Authenticity features — card x 49–975, y 1069–1183

Title x 406–613 with gold rules either side. Nine icons, centres at
x **96 / 194 / 297 / 395 / 491 / 586 / 698 / 808 / 921**; captions y 1145–1175.

## Bottom row — y 1201–1429

| Element | Box |
|---|---|
| Seal | circle centred ~x 155 / y 1300, d ≈ 190, with tricolour ribbons |
| Authenticity statement | x 268–665 |
| Disclaimer | x 681–978 |
| Signature rule | x 361–546, y 1393 |

## Footer band — y 1445–1536 (h 91)

Dark green. Serif italic gold tagline y 1455–1489 (~26px); social icons, URL and
handle below; Cameroon map silhouette at the right; kente blocks at both ends.

## Palette

| Token | Value |
|---|---|
| Deep green | `#023415` / `#0A3A22` |
| Footer gradient | `#0E4A2B` → `#052616` |
| Gold | `#E5A82E`, light `#F9D588`, deep `#A6761F` |
| Cream page | `#FEFDF7` |
| Card | `#FFFFFF`, stroke `#E8E0CB` |
| Card header band | `#F3EFE2` → `#EDE7D6` |
| Red (kente/flag) | `#B4141B` |
| Ink | `#1D1B16`, muted `#6B6659` |

## Deviations from the PNG, and why

Two blocks in the artwork name things the platform does not do, and a
certificate that prints them is making a promise nobody can keep:

- **AI Visual Fingerprint** and **Watermark Reference** — no model, no
  watermarking pipeline. Omitted; the Digital Identity block keeps its measured
  geometry and is filled with values that are really computed (content hash,
  perceptual image hash, HMAC signature, verification PIN).
- **Collection** and **Edition** rows — no such fields exist. The Product
  Information block is row-driven, so it fills to the same height from the data
  the artisan actually entered.

Everything else — every card, rule, ornament, icon slot, colour and type size —
follows the measurements above.
