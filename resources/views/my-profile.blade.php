<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>My Career Profile — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:900px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
h1{font-size:1.3rem;font-weight:800;margin-bottom:.3rem;}
.subtitle{font-size:.83rem;color:var(--muted);margin-bottom:1.5rem;}
.tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:1.2rem;}
.tab-btn{padding:.6rem 1.2rem;background:none;border:none;cursor:pointer;font-size:.85rem;font-weight:600;color:var(--muted);border-bottom:2px solid transparent;margin-bottom:-2px;}
.tab-btn.active{color:var(--green);border-bottom-color:var(--green);}
.tab-content{display:none;} .tab-content.active{display:block;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;}
.card h2{font-size:.98rem;font-weight:700;margin-bottom:.9rem;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;}
@media(max-width:600px){.form-grid{grid-template-columns:1fr;}}
.form-group{display:flex;flex-direction:column;gap:.3rem;}
.form-group.full{grid-column:1/-1;}
label{font-size:.78rem;font-weight:600;}
input,select,textarea{padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--green);}
.save-btn{padding:.6rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.88rem;cursor:pointer;}
.tag-input{display:flex;flex-wrap:wrap;gap:.3rem;padding:.4rem .6rem;border:1px solid var(--border);border-radius:7px;min-height:40px;cursor:text;}
.tag-item{background:var(--light-bg);border-radius:5px;padding:.15rem .6rem;font-size:.78rem;display:flex;align-items:center;gap:.3rem;}
.tag-item button{background:none;border:none;cursor:pointer;color:var(--muted);font-size:.85rem;line-height:1;}
.tag-add{border:none;outline:none;font-size:.82rem;flex:1;min-width:80px;}
.table-wrap{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{padding:.5rem .8rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--muted);border-bottom:2px solid var(--border);background:var(--light-bg);}
td{padding:.7rem .8rem;font-size:.83rem;border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
.badge{font-size:.7rem;padding:2px 8px;border-radius:99px;font-weight:700;text-transform:uppercase;}
.b-submitted{background:#d4edda;color:#007a33;}.b-shortlisted{background:#cce5ff;color:#0056b3;}.b-interview{background:#fff3cd;color:#7a5900;}.b-offered{background:#d4edda;color:#007a33;font-weight:900;}.b-rejected{background:#f8d7da;color:#721c24;}.b-withdrawn{background:#eee;color:#666;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.empty{text-align:center;padding:2.5rem;color:var(--muted);font-size:.85rem;}
</style>

<div class="page">
    <h1>My Career Profile</h1>
    <p class="subtitle">Build your professional profile, manage your skills, and track job applications.</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('profile',this)">Profile</button>
        <button class="tab-btn" onclick="showTab('applications',this)">Applications {{ $applications->count() > 0 ? '('.$applications->count().')' : '' }}</button>
        <button class="tab-btn" onclick="showTab('cvs',this)">My CVs</button>
    </div>

    <div id="tab-profile" class="tab-content active">
        <form method="POST" action="/my-profile">
            @csrf
            <div class="card">
                <h2>Professional Headline</h2>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Professional Headline</label>
                        <input type="text" name="headline" value="{{ $profile->headline ?? '' }}" placeholder="e.g. Senior Software Engineer with 5+ years in FinTech">
                    </div>
                    <div class="form-group full">
                        <label>Summary / Bio</label>
                        <textarea name="summary" rows="3" placeholder="Brief professional summary…">{{ $profile->summary ?? '' }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" value="{{ $profile->location ?? '' }}" placeholder="e.g. Douala, Cameroon">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" value="{{ $profile->phone ?? '' }}" placeholder="+237 6xx xxx xxx">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Online Presence</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="{{ $profile->linkedin_url ?? '' }}" placeholder="https://linkedin.com/in/you">
                    </div>
                    <div class="form-group">
                        <label>GitHub URL</label>
                        <input type="url" name="github_url" value="{{ $profile->github_url ?? '' }}" placeholder="https://github.com/you">
                    </div>
                    <div class="form-group full">
                        <label>Portfolio / Website</label>
                        <input type="url" name="portfolio_url" value="{{ $profile->portfolio_url ?? '' }}" placeholder="https://yoursite.com">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Job Preferences</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Open to Work?</label>
                        <select name="open_to_work">
                            <option value="1" {{ ($profile->open_to_work ?? 1) ? 'selected' : '' }}>Yes — actively looking</option>
                            <option value="0" {{ !($profile->open_to_work ?? 1) ? 'selected' : '' }}>No — not looking</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Job Type Preference</label>
                        <select name="job_type_preference">
                            @foreach(['any'=>'Any','full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($profile->job_type_preference ?? 'any') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Salary Expectation (XAF/month)</label>
                        <input type="number" name="salary_expectation" value="{{ $profile->salary_expectation ?? '' }}" placeholder="e.g. 350000" step="10000">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Skills</h2>
                @php $skills = json_decode($profile->skills ?? '[]', true) ?: []; @endphp
                <div class="tag-input" id="skills-container">
                    @foreach($skills as $sk)<span class="tag-item">{{ $sk }}<button type="button" onclick="this.parentElement.remove()">×</button></span>@endforeach
                    <input class="tag-add" id="skill-input" placeholder="Type skill + Enter…">
                </div>
                <input type="hidden" name="skills" id="skills-hidden" value="{{ $profile->skills ?? '[]' }}">
                <script>
                document.getElementById('skill-input').addEventListener('keydown',function(e){
                    if(e.key==='Enter'||e.key===','){e.preventDefault();var v=this.value.trim();if(!v)return;var t=document.createElement('span');t.className='tag-item';t.innerHTML=v+'<button type="button" onclick="this.parentElement.remove()">×</button>';this.before(t);this.value='';updateSkills();}
                });
                function updateSkills(){var t=[...document.querySelectorAll('#skills-container .tag-item')].map(x=>x.textContent.slice(0,-1).trim());document.getElementById('skills-hidden').value=JSON.stringify(t);}
                document.getElementById('skills-container').addEventListener('click',function(){document.getElementById('skill-input').focus();setTimeout(updateSkills,10);});
                </script>
            </div>

            <div class="card">
                <h2>Work Experience</h2>
                @php $expList = json_decode($profile->experience ?? '[]', true) ?: []; @endphp
                <div id="exp-list">
                    @foreach($expList as $i=>$ex)
                    <div class="exp-entry" data-index="{{ $i }}" style="border:1px solid var(--border);border-radius:8px;padding:.8rem;margin-bottom:.6rem;position:relative;">
                        <button type="button" onclick="removeEntry('exp',{{ $i }})" style="position:absolute;top:.5rem;right:.5rem;background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;">×</button>
                        <div class="form-grid">
                            <div class="form-group"><label>Job Title</label><input type="text" class="exp-title" value="{{ $ex['title'] ?? '' }}" placeholder="e.g. Software Engineer" onchange="syncExp()"></div>
                            <div class="form-group"><label>Company</label><input type="text" class="exp-company" value="{{ $ex['company'] ?? '' }}" placeholder="e.g. Orange Cameroun" onchange="syncExp()"></div>
                            <div class="form-group"><label>Start Date</label><input type="text" class="exp-start" value="{{ $ex['start'] ?? '' }}" placeholder="e.g. Jan 2022" onchange="syncExp()"></div>
                            <div class="form-group"><label>End Date</label><input type="text" class="exp-end" value="{{ $ex['end'] ?? '' }}" placeholder="e.g. Present" onchange="syncExp()"></div>
                            <div class="form-group full"><label>Description</label><textarea class="exp-desc" rows="2" placeholder="Key responsibilities…" onchange="syncExp()">{{ $ex['description'] ?? '' }}</textarea></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="experience" id="exp-hidden" value="{{ $profile->experience ?? '[]' }}">
                <button type="button" onclick="addExpEntry()" style="font-size:.82rem;padding:.35rem .8rem;border:1px dashed var(--border);border-radius:7px;background:#fff;cursor:pointer;color:var(--green);">+ Add Experience</button>
            </div>

            <div class="card">
                <h2>Education</h2>
                @php $eduList = json_decode($profile->education ?? '[]', true) ?: []; @endphp
                <div id="edu-list">
                    @foreach($eduList as $i=>$ed)
                    <div class="edu-entry" data-index="{{ $i }}" style="border:1px solid var(--border);border-radius:8px;padding:.8rem;margin-bottom:.6rem;position:relative;">
                        <button type="button" onclick="removeEntry('edu',{{ $i }})" style="position:absolute;top:.5rem;right:.5rem;background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;">×</button>
                        <div class="form-grid">
                            <div class="form-group"><label>Degree / Certificate</label><input type="text" class="edu-degree" value="{{ $ed['degree'] ?? '' }}" placeholder="e.g. BSc Computer Science" onchange="syncEdu()"></div>
                            <div class="form-group"><label>Institution</label><input type="text" class="edu-institution" value="{{ $ed['institution'] ?? '' }}" placeholder="e.g. University of Yaoundé I" onchange="syncEdu()"></div>
                            <div class="form-group"><label>Year</label><input type="text" class="edu-year" value="{{ $ed['year'] ?? '' }}" placeholder="e.g. 2019" onchange="syncEdu()"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="education" id="edu-hidden" value="{{ $profile->education ?? '[]' }}">
                <button type="button" onclick="addEduEntry()" style="font-size:.82rem;padding:.35rem .8rem;border:1px dashed var(--border);border-radius:7px;background:#fff;cursor:pointer;color:var(--green);">+ Add Education</button>
            </div>

            <div class="card">
                <h2>Certifications</h2>
                @php $certList = json_decode($profile->certifications ?? '[]', true) ?: []; @endphp
                <div id="cert-list">
                    @foreach($certList as $i=>$ce)
                    <div class="cert-entry" data-index="{{ $i }}" style="border:1px solid var(--border);border-radius:8px;padding:.8rem;margin-bottom:.6rem;position:relative;">
                        <button type="button" onclick="removeEntry('cert',{{ $i }})" style="position:absolute;top:.5rem;right:.5rem;background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;">×</button>
                        <div class="form-grid">
                            <div class="form-group"><label>Certificate Name</label><input type="text" class="cert-name" value="{{ $ce['name'] ?? '' }}" placeholder="e.g. AWS Certified Developer" onchange="syncCert()"></div>
                            <div class="form-group"><label>Issuer / Year</label><input type="text" class="cert-issuer" value="{{ $ce['issuer'] ?? '' }}" placeholder="e.g. Amazon, 2023" onchange="syncCert()"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="certifications" id="cert-hidden" value="{{ $profile->certifications ?? '[]' }}">
                <button type="button" onclick="addCertEntry()" style="font-size:.82rem;padding:.35rem .8rem;border:1px dashed var(--border);border-radius:7px;background:#fff;cursor:pointer;color:var(--green);">+ Add Certification</button>
            </div>

            <div class="card">
                <h2>Languages</h2>
                @php $langList = json_decode($profile->languages ?? '[]', true) ?: []; @endphp
                <div id="lang-list">
                    @foreach($langList as $i=>$la)
                    <div class="lang-entry" style="display:flex;gap:.6rem;margin-bottom:.4rem;align-items:center;">
                        <input type="text" class="lang-name" value="{{ $la['name'] ?? '' }}" placeholder="Language" style="flex:2;" onchange="syncLang()">
                        <select class="lang-level" onchange="syncLang()" style="flex:1;">
                            @foreach(['native'=>'Native','fluent'=>'Fluent','advanced'=>'Advanced','intermediate'=>'Intermediate','basic'=>'Basic'] as $lv=>$ll)
                            <option value="{{ $lv }}" {{ ($la['level']??'')===$lv?'selected':'' }}>{{ $ll }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="this.closest('.lang-entry').remove();syncLang();" style="background:none;border:none;cursor:pointer;color:var(--muted);">×</button>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="languages" id="lang-hidden" value="{{ $profile->languages ?? '[]' }}">
                <button type="button" onclick="addLangEntry()" style="font-size:.82rem;padding:.35rem .8rem;border:1px dashed var(--border);border-radius:7px;background:#fff;cursor:pointer;color:var(--green);">+ Add Language</button>
            </div>

            <button type="submit" class="save-btn" onclick="syncAll()">Save Profile</button>
            <a href="/cv" style="display:inline-block;margin-left:.8rem;font-size:.85rem;color:var(--green);">Build your CV →</a>
        </form>
    </div>

    <div id="tab-applications" class="tab-content">
        @if($applications->isEmpty())
        <div class="empty">You haven't applied to any jobs yet. <a href="/jobs" style="color:var(--green);">Browse open positions</a></div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Job</th><th>Company</th><th>Applied</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($applications as $app)
                    <tr>
                        <td><a href="/jobs/{{ $app->job_id }}" style="font-weight:600;color:var(--text);">{{ $app->title_en ?? 'Position' }}</a></td>
                        <td>{{ $app->company_name ?? '—' }}</td>
                        <td>{{ $app->created_at ? date('d M Y', strtotime($app->created_at)) : '' }}</td>
                        <td><span class="badge b-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                        <td style="white-space:nowrap;">
                            <a href="/jobs/{{ $app->job_id }}" style="font-size:.78rem;color:var(--green);">View</a>
                            @if(!in_array($app->status, ['withdrawn','rejected']))
                            · <form method="POST" action="/applications/{{ $app->id }}/withdraw" style="display:inline;" onsubmit="return confirm('Withdraw your application for this role?')">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;font-size:.78rem;color:var(--red);cursor:pointer;font-family:inherit;">Withdraw</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div id="tab-cvs" class="tab-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <span style="font-size:.85rem;color:var(--muted);">{{ $cvs->count() }} CV{{ $cvs->count()!=1?'s':'' }} saved</span>
            <a href="/cv" style="display:inline-block;padding:.5rem 1rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.82rem;text-decoration:none;">+ New CV</a>
        </div>
        @if($cvs->isEmpty())
        <div class="empty">No CVs yet. <a href="/cv" style="color:var(--green);">Create your first CV</a></div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Title</th><th>Template</th><th>Language</th><th>Public</th><th>Last Updated</th><th></th></tr></thead>
                <tbody>
                    @foreach($cvs as $cv)
                    <tr>
                        <td><a href="/cv/{{ $cv->id }}" style="font-weight:600;color:var(--text);">{{ $cv->title }}</a></td>
                        <td>{{ ucfirst($cv->template ?? 'classic') }}</td>
                        <td>{{ strtoupper($cv->language ?? 'en') }}</td>
                        <td>{{ ($cv->is_public??0) ? '<i data-lucide="check" class="lic"></i> Public' : 'Private' }}</td>
                        <td>{{ $cv->updated_at ? date('d M Y', strtotime($cv->updated_at)) : '' }}</td>
                        <td style="white-space:nowrap;"><a href="/cv/{{ $cv->id }}" style="font-size:.78rem;color:var(--green);">Preview</a> · <a href="/cv/{{ $cv->id }}/settings" style="font-size:.78rem;color:var(--muted);">Settings</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
function showTab(id,btn){
    document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+id).classList.add('active');
    btn.classList.add('active');
}

// ── Experience ──
function syncExp(){
    var entries=[...document.querySelectorAll('#exp-list .exp-entry')].map(function(el){
        return{title:el.querySelector('.exp-title').value,company:el.querySelector('.exp-company').value,start:el.querySelector('.exp-start').value,end:el.querySelector('.exp-end').value,description:el.querySelector('.exp-desc').value};
    });
    document.getElementById('exp-hidden').value=JSON.stringify(entries);
}
function addExpEntry(){
    var d=document.createElement('div');d.className='exp-entry';d.style='border:1px solid var(--border);border-radius:8px;padding:.8rem;margin-bottom:.6rem;position:relative;';
    d.innerHTML='<button type="button" onclick="this.closest(\'.exp-entry\').remove();syncExp();" style="position:absolute;top:.5rem;right:.5rem;background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;">×</button><div class="form-grid"><div class="form-group"><label>Job Title</label><input type="text" class="exp-title" placeholder="e.g. Software Engineer" onchange="syncExp()"></div><div class="form-group"><label>Company</label><input type="text" class="exp-company" placeholder="e.g. Orange Cameroun" onchange="syncExp()"></div><div class="form-group"><label>Start Date</label><input type="text" class="exp-start" placeholder="e.g. Jan 2022" onchange="syncExp()"></div><div class="form-group"><label>End Date</label><input type="text" class="exp-end" placeholder="e.g. Present" onchange="syncExp()"></div><div class="form-group full"><label>Description</label><textarea class="exp-desc" rows="2" placeholder="Key responsibilities…" onchange="syncExp()"></textarea></div></div>';
    document.getElementById('exp-list').appendChild(d);
}
function removeEntry(type,i){var el=document.querySelector('#'+type+'-list [data-index="'+i+'"]');if(el)el.remove();if(type==='exp')syncExp();else if(type==='edu')syncEdu();else syncCert();}

// ── Education ──
function syncEdu(){
    var entries=[...document.querySelectorAll('#edu-list .edu-entry')].map(function(el){
        return{degree:el.querySelector('.edu-degree').value,institution:el.querySelector('.edu-institution').value,year:el.querySelector('.edu-year').value};
    });
    document.getElementById('edu-hidden').value=JSON.stringify(entries);
}
function addEduEntry(){
    var d=document.createElement('div');d.className='edu-entry';d.style='border:1px solid var(--border);border-radius:8px;padding:.8rem;margin-bottom:.6rem;position:relative;';
    d.innerHTML='<button type="button" onclick="this.closest(\'.edu-entry\').remove();syncEdu();" style="position:absolute;top:.5rem;right:.5rem;background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;">×</button><div class="form-grid"><div class="form-group"><label>Degree / Certificate</label><input type="text" class="edu-degree" placeholder="e.g. BSc Computer Science" onchange="syncEdu()"></div><div class="form-group"><label>Institution</label><input type="text" class="edu-institution" placeholder="e.g. University of Yaoundé I" onchange="syncEdu()"></div><div class="form-group"><label>Year</label><input type="text" class="edu-year" placeholder="e.g. 2019" onchange="syncEdu()"></div></div>';
    document.getElementById('edu-list').appendChild(d);
}

// ── Certifications ──
function syncCert(){
    var entries=[...document.querySelectorAll('#cert-list .cert-entry')].map(function(el){
        return{name:el.querySelector('.cert-name').value,issuer:el.querySelector('.cert-issuer').value};
    });
    document.getElementById('cert-hidden').value=JSON.stringify(entries);
}
function addCertEntry(){
    var d=document.createElement('div');d.className='cert-entry';d.style='border:1px solid var(--border);border-radius:8px;padding:.8rem;margin-bottom:.6rem;position:relative;';
    d.innerHTML='<button type="button" onclick="this.closest(\'.cert-entry\').remove();syncCert();" style="position:absolute;top:.5rem;right:.5rem;background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;">×</button><div class="form-grid"><div class="form-group"><label>Certificate Name</label><input type="text" class="cert-name" placeholder="e.g. AWS Certified Developer" onchange="syncCert()"></div><div class="form-group"><label>Issuer / Year</label><input type="text" class="cert-issuer" placeholder="e.g. Amazon, 2023" onchange="syncCert()"></div></div>';
    document.getElementById('cert-list').appendChild(d);
}

// ── Languages ──
function syncLang(){
    var entries=[...document.querySelectorAll('#lang-list .lang-entry')].map(function(el){
        return{name:el.querySelector('.lang-name').value,level:el.querySelector('.lang-level').value};
    });
    document.getElementById('lang-hidden').value=JSON.stringify(entries);
}
function addLangEntry(){
    var d=document.createElement('div');d.className='lang-entry';d.style='display:flex;gap:.6rem;margin-bottom:.4rem;align-items:center;';
    d.innerHTML='<input type="text" class="lang-name" placeholder="Language" style="flex:2;" onchange="syncLang()"><select class="lang-level" onchange="syncLang()" style="flex:1;"><option value="native">Native</option><option value="fluent">Fluent</option><option value="advanced">Advanced</option><option value="intermediate">Intermediate</option><option value="basic">Basic</option></select><button type="button" onclick="this.closest(\'.lang-entry\').remove();syncLang();" style="background:none;border:none;cursor:pointer;color:var(--muted);">×</button>';
    document.getElementById('lang-list').appendChild(d);
}

// Sync all JSON fields before submit
function syncAll(){syncExp();syncEdu();syncCert();syncLang();updateSkills();}
</script>
@include('partials.footer')
</body>
</html>
