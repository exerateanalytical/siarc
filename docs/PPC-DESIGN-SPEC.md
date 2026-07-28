# Product Provenance Certificate (PPC) — design spec

The PPC is the longest document the platform issues: a three-sheet dossier a
researcher, a gallery or an auction house reads end to end. It is compiled from
`App\Support\ProvenanceDossier` and `App\Support\ProvenanceRegistry`, and it is
rendered by the single view `resources/views/pages/certificate-provenance.blade.php`
at `/certificat-provenance/{slug}` (`?lang=fr|en`).

## Geometry

| | Artwork PNG | Rendered |
|---|---|---|
| Sheet 1 | 1024 × 1536 | 1024 canvas, height set by content |
| Sheet 2 | 1024 × 1536 | same canvas |
| Sheet 3 | 1024 × 1536 | same canvas |

All three sheets live inside one `.coa-page` at the artwork's own 1024px width,
and the whole stack is scaled by a single transform. Scaling per sheet would let
sheet 2 end up a different size from sheet 1 on a narrow screen, and a reader
would stop believing the three pages are one file. Height is not fixed to the
artwork's 1536: the register decides how many rows there are, and a document
that clips its own ownership chain to hit a design measurement is worse than a
long one. In print each sheet is one A4 page (`break-after: page`).

The burgundy bezel matches the PPC colour on the shared classification band
(`partials/certificate-band.blade.php`, `$code = 'PPC'`), which runs down the
left edge of every sheet.

## What each sheet carries

Every sheet repeats the certificate number and its own `Page n of 3`, because a
sheet that comes loose from the dossier must still be identifiable.

- **Sheet 1 — executive certificate.** Dossier identity (COA number, UUID, PRN,
  OLN, GAN, product UUID, issue date, status), QR + verification PIN, the piece
  with its photograph and image hash, original creator, current holder,
  provenance summary counts, the executive timeline, the geographic journey and
  the register-status ticks.
- **Sheet 2 — provenance record.** The full ownership chain in the register's
  order, transfer certificates, the detailed journey, and the documented events
  grouped by type (exhibitions, museum accessions, gallery representation,
  publications, press, awards, restorations, conservation, condition reports,
  valuations), each with its own empty sentence.
- **Sheet 3 — verification and register.** Related register documents, the
  Legacy Index with its bases, the audit trail, what the document does and does
  not say, the certification authority signature block, the verifiable-feature
  legend and the standards used.

## The dossier's number

There is no PPC number anywhere in the register. The artwork's
`AH237-PPC-CM-…` string would be a reference nobody could look up, so the
certificate of authenticity's number — the record this dossier is compiled from
— is what is printed on all three sheets and what the QR resolves to.

## The Legacy Index

`legacyIndex()` returns a total, a denominator and one `basis` sentence per
category. The view prints all three, unaltered.

- The printed total is `total / max` exactly as returned. The artwork's designed
  96/100 is not reachable: the denominator is the sum of the *assessable*
  categories only, so a well-documented piece with nothing to conserve and no
  appraisal is scored out of 75, not 100.
- A category with `max` 0 prints the words **Not assessed** in a dashed slate
  chip, never a fraction, and is visibly outside the total. Scoring an absence is
  unfair in both directions.
- Assessed categories print `score/max` **without spaces around the slash**,
  while the total prints `total / max` **with** them. This is deliberate: a
  genuine "0 out of 20" (no supporting documents filed — an assessable finding)
  must never be confusable with a category nobody could judge, and the test
  asserts on the spaced form.
- Every category prints its `basis`, so the holder can read the score, disagree
  with it, and see which missing document would move it.
- One line states what the number is about: it measures the **completeness of
  the documented record, not the merit of the work or the maker**.

## Empty states

`journey()` returning `[]` prints a sentence saying no country has been recorded
and that a default origin would be an invention — never a placeholder country
and never the artwork's five-pin world map. Each event type prints its own
"nothing recorded" sentence rather than a shared "none", because "no exhibition
recorded" and "no appraisal recorded" are different findings about a file.

## What the artwork asks for and this view refuses

Omitted, with the reason: rainbow holographic shield, embossed gold seal,
reactive ink, ghost portrait watermark, latent image, invisible watermark,
anti-copy as a *claim*, contactless chip, GS1 barcode, C2PA reference,
"AI fingerprint" / "AI feature vector", blockchain, "legally recognized",
"punishable by law". Every one is either a property of a security print run or a
piece of hardware that no web page and no home printout carries, or a capability
nothing behind this platform has. They are the most damaging possible additions,
because they are exactly the features a reader would "check" by looking at a
picture of them.

The perceptual hash is printed under the honest label **image hash**, described
as a 64-bit hash of the photograph that detects a changed image and does not
examine the object.

Awards print only from genuine `award` rows. No UNESCO, ministry or SIARC
honour is invented. An event row saying the piece was *exhibited at* SIARC is a
fact about where the object was shown and renders as a plain register entry;
nothing on the sheet dresses it as an endorsement, and each block states how
many of its rows were confirmed against the institution's own record.

The four handwritten signatures on the artwork — two of them belonging to named,
real people — are not drawn. No specimen signature exists in this platform, and
drawing one under a real person's name is a forgery with extra steps. The
parties are text entries with their register references, and the only signature
on the sheet is the detached Ed25519 one, printed in full with its key id and
the URL of the published key (`/.well-known/jwks.json`).

## Tests

`tests/Feature/ProvenanceCertificateTest.php` guards reachability, both
languages, all three sheets and their identity, the register numbers, holder
order, the refusals above, the Legacy Index total, denominator, bases and
unassessed rendering, and the empty states.

Two fixtures were resequenced while this view was built, and neither assertion
was weakened. The Legacy Index tests now read the register *after* the request,
because rendering the certificate is itself a registry act (the registry number
is assigned on first use) and an index taken beforehand describes a file one
document shorter than the one the reader is holding. The no-journey test now
clears the founding ownership row's country explicitly, so the empty journey it
asserts on is a real state rather than an accident of the factory.
