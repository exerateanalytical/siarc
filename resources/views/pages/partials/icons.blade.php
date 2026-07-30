{{--
    The icon library, loaded without blocking the first paint.

    It used to be a plain <script src> in the <head>, which the browser must
    fetch, parse and execute before it is allowed to render anything. 71 KB of
    that, on a phone, is time the visitor spends looking at a blank screen for
    no reason: not one icon is needed before the page has text.

    `defer` fixes that, but on its own it would break every icon on the site.
    Pages call `lucide.createIcons()` from a bare inline <script> at the end of
    the body, and inline scripts run during parsing — before any deferred file
    has executed. Those calls would hit an undefined `lucide` and throw.

    So: a stub goes first and records the calls, the real library replaces it
    and replays them (the replay lives in scripts/build-lucide-subset.cjs).
    Deferred scripts are guaranteed to run after the document is parsed and
    before DOMContentLoaded, so by replay time every element the queued call
    needs to find is in the DOM.

    The stub also answers `createElement`/`replaceElement` with a no-op rather
    than leaving them undefined, so a page that reaches for one early gets
    nothing done instead of a thrown error that stops the rest of its script.
--}}
<script>
    window.__lucideQueue = [];
    window.lucide = {
        createIcons: function (o) { window.__lucideQueue.push(o || {}); },
        createElement: function () { return null; },
        replaceElement: function () {},
        icons: {}
    };
</script>
<script defer src="{{ asset_v('vendor/lucide-subset.js') }}"></script>
