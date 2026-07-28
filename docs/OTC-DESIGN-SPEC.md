# Ownership Transfer Certificate — measured design spec

Source: `certificates/Ownership Transfer Certificate (OTC).png`, **1024 × 1536**.

The artwork is a banknote-style sheet rather than the COA's framed page: a dark
rounded bezel, an ornamental gold band all round, a guilloché field under the
document, and a green spine down the binding edge. It was measured the same way
as its sister certificate — throwaway GD scripts scanning for card strokes,
long dark runs and fill changes. The PNG is a textured render, so single-pixel
luminance scans are noisy; the numbers below come from run-length scans that
require a stroke to persist across 70–90 % of a band, which is what separates a
card edge from paper grain.

All values are source pixels. The implementation draws at a fixed 1024px canvas
and scales the whole sheet as one unit, so the document never reflows into a
different arrangement — it only gets smaller.

## Frame

| Element | Measurement |
|---|---|
| Outer bezel | x 0–9, `#0D1411`, radius 18 |
| Ornamental gold band | 22px, x 9–31 and 993–1015, same top and bottom |
| Sheet | x 31–992 (**961 wide**), `#FDF9EF` |
| Spine | x 31–61, y 33–430, deep green, "ARTISANHUB237" set vertically |
| Guilloché field | centred rosettes, decorative only |

## Header — y 31–282

| Element | Box |
|---|---|
| Logo lockup | x 296–699 (w 403), y 51–110 (h 59) |
| Tagline | under the wordmark, ~10.5px, tracking .26em |
| Title | x 173–857 (w 684), y 165–200, cap-height 28 → ~41px condensed serif |
| "(OTC)" + gold rules | y 205–232 |
| Intro, 2 lines | y 245–272, ~10px uppercase, tracked |
| Authority shield | top right, x ~775–847 |

## Meta card — x 140–887, y 282–477

Three bands, split at y **339** and **420**; card bottom **477**.

| Band | Contents |
|---|---|
| y 282–339 | certificate number · UUID · issue date · status, 4 columns |
| y 339–420 | content hash · CA signature · verification URL + PIN |
| y 420–477 | the OLN plaque (x ~140–484, h 27) |

The QR panel is a column of its own down the right of the card and spans the
lower two bands — measured at x ~739–879.

## Column grid

Everything below the meta card divides into a left area **x 31–762** (731 wide)
and a right column **x 771–992** (221 wide), 8px apart.

| Row | y | Columns |
|---|---|---|
| Body | 507–795 | product identity **469** · original creator **255** (gap 7) |
| Owners | 803–1055 | previous **259** · new **242** · transfer **213** (gap 8) |
| Chain | 1063–1220 | chain **366** · condition **169** · checks **182** (gap 7) |
| Insurance/log | 1228–1344 | insurance **210** · audit **288** · evidence **212** · compliance **230** (gap 7) |
| Signatures | 1352–1467 | parties **670** · authority **283** (gap 8) |
| Foot bar | 1477–1499 | serial · statement pill · reference |
| Legal line | ~1513–1524 | centred, small caps |

Right column stack: security features 507–897, verification result 903–1100,
export information 1113–1217.

Card head bands are 27px (507→534). Ruled label/value rows run ~16px with a
~100px label column against a ~200px value column.

## Palette

| Token | Value |
|---|---|
| Bezel | `#0D1411` |
| Sheet / card | `#FDF9EF` / `#FFFDF7` |
| Card stroke | `#E3D3B0`; head band `#F8F1DF` → `#F1E7CE` |
| Gold | `#C9942E`, deep `#8A5F14`, light `#E9CE8B` |
| Deep green | `#123D24` → `#06200F` |
| Ink | `#1D1B16`, muted `#5D5745` |
| Certificate number red | `#B4141B`; tick green `#0F7A34` |

## Achieved against measured

Width, the frame, the spine, the meta card bands and every column boundary in
the table above are built to the measured numbers. The rendered sheet runs
**~1870px tall against the artwork's 1536**, and that difference is content,
not layout: the artwork's fields are short invented strings, and the register's
are not. The product card alone carries sixteen real attribute rows; the
content hash is 64 characters and the Ed25519 signature 86, printed in full
where the artwork showed a truncated fabrication and a drawn flourish. Blocks
sit at the measured x positions and in the measured order; the vertical rhythm
stretches where a real record is longer than a mock one.

## Deviations from the PNG, and why

The artwork advertises a set of protections this platform does not have. A
certificate is read as a statement of fact by people who cannot check it, so
every one of these was dropped rather than drawn.

**Removed outright**

- **AI Fingerprint ID** and **AI Fingerprint Match** — there is no model. The
  slot is taken by the real perceptual image hash, labelled as pHash: arithmetic
  over the pixels, not a judgement about them.
- **Holographic seal, embossed seal, watermark, UV reactive ink, microtext,
  tamper-evident foil, anti-copy pattern** — properties of a physical print run.
  Nothing about how a web page is served could make any of them true. The
  guilloché is kept as decoration and captioned nowhere.
- **"Secure QR — encrypted QR for real-time verification"** — the QR is a plain
  URL.
- **"Stored securely on the ArtisanHub237 blockchain registry and cannot be
  altered"** — there is no blockchain. There is a hash-linked event log
  (`CertificationAuthority::appendToChain` / `verifyChain`) where each entry
  carries its predecessor's digest, which gives the property the sentence was
  reaching for. That is what the panel says instead.
- **Digital evidence — six "linked records" behind View links** — none of those
  documents exist to link to, and six dead links imply six files a buyer could
  ask for. The panel is gone; its width went to the audit trail.
- **Handwritten signatures and the inked fingerprint plate** (artisan, both
  owners, the authority) and the two institutional rubber stamps. The register
  holds no signature images; a rendered scrawl beside a real name is the exact
  thing a provenance document exists to prevent. The Signatures panel keeps its
  geometry and prints who the parties were, the reference each is filed under,
  and when the transfer was entered.
- **Export Ready: Yes** — whether a work may lawfully leave the country turns on
  CITES schedules and cultural heritage law this platform holds no register of.
  `export_restricted` is deliberately absent from `ProductFlags::TICKS` for the
  same reason; a green clearance printed here would be invented.
- **Collection / Edition / Edition Number, Tax Reference, Damage Notes,
  Restoration Notes, Packaging, Certificate Included, Identity Verification** —
  no such columns.
- **C2PA Compliant** badge — nothing in the pipeline produces C2PA assertions.
- **"Legally recognized when verified online"** on the foot bar, and
  "punishable by law" on the legal line. A private register confers no legal
  recognition and prosecutes nobody. Both were rewritten to what is true: the
  authoritative copy is the online one, and alteration voids the sheet.

**Conditional**

- **Invisible Watermark Ref** — `certificates.watermark_ref` is a real nullable
  column, so the row prints only when something was actually stored in it.
- **Export information** and **Insurance** print only when their columns hold
  something. An empty export card on a document that crosses a border invites a
  customs officer to read blanks as clearances.
- **Declared value** is suppressed when `value_is_private`, and the row says the
  figure was withheld rather than vanishing silently — the reader should know
  there is a number and why they do not have it.

**Kept, because it is real**

Certificate number and UUID, the OLN and PRN, SHA-256 content hash, the Ed25519
CA signature with its key id (independently checkable at
`/.well-known/jwks.json`), verification PIN, QR, ISO 8601 timestamps, ISO 3166-1
country codes, ISO 4217 currency, the full ownership chain in sequence, transfer
details, condition, the `$flags` tick map — only the keys the map actually
carries, since an absent key means undeterminable and must never become a tick —
the audit trail, and verification counts. The signature and content hash are
re-verified at render time rather than read from a column, so a "valid" on the
sheet is a result and not a stored claim.
