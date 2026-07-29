#!/usr/bin/env node
/**
 * Live responsive audit + screenshot harness.  docs/RESPONSIVE-CONTRACT.md.
 *
 * Why this exists rather than "resize the window"
 * -----------------------------------------------
 * Headless Chrome on this machine refuses to give a window narrower than about
 * 489 CSS px — `--window-size=360,800` silently lands at 489, so a 360px audit
 * run that way is measuring a 489px page and reporting it green.  The floor is
 * a *window* floor, not a *viewport* floor, so the way through it is CDP's
 * `Emulation.setDeviceMetricsOverride`, which sets the layout viewport
 * directly and is unaffected by the OS window minimum.  Everything below runs
 * through that one call, which is why 360 is a real 360 here.
 *
 * No npm dependency: Chrome is launched with --remote-debugging-port and driven
 * over the DevTools protocol using Node's built-in global WebSocket (Node 22+).
 *
 *   node scripts/responsive-audit.js                  # audit every public route
 *   node scripts/responsive-audit.js --widths 360,768
 *   node scripts/responsive-audit.js --routes /,/contact
 *   node scripts/responsive-audit.js --shot /  --out storage/app/responsive
 *   node scripts/responsive-audit.js --shot / --theme dark --click '#mobile-menu-btn'
 */

const { spawn, execFileSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

const BASE = process.env.RESPONSIVE_BASE || 'http://artisanatcameroun.test';
/* A fixed port collides with a leftover Chrome (or anything else holding it),
   and Chrome's only complaint is "Cannot start http server for devtools" on a
   stream nobody reads. Pick a fresh one per run. */
const PORT = 9300 + Math.floor(Math.random() * 600);

/* Contract numbers — keep in step with docs/RESPONSIVE-CONTRACT.md. */
const FONT_FLOOR = 12;      // px, absolute
const TAP_MIN = 44;         // px, both axes
const DEFAULT_WIDTHS = [360, 390, 414, 768];

const argv = process.argv.slice(2);
const arg = (name, fallback = null) => {
  const i = argv.indexOf('--' + name);
  return i === -1 ? fallback : argv[i + 1];
};
const has = (name) => argv.includes('--' + name);

/* ── Chrome ─────────────────────────────────────────────────────────────── */

function chromePath() {
  const candidates = [
    process.env.CHROME_PATH,
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
  ].filter(Boolean);
  for (const c of candidates) if (fs.existsSync(c)) return c;
  throw new Error('Chrome not found. Set CHROME_PATH.');
}

async function launch() {
  const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'resp-audit-'));
  const proc = spawn(chromePath(), [
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--no-default-browser-check',
    '--disable-extensions',
    '--force-device-scale-factor=1',
    '--user-data-dir=' + profile,
    '--remote-debugging-port=' + PORT,
    'about:blank',
  ], { stdio: 'ignore' });

  for (let i = 0; i < 100; i++) {
    try {
      const r = await fetch(`http://127.0.0.1:${PORT}/json/version`);
      if (r.ok) return { proc, profile };
    } catch (e) { /* not up yet */ }
    await new Promise((r) => setTimeout(r, 100));
  }
  throw new Error('Chrome did not expose a debugging port.');
}

/** Minimal CDP client over the built-in WebSocket. */
class Cdp {
  constructor(ws) { this.ws = ws; this.id = 0; this.pending = new Map(); this.events = new Map(); }

  static async attach() {
    // The debugging port answers before the first page target is registered.
    let page = null;
    for (let i = 0; i < 60 && !page; i++) {
      const list = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
      page = list.find((t) => t.type === 'page');
      if (!page) await new Promise((r) => setTimeout(r, 100));
    }
    if (!page) throw new Error('Chrome exposed no page target.');
    const ws = new WebSocket(page.webSocketDebuggerUrl);
    await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });
    const cdp = new Cdp(ws);
    ws.onmessage = (m) => {
      const msg = JSON.parse(m.data);
      if (msg.id && cdp.pending.has(msg.id)) {
        const { resolve, reject } = cdp.pending.get(msg.id);
        cdp.pending.delete(msg.id);
        msg.error ? reject(new Error(msg.error.message)) : resolve(msg.result);
      } else if (msg.method) {
        (cdp.events.get(msg.method) || []).forEach((fn) => fn(msg.params));
      }
    };
    return cdp;
  }

  send(method, params = {}) {
    const id = ++this.id;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.ws.send(JSON.stringify({ id, method, params }));
    });
  }

  on(method, fn) {
    if (!this.events.has(method)) this.events.set(method, []);
    this.events.get(method).push(fn);
  }

  async eval(expression) {
    const r = await this.send('Runtime.evaluate', {
      expression, returnByValue: true, awaitPromise: true,
    });
    if (r.exceptionDetails) throw new Error(r.exceptionDetails.text + ' ' + (r.exceptionDetails.exception || {}).description);
    return r.result.value;
  }
}

/* ── The measurement, run inside the page ───────────────────────────────── */
/* Everything the contract can only be checked for at layout time.  Returned as
   plain data so the reporting stays out here. */
const PROBE = (fontFloor, tapMin) => `(() => {
  const out = { overflow: [], tiny: [], taps: [], docWidth: 0, viewport: innerWidth };
  const de = document.documentElement;
  out.docWidth = Math.max(de.scrollWidth, document.body ? document.body.scrollWidth : 0);

  const visible = (el, cs) =>
    cs.display !== 'none' && cs.visibility !== 'hidden' && cs.opacity !== '0' &&
    el.getClientRects().length > 0;

  const desc = (el) => {
    let s = el.tagName.toLowerCase();
    if (el.id) s += '#' + el.id;
    const c = (el.getAttribute('class') || '').trim().split(/\\s+/).slice(0, 4).join('.');
    if (c) s += '.' + c;
    return s;
  };

  const all = document.querySelectorAll('body *');
  for (const el of all) {
    const cs = getComputedStyle(el);
    if (!visible(el, cs)) continue;
    const r = el.getBoundingClientRect();

    /* 1. Horizontal overflow: anything painting past the viewport's right edge,
          unless it lives inside a container that scrolls horizontally on
          purpose (the contract's escape hatch), or is a fixed/sticky overlay
          the page deliberately positions off-canvas. */
    if (r.right > innerWidth + 1 || r.left < -1) {
      let scoped = false;
      for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
        const pcs = getComputedStyle(p);
        if (/(auto|scroll|hidden|clip)/.test(pcs.overflowX)) { scoped = true; break; }
      }
      if (!scoped && r.width > 0 && r.height > 0) {
        out.overflow.push({ el: desc(el), left: Math.round(r.left), right: Math.round(r.right), w: Math.round(r.width) });
      }
    }

    /* 2. Type floor: measured on elements that actually own text. */
    const ownsText = [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim().length > 1);
    if (ownsText) {
      const fs = parseFloat(cs.fontSize);
      if (fs && fs < ${fontFloor} - 0.01) {
        out.tiny.push({ el: desc(el), size: +fs.toFixed(2), text: el.textContent.trim().slice(0, 40) });
      }
    }
  }

  /* 3. Tap targets. Only real, visible, in-flow controls; an inline link inside
        a paragraph is text, not a target, and is excluded the way WCAG 2.5.8
        excludes it. */
  const controls = document.querySelectorAll('a[href], button, [role="button"], input:not([type="hidden"]), select, textarea, summary');
  for (const el of controls) {
    const cs = getComputedStyle(el);
    if (!visible(el, cs)) continue;
    if (el.hasAttribute('data-tap-exempt')) continue;
    /* A checkbox or radio is a 16px box by convention on every platform. It is
       a legitimate target when its label is the real click surface, so the
       label is measured instead of the box. No label, no exemption. */
    if (/^(checkbox|radio)$/.test(el.type || '')) {
      const lbl = el.closest('label') || (el.id && document.querySelector('label[for="' + CSS.escape(el.id) + '"]'));
      if (lbl) {
        const lr = lbl.getBoundingClientRect();
        if (lr.height >= ${tapMin} - 0.5 && lr.width >= ${tapMin} - 0.5) continue;
        out.taps.push({ el: desc(el) + ' (label ' + Math.round(lr.width) + 'x' + Math.round(lr.height) + ')', w: Math.round(lr.width), h: Math.round(lr.height), text: lbl.textContent.trim().slice(0, 30) });
        continue;
      }
    }
    const inSentence = el.tagName === 'A' && (() => {
      const p = el.parentElement;
      if (!p) return false;
      if (!/^(P|LI|SPAN|TD|LABEL|SMALL|EM|STRONG)$/.test(p.tagName)) return false;
      return p.textContent.trim().length > el.textContent.trim().length + 3;
    })();
    if (inSentence) continue;
    const r = el.getBoundingClientRect();
    if (r.width < 1 || r.height < 1) continue;
    if (r.width < ${tapMin} - 0.5 || r.height < ${tapMin} - 0.5) {
      out.taps.push({ el: desc(el), w: Math.round(r.width), h: Math.round(r.height), text: el.textContent.trim().slice(0, 30) });
    }
  }
  return out;
})()`;

/* ── Routes, enumerated from the router, never from a hand list ─────────── */

function publicRoutes() {
  const php = process.env.PHP_BIN || 'php';
  const json = execFileSync(php, ['artisan', 'route:list', '--json'], {
    cwd: path.resolve(__dirname, '..'), maxBuffer: 64 * 1024 * 1024,
  }).toString();
  const rows = JSON.parse(json.slice(json.indexOf('[')));
  const skip = /auth|verified|password\.confirm|siac\.auth|admin/;
  return rows
    .filter((r) => (r.method || '').includes('GET'))
    .filter((r) => !/\{/.test(r.uri))                     // no parameters to invent
    .filter((r) => !skip.test(r.middleware || ''))
    .filter((r) => !/^(_|api|docs|sanctum|storage|livewire|telescope|horizon)/.test(r.uri))
    /* robots.txt, sitemap.xml and jwks.json are not pages. Chrome renders plain
       text in a non-wrapping <pre>, which reports as a 980px-wide document at
       360 — a finding about the browser, not about the site. */
    .filter((r) => !/\.(txt|xml|json|csv|pdf|ico)$/.test(r.uri))
    .map((r) => '/' + (r.uri === '/' ? '' : r.uri))
    .filter((v, i, a) => a.indexOf(v) === i)
    .sort();
}

/* ── Runs ───────────────────────────────────────────────────────────────── */

async function setWidth(cdp, width, height = 780) {
  await cdp.send('Emulation.setDeviceMetricsOverride', {
    width, height, deviceScaleFactor: 1, mobile: width < 768,
    screenWidth: width, screenHeight: height,
  });
  await cdp.send('Emulation.setTouchEmulationEnabled', { enabled: width < 768, maxTouchPoints: 5 });
}

async function goto(cdp, url, theme) {
  await cdp.send('Page.navigate', { url });
  await new Promise((res) => {
    const t = setTimeout(res, 8000);
    cdp.on('Page.loadEventFired', () => { clearTimeout(t); res(); });
  });
  if (theme) {
    await cdp.eval(`(function(){try{localStorage.setItem('theme','${theme}');}catch(e){}
      document.documentElement.classList.toggle('dark', '${theme}'==='dark');
      document.documentElement.style.colorScheme='${theme}';})()`);
  }
  await cdp.eval('new Promise(r=>setTimeout(r,450))');   // CDN Tailwind + lucide
}

async function main() {
  const { proc, profile } = await launch();
  const cdp = await Cdp.attach();
  await cdp.send('Page.enable');
  await cdp.send('Runtime.enable');
  let failed = 0;

  try {
    if (has('shot')) {
      const route = arg('shot', '/');
      const out = arg('out', 'storage/app/responsive');
      const theme = arg('theme', 'light');
      const click = arg('click', null);
      const widths = (arg('widths') || '360,390,1280').split(',').map(Number);
      fs.mkdirSync(out, { recursive: true });
      for (const w of widths) {
        await setWidth(cdp, w, +(arg('height') || 900));
        await goto(cdp, BASE + route, theme);
        if (has('bottom')) {
          await cdp.eval('window.scrollTo(0, document.documentElement.scrollHeight)');
          await cdp.eval('new Promise(r=>setTimeout(r,350))');
        }
        if (click) {
          await cdp.eval(`document.querySelector(${JSON.stringify(click)}).click()`);
          await cdp.eval('new Promise(r=>setTimeout(r,300))');
        }
        const name = arg('name', route.replace(/[^a-z0-9]+/gi, '_') || 'home');
        const file = path.join(out, `${name}-${w}-${theme}${click ? '-open' : ''}.png`);
        const shot = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: has('full') });
        fs.writeFileSync(file, Buffer.from(shot.data, 'base64'));
        console.log('wrote ' + file);
      }
      return;
    }

    const widths = (arg('widths') || DEFAULT_WIDTHS.join(',')).split(',').map(Number);
    const routes = arg('routes') ? arg('routes').split(',') : publicRoutes();
    console.log(`${routes.length} routes x ${widths.length} widths (${widths.join(', ')})\n`);

    for (const w of widths) {
      await setWidth(cdp, w);
      for (const route of routes) {
        await goto(cdp, BASE + route);
        const r = await cdp.eval(PROBE(FONT_FLOOR, TAP_MIN));
        const problems = [];
        /* Overflow is a rule at every width. The type floor and the tap floor
           are phone rules — below `md` the reader is holding the thing in one
           hand; at 1280 the desktop artwork's 9px micro-lettering and 36px
           pointer controls govern. docs/RESPONSIVE-CONTRACT.md says so too. */
        const phone = w < 768;
        if (r.docWidth > w + 1) problems.push(`page scrolls sideways: ${r.docWidth}px wide in a ${w}px viewport`);
        r.overflow.slice(0, 5).forEach((o) => problems.push(`overflow  ${o.el} → right ${o.right}px`));
        if (phone) {
          r.tiny.slice(0, 5).forEach((t) => problems.push(`type ${t.size}px  ${t.el}  “${t.text}”`));
          r.taps.slice(0, 5).forEach((t) => problems.push(`tap ${t.w}x${t.h}  ${t.el}  “${t.text}”`));
        }
        if (problems.length) {
          failed++;
          console.log(`FAIL ${w}px ${route}`);
          problems.forEach((p) => console.log('       ' + p));
        } else {
          console.log(`ok   ${w}px ${route}`);
        }
      }
    }
    console.log(`\n${failed} failing page/width combinations.`);
  } finally {
    cdp.ws.close();
    proc.kill();
    try { fs.rmSync(profile, { recursive: true, force: true }); } catch (e) {}
  }
  process.exit(failed ? 1 : 0);
}

main().catch((e) => { console.error(e); process.exit(2); });
