<?php
// Phase 2-7 routes appended to web.php
// ══════════════════════════════════════════════════════════════════════════════
// PHASE 2: TENDER & PROCUREMENT PORTAL
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/tenders', function (Request $request) {
    return view('tenders');
});

Route::get('/tenders/{id}', function ($id) {
    $tender = DB::table('tenders')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$tender) abort(404);
    $company = DB::table('companies')->where('id',$tender->company_id)->first();
    if (!$company) abort(404);
    return view('tender', compact('tender','company'));
});

Route::post('/tenders', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    DB::table('tenders')->insert([
        'id'            => (string)\Illuminate\Support\Str::uuid(),
        'company_id'    => $request->input('company_id'),
        'posted_by'     => $authUser['id'],
        'title'         => $request->input('title'),
        'description'   => $request->input('description'),
        'category'      => $request->input('category'),
        'type'          => $request->input('type'),
        'status'        => 'open',
        'budget_estimate' => $request->input('budget_estimate') ?: null,
        'currency'      => 'XAF',
        'deadline'      => $request->input('deadline'),
        'location'      => $request->input('location'),
        'eligibility'   => $request->input('eligibility'),
        'contact_email' => $request->input('contact_email'),
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect('/tenders')->with('success', 'Tender published successfully.');
});

Route::post('/tenders/{id}/bid', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $tender = DB::table('tenders')->where('id',$id)->where('status','open')->first();
    if (!$tender) return redirect("/tenders/$id")->with('error','This tender is not accepting bids.');
    $alreadyBid = DB::table('tender_bids')->where('tender_id',$id)->where('company_id',$request->input('company_id'))->exists();
    if ($alreadyBid) return redirect("/tenders/$id")->with('error','Your company has already submitted a bid.');
    DB::table('tender_bids')->insert([
        'id'               => (string)\Illuminate\Support\Str::uuid(),
        'tender_id'        => $id,
        'company_id'       => $request->input('company_id'),
        'submitted_by'     => $authUser['id'],
        'proposal'         => $request->input('proposal'),
        'bid_amount'       => $request->input('bid_amount') ?: null,
        'currency'         => 'XAF',
        'technical_approach' => $request->input('technical_approach'),
        'status'           => 'submitted',
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
    DB::table('tenders')->where('id',$id)->increment('bid_count');
    return redirect("/tenders/$id")->with('success','Your bid has been submitted.');
});

Route::post('/tenders/bids/{id}/shortlist', function ($id) {
    DB::table('tender_bids')->where('id',$id)->update(['status'=>'shortlisted','updated_at'=>now()]);
    return back()->with('success','Bid shortlisted.');
});

Route::post('/tenders/bids/{id}/reject', function ($id) {
    DB::table('tender_bids')->where('id',$id)->update(['status'=>'rejected','updated_at'=>now()]);
    return back()->with('success','Bid rejected.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 3: INVESTMENT MARKETPLACE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/invest-hub', function (Request $request) {
    return view('invest-hub');
});

Route::get('/invest-hub/{id}', function ($id) {
    $seek = DB::table('invest_seeks')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$seek) abort(404);
    $company = DB::table('companies')->where('id',$seek->company_id)->first();
    $authUser = session('auth_user');
    $interests = DB::table('invest_interests')->where('seek_id',$id)->count();
    $myCompanies = $authUser ? DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser['id'])
        ->where('company_users.status','approved')
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name')->get() : collect();
    $alreadyExpressed = $authUser && DB::table('invest_interests')
        ->where('seek_id',$id)->where('investor_user_id',$authUser['id'])->exists();
    $isOwner = $authUser && (string)$seek->company_id === (string)($myCompanies->first()->id??'');
    DB::table('invest_seeks')->where('id',$id)->increment('view_count');
    return view('invest-hub-detail', compact('seek','company','authUser','interests','myCompanies','alreadyExpressed','isOwner'));
});

Route::post('/invest-hub', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    DB::table('invest_seeks')->insert([
        'id'            => (string)\Illuminate\Support\Str::uuid(),
        'company_id'    => $request->input('company_id'),
        'posted_by'     => $authUser['id'],
        'title'         => $request->input('title'),
        'description'   => $request->input('description'),
        'type'          => $request->input('type'),
        'sector'        => $request->input('sector'),
        'amount_sought' => $request->input('amount_sought'),
        'currency'      => 'XAF',
        'equity_offered' => $request->input('equity_offered') ?: null,
        'use_of_funds'  => $request->input('use_of_funds'),
        'traction'      => $request->input('traction'),
        'deadline'      => $request->input('deadline') ?: null,
        'status'        => 'open',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect('/invest-hub')->with('success','Investment opportunity listed.');
});

Route::post('/invest-hub/{id}/express', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    if (DB::table('invest_interests')->where('seek_id',$id)->where('investor_user_id',$authUser['id'])->exists()) {
        return redirect("/invest-hub/$id")->with('error','You have already expressed interest.');
    }
    DB::table('invest_interests')->insert([
        'id'                  => (string)\Illuminate\Support\Str::uuid(),
        'seek_id'             => $id,
        'investor_user_id'    => $authUser['id'],
        'investor_company_id' => $request->input('investor_company_id') ?: null,
        'message'             => $request->input('message'),
        'proposed_amount'     => $request->input('proposed_amount') ?: null,
        'status'              => 'expressed',
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);
    DB::table('invest_seeks')->where('id',$id)->increment('interest_count');
    return redirect("/invest-hub/$id")->with('success','Interest expressed successfully.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 4: SUPPLIER PERFORMANCE CENTER
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/supplier-reviews', function (Request $request) {
    return view('supplier-reviews');
});

Route::post('/supplier-reviews', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $supplierId  = $request->input('supplier_company_id');
    $reviewerId  = $request->input('reviewer_company_id');
    if ($supplierId === $reviewerId) return back()->with('error','You cannot review your own company.');
    if (DB::table('supplier_reviews')->where('supplier_company_id',$supplierId)->where('reviewer_company_id',$reviewerId)->exists()) {
        return back()->with('error','Your company has already reviewed this supplier.');
    }
    DB::table('supplier_reviews')->insert([
        'id'                  => (string)\Illuminate\Support\Str::uuid(),
        'supplier_company_id' => $supplierId,
        'reviewer_company_id' => $reviewerId,
        'reviewer_user_id'    => $authUser['id'],
        'score_delivery'      => (int)$request->input('score_delivery',3),
        'score_quality'       => (int)$request->input('score_quality',3),
        'score_communication' => (int)$request->input('score_communication',3),
        'score_pricing'       => (int)$request->input('score_pricing',3),
        'score_overall'       => (int)$request->input('score_overall',3),
        'review_text'         => $request->input('review_text'),
        'would_recommend'     => $request->has('would_recommend') ? 1 : 0,
        'status'              => 'published',
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);
    // Reputation event
    DB::table('reputation_events')->insert([
        'company_id'   => $supplierId,
        'type'         => 'review_received',
        'points'       => (int)$request->input('score_overall',3) >= 4 ? 5 : 0,
        'description'  => 'Received a supplier review',
        'source_type'  => 'supplier_review',
        'source_id'    => $supplierId,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    return redirect('/supplier-reviews')->with('success','Review submitted.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 5: FEDERATION MODE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/federations', function () {
    return view('federations');
});

Route::get('/federations/{slug}', function ($slug) {
    $fed = DB::table('federations')->where('slug',$slug)->whereNull('deleted_at')->first();
    if (!$fed) abort(404);
    return view('federation', compact('fed'));
});

Route::post('/federations/{slug}/join', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $fed = DB::table('federations')->where('slug',$slug)->first();
    if (!$fed) abort(404);
    $companyId = $request->input('company_id');
    if (DB::table('federation_members')->where('federation_id',$fed->id)->where('company_id',$companyId)->exists()) {
        return redirect("/federations/$slug")->with('error','Your company already has a membership request.');
    }
    DB::table('federation_members')->insert([
        'federation_id' => $fed->id,
        'company_id'    => $companyId,
        'role'          => 'member',
        'status'        => 'pending',
        'invited_by'    => $authUser['id'],
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect("/federations/$slug")->with('success','Membership request submitted. Awaiting federation approval.');
});

Route::post('/federations/{slug}/post', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $fed = DB::table('federations')->where('slug',$slug)->first();
    if (!$fed) abort(404);
    DB::table('federation_posts')->insert([
        'federation_id' => $fed->id,
        'user_id'       => $authUser['id'],
        'company_id'    => $request->input('company_id'),
        'title'         => $request->input('title') ?: null,
        'body'          => $request->input('body'),
        'type'          => $request->input('type','discussion'),
        'is_pinned'     => 0,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect("/federations/$slug")->with('success','Post published.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 6: ESG & SUSTAINABILITY
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/esg', function () {
    return view('esg');
});

Route::get('/esg/submit', function () {
    return view('esg-submit');
});

Route::post('/esg/submit', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $companyId = $request->input('company_id');
    $year      = (int)$request->input('year', date('Y') - 1);
    if (DB::table('esg_reports')->where('company_id',$companyId)->where('year',$year)->exists()) {
        return back()->with('error',"An ESG report for $year already exists for this company.");
    }
    // Compute scores
    $env = 0; $envCount = 0;
    if ($request->input('co2_tonnes') !== null) { $env += 60; $envCount++; }
    if ($request->input('renewable_energy_pct') !== null) { $env += min((float)$request->input('renewable_energy_pct')*1.5, 100); $envCount++; }
    if ($request->input('recycled_pct') !== null) { $env += min((float)$request->input('recycled_pct'), 100); $envCount++; }
    if ($request->input('environmental_initiatives')) { $env += 20; $envCount++; }
    $envScore = $envCount > 0 ? min(round($env / max($envCount,1)), 100) : null;
    $soc = 0; $socCount = 0;
    if ($request->input('female_employees') && $request->input('total_employees')) {
        $femalePct = (float)$request->input('female_employees')/(float)$request->input('total_employees')*100;
        $soc += min($femalePct*2, 50); $socCount++;
    }
    if ($request->has('has_health_insurance')) { $soc += 30; $socCount++; }
    if ($request->input('community_initiatives')) { $soc += 20; $socCount++; }
    $socScore = $socCount > 0 ? min(round($soc / max($socCount,1) * 1.5), 100) : null;
    $gov = 0;
    if ($request->has('has_ethics_policy')) $gov += 25;
    if ($request->has('has_whistleblower_policy')) $gov += 25;
    if ($request->has('has_board_diversity')) $gov += 25;
    if ($request->has('anti_corruption_training')) $gov += 25;
    $govScore = $gov > 0 ? $gov : null;
    $scores = array_filter([$envScore, $socScore, $govScore]);
    $overallScore = count($scores) > 0 ? round(array_sum($scores)/count($scores)) : null;
    DB::table('esg_reports')->insert([
        'id'                      => (string)\Illuminate\Support\Str::uuid(),
        'company_id'              => $companyId,
        'submitted_by'            => $authUser['id'],
        'year'                    => $year,
        'status'                  => 'published',
        'co2_tonnes'              => $request->input('co2_tonnes') ?: null,
        'energy_kwh'              => $request->input('energy_kwh') ?: null,
        'renewable_energy_pct'    => $request->input('renewable_energy_pct') ?: null,
        'water_m3'                => $request->input('water_m3') ?: null,
        'waste_tonnes'            => $request->input('waste_tonnes') ?: null,
        'recycled_pct'            => $request->input('recycled_pct') ?: null,
        'environmental_initiatives' => $request->input('environmental_initiatives'),
        'total_employees'         => $request->input('total_employees') ?: null,
        'female_employees'        => $request->input('female_employees') ?: null,
        'local_employees_pct'     => $request->input('local_employees_pct') ?: null,
        'training_hours_per_employee' => $request->input('training_hours_per_employee') ?: null,
        'safety_incidents'        => $request->input('safety_incidents') !== null ? (int)$request->input('safety_incidents') : null,
        'has_health_insurance'    => $request->has('has_health_insurance') ? 1 : 0,
        'community_initiatives'   => $request->input('community_initiatives'),
        'has_ethics_policy'       => $request->has('has_ethics_policy') ? 1 : 0,
        'has_whistleblower_policy' => $request->has('has_whistleblower_policy') ? 1 : 0,
        'has_board_diversity'     => $request->has('has_board_diversity') ? 1 : 0,
        'anti_corruption_training' => $request->has('anti_corruption_training') ? 1 : 0,
        'governance_notes'        => $request->input('governance_notes'),
        'env_score'               => $envScore,
        'social_score'            => $socScore,
        'governance_score'        => $govScore,
        'overall_esg_score'       => $overallScore,
        'created_at'              => now(),
        'updated_at'              => now(),
    ]);
    return redirect('/esg')->with('success','ESG report submitted and published.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 7: EXPORT HUB
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/export-hub', function () {
    return view('export-hub');
});

Route::get('/export-hub/{slug}', function ($slug) {
    $resource = DB::table('export_resources')->where('slug',$slug)->where('is_published',1)->first();
    if (!$resource) abort(404);
    DB::table('export_resources')->where('id',$resource->id)->increment('view_count');
    $related = DB::table('export_resources')
        ->where('category',$resource->category)
        ->where('slug','!=',$slug)
        ->where('is_published',1)
        ->limit(3)->get();
    return view('export-resource', compact('resource','related'));
});

Route::get('/export-hub/assessment', function () {
    $authUser = requireAuth(request());
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $myCompanies = DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser['id'])
        ->where('company_users.status','approved')
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name')->get();
    return view('export-assessment', compact('authUser','myCompanies'));
});

Route::post('/export-hub/assessment', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $answers  = $request->except(['_token','company_id','product_name','hs_code','target_market']);
    $score    = 0; $maxScore = 0;
    $scoreMap = ['registered'=>10,'has_product'=>10,'has_certifications'=>15,'has_export_docs'=>15,'has_packaging'=>10,'has_insurance'=>10,'has_bank_account'=>5,'knows_hs_code'=>5,'knows_target_market'=>5,'has_export_partner'=>10,'has_logistics'=>5];
    foreach ($scoreMap as $key => $pts) { $maxScore += $pts; if (!empty($answers[$key])) $score += $pts; }
    $pct = $maxScore > 0 ? round($score/$maxScore*100) : 0;
    $level = $pct >= 80 ? 'expert' : ($pct >= 65 ? 'ready' : ($pct >= 45 ? 'almost_ready' : ($pct >= 25 ? 'developing' : 'not_ready')));
    $recommendations = [];
    if (empty($answers['has_certifications'])) $recommendations[] = 'Obtain relevant product certifications (ISO, phytosanitary, halal, organic as applicable)';
    if (empty($answers['knows_hs_code'])) $recommendations[] = 'Identify the correct HS code for your product to determine import duties in target markets';
    if (empty($answers['has_export_docs'])) $recommendations[] = 'Prepare export documentation: Certificate of Origin, Commercial Invoice, Packing List, Bill of Lading';
    if (empty($answers['has_packaging'])) $recommendations[] = 'Ensure product packaging meets destination country labelling requirements';
    if (empty($answers['has_export_partner'])) $recommendations[] = 'Find an experienced export partner or freight forwarder in Cameroon';
    if (empty($answers['has_logistics'])) $recommendations[] = 'Establish reliable logistics arrangements — contact Port of Douala shipping agents';
    $id = (string)\Illuminate\Support\Str::uuid();
    DB::table('export_assessments')->insert([
        'id'              => $id,
        'company_id'      => $request->input('company_id'),
        'user_id'         => $authUser['id'],
        'product_name'    => $request->input('product_name'),
        'hs_code'         => $request->input('hs_code'),
        'target_market'   => $request->input('target_market'),
        'answers'         => json_encode($answers),
        'readiness_score' => $pct,
        'readiness_level' => $level,
        'recommendations' => json_encode($recommendations),
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
    return redirect("/export-hub/assessment/$id");
});

Route::get('/export-hub/assessment/{id}', function ($id) {
    $authUser = requireAuth(request());
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $assessment = DB::table('export_assessments')->where('id',$id)->first();
    if (!$assessment) abort(404);
    $company = DB::table('companies')->where('id',$assessment->company_id)->first();
    return view('export-assessment-result', compact('assessment','company'));
});

// ── API routes for all phases ──────────────────────────────────────────────
Route::prefix('api/v1')->group(function () {

    Route::get('/tenders', function (Request $request) {
        $q = $request->get('q',''); $status = $request->get('status','open');
        $query = DB::table('tenders')->join('companies','tenders.company_id','=','companies.id')
            ->where('tenders.is_public',1)->whereNull('tenders.deleted_at');
        if ($q) $query->where('tenders.title','like',"%$q%");
        if ($status) $query->where('tenders.status',$status);
        $total = (clone $query)->count();
        $items = $query->select('tenders.id','tenders.title','tenders.category','tenders.type','tenders.status','tenders.budget_estimate','tenders.currency','tenders.deadline','companies.name as company_name')
            ->orderBy('tenders.deadline')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/tenders/{id}', function ($id) {
        $t = DB::table('tenders')->where('id',$id)->first();
        if (!$t) return apiError('Not found',404);
        return apiJson(['tender'=>$t]);
    });

    Route::get('/invest-hub', function (Request $request) {
        $query = DB::table('invest_seeks')->join('companies','invest_seeks.company_id','=','companies.id')
            ->where('invest_seeks.status','open')->whereNull('invest_seeks.deleted_at');
        $total = (clone $query)->count();
        $items = $query->select('invest_seeks.id','invest_seeks.title','invest_seeks.type','invest_seeks.sector','invest_seeks.amount_sought','invest_seeks.currency','invest_seeks.equity_offered','invest_seeks.status','companies.name as company_name')
            ->orderByDesc('invest_seeks.created_at')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/federations', function () {
        $feds = DB::table('federations')->where('status','active')->where('is_public',1)->whereNull('deleted_at')
            ->orderByRaw('is_featured DESC')->orderBy('name')->get();
        return apiJson(['total'=>$feds->count(),'items'=>$feds]);
    });

    Route::get('/federations/{slug}', function ($slug) {
        $fed = DB::table('federations')->where('slug',$slug)->first();
        if (!$fed) return apiError('Not found',404);
        $members = DB::table('federation_members')->join('companies','federation_members.company_id','=','companies.id')
            ->where('federation_members.federation_id',$fed->id)->where('federation_members.status','active')
            ->select('companies.id','companies.name','companies.slug','federation_members.role')->get();
        return apiJson(['federation'=>$fed,'members'=>$members,'member_count'=>$members->count()]);
    });

    Route::get('/esg', function () {
        $reports = DB::table('esg_reports')->join('companies','esg_reports.company_id','=','companies.id')
            ->where('esg_reports.status','published')->whereNull('companies.deleted_at')
            ->select('esg_reports.id','esg_reports.year','esg_reports.env_score','esg_reports.social_score','esg_reports.governance_score','esg_reports.overall_esg_score','companies.name as company_name','companies.slug as company_slug')
            ->orderByDesc('overall_esg_score')->get();
        return apiJson(['total'=>$reports->count(),'avg_score'=>round($reports->avg('overall_esg_score')),'reports'=>$reports]);
    });

    Route::get('/export-resources', function () {
        $resources = DB::table('export_resources')->where('is_published',1)->orderByRaw('is_featured DESC')->orderBy('title')->get();
        return apiJson(['total'=>$resources->count(),'items'=>$resources]);
    });

    Route::get('/supplier-reviews', function (Request $request) {
        $supplierId = $request->get('supplier_id','');
        $query = DB::table('supplier_reviews')->where('status','published');
        if ($supplierId) $query->where('supplier_company_id',$supplierId);
        $reviews = $query->orderByDesc('created_at')->limit(50)->get();
        return apiJson(['total'=>$reviews->count(),'reviews'=>$reviews]);
    });

});
