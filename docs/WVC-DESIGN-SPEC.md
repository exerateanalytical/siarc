# Workshop Verification Certificate (WVC) — design and honesty spec

`resources/views/pages/certificate-workshop-verification.blade.php`
Route `/certificat-atelier/{gwn}` (`workshop.certificate`), `?lang=fr|en`.
Guard: `tests/Feature/WorkshopCertificateTest.php` (12 tests), plus the family
sweeps in `CertificateFamilyTest` and `CertificateBandTest`.

## Geometry

Identical to its two most recent siblings, deliberately: one 1024px canvas, one
961px sheet inside a 22px gold-dotted frame on a deep-emerald bezel, the whole
sheet scaled as a single unit by the script at the foot of the page. Nothing
reflows. A phone shows the same document in the same arrangement as a printout,
which is the only way a certificate can be read aloud over the telephone —
"section 8, third row" has to mean the same thing on both.

- `.coa-fit` / `.coa-page` wrapper and scaler, `MIN_SCALE` 0.62, pan hint below.
- `@page A4 portrait`, 5mm margin; one sheet, one page.
- Left classification band via `partials/certificate-band` with `$code = 'WVC'`.
- Guilloché and microtext come from `partials/coa-security`; ornaments from
  `partials/coa-ornaments`. Both are real vector line work and real 1.6px type.

Column runs, all inside the 921px live area (961 less two 20px margins):

| Row | Columns |
| --- | --- |
| 1 | `598 / 315` — certificate identity, QR |
| 2 | `1fr ×3` — workshop profile, geographic identity, infrastructure |
| 3 | `340 / 300 / 273` — equipment, production capabilities, workforce |
| 4 | full width — compliance table |
| 5 | `300 / 613` — sustainability, inspection report |
| 6 | `527 / 386` — quality assessment, export readiness |
| 7 | `300 / 613` — traceable portfolio, verifiable features |
| 8 | `437 / 476` — verification result, audit trail |
| 9–10 | full width — who this rests on, scope and limits, sheet foot |

## The band

`WVC` was added to `config/certificate_types.php` as the eleventh type:
`#0E4D2A` deep emerald, accent `#E7C878`. The colour is unique, holds well above
4.5:1 against cream type, and is far enough from OTC's `#0B6B45` to read as its
own document.

The icon reuses the `museum` drawing — a building front. `certificate-band.blade.php`
draws its ten icons itself and was out of scope for this pass, so a new icon key
would have rendered an empty ring, which tells a reader less than a shared
building glyph does. Colour still separates the two. When somebody draws a
bench-and-tools glyph, only the `icon` line in the config changes.

**Known consequence:** `CertificateBandTest::test_all_ten_types_are_declared_with_a_colour_an_icon_and_both_languages`
asserts `array_keys(config('certificate_types')) === [ten codes]` and now fails
on the eleventh. That test's own docblock says whoever adds the eleventh type
should find out at the point of adding it, which is what happened. The fix is one
line — add `'WVC'` to `CertificateBandTest::CODES` — and belongs to whoever owns
that file. Every other assertion in that suite passes, including the icon and
colour-collision tests.

## What the artwork asked for and did not get

The supplied sheet has eighteen numbered panels. Section 10 is
*"AI & Field Inspection Report"*: satellite location verification, GPS accuracy,
an AI image match, a duplicate-workshop check and a fraud-risk score. There is no
model, no imagery feed and no fraud system behind this platform, and
`WorkshopRegister` has no columns for any of it — by decision, so nobody can
quietly fill them. `checks()` omits those keys entirely rather than returning
them false, because false says "we looked and it failed", which is a claim about
the workshop, while absence says "this check does not exist here", which is a
claim about us and is the true one.

Section 10 therefore renders the human inspection that genuinely exists: named
inspector, inspector reference, report reference, method, dates, written
findings, per-dimension scores, outcome, next due date, and the report UUID.
Nothing on the sheet names AI, satellite imagery or fraud.

Also omitted, and why:

| Artwork element | Why it is not here |
| --- | --- |
| Holographic shield, embossed gold seal, UV reactive ink, invisible watermark, tamper-evident pattern | Physical properties of a print run that has never happened. These are exactly the features a reader would "check" by looking at a picture of them. |
| NFC chip, blockchain | Hardware and a system that do not exist. |
| "Anti-copy" as a claim | The anti-copy screen is drawn and is listed as a drawn feature, not as a protection that works. |
| "Punishable by law" | The platform has no power to prosecute anybody. |
| Four handwritten signatures over Workshop Owner / Field Inspector / Regional Officer / AHCA Director | Three of the four posts do not exist, and a drawn signature asserts that a person put their hand to a sheet nobody touched — this document is issued by software. Section 17 names the inspector as a text entry with their reference instead, as the transfer certificate names its parties. |
| Six portfolio counters (awards, exhibitions, collections…) | Four are not columns anywhere. Section 13 prints the traceability finding the export register actually computed. |
| Craft categories / main products / primary materials | Not columns on this table. Section 6 prints the three capability columns that are. |
| "108/120 (90%)" and a five-star rating | A number a designer typed. See below. |
| "GPS VERIFIED" over the site photograph | `geo_verified_at` is set only when somebody stood at the pin. Where it is null, section 3 says the coordinates were declared and are not a verified location. |
| Section 14 "Security Features" | Renamed *Verifiable features* and cut to the ten things a reader can actually check: Ed25519 signature, SHA-256 hash, published key, UUID, serial numbering, QR, hash-chained log, guilloché, microtext, anti-copy screen. |

## Scores show their working

`assessment()` and `exportReadiness()` each return
`['categories' => [key => ['score','max','basis']], 'total','max','rating']`.

- `total/max` is printed exactly as returned. The seeded preview prints
  **210/224 (94%) TRÈS BON** and **32/32 (100%) EXCELLENT**.
- Every category prints its own `score/max` and its `basis` phrase underneath, so
  a wrong score is arguable rather than merely disappointing.
- **A category with `max` 0 renders as a dashed slate chip reading "Non évalué" /
  "Not assessed".** Never `0/20`, never dropped. Turning our missing data into a
  zero would be a finding against the workshop; dropping it would hide that
  anything was left unlooked-at.
- The total is captioned as being over the assessable dimensions only, with the
  count of excluded ones, and says in words that a high percentage over few
  dimensions is a narrow score rather than a good one.

## Unassessed versus valid

The single most important distinction on the sheet, and it appears three times.

**Compliance rows (section 8).** A row at `unassessed` gets a grey row wash, a
muted label, em-dashes in every date and reference cell, and a **dashed slate
chip** reading *Jamais évalué / Never assessed*, against the solid green
tick-and-word of a `valid` row. The dash is deliberate: an outline survives a
black-and-white photocopy where a colour does not. Under the table, a boxed
caption names the unassessed obligations in full and states that they are neither
compliant nor non-compliant. The markup carries `data-compliance="{state}"` so
the two can never be styled alike by a later accident, and the test asserts both
states are present and that exactly the two seeded rows are unassessed.

The expiry beats the stored column for every row except an unassessed one —
nothing sweeps this table, so a licence that lapsed last week still reads `valid`
in storage. An unassessed row is never re-read this way, because a date on a
document nobody looked at proves nothing either way.

**Three-valued booleans (sections 4 and 9).** Fire equipment, emergency exits
and the four sustainability practices are nullable on purpose. `false` prints a
red cross and "none found" — an inspector looked. `null` prints a dashed circle
and "Not assessed" — nobody looked. The artwork's column of green ticks cannot
express the difference.

**Workforce counts (section 7).** A null count prints a dashed "Not recorded"
chip, never `0`, and the five counts are **not** summed into an employee total,
because a total would hide which of them were never declared.

**Null measurements.** No row anywhere prints a zero as a measurement. Floor
area, altitude and every room count drop out entirely when null. The test asserts
an unmeasured workshop never prints "0 m²".

## The check list

Section 15 renders `$checks` by key, and only keys the register returned. The
markup carries `data-check="{key}"`, and the test asserts every printed key
exists in `WorkshopRegister::checks()`. The caption states that the list is
complete and that a check not on it has not failed — it does not exist here, and
gets neither a tick nor a cross.

The signature state is rendered from `WorkshopRegister::signatureState()`
alongside the key id, so a certificate whose signature does not verify says so on
its face rather than showing a green tick regardless.

## The level ladder

`level` is printed as "Level N of 7" with all seven rungs listed and the granted
one marked, each rung stating what evidence it takes (document review at the
bottom, an on-site visit with six scored dimensions at the top). "LEVEL 4" alone
tells a reader only that three higher numbers exist.

## Identifiers printed

Certificate number, certificate UUID, GWN, workshop UUID, artisan register number
(GAN), version, SHA-256 content hash, Ed25519 signature, signing key id, ISO 8601
UTC issue and expiry timestamps, recorded verification count, verification PIN,
QR to the register, the readable verification address, ISO 3166-1 country code,
and the full audit trail with ISO 8601 timestamps and actors.

The verification address is this certificate's own URL. `/verifier` answers for
product certificates only, and pointing a reader at a form that reports "not
found" for a perfectly valid WVC would be worse than printing no address at all.

## Known limitation

The `basis` phrases in sections 11, 12 and 13 come from `WorkshopRegister` and
are written in English, so they appear in English on the French sheet. They are
the register's own reasoning and `app/Support/` was out of scope for this pass;
another pass is translating that layer. Everything the view itself writes is in
both languages.
