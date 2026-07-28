# Certificate security features — what is real, and what would need a printer

The platform issues four certificates. Their designs call for a set of "security
features". Some of those are genuinely renderable and are implemented; the rest
are physical properties of ink, foil and paper, and no web page or home printout
can carry them.

This document exists because the difference is a security claim, not a design
detail. Captioning a gradient on a screen "hologram", or a strip of colour "UV
reactive ink", tells a buyer that a check exists which nobody can perform. That
would be worse than having no feature at all, because it teaches buyers to accept
a mark that any forger can copy in five minutes. **Nothing on any screen or PDF
the platform produces may be labelled with a feature from the second section.**

The artwork for the implemented features lives in
`resources/views/pages/partials/coa-security.blade.php`, reviewable in local
development at `/apercu-securite`.

---

## 1. Implemented — on screen and in the printed output

These are present in every certificate the platform renders, and survive being
printed on an ordinary office printer.

### Guilloché
Interlaced line work generated from hypotrochoid equations (`coaGuillocheBorder`,
`coaGuillocheRosette`). The parameters, and the reasoning behind them, are in the
partial. Its security value is real but modest: the curves are finer than most
reproduction chains resolve, so a photocopy or a phone photo visibly coarsens
them. It is not a cryptographic check and must not be described as one.

### Microtext
Real SVG `<text>` at 1.6–1.8px reading `ARTISANHUB237 CERTIFICATION AUTHORITY •`
(`coaMicrotextLine`, `coaMicrotextPath`). Illegible at 100%, legible on zoom or
under a loupe on a good print. Because it is vector text and not an image of
text, it stays sharp at any scale in the original and degrades in a rescan.

### Anti-copy fine-line screen
`coaAntiCopy`: 0.25px lines at a 3px pitch, rotated 38°. Honest statement of what
it does — it does not prevent copying, it degrades a copy. A scanner or copier
resampling near this pitch produces moiré or fills the screen solid, so a
reproduction looks unlike the original.

### Serial numbering
Every certificate carries a certificate number allocated by the platform and
recorded server-side. Two documents bearing the same number is a detectable
condition.

### Certificate UUID
An opaque per-certificate identifier, independent of the human-readable serial,
used for verification lookups.

### QR verification
The QR encodes the public verification URL. The check happens against the
platform's own record — the QR carries no trust of its own, it is only a
shortcut to the lookup.

### Certificate content hash
A hash over the certified facts. If a field on a presented document differs from
what was certified, the hash will not reproduce.

### Ed25519 detached signature
`app/Support/CertificationAuthority.php` signs the certificate payload with an
Ed25519 private key held by the server; the public key is published at
`/.well-known/jwks.json` in standard JOSE form. This is the one feature on the
list that a third party can verify without trusting the platform's UI, and it is
therefore the feature the certificate should lead with.

### Tamper-evident hash chain
Certificate lifecycle events are appended to a hash chain
(`appendToChain`/`verifyChain`), so a removed or edited event breaks the chain.

---

## 2. Print-shop features — NOT implemented, and not claimable digitally

Each of these requires a security printer. The platform cannot assert any of
them, because the platform does not control the substrate the document is
printed on. If an operator commissions a print run, this is the specification
the printer would need.

### UV-reactive (fluorescent) ink
**What it is.** Ink invisible under normal light that fluoresces under a 365nm
UV lamp.
**Why it cannot be asserted digitally.** A screen emits its own visible light and
has no ink; a home printer's toner is inert under UV. Printing a certificate
that says "UV ink" produces a document whose stated check fails on every genuine
copy.
**If commissioned.** Supply the UV element as a separate 100% spot-colour plate,
named `UV-INVISIBLE`, artwork in vector outline, minimum stroke 0.3mm and
minimum type 6pt (fluorescent inks spread). Specify the excitation wavelength
(365nm long-wave is standard) and the emission colour. Do not overprint the UV
element on heavy dark ink, which quenches it.

### Holographic foil / hot stamping
**What it is.** A metallised film with a diffraction pattern applied under heat
and pressure, producing an image that changes with viewing angle.
**Why it cannot be asserted digitally.** The whole security property is
angle-dependent diffraction, which a fixed-pixel screen cannot produce. The
platform ships `coaHoloGradient`, which is explicitly named and documented as an
*iridescent visual treatment*, and is never captioned as a hologram.
**If commissioned.** A hologram needs an origination master (typically 4–8 weeks
lead time) plus a stamping die. Supply the foil footprint as a separate vector
plate, keep it away from folds and from the QR quiet zone, and specify whether
the hologram is generic stock or a custom-originated design — only a custom
origination has any anti-counterfeiting value, and it should carry a covert
layer (e.g. a hidden-image or micro-relief element) whose specification is held
by the operator, not published.

### Embossing / blind embossing
**What it is.** A raised or recessed relief pressed into the stock with a
male/female die.
**Why it cannot be asserted digitally.** It is a physical deformation of paper;
a screen has no relief, and a printout has none either.
**If commissioned.** A brass die at the seal position, single-level or
multi-level bevel, minimum feature 0.5mm, minimum 1.5mm clear of the trim edge.
Emboss depth depends on stock weight — specify a stock of at least 250gsm.

### Intaglio
**What it is.** Ink laid so thickly from an engraved plate that the print is
palpably raised; it is the tactile feature on most banknotes.
**Why it cannot be asserted digitally.** It requires an intaglio press. It is
also the single most expensive feature here and is realistic only at volume.
**If commissioned.** Engraved plate, line depth and ink film weight per the
printer's house standard; supply the intaglio element as its own line-art plate
with no halftones (intaglio cannot render tint screens).

### Security thread / planchettes / fibres
**What it is.** A thread or coloured/fluorescent fibres embedded *within* the
paper during manufacture.
**Why it cannot be asserted digitally.** It is a property of the papermaking, not
of anything printed. Nothing the platform generates can put a fibre inside a
sheet.
**If commissioned.** Ordered from the mill as custom stock, not from the printer:
specify thread type (window or embedded), pitch, and whether it carries
microprint or a magnetic/fluorescent signature. Minimum order quantities are
large — this only makes sense for a substantial run.

### Watermarked stock
**What it is.** A true watermark formed by varying paper thickness on the wire
during manufacture, visible in transmitted light.
**Why it cannot be asserted digitally.** A printed grey shape is not a watermark
and is trivially copied; a real one cannot be produced by any printer, only by a
paper mill. The certificate design's original "Watermark Reference" block was
dropped for this reason (see `docs/COA-DESIGN-SPEC.md`).
**If commissioned.** Custom mould-made stock; supply the watermark artwork as a
greyscale relief map, typically at least 40mm across — small watermarks do not
resolve. Long lead times and mill minimums apply.

---

## 3. Recommended wording for the certificate's security-features strip

The strip should describe what the reader can actually check, in the reader's
terms, and claim nothing else. Recommended captions:

| Caption (FR) | Caption (EN) |
|---|---|
| Signature numérique Ed25519 | Ed25519 digital signature |
| Empreinte du contenu | Content hash |
| Numéro de série unique | Unique serial number |
| Identifiant de certificat | Certificate identifier |
| Vérification par QR code | QR code verification |
| Journal inviolable | Tamper-evident log |
| Guilloché | Guilloché line work |
| Microtexte | Microtext |
| Trame anti-copie | Anti-copy line screen |

And a single qualifying line beneath the strip, which is the honest frame for
the whole block:

> **FR** — Ces éléments sont vérifiables sur ce document numérique et sur son
> impression. Ce certificat ne comporte aucun procédé d'impression sécurisée
> (encre UV, holographie, gaufrage, taille-douce, fil de sécurité, papier
> filigrané) : ne vous fiez pas à de telles mentions si elles apparaissent sur
> une copie.
>
> **EN** — These features are verifiable on this digital document and on a
> printout of it. This certificate carries no physical security printing (UV
> ink, holographic foil, embossing, intaglio, security thread, watermarked
> stock): do not trust such claims if they appear on a copy.

The second sentence is doing real work. It tells a buyer that a document
presented to them *with* a foil seal claiming to be one of ours is, by that fact
alone, suspect.
