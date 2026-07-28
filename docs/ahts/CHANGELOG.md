# AHTS — Change log

All notable changes to the ArtisanHub237 Trust Standard.

## [1.0-draft] — 2026-07-28

### Added
- `00-blueprint.md` — the AHTI blueprint, Levels 1–15, as authored by the platform owner.
- `01-volumes.md` — the AHCM volume plan (I–XV) and the four build phases.
- `10-identifiers.md` — identifier formats, reserved codes, and the reconciliation
  between the blueprint's names and the numbers already issued.
- `90-implementation.md` — the status annex, mapping the specification onto what
  exists in the codebase.

### Decisions recorded
- **Risk levels gain a fourth state, `unassessed`, which is the default.**
  Low/Medium/High alone forces an unmeasured risk to be reported as low.
- **Scores must expose a per-category basis**, and a category with nothing to
  assess leaves the denominator rather than scoring full marks.
- **Physical security features are not claimed on screen.** Guilloché, microtext
  and the anti-copy screen are implemented and real; hologram, foil, UV ink,
  embossing, ghost watermark and latent image are print-shop processes and appear
  only in the print specification.
- **Certificate numbering drift acknowledged.** The Certificate of Authenticity
  uses `AHC-COA-…` while later certificates use `AH237-…-CM-…`. New types follow
  the `AH237` form; existing numbers are not renumbered, because a certificate
  already in circulation must keep matching its record.

### Open questions
- Whether to issue a single Global Certificate Number alongside the per-type
  numbers. Options and trade-offs in `10-identifiers.md`.
- Whether the status vocabulary should be unified across all document types
  before more documents are built.

## [1.0-draft] — 2026-07-28 (second intake)

### Added — machine-readable specification, `spec/`
- `00-foundation.json` · `20-registries.json` · `30-identifiers.json`
- `40-design-system.json` · `50-security-framework.json` · `60-certificate-catalogue.json`
- `70a-trust-evaluation-engine.json` · `70b-business-rule-engine.json`
- `70c-trust-intelligence-layer.json` · `70d-decision-governance-engine.json`

All ten recorded verbatim as authored and validated as parsable JSON.

### Added — `20-conflicts.md`
Eight items where the specification contradicts itself, contradicts what is
built, or specifies something this deployment cannot do. Each carries a
recommendation. Summary:

1. GINS identifier format is a different scheme from the numbers already issued
   and printed. Recommend additive adoption, never renaming in place.
2. Classification colours differ between the blueprint and the design system,
   and two design-system values fail the contrast ratio the same file requires.
3. RSA-4096 is named primary but cannot be generated on this server; Ed25519 is
   built, is in the spec's own alternatives list, and fits on a printed page.
4. Embossing, foil and paper watermark are marked mandatory but cannot exist on
   a screen. Recommend splitting mandatory_print from mandatory_digital.
5. Certificate dependencies and requires_verified_artisan would stop all
   issuance today. Recommend recording unmet prerequisites on the document
   rather than refusing to issue.
6. Typography differs from what is bundled and measured.
7. QR payload, audit retention, GVN initials, unbuilt registries.
8. Trust engine: UNESCO and National Heritage bonuses must be gated on a
   verified award record with an external reference; MR001 repeats the blocking
   problem; craftsmanship scoring needs an evidence basis or should be dropped,
   since the platform has no standing to grade an artisan's craft.
