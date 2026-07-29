# Mobile responsiveness — the contract

One set of numbers, defined once, consumed everywhere. This is to layout what
`docs/DARK-MODE-CONTRACT.md` is to colour: agents build against this file and
none of them invents a breakpoint, a gutter or a font size.

It exists because "make it responsive" had been said several times and kept
being un-said by the next page. Fixing named screens is worth little; the point
of a contract is that the *next* page cannot break the same way.

---

## 1. Target widths

| Width | What it is | Why it is in the list |
|---|---|---|
| **360** | entry Android (Galaxy A-series, Tecno, itel) | **the floor.** A large share of Cameroonian buyers hold exactly this. Nothing may break here. |
| **390** | iPhone 12–16 | the most common single width in analytics anywhere |
| **414** | large iPhone / phablet | catches layouts that only work at one width |
| **768** | tablet portrait / `md` | the width where desktop chrome starts appearing and the topbar historically overflowed |
| **1280** | the artwork's desktop width | the pixel-replica reference; must not regress |

360 is a hard floor, not an aspiration. If something has to give, it gives above
360, never below it.

### Testing below 489px — read this before you "resize the window"

Headless Chrome on this machine **will not give a window narrower than ~489 CSS
px**. `--window-size=360,800` silently lands at 489, so a "360px audit" run that
way is measuring a 489px page and reporting it green. This has to be said out
loud because the failure is invisible: the run passes.

The floor is a *window* floor, not a *viewport* floor. The way through it is
CDP's `Emulation.setDeviceMetricsOverride`, which sets the layout viewport
directly and is not subject to the OS window minimum.

`scripts/responsive-audit.cjs` is that harness and is the only supported way to
check a mobile width here. It launches Chrome with `--remote-debugging-port`,
drives it over the DevTools protocol using Node's built-in global `WebSocket`
(Node 22+, so **no npm dependency**), and every width it reports is a real one.

```bash
# audit every public route, at every contract width
node scripts/responsive-audit.cjs

node scripts/responsive-audit.cjs --widths 360 --routes /,/contact
node scripts/responsive-audit.cjs --shot / --widths 360,390,1280 --theme dark
node scripts/responsive-audit.cjs --shot / --widths 360 --click '#mobile-menu-btn'
node scripts/responsive-audit.cjs --shot /contact --widths 1280 --bottom   # footer
```

It exits non-zero on any violation, so it is usable as a gate. Routes come from
`php artisan route:list --json`, never from a list in the file.

> Git Bash mangles a bare `/` argument into a Windows path. Export
> `MSYS_NO_PATHCONV=1` before running it, or use PowerShell.

---

## 2. Type scale — mobile-first, not merely floored

**99.99% of the users hold a phone.** The previous version of this table set
only *minimums*: a 12px floor stops text being illegible, but a page whose whole
body sits on the floor still reads as a shrunken desktop site — which is what
the owner named when he called the mobile type "cheap". The apps our buyers
hold all day set body text at 15–17px (iOS HIG body 17pt, Material 3
body-large 16sp / body-medium 14sp, WhatsApp/Instagram ≈ 15–16px). This table
is now a **target ramp**, written phone-first: the mobile column is the size
the class states outright, and the desktop keeps its artwork-measured size
behind an `md:` prefix.

The ramp is enforced **below `md` (< 768px)** — the widths at which the reader
is holding the device in one hand. At 1024 and 1280 the desktop artwork governs
and its 9–10px uppercase micro-lettering (the logo strapline, the utility-row
icon labels) stays as drawn. This is not a loophole: it is the only reading that
is consistent with the pixel-replica mandate *and* with a legible phone.

| Role | Phone (< 768) — the stated size | Desktop — the measured size | Notes |
|---|---|---|---|
| `h1` page title | **22–26px** | 34–44px | one per page |
| `h2` section head | **18–20px** | 26–30px | |
| `h3` card title | **15–16px** | 18–20px | |
| `h4` label / eyebrow | **13–14px** | 15–16px | |
| **body** | **15–16px** | 12–16px as drawn | the default; anything a buyer reads |
| **secondary / meta** | **13–14px** | 11–12px | bylines, counts, timestamps |
| caption / label / legal | **12px — absolute floor** | 10.5–11.5px | badges, chips, table headers |
| form field text | **16px** | 12.5–15px | below 16px iOS zooms the page on focus |

**Line-height 1.5–1.6 for body copy on phones.** A 15px line set solid reads
worse than a 13px line with air.

How to write it — mobile value first, artwork value behind the breakpoint:

```html
<p class="text-[15px] leading-relaxed md:text-[12px] md:leading-normal">…</p>
<span class="text-[13px] md:text-[11px] text-neutral-500">…</span>
```

Two layers make the ramp real:

1. **The kit** (`pages/partials/ui-kit.blade.php`) states the phone sizes for
   every semantic class (`.ui-field` 16px, `.ui-btn` 14px, `.ui-card-sub` 13px,
   table cells 14px…) in its `@media (max-width: 767.98px)` blocks, and remaps
   the legacy sub-13px `text-[…px]` utilities upward (≤10.5 → 12, 11–11.5 → 13,
   12–12.5 → 14) so a page that has not yet been swept still reads at ramp
   sizes. The remap deliberately does not touch `md:`-prefixed classes.
2. **Swept pages** state the phone size directly in the markup, as above. The
   remap is a net, not the design: new code writes the mobile-first pair.

### Consistency with `docs/ARTISAN-PROFILE-V2-SPEC.md`

That spec measured the mobile artwork and found the ÷2 conversion produces
5.5–6.2px body copy, and recorded the owner's choice: **keep the artwork's
proportions, scale to readable sizes.** This table is that decision made
general. Preserve the *ratios* the artwork draws (hero name ≈ 2.8× the product
name, section heads ≈ 1.3× body); take the absolute values from the column
above. Where the two disagree, the readable size wins and the ratio is
re-derived from it.

---

## 3. Spacing and containers

| | 360–639 | 640–1023 (`sm`/`md`) | ≥ 1024 (`lg`+) |
|---|---|---|---|
| page gutter | **16px** (`px-4`) | 25px (`sm:px-[25px]`) | 25px |
| max content width | — | — | **1280px**, centred |
| card padding | 16px | 20px | 20–24px |
| section rhythm (vertical) | 32px | 40px | 48–64px |
| grid gap | 12–16px | 20px | 24–32px |

The canonical container is therefore:

```html
<div class="max-w-[1280px] mx-auto px-4 sm:px-[25px]">
```

Both `directory-header` and `directory-footer` use exactly that. Copy it; do not
invent a third gutter.

---

## 4. Tap targets — 44 × 44, non-negotiable

Every interactive element — link, button, icon button, select, checkbox row,
tab, pagination cell, bottom-nav item — is **at least 44px on both axes** below
`md`. It is the single most common mobile defect on this site and the one the
owner feels first.

How to get there without redrawing the artwork: keep the *visual* size and grow
the *target*.

```html
<!-- icon button -->
<button class="w-11 h-11 inline-flex items-center justify-center rounded-lg">

<!-- a dense list row: 12px type, 44px row -->
<a class="flex items-center min-h-[44px] md:min-h-0 text-[13px] md:text-[12px]">

<!-- a 28px social disc that must still be tappable -->
<a class="w-11 h-11 md:w-[28px] md:h-[28px] rounded-full …">
```

`-mr-2` / `-mx-1` restore the optical alignment the artwork has while the target
stays full size.

### The three exceptions, and they are the only three

1. **A link inside running prose.** A hyperlink in a sentence is text; growing
   it would break the line box. Mirrors WCAG 2.5.8's inline exception.
2. **A checkbox or radio whose label is the target.** The 16px box is a platform
   convention. The exemption applies **only** if the associated `<label>` is
   itself ≥ 44 × 44 — the audit measures the label and fails if it is not.
3. **`data-tap-exempt`** on the element, which must carry a comment saying why.
   Reach for this roughly never.

Nothing else is exempt, and there is no silent skip anywhere in the harness.

---

## 5. The rules that stop the regression

These are the ones that make next month's page safe.

1. **No horizontal page scroll, ever, at any width.**
   `document.documentElement.scrollWidth` must not exceed the viewport. This is
   checked at *every* contract width, not only the phone ones.
2. **Wide content scrolls inside its own container**, never the page. Tables,
   code blocks, diagrams, chip rows and timelines get
   `<div class="overflow-x-auto">…</div>`. That wrapper is also the audit's
   escape hatch: anything inside an `overflow-x` ancestor is not counted as page
   overflow.
3. **Images are fluid.** `max-w-full h-auto` (or `object-contain` in a sized
   box). No `<img>` with a fixed width and no responsive override.
4. **No fixed pixel width on a layout container.** `w-[600px]` on a wrapper
   cannot shrink and is how the page ends up 415px wide inside a 360px screen.
   Use `max-w-[600px] w-full`, or give the fixed width a responsive prefix
   (`lg:w-[600px]`).
5. **Grids collapse, and where they collapse is documented.**
   `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` — declare a base. A bare
   `grid-cols-3` gives 110px columns at 360.
6. **Flex rows need `min-w-0`.** A flex child defaults to `min-width:auto` and
   refuses to shrink below its content; that is what pushed the topbar to 415px.
   `min-w-0` on the shrinkable child, `shrink-0` only on things that genuinely
   must not shrink, and `truncate` on the text inside.
7. **`whitespace-nowrap` is a promise the phone cannot keep.** Allowed only on
   short strings (a price, a badge, "FR/EN"), never on a sentence.
8. **A viewport meta on every page**, `width=device-width, initial-scale=1`, and
   **never** `user-scalable=no` or `maximum-scale` below 5 — pinch-zoom is an
   accessibility right, not a style preference.
9. **`100vw` is not the viewport.** It includes the scrollbar and overflows by
   ~15px on desktop. Use `100%`.
10. **Fixed/sticky bars own their safe area.** `env(safe-area-inset-bottom)` on
    the bottom nav and anything that floats above it.

---

## 6. Where this is enforced

| Layer | File | What it catches |
|---|---|---|
| Static, in CI, every route | `tests/Feature/ResponsiveContractTest.php` | viewport meta, fixed widths, sub-floor type, rigid grids, nowrap sentences, unwrapped tables, non-fluid images — from the **rendered HTML of every public GET route enumerated from the router** |
| Live layout | `scripts/responsive-audit.cjs` | real overflow, computed font sizes, measured tap rects, at real 360px |
| Chrome behaviour | `tests/Feature/MobileChromeTest.php` | the mobile menu markup, the single-authority toggle, the footer's two mobile columns |

The PHPUnit tests are the gate — they run in `php artisan test` with everything
else and need no browser. The node harness is the measurement, because a
computed layout is not decidable from markup.

---

## 7. The mobile menu, since every page has it

`resources/views/pages/partials/directory-header.blade.php` owns it. Two things
about it are load-bearing and must not be "tidied":

- **It is shown and hidden by the `hidden` class**, not by a `data-` attribute
  alone. Twenty page views still bind their own legacy
  `mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'))`, and
  the partial does not own those files.
- **The header's handler does not toggle; it sets.** It reads the state it owns
  (`data-open` on the panel), flips that, and writes the classes to match. A
  listener on `document` always runs after a listener bound to the button
  itself, so whatever a legacy page handler did a microsecond earlier is
  overwritten. Turning that `setOpen(...)` back into a `classList.toggle(...)`
  re-breaks the hamburger on twenty pages at once — which is exactly the bug
  this contract was written after.

If you ever do delete the twenty legacy handlers, delete them all in one commit
and leave the authoritative version alone anyway.
