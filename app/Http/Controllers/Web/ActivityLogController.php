<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user.roles', 'vendor'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_title', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get modules list for filtering dropdown
        $modules = ActivityLog::distinct()->pluck('module')->filter()->sort()->values();
        if ($modules->isEmpty()) {
            $modules = collect(['Product', 'Sale', 'Category', 'Customer', 'Purchase', 'User', 'Coupon', 'Banner', 'IncomeExpense', 'Auth']);
        }

        // Get users list for filtering dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        // Summary counts
        $stats = [
            'total'   => ActivityLog::count(),
            'today'   => ActivityLog::whereDate('created_at', today())->count(),
            'created' => ActivityLog::where('action', 'created')->count(),
            'updated' => ActivityLog::where('action', 'updated')->count(),
            'deleted' => ActivityLog::where('action', 'deleted')->count(),
        ];

        return view('admin.activity-logs.index', compact('logs', 'modules', 'users', 'stats'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['user.roles', 'vendor']);

        return response()->json([
            'id'            => $activityLog->id,
            'action'        => $activityLog->action,
            'action_label'  => ucfirst($activityLog->action),
            'badge_class'   => $activityLog->action_badge_class,
            'action_icon'   => $activityLog->action_icon,
            'module'        => $activityLog->module,
            'module_icon'   => $activityLog->module_icon,
            'subject_title' => $activityLog->subject_title,
            'subject_type'  => $activityLog->subject_type,
            'subject_id'    => $activityLog->subject_id,
            'description'   => $activityLog->description,
            'user_name'     => $activityLog->user?->name ?? 'System / Guest',
            'user_email'    => $activityLog->user?->email ?? '—',
            'user_role'     => $activityLog->user?->roles->first()?->name ? ucfirst($activityLog->user->roles->first()->name) : 'User',
            'vendor_name'   => $activityLog->vendor?->name ?? 'All Branches',
            'ip_address'    => $activityLog->ip_address ?? '—',
            'user_agent'    => $activityLog->user_agent ?? '—',
            'created_at'    => $activityLog->created_at->format('d M Y, h:i:s A'),
            'time_ago'      => $activityLog->created_at->diffForHumans(),
            'properties'    => $activityLog->properties ?? [],
        ]);
    }

    public function clear(Request $request)
    {
        $days = $request->input('days', '90');

        if ($days === 'all') {
            ActivityLog::truncate();
            $msg = 'সব অ্যাক্টিভিটি লগ সফলভাবে মুছে ফেলা হয়েছে।';
        } else {
            $daysInt = max(1, (int)$days);
            $cutoff = now()->subDays($daysInt);
            $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();
            $msg = "{$daysInt} দিনের আগের মোট {$deleted} টি লগ মুছে ফেলা হয়েছে।";
        }

        return back()->with('success', $msg);
    }
}
