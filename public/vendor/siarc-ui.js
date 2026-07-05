/* SIARC 2026 — shared UI behaviours (no dependencies).
   Data-attribute driven so Blade views only add attributes, never bespoke JS.

   TABS:    <div data-tabs="name"> <button class="si-tab is-active" data-tab="k">…</button> … </div>
            panels anywhere:  <div data-panel="k" data-tabs-for="name"> … </div>
            (if no panels exist the tab still toggles its active state)
            add data-tabs-filter="#scopeSelector" on the group to make tabs filter
            [data-filter-item]s by data-filter-tags (tab key 'all' shows everything)
   FILTER:  <input data-filter="#scopeSelector" …>   +  items: [data-filter-item] (matched on data-filter-text|textContent)
            optional empty state: [data-filter-empty] inside scope
   SELECT:  <select data-filter-select="#scopeSelector"> options value matched against item [data-filter-tags]
   SORT:    <select data-sort="#scopeSelector"> option value = 'az' | 'za' ; items sort on data-sort-key|textContent
   PAGE:    <div data-page="#scopeSelector" data-page-size="10">  with [data-page-prev] [data-page-next] [data-page-info]
   TOAST:   any element [data-toast="message"] shows a transient toast on click
*/
(function () {
  function ready(fn){ document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); }
  function scopeOf(el, attr){ var s = el.getAttribute(attr); return (s && document.querySelector(s)) || el.closest('[data-filter-scope]') || document; }
  function items(scope){ return Array.prototype.slice.call(scope.querySelectorAll('[data-filter-item]')); }

  ready(function () {
    /* ---- Tabs ---- */
    document.querySelectorAll('[data-tabs]').forEach(function (group) {
      var name = group.getAttribute('data-tabs');
      var triggers = Array.prototype.slice.call(group.querySelectorAll('[data-tab]'));
      var panels = Array.prototype.slice.call(document.querySelectorAll('[data-panel][data-tabs-for="' + name + '"]'));
      var fSel = group.getAttribute('data-tabs-filter');
      var fScope = fSel && document.querySelector(fSel);
      function activate(key) {
        triggers.forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('data-tab') === key); });
        panels.forEach(function (p) { p.hidden = (p.getAttribute('data-panel') !== key); });
        if (fScope) {
          var k = (key || '').toLowerCase();
          items(fScope).forEach(function (it) {
            var tags = (it.getAttribute('data-filter-tags') || '').toLowerCase().split(/[\s,]+/);
            var ok = !k || k === 'all' || tags.indexOf(k) >= 0;
            it.setAttribute('data-hidden', ok ? '0' : '1'); it.style.display = ok ? '' : 'none';
          });
          repage(fScope);
        }
      }
      triggers.forEach(function (t) {
        t.addEventListener('click', function (e) { e.preventDefault(); activate(t.getAttribute('data-tab')); });
      });
      var active = triggers.filter(function (t) { return t.classList.contains('is-active'); })[0] || triggers[0];
      if (active) activate(active.getAttribute('data-tab'));
    });

    /* ---- Client-side pagination (applied first so filters can re-page) ---- */
    var pagers = [];
    document.querySelectorAll('[data-page]').forEach(function (ctrl) {
      var scope = scopeOf(ctrl, 'data-page');
      var size = parseInt(ctrl.getAttribute('data-page-size') || '10', 10);
      var state = { page: 1 };
      function render() {
        var visible = items(scope).filter(function (it) { return it.getAttribute('data-hidden') !== '1'; });
        var pages = Math.max(1, Math.ceil(visible.length / size));
        state.page = Math.min(state.page, pages);
        visible.forEach(function (it, i) {
          var on = i >= (state.page - 1) * size && i < state.page * size;
          it.style.display = on ? '' : 'none';
        });
        var info = ctrl.querySelector('[data-page-info]');
        if (info) info.textContent = visible.length ? ('Affichage de ' + ((state.page - 1) * size + 1) + ' à ' + Math.min(state.page * size, visible.length) + ' sur ' + visible.length) : 'Aucun résultat';
      }
      var prev = ctrl.querySelector('[data-page-prev]'), next = ctrl.querySelector('[data-page-next]');
      if (prev) prev.addEventListener('click', function (e) { e.preventDefault(); if (state.page > 1) { state.page--; render(); } });
      if (next) next.addEventListener('click', function (e) { e.preventDefault(); state.page++; render(); });
      ctrl.querySelectorAll('[data-page-num]').forEach(function (b) {
        b.addEventListener('click', function (e) { e.preventDefault(); state.page = parseInt(b.getAttribute('data-page-num'), 10) || 1; render(); });
      });
      pagers.push({ scope: scope, reset: function () { state.page = 1; render(); } });
      render();
    });
    function repage(scope) { pagers.forEach(function (p) { if (p.scope === scope || scope === document) p.reset(); }); }

    /* ---- Live text filter ---- */
    document.querySelectorAll('[data-filter]').forEach(function (input) {
      var scope = scopeOf(input, 'data-filter');
      var empty = scope.querySelector('[data-filter-empty]');
      function apply() {
        var q = (input.value || '').toLowerCase().trim(), shown = 0;
        items(scope).forEach(function (it) {
          var hay = (it.getAttribute('data-filter-text') || it.textContent || '').toLowerCase();
          var ok = !q || hay.indexOf(q) >= 0;
          it.setAttribute('data-hidden', ok ? '0' : '1'); it.style.display = ok ? '' : 'none';
          if (ok) shown++;
        });
        if (empty) empty.style.display = shown ? 'none' : '';
        repage(scope);
      }
      input.addEventListener('input', apply);
    });

    /* ---- Select filters (category / status / pays) ---- */
    document.querySelectorAll('[data-filter-select]').forEach(function (sel) {
      var scope = scopeOf(sel, 'data-filter-select');
      sel.addEventListener('change', function () {
        var v = (sel.value || '').toLowerCase().trim();
        items(scope).forEach(function (it) {
          var tags = (it.getAttribute('data-filter-tags') || '').toLowerCase();
          var ok = !v || v === 'tous' || v === 'toutes' || v === 'all' || tags.indexOf(v) >= 0;
          it.setAttribute('data-hidden', ok ? '0' : '1'); it.style.display = ok ? '' : 'none';
        });
        repage(scope);
      });
    });

    /* ---- Sort ---- */
    document.querySelectorAll('[data-sort]').forEach(function (sel) {
      var scope = scopeOf(sel, 'data-sort');
      sel.addEventListener('change', function () {
        var dir = sel.value === 'za' ? -1 : 1;
        var list = items(scope);
        var parent = list.length ? list[0].parentNode : null;
        if (!parent) return;
        list.sort(function (a, b) {
          var ka = (a.getAttribute('data-sort-key') || a.textContent || '').toLowerCase();
          var kb = (b.getAttribute('data-sort-key') || b.textContent || '').toLowerCase();
          return ka < kb ? -dir : ka > kb ? dir : 0;
        }).forEach(function (it) { parent.appendChild(it); });
        repage(scope);
      });
    });

    /* ---- Toast (graceful action feedback) ---- */
    document.querySelectorAll('[data-toast]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        if (el.tagName === 'A' && el.getAttribute('href') && el.getAttribute('href') !== '#') return;
        e.preventDefault();
        var t = document.createElement('div');
        t.textContent = el.getAttribute('data-toast');
        t.style.cssText = 'position:fixed;left:50%;bottom:28px;transform:translateX(-50%);background:#0B3A1E;color:#fff;padding:12px 20px;border-radius:12px;font:600 13px Poppins,system-ui,sans-serif;box-shadow:0 12px 32px rgba(6,43,21,.35);z-index:9999;opacity:0;transition:opacity .2s,transform .2s;';
        document.body.appendChild(t);
        requestAnimationFrame(function () { t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(-4px)'; });
        setTimeout(function () { t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 250); }, 2200);
      });
    });
  });
})();
