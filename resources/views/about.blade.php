<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>About Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark) 0%,var(--mid) 100%);padding:3rem 2rem;text-align:center;color:#fff;}
.hero h1{font-size:2rem;font-weight:800;margin-bottom:.5rem;}
.hero p{color:#aab;font-size:1rem;}
.page{max-width:860px;margin:2rem auto;padding:0 1.5rem;}
.section{margin-bottom:2.5rem;}
.section h2{font-size:1.25rem;font-weight:700;margin-bottom:.8rem;color:var(--text);}
.section p{color:var(--muted);line-height:1.75;margin-bottom:.8rem;}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin:2rem 0;}
.stat-card{background:var(--white);border-radius:var(--radius);padding:1.5rem;text-align:center;box-shadow:var(--shadow);}
.stat-num{font-size:2rem;font-weight:900;color:var(--green);display:block;}
.stat-label{font-size:.78rem;color:var(--muted);margin-top:.25rem;}
.team-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-top:1rem;}
.team-card{background:var(--white);border-radius:var(--radius);padding:1.2rem;text-align:center;box-shadow:var(--shadow);}
.avatar{width:56px;height:56px;border-radius:50%;margin:0 auto .75rem;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:#fff;}
.team-name{font-weight:700;font-size:.9rem;}
.team-role{font-size:.78rem;color:var(--muted);}
.vals{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-top:1rem;}
.val{border-left:3px solid var(--green);padding:.8rem 1rem;background:var(--white);border-radius:0 var(--radius) var(--radius) 0;box-shadow:var(--shadow);}
.val h3{font-size:.95rem;font-weight:700;margin-bottom:.3rem;}
.val p{font-size:.8rem;color:var(--muted);margin:0;}
</style>

<div class="hero">
    <h1>About Galerie virtuelle de l'artisanat du Cameroun</h1>
    <p>Connecting investors, businesses, and talent across Cameroon's 10 regions</p>
</div>

<div class="page">
    <div class="stats">
        <div class="stat-card"><span class="stat-num">{{ number_format(DB::table('companies')->whereNull('deleted_at')->count()) }}</span><div class="stat-label">Listed Companies</div></div>
        <div class="stat-card"><span class="stat-num">{{ number_format(DB::table('share_offerings')->whereNull('deleted_at')->whereIn('status',['open','cmf_approved'])->count()) }}</span><div class="stat-label">Active Offerings</div></div>
        <div class="stat-card"><span class="stat-num">{{ number_format(DB::table('job_postings')->where('status','open')->whereNull('deleted_at')->count()) }}</span><div class="stat-label">Open Jobs</div></div>
        <div class="stat-card"><span class="stat-num">10</span><div class="stat-label">Regions Covered</div></div>
    </div>

    <div class="section">
        <h2>Our Mission</h2>
        <p>Galerie virtuelle de l'artisanat du Cameroun was founded to make Cameroon's business landscape transparent and accessible — for investors seeking opportunities, companies seeking capital and talent, and professionals seeking careers.</p>
        <p>We partner with the CMF (Commission des Marchés Financiers), RCCM courts, and regional chambers of commerce to provide verified, up-to-date company information and regulated investment access.</p>
    </div>

    <div class="section">
        <h2>What We Offer</h2>
        <div class="vals">
            <div class="val"><h3>Company Directory</h3><p>Browse {{ number_format(DB::table('companies')->whereNull('deleted_at')->count()) }} registered companies, filter by region, industry, and verification status.</p></div>
            <div class="val"><h3>Investment Marketplace</h3><p>Invest in CMF-regulated share offerings from top Cameroonian companies from as little as 50,000 XAF.</p></div>
            <div class="val"><h3>Job Board</h3><p>Find jobs at verified Cameroonian companies and apply directly through the platform.</p></div>
            <div class="val"><h3>Digital CV Builder</h3><p>Create a professional CV in EN or FR, choose from multiple templates, and share with employers.</p></div>
            <div class="val"><h3>Company Verification</h3><p>Three-tier verification (Basic, Verified, Certified) gives investors confidence in company legitimacy.</p></div>
            <div class="val"><h3>B2B Data API</h3><p>Developers can access company, offering, and market data via our REST API with rate limiting and usage analytics.</p></div>
        </div>
    </div>

    <div class="section">
        <h2>Our Values</h2>
        <p><strong>Transparency:</strong> All company data is sourced from official registries (RCCM, NIU, ANOR). Verification status is displayed clearly on every listing.</p>
        <p><strong>Inclusion:</strong> We support companies across all 10 regions and in both official languages — English and French.</p>
        <p><strong>Regulation:</strong> Investment offerings are only published after CMF review. We do not host unregulated securities.</p>
    </div>

    <div class="section">
        <h2>Leadership</h2>
        <div class="team-grid">
            @php $team = [['Alain N.','CEO & Co-Founder','#007a33'],['Marie F.','CTO & Co-Founder','#ce1126'],['Paul B.','Head of Compliance','#0056b3'],['Sylvie T.','Head of Operations','#7b2d8b']]; @endphp
            @foreach($team as [$name,$role,$color])
            <div class="team-card">
                <div class="avatar" style="background:{{ $color }}">{{ strtoupper(substr($name,0,2)) }}</div>
                <div class="team-name">{{ $name }}</div>
                <div class="team-role">{{ $role }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <h2>Contact Us</h2>
        <p>Email: <a href="mailto:info@camcompany.cm">info@camcompany.cm</a><br>
        Phone: +237 6 XX XX XX XX<br>
        Address: Rue de la Chambre de Commerce, Douala, Cameroun<br>
        Business hours: Mon–Fri 8h–18h WAT</p>
        <a href="/support" style="display:inline-block;padding:.6rem 1.4rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.9rem;text-decoration:none;margin-top:.8rem;">Open a Support Ticket</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
