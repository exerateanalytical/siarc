{{-- ============================================================
     THE platform UI kit.

     One definition for every form field, card, label and button, taken from
     the admin dashboard (/tableau-de-bord/admin), which is the reference
     design. Before this existed the platform had 19 different field border
     colours and 9 different field heights across 446 inputs, because every
     page was built as a separate pixel replica.

     Plain CSS rather than Tailwind utilities on purpose: Tailwind is loaded
     from a CDN bundle at runtime here, so `@apply` is unavailable, and a
     semantic class is the only thing that keeps 74 files from drifting again.

     Include once per page — @once makes that safe from partials.

     Usage:
       <label class="ui-label">Nom</label>
       <input class="ui-field">                     text / email / tel / number / date
       <select class="ui-field ui-select">
       <textarea class="ui-field ui-textarea">
       <div class="ui-field-group"><i …></i><input class="ui-field-bare"></div>
       <p class="ui-hint">…</p>  <p class="ui-error">…</p>

       <section class="ui-card">…</section>
       <h2 class="ui-card-title">…</h2>

       <button class="ui-btn ui-btn-primary">   filled green
       <button class="ui-btn ui-btn-secondary"> white, bordered
       <button class="ui-btn ui-btn-danger">    destructive
       <button class="ui-btn ui-btn-ghost">     borderless
       add ui-btn-sm / ui-btn-lg to resize

     Modifiers: ui-field--sm (32px), ui-field--lg (44px), ui-field--invalid
     ============================================================ --}}
@once
<style>
    :root {
        /* Surfaces */
        --ui-page:        #F8F4EC;   /* admin body */
        --ui-surface:     #FFFFFF;
        --ui-surface-alt: #F8F4EC;   /* table headers, inset panels */

        /* Lines. Cards use the lighter one, fields the darker, so a field
           reads as interactive against the card it sits on. */
        --ui-border-card:  #EFEBE2;
        --ui-border-field: #EAE5D8;
        --ui-border-soft:  #F5F1E8;  /* row dividers */

        /* Text */
        --ui-ink:    #1B1B18;
        --ui-body:   #3B382F;
        --ui-muted:  #8A857A;
        --ui-faint:  #B8B2A4;
        --ui-label:  #8A6D1F;        /* gold, used for table headers + eyebrows */

        /* Accent */
        --ui-green:       #157A43;
        --ui-green-deep:  #14652F;
        --ui-green-dark:  #0F4824;
        --ui-green-tint:  #E2F3E8;
        --ui-gold:        #C9942E;
        --ui-gold-tint:   #FBF1DD;
        --ui-danger:      #B42025;
        --ui-danger-tint: #FDE8E8;

        --ui-radius:       10px;      /* fields, buttons */
        --ui-radius-card:  16px;
        --ui-h:            38px;      /* the field height */
    }

    /* ── Dark mode ──────────────────────────────────────────────
       The kit is plain CSS driven by variables, so the whole platform's
       fields, cards, tables, pills and buttons switch by re-declaring the
       variables once. Nothing below this block needs a dark rule of its own,
       and a page that hand-rolls a dark card is drift.

       Values are the tokens in docs/DARK-MODE-CONTRACT.md. Two of them were
       added by this pass because the table lacked a pair that passes:

         --ui-green (#339B56)       green *text*. The table's brand #2E9250 is
                                    4.31:1 on surface-2 — under AA for body
                                    text — so the fill stays #2E9250 and the
                                    label lightens: 4.80:1 on #1A1E16.
         --ui-border-field (#68715B) the boundary of an actual control. WCAG
                                    1.4.11 wants 3:1 for that; the table's
                                    border-strong #39402F is 1.57:1 on the
                                    input fill. Card hairlines keep #262B21 —
                                    they are decorative, and the card is also
                                    told apart by its fill. */
    html.dark {
        color-scheme: dark;

        --ui-page:        #0A0C09;
        --ui-surface:     #12150F;
        --ui-surface-alt: #1A1E16;

        --ui-border-card:  #262B21;
        --ui-border-field: #68715B;
        --ui-border-soft:  #21261C;

        --ui-ink:    #F3EFE7;
        --ui-body:   #B4B5A6;
        --ui-muted:  #868778;
        --ui-faint:  #868778;   /* placeholders: 4.63:1 on the input fill */
        --ui-label:  #EDB33A;

        --ui-green:       #339B56;
        --ui-green-deep:  #3CA862;
        --ui-green-dark:  #2E9250;
        --ui-green-tint:  #0C3D1D;
        --ui-gold:        #E9A81E;
        --ui-gold-tint:   #3A2B06;
        --ui-danger:      #F0555C;
        --ui-danger-tint: #3A1013;
    }

    /* The handful of rules below hardcode a colour rather than read a
       variable, so each needs a dark counterpart. */
    html.dark .ui-field:hover:not(:disabled):not(:focus) { border-color: #7C866E; }
    html.dark .ui-field:focus,
    html.dark .ui-field-group:focus-within { box-shadow: 0 0 0 3px rgba(51, 155, 86, 0.25); }
    html.dark .ui-field--invalid:focus,
    html.dark .ui-field[aria-invalid="true"]:focus { box-shadow: 0 0 0 3px rgba(240, 85, 92, 0.25); }
    html.dark .ui-field:disabled,
    html.dark .ui-field[readonly] { background-color: #1F241B; color: var(--ui-muted); }
    html.dark .ui-select {
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%23B4B5A6' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5.5 7.5 10 12l4.5-4.5'/%3E%3C/svg%3E");
    }
    html.dark .ui-dropzone { background-color: #161A13; }
    html.dark .ui-file::file-selector-button:hover,
    html.dark .ui-dropzone:hover { background-color: #17301F; border-color: var(--ui-green); }
    html.dark .ui-btn:focus-visible { outline-color: var(--ui-green); }
    /* White on the dark brand fill is 3.93:1 — under AA. The contract's
       brand-ink is near-black for exactly this reason. */
    html.dark .ui-btn-primary { color: #04150A; }
    html.dark .ui-btn-secondary { border-color: var(--ui-border-field); }
    html.dark .ui-btn-danger { border-color: #5C2126; }
    /* #339B56 is only 3.51:1 on the success well; success-ink is 7.58:1. */
    html.dark .ui-pill-ok      { color: #8BDCA6; }
    html.dark .ui-pill-warn    { color: #EDB33A; }
    html.dark .ui-pill-neutral { background: #1F241B; color: var(--ui-body); }
    html.dark .ui-alert-ok     { border-color: #1B5E33; color: #8BDCA6; }
    html.dark .ui-alert-warn   { border-color: #6A5210; color: #EDB33A; }
    html.dark .ui-alert-danger { border-color: #7A2A2E; color: var(--ui-danger); }

    /* ── Labels & helper text ───────────────────────────────── */
    .ui-label {
        display: block;
        margin-bottom: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--ui-ink);
        line-height: 1.3;
    }
    .ui-label .ui-req { color: var(--ui-danger); margin-left: 2px; }

    /* Small gold eyebrow, as used above admin table sections */
    .ui-eyebrow {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--ui-label);
    }

    .ui-hint  { margin-top: 5px; font-size: 11.5px; color: var(--ui-muted); line-height: 1.45; }
    .ui-error { margin-top: 5px; font-size: 11.5px; color: var(--ui-danger); line-height: 1.45; }

    /* ── The field ──────────────────────────────────────────── */
    .ui-field {
        display: block;
        width: 100%;
        height: var(--ui-h);
        padding: 0 12px;
        font-family: inherit;
        font-size: 12.5px;
        line-height: 1.2;
        color: var(--ui-ink);
        background-color: var(--ui-surface);
        border: 1px solid var(--ui-border-field);
        border-radius: var(--ui-radius);
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
        -webkit-appearance: none;
        appearance: none;
    }
    .ui-field::placeholder { color: var(--ui-faint); }
    .ui-field:hover:not(:disabled):not(:focus) { border-color: #DCD5C6; }
    .ui-field:focus {
        border-color: var(--ui-green);
        box-shadow: 0 0 0 3px rgba(21, 122, 67, 0.12);
    }
    .ui-field:disabled,
    .ui-field[readonly] {
        background-color: #F7F6F3;
        color: var(--ui-muted);
        cursor: not-allowed;
    }
    .ui-field[readonly] { cursor: default; }

    .ui-field--invalid,
    .ui-field[aria-invalid="true"] { border-color: var(--ui-danger); }
    .ui-field--invalid:focus,
    .ui-field[aria-invalid="true"]:focus { box-shadow: 0 0 0 3px rgba(180, 32, 37, 0.12); }

    /* For fields sitting on a dark panel — the footer newsletter box, say.
       Same geometry and the same mobile type size; only the colours invert.
       Without this, such a field gets hand-rolled and quietly loses the 16px
       rule below, which is what makes iOS zoom the page on focus. */
    .ui-field--invert {
        background: transparent;
        border-color: rgba(255, 255, 255, 0.28);
        color: #FFFFFF;
    }
    .ui-field--invert::placeholder { color: rgba(255, 255, 255, 0.55); }
    .ui-field--invert:focus {
        border-color: var(--ui-gold);
        box-shadow: 0 0 0 3px rgba(229, 168, 46, 0.18);
    }

    /* Select: same box, with a chevron drawn in so no extra markup is needed */
    .ui-select {
        padding-right: 32px;
        cursor: pointer;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%238A857A' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5.5 7.5 10 12l4.5-4.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px 16px;
    }
    .ui-select[multiple] { height: auto; padding: 8px 12px; background-image: none; }

    .ui-textarea {
        height: auto;
        min-height: 96px;
        padding: 10px 12px;
        line-height: 1.55;
        resize: vertical;
    }

    /* A field with an icon or prefix beside the input */
    .ui-field-group {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        height: var(--ui-h);
        padding: 0 12px;
        background-color: var(--ui-surface);
        border: 1px solid var(--ui-border-field);
        border-radius: var(--ui-radius);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ui-field-group:focus-within {
        border-color: var(--ui-green);
        box-shadow: 0 0 0 3px rgba(21, 122, 67, 0.12);
    }
    .ui-field-group > svg,
    .ui-field-group > i { flex: none; color: var(--ui-muted); }
    .ui-field-bare {
        flex: 1 1 auto;
        min-width: 0;
        height: 100%;
        border: 0;
        outline: none;
        background: transparent;
        font-family: inherit;
        font-size: 12.5px;
        color: var(--ui-ink);
    }
    .ui-field-bare::placeholder { color: var(--ui-faint); }

    /* Size modifiers live AFTER .ui-field-group so they win on both — a group
       carrying ui-field--lg must actually be 44px, not silently stay at 38 and
       sit 6px shorter than the plain fields beside it. */
    .ui-field--sm { height: 32px; padding: 0 10px; font-size: 12px; }
    .ui-field--lg { height: 44px; padding: 0 14px; font-size: 13.5px; }
    .ui-field-group.ui-field--sm,
    .ui-field-group.ui-field--lg { padding-left: 12px; padding-right: 12px; }
    .ui-field--sm .ui-field-bare { font-size: 12px; }
    .ui-field--lg .ui-field-bare { font-size: 13.5px; }
    /* A textarea sizes to its content, so a height modifier must not cap it. */
    .ui-textarea.ui-field--sm,
    .ui-textarea.ui-field--lg { height: auto; }

    /* A readable floor for body text on phones.

       The design was drawn for desktop and uses 10px–11.5px for breadcrumbs,
       card metadata, badges and captions — around 110 places across the public
       pages. That is roughly half a comfortable mobile reading size, and on a
       phone in daylight it is genuinely hard to read.

       Raising it here rather than editing every occurrence keeps the desktop
       density exactly as designed, and means a new 10px label inherits the fix
       instead of reintroducing the problem. Headings, prices and anything
       already at 12px or above are untouched, so nothing reflows.

       Scoped to width alone, not (pointer: coarse): a desktop with a touch
       screen should keep its designed density. */
    @media (max-width: 767.98px) {
        .text-\[10px\], .text-\[10\.5px\] { font-size: 12px !important; }
        .text-\[11px\], .text-\[11\.5px\] { font-size: 12.5px !important; }
    }

    /* iOS Safari zooms the page whenever a focused control's text is under 16px.
       The platform's density is deliberately compact on desktop, so raise the
       type only on touch-primary screens — the field grows a little, nothing
       jumps, and the page stops zooming on every tap. */
    @media (max-width: 767.98px), (pointer: coarse) {
        .ui-field,
        .ui-field-bare,
        .ui-field--sm,
        .ui-field--lg,
        .ui-field--sm .ui-field-bare,
        .ui-field--lg .ui-field-bare { font-size: 16px; }
        .ui-field:not(.ui-textarea) { height: 44px; }
        .ui-field-group { height: 44px; }
        .ui-field--sm:not(.ui-textarea),
        .ui-field-group.ui-field--sm { height: 40px; }
    }

    /* File input. Can't be a .ui-field — the control is the browser's own
       button plus a filename, so the box is styled and the button inside it. */
    .ui-file {
        display: block;
        width: 100%;
        font-family: inherit;
        font-size: 12px;
        color: var(--ui-muted);
    }
    .ui-file::file-selector-button {
        margin-right: 12px;
        padding: 8px 14px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        color: var(--ui-green);
        background-color: var(--ui-surface);
        border: 1px solid var(--ui-border-field);
        border-radius: var(--ui-radius);
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
    }
    .ui-file::file-selector-button:hover {
        border-color: var(--ui-green);
        background-color: var(--ui-green-tint);
    }
    .ui-file:focus { outline: none; }
    .ui-file:focus::file-selector-button {
        border-color: var(--ui-green);
        box-shadow: 0 0 0 3px rgba(21, 122, 67, 0.12);
    }

    /* Dropzone-style file field, for the larger upload areas */
    .ui-dropzone {
        display: block;
        padding: 18px;
        text-align: center;
        background-color: #FCFBF8;
        border: 1px dashed var(--ui-border-field);
        border-radius: var(--ui-radius);
        transition: border-color .15s ease, background-color .15s ease;
    }
    .ui-dropzone:hover { border-color: var(--ui-green); background-color: var(--ui-green-tint); }

    /* Checkbox / radio */
    .ui-check {
        width: 16px; height: 16px;
        accent-color: var(--ui-green-deep);
        border-radius: 4px;
        cursor: pointer;
        flex: none;
    }
    .ui-check-row {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 12.5px; color: var(--ui-body); line-height: 1.45;
    }

    /* Field layout helpers */
    .ui-form-grid { display: grid; grid-template-columns: 1fr; gap: 18px 20px; }
    @media (min-width: 640px) { .ui-form-grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .ui-form-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .ui-form-grid > .ui-span-all { grid-column: 1 / -1; }

    /* ── Cards ──────────────────────────────────────────────── */
    .ui-card {
        background-color: var(--ui-surface);
        border: 1px solid var(--ui-border-card);
        border-radius: var(--ui-radius-card);
        padding: 20px;
    }
    .ui-card--flush { padding: 0; overflow: hidden; }
    .ui-card-title {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ui-ink);
        line-height: 1.35;
    }
    .ui-card-sub { margin-top: 4px; font-size: 12px; color: var(--ui-muted); line-height: 1.45; }
    .ui-card-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .ui-divider { height: 1px; background: var(--ui-border-soft); border: 0; margin: 18px 0; }

    /* Definition rows on detail pages */
    .ui-dl { display: grid; grid-template-columns: 1fr; gap: 14px 24px; }
    @media (min-width: 640px) { .ui-dl--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .ui-dt { font-size: 11.5px; color: var(--ui-muted); }
    .ui-dd { margin-top: 3px; font-size: 13px; font-weight: 600; color: var(--ui-ink); word-break: break-word; }

    /* ── Buttons ────────────────────────────────────────────── */
    .ui-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: var(--ui-h);
        padding: 0 16px;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        border: 1px solid transparent;
        border-radius: var(--ui-radius);
        cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        text-decoration: none;
    }
    .ui-btn:focus-visible { outline: 2px solid var(--ui-green); outline-offset: 2px; }
    .ui-btn:disabled, .ui-btn[aria-disabled="true"] { opacity: .55; cursor: not-allowed; }
    .ui-btn-sm { height: 32px; padding: 0 12px; font-size: 12px; }
    .ui-btn-lg { height: 44px; padding: 0 20px; font-size: 13.5px; }
    .ui-btn-block { width: 100%; }

    .ui-btn-primary   { background-color: var(--ui-green-dark); color: #fff; }
    .ui-btn-primary:hover:not(:disabled)   { background-color: var(--ui-green-deep); }

    .ui-btn-secondary { background-color: var(--ui-surface); color: var(--ui-green); border-color: var(--ui-border-card); }
    .ui-btn-secondary:hover:not(:disabled) { border-color: var(--ui-green); }

    .ui-btn-ghost     { background-color: transparent; color: var(--ui-body); }
    .ui-btn-ghost:hover:not(:disabled)     { background-color: var(--ui-surface-alt); color: var(--ui-ink); }

    .ui-btn-danger    { background-color: var(--ui-surface); color: var(--ui-danger); border-color: #F0D6D6; }
    .ui-btn-danger:hover:not(:disabled)    { background-color: var(--ui-danger-tint); border-color: var(--ui-danger); }

    /* ── Status pills ───────────────────────────────────────── */
    .ui-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 999px;
        font-size: 10.5px; font-weight: 700; letter-spacing: .01em;
        white-space: nowrap;
    }
    .ui-pill-ok      { background: var(--ui-green-tint); color: var(--ui-green); }
    .ui-pill-warn    { background: var(--ui-gold-tint);  color: #8A6D1F; }
    .ui-pill-danger  { background: var(--ui-danger-tint);color: var(--ui-danger); }
    .ui-pill-neutral { background: #F2F5F2;              color: var(--ui-muted); }

    /* ── Flash messages ─────────────────────────────────────── */
    .ui-alert {
        display: flex; align-items: flex-start; gap: 9px;
        padding: 11px 14px; border-radius: var(--ui-radius);
        font-size: 12.5px; line-height: 1.5; border: 1px solid transparent;
    }
    .ui-alert > svg, .ui-alert > i { flex: none; margin-top: 1px; }
    .ui-alert-ok     { background: var(--ui-green-tint);  border-color: #BFDCC8; color: #14532D; }
    .ui-alert-warn   { background: var(--ui-gold-tint);   border-color: #EAD9AC; color: #8A6D1F; }
    .ui-alert-danger { background: var(--ui-danger-tint); border-color: #F5C9C9; color: var(--ui-danger); }

    /* ── Tables ─────────────────────────────────────────────── */
    .ui-table-wrap { overflow-x: auto; }
    .ui-table { width: 100%; border-collapse: collapse; text-align: left; }
    .ui-table thead th {
        padding: 11px 8px;
        background: var(--ui-surface-alt);
        font-size: 11px; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: var(--ui-label);
        white-space: nowrap;
    }
    .ui-table tbody td { padding: 13px 8px; font-size: 12.5px; color: var(--ui-body); vertical-align: middle; }
    .ui-table tbody tr { border-top: 1px solid var(--ui-border-soft); }
    .ui-table thead th:first-child, .ui-table tbody td:first-child { padding-left: 20px; }
    .ui-table thead th:last-child,  .ui-table tbody td:last-child  { padding-right: 20px; }

    .ui-empty { padding: 44px 16px; text-align: center; font-size: 12.5px; color: var(--ui-muted); }
</style>
@endonce

{{-- Dark-mode foundation: `darkMode: 'class'` merged onto whatever
     `tailwind.config` this page set, the contract palette, the no-flash boot
     script and the toggle. Included from here, and not from 47 hand-edited
     copies, because every page in the platform already includes the kit — and
     always later in <head> than its own `tailwind.config` assignment, which is
     what makes the merge land. See resources/views/pages/partials/theme.blade.php
     and docs/DARK-MODE-CONTRACT.md. --}}
@include('pages.partials.theme')
