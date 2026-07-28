# Product Registration Certificate — measured design spec

Source: `certificates/Official product registration certificate.png`, **1024 × 1536** (2:3).

Measured the same way as the Certificate of Authenticity: one-off GD scripts that
scan the PNG for the frame edges, for the numbered green section tabs (dilated
dark-pixel runs, grouped across consecutive rows), for the card column edges
(longest vertical run of non-cream pixels per column inside a row band), and for
text bounding boxes to derive type sizes. All numbers below are source pixels,
so the implementation renders on a fixed 1024px canvas and scales the whole
sheet as one unit — the document only ever gets smaller, it never reflows.

Implementation: `resources/views/pages/certificate-product-registration.blade.php`.
Ornaments are reused from `pages/partials/coa-ornaments.blade.php`.

## Frame

| Element | Measurement |
|---|---|
| Outer dark edge | 7px, `#04120A` |
| Ornamental band | 47px left and right, 21px top and bottom, `#062816` with the kente lattice in gold |
| Registry wordmark | set vertically down the left band, ~13px, tracking .34em, `#E8C878` |
| Cream page | x 65–981 (**916 wide**), `#FAF5E9`, 1px gold rule |
| Content box | x 78–968 (**890 wide**), i.e. 13px inset inside the cream |

The artwork's side bands are genuinely uneven — 58px on the left (it carries the
vertical wordmark) against 43px on the right. The implementation uses 47px on
both sides, which preserves the 916px cream width exactly while removing an
asymmetry that reads as a printing fault rather than as design.

## Header — y 28–258

| Element | Box |
|---|---|
| Wax seal medallion | x 65–180, y 95–195 (d ≈ 112) |
| Logo lockup | wordmark y 40–90, tagline y 100–110 |
| Title `PRODUCT REGISTRATION CERTIFICATE` | x 218–800 (w 582), y 163–194 — cap-height 32 → ~45px serif, condensed to fit |
| Ribbon `PRC — OFFICIAL REGISTRATION…` | x 289–735, y 207–228 (h 22), ~10.5px |
| Intro, 2 lines | y 234–258, ~11px, centred |
| Cameroon silhouette | right-hand watermark, ~128 × 146, low opacity |

## Section tabs — measured y of the ink

| Row | Sections | Tab ink y | Achieved |
|---|---|---|---|
| A | 1 · features column | 264–286 | **264** |
| B | 2 · 3 · 4 | 567–584 | 579 |
| C | 5 · 6 · 7 | 839–854 | 842 |
| D | 8 · 9 · ownership · sustainability | 999–1013 | 974 |
| E | geography · rights · related · statement | 1159–1173 | 1152 |
| F | audit · verification · authority · standards | 1291–1305 | 1295 |
| — | footer band top | 1424 | 1469 |

Tabs are 24px pills overhanging the card top-left corner by 12px; card tops sit
about 7px below the tab's top edge, and rows are 19px apart (the artwork's 12px
gap plus that overhang).

Sheet height: **1536** measured, **≈1582** achieved (+3%). The overrun is
entirely in the last row, where the audit trail and verification blocks carry
more real timestamps than the artwork's mock-up did.

## Column grids (measured card edges)

| Row | Columns |
|---|---|
| A | 78–822 (744) · 831–968 (137) |
| B | 78–384 (306) · 393–676 (283) · 685–968 (283) |
| C | 78–384 · 393–676 · 690–968 |
| D | 78–560 (482) · 566–786 (220) · 793–968 (170) |
| E | 78–245 (167) · 251–433 (180) · 436–663 (226) · 670–968 (290) |
| F | 79–242 (165) · 247–427 (180) · 436–786 (342) · 793–968 (176) |

Inside section 1: QR panel x 645–805 / y 285–440; barcode panel x 590–805 /
y 455–535; features column x 831–968 with rows ~25px apart.

## Type

| Use | Size |
|---|---|
| Section tab | 11.5px, 700, tracked .045em, uppercase |
| Row label / value | 9.5px (8px in the three narrow columns, 7–7.5px in the audit trail) |
| Body prose | 8–9.5px, line-height 1.4 |
| Statement / disclaimer strip | 8px, uppercase for the strip |
| Footer strapline | 15px / 10.5px |

## Palette

| Token | Value |
|---|---|
| Frame green | `#062816`, crown `#0D4325`, edge `#04120A` |
| Gold | `#C9A24B`, light `#F0D79A`, deep `#8A6A22` |
| Cream page | `#FAF5E9`, card `#FDFAF1` |
| Tab green | `#10502C` → `#06301A`, tab text `#F6E4B0` |
| Registry number red | `#8A1F14` |
| Ink | `#1D1B16`, muted `#6B6659`, label `#3A362D` |
| Valid green / revoked red | `#0C7A3E` / `#A11A12` |

## Deviations from the PNG, and why

The artwork's SECURITY FEATURES column and several of its rows name measures
this platform has never taken. A labelled row on a certificate is read as a
check that was performed, so each of these is omitted rather than rendered:

- **AI Fingerprint ID / AI Visual Fingerprint** — there is no model. The slot is
  taken by the perceptual image hash that `ProductCertificate::perceptualHash()`
  really computes, labelled as an image hash.
- **Invisible Watermark ID** — the row renders only when
  `product_certificates.watermark_ref` is actually set. Nothing currently writes
  it, so it is absent, and a test asserts that it stays absent.
- **Holographic seal, embossed seal, UV reactive ink, invisible fibres,
  anti-copy pattern, tamper-evident, microtext, guilloché** — physical print
  properties that cannot exist on a screen. Decorative treatment is fine; a
  caption claiming the property is not, so none is written.
- **C2PA Provenance Ref** — nothing in this codebase writes a provenance
  manifest. Omitted entirely rather than shown as pending.
- **Barcode** — kept, but encoded for real. The bars are a genuine Code 39
  encoding of the PRN, generated in the view. A decorative barcode that scans as
  noise is worse than none.
- **Handwritten signatures** (sections 1, 3 and 19 of the artwork) — the
  platform holds no specimen signature from anyone and never asked for one.
  Drawing a flourish would be forging the maker's hand. The blocks carry instead
  the signature that genuinely exists, named for the algorithm that produced it
  (Ed25519 with its published key id when the certificate has one, otherwise the
  stored HMAC-SHA-256), plus the certification authority seal.
- **"To be issued" certificates** (OTC, PPC, EAC, EC, RC) — a certificate that
  does not exist is not a fact about this product. Only the certificates that
  really exist are listed.
- **INTERNATIONAL COMPLIANCE ticked "Compliant"** — nobody has audited this
  platform against any of those standards. The block is retitled *Standards
  used* and names each standard beside the place this document uses it, which
  the reader can check on the page in front of them. GDPR compliance is not
  claimed at all.
- **Sustainability ticks** — rendered from the product's own stored fields, only
  where the artisan filled them in, and captioned "declared by the workshop, not
  audited".
- **Intellectual property block** — the artwork asserts a copyright claim, a
  rights holder, a design registration and a licensing status. No office has
  examined any of them. What remains is who declared the work and the statement
  that registration confers no such right.
- **Verification ticks** — rendered only for the keys present in
  `ProductFlags::checks()`. An absent key means the platform cannot determine
  the answer, and that renders as nothing at all: an unticked box is a printed
  "no", and we are no more entitled to it than to the "yes". The card says
  explicitly that a tick means nothing was reported, not that anything was
  investigated.

Every other row on the sheet is driven off the database and disappears when its
value is null, so the blocks fill to different heights for different products.
That is why the measured geometry above is a target rather than a fixed frame.
