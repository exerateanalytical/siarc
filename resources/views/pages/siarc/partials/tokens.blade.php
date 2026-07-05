{{-- ═══════════════════════════════════════════════════════════════════════════
     SIARC 2026 design tokens — one source of truth for the whole SIARC module.
     Included inside the <head> of layouts/siarc-admin and pages/siarc/public.
     Palette, kente weave, mudcloth texture, cards, badges, animations.
     100% local assets (no CDNs); pairs with vendor/tailwindcss.js + fonts.css.
════════════════════════════════════════════════════════════════════════════ --}}
<style>
    :root{
        --si-green-900:#042B15; --si-green-800:#0B3A1E; --si-green-700:#0F4824;
        --si-green-600:#14652F; --si-green-500:#157A43; --si-green-400:#2E8B57;
        --si-gold-600:#C97A16;  --si-gold-500:#E6B201;  --si-gold-400:#FBB604;
        --si-red:#C0010C;       --si-cream:#F8F4EC;      --si-cream-2:#FBF7F0;
        --si-ink:#1D1B16;       --si-ink-soft:#55524A;   --si-line:#ECEAE3;
    }
    .font-display{font-family:'Playfair Display',Georgia,serif;}

    /* ── Kente woven band (top borders / accents) ───────────────────────────── */
    .siarc-kente{height:14px;width:100%;position:relative;overflow:hidden;
        background:repeating-linear-gradient(45deg,
            var(--si-gold-500) 0 7px,var(--si-green-700) 7px 14px,
            var(--si-red) 14px 21px,var(--si-green-800) 21px 28px);}
    .siarc-kente::after{content:"";position:absolute;inset:0;
        background:repeating-linear-gradient(-45deg,
            rgba(0,0,0,.18) 0 7px,transparent 7px 14px),
            repeating-linear-gradient(-45deg,
            rgba(255,255,255,.10) 3px,transparent 4px 11px);}
    .siarc-kente-thin{height:6px;}
    /* vertical kente rail for side accents */
    .siarc-kente-v{width:14px;height:100%;
        background:repeating-linear-gradient(45deg,
            var(--si-gold-500) 0 7px,var(--si-green-700) 7px 14px,
            var(--si-red) 14px 21px,var(--si-green-800) 21px 28px);}

    /* ── Mudcloth / bogolan faint texture for cream sections ────────────────── */
    .siarc-mud{background-color:var(--si-cream);
        background-image:
            radial-gradient(circle at 1px 1px,rgba(15,72,36,.055) 1px,transparent 0),
            repeating-linear-gradient(45deg,rgba(201,122,22,.035) 0 2px,transparent 2px 22px);
        background-size:22px 22px,100% 100%;}
    .siarc-adire{background-color:var(--si-green-800);
        background-image:
            radial-gradient(circle at 10px 10px,rgba(230,178,1,.10) 2px,transparent 0),
            radial-gradient(circle at 30px 30px,rgba(255,255,255,.05) 1.5px,transparent 0);
        background-size:40px 40px;}

    /* ── Cards & elevation ──────────────────────────────────────────────────── */
    .siarc-card{background:#fff;border:1px solid var(--si-line);border-radius:16px;}
    .siarc-shadow{box-shadow:0 1px 2px rgba(16,40,24,.04),0 10px 26px -12px rgba(16,40,24,.14);}
    .siarc-shadow-lg{box-shadow:0 18px 48px -18px rgba(6,43,21,.34);}
    .siarc-lift{transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;}
    .siarc-lift:hover{transform:translateY(-3px);
        box-shadow:0 20px 40px -18px rgba(6,43,21,.30);border-color:#D7E4DB;}

    /* ── Numbered corner badge (feature/module cards) ───────────────────────── */
    .siarc-num{font-family:'Playfair Display',serif;font-weight:800;
        color:var(--si-gold-500);line-height:1;}

    /* ── Section heading kicker (◆ label ◆) ─────────────────────────────────── */
    .siarc-kicker{display:inline-flex;align-items:center;gap:.6rem;
        font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:12px;}
    .siarc-kicker::before,.siarc-kicker::after{content:"";width:26px;height:2px;
        background:linear-gradient(90deg,transparent,var(--si-gold-500));}
    .siarc-kicker::after{transform:scaleX(-1);}

    /* ── Buttons ────────────────────────────────────────────────────────────── */
    .siarc-btn{display:inline-flex;align-items:center;gap:.5rem;font-weight:600;
        border-radius:12px;transition:transform .15s ease,filter .15s ease,background .15s;}
    .siarc-btn:hover{filter:brightness(1.05);}
    .siarc-btn:active{transform:translateY(1px);}
    .siarc-btn-primary{background:var(--si-gold-500);color:#3a2a00;}
    .siarc-btn-green{background:var(--si-green-500);color:#fff;}
    .siarc-btn-outline{border:1.5px solid rgba(255,255,255,.35);color:#fff;}

    /* ── Animations ─────────────────────────────────────────────────────────── */
    @keyframes siarcFade{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
    .siarc-in{animation:siarcFade .5s ease both;}
    @keyframes siarcPulse{0%,100%{opacity:1}50%{opacity:.35}}
    .siarc-pulse{animation:siarcPulse 1.6s ease-in-out infinite;}

    /* ── Scrollbar (admin sidebar) ──────────────────────────────────────────── */
    .siarc-scroll::-webkit-scrollbar{width:8px;}
    .siarc-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:8px;}
    .siarc-scroll::-webkit-scrollbar-track{background:transparent;}

    /* ── Tabs (data-attribute driven — see vendor/siarc-ui.js) ──────────────── */
    .si-tab{position:relative;padding:0 0 .8rem;font-size:13.5px;font-weight:600;color:#8A857A;cursor:pointer;white-space:nowrap;background:none;border:none;transition:color .15s;}
    .si-tab:hover{color:#3B382F;}
    .si-tab.is-active{color:var(--si-green-500);}
    .si-tab.is-active::after{content:"";position:absolute;left:0;right:0;bottom:-1px;height:2.5px;border-radius:9999px;background:var(--si-green-500);}
    [data-page-num].is-active{background:var(--si-green-500);color:#fff;}
</style>
