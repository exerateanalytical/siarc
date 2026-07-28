# ArtisanHub237 Trust Standard (AHTS)

**Version:** 1.0 (draft) · **Classification:** Official · **Owner:** ArtisanHub237 Certification Authority

This directory is the authoritative specification for the ArtisanHub237 trust
infrastructure: every certificate, registry, identifier, workflow and
verification service is defined here, and implementations are expected to
reference it rather than re-deciding the same questions per document.

It is kept in the repository, under version control, on purpose. A trust
standard that lives in a word processor drifts from the software that is
supposed to implement it, and the drift is invisible until a certificate makes
a claim the code cannot keep.

## Files

| File | What it holds |
|---|---|
| `00-blueprint.md` | The AHTI blueprint, Levels 1–15. The owner's specification, recorded as authored. |
| `01-volumes.md` | The AHCM volume plan (I–XV) and the four build phases. |
| `10-identifiers.md` | Identifier formats, and the reconciliation between the blueprint's names and the ones already issued. |
| `20-conflicts.md` | Where the parts contradict each other, contradict the build, or specify something the platform cannot do. Read this before implementing anything. |
| `90-implementation.md` | What exists in this codebase today, mapped against the specification. |
| `CHANGELOG.md` | Version history. |
| `spec/*.json` | The machine-readable specification, recorded verbatim as authored. |

### `spec/`

| File | Part |
|---|---|
| `00-foundation.json` | Document, foundation, principles, governance, lifecycle |
| `20-registries.json` | Registry architecture |
| `30-identifiers.json` | Global Identity & Numbering System (GINS) |
| `40-design-system.json` | Certificate Design System (CDS) |
| `50-security-framework.json` | Security Framework (AHSF) |
| `60-certificate-catalogue.json` | Certificate Catalogue (AHCC) |
| `70a-trust-evaluation-engine.json` | Trust Evaluation Engine (ATEE) |
| `70b-business-rule-engine.json` | Business Rule Engine (BRE) |
| `70c-trust-intelligence-layer.json` | Trust Intelligence Layer (TIL) |
| `70d-decision-governance-engine.json` | Decision & Governance Engine (DGE) |

These are held as JSON rather than prose so they can be validated, diffed and
read by code. All ten parse; a CI check should keep it that way.

Related specs already written, referenced from here rather than duplicated:
`docs/COA-DESIGN-SPEC.md`, `docs/PRC-DESIGN-SPEC.md`, `docs/OTC-DESIGN-SPEC.md`,
`docs/AVC-DESIGN-SPEC.md`, `docs/PRINT-SECURITY-SPEC.md`.

## How to read this

Two kinds of statement appear in these documents and they must never be
confused:

- **Specified.** The standard requires it. It may or may not exist yet.
- **Implemented.** It exists in this codebase and is covered by a test.

`90-implementation.md` is the only place that asserts the second, and it is
maintained alongside the code. Everything in `00-blueprint.md` and
`01-volumes.md` is specification until that file says otherwise.

This distinction is the whole reason the status file exists. A certificate that
prints a field because the standard mentions it — rather than because the
register holds a value — is the failure mode this entire project has been
built to avoid.

## How to extend it

The owner is adding to this specification incrementally. New material should:

1. Go into the numbered file it belongs to, keeping the existing section
   numbering stable so external references do not break.
2. Record identifier formats in `10-identifiers.md`, never inline in a
   certificate design.
3. Note in `CHANGELOG.md` what changed and why.

When a new certificate type is specified, it needs, at minimum: its code, its
purpose, the register that backs each field, its lifecycle states, and — for
every field — where the value comes from. A field with no source is not
specified; it is decoration, and it will not be rendered.
