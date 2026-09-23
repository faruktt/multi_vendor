<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorWorkSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ModeratorWorkController extends Controller
{
    /**
     * Start a new work shift / session
     */
    public function startWork(Request $request)
    {
        $moderator = auth('moderator')->user();

        // Check if there is already an active session
        $existing = $moderator->activeSession();
        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'একটি শিফট ইতিমধ্যে চলমান রয়েছে।',
                    'session' => $existing,
                ], 422);
            }
            return back()->with('error', 'আপনার একটি শিফট ইতিমধ্যে চলমান রয়েছে।');
        }

        $session = ModeratorWorkSession::create([
            'moderator_id'     => $moderator->id,
            'started_at'       => Carbon::now(),
            'status'           => 'in_progress',
            'duration_seconds' => 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'কাজ শুরু হয়েছে! টাইমার চালু হয়েছে।',
                'session_id' => $session->id,
                'started_at' => $session->started_at->toIso8601String(),
            ]);
        }

        return back()->with('success', 'কাজ শুরু হয়েছে! টাইমার চালু হয়েছে।');
    }

    /**
     * Stop active work shift immediately (freezes countdown, calculates earnings)
     * Then work description input field/modal opens for report entry
     */
    public function stopWork(Request $request)
    {
        $moderator = auth('moderator')->user();

        $session = $moderator->activeSession();
        if (!$session) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'কোনো চলমান শিফট পাওয়া যায়নি।',
                ], 422);
            }
            return back()->with('error', 'কোনো চলমান শিফট পাওয়া যায়নি।');
        }

        $now = Carbon::now();
        $durationSeconds = max(1, abs($now->timestamp - $session->started_at->timestamp));
        $ratePerMinute = (float) ($moderator->rate_per_minute ?? 0);
        $earnedAmount = round(($durationSeconds / 60) * $ratePerMinute, 2);

        $session->update([
            'ended_at'         => $now,
            'duration_seconds' => $durationSeconds,
            'rate_per_minute'  => $ratePerMinute,
            'earned_amount'    => $earnedAmount,
            'status'           => 'completed',
        ]);

        $formattedTime = $session->formattedDuration();
        $compactTime   = $session->compactDuration();
        $minutesWorked = round($durationSeconds / 60, 1);

        if ($request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'message'          => "কাজ স্টপ হয়েছে! মোট সময়: {$formattedTime}। এখন কাজের বিবরণ ও রিপোর্ট লিখুন।",
                'session_id'       => $session->id,
                'duration_seconds' => $durationSeconds,
                'minutes_worked'   => $minutesWorked,
                'formatted_time'   => $formattedTime,
                'compact_time'     => $compactTime,
                'rate_per_minute'  => $ratePerMinute,
                'earned_amount'    => $earnedAmount,
                'tasks_summary'    => $session->tasks_summary ?? '',
                'work_report'      => $session->work_report ?? '',
            ]);
        }

        return redirect()->route('moderator.dashboard')
            ->with('success', "কাজ স্টপ হয়েছে! মোট সময়: {$formattedTime}। অর্জিত আয়: ৳{$earnedAmount}। কাজের বিবরণ রিপোর্ট সংরক্ষণ করুন।");
    }

    /**
     * End active work shift and submit work report
     */
    public function endWork(Request $request)
    {
        $moderator = auth('moderator')->user();

        $session = $moderator->activeSession();
        if (!$session) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'কোনো চলমান শিফট পাওয়া যায়নি।',
                ], 422);
            }
            return back()->with('error', 'কোনো চলমান শিফট পাওয়া যায়নি।');
        }

        $validated = $request->validate([
            'tasks_summary' => 'nullable|string|max:255',
            'work_report'   => 'nullable|string|max:10000',
        ]);

        $now = Carbon::now();
        $durationSeconds = max(1, abs($now->timestamp - $session->started_at->timestamp));
        $ratePerMinute = (float) ($moderator->rate_per_minute ?? 0);
        $earnedAmount = round(($durationSeconds / 60) * $ratePerMinute, 2);

        $session->update([
            'ended_at'            => $now,
            'duration_seconds'    => $durationSeconds,
            'rate_per_minute'     => $ratePerMinute,
            'earned_amount'       => $earnedAmount,
            'status'              => 'completed',
            'tasks_summary'       => $validated['tasks_summary'] ?? null,
            'work_report'         => $validated['work_report'] ?? null,
            'report_submitted_at' => $now,
        ]);

        $formattedTime = $session->formattedDuration();

        if ($request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'message'          => "কাজ সমাপ্ত হয়েছে! আপনি {$formattedTime} কাজ করেছেন এবং রিপোর্ট জমা হয়েছে। অর্জিত আয়: ৳{$earnedAmount}",
                'duration_seconds' => $durationSeconds,
                'formatted_time'   => $formattedTime,
                'earned_amount'    => $earnedAmount,
                'session'          => $session,
            ]);
        }

        return redirect()->route('moderator.dashboard')
            ->with('success', "কাজ সফলভাবে শেষ হয়েছে! মোট সময়: {$formattedTime}। অর্জিত আয়: ৳{$earnedAmount}। কাজের রিপোর্ট সংরক্ষিত হয়েছে।");
    }

    /**
     * Add activity log entry during active shift without stopping work
     */
    public function logActivity(Request $request)
    {
        $moderator = auth('moderator')->user();
        $session = $moderator->activeSession();

        if (!$session) {
            return back()->with('error', 'কোনো শিফট চালু নেই। রিপোর্ট লেখার পূর্বে কাজ শুরু করুন।');
        }

        $validated = $request->validate([
            'activity' => 'required|string|max:1000',
        ]);

        $timeStr = Carbon::now()->format('h:i A');
        $note = trim($validated['activity']);

        // 1. Create entry in moderator_work_logs
        \App\Models\ModeratorWorkLog::create([
            'work_session_id' => $session->id,
            'moderator_id'    => $moderator->id,
            'log_time'        => Carbon::now(),
            'activity'        => $note,
        ]);

        // 2. Automatically append to work_report on session
        $currentReport = trim((string)$session->work_report);
        $entryLine = "• [{$timeStr}] {$note}";
        $newReport = $currentReport ? "{$currentReport}\n{$entryLine}" : $entryLine;

        $session->update([
            'work_report' => $newReport,
        ]);

        return back()->with('success', "কাজের আপডেট যুক্ত হয়েছে: {$note}");
    }

    /**
     * Delete an activity log
     */
    public function deleteActivityLog(\App\Models\ModeratorWorkLog $log)
    {
        $moderator = auth('moderator')->user();
        if ($log->moderator_id !== $moderator->id) {
            abort(403);
        }

        $sessionId = $log->work_session_id;
        $log->delete();

        // Rebuild session work_report
        $session = ModeratorWorkSession::find($sessionId);
        if ($session) {
            $remaining = $session->logs()->orderBy('log_time')->get();
            $lines = [];
            foreach ($remaining as $item) {
                $lines[] = "• [" . $item->log_time->format('h:i A') . "] " . $item->activity;
            }
            $session->update([
                'work_report' => !empty($lines) ? implode("\n", $lines) : null,
            ]);
        }

        return back()->with('success', 'আপডেটটি মুছে ফেলা হয়েছে।');
    }

    /**
     * View history of work shifts & reports
     */
    public function myReports(Request $request)
    {
        $moderator = auth('moderator')->user();

        $query = $moderator->workSessions()->latest('started_at');

        if ($request->filled('from')) {
            $query->whereDate('started_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('started_at', '<=', $request->to);
        }

        $sessions = $query->paginate(15)->withQueryString();

        $totalSeconds = (int) $moderator->workSessions()
            ->where('status', 'completed')
            ->when($request->filled('from'), fn($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('started_at', '<=', $request->to))
            ->sum('duration_seconds');

        return view('moderator.reports.index', compact('moderator', 'sessions', 'totalSeconds'));
    }

    /**
     * Update / edit an existing session's report
     */
    public function updateReport(Request $request, ModeratorWorkSession $session)
    {
        $moderator = auth('moderator')->user();

        if ($session->moderator_id !== $moderator->id) {
            abort(403);
        }

        $validated = $request->validate([
            'tasks_summary' => 'nullable|string|max:255',
            'work_report'   => 'nullable|string|max:10000',
        ]);

        $session->update([
            'tasks_summary'       => $validated['tasks_summary'] ?? $session->tasks_summary,
            'work_report'         => $validated['work_report'] ?? $session->work_report,
            'report_submitted_at' => Carbon::now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'কাজের রিপোর্ট সফলভাবে সংরক্ষিত হয়েছে!',
                'session' => $session,
            ]);
        }

        return back()->with('success', 'কাজের রিপোর্ট সফলভাবে আপডেট হয়েছে!');
    }
}
