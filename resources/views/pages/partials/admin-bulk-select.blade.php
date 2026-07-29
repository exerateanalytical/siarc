{{-- Shared vanilla-JS engine for admin list bulk-select + bulk-action toolbars.
     No framework, matching the rest of the admin panel (native <details> menus,
     inline onchange handlers). Included once from layouts/admin.blade.php.

     Usage per page:
       <table data-bulk-root="users">
         <thead><th><label class="ui-bulk-check-cell"><input type="checkbox" data-bulk-select-all></label></th>...
         <tbody><tr><td><label class="ui-bulk-check-cell"><input type="checkbox" data-bulk-row value="{{ $id }}"></label></td>...

       <div id="bulk-toolbar-users" class="hidden" data-bulk-toolbar="users">
         <span data-bulk-count="users">0</span> selected
         <button type="button" onclick="AdminBulk.get('users').submit('status','suspended','Suspend %n user(s)? ...')">Suspend</button>
       </div>

       <form id="bulk-form-users" data-bulk-form="users" method="POST" action="...">@csrf<input type="hidden" name="bulk_action"><input type="hidden" name="value"></form>

     AdminBulk.init('users', { cap: 200 }) wires it all up; AdminBulk.get('users').submit(action, value, confirmTemplate)
     builds ids[] on the form and submits, after a window.confirm() showing the
     exact action and row count (the "%n" placeholder is replaced with the
     selected count) — never a bare "are you sure?". --}}
<script>
window.AdminBulk = (function () {
    const instances = {};

    function boxesFor(key) {
        return Array.from(document.querySelectorAll('[data-bulk-root="' + key + '"] [data-bulk-row]'));
    }

    function init(key, opts) {
        opts = opts || {};
        const cap = opts.cap || 200;
        const header = document.querySelector('[data-bulk-root="' + key + '"] [data-bulk-select-all]');
        const toolbar = document.querySelector('[data-bulk-toolbar="' + key + '"]');
        const countEls = document.querySelectorAll('[data-bulk-count="' + key + '"]');
        const root = document.querySelector('[data-bulk-root="' + key + '"]');
        const form = document.querySelector('[data-bulk-form="' + key + '"]');
        if (!root || !form) return null;

        function selectable() { return boxesFor(key).filter(function (b) { return !b.disabled; }); }
        function checked() { return boxesFor(key).filter(function (b) { return b.checked; }); }

        function refresh() {
            const sel = checked();
            countEls.forEach(function (el) { el.textContent = sel.length; });
            if (toolbar) toolbar.classList.toggle('hidden', sel.length === 0);
            if (header) {
                const avail = selectable();
                header.checked = avail.length > 0 && sel.length === avail.length;
                header.indeterminate = sel.length > 0 && sel.length < avail.length;
            }
        }

        if (header) {
            header.addEventListener('change', function () {
                selectable().forEach(function (b) { b.checked = header.checked; });
                refresh();
            });
        }
        root.addEventListener('change', function (e) {
            if (e.target && e.target.matches('[data-bulk-row]')) refresh();
        });
        refresh();

        function submit(action, value, confirmTemplate) {
            const sel = checked();
            if (sel.length === 0) return;
            if (sel.length > cap) {
                window.alert((opts.capMessage) || ('Maximum ' + cap + ' rows per batch. Filter or paginate to process the rest.'));
                return;
            }
            const message = (confirmTemplate || 'Apply this action to %n row(s)?').replace('%n', sel.length);
            if (!window.confirm(message)) return;

            form.querySelector('[name="bulk_action"]').value = action;
            const valueInput = form.querySelector('[name="value"]');
            if (valueInput) valueInput.value = value == null ? '' : value;

            Array.from(form.querySelectorAll('input[name="ids[]"]')).forEach(function (i) { i.remove(); });
            sel.forEach(function (cb) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            form.submit();
        }

        instances[key] = { submit: submit, refresh: refresh };
        return instances[key];
    }

    function get(key) { return instances[key]; }

    return { init: init, get: get };
})();
</script>
