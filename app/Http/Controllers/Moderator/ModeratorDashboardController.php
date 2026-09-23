<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ModeratorDashboardController extends Controller
{
    /**
     * Show the moderator work dashboard
     */
    public function index()
    {
        $moderator = auth('moderator')->user();

        // Active running session
        $activeSession = $moderator->activeSession();

        // Calculate Today's worked seconds
        $todayStart = Carbon::today();
        $todaySeconds = (int) $moderator->workSessions()
            ->where('status', 'completed')
            ->whereDate('started_at', $todayStart)
            ->sum('duration_seconds');

        // Calculate This Week's worked seconds
        $weekStart = Carbon::now()->startOfWeek();
        $weekSeconds = (int) $moderator->workSessions()
            ->where('status', 'completed')
            ->where('started_at', '>=', $weekStart)
            ->sum('duration_seconds');

        // Total completed sessions
        $totalSessions = $moderator->workSessions()->where('status', 'completed')->count();

        // Total reports submitted
        $totalReports = $moderator->workSessions()
            ->where('status', 'completed')
            ->whereNotNull('work_report')
            ->count();

        // Recent work sessions
        $recentSessions = $moderator->workSessions()
            ->latest('started_at')
            ->take(10)
            ->get();

        // Real-time activity logs for current session
        $activeLogs = $activeSession ? $activeSession->logs()->orderBy('log_time', 'desc')->get() : collect();

        // Financial & rate metrics
        $ratePerMinute    = (float) ($moderator->rate_per_minute ?? 0);
        $todayEarned      = round(($todaySeconds / 60) * $ratePerMinute, 2);
        $totalEarned      = $moderator->totalEarnedAmount();
        $totalWithdrawn   = $moderator->totalWithdrawnAmount();
        $availableBalance = $moderator->availableBalance();

        // Check if there is an uncompleted report on the most recent completed session
        $latestCompleted = $moderator->workSessions()
            ->where('status', 'completed')
            ->latest('ended_at')
            ->first();
        $needsReportSession = ($latestCompleted && empty($latestCompleted->tasks_summary) && empty($latestCompleted->work_report)) ? $latestCompleted : null;

        return view('moderator.dashboard', compact(
            'moderator',
            'activeSession',
            'activeLogs',
            'todaySeconds',
            'weekSeconds',
            'totalSessions',
            'totalReports',
            'recentSessions',
            'ratePerMinute',
            'todayEarned',
            'totalEarned',
            'totalWithdrawn',
            'availableBalance',
            'needsReportSession'
        ));
    }
}
