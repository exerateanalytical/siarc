{{-- ============================================================
     THE dark-mode foundation. One file. Included once per page by
     `pages/partials/ui-kit.blade.php`, which every page already includes.

     Where `darkMode: 'class'` lives now
     -----------------------------------
     It used to be merged here, at runtime, onto each page's inline
     `tailwind.config`, because the site compiled Tailwind in the browser from
     the 407 KB Play CDN bundle. That bundle is gone: `public/vendor/app.css` is
     built ahead of time by `tailwind.config.cjs`, which sets `darkMode: 'class'`
     once, so every `dark:` variant on the site is already in the stylesheet and
     there is nothing left to merge. The palette below, the light lock and the
     no-flash boot script are unchanged and are still the source of truth.

     Pages that used to declare a colour of their own now emit it as a
     `--c-*` custom property in the same place their config script sat; the
     stylesheet reads those, so a token meaning different shades on different
     pages still resolves per page. See `tailwind.config.cjs`.

     The palette itself is `docs/DARK-MODE-CONTRACT.md`. Tokens are CSS custom
     properties in `rgb r g b` channel form (so `bg-surface/60` works) and are
     re-pointed by the `.dark` class, which means `bg-surface` is already
     correct in both themes — `dark:` variants remain available and are what
     pages use for anything the tokens do not cover.

     Certificates: a view that sets `$lockLightTheme = true` before including
     the ui-kit gets a hard light lock — the class is stripped, the toggle is
     never mounted, and a `!important` reset kills any inherited dark rule.
     ============================================================ --}}
@php
    // A document (certificate, ticket) is printed. It renders light, always.
    // Views declare it; the route-name fallback catches anything that forgets.
    $themeLocked = ($lockLightTheme ?? false) || \App\Support\Theme::routeIsDocument();
@endphp
@once
<style>
    /* ── The palette. docs/DARK-MODE-CONTRACT.md is the source of truth. ──
       Channel triplets rather than hex so Tailwind's `/alpha` modifier works. */
    :root {
        --t-bg:            252 249 246;  /* #FCF9F6 */
        --t-surface:       252 250 246;  /* #FCFAF6 */
        --t-surface-2:     255 255 255;  /* #FFFFFF */
        --t-inset:         249 246 239;  /* #F9F6EF */
        --t-border:        231 226 216;  /* #E7E2D8 */
        --t-border-strong: 213 206 192;  /* #D5CEC0 */
        --t-ink:            26  26  23;  /* #1A1A17 */
        --t-ink-2:          87  87  78;  /* #57574E */
        --t-ink-3:         124 124 112;  /* #7C7C70 */
        --t-brand:          20 101  47;  /* #14652F */
        --t-brand-ink:     255 255 255;  /* #FFFFFF */
        --t-brand-deep:      2  65  29;  /* #02411D */
        --t-gold:          226 154   8;  /* #E29A08 */
        --t-gold-ink:      122  78   2;  /* #7A4E02 */
        --t-danger:        204   6  14;  /* #CC060E */
        --t-success-bg:    223 243 228;  /* #DFF3E4 */
        --t-success-ink:     0  55  18;  /* #003712 */
    }

    .dark {
        --t-bg:             10  12   9;  /* #0A0C09 */
        --t-surface:        18  21  15;  /* #12150F */
        --t-surface-2:      26  30  22;  /* #1A1E16 */
        --t-inset:           7   8   5;  /* #070805 */
        --t-border:         38  43  33;  /* #262B21 */
        --t-border-strong:  57  64  47;  /* #39402F */
        --t-ink:           243 239 231;  /* #F3EFE7 */
        --t-ink-2:         180 181 166;  /* #B4B5A6 */
        --t-ink-3:         134 135 120;  /* #868778 */
        --t-brand:          46 146  80;  /* #2E9250 */
        --t-brand-ink:       4  21  10;  /* #04150A */
        --t-brand-deep:     12  59  30;  /* #0C3B1E */
        --t-gold:          233 168  30;  /* #E9A81E */
        --t-gold-ink:      237 179  58;  /* #EDB33A */
        --t-danger:        240  85  92;  /* #F0555C */
        --t-success-bg:     12  61  29;  /* #0C3D1D */
        --t-success-ink:   139 220 166;  /* #8BDCA6 */
    }

    /* ── The toggle. Heritage green pill, gold knob, Poppins like everything else. ── */
    .theme-toggle {
        display: inline-flex; align-items: center; gap: 8px;
        height: 34px; padding: 0 6px 0 10px;
        border-radius: 999px;
        border: 1px solid rgb(var(--t-border-strong));
        background: rgb(var(--t-surface-2));
        color: rgb(var(--t-ink-2));
        font-family: inherit; font-size: 11.5px; font-weight: 600;
        line-height: 1; cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .theme-toggle:hover  { border-color: rgb(var(--t-brand)); color: rgb(var(--t-ink)); }
    .theme-toggle:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgb(var(--t-brand) / .28);
    }
    .theme-toggle__label { display: none; }
    @media (min-width: 640px) { .theme-toggle__label { display: inline; } }
    /* docs/RESPONSIVE-CONTRACT.md: 44×44 is the tap floor below `md`. The pill
       keeps its 34px look on a pointer device, where it was measured off the
       artwork, and grows to a thumb-sized target on a phone. */
    @media (max-width: 767px) {
        .theme-toggle { height: 44px; min-width: 44px; padding: 0 8px 0 12px; font-size: 14px; }
    }

    .theme-toggle__track {
        position: relative; flex: none;
        width: 38px; height: 20px; border-radius: 999px;
        background: rgb(var(--t-border));
        transition: background-color .18s ease;
    }
    .theme-toggle__knob {
        position: absolute; top: 2px; left: 2px;
        width: 16px; height: 16px; border-radius: 999px;
        background: rgb(var(--t-gold));
        display: grid; place-items: center;
        transition: transform .18s ease, background-color .18s ease;
    }
    .theme-toggle__knob svg { width: 10px; height: 10px; display: block; }
    .dark .theme-toggle__track { background: rgb(var(--t-brand-deep)); }
    .dark .theme-toggle__knob  { transform: translateX(18px); }

    .theme-toggle__sun, .theme-toggle__moon { transition: opacity .12s ease; }
    .theme-toggle__moon { display: none; }
    .dark .theme-toggle__sun  { display: none; }
    .dark .theme-toggle__moon { display: block; }

    /* Floating fallback: used only while the shared chrome has no slot for the
       control. It removes itself the moment a `[data-theme-toggle-slot]` or an
       inline `.theme-toggle` exists on the page. */
    .theme-toggle--floating {
        position: fixed; right: 14px; z-index: 40;
        bottom: calc(14px + env(safe-area-inset-bottom));
        box-shadow: 0 6px 20px rgb(0 0 0 / .16);
    }
    @media (max-width: 639px) {
        /* Clear of the mobile bottom bar. */
        .theme-toggle--floating { bottom: calc(74px + env(safe-area-inset-bottom)); }
    }

    /* Someone who asked for less motion gets none of it. */
    @media (prefers-reduced-motion: reduce) {
        .theme-toggle, .theme-toggle__track, .theme-toggle__knob,
        .theme-toggle__sun, .theme-toggle__moon { transition: none !important; }
        html.theme-switching, html.theme-switching * { transition: none !important; animation: none !important; }
    }
    /* A theme flip repaints the whole page; letting every hover transition on
       the page run at once looks like a smear. Suppressed for one frame. */
    html.theme-switching, html.theme-switching * {
        transition: none !important;
    }
</style>

@if ($themeLocked)
<style>
    /* ── Certificate / document lock ───────────────────────────────────────
       A certificate is a printed document: its colours are specified per type
       in config/certificate_types.php and it carries @@page A4 rules. It
       renders light regardless of the toggle. Omitting `dark:` classes is not
       enough — an inherited rule from a wrapper would leak in — so the class
       is stripped below AND any dark rule that survives is neutralised. */
    html.dark { color-scheme: light !important; }
</style>
<script>
(function () {
    var e = document.documentElement;
    e.classList.remove('dark');
    e.style.colorScheme = 'light';
    e.setAttribute('data-theme-locked', 'light');
    /* Anything that tries to re-add it later (a cached toggle handler, a
       bfcache restore) is undone immediately. */
    new MutationObserver(function () {
        if (e.classList.contains('dark')) e.classList.remove('dark');
    }).observe(e, { attributes: true, attributeFilter: ['class'] });
})();
</script>
@else
<script>
/* ── No-flash boot ──────────────────────────────────────────────────────
   Inline and blocking, in <head>, before any body markup exists. Not
   deferred, not Alpine, not DOMContentLoaded — the class has to be on <html>
   before the first paint or every navigation flashes white. */
(function () {
    var e = document.documentElement;
    var stored = null;
    try { stored = window.localStorage.getItem('theme'); } catch (err) {}
    var dark = stored === 'dark' || stored === 'light'
        ? stored === 'dark'
        : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

    e.classList.toggle('dark', !!dark);
    e.style.colorScheme = dark ? 'dark' : 'light';

    /* Timestamp so "no flash" is measurable rather than asserted: this must be
       smaller than the first-contentful-paint entry's startTime. */
    try { window.__themeBootAt = performance.now(); } catch (err) {}

    /* The public API the toggle (and anything else) drives. Defined here, in
       the blocking script, so a control rendered anywhere in the page can call
       it without waiting for a bundle. */
    window.ArtisanTheme = {
        get: function () { return e.classList.contains('dark') ? 'dark' : 'light'; },
        stored: function () { try { return window.localStorage.getItem('theme'); } catch (err) { return null; } },
        apply: function (theme, persist) {
            if (e.getAttribute('data-theme-locked')) return;
            var isDark = theme === 'dark';
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!reduce) {
                e.classList.add('theme-switching');
                window.setTimeout(function () { e.classList.remove('theme-switching'); }, 60);
            }
            e.classList.toggle('dark', isDark);
            e.style.colorScheme = isDark ? 'dark' : 'light';
            if (persist !== false) { try { window.localStorage.setItem('theme', theme); } catch (err) {} }
            document.querySelectorAll('.theme-toggle').forEach(function (b) {
                b.setAttribute('aria-checked', isDark ? 'true' : 'false');
            });
            window.dispatchEvent(new CustomEvent('themechange', { detail: { theme: theme } }));
        },
        toggle: function () { this.apply(this.get() === 'dark' ? 'light' : 'dark'); }
    };

    /* Follow the OS until the member has chosen. */
    if (window.matchMedia) {
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        var onOs = function (ev) {
            if (window.ArtisanTheme.stored()) return;
            window.ArtisanTheme.apply(ev.matches ? 'dark' : 'light', false);
        };
        if (mq.addEventListener) mq.addEventListener('change', onOs);
        else if (mq.addListener) mq.addListener(onOs);
    }

    /* Other tabs, and bfcache / JS-driven navigation restores. */
    window.addEventListener('storage', function (ev) {
        if (ev.key === 'theme' && ev.newValue) window.ArtisanTheme.apply(ev.newValue, false);
    });
    var resync = function () {
        var s = window.ArtisanTheme.stored();
        if (s) window.ArtisanTheme.apply(s, false);
    };
    window.addEventListener('pageshow', resync);
    window.addEventListener('popstate', resync);
    document.addEventListener('turbo:load', resync);
    document.addEventListener('htmx:afterSettle', resync);
})();
</script>

<script>
/* ── Toggle wiring + chrome fallback ────────────────────────────────────
   Any `.theme-toggle` in the page is wired by delegation, so a control
   rendered by a partial, injected later, or duplicated in a mobile menu all
   work without extra script.

   The three shells now carry the control themselves: `directory-header` (public
   chrome — utility row on desktop, mobile menu on phones), `layouts/dashboard`
   and `admin-heritage-header`. The floating fallback below therefore mounts only
   on a page that uses none of them, so dark mode is still reachable there. It
   stands down the moment the page contains a `.theme-toggle` or an element
   carrying `data-theme-toggle-slot`. */
(function () {
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest && ev.target.closest('.theme-toggle');
        if (!btn) return;
        ev.preventDefault();
        window.ArtisanTheme.toggle();
    });

    var mount = function () {
        if (document.documentElement.getAttribute('data-theme-locked')) return;
        if (document.querySelector('.theme-toggle')) return;             // chrome has one
        if (document.querySelector('[data-theme-toggle-slot]')) return;  // chrome will fill it
        var tpl = document.getElementById('theme-toggle-template');
        if (!tpl) return;
        var node = tpl.content.firstElementChild.cloneNode(true);
        node.classList.add('theme-toggle--floating');
        document.body.appendChild(node);
        node.setAttribute('aria-checked', window.ArtisanTheme.get() === 'dark' ? 'true' : 'false');
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mount);
    else mount();
})();
</script>

<template id="theme-toggle-template">@include('pages.partials.theme-toggle')</template>
@endif
@endonce
