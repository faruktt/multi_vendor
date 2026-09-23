<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Moderator;
use App\Models\ModeratorWorkSession;
use App\Models\ModeratorWithdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ModeratorManagementController extends Controller
{
    /**
     * Display all moderators
     */
    public function index(Request $request)
    {
        $query = Moderator::query()->withCount([
            'workSessions as total_sessions' => fn($q) => $q->where('status', 'completed'),
        ])->with(['workSessions' => function ($q) {
            $q->where('status', 'in_progress')->latest('started_at');
        }]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $moderators = $query->latest()->paginate(15)->withQueryString();

        // Summary counts
        $totalCount         = Moderator::count();
        $activeCount        = Moderator::where('status', 'active')->count();
        $workingNow         = ModeratorWorkSession::where('status', 'in_progress')->distinct('moderator_id')->count('moderator_id');
        $pendingWithdrawals = ModeratorWithdrawal::where('status', 'pending')->count();

        return view('admin.moderators.index', compact('moderators', 'totalCount', 'activeCount', 'workingNow', 'pendingWithdrawals'));
    }

    /**
     * Store a new moderator
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|string|email|max:255|unique:moderators,email',
            'password'            => 'required|string|min:6',
            'phone'               => 'nullable|string|max:30',
            'address'             => 'nullable|string|max:500',
            'rate_per_minute'     => 'nullable|numeric|min:0',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'nid_front'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'nid_back'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'guardian_nid_front'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'guardian_nid_back'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'status'              => 'nullable|in:active,inactive',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('moderators', 'uploads');
        }

        $nidFrontPath = null;
        if ($request->hasFile('nid_front')) {
            $nidFrontPath = $request->file('nid_front')->store('moderator_docs', 'uploads');
        }

        $nidBackPath = null;
        if ($request->hasFile('nid_back')) {
            $nidBackPath = $request->file('nid_back')->store('moderator_docs', 'uploads');
        }

        $guardianNidFrontPath = null;
        if ($request->hasFile('guardian_nid_front')) {
            $guardianNidFrontPath = $request->file('guardian_nid_front')->store('moderator_docs', 'uploads');
        }

        $guardianNidBackPath = null;
        if ($request->hasFile('guardian_nid_back')) {
            $guardianNidBackPath = $request->file('guardian_nid_back')->store('moderator_docs', 'uploads');
        }

        Moderator::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'phone'              => $validated['phone'] ?? null,
            'address'            => $validated['address'] ?? null,
            'rate_per_minute'    => $validated['rate_per_minute'] ?? 0.00,
            'image'              => $imagePath,
            'nid_front'          => $nidFrontPath,
            'nid_back'           => $nidBackPath,
            'guardian_nid_front' => $guardianNidFrontPath,
            'guardian_nid_back'  => $guardianNidBackPath,
            'status'             => $validated['status'] ?? 'active',
        ]);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'নতুন মডারেটর সফলভাবে তৈরি করা হয়েছে!');
    }

    /**
     * Update existing moderator details
     */
    public function update(Request $request, Moderator $moderator)
    {
        $validated = $request->validate([
            'name'                      => 'required|string|max:255',
            'email'                     => 'required|string|email|max:255|unique:moderators,email,' . $moderator->id,
            'password'                  => 'nullable|string|min:6',
            'phone'                     => 'nullable|string|max:30',
            'address'                   => 'nullable|string|max:500',
            'rate_per_minute'           => 'nullable|numeric|min:0',
            'image'                     => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'remove_image'              => 'nullable|boolean',
            'nid_front'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_nid_front'          => 'nullable|boolean',
            'nid_back'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_nid_back'           => 'nullable|boolean',
            'guardian_nid_front'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_guardian_nid_front' => 'nullable|boolean',
            'guardian_nid_back'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_guardian_nid_back'  => 'nullable|boolean',
            'status'                    => 'nullable|in:active,inactive',
        ]);

        $updateData = [
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'] ?? null,
            'address'         => $validated['address'] ?? null,
            'rate_per_minute' => $validated['rate_per_minute'] ?? $moderator->rate_per_minute ?? 0.00,
            'status'          => $validated['status'] ?? $moderator->status,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Profile Image
        if ($request->boolean('remove_image')) {
            if ($moderator->image && Storage::disk('uploads')->exists($moderator->image)) {
                Storage::disk('uploads')->delete($moderator->image);
            }
            $updateData['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($moderator->image && Storage::disk('uploads')->exists($moderator->image)) {
                Storage::disk('uploads')->delete($moderator->image);
            }
            $updateData['image'] = $request->file('image')->store('moderators', 'uploads');
        }

        // Moderator NID Front
        if ($request->boolean('remove_nid_front')) {
            if ($moderator->nid_front && Storage::disk('uploads')->exists($moderator->nid_front)) {
                Storage::disk('uploads')->delete($moderator->nid_front);
            }
            $updateData['nid_front'] = null;
        }
        if ($request->hasFile('nid_front')) {
            if ($moderator->nid_front && Storage::disk('uploads')->exists($moderator->nid_front)) {
                Storage::disk('uploads')->delete($moderator->nid_front);
            }
            $updateData['nid_front'] = $request->file('nid_front')->store('moderator_docs', 'uploads');
        }

        // Moderator NID Back
        if ($request->boolean('remove_nid_back')) {
            if ($moderator->nid_back && Storage::disk('uploads')->exists($moderator->nid_back)) {
                Storage::disk('uploads')->delete($moderator->nid_back);
            }
            $updateData['nid_back'] = null;
        }
        if ($request->hasFile('nid_back')) {
            if ($moderator->nid_back && Storage::disk('uploads')->exists($moderator->nid_back)) {
                Storage::disk('uploads')->delete($moderator->nid_back);
            }
            $updateData['nid_back'] = $request->file('nid_back')->store('moderator_docs', 'uploads');
        }

        // Guardian NID Front
        if ($request->boolean('remove_guardian_nid_front')) {
            if ($moderator->guardian_nid_front && Storage::disk('uploads')->exists($moderator->guardian_nid_front)) {
                Storage::disk('uploads')->delete($moderator->guardian_nid_front);
            }
            $updateData['guardian_nid_front'] = null;
        }
        if ($request->hasFile('guardian_nid_front')) {
            if ($moderator->guardian_nid_front && Storage::disk('uploads')->exists($moderator->guardian_nid_front)) {
                Storage::disk('uploads')->delete($moderator->guardian_nid_front);
            }
            $updateData['guardian_nid_front'] = $request->file('guardian_nid_front')->store('moderator_docs', 'uploads');
        }

        // Guardian NID Back
        if ($request->boolean('remove_guardian_nid_back')) {
            if ($moderator->guardian_nid_back && Storage::disk('uploads')->exists($moderator->guardian_nid_back)) {
                Storage::disk('uploads')->delete($moderator->guardian_nid_back);
            }
            $updateData['guardian_nid_back'] = null;
        }
        if ($request->hasFile('guardian_nid_back')) {
            if ($moderator->guardian_nid_back && Storage::disk('uploads')->exists($moderator->guardian_nid_back)) {
                Storage::disk('uploads')->delete($moderator->guardian_nid_back);
            }
            $updateData['guardian_nid_back'] = $request->file('guardian_nid_back')->store('moderator_docs', 'uploads');
        }

        $moderator->update($updateData);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'মডারেটর তথ্য সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(Moderator $moderator)
    {
        $newStatus = $moderator->status === 'active' ? 'inactive' : 'active';
        $moderator->update(['status' => $newStatus]);

        // If deactivated while on duty, end active session
        if ($newStatus === 'inactive') {
            $activeSession = $moderator->activeSession();
            if ($activeSession) {
                $now = Carbon::now();
                $activeSession->update([
                    'ended_at'         => $now,
                    'duration_seconds' => max(1, abs($now->timestamp - $activeSession->started_at->timestamp)),
                    'status'           => 'completed',
                    'tasks_summary'    => 'Auto ended upon account deactivation',
                ]);
            }
        }

        return back()->with('success', "মডারেটরের স্ট্যাটাস {$newStatus} করা হয়েছে!");
    }

    /**
     * Delete moderator
     */
    public function destroy(Moderator $moderator)
    {
        if ($moderator->image && Storage::disk('uploads')->exists($moderator->image)) {
            Storage::disk('uploads')->delete($moderator->image);
        }

        $moderator->delete();

        return redirect()->route('admin.moderators.index')
            ->with('success', 'মডারেটর মুছে ফেলা হয়েছে!');
    }

    /**
     * Work sessions and reports dashboard for admin
     */
    public function reports(Request $request)
    {
        $preset = $request->get('preset', 'this_month');
        $from   = $request->get('from');
        $to     = $request->get('to');

        if (!$from || !$to) {
            match ($preset) {
                'today'      => [$from = Carbon::today()->toDateString(), $to = Carbon::today()->toDateString()],
                'this_week'  => [$from = Carbon::now()->startOfWeek()->toDateString(), $to = Carbon::now()->endOfWeek()->toDateString()],
                'this_month' => [$from = Carbon::now()->startOfMonth()->toDateString(), $to = Carbon::now()->endOfMonth()->toDateString()],
                'last_month' => [$from = Carbon::now()->subMonth()->startOfMonth()->toDateString(), $to = Carbon::now()->subMonth()->endOfMonth()->toDateString()],
                'this_year'  => [$from = Carbon::now()->startOfYear()->toDateString(), $to = Carbon::now()->endOfYear()->toDateString()],
                default      => [$from = Carbon::now()->startOfMonth()->toDateString(), $to = Carbon::now()->endOfMonth()->toDateString()],
            };
        }

        $query = ModeratorWorkSession::query()
            ->with('moderator')
            ->whereDate('started_at', '>=', $from)
            ->whereDate('started_at', '<=', $to);

        if ($request->filled('moderator_id')) {
            $query->where('moderator_id', $request->moderator_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Summary metrics
        $clone = clone $query;
        $totalSessions = (clone $clone)->count();
        $completedSessions = (clone $clone)->where('status', 'completed')->count();
        $totalDurationSeconds = (int) (clone $clone)->where('status', 'completed')->sum('duration_seconds');
        $reportsSubmitted = (clone $clone)->whereNotNull('work_report')->count();
        $currentlyWorking = ModeratorWorkSession::where('status', 'in_progress')->count();

        // Paginated sessions list
        $sessions = $query->latest('started_at')->paginate(20)->withQueryString();

        // All moderators for filter dropdown
        $allModerators = Moderator::orderBy('name')->get();

        // Format total duration
        $totalHours = floor($totalDurationSeconds / 3600);
        $totalMinutes = floor(($totalDurationSeconds % 3600) / 60);
        $formattedTotalTime = $totalHours > 0 ? "{$totalHours}h {$totalMinutes}m" : "{$totalMinutes}m";

        return view('admin.moderators.reports', compact(
            'sessions',
            'allModerators',
            'from',
            'to',
            'preset',
            'totalSessions',
            'completedSessions',
            'totalDurationSeconds',
            'formattedTotalTime',
            'reportsSubmitted',
            'currentlyWorking'
        ));
    }

    /**
     * Display moderator salary withdrawal requests
     */
    public function withdrawals(Request $request)
    {
        $query = ModeratorWithdrawal::with(['moderator', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('moderator_id')) {
            $query->where('moderator_id', $request->moderator_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Summary KPI stats
        $stats = [
            'pending_count'   => ModeratorWithdrawal::where('status', 'pending')->count(),
            'pending_amount'  => (float) ModeratorWithdrawal::where('status', 'pending')->sum('amount'),
            'approved_count'  => ModeratorWithdrawal::where('status', 'approved')->count(),
            'approved_amount' => (float) ModeratorWithdrawal::where('status', 'approved')->sum('amount'),
            'rejected_count'  => ModeratorWithdrawal::where('status', 'rejected')->count(),
            'rejected_amount' => (float) ModeratorWithdrawal::where('status', 'rejected')->sum('amount'),
        ];

        $withdrawals = $query->latest()->paginate(20)->withQueryString();
        $allModerators = Moderator::orderBy('name')->get();

        return view('admin.moderators.withdrawals', compact('withdrawals', 'stats', 'allModerators'));
    }

    /**
     * Approve moderator salary withdrawal request with admin note
     */
    public function approveWithdrawal(Request $request, ModeratorWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'এই উইথড্র রিকোয়েস্টটি ইতিমধ্যে প্রক্রিয়াজাত করা হয়েছে।');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $withdrawal->update([
            'status'       => 'approved',
            'admin_note'   => $validated['admin_note'] ?? null,
            'processed_by' => auth()->id(),
            'processed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'মডারেটরের বেতন উইথড্র রিকোয়েস্ট সফলভাবে অ্যাপ্রুভ করা হয়েছে!');
    }

    /**
     * Reject moderator salary withdrawal request with admin note
     */
    public function rejectWithdrawal(Request $request, ModeratorWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'এই উইথড্র রিকোয়েস্টটি ইতিমধ্যে প্রক্রিয়াজাত করা হয়েছে।');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ], [
            'admin_note.required' => 'বাতিল করার কারণ বা এডমিন নোট দেওয়া আবশ্যক।',
        ]);

        $withdrawal->update([
            'status'       => 'rejected',
            'admin_note'   => $validated['admin_note'],
            'processed_by' => auth()->id(),
            'processed_at' => Carbon::now(),
        ]);

        return back()->with('success', 'মডারেটরের উইথড্র রিকোয়েস্ট বাতিল করা হয়েছে এবং কারণ সংরক্ষণ করা হয়েছে।');
    }
}
