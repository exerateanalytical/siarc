<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Dashboard — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.page-title{font-size:1.3rem;font-weight:800;margin-bottom:1.2rem;}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;text-align:center;}
.stat-val{font-size:1.6rem;font-weight:800;color:var(--text);}
.stat-lbl{font-size:.75rem;color:var(--muted);margin-top:3px;}
.grid2{display:grid;grid-template-columns:1fr 320px;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-title{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);display:flex;justify-content:space-between;align-items:center;}
.card-body{padding:1rem;}
.pledge-row{display:flex;gap:.75rem;padding:.65rem 0;border-bottom:1px solid var(--border);align-items:flex-start;font-size:.84rem;}
.pledge-row:last-child{border-bottom:none;}
.pledge-info{flex:1;}
.pledge-title{font-weight:600;color:var(--text);}
.pledge-sub{font-size:.76rem;color:var(--muted);margin-top:1px;}
.pledge-amount{font-weight:700;text-align:right;white-space:nowrap;}
.pledge-status{font-size:.68rem;font-weight:700;padding:1px 7px;border-radius:99px;margin-top:3px;display:inline-block;}
.ps-confirmed{background:#d4edda;color:#007a33;}
.ps-pending_payment{background:#fff3cd;color:#856404;}
.ps-cancelled{background:#f8f9fa;color:#6c757d;}
.ps-completed{background:#cce5ff;color:#0056b3;}
.notif-row{display:flex;gap:.6rem;padding:.6rem 0;border-bottom:1px solid var(--border);align-items:flex-start;}
.notif-row:last-child{border-bottom:none;}
.notif-dot{width:8px;height:8px;border-radius:50%;background:var(--green);flex-shrink:0;margin-top:5px;}
.notif-dot-read{background:#ddd;}
.notif-text{flex:1;font-size:.82rem;}
.notif-title{font-weight:600;}
.notif-date{font-size:.71rem;color:var(--muted);margin-top:2px;}
.empty-state{text-align:center;padding:2rem;color:var(--muted);font-size:.85rem;}
.btn-invest{display:inline-block;padding:.4rem .9rem;background:var(--green);color:#fff;border-radius:7px;font-size:.8rem;font-weight:600;}
.btn-invest:hover{background:#00962e;}
@media(max-width:700px){.stats-row{grid-template-columns:1fr 1fr;}.grid2{grid-template-columns:1fr;}}
</style>

@php
    $authUser = session('auth_user');
    $pendingCount = $pledges->whereIn('status',['pending_payment'])->count();
    $confirmedTotal = $pledges->whereIn('status',['confirmed','completed'])->sum('amount');
    $activePledges = $pledges->whereIn('status',['confirmed','pending_payment']);
@endphp

<div class="page">
    <div class="page-title">Welcome back, {{ $authUser['first_name'] }} <i data-lucide="hand" class="lic"></i></div>

    @if(!($authUser['onboarding_completed'] ?? true))
    <div style="background:linear-gradient(135deg,var(--dark),var(--mid));border-radius:var(--radius);padding:1.1rem 1.4rem;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.2rem;flex-wrap:wrap;">
        <div>
            <div style="font-size:.9rem;font-weight:800;margin-bottom:.2rem;">Complete your onboarding</div>
            <div style="font-size:.78rem;color:#aab;">You haven't finished setting up your account yet. It only takes a few minutes.</div>
        </div>
        <a href="/welcome" style="background:var(--green);color:#fff;padding:.5rem 1.2rem;border-radius:7px;font-size:.82rem;font-weight:700;white-space:nowrap;">Get started &rarr;</a>
    </div>
    @endif

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-val">{{ number_format($confirmedTotal/1000000, 1) }}M</div>
            <div class="stat-lbl">XAF Invested</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $pledges->count() }}</div>
            <div class="stat-lbl">Total Pledges</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $pendingCount }}</div>
            <div class="stat-lbl">Pending Payments</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $unreadCount }}</div>
            <div class="stat-lbl">Unread Notifications</div>
        </div>
    </div>

    @php
    $gradeColors = ['A'=>'#16a34a','B'=>'#0284c7','C'=>'#d97706','D'=>'#dc2626','E'=>'#6b7280'];
    @endphp
    <div class="card" style="margin-bottom:1.2rem;">
        <div class="card-title"><i data-lucide="rocket" style="width:18px;height:18px;display:inline;vertical-align:-3px;"></i> Business Hub
            @if(isset($myCompanyHub) && $myCompanyHub)
            <a href="/companies/{{ $myCompanyHub['company']->slug }}?tab=activity" style="font-size:.75rem;color:var(--green);font-weight:400;">{{ $myCompanyHub['company']->name }} →</a>
            @endif
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(135px,1fr));gap:.6rem;">
                @php
                $hubLinks = [
                    ['/prm','handshake','Partners',$hub['partners'] ?? 0],
                    ['/cards','contact','Digital Cards',$hub['cards'] ?? 0],
                ];
                if (isset($myCompanyHub) && $myCompanyHub) {
                    $h = $myCompanyHub;
                    $hubLinks[] = ['/tenders','file-text','Tenders',$h['tenders']];
                    $hubLinks[] = ['/invest-hub','hand-coins','Investment',$h['invest']];
                    $hubLinks[] = ['/events','calendar','Events',$h['events']];
                    $hubLinks[] = ['/innovation','lightbulb','Innovation',$h['innovation']];
                    $hubLinks[] = ['/assets','package','Assets',$h['assets']];
                    $hubLinks[] = ['/logistics','truck','Logistics',$h['logistics']];
                    $hubLinks[] = ['/compliance','shield-check','Compliance',$h['compliance']['total'] > 0 ? $h['compliance']['ok'].'/'.$h['compliance']['total'] : '0'];
                    $hubLinks[] = ['/recruiter','user-round-search','Recruiter',$h['applications'] ?? 0];
                    $hubLinks[] = ['/analytics','bar-chart-3','Analytics','→'];
                }
                @endphp
                @foreach($hubLinks as [$url,$icon,$label,$count])
                <a href="{{ $url }}" style="display:block;padding:.7rem .8rem;background:var(--light-bg);border:1px solid var(--border);border-radius:9px;text-align:center;">
                    <i data-lucide="{{ $icon }}" style="width:22px;height:22px;color:var(--green);"></i>
                    <div style="font-size:1.1rem;font-weight:800;color:var(--text);">{{ $count }}</div>
                    <div style="font-size:.72rem;color:var(--muted);">{{ $label }}</div>
                </a>
                @endforeach
                @if(isset($myCompanyHub) && $myCompanyHub && $myCompanyHub['health'])
                <a href="/health-score/{{ $myCompanyHub['company']->slug }}" style="display:block;padding:.7rem .8rem;background:var(--light-bg);border:1px solid var(--border);border-radius:9px;text-align:center;">
                    <i data-lucide="heart-pulse" style="width:22px;height:22px;color:#16a34a;"></i>
                    <div style="font-size:1.1rem;font-weight:800;color:{{ $gradeColors[$myCompanyHub['health']->grade]??'#6b7280' }};">{{ $myCompanyHub['health']->overall_score }} ({{ $myCompanyHub['health']->grade }})</div>
                    <div style="font-size:.72rem;color:var(--muted);">Health Score</div>
                </a>
                @endif
            </div>
            @if(!isset($myCompanyHub) || !$myCompanyHub)
            <div style="font-size:.8rem;color:var(--muted);margin-top:.7rem;"><i data-lucide="lightbulb" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> <a href="/" style="color:var(--green);">Claim or create a company</a> to unlock tenders, events, assets, compliance tracking, and your collaboration health score.</div>
            @endif
        </div>
    </div>

    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-title">My Investments <a href="/offerings" class="btn-invest" style="font-size:.75rem;">Browse Offerings</a></div>
                <div class="card-body">
                    @if($pledges->isEmpty())
                        <div class="empty-state">You haven't made any investments yet.<br><a href="/offerings" style="color:var(--green);font-weight:600;">Browse open offerings →</a></div>
                    @else
                        @foreach($pledges as $p)
                            @php $stClass = 'ps-'.str_replace('_','-',$p->status); @endphp
                            <div class="pledge-row">
                                <div class="pledge-info">
                                    <div class="pledge-title">{{ $p->title_en }}</div>
                                    <div class="pledge-sub">{{ $p->company_name }} · {{ date('d M Y',strtotime($p->created_at)) }}</div>
                                    <span class="pledge-status {{ $stClass }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                                </div>
                                <div>
                                    <div class="pledge-amount">{{ number_format($p->amount) }} XAF</div>
                                    @if($p->status === 'pending_payment')
                                        <a href="/pay/{{ $p->id }}" style="font-size:.75rem;color:var(--green);font-weight:600;display:block;text-align:right;margin-top:3px;">Pay now →</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            @if($watchlist->count() > 0)
            <div class="card">
                <div class="card-title">Watchlist <a href="/watchlist" style="font-size:.75rem;color:var(--green);font-weight:400;">View all →</a></div>
                <div class="card-body">
                    @foreach($watchlist as $w)
                        <div class="pledge-row">
                            <div class="pledge-info">
                                <div class="pledge-title"><a href="/companies/{{ $w->slug }}" style="color:var(--text)">{{ $w->name }}</a></div>
                                <div class="pledge-sub">{{ ucfirst($w->verification_status) }} · <i data-lucide="eye" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ number_format($w->view_count) }}</div>
                            </div>
                            <a href="/companies/{{ $w->slug }}" class="btn-invest" style="font-size:.72rem;">View →</a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            <div class="card">
                <div class="card-title">Notifications <a href="/notifications" style="font-size:.75rem;color:var(--green);font-weight:400;">View all</a></div>
                <div class="card-body">
                    @if($notifications->isEmpty())
                        <div class="empty-state">No notifications yet.</div>
                    @else
                        @foreach($notifications as $n)
                            <div class="notif-row">
                                <div class="notif-dot {{ $n->read_at?'notif-dot-read':'' }}"></div>
                                <div class="notif-text">
                                    <div class="notif-title">{{ $n->title_en ?: $n->title_fr }}</div>
                                    <div class="notif-date">{{ date('d M Y H:i',strtotime($n->created_at)) }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-title">Quick Links</div>
                <div style="padding:.75rem 1rem;display:flex;flex-direction:column;gap:.4rem;">
                    <a href="/profile" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="user" style="width:15px;height:15px;"></i> My Profile</a>
                    <a href="/my-profile" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="briefcase" style="width:15px;height:15px;"></i> Career Profile</a>
                    <a href="/portfolio" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="trending-up" style="width:15px;height:15px;"></i> My Portfolio</a>
                    <a href="/wallet" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="wallet" style="width:15px;height:15px;"></i> My Wallet</a>
                    <a href="/watchlist" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="star" style="width:15px;height:15px;"></i> Watchlist</a>
                    <a href="/investor-profile" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);"><i data-lucide="contact" class="lic"></i> KYC / Investor</a>
                    <a href="/cv" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="file-text" style="width:15px;height:15px;"></i> My CVs</a>
                    <a href="/jobs" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="briefcase" style="width:15px;height:15px;"></i> Browse Jobs</a>
                    <a href="/support" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="ticket" style="width:15px;height:15px;"></i> Support Tickets</a>
                    <a href="/notifications" style="font-size:.85rem;color:var(--green);padding:.4rem 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.4rem;"><i data-lucide="bell" style="width:15px;height:15px;"></i> Notifications</a>
                    <a href="/developer" style="font-size:.85rem;color:var(--green);padding:.4rem 0;display:flex;align-items:center;gap:.4rem;"><i data-lucide="code" style="width:15px;height:15px;"></i> Developer API</a>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
