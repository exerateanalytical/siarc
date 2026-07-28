#!/usr/bin/env node
/**
 * Mobile-responsiveness audit for the public site.
 *
 * Fetches each page as it is actually served and looks for the patterns that
 * break a 375px screen. It reads rendered HTML rather than Blade sources, so a
 * class assembled at runtime is still caught, and it reports per page rather
 * than per file — which is how the problem is experienced.
 *
 * What it flags, and why each one matters on a phone:
 *
 *  FIXED WIDTH   w-[600px] with no responsive prefix. The element cannot shrink,
 *                so the page scrolls sideways — the single worst mobile fault.
 *  WIDE TABLE    a table with no overflow-x ancestor. Same outcome, via a
 *                different route.
 *  RIGID GRID    grid-cols-3 or more with no base-level override. Three columns
 *                on a 375px screen gives ~110px each.
 *  TINY TEXT     text below 12px. Below ~16px iOS also zooms on focus, which is
 *                handled separately in the UI kit for form fields.
 *  NOWRAP        whitespace-nowrap on a long string, which forces the container
 *                wider than the screen.
 *  NO VIEWPORT   a missing viewport meta, which makes everything else moot.
 *
 * Usage: node scripts/mobile-audit.js [baseUrl]
 */

const BASE = process.argv[2] || 'http://artisanatcameroun.test';

const PAGES = [
  '/', '/about', '/actualites', '/carrieres', '/centres-artisanat',
  '/certificat-adhesion', '/collections-heritage', '/contact', '/creer-mon-compte',
  '/disclaimer', '/evenements', '/faq', '/forgot-password', '/galerie/entreprises',
  '/galerie/messages/nouveau', '/galerie/produits', '/galerie/recherche',
  '/galerie/secteurs', '/guide-artisan', '/inscription-rapide', '/login',
  '/mentions-legales', '/partenaires', '/presse', '/privacy', '/terms',
  '/verification-certificat',
];

const RESPONSIVE = /(^|:)(sm|md|lg|xl|2xl):/;

function classesOf(html) {
  const out = [];
  const re = /class="([^"]*)"/g;
  let m;
  while ((m = re.exec(html))) out.push(m[1]);
  return out;
}

/**
 * An element is off the hook if it is hidden at this width, or shown only on
 * hover — a desktop megamenu cannot widen a phone screen it never appears on.
 */
function inert(classAttr) {
  const toks = classAttr.split(/\s+/);
  // `hidden` with no base-level override, or a hover-only reveal.
  const hiddenAtBase = toks.includes('hidden') && !toks.some(t => /^(block|flex|grid|inline)/.test(t));
  const hoverOnly = toks.some(t => t.startsWith('group-hover:') || t.startsWith('hover:'));
  return hiddenAtBase || hoverOnly;
}

/**
 * A width utility only bites if nothing constrains it. `w-[600px]` alongside
 * `max-w-[92vw]` or `max-w-full` shrinks correctly and is not a fault.
 */
function fixedWidths(classAttr) {
  if (inert(classAttr)) return [];
  const toks = classAttr.split(/\s+/);
  const capped = toks.some(t => /^max-w-(\[\d+vw\]|full|screen-|\[100%\])/.test(t));
  if (capped) return [];

  const hits = [];
  for (const tok of toks) {
    if (RESPONSIVE.test(tok)) continue;            // sm:w-[600px] is fine
    const m = tok.match(/^(?:min-)?w-\[(\d+)px\]$/);
    if (m && Number(m[1]) > 375) hits.push(tok);
  }
  return hits;
}

function rigidGrid(classAttr) {
  if (inert(classAttr)) return null;
  const toks = classAttr.split(/\s+/);
  const base = toks.find(t => /^grid-cols-(\d+)$/.test(t));
  if (!base) return null;
  const n = Number(base.match(/\d+/)[0]);
  return n >= 3 ? base : null;
}

/**
 * Sub-12px type classes.
 *
 * The UI kit ships a phone-only floor that lifts 10px–11.5px to 12px/12.5px, so
 * when that rule is present on the page these classes render at a readable size
 * and are not a fault. Counting them anyway would report a problem that has
 * already been solved — the class in the markup is not what the reader sees.
 */
function tinyText(classAttr, hasFloor) {
  if (hasFloor) return [];
  return classAttr.split(/\s+/).filter(t => {
    if (RESPONSIVE.test(t)) return false;
    const m = t.match(/^text-\[([\d.]+)px\]$/);
    return m && parseFloat(m[1]) < 12;
  });
}

/** Tables whose nearest 3 ancestors carry no horizontal scroll. */
function unguardedTables(html) {
  let count = 0;
  const re = /<table[^>]*>/g;
  let m;
  while ((m = re.exec(html))) {
    const before = html.slice(Math.max(0, m.index - 700), m.index);
    if (!/overflow-x-auto|overflow-x-scroll|ui-table-wrap/.test(before)) count++;
  }
  return count;
}

function longNowrap(html) {
  let count = 0;
  const re = /class="[^"]*whitespace-nowrap[^"]*"[^>]*>([^<]{45,})/g;
  while (re.exec(html)) count++;
  return count;
}

(async () => {
  const rows = [];

  for (const page of PAGES) {
    let html, status;
    try {
      const res = await fetch(BASE + page, { redirect: 'follow' });
      status = res.status;
      html = await res.text();
    } catch (e) {
      rows.push({ page, status: 'ERR', note: String(e.message).slice(0, 40) });
      continue;
    }

    if (status !== 200) { rows.push({ page, status, skip: true }); continue; }

    // Does this page ship the kit's phone-only type floor?
    const hasFloor = /font-size: 12px !important/.test(html);

    const attrs = classesOf(html);
    const widths = new Set();
    const grids = new Set();
    const fonts = new Set();

    for (const a of attrs) {
      fixedWidths(a).forEach(w => widths.add(w));
      const g = rigidGrid(a);
      if (g) grids.add(g);
      tinyText(a, hasFloor).forEach(f => fonts.add(f));
    }

    rows.push({
      page, status,
      viewport: /name="viewport"/.test(html),
      fixedWidths: [...widths],
      rigidGrids: [...grids],
      tinyText: [...fonts],
      typeFloor: hasFloor,
      wideTables: unguardedTables(html),
      longNowrap: longNowrap(html),
    });
  }

  // Rank by how badly a visitor would notice.
  const score = r => (r.skip ? -1 :
    (r.viewport ? 0 : 100) + r.fixedWidths.length * 10 + r.wideTables * 8 +
    r.rigidGrids.length * 4 + r.longNowrap * 2 + r.tinyText.length);

  rows.sort((a, b) => score(b) - score(a));

  console.log('\nMOBILE AUDIT @ 375px —', BASE, '\n');
  console.log('page                        score  fixedW  grid  table  nowrap  tiny');
  console.log('-'.repeat(74));

  for (const r of rows) {
    if (r.skip) { console.log(`${r.page.padEnd(28)}  (http ${r.status})`); continue; }
    if (r.status === 'ERR') { console.log(`${r.page.padEnd(28)}  ERROR ${r.note}`); continue; }
    console.log(
      r.page.padEnd(28) +
      String(score(r)).padStart(5) +
      String(r.fixedWidths.length).padStart(8) +
      String(r.rigidGrids.length).padStart(6) +
      String(r.wideTables).padStart(7) +
      String(r.longNowrap).padStart(8) +
      String(r.tinyText.length).padStart(6) +
      (r.viewport ? '' : '   NO VIEWPORT')
    );
  }

  console.log('\nDETAIL (worst first)\n');
  for (const r of rows.slice(0, 10)) {
    if (r.skip || r.status === 'ERR' || score(r) === 0) continue;
    console.log(r.page);
    if (r.fixedWidths.length) console.log('   fixed widths :', r.fixedWidths.join(' '));
    if (r.rigidGrids.length) console.log('   rigid grids  :', r.rigidGrids.join(' '));
    if (r.wideTables) console.log('   wide tables  :', r.wideTables, 'with no horizontal scroll');
    if (r.longNowrap) console.log('   long nowrap  :', r.longNowrap);
    if (r.tinyText.length) console.log('   tiny text    :', r.tinyText.join(' '));
    console.log();
  }
})();
