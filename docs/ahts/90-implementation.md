# AHTS — Implementation status

**The only file in this directory that asserts something exists.** Everything in
`00-blueprint.md` and `01-volumes.md` is specification until it appears here.

Kept alongside the code and updated with it. If this file and the code disagree,
the code is right and this file is a bug.

Legend: **Built** — exists and is covered by a test · **Partial** — exists, with
the gap named · **Specified** — designed, not built.

---

## Level 1 — Design system

| Item | Status | Where |
|---|---|---|
| Brand standards (logo, colour, type, grid) | Partial | `docs/COA-DESIGN-SPEC.md`; per-document specs for PRC/OTC/AVC. No single brand manual yet. |
| A4 / print-ready / high-zoom | Built | Every certificate is drawn on a fixed 1024px canvas scaled as one unit, with `@page A4` print rules. |
| Mobile version | Built | Sheets hold a legible minimum scale and become pannable rather than reflowing. |
| CMYK print compatible | Specified | Screen output is sRGB. CMYK conversion is a print-production step — see `docs/PRINT-SECURITY-SPEC.md`. |
| Component library | Partial | Ornament and security sheets are shared (`partials/coa-ornaments`, `partials/coa-security`). The header/identity/person/timeline/status/QR/signature blocks are still duplicated per document rather than extracted. |

---

## Level 2 — Security framework

| Item | Status | Notes |
|---|---|---|
| UUID | Built | Every certificate row. |
| SHA-256 hash | Built | Over canonical certified facts; recomputed on verification. |
| PKI digital signature | Built | **Ed25519 (RFC 8032)**, published at `/.well-known/jwks.json` in JOSE form. A third party verifies offline with no help from us — asserted by a test that uses raw libsodium rather than our verifier. |
| Live verification | Built | `/verifier`, plus per-document verification pages. |
| Audit log | Built | `certificate_events`, hash-chained. |
| Revocation list | Partial | Revocation state exists per certificate; no aggregated public revocation feed yet. |
| Tamper evidence | Built | Append-only hash chain; editing or deleting a past entry is detected. Not a blockchain, and the public page says so. |
| Dynamic QR | Built | Encodes the short verification URL. |
| NFC | Specified | Requires hardware; nothing is claimed on any document. |
| Guilloché, microtext, anti-copy | Built | Real hypotrochoid maths and real 1.6px text — `partials/coa-security.blade.php`. |
| Hologram, gold foil, UV ink, ghost watermark, latent image, embossing | **Specified — cannot exist digitally** | Print-shop processes. Plate requirements in `docs/PRINT-SECURITY-SPEC.md`. No screen element is captioned with these. |
| Invisible watermark | Built (limited) | Real LSB payload, `app/Support/ImageWatermark.php`. Survives PNG copying; does **not** survive resize, JPEG re-encode, crop or screenshot. Stated in the class docblock. |

---

## Level 3 — Identity framework

| Identifier | Status |
|---|---|
| GAN | Built — `businesses.gan` |
| PRN (blueprint GPN) | Built — `products.prn` |
| OLN (blueprint GPLN) | Built — `products.oln` |
| GCN | Specified — see the open question in `10-identifiers.md` |
| GWN, GCOL, GEN, GRN, GVN, GSN, GECN, GPPN | Specified — formats reserved |

Assign-once and survives-renaming are enforced by `tests/Feature/ProvenanceRegistryTest.php`.

---

## Level 4 — Registry framework

| Module | Status | Tables |
|---|---|---|
| Artisan | Built | `businesses`, `artisan_verifications` |
| Product | Built | `products`, `product_certificates`, `product_flags` |
| Ownership | Built | `product_ownerships`, `ownership_transfers` |
| Provenance | Built | `provenance_events`, `provenance_valuations`, `provenance_restorations`, `provenance_evidence` |
| Certificate | Built | `certificate_events` (hash-chained) |
| Shipment / Customs | In progress | `export_consignments`, `exporters`, `shipments`, `condition_reports` |
| Exhibition / Restoration | Built | Typed rows on the provenance spine |
| Insurance | Partial | Columns on `ownership_transfers`; no standalone registry |
| Workshop | Specified | No separate registry; workshop data lives on the business |

---

## Level 5 — Document classes

| Document | Status |
|---|---|
| Certificate of Authenticity (COA) | **Built** — `/certificat/{slug}` |
| Product Registration (PRC) | **Built** — `/certificat-enregistrement/{slug}` |
| Ownership Transfer (OTC) | **Built** — `/certificat-transfert/{ref}` |
| Artisan Verification (AVC) | **Built** — `/certificat-artisan/{slug}` |
| Export Authenticity (EAC) | Register in progress; document specified |
| Product Provenance (PPC) | Register built; document specified |
| Workshop / Collector / Buyer registration | Specified |
| Product Passport, Limited Edition | Specified |
| Gift / Donation / Inheritance | Partial — `ownership_transfers.transfer_type` already carries these; no dedicated documents |
| Valuation, Insurance, Gallery, Auction listing | Partial — valuations recorded; no documents |
| Exhibition, Museum acquisition, Restoration, Conservation | Partial — all recorded as provenance events; no documents |
| Shipping / Customs verification | Specified |

---

## Level 6 — Status system

Built per record type, but **not yet unified**. Certificates use
`active/superseded/revoked/…`; consignments use a longer chain. A single badge
vocabulary across all documents is specified and not done.

---

## Level 7 — Risk engine

In progress with the export register. The specification's three levels are
extended to four: **unassessed** is required, and is the default. Defaulting an
unmeasured risk to "low" would put a green tick on a compliance question nobody
asked.

---

## Level 8 — Scoring engine

| Score | Status |
|---|---|
| Legacy Index | **Built** — `ProvenanceDossier::legacyIndex()` |
| Export Readiness | In progress — `ExportRegister::readiness()` |
| Authenticity, Documentation, Collection, Conservation, Heritage, Trust | Specified |

Both implemented scores follow the same rules, which apply to any score added
later:

- Every category returns a prose **basis** stating what it found or what is
  missing, so the printed number can be argued with.
- A category with nothing to assess is **unassessed and leaves the denominator**,
  rather than scoring full marks or scoring zero.
- Scores measure the completeness of the record, never the merit of the maker.
  The Legacy Index deliberately does not reward a work for changing hands often,
  and does not penalise a well-kept piece for having no restoration history.

---

## Level 9 — Certificate family

Colour/icon/band scheme specified in the blueprint; the shared band component is
in progress. Seals and ornament are shared today; per-type seals and watermarks
are specified.

---

## Level 10 — Digital passport framework

Specified. The register is the live record and certificates already read from
it, so the model is in place, but there is no passport document or endpoint yet.
The framework's key consequence **is** implemented: a certificate reports
`superseded` once the record it describes has moved on.

---

## Level 11 — API framework

| Channel | Status |
|---|---|
| Web | Built |
| QR | Built |
| UUID | Built — verification accepts the certificate number or UUID |
| API | Partial — the public API is closed (401); no verification endpoint yet |
| NFC | Specified |

---

## Level 12 — Future technologies

All reserved, none implemented, none claimed on any document. The nearest real
capability is the perceptual image fingerprint (`app/Support/ImageFingerprint.php`) —
genuine DCT/block/difference hashing with Hamming matching. It is **not** AI, and
is not labelled as such anywhere.

---

## Level 13 — Governance

Largely specified. Implemented: an explicit key ceremony that refuses to
overwrite an existing signing key, revocation state per certificate, retention
by way of an append-only event log. Not implemented: reviewing authority,
approval and appeal workflows, expiry rules beyond the export consignment,
formal audit policy.

---

## Level 14 — Visual design system

Certificates built. ID cards, passports, registry extracts, shipping labels,
authentication labels and verification dashboards are specified.

---

## Level 15 — Certificate lifecycle

Partial. The export consignment implements a real state machine with refused
transitions. Other certificate types issue directly to active and then supersede
or revoke, skipping submitted/under-review/approved. Unifying them is specified.

---

## Standing rules that outrank the specification

These come from repeated failures on this project and hold regardless of what a
design shows:

1. **A value comes from the register or the field does not render.** A blank
   labelled row on a certificate reads as a measured fact.
2. **No claim the platform cannot keep.** A labelled security feature is read as
   a measure that was taken.
3. **A check the platform never performs is absent, not false.** An unticked
   "criminal record" box implies the check was run and failed.
4. **No external organisation's name** on a document it did not endorse.
5. **No invented signatures.** Nobody's specimen signature is on file; drawing
   one forges a named person's hand.
6. **Personal data is masked** — identity documents show the last four digits
   only, and are encrypted at rest.
