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
