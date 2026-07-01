<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>How It Works — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark) 0%,var(--mid) 100%);padding:3rem 2rem;text-align:center;color:#fff;}
.hero h1{font-size:2rem;font-weight:800;margin-bottom:.5rem;}
.hero p{color:#aab;max-width:560px;margin:0 auto;}
.page{max-width:960px;margin:0 auto;padding:2rem 1.5rem 3rem;}
.section-title{font-size:1.4rem;font-weight:800;text-align:center;margin:2.5rem 0 1.5rem;}
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.2rem;}
.step{background:var(--white);border-radius:var(--radius);padding:1.5rem;box-shadow:var(--shadow);position:relative;}
.step-num{width:36px;height:36px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem;margin-bottom:.9rem;}
.step h3{font-size:.95rem;font-weight:700;margin-bottom:.4rem;}
.step p{font-size:.82rem;color:var(--muted);line-height:1.6;margin:0;}
.tabs{display:flex;border-bottom:2px solid var(--border);margin-bottom:1.5rem;gap:0;overflow-x:auto;}
.tab-btn{padding:.65rem 1.3rem;border:none;background:transparent;font-weight:600;font-size:.88rem;cursor:pointer;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;white-space:nowrap;}
.tab-btn.active{color:var(--green);border-bottom-color:var(--green);}
.tab-panel{display:none;}.tab-panel.active{display:block;}
.faq-item{border-bottom:1px solid var(--border);padding:1rem 0;}
.faq-q{font-weight:700;font-size:.9rem;cursor:pointer;display:flex;justify-content:space-between;align-items:center;}
.faq-a{font-size:.85rem;color:var(--muted);line-height:1.65;margin-top:.5rem;display:none;}
.faq-item.open .faq-a{display:block;}
.faq-item.open .faq-q::after{content:'−';}
.faq-q::after{content:'+';font-size:1.1rem;}
.cta{background:linear-gradient(135deg,var(--green),#009040);border-radius:var(--radius);padding:2rem;text-align:center;color:#fff;margin-top:2.5rem;}
.cta h2{font-size:1.3rem;font-weight:800;margin-bottom:.4rem;}
.cta p{opacity:.85;margin-bottom:1.2rem;font-size:.9rem;}
.cta a{display:inline-block;padding:.7rem 2rem;background:#fff;color:var(--green);border-radius:8px;font-weight:700;text-decoration:none;margin:.3rem;}
.cta a.sec{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.4);}
</style>

<div class="hero">
    <h1>How Galerie virtuelle de l'artisanat du Cameroun Works</h1>
    <p>Your complete guide to browsing companies, investing in regulated offerings, finding jobs, and building your career in Cameroon.</p>
</div>

<div class="page">
    <div class="tabs" id="tabs">
        <button class="tab-btn active" onclick="switchTab('investors')">For Investors</button>
        <button class="tab-btn" onclick="switchTab('companies')">For Companies</button>
        <button class="tab-btn" onclick="switchTab('jobseekers')">For Job Seekers</button>
        <button class="tab-btn" onclick="switchTab('developers')">For Developers</button>
    </div>

    <div class="tab-panel active" id="tab-investors">
        <div class="section-title" style="text-align:left;font-size:1.1rem;margin-top:0;">Invest in Cameroonian Companies</div>
        <div class="steps">
            <div class="step"><div class="step-num">1</div><h3>Create Account</h3><p>Register with your email and complete your investor profile. KYC is required before investments above 500,000 XAF.</p></div>
            <div class="step"><div class="step-num">2</div><h3>Browse Offerings</h3><p>Filter by sector, instrument type (shares, bonds), minimum investment, and CMF approval status.</p></div>
            <div class="step"><div class="step-num">3</div><h3>Pledge</h3><p>Enter your investment amount. The platform confirms your pledge and locks it for 24 hours pending payment.</p></div>
            <div class="step"><div class="step-num">4</div><h3>Pay</h3><p>Pay via MTN Mobile Money, Orange Money, or bank transfer within 24 hours. Unpaid pledges expire automatically.</p></div>
            <div class="step"><div class="step-num">5</div><h3>Receive Allocation</h3><p>Once the offering closes and CMF approves, shares or bonds are allocated to your account within 10 business days.</p></div>
            <div class="step"><div class="step-num">6</div><h3>Track Portfolio</h3><p>View your holdings, investment history, and dividend income in your investor dashboard.</p></div>
        </div>
        <div style="margin-top:1.5rem;padding:1rem;background:#e8f5e9;border-radius:var(--radius);font-size:.85rem;color:#2e7d32;">
            <strong>CMF Regulated:</strong> All share and bond offerings on Galerie virtuelle de l'artisanat du Cameroun are reviewed by the Commission des Marchés Financiers. We do not host unregulated securities. <a href="/help/share-allocation-process" style="color:var(--green);">Learn about allocation →</a>
        </div>
    </div>

    <div class="tab-panel" id="tab-companies">
        <div class="section-title" style="text-align:left;font-size:1.1rem;margin-top:0;">List and Grow Your Company</div>
        <div class="steps">
            <div class="step"><div class="step-num">1</div><h3>Find Your Listing</h3><p>Search the directory for your company. Many companies are pre-populated from RCCM and NIU data.</p></div>
            <div class="step"><div class="step-num">2</div><h3>Claim Ownership</h3><p>Submit a claim with proof of authority (RCCM extract or power of attorney). Approved within 3 business days.</p></div>
            <div class="step"><div class="step-num">3</div><h3>Complete Your Profile</h3><p>Add products/services, photos, team members, awards, and branch locations. Verified listings get 3× more views.</p></div>
            <div class="step"><div class="step-num">4</div><h3>Post Jobs</h3><p>Reach qualified Cameroonian candidates by posting positions on the job board with salary ranges and requirements.</p></div>
            <div class="step"><div class="step-num">5</div><h3>Raise Capital</h3><p>Work with our CMF-licensed partners to structure a share or bond offering. We handle the CMF submission process.</p></div>
            <div class="step"><div class="step-num">6</div><h3>Build Reputation</h3><p>Collect verified reviews from customers and investors. Respond publicly to maintain trust.</p></div>
        </div>
    </div>

    <div class="tab-panel" id="tab-jobseekers">
        <div class="section-title" style="text-align:left;font-size:1.1rem;margin-top:0;">Find Your Next Role in Cameroon</div>
        <div class="steps">
            <div class="step"><div class="step-num">1</div><h3>Create Account</h3><p>Register and set up your career profile with your headline, skills, and job preferences.</p></div>
            <div class="step"><div class="step-num">2</div><h3>Build Your CV</h3><p>Use the CV Builder to create a professional CV in English or French. Export as PDF or share with a public link.</p></div>
            <div class="step"><div class="step-num">3</div><h3>Browse Jobs</h3><p>Filter by job type (full-time, internship, remote), location, company, and salary range.</p></div>
            <div class="step"><div class="step-num">4</div><h3>Apply</h3><p>Submit your cover letter directly through the platform. Your Galerie virtuelle de l'artisanat du Cameroun profile is attached automatically.</p></div>
            <div class="step"><div class="step-num">5</div><h3>Track Applications</h3><p>Monitor application status (submitted, shortlisted, interview, offered) in your career dashboard.</p></div>
            <div class="step"><div class="step-num">6</div><h3>Get Hired</h3><p>Companies contact you directly via your registered email when they shortlist or offer a position.</p></div>
        </div>
    </div>

    <div class="tab-panel" id="tab-developers">
        <div class="section-title" style="text-align:left;font-size:1.1rem;margin-top:0;">Integrate the Galerie virtuelle de l'artisanat du Cameroun API</div>
        <div class="steps">
            <div class="step"><div class="step-num">1</div><h3>Get API Key</h3><p>Log in and visit the Developer Portal. Create a named API key — free tier includes 1,000 requests/day.</p></div>
            <div class="step"><div class="step-num">2</div><h3>Authenticate</h3><p>Pass your key via <code>Authorization: Bearer cc_xxx</code> header on all API requests.</p></div>
            <div class="step"><div class="step-num">3</div><h3>Call Endpoints</h3><p>Access <code>/api/v1/companies</code>, <code>/api/v1/offerings</code>, <code>/api/v1/jobs</code> and more. Full OpenAPI 3.1 spec available.</p></div>
            <div class="step"><div class="step-num">4</div><h3>Set Up Webhooks</h3><p>Subscribe to events (new_offering, offering_closed, company_verified) via the webhook management panel.</p></div>
        </div>
        <p style="margin-top:1rem;font-size:.85rem;color:var(--muted);">View the full API documentation at <a href="/docs/api">/docs/api</a> or <a href="/developer">manage your API keys →</a></p>
    </div>

    <div style="margin-top:2.5rem;">
        <div class="section-title" style="font-size:1.1rem;">Frequently Asked Questions</div>
        @php $faqs = [
            ['Is investing through Galerie virtuelle de l'artisanat du Cameroun safe?','All offerings are reviewed by CMF before publication. Galerie virtuelle de l'artisanat du Cameroun itself is not a broker — we are a marketplace connecting investors with CMF-licensed issuers. Investment always carries risk; never invest more than you can afford to lose.'],
            ['What is the minimum investment?','Minimum investment amounts are set by each issuer. Most offerings start at 50,000–100,000 XAF. Check the offering page for exact minimums.'],
            ['Can foreigners invest?','Non-Cameroonian residents may browse the directory but must comply with Cameroonian foreign investment regulations before making pledges. Contact us for guidance.'],
            ['How long does KYC take?','KYC document review takes 24-48 business hours. You can invest up to 500,000 XAF without KYC.'],
            ['Is the job board free for job seekers?','Yes, browsing and applying for jobs is completely free for job seekers. Companies pay a listing fee.'],
        ]; @endphp
        @foreach($faqs as [$q,$a])
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">{{ $q }}</div>
            <div class="faq-a">{{ $a }}</div>
        </div>
        @endforeach
    </div>

    <div class="cta">
        <h2>Ready to get started?</h2>
        <p>Join thousands of Cameroonian investors, companies, and professionals on Galerie virtuelle de l'artisanat du Cameroun.</p>
        <a href="/register">Create Free Account</a>
        <a href="/" class="sec">Browse Companies</a>
    </div>
</div>

<script>
function switchTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+id).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>
@include('partials.footer')
</body>
</html>
