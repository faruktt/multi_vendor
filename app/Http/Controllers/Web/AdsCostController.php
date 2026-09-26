<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdsCost;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdsCostController extends Controller
{
    public function index(Request $request)
    {
        $query = AdsCost::with('createdBy')->latest('cost_date')->latest('id');

        // Platform filter
        if ($request->filled('platform') && $request->platform !== 'all') {
            $query->where('platform', $request->platform);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('campaign_name', 'like', "%{$search}%")
                  ->orWhere('ad_account', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('target_url', 'like', "%{$search}%");
            });
        }

        // Date range / Period filter
        $period = $request->get('period', 'all');
        $fromDate = $request->get('from_date');
        $toDate   = $request->get('to_date');

        if ($period === 'today') {
            $query->whereDate('cost_date', Carbon::today());
        } elseif ($period === 'yesterday') {
            $query->whereDate('cost_date', Carbon::yesterday());
        } elseif ($period === 'this_week') {
            $query->whereBetween('cost_date', [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()]);
        } elseif ($period === 'this_month') {
            $query->whereMonth('cost_date', Carbon::now()->month)->whereYear('cost_date', Carbon::now()->year);
        } elseif ($period === 'last_month') {
            $lastMonth = Carbon::now()->subMonth();
            $query->whereMonth('cost_date', $lastMonth->month)->whereYear('cost_date', $lastMonth->year);
        } elseif ($period === 'this_year') {
            $query->whereYear('cost_date', Carbon::now()->year);
        } elseif ($fromDate || $toDate) {
            if ($fromDate) {
                $query->whereDate('cost_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('cost_date', '<=', $toDate);
            }
        }

        // Filtered summary statistics
        $statsQuery = clone $query;
        $totalCost = (float) $statsQuery->sum('amount');
        $totalImpressions = (int) $statsQuery->sum('impressions');
        $totalClicks = (int) $statsQuery->sum('clicks');
        $totalConversions = (int) $statsQuery->sum('conversions');
        $avgCpa = $totalConversions > 0 ? round($totalCost / $totalConversions, 2) : 0;
        $avgCpc = $totalClicks > 0 ? round($totalCost / $totalClicks, 2) : 0;

        // Quick aggregate KPI cards
        $todayCost = (float) AdsCost::whereDate('cost_date', Carbon::today())->sum('amount');
        $thisMonthCost = (float) AdsCost::whereMonth('cost_date', Carbon::now()->month)
            ->whereYear('cost_date', Carbon::now()->year)
            ->sum('amount');
        $allTimeCost = (float) AdsCost::sum('amount');
        $allTimeConversions = (int) AdsCost::sum('conversions');

        // Platform breakdown
        $platformsList = ['facebook', 'google', 'tiktok', 'instagram', 'youtube', 'snapchat', 'other'];
        $platformBreakdown = AdsCost::selectRaw('platform, SUM(amount) as total_amount, COUNT(*) as count, SUM(conversions) as total_conversions')
            ->groupBy('platform')
            ->get()
            ->keyBy('platform');

        $records = $query->paginate(20)->withQueryString();

        return view('admin.ads-cost.index', compact(
            'records',
            'totalCost',
            'totalImpressions',
            'totalClicks',
            'totalConversions',
            'avgCpa',
            'avgCpc',
            'todayCost',
            'thisMonthCost',
            'allTimeCost',
            'allTimeConversions',
            'platformBreakdown',
            'platformsList',
            'period',
            'fromDate',
            'toDate'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform'      => 'required|string|in:facebook,google,tiktok,instagram,youtube,snapchat,other',
            'campaign_name' => 'required|string|max:255',
            'ad_account'    => 'nullable|string|max:255',
            'cost_date'     => 'required|date',
            'amount'        => 'required|numeric|min:0.01',
            'currency'      => 'nullable|string|max:10',
            'amount_usd'    => 'nullable|numeric|min:0',
            'impressions'   => 'nullable|integer|min:0',
            'clicks'        => 'nullable|integer|min:0',
            'conversions'   => 'nullable|integer|min:0',
            'target_url'    => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $validated['currency'] = $request->input('currency') ?: 'BDT';
        $validated['impressions'] = (int) $request->input('impressions', 0);
        $validated['clicks'] = (int) $request->input('clicks', 0);
        $validated['conversions'] = (int) $request->input('conversions', 0);
        $validated['amount_usd'] = $request->filled('amount_usd') ? (float) $request->input('amount_usd') : null;
        $validated['created_by'] = auth()->id();

        $adsCost = AdsCost::create($validated);

        ActivityLogger::log('created', $adsCost, "Added ads cost: {$adsCost->campaign_name} ({$adsCost->platform_label}) ৳" . number_format($adsCost->amount, 2));

        return back()->with('success', 'Ads cost entry recorded successfully.');
    }

    public function update(Request $request, AdsCost $adsCost)
    {
        $validated = $request->validate([
            'platform'      => 'required|string|in:facebook,google,tiktok,instagram,youtube,snapchat,other',
            'campaign_name' => 'required|string|max:255',
            'ad_account'    => 'nullable|string|max:255',
            'cost_date'     => 'required|date',
            'amount'        => 'required|numeric|min:0.01',
            'currency'      => 'nullable|string|max:10',
            'amount_usd'    => 'nullable|numeric|min:0',
            'impressions'   => 'nullable|integer|min:0',
            'clicks'        => 'nullable|integer|min:0',
            'conversions'   => 'nullable|integer|min:0',
            'target_url'    => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $validated['currency'] = $request->input('currency') ?: 'BDT';
        $validated['impressions'] = (int) $request->input('impressions', 0);
        $validated['clicks'] = (int) $request->input('clicks', 0);
        $validated['conversions'] = (int) $request->input('conversions', 0);
        $validated['amount_usd'] = $request->filled('amount_usd') ? (float) $request->input('amount_usd') : null;

        $adsCost->update($validated);

        ActivityLogger::log('updated', $adsCost, "Updated ads cost: {$adsCost->campaign_name} ({$adsCost->platform_label})");

        return back()->with('success', 'Ads cost updated successfully.');
    }

    public function destroy(AdsCost $adsCost)
    {
        $name = $adsCost->campaign_name;
        $platform = $adsCost->platform_label;
        $amount = $adsCost->amount;

        ActivityLogger::log('deleted', $adsCost, "Deleted ads cost: {$name} ({$platform}) ৳" . number_format($amount, 2));

        $adsCost->delete();

        return back()->with('success', 'Ads cost record deleted successfully.');
    }
}
