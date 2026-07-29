#!/usr/bin/env node
/* Batch screenshot driver for the static-asset migration: same routes, widths
   and themes before and after, into two directories, so every pair can be
   diffed numerically instead of eyeballed selectively.
   Reuses scripts/responsive-audit.cjs's Chrome/CDP plumbing conventions.

   node scripts/shots.cjs <outdir> [--widths 360,1280] [--full] */
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

const BASE = process.env.RESPONSIVE_BASE || 'http://artisanatcameroun.test';
const PORT = 9300 + Math.floor(Math.random() * 600);
const OUT = process.argv[2] || 'storage/app/shots';
const argv = process.argv.slice(2);
const arg = (n, d) => { const i = argv.indexOf('--' + n); return i === -1 ? d : argv[i + 1]; };
const WIDTHS = (arg('widths', '360,1280')).split(',').map(Number);
const FULL = argv.includes('--full');

/* `--cookie name=value` shoots pages behind the session gate (the admin console,
   an unpublished artisan profile in admin preview). `--routes name=/path,…`
   overrides the list below. */
const COOKIE = arg('cookie', null);
const ROUTES = arg('routes', null)
  ? arg('routes').split(',').map((p) => p.split('='))
  : [
  ['home', '/'],
  ['directory', '/galerie/entreprises'],
  ['products', '/galerie/produits'],
  ['login', '/login'],
  ['offline', '/hors-ligne'],
  ['certificate', '/certificat-artisan/daouda-garga'],
  ['contact', '/contact'],
  ['security-artwork', '/apercu-securite'],
  ['events', '/evenements'],
  ['search', '/galerie/recherche'],
  ['collections', '/collections-heritage'],
  ['faq', '/faq'],
];


function chromePath() {
  const c = [process.env.CHROME_PATH, 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe', '/usr/bin/google-chrome'].filter(Boolean);
  for (const p of c) if (fs.existsSync(p)) return p;
  throw new Error('Chrome not found. Set CHROME_PATH.');
}

class Cdp {
  constructor(ws) { this.ws = ws; this.id = 0; this.pending = new Map(); this.handlers = {};
    ws.onmessage = (e) => { const m = JSON.parse(e.data);
      if (m.id && this.pending.has(m.id)) { const { res, rej } = this.pending.get(m.id); this.pending.delete(m.id);
        m.error ? rej(new Error(m.error.message)) : res(m.result); }
      else if (m.method && this.handlers[m.method]) this.handlers[m.method].forEach((h) => h(m.params)); }; }
  on(ev, fn) { (this.handlers[ev] = this.handlers[ev] || []).push(fn); }
  send(method, params = {}) { const id = ++this.id;
    return new Promise((res, rej) => { this.pending.set(id, { res, rej }); this.ws.send(JSON.stringify({ id, method, params })); }); }
  async eval(expr) { const r = await this.send('Runtime.evaluate', { expression: expr, awaitPromise: true, returnByValue: true });
    return r.result && r.result.value; }
  static async attach() {
    for (let i = 0; i < 60; i++) {
      try { const list = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
        const page = list.find((t) => t.type === 'page');
        if (page) { const ws = new WebSocket(page.webSocketDebuggerUrl);
          await new Promise((r, j) => { ws.onopen = r; ws.onerror = j; }); return new Cdp(ws); } } catch (e) {}
      await new Promise((r) => setTimeout(r, 250));
    } throw new Error('cannot attach to Chrome');
  }
}

(async () => {
  const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'shots-'));
  const proc = spawn(chromePath(), [`--remote-debugging-port=${PORT}`, `--user-data-dir=${profile}`,
    '--headless=new', '--no-first-run', '--no-default-browser-check', '--disable-gpu',
    '--hide-scrollbars', '--force-device-scale-factor=1', 'about:blank'], { stdio: 'ignore' });
  const cdp = await Cdp.attach();
  await cdp.send('Page.enable'); await cdp.send('Runtime.enable');
  fs.mkdirSync(OUT, { recursive: true });

  if (COOKIE) {
    const eq = COOKIE.indexOf('=');
    await cdp.send('Network.enable');
    await cdp.send('Network.setCookie', {
      name: COOKIE.slice(0, eq), value: COOKIE.slice(eq + 1),
      domain: new URL(BASE).hostname, path: '/',
    });
  }

  try {
    for (const w of WIDTHS) {
      await cdp.send('Emulation.setDeviceMetricsOverride', { width: w, height: 900, deviceScaleFactor: 1,
        mobile: w < 768, screenWidth: w, screenHeight: 900 });
      await cdp.send('Emulation.setTouchEmulationEnabled', { enabled: w < 768, maxTouchPoints: 5 });
      for (const theme of ['light', 'dark']) {
        for (const [name, route] of ROUTES) {
          await cdp.send('Page.navigate', { url: BASE + '/nope-warmup' }).catch(() => {});
          await cdp.eval(`(function(){try{localStorage.setItem('theme','${theme}');}catch(e){}})()`).catch(() => {});
          await cdp.send('Page.navigate', { url: BASE + route });
          await new Promise((res) => { const t = setTimeout(res, 10000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); res(); }); });
          await cdp.eval(`(function(){document.documentElement.classList.toggle('dark','${theme}'==='dark');
            document.documentElement.style.colorScheme='${theme}';})()`);
          await cdp.eval(`new Promise(r=>setTimeout(r,${+(process.env.SETTLE_MS || 700)}))`);
          const shot = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: FULL });
          const file = path.join(OUT, `${name}-${w}-${theme}.png`);
          fs.writeFileSync(file, Buffer.from(shot.data, 'base64'));
          console.log('wrote', file);
        }
      }
    }
  } finally { proc.kill(); }
  process.exit(0);
})();
