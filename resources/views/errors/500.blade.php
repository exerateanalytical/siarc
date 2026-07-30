<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Erreur serveur — Artisan Hub 237</title>
<style>
        /* Nothing may scroll the page sideways on a phone; wide content
           (tables, diagrams) scrolls inside its own container instead. */
        html, body { overflow-x: clip; }

*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',-apple-system,'Segoe UI',sans-serif;background:#F8F6F2;color:#1B1B18;min-height:100vh;display:flex;flex-direction:column;}
.wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:2rem;text-align:center;}
.code{font-size:6rem;font-weight:700;line-height:1;color:#DCE7DF;margin-bottom:.5rem;}
h1{font-size:1.35rem;font-weight:700;margin-bottom:.5rem;color:#14532D;}
p{color:#55524A;font-size:.9rem;margin-bottom:1.5rem;}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:.65rem 1.5rem;background:#0A3020;color:#fff;border-radius:8px;font-weight:600;font-size:.85rem;text-decoration:none;margin:.3rem;}
.btn-sec{background:#fff;color:#14652F;border:1px solid #DCE7DF;}
nav{background:#0A2C1D;padding:.85rem 1.5rem;display:flex;align-items:center;gap:.6rem;}
.logo{color:#fff;min-height:44px;font-weight:700;font-size:.85rem;text-decoration:none;display:flex;align-items:center;gap:.6rem;text-transform:uppercase;letter-spacing:.02em;}
.flag{display:inline-flex;height:20px;border-radius:3px;overflow:hidden;}
.flag span{display:block;width:8px;height:20px;}
.tricolor{display:flex;height:5px;}
.tricolor span:nth-child(1){width:46%;background:#094F2B;}
.tricolor span:nth-child(2){width:26%;background:#B61012;}
.tricolor span:nth-child(3){flex:1;background:#E9A411;}
/* Dark mode. These two pages carry no Tailwind at all, so the palette from
   docs/DARK-MODE-CONTRACT.md is written out by hand. The tricolor and the nav
   are brand marks and stay exactly as they are in both themes. */
html.dark body{background:#0A0C09;color:#F3EFE7;}
html.dark .code{color:#262B21;}
html.dark h1{color:#339B56;}
html.dark p{color:#B4B5A6;}
html.dark .btn{background:#2E9250;color:#04150A;}
html.dark .btn-sec{background:#12150F;color:#339B56;border-color:#68715B;}
</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body>
<nav><a class="logo" href="/"><span class="flag"><span style="background:#094F2B"></span><span style="background:#B61012"></span><span style="background:#E9A411"></span></span>Artisan Hub 237</a></nav>
<div class="tricolor"><span></span><span></span><span></span></div>
<div class="wrap">
    <div>
        <div class="code">500</div>
        <h1>Une erreur est survenue</h1>
        <p>Une erreur inattendue s'est produite. Notre équipe a été notifiée. Veuillez réessayer dans quelques minutes.</p>
        <a href="/" class="btn">Retour à l'accueil</a>
        <a href="/tableau-de-bord/support" class="btn btn-sec">Contacter le support</a>
    </div>
</div>
</body>
</html>
