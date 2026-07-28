# Export Authenticity Certificate (EAC) — design specification

View: `resources/views/pages/certificate-export-authenticity.blade.php`
Route: `GET /certificat-export/{ref}` (`export.certificate`), `?lang=fr|en`
Test: `tests/Feature/ExportCertificateTest.php`
Source artwork: `certificates/EXPORT AUTHENTICITY CERTIFICATE (EAC).png`

## What the artwork is

The PNG is a composite, not a page: 1024 × 1536 with sheet 1 down the left
(x 0 – 612, full height) and sheets 2 and 3 stacked on the right (x ≈ 685 – 1024,
split at y ≈ 784, where the navy "PAGE 3 – VERIFICATION & COMPLIANCE" bar
begins). The two right-hand sheets are reproduced at a smaller scale than the
left one, so no single scale factor recovers source pixels from it. Measured
values that *are* reliable off it are the palette and the structural grammar,
and those are what the build follows:

| Measured | Value |
| --- | --- |
| Composite canvas | 1024 × 1536 |
| Sheet 1 extent | x 0 – 612, y 0 – 1440; navy footer band from y ≈ 1424 |
| Sheets 2 / 3 extent | x 685 – 1024, split at y ≈ 784 |
| Bezel navy | sampled `#052541` at (60,150) |
| Paper | sampled `#FCF6EC` at (700,60) |
| Frame | gold ornamental band between bezel and paper |
| Section heads | navy bars, gold letter chip, uppercase caption — A, B, C… |

## Achieved

Each sheet is drawn on its own 1024 px canvas: a 9 px navy bezel, a 22 px
ornamental gold frame, and a 961 px cream page — the same skeleton as
`certificate-ownership-transfer.blade.php`, so the family reads as a family.
All three are stacked inside one `.coa-page` and scaled as a single unit by the
script at the foot of the view. Per-sheet scaling was rejected: two pages of one
dossier at different sizes on a narrow screen is the specific thing a multi-page
document must not do.

| Element | Artwork | Achieved |
| --- | --- | --- |
| Sheet width | 1024 (sheet 1) | 1024, paper 961 |
| Bezel / frame | navy + gold band | 9 px `#041B33`, 22 px gold repeat, `#C9942E` hairline |
| Section heads | navy bar + letter | 22 px bar, 14 px gold letter chip, 9.5 px caption |
| Body type | small ruled label/value | 8.5 px `.kv` rows, hairline `#EFE7D5` |
| Page 1 | header, identity, QR, parties, score, ticks, timeline | same, sections A–K |
| Page 2 | specs, provenance, compliance, shipping, risk | same, sections A–F |
| Page 3 | documents, audit trail, standards, security legend | same, sections A–F |
| Classification band | 40 px EAC band | `partials/certificate-band` with `$code = 'EAC'`, on **every** sheet |

Every sheet carries the certificate number and `Page N of 3` in its header strip
and again in its foot. That repetition is the point of a dossier: a page that
arrives on its own must still say which consignment it belongs to and how much
of the document is missing.

`break-after: page` between sheets, `@page A4 portrait`, so printing yields
three pages.

## Showing the working

The register (`app/Support/ExportRegister.php`) returns a `basis` phrase with
every readiness category and every risk line, and reports `max = 0` for anything
it could not judge. The view is where that design either survives or dies.

- A category with `max = 0` renders as a dashed slate **Not assessed** chip with
  its basis beneath it. It never renders as `0 / 10` and is never dropped.
  Rendering it as a zero would report an inspection failure nobody carried out;
  dropping it would make an absence indistinguishable from a pass.
- The percentage is captioned as being over the assessable categories only, so
  the denominator is not read as the theoretical total.
- Risk has four appearances, not two: `low` green tick, `medium` amber triangle,
  `high` red octagon, `unassessed` a **slate chip with a dashed border and a
  dashed-circle glyph**. The test asserts the unassessed row carries neither the
  green hex nor the low wording.
- Expiry beats status. If `expires_at` is past, the chip reads EXPIRED whatever
  the status column says — the column records where the consignment got to, the
  date records whether the document may still be relied on.

## Omitted, and why

Physical or hardware properties a screen and a home printer cannot carry, all of
which appear on the artwork:

- rainbow holographic shield, embossed gold seal, UV reactive artwork, ghost
  watermark, latent image, invisible watermark, dynamic QR code, NFC chip,
  "anti-copy" as a *claim* (the screen itself is rendered, uncaptioned),
  GS1-compatible barcode structure.

Acts by parties that have not occurred:

- the five handwritten signatures (artisan, owner, exporter, reviewing officer,
  authorised signatory) — no specimen signatures exist, and drawing one beside a
  named person's name is a forgery with extra steps. Parties are text entries
  with their register references (page 1, section K);
- the customs endorsement stamp — a real act by a state official. The customs
  declaration number renders only when `customs_declaration_no` is set, as a
  recorded reference, with no stamp and no officer named;
- "may be punishable by law" — a private company cannot say that. Replaced with
  the honest equivalent: the authoritative copy is the one verified online.

Claims with nothing behind them:

- blockchain (there is a hash-chained event log, and that is what is named);
- AI fingerprint / AI visual match (no model);
- C2PA-compatible provenance (nothing writes a manifest);
- a fixed "Active" chip per related document (only references the register
  actually holds are listed);
- the artwork's `E. DESTICATION (IMPOGNERO)` panel (garbled placeholder text).

Data the route does not carry, so no block exists for it at all:

- **insurance**. `ExportRegister` reads cover off `ownership_transfers`, and the
  route passes no cover record, so there is no insurance panel. Cover still
  appears where the register does report it — as a readiness category and a risk
  line, both usually `unassessed`, worded "cover" rather than as an insurer's
  claim.

Any field whose value is null is filtered out by `$rows()` before render. Blocks
D (shipping) and E (condition) on page 2 do not exist unless a shipment or an
inspection does.

## Kept

Certificate number, GECN, UUID, version, PRN, OLN, product UUID, COA number,
content hash, Ed25519 signature with `kid` and the `/.well-known/jwks.json`
address, internal HMAC seal, verification PIN, QR to the short verify URL, a
**real Code 39** encoding of the certificate number (same routine as the
registration certificate; an unencodable character drops the symbol rather than
printing a partial one), ISO 8601 timestamps in UTC with the `Z`, ISO 3166-1
country codes, ISO 4217 in the standards list, the ownership chain, the flag
ticks that `$flags` actually carries, the full `$trail`, and the standards-used
list.

Security legend (page 3, section D) lists only what a reader can check: Ed25519
signature, SHA-256 hash, published public key, certificate UUID, serial
numbering, QR verification, hash-chained log, guilloché, microtext, anti-copy
screen. The physical measures stay in `docs/PRINT-SECURITY-SPEC.md`.

## Tests

`php artisan test --filter=ExportCertificateTest` — 11 tests, 65 assertions.
