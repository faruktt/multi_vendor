<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserActivityController extends Controller
{
    /**
     * Display user activity and page hit analytics.
     */
    public function index(Request $request)
    {
        // 1. Resolve date range filter
        $preset = $request->get('preset', 'all_time');
        [$from, $to] = $this->resolveDateRange($request, $preset);

        $baseQuery = UserActivity::query();

        if ($from && $to) {
            $baseQuery->whereBetween('created_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ]);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $baseQuery->where(function ($q) use ($search) {
                $q->where('path', 'like', "%{$search}%")
                  ->orWhere('page_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('referer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('device')) {
            $baseQuery->where('device', $request->device);
        }

        if ($request->filled('user_type')) {
            $baseQuery->where('user_type', $request->user_type);
        }

        // 2. High-level metric stats
        $totalAllTimeHits       = UserActivity::count();
        $totalTodayHits         = UserActivity::whereDate('created_at', today())->count();
        $totalAllTimeVisitors   = UserActivity::distinct('ip_address')->count('ip_address');
        $todayVisitors          = UserActivity::whereDate('created_at', today())->distinct('ip_address')->count('ip_address');

        // Filtered counts
        $filteredHits           = (clone $baseQuery)->count();
        $filteredUniqueVisitors = (clone $baseQuery)->distinct('ip_address')->count('ip_address');

        // 3. Page Hits Breakdown (Which page got how many hits)
        $pageBreakdownQuery = (clone $baseQuery)
            ->select(
                'path',
                'page_name',
                DB::raw('count(*) as total_hits'),
                DB::raw('count(distinct ip_address) as unique_hits'),
                DB::raw('max(created_at) as last_visited_at')
            )
            ->groupBy('path', 'page_name')
            ->orderByDesc('total_hits');

        $pageBreakdown = $pageBreakdownQuery->paginate(15, ['*'], 'pages_page')->withQueryString();

        // 4. Live Recent Visits Feed
        $recentVisits = (clone $baseQuery)->latest()->paginate(25, ['*'], 'visits_page')->withQueryString();

        // 5. Device distribution
        $deviceStats = (clone $baseQuery)
            ->select('device', DB::raw('count(*) as count'))
            ->groupBy('device')
            ->pluck('count', 'device')
            ->toArray();

        // 6. User Type distribution
        $userTypeStats = (clone $baseQuery)
            ->select('user_type', DB::raw('count(*) as count'))
            ->groupBy('user_type')
            ->pluck('count', 'user_type')
            ->toArray();

        // 7. Last 7 Days Traffic Trend (Hits & Unique visitors)
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        $dailyTrend = UserActivity::where('created_at', '>=', $sevenDaysAgo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as hits'),
                DB::raw('count(distinct ip_address) as visitors')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trendDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->toDateString();
            $trendDays[] = [
                'date'     => $d,
                'label'    => Carbon::parse($d)->format('D, d M'),
                'hits'     => $dailyTrend[$d]->hits ?? 0,
                'visitors' => $dailyTrend[$d]->visitors ?? 0,
            ];
        }

        return view('admin.user-activity.index', compact(
            'totalAllTimeHits',
            'totalTodayHits',
            'totalAllTimeVisitors',
            'todayVisitors',
            'filteredHits',
            'filteredUniqueVisitors',
            'pageBreakdown',
            'recentVisits',
            'deviceStats',
            'userTypeStats',
            'trendDays',
            'preset',
            'from',
            'to'
        ));
    }

    /**
     * Clear all user activity logs (super-admin only).
     */
    public function clear(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403);
        UserActivity::truncate();
        return back()->with('success', 'User activity logs cleared successfully.');
    }

    private function resolveDateRange(Request $request, string $preset): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [$request->from, $request->to];
        }

        $now = Carbon::now();
        return match ($preset) {
            'today'       => [today()->toDateString(), today()->toDateString()],
            'yesterday'   => [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'last_7_days' => [$now->copy()->subDays(6)->toDateString(), $now->toDateString()],
            'this_month'  => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'last_month'  => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'all_time'    => [null, null],
            default       => [null, null],
        };
    }
}
