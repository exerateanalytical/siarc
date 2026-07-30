#!/usr/bin/env node
/**
 * Fold the @font-face rules into public/vendor/app.css, keeping only the
 * subsets this site can actually use.
 *
 * Two things this fixes.
 *
 * 1. Styling used to need two render-blocking requests — app.css and
 *    fonts.css. On a phone that is two round-trips before the page can look
 *    like anything. There is no reason for the second one: the font rules are
 *    static and belong in the same file.
 *
 * 2. resources/css/fonts-source.css declares Cyrillic, Greek, Devanagari and
 *    Vietnamese subsets. This is a French/English site; those woff2 files can
 *    never be selected by a French or English page. Browsers honour
 *    unicode-range and would not have downloaded them, so this is not a speed
 *    win at runtime — it is ~450 KB of dead weight removed from every upload,
 *    and 20-odd files that no longer have to be right.
 *
 * The @font-face blocks are emitted BEFORE the Tailwind output. Order does not
 * matter for correctness here (no selector collides), but a font-face the
 * browser meets early can start fetching sooner.
 *
 * Run by `npm run build:css`, after tailwindcss has written app.css.
 */

const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const SRC = path.join(ROOT, 'resources/css/fonts-source.css');
const CSS = path.join(ROOT, 'public/vendor/app.css');

/* Latin covers English and French entirely; latin-ext carries the rest of the
   Western European range. Everything else is unreachable on this site. */
const KEEP = new Set(['latin', 'latin-ext']);

const MARKER = '/*__FONTS__*/';

function main() {
  const src = fs.readFileSync(SRC, 'utf8');

  /* The source is Google's own output: each @font-face is preceded by a
     `/* subset *\/` comment naming its unicode-range. Pair them up. */
  const blocks = [...src.matchAll(/\/\*\s*([a-z-]+)\s*\*\/\s*(@font-face\s*\{[^}]*\})/g)];
  if (blocks.length === 0) throw new Error('no @font-face blocks parsed from ' + SRC);

  const kept = blocks.filter(([, subset]) => KEEP.has(subset));
  const dropped = blocks.length - kept.length;

  /* Collect the woff2 files still referenced, so the caller can prune. */
  const used = new Set();
  for (const [, , block] of kept) {
    const m = block.match(/url\(([^)]+)\)/);
    if (m) used.add(path.basename(m[1].trim().replace(/^["']|["']$/g, '')));
  }

  const fontCss = kept.map(([, , block]) => block.replace(/\s+/g, ' ').trim()).join('');

  let css = fs.readFileSync(CSS, 'utf8');
  /* Idempotent: strip a previously injected block before injecting again, so
     running this twice does not stack two copies of the font rules. */
  const start = css.indexOf(MARKER);
  if (start !== -1) {
    const end = css.indexOf(MARKER, start + MARKER.length);
    if (end === -1) throw new Error('found an opening font marker with no closing one in app.css');
    css = css.slice(0, start) + css.slice(end + MARKER.length);
  }

  fs.writeFileSync(CSS, MARKER + fontCss + MARKER + css, 'utf8');

  fs.writeFileSync(
    path.join(ROOT, 'storage/app/font-manifest.json'),
    JSON.stringify({ kept: [...used].sort() }, null, 2),
    'utf8'
  );

  console.log(
    `fonts: kept ${kept.length} @font-face (${[...KEEP].join(', ')}), dropped ${dropped}; ` +
    `${used.size} woff2 referenced; app.css now ${fs.statSync(CSS).size} bytes`
  );
}

main();
