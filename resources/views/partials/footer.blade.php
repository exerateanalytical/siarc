@php $authUser = $authUser ?? session('auth_user'); @endphp
<footer>
<style>
footer{margin-top:3rem;background:var(--dark);color:#8899aa;font-size:.82rem;text-align:left;}
.footer-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:2rem;padding:2.5rem 2rem 2rem;}
.footer-brand p{color:#6b7a8d;margin-top:.5rem;font-size:.8rem;line-height:1.6;max-width:220px;}
.footer-col h4{color:#fff;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:.75rem;}
.footer-col a{display:block;color:#8899aa;margin-bottom:.4rem;font-size:.81rem;transition:color .15s;}
.footer-col a:hover{color:var(--yellow);}
.footer-bottom{border-top:1px solid rgba(255,255,255,.06);padding:1rem 2rem;display:flex;align-items:center;justify-content:flex-start;gap:1.5rem;max-width:1200px;margin:0 auto;flex-wrap:wrap;}
.footer-flag{display:flex;height:14px;border-radius:2px;overflow:hidden;margin-right:.4rem;}
.footer-flag span{display:block;width:6px;height:14px;}
@media(max-width:700px){.footer-grid{grid-template-columns:1fr 1fr;gap:1.5rem;}.footer-bottom{flex-direction:column;align-items:flex-start;}}
@media(max-width:440px){.footer-grid{grid-template-columns:1fr;}}
</style>
<div class="footer-grid">
    <div class="footer-brand">
        <div style="display:flex;align-items:center;gap:.5rem;color:#fff;font-weight:800;font-size:1rem;">
            <div class="footer-flag"><span class="f-g"></span><span class="f-r"></span><span class="f-y"></span></div>
            Galerie virtuelle de l'artisanat du Cameroun
        </div>
        <p>The leading platform for Cameroon company intelligence, share offerings, and talent.</p>
    </div>
    <div class="footer-col">
        <h4>Platform</h4>
        <a href="/">Company Directory</a>
        <a href="/associations">Associations</a>
        <a href="/collabcam" style="color:var(--yellow);">CollabCam</a>
        <a href="/offerings">Share Offerings</a>
        <a href="/jobs">Jobs Board</a>
        <a href="/blog">Blog &amp; Insights</a>
    </div>
    <div class="footer-col">
        <h4>Company</h4>
        <a href="/about">About Us</a>
        <a href="/how-it-works">How It Works</a>
        <a href="/help">Help Centre</a>
        <a href="/support">Contact Support</a>
    </div>
    <div class="footer-col">
        <h4>Legal &amp; Dev</h4>
        <a href="/privacy">Privacy Policy</a>
        <a href="/terms">Terms of Service</a>
        <a href="/docs/api">API Docs</a>
        @if($authUser)<a href="/developer">Developer Keys</a>@else<a href="/register">Create Account</a>@endif
    </div>
</div>
<div class="footer-bottom">
    <span>&copy; {{ date('Y') }} Galerie virtuelle de l'artisanat du Cameroun · Built in Cameroon 🇨🇲</span>
    @if($authUser)
        <a href="/dashboard" style="color:#8899aa;">Dashboard</a>
        <a href="/portfolio" style="color:#8899aa;">Portfolio</a>
        <a href="/wallet" style="color:#8899aa;">Wallet</a>
    @else
        <a href="/login" style="color:#8899aa;">Log in</a>
        <a href="/register" style="color:var(--yellow);">Register free</a>
    @endif
</div>
</footer>
