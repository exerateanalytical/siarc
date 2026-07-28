# AHTI — ArtisanHub237 Trust Infrastructure

**The governing specification for the certification ecosystem.**
Recorded as authored by the platform owner. Status of each level against the
codebase is tracked separately in `90-implementation.md`.

Rather than designing certificates one at a time, this defines the framework
from which every certificate, passport, badge and verification document is
generated.

---

## Level 1 — Design system

### 1.1 Brand standards
Logo usage · colours · typography · margins · grid · icons · security colours ·
print specifications · digital specifications.

### 1.2 Document standards
Every document is: A4 · ultra HD · print-ready · PDF-ready · readable at high
zoom · CMYK print compatible · with a digital version and a mobile version.

### 1.3 Component library
Reusable components rather than per-certificate redesign.

| Component | Contains |
|---|---|
| Header | Logo, authority, certificate name, certificate code, version |
| Identity block | Certificate number, UUID, product UUID, registry number |
| Product block | Image, product name, product details |
| Person block | Reusable for artisan, owner, buyer, exporter, curator, restorer |
| Timeline | Reusable |
| Status | Reusable |
| QR | Reusable |
| Signature | Reusable |
| Footer | Reusable |

---

## Level 2 — Security framework

Every official document uses the same security architecture.

**Physical:** rainbow hologram · gold foil seal · guilloché · UV ink ·
microtext · ghost watermark · anti-copy background · fine-line patterns ·
serialised numbering · latent image.

**Digital:** UUID · SHA-256 (or stronger) hash · PKI digital signature ·
dynamic QR · NFC · live verification · revocation list · audit log.

> Implementation note: the physical and digital halves are not
> interchangeable, and the distinction is load-bearing. See
> `docs/PRINT-SECURITY-SPEC.md` for which of the physical features can exist on
> a screen (none) and what a security printer needs to produce them.

---

## Level 3 — Identity framework

Everything receives a permanent identifier.

| Entity | Code | Name |
|---|---|---|
| Artisan | GAN | Global Artisan Number |
| Workshop | GWN | Global Workshop Number |
| Product | GPN | Global Product Number |
| Provenance | GPLN | Global Provenance Ledger Number |
| Certificate | GCN | Global Certificate Number |
| Collection | GCOL | Global Collection Number |
| Exhibition | GEN | Global Exhibition Number |
| Restoration | GRN | Global Restoration Number |
| Valuation | GVN | Global Valuation Number |
| Shipment | GSN | Global Shipment Number |

Formats, validation and the reconciliation with identifiers already issued are
in `10-identifiers.md`.

---

## Level 4 — Registry framework

One Trust Registry, not separate databases. Modules:

Artisan · Workshop · Product · Ownership · Provenance · Certificate · Shipment ·
Exhibition · Restoration · Insurance · Customs.

---

## Level 5 — Document classes

**Identity:** Artisan Verification · Workshop Registration · Collector
Registration · Buyer Verification.

**Product:** Product Registration · Certificate of Authenticity · Product
Passport · Limited Edition.

**Ownership:** Ownership Transfer · Gift · Donation · Inheritance.

**Market:** Valuation · Insurance · Gallery Listing · Auction Listing.

**Cultural:** Exhibition · Museum Acquisition · Restoration · Conservation.

**Trade:** Export Authenticity · Shipping Verification · Customs Verification.

---

## Level 6 — Status system

Universal status badges: 🟢 Active · 🟡 Pending · 🟠 Suspended · 🔴 Revoked ·
⚪ Archived.

---

## Level 7 — Risk engine

Every product carries risk indicators: counterfeit · ownership · damage ·
export · compliance · insurance · transit · fraud. Each rated Low / Medium /
High.

> Implementation note: a fourth state, **unassessed**, is required. Defaulting
> an unmeasured risk to "low" is how a compliance failure reaches a customs
> desk wearing a green tick.

---

## Level 8 — Scoring engine

Every score has an algorithm — no arbitrary numbers.

Authenticity · Documentation · Legacy · Export Readiness · Collection ·
Conservation · Heritage · Trust.

> Implementation note: each score must expose its inputs and a per-category
> basis, so a document can show its working. A score printed on a certificate is
> a claim about someone's work and has to be defensible. Scores measure the
> completeness of the record, never the merit of the maker.

---

## Level 9 — Certificate family

Every certificate has a unique colour, icon, side band, seal and watermark.
Everything else stays consistent.

| Code | Document | Colour | Icon |
|---|---|---|---|
| COA | Certificate of Authenticity | Royal blue | Shield |
| OTC | Ownership Transfer | Emerald green | Key |
| PRC | Product Registration | Gold | Registry |
| AVC | Artisan Verification | Purple | Verified artisan |
| PPC | Product Provenance | Burgundy | Timeline scroll |
| EAC | Export Authenticity | Navy blue | Globe & cargo ship |
| EC | Exhibition | Crimson | Museum |
| RC | Restoration | Teal | Restoration tools |
| VAC | Valuation | Bronze | Balance scale |
| DPP | Digital Product Passport | Black & gold | Microchip |

---

## Level 10 — Digital passport framework

One live passport per product. Everything updates there. **Certificates are
snapshots generated from the passport at a point in time** — which is why a
certificate must be able to report that the record has since moved on.

---

## Level 11 — API framework

Every certificate verifiable by QR · API · UUID · NFC · web · future mobile app.

---

## Level 12 — Future technologies

Reserved support for: AI image fingerprinting · Content Credentials (C2PA)
references where supported · digital evidence archives · optional blockchain
anchors · machine-readable customs data · machine-readable museum data.

> These are **reserved**, not implemented. A reserved capability must not appear
> on a document until it exists.

---

## Level 13 — Governance

Issuing authority · reviewing authority · approval workflow · revocation
workflow · appeal workflow · certificate expiry rules · document retention ·
audit policy.

---

## Level 14 — Visual design system

Reusable templates for certificates · ID cards · product passports · registry
extracts · shipping labels · authentication labels · QR verification pages ·
verification dashboards.

---

## Level 15 — Certificate lifecycle

Every certificate follows the same lifecycle:

```text
Draft
   ↓
Submitted
   ↓
Under Review
   ↓
Verified
   ↓
Approved
   ↓
Issued
   ↓
Active
   ↓
Updated (if applicable)
   ↓
Archived or Revoked
```
