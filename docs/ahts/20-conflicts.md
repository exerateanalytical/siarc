# AHTS — Conflicts and reconciliation

The v1.0 specification parts in `spec/` are recorded verbatim as authored. This
file records where they **contradict each other, contradict what is already
built, or specify something the platform cannot currently do**.

None of these are objections to the specification. They are decisions that have
to be made explicitly, because every one of them silently changes a document
already in circulation if it is applied without thought.

Ordered by cost of getting it wrong.

---

## 1. Identifier format — breaking, and already in circulation

**The specification** (`spec/30-identifiers.json`) defines a random-segment
format with a MOD97 checksum:

```
GAN-CM-20260728-84ABXK-52
```

**What is issued today** is sequential, with no checksum and an `AH237` issuer
prefix:

```
AH237-GAN-CM-0000000579
AH237-PRN-CM-2026-000000000058
AH237-OLN-0000000058
```

These are not variations of one scheme; they are different schemes. The
specification is better in two real ways — a checksum catches transcription
errors when a curator types a number off a printed sheet, and a random segment
does not leak how many artisans or products exist. Sequential numbers disclose
business volume to anyone holding two certificates.

**The cost of adopting it:** every certificate already issued quotes the old
form. Renumbering breaks the link between a printed document and its record,
which is the one failure a provenance system cannot recover from.

**Recommendation — additive, not a rename.** Issue GINS identifiers alongside
the existing ones for all *new* records, keep the existing values permanently
resolvable, and have verification accept either. `allow_reuse: false` and
`immutable: true` are then satisfied for both generations. Nothing already
printed stops working.

**Not recommended:** rewriting `businesses.gan` and `products.prn` in place.

---

## 2. Classification colours — the two specification parts disagree

Three colour sets now exist for the same ten documents:

| Code | Blueprint Level 9 | `spec/40-design-system.json` | Built |
|---|---|---|---|
| COA | Royal blue | `#0E5A36` green | `#1B3A93` |
| PRC | Gold | `#1565C0` blue | `#8A6410` |
| OTC | Emerald green | `#8E24AA` purple | `#0B6B45` |
| AVC | Purple | `#00897B` teal | `#4A2A7A` |
| PPC | Burgundy | `#6A1B9A` purple | `#6E1327` |
| EAC | Navy | `#003366` | `#10285C` |

The build followed Level 9. The design system reassigns nearly every colour —
COA becomes green, PRC becomes blue, OTC becomes purple.

Two of the design-system values also fail the check the band was built against:
`#00897B` (AVC) and `#EF6C00` (VAC) do not hold a 4.5:1 contrast ratio against
the cream type used on the band, which `spec/40-design-system.json` itself
requires under `accessibility.minimum_contrast_ratio`. The specification
contradicts itself here.

**Needs a decision.** Either Level 9 stands and the design system's palette is
corrected to match, or the design system stands and the band is recoloured —
in which case the two failing values need darkening first. Changing colours is
cheap and breaks nothing; leaving both recorded and unreconciled is what causes
the next document to pick the wrong one.

---

## 3. Signature algorithm — specified primary is impossible on this server

`spec/50-security-framework.json` names **RSA-4096** as the signature algorithm
and Ed25519 as an alternative, with a 4096-bit offline root CA.

The platform signs with **Ed25519**, published at `/.well-known/jwks.json`.

That was not a preference. This PHP build ships openssl without an `openssl.cnf`
and **cannot generate an RSA or EC key at all** — key generation fails outright.
libsodium needs no configuration. Ed25519 is also listed in the specification's
own `alternative_algorithms`, is an IETF standard (RFC 8032), and produces
64-byte signatures that fit on a printed page where a 512-byte RSA signature
does not.

**Recommendation:** promote Ed25519 to the primary algorithm in the
specification and keep RSA-4096 as the reserved option for a future deployment
with a hardware security module, which is where an offline 4096-bit root
belongs anyway. The `public_key_infrastructure` block describing an offline root
and separate issuing CAs remains unbuilt either way — today there is one key,
and `90-implementation.md` says so.

---

## 4. Physical features marked mandatory that cannot exist digitally

`spec/50-security-framework.json` marks these `mandatory: true`:

- PS004 Embossed Seal
- PS005 Gold Foil Seal
- PS006 Ghost Watermark
- PS007 Anti-Copy Background

Embossing, foil and a true paper watermark are properties of **stock and press**.
No screen and no office printer can produce them. Guilloché (PS001), microtext
(PS002), serial numbering (PS009) and an anti-copy line screen are implemented
and real — see `partials/coa-security.blade.php`.

**Recommendation:** split the block into `mandatory_print` and
`mandatory_digital`. A digitally issued certificate cannot be non-compliant for
lacking a foil seal, and marking it so would either make every issued document
non-compliant or push someone to caption a screen element with a claim it cannot
keep. Requirements for a print run are already written up in
`docs/PRINT-SECURITY-SPEC.md`.

---

## 5. Certificate dependencies would stop current issuance

`spec/60-certificate-catalogue.json` declares:

```
COA  requires  PRC, AVC
PPC  requires  COA, PRC
OTC  requires  COA, PPC
EAC  requires  COA, PPC, OTC
```

plus `issuance_rules.requires_verified_artisan: true`.

Today a Certificate of Authenticity issues for any published product whose
business exists, with no artisan verification and no registration certificate.
Enforcing the dependency chain as written means **no COA can be issued until its
artisan is verified** — and of the 510 imported SIARC artisans, none are, because
none have claimed their profiles yet.

The intent is right: a certificate of authenticity backed by an unverified
identity is weak. But applied literally and immediately it stops the platform
issuing anything.

**Recommendation:** implement the chain as a **recorded prerequisite state**
rather than a hard block — the certificate records which prerequisites were
satisfied at issue, and says so on its face. A COA issued for an unverified
artisan then reads honestly as exactly that, which is more useful to a buyer
than no certificate at all. Harden to a hard block once artisan verification is
routine.

Note also that `OTC requires PPC` is circular in practice: the provenance
certificate is generated *from* the ownership record, so requiring it before a
transfer inverts the dependency.

---

## 6. Typography

`spec/40-design-system.json` specifies Inter / Merriweather / JetBrains Mono.
The certificates are set in Poppins and Playfair Display, which are the fonts
bundled and already used across the platform.

Low cost either way, but it needs deciding once: mixed typography across a
document family is exactly the inconsistency the standard exists to prevent.
Adopting Inter means bundling it and restating every measured type size in the
per-document design specs, since cap-heights differ.

---

## 7. Smaller items

- **QR payload.** Specification: UUID, identifier, verification URL, certificate
  hash. Built: the short verification URL only. Adding the hash makes the QR
  denser and the printed module size larger; the minimum 25mm in the
  specification assumes that. Worth doing, needs the size check.
- **Audit retention 50 years.** Recorded; no retention or archival policy is
  implemented.
- **`GVN`** as Global Valuation Number shares initials with an unrelated
  organisation whose name was removed from these certificates earlier. Safe as an
  internal class; it must never be expanded on a document into anything that
  reads as those initials.
- **Registries specified but not built:** workshop, collection, museum,
  insurance as a standalone registry. Valuation and exhibition exist as typed
  provenance events rather than separate registries — functionally equivalent,
  differently shaped.
- **`immutable_events: true`** is satisfied for the certificate event log, which
  is hash-chained. It is *not* satisfied for registry rows generally, which are
  ordinary updatable tables.

---

## 8. Trust evaluation engine (Part 7) — three items need care

`spec/70b-business-rule-engine.json` introduces automatic bonuses, and two of
them award points for **honours conferred by bodies outside this platform**:

| Rule | Award | Points |
|---|---|---|
| B002 | UNESCO Heritage Recognition | +10 |
| B003 | National Heritage Listing | +8 |

This is legitimate **only if a real row records the recognition**. There is no
awards register today, and the `award` provenance event type exists precisely so
a genuine one can be entered. What must never happen is a score rising because
someone typed a body's name into a free-text field. A UNESCO listing is a matter
of public record and should be evidenced by its reference, not asserted.

**Recommendation:** gate B002/B003 on a verified `award` event carrying an
external reference, and show that reference wherever the bonus is shown. The
standing rule on external organisations otherwise makes these two unusable.

**MR001 repeats the blocking problem** already described in item 5: verified
artisan required, severity CRITICAL, on-fail `STOP_SCORING`. With 510 imported
artisans yet to claim their profiles, that halts evaluation platform-wide. The
same recommendation applies — record the unmet prerequisite on the face of the
result rather than refusing to produce one. `CAP001` already does exactly that
more gracefully, capping authenticity at 40 for an unverified artisan, which is
the better mechanism.

**Craftsmanship is scored at weight 10.** Every score implemented so far
deliberately measures the completeness of the *record* rather than the merit of
the work, because a platform grading an artisan's craft is making an aesthetic
judgement it has no standing to make and no way to defend. If craftsmanship
stays, it needs a defined, evidence-based basis — for example an independent
expert assessment on file, which is `B006` — and must never be derived from
photographs or price. Otherwise the honest move is to drop the category and
redistribute its weight.

Also noted: the trust classification runs to AAA/AA/A grades. These read as
credit ratings, and a museum or insurer will reasonably interpret them that way.
Whatever the grade is derived from must be defensible in that light.

---

## How to close these

Each item above is a one-line decision. Record the choice in `CHANGELOG.md`,
update the relevant `spec/*.json`, and only then change code — in that order, so
the standard leads the implementation rather than being back-filled to match it.
