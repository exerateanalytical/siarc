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

## [1.0-draft] — 2026-07-28 (third intake: Part 8, Digital Product Passport)

### Added
- `spec/80a-digital-product-passport.json` — canonical product record (AHDPP)
- `spec/80b-ownership-provenance-chain.json` — ownership, provenance, custody (OPCC)
- `spec/80c-restoration-conservation-market.json` — restoration, conservation,
  exhibitions, valuations, insurance (RCEVI)
- `spec/80d-export-compliance-lifecycle.json` — export, compliance, APIs,
  timeline, lifecycle, digital twin (ECVLDT)

Fourteen specification files now recorded, all parsing as valid JSON.

### Conflicts recorded — item 9 in `20-conflicts.md`
- **The passport is public by default and carries the artisan's village and GPS,
  the workshop location, and the owner's legal name, city and contact.** Combined
  with permanent retention and no delete path, that contradicts the standard's own
  privacy principle. Recommend defaulting to private with an opt-in public
  projection, and separating "retained" from "published".
- **Three optimistic defaults**: every risk domain defaults LOW; chain integrity
  and provenance completeness default to 100%; legal compliance defaults every
  flag true including `not_stolen`. An unassessed record would report itself
  perfect. Recommend UNASSESSED/null defaults, and `not_stolen` reported as "no
  report on this register" rather than a boolean.
- `digital_twin` is `future_reserved: false` in 80a and `enabled: true` with
  real-time sync in 80d — the two parts disagree, and neither is built.
- `never_restored: true` as a default asserts a historical fact from an empty
  table; should read "no restoration recorded".

## [1.0-draft] — 2026-07-28 (fourth intake: Parts 9-14)

### Added
- `spec/90-interoperability.json` (AIIF)
- `spec/100-analytics-intelligence.json` (AIDSF)
- `spec/110-marketplace-commerce.json` (MCEF)
- `spec/120-sustainability-esg.json` (SCHEIF)
- `spec/130-ai-automation.json` (AIASSF)
- `spec/140-security-privacy-risk-compliance.json` (ESPRCF)

Twenty specification files, all parsing. Parts 13 and 14 arrived containing the
same ESPRCF block; it is stored once.

### Conflicts recorded - items 10 and 11
- **Item 10 is blocking.** The marketplace part specifies escrow, mobile money,
  card payments, refunds and a tax engine. The legal copy and four issued
  certificates state the platform is not a party to transactions and collects no
  payments. Both cannot be true. Escrow makes the platform a payment
  intermediary with licensing and AML obligations, which is why that same part
  requires KYC and AML. Decide whether the platform takes money before building
  anything from this part; if it does, the legal copy and every certificate
  disclaimer must change first, and be dated.
- **Item 11**: Parts 9-14 describe an estate that does not exist. Fine as a
  target; nothing may cite ISO 27001 or SOC monitoring on a document until
  audited. Sustainability defaults for forced labour and human rights compliance
  default true and must default unassessed. The built perceptual fingerprint is
  not AI.
