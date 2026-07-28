# Artisan Verification Certificate — design spec

Source artwork: `certificates/Artisan Verification Certificate (AVC).png`
View: `resources/views/pages/certificate-artisan-verification.blade.php`
Route: `GET /certificat-artisan/{slug}` (`artisan.verification.certificate`)
Tests: `tests/Feature/ArtisanVerificationCertificateTest.php` — 10 tests, 62 assertions.

## Canvas

| Measurement | Artwork | Built |
|---|---|---|
| Canvas | 1024 × 1536 px | 1024 px wide, height grows with the record |
| Outer dark page pad | ~7 px | 7 px (`.av-page`) |
| Frame: left band | 58 px, kente lattice, vertical wordmark | 58 px (`.av-band`, `#coaKenteDark`) |
| Frame: right band | ~12 px gold rule | 12 px |
| Frame: top / bottom | ~14 px | 14 px |
| Inner gold rule | 2 px | `box-shadow: inset 0 0 0 2px #C9A24B` |
| Cream field | `#FAF5E9`, 13 px side pad | same |

Colours read off the PNG: frame green `#062816`→`#04180D`, gold `#C9A24B`,
tab green `#10502C`→`#06301A`, tab text `#F6E4B0`, accent red `#8A1F14`,
tick green `#0C7A3E`, cross red `#A0231B`.

Type: Poppins throughout except the title (Playfair Display). The title is set
`nowrap` and compressed `scaleX(.86)`, at 33 px in English and 29 px in French —
the French string is four characters longer and at a shared size it collided
with the QR panel. Body rows are 9.5 px, ticks 10 px, footnotes 8 px.

The sheet is drawn at 1024 px and scaled as one unit by the script at the
bottom (`MIN_SCALE 0.62`, pannable below that), identical to the sister
certificates. Nothing reflows; it only gets smaller.

## Structure as built

Header (seal · lockup · title · ribbon · intro, with QR + status panel right),
then: 1 certificate identity · 2 artisan · 3 workshop · 4 declared trade ·
5 checks performed · 6 verification level (the seven-rung ladder) ·
7 record metrics · 8 integrity and signature · 9 statement · 10 audit trail ·
footer band · microtext strip.

The artwork's numbered green tab overhanging each card's top-left corner is
kept (`.ac-head`), as is the label · colon · value row (`.ar`).

## The ladder

All seven rungs render, with `data-rung="N" data-attained="0|1"` on each so the
test can assert the distinction directly. An attained rung gets a filled green
pip and an "attained" tag; an unattained one gets an outlined pip in grey.
Rungs 6 and 7 additionally carry an "outside the platform" tag and say in prose
that the platform holds no register of the title and cannot award it — that is
the truth of `ArtisanVerification::levelFor()`, which tops out at 5.

The headline standing (`data-standing`) is always the rung actually held.

## What the artwork carries and this document does not

Blocks removed wholesale: awards & recognitions (7), training & development (8),
export readiness (9), sustainability (10), portfolio thumbnails (11),
AI verification & security (12), digital credentials / NFC (13),
security features strip (15), related records (16), named signatures (18).

Fields removed: date of birth, gender, nationality, national ID number,
passport reference, GPS coordinates to five decimals, workshop photographs,
artisan portrait (a logo or cover image renders only when the shop uploaded
one), trust score, follower count, customer star rating (a rating shows only
when `metrics` genuinely holds one because reviews exist), guild membership,
professional association, apprentices trained.

Captions removed: NFC, wallet integration, holographic shield, UV reactive ink,
invisible watermark, embossed seal, tamper-evident. The decorative treatments
stay — guilloché rosette, anti-copy line screen, real 1.6 px microtext — because
they are honest as decoration and real as vector artefacts; only the claims
about physical properties are gone.

The identity document appears solely as `•••• 7891` via
`ArtisanVerification::maskedIdentityDocument()`. The full number is encrypted at
rest and never reaches a template.

Checks render only for keys present in the stored map. An absent key produces
neither tick nor cross, because a cross for a check we never ran would read as a
failure we have no evidence of.

## What is genuinely verifiable

Section 8 carries the SHA-256 content hash, the Ed25519 CA signature, the key
id, the live signature state, the verification PIN and the JWKS address. A
stranger can fetch `/.well-known/jwks.json` and verify the signature offline
without trusting us. That is the only security claim on the sheet that survives
being tested, and it is the only one made.
