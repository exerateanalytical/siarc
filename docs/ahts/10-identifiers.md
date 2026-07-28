# AHTS — Identifier standards

Every permanent identifier issued by the platform, its format, and how the
blueprint's naming reconciles with the numbers already in the database.

## Why this file exists separately

Identifiers are the one thing a certificate cannot revise. A layout can be
redesigned and a wording softened, but a number printed on a document in a
museum's files is permanent — reusing or renaming it later breaks the link
between a physical object and its record. So identifier decisions are made
here, once, and every certificate reads them from here.

## Structure

```
AH237-<TYPE>-<CC>-<YYYY>-<SEQ>
```

- `AH237` — issuer prefix, fixed.
- `<TYPE>` — three or four letter identifier class (below).
- `<CC>` — ISO 3166-1 alpha-2 of the issuing country. Present so the scheme
  survives the platform operating outside Cameroon; omitted on identifiers that
  are global by nature rather than issued per jurisdiction.
- `<YYYY>` — year of issue. Omitted where the identifier must not imply a date.
- `<SEQ>` — zero-padded sequence.

## Currently issued

These exist in the database today and are printed on live certificates.
**They cannot be renamed without invalidating documents already in circulation.**

| Code | Name | Format | Where |
|---|---|---|---|
| GAN | Global Artisan Number | `AH237-GAN-CM-0000000579` | `businesses.gan` |
| PRN | Product Registry Number | `AH237-PRN-CM-2026-000000000058` | `products.prn` |
| OLN | Ownership Ledger Number | `AH237-OLN-0000000058` | `products.oln` |
| — | Certificate of Authenticity no. | `AHC-COA-2026-000000058` | `product_certificates.certificate_no` |
| — | Ownership Transfer cert. no. | `AH237-OTC-CM-2026-000000000001` | `ownership_transfers.certificate_no` |
| — | Artisan Verification cert. no. | `AH237-AVC-CM-2026-0000000579` | `artisan_verifications.certificate_no` |

Neither the OLN nor the GAN carries a year, deliberately: both must be stable
for the lifetime of the thing they name, and a year segment invites the
assumption that a new one is issued annually.

## Reconciliation with the blueprint

Level 3 of the blueprint names ten identifiers. Three of them overlap with
numbers already issued under different names:

| Blueprint | Already issued as | Recommendation |
|---|---|---|
| GAN — Global Artisan Number | GAN | **No change.** Already matches. |
| GPN — Global Product Number | PRN — Product Registry Number | **Keep PRN as the issued value; treat GPN as its blueprint name.** The certificates in circulation print PRN. |
| GPLN — Global Provenance Ledger Number | OLN — Ownership Ledger Number | **Keep OLN as the issued value.** Same reason. |
| GCN — Global Certificate Number | per-type certificate numbers | **See open question below.** |
| GWN, GCOL, GEN, GRN, GVN, GSN | not yet issued | Adopt the blueprint names directly when built. |

### Open question — certificate numbering

The blueprint specifies a single Global Certificate Number. What exists is a
number per certificate type, and those numbers are not consistent with each
other: the Certificate of Authenticity uses `AHC-COA-…` while the transfer and
verification certificates use `AH237-…-CM-…`. That inconsistency is a defect in
what was built, not in the specification.

Three options, in order of preference:

1. **Adopt the `AH237-<TYPE>-<CC>-<YYYY>-<SEQ>` form for all new certificate
   types, and leave existing COA numbers as they are.** Costs nothing, stops the
   drift spreading, and never invalidates a document. Existing COA numbers stay
   valid and resolvable forever; only their prefix looks historical.
2. Additionally issue a GCN alongside the per-type number, as a single
   cross-type sequence. This satisfies the blueprint literally and keeps the
   type-readable numbers people actually quote.
3. Renumber the existing COA certificates to match. **Not recommended** —
   any certificate already downloaded or printed would stop matching its record.

Pending a decision, new work follows option 1.

## Reserved

Specified in the blueprint, not yet issued. Formats fixed here so that
implementations do not each invent one:

| Code | Name | Format |
|---|---|---|
| GWN | Global Workshop Number | `AH237-GWN-CM-0000000000` |
| GCOL | Global Collection Number | `AH237-GCOL-CM-0000000000` |
| GEN | Global Exhibition Number | `AH237-GEN-CM-YYYY-000000000000` |
| GRN | Global Restoration Number | `AH237-GRN-CM-YYYY-000000000000` |
| GVN | Global Valuation Number | `AH237-GVN-CM-YYYY-000000000000` |
| GSN | Global Shipment Number | `AH237-GSN-CM-YYYY-000000000000` |
| GECN | Global Export Certificate Number | `AH237-GECN-CM-YYYY-000000000000` |
| GPPN | Global Product Provenance Number | `AH237-GPPN-CM-YYYY-000000000000` |

`GVN` collides with an abbreviation used by an unrelated organisation whose name
was removed from this platform's certificates earlier. It is safe as an internal
identifier class, but it must not be expanded on a document into anything that
reads as that organisation's initials.

## Rules

1. **Assign once, never reissue.** An identifier is allocated on first request
   and stored; recomputing it must return the stored value.
2. **Survives renaming.** A renamed business, re-slugged product or claimed
   profile keeps its identifiers. Enforced by
   `tests/Feature/ProvenanceRegistryTest.php`.
3. **Unique at the database level**, not merely in application code.
4. **No meaning in the sequence.** The sequence is an ordinal, not a count of
   anything, and must not be read as one.
5. **A certificate quotes identifiers; it never derives them at render time.**
   A document that computes its own number will disagree with the register the
   moment either changes.
