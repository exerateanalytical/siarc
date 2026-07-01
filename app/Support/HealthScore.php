<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Collaboration Health Score
 *
 * Computes a 0-100 health score for a company by aggregating its activity
 * across every collaboration module on the platform. Five weighted pillars:
 *   Network (20%) · Activity (25%) · Reputation (25%) · Sustainability (15%) · Engagement (15%)
 */
class HealthScore
{
    /** Compute the live score + signal breakdown for a company (no persistence). */
    public static function compute(string $companyId): array
    {
        // ── NETWORK: breadth of collaborative relationships ───────────────
        $collabs = DB::table('collabcam_collaboration_members')
            ->where('company_id', $companyId)->where('status', 'active')->count();
        $feds = DB::table('federation_members')
            ->where('company_id', $companyId)->where('status', 'active')->count();
        $requests = DB::table('collabcam_requests')
            ->where(function ($q) use ($companyId) {
                $q->where('from_company_id', $companyId)->orWhere('to_company_id', $companyId);
            })
            ->whereIn('status', ['accepted', 'active', 'completed'])->count();
        $network = min(100, $collabs * 20 + $feds * 15 + $requests * 10);

        // ── ACTIVITY: output across modules ───────────────────────────────
        $tenders   = DB::table('tenders')->where('company_id', $companyId)->whereNull('deleted_at')->count();
        $bids      = DB::table('tender_bids')->where('company_id', $companyId)->count();
        $innov     = DB::table('innovation_projects')->where('company_id', $companyId)->count();
        $innovPart = DB::table('innovation_participants')->where('company_id', $companyId)->count();
        $opps      = DB::table('collabcam_opportunities')->where('company_id', $companyId)->whereNull('deleted_at')->count();
        $invest    = DB::table('invest_seeks')->where('company_id', $companyId)->count();
        $events    = DB::table('events')->where('organizer_company_id', $companyId)->count();
        $assets    = DB::table('shared_assets')->where('company_id', $companyId)->count();
        $logi      = DB::table('logistics_listings')->where('company_id', $companyId)->count();
        $activity  = min(100, ($tenders + $opps + $innov + $events + $invest + $assets + $logi) * 12 + ($bids + $innovPart) * 8);

        // ── REPUTATION: reviews, reputation points, verification ──────────
        $reviewsQ  = DB::table('supplier_reviews')->where('supplier_company_id', $companyId)->where('status', 'published');
        $avgReview = (clone $reviewsQ)->avg('score_overall');
        $revCount  = (clone $reviewsQ)->count();
        $repPoints = (int) DB::table('reputation_events')->where('company_id', $companyId)->sum('points');
        $verified  = DB::table('companies')->where('id', $companyId)->value('verification_status') === 'verified';
        $repScore  = $avgReview ? ($avgReview / 5) * 50 : 20;   // reviews up to 50 (20 baseline)
        $repScore += $verified ? 25 : 0;                         // verification +25
        $repScore += min(25, $repPoints / 4);                   // reputation points up to 25
        $reputation = (int) min(100, round($repScore));

        // ── SUSTAINABILITY & COMPLIANCE ───────────────────────────────────
        $esg = DB::table('esg_reports')->where('company_id', $companyId)
            ->whereIn('status', ['published', 'verified'])->orderByDesc('year')->value('overall_esg_score');
        $trkTotal = DB::table('compliance_tracker')->where('company_id', $companyId)->count();
        $trkOk    = DB::table('compliance_tracker')->where('company_id', $companyId)->where('status', 'compliant')->count();
        $susScore = $esg !== null ? ($esg / 100) * 60 : 25;     // ESG up to 60 (25 baseline)
        $susScore += $trkTotal > 0 ? ($trkOk / $trkTotal) * 40 : 15; // compliance up to 40 (15 baseline)
        $sustainability = (int) min(100, round($susScore));

        // ── ENGAGEMENT: recency of activity ───────────────────────────────
        $dates = [];
        foreach ([
            ['tenders', 'company_id'], ['collabcam_opportunities', 'company_id'],
            ['innovation_projects', 'company_id'], ['events', 'organizer_company_id'],
            ['shared_assets', 'company_id'], ['logistics_listings', 'company_id'],
        ] as [$tbl, $fk]) {
            $d = DB::table($tbl)->where($fk, $companyId)->max('created_at');
            if ($d) $dates[] = strtotime($d);
        }
        if ($dates) {
            $daysAgo = (time() - max($dates)) / 86400;
            $engagement = $daysAgo <= 7 ? 100 : ($daysAgo <= 30 ? 85 : ($daysAgo <= 90 ? 65 : ($daysAgo <= 180 ? 45 : 25)));
        } else {
            $engagement = 10;
        }

        // ── OVERALL (weighted) ────────────────────────────────────────────
        $overall = (int) round($network * 0.20 + $activity * 0.25 + $reputation * 0.25 + $sustainability * 0.15 + $engagement * 0.15);
        $grade = $overall >= 85 ? 'A' : ($overall >= 70 ? 'B' : ($overall >= 55 ? 'C' : ($overall >= 40 ? 'D' : 'E')));

        return [
            'network' => $network, 'activity' => $activity, 'reputation' => $reputation,
            'sustainability' => $sustainability, 'engagement' => $engagement,
            'overall' => $overall, 'grade' => $grade,
            'signals' => [
                'collabs' => $collabs, 'feds' => $feds, 'requests' => $requests,
                'tenders' => $tenders, 'bids' => $bids, 'innovation' => $innov + $innovPart,
                'opportunities' => $opps, 'reviews' => $revCount, 'avg_review' => $avgReview ? round($avgReview, 1) : null,
                'reputation_points' => $repPoints, 'verified' => $verified, 'esg' => $esg,
                'compliance_total' => $trkTotal, 'compliance_ok' => $trkOk,
            ],
        ];
    }

    /** Compute and persist the score into company_health_scores. Returns the breakdown. */
    public static function store(string $companyId): array
    {
        $s = self::compute($companyId);
        $data = [
            'network_score'        => $s['network'],
            'activity_score'       => $s['activity'],
            'reputation_score'     => $s['reputation'],
            'sustainability_score' => $s['sustainability'],
            'engagement_score'     => $s['engagement'],
            'overall_score'        => $s['overall'],
            'grade'                => $s['grade'],
            'computed_at'          => now(),
            'updated_at'           => now(),
        ];
        if (DB::table('company_health_scores')->where('company_id', $companyId)->exists()) {
            DB::table('company_health_scores')->where('company_id', $companyId)->update($data);
        } else {
            $data['company_id'] = $companyId;
            $data['created_at'] = now();
            DB::table('company_health_scores')->insert($data);
        }
        return $s;
    }
}
