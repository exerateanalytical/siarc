/* ============================================================================
   The build config for `public/vendor/app.css` — the ONE stylesheet the whole
   site loads.

   What this replaced
   ------------------
   Every page used to load `public/vendor/tailwindcss.js` (407 KB): the Tailwind
   *Play CDN*, a CSS compiler that ran in the visitor's browser and generated
   the stylesheet at runtime, on every page load, on every phone. It is
   explicitly not a production tool. Brotli made it cheap to *download* and did
   nothing about the parse-compile-execute cost, which is what actually held up
   the first paint on a mid-range Android.

   The token problem this config solves
   ------------------------------------
   47 views carried their own inline `tailwind.config`, and the same token name
   meant different colours on different pages — `cream` was three shades,
   `gold` three, `leaf` two, `goldbt` four, and the `brand`/`forest` 50–900
   scales were two different ramps. A single static stylesheet can define
   `bg-cream` exactly once, so building naively would have silently rendered
   the wrong shade on most of the site.

   Rather than rewrite ~239 utility usages to arbitrary values — which cannot
   work for the shared partials (`directory-header`, `components/business-card`,
   `sector-browser`, `auth/partials/replica-bottom`) that are included from
   pages with *different* values for the same token, and so have no single
   correct answer — every colliding token is compiled to a CSS custom property:

       leaf: rgb(var(--c-leaf, 22 76 40) / <alpha-value>)

   Each page then declares only its own values in a tiny `<style>:root{…}</style>
   block, in the exact place its `tailwind.config` script used to sit. Resolution
   is therefore by the same page-scoped cascade as before — a partial included
   from two pages still renders each page's shade — and nothing can resolve from
   the wrong file's config. The `var()` fallback is the dominant value, so a
   token used on a page that never declared it degrades to a sane colour instead
   of an invalid declaration.

   Tokens whose value was identical in every config are plain literals below.

   `--f-serif` is the same mechanism: 24 configs set Playfair, 23 did not, and
   `font-serif` is used in partials rendered under both. The fallback is
   Tailwind's stock serif stack, which is what those pages resolve to today.

   Dark mode
   ---------
   `darkMode: 'class'` bakes every `dark:` variant into this file. That replaces
   the runtime config merge that `pages/partials/theme.blade.php` used to do
   against the CDN; the `--t-*` palette and the no-flash boot script in that
   partial are unchanged and still the source of truth. docs/DARK-MODE-CONTRACT.md.

   Rebuild with `npm run build:assets` and commit `public/vendor/app.css` —
   the production host has no Node.
   ========================================================================== */

/** A colliding token: page-declared custom property, dominant value as fallback. */
const v = (name, fallback) => `rgb(var(--c-${name}, ${fallback}) / <alpha-value>)`;
/** A dark-mode contract token from docs/DARK-MODE-CONTRACT.md. */
const t = (name) => `rgb(var(--t-${name}) / <alpha-value>)`;

/** `#f0f9f4` → `240 249 244`. The fallback sits inside `rgb(… / <alpha-value>)`,
    so it has to be channels; a hex there is an invalid declaration. */
const ch = (hex) => {
    const h = hex.replace('#', '');
    const f = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
    return [0, 2, 4].map((i) => parseInt(f.slice(i, i + 2), 16)).join(' ');
};

/** The 50–900 ramps, which collide between layouts/dashboard and the others. */
const ramp = (name, fallbacks) =>
    Object.fromEntries(Object.entries(fallbacks).map(([k, hex]) => [k, v(`${name}-${k}`, ch(hex))]));

module.exports = {
    darkMode: 'class',

    /* The JIT scans these for class names. No class on this site is built by
       string concatenation (verified), so literal scanning is complete —
       `text-[13px]`, `md:text-[10px]` and `dark:bg-[#12150F]` are all picked up
       from the markup exactly as written. */
    content: [
        './resources/views/**/*.blade.php',
        './resources/views/**/*.php',
        './app/**/*.php',
        './config/**/*.php',
        './routes/**/*.php',
        './public/vendor/siarc-ui.js',
    ],

    theme: {
        extend: {
            colors: {
                /* ── Colliding tokens — resolved per page via --c-* ───────── */
                cream:  v('cream',  '248 243 237'),   /* #F8F3ED · also #F7F2EC, #FAF7F2 */
                parch:  v('parch',  '253 251 247'),   /* #FDFBF7 · also #FBF9F3 */
                leaf:   v('leaf',   '22 76 40'),      /* #164C28 · also #14652F */
                gold:   v('gold',   'var(--t-gold)'), /* pages that declare none inherit the contract gold */
                goldbt: v('goldbt', '233 168 48'),    /* #E9A830 · also #E0A52F, #F0B93E, #E9AC33 */
                night:  v('night',  '19 17 16'),      /* #131110 · also #0D0F0D */

                brand: {
                    /* DEFAULT is the dark-mode contract brand, exactly as the old
                       runtime merge injected it onto every page's `brand` scale. */
                    DEFAULT: t('brand'),
                    ...ramp('brand', {
                        50: '#fef9ee', 100: '#fdf0d3', 200: '#fada9a', 300: '#f7c062',
                        400: '#f4a32a', 500: '#e8880e', 600: '#cc6a09', 700: '#a84e0b',
                        800: '#873d10', 900: '#6e3311',
                    }),
                },
                forest: {
                    ...ramp('forest', {
                        50: '#f0f9f4', 100: '#dbf0e3', 200: '#b8e0c9',
                        400: '#5ba883', 500: '#2d6a4f', 600: '#1b4332',
                        700: '#0d2b1e', 800: '#082018', 900: '#03130e',
                    }),
                    300: '#8cc9a8',   /* identical in every config */
                },

                /* ── Tokens with one value everywhere ─────────────────────── */
                sand:      '#E7E1D4',
                pine:      '#0E1D13',
                pinefc:    '#0A2415',
                pineur:    '#0E261C',
                inkgr:     '#0F2D19',
                deep:      '#0A331C',
                deepfc:    '#02301B',
                deepgr:    '#0A1F10',
                herogr:    '#03341B',
                certgr:    '#012B19',
                panel:     '#091C10',
                muted:     '#8A857A',
                sage:      '#A8B8AC',
                cocoa:     '#4A2E1E',
                goldlt:    '#D9A439',
                golddk:    '#B0821A',
                goldic:    '#D79326',
                dashgold:  '#E5A82E',
                flagg:     '#125527',
                flagr:     '#C10913',
                flagy:     '#EBAB1A',
                sidegreen: '#02301B',
                sideband:  '#0B3D28',
                siderow:   '#14532D',
                qdside:    '#02301B',
                qdact:     '#14532D',
                qddeep:    '#0B3D28',
                obside:    '#012915',
                obact:     '#01602D',
                obdeep:    '#0A2E1C',

                /* ── The dark-mode contract. docs/DARK-MODE-CONTRACT.md ───── */
                'bg':             t('bg'),
                'surface':        t('surface'),
                'surface-2':      t('surface-2'),
                'inset':          t('inset'),
                'border':         t('border'),
                'border-strong':  t('border-strong'),
                'ink':            t('ink'),
                'ink-2':          t('ink-2'),
                'ink-3':          t('ink-3'),
                'brand-ink':      t('brand-ink'),
                'brand-deep':     t('brand-deep'),
                'gold-ink':       t('gold-ink'),
                'danger':         t('danger'),
                'success-bg':     t('success-bg'),
                'success-ink':    t('success-ink'),
            },

            fontFamily: {
                sans: ['Poppins', 'system-ui', 'sans-serif'],
                /* Playfair on the 24 pages that declared it, Tailwind's stock
                   serif on the 23 that did not — decided per page by --f-serif. */
                serif: ['var(--f-serif, ui-serif, Georgia, Cambria, "Times New Roman", Times, serif)'],
            },
        },
    },

    plugins: [],
};
