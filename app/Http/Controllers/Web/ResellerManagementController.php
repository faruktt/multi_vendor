<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\ResellerApprovedMail;
use App\Models\Reseller;
use App\Models\ResellerWithdrawal;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResellerManagementController extends Controller
{
    /** List all resellers with details panel and filters */
    public function index(Request $request)
    {
        $query = Reseller::withCount(['orders'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('business_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('reseller_id')) {
            $query->where('id', $request->reseller_id);
        }

        $resellers    = $query->paginate(20)->withQueryString();
        $allResellers = Reseller::orderBy('name')->get();
        $pendingCount = Reseller::where('status', 'pending')->count();
        $activeCount  = Reseller::where('status', 'active')->count();

        // Selected Reseller Details
        $selectedReseller = null;
        $resellerStats    = null;

        if ($request->filled('reseller_id')) {
            $selectedReseller = Reseller::withCount(['orders'])->find($request->reseller_id);
        } elseif ($request->filled('search') && $resellers->total() === 1) {
            $selectedReseller = $resellers->first();
        }

        if ($selectedReseller) {
            $resellerSales = Sale::withoutGlobalScopes()->where('reseller_id', $selectedReseller->id);
            $completedSales = Sale::withoutGlobalScopes()->where('reseller_id', $selectedReseller->id)->whereIn('order_status', ['completed', 'complete']);

            $totalOrders     = $resellerSales->count();
            $totalSales      = (float) $resellerSales->sum('total');
            $totalProfit     = (float) $completedSales->sum('reseller_profit');
            $totalWithdrawn  = (float) ResellerWithdrawal::where('reseller_id', $selectedReseller->id)->where('status', 'approved')->sum('amount');
            $pendingWithdrawn= (float) ResellerWithdrawal::where('reseller_id', $selectedReseller->id)->where('status', 'pending')->sum('amount');
            $availableProfit = max(0, $totalProfit - $totalWithdrawn);
            $withdrawableProfit = max(0, $availableProfit - $pendingWithdrawn);

            $resellerStats = [
                'orders'              => $totalOrders,
                'sales'               => $totalSales,
                'total_profit'        => $totalProfit,
                'withdrawn'           => $totalWithdrawn,
                'pending_withdrawn'   => $pendingWithdrawn,
                'available_profit'    => $availableProfit,
                'withdrawable_profit' => $withdrawableProfit,
            ];
        }

        // Global / All Resellers Aggregated Stats
        $globalOrdersCount   = Sale::withoutGlobalScopes()->where('channel', 'reseller')->count();
        $globalSalesTotal    = (float) Sale::withoutGlobalScopes()->where('channel', 'reseller')->sum('total');
        $globalProfitTotal   = (float) Sale::withoutGlobalScopes()->where('channel', 'reseller')->whereIn('order_status', ['completed', 'complete'])->sum('reseller_profit');
        $globalWithdrawn     = (float) ResellerWithdrawal::where('status', 'approved')->sum('amount');
        $globalPending       = (float) ResellerWithdrawal::where('status', 'pending')->sum('amount');
        $globalAvailable     = max(0, $globalProfitTotal - $globalWithdrawn);
        $pendingWithdrawalsCount = ResellerWithdrawal::where('status', 'pending')->count();

        $globalStats = [
            'total_resellers'     => Reseller::count(),
            'active_resellers'    => $activeCount,
            'pending_resellers'   => $pendingCount,
            'total_orders'        => $globalOrdersCount,
            'total_sales'         => $globalSalesTotal,
            'total_profit'        => $globalProfitTotal,
            'total_withdrawn'     => $globalWithdrawn,
            'pending_withdrawn'   => $globalPending,
            'available_profit'    => $globalAvailable,
            'pending_withdrawals' => $pendingWithdrawalsCount,
        ];

        return view('admin.resellers.index', compact(
            'resellers',
            'allResellers',
            'pendingCount',
            'activeCount',
            'selectedReseller',
            'resellerStats',
            'globalStats'
        ));
    }

    /** Approve a reseller */
    public function approve(Reseller $reseller)
    {
        $reseller->update(['status' => 'active', 'approved_at' => now()]);

        try {
            if (!empty($reseller->email)) {
                Mail::to($reseller->email)->send(new ResellerApprovedMail($reseller));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send reseller approval email to {$reseller->email}: " . $e->getMessage());
        }

        return back()->with('success', "Reseller {$reseller->name} approved and notification email sent.");
    }

    /** Reject / deactivate a reseller */
    public function reject(Reseller $reseller)
    {
        $reseller->update(['status' => 'inactive']);
        return back()->with('success', "Reseller {$reseller->name} deactivated.");
    }

    /** Delete a reseller */
    public function destroy(Reseller $reseller)
    {
        $reseller->delete();
        return back()->with('success', 'Reseller deleted.');
    }

    /** All reseller orders with filters */
    public function orders(Request $request)
    {
        $query = Sale::withoutGlobalScopes()
            ->where('channel', 'reseller')
            ->with(['reseller', 'vendor', 'saleItems', 'customer'])
            ->latest();

        if ($request->filled('reseller_id')) $query->where('reseller_id', $request->reseller_id);
        if ($request->filled('status'))      $query->where('order_status', $request->status);
        if ($request->filled('from'))        $query->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to'))          $query->whereDate('created_at', '<=', $request->to);

        $orders    = $query->paginate(20)->withQueryString();
        $resellers = Reseller::where('status', 'active')->orderBy('name')->get();

        return view('admin.resellers.orders', compact('orders', 'resellers'));
    }

    /** Reseller summary report */
    public function report(Request $request)
    {
        $preset = $request->get('preset', 'this_month');
        [$from, $to] = $this->resolveRange($request, $preset);

        $fromC = Carbon::parse($from)->startOfDay();
        $toC   = Carbon::parse($to)->endOfDay();

        $resellers = Reseller::withCount(['orders'])
            ->withSum('orders as total_amount', 'total')
            ->withSum('orders as total_due', 'due_amount')
            ->get()
            ->map(function ($r) use ($fromC, $toC) {
                $r->period_orders = Sale::withoutGlobalScopes()
                    ->where('reseller_id', $r->id)
                    ->whereBetween('created_at', [$fromC, $toC])
                    ->count();
                $r->period_amount = round(Sale::withoutGlobalScopes()
                    ->where('reseller_id', $r->id)
                    ->whereBetween('created_at', [$fromC, $toC])
                    ->sum('total'), 2);
                $r->period_profit = round(Sale::withoutGlobalScopes()
                    ->where('reseller_id', $r->id)
                    ->whereIn('order_status', ['completed', 'complete'])
                    ->whereBetween('created_at', [$fromC, $toC])
                    ->sum('reseller_profit'), 2);
                return $r;
            })
            ->sortByDesc('period_amount');

        $totalOrders = Sale::withoutGlobalScopes()->where('channel', 'reseller')->whereBetween('created_at', [$fromC, $toC])->count();
        $totalAmount = round(Sale::withoutGlobalScopes()->where('channel', 'reseller')->whereBetween('created_at', [$fromC, $toC])->sum('total'), 2);
        $totalProfit = round(Sale::withoutGlobalScopes()->where('channel', 'reseller')->whereIn('order_status', ['completed', 'complete'])->whereBetween('created_at', [$fromC, $toC])->sum('reseller_profit'), 2);
        $totalWithdrawn = round(ResellerWithdrawal::where('status', 'approved')->whereBetween('created_at', [$fromC, $toC])->sum('amount'), 2);

        return view('admin.resellers.report', compact('resellers', 'totalOrders', 'totalAmount', 'totalProfit', 'totalWithdrawn', 'preset', 'from', 'to'));
    }

    private function resolveRange(Request $request, string $preset): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [$request->from, $request->to];
        }
        $now = Carbon::now();
        return match ($preset) {
            'today'      => [today()->toDateString(), today()->toDateString()],
            'this_week'  => [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'this_year'  => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            default      => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        };
    }

    public function withdrawProfit(Request $request, Reseller $reseller)
    {
        $request->validate([
            'amount'          => 'required|numeric|min:0.01',
            'payment_method'  => 'nullable|string|max:50',
            'payment_details' => 'nullable|string|max:255',
            'note'            => 'nullable|string|max:255',
        ]);

        if ($request->amount > $reseller->available_balance) {
            return back()->with('error', 'Withdrawal amount exceeds available balance.');
        }

        ResellerWithdrawal::create([
            'reseller_id'     => $reseller->id,
            'amount'          => round((float) $request->amount, 2),
            'payment_method'  => $request->payment_method ?? 'cash',
            'payment_details' => $request->payment_details ?? 'Admin Direct Payout',
            'note'            => $request->note,
            'status'          => 'approved',
            'processed_by'    => auth()->id(),
            'processed_at'    => now(),
            'created_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Profit deducted successfully.');
    }

    /** List all reseller withdrawal requests with filters and stats */
    public function withdrawals(Request $request)
    {
        $query = ResellerWithdrawal::with(['reseller', 'processedBy', 'createdBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reseller_id')) {
            $query->where('reseller_id', $request->reseller_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('payment_details', 'like', "%{$s}%")
                  ->orWhere('note', 'like', "%{$s}%")
                  ->orWhere('admin_note', 'like', "%{$s}%")
                  ->orWhereHas('reseller', function ($rq) use ($s) {
                      $rq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%")
                         ->orWhere('business_name', 'like', "%{$s}%");
                  });
            });
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        $stats = [
            'total_count'    => ResellerWithdrawal::count(),
            'pending_count'  => ResellerWithdrawal::where('status', 'pending')->count(),
            'approved_count' => ResellerWithdrawal::where('status', 'approved')->count(),
            'rejected_count' => ResellerWithdrawal::where('status', 'rejected')->count(),
            'pending_amount' => (float) ResellerWithdrawal::where('status', 'pending')->sum('amount'),
            'approved_amount'=> (float) ResellerWithdrawal::where('status', 'approved')->sum('amount'),
        ];

        $resellers = Reseller::orderBy('name')->get();

        return view('admin.resellers.withdrawals', compact('withdrawals', 'stats', 'resellers'));
    }

    /** Approve a reseller withdrawal request and deduct profit */
    public function approveWithdrawal(Request $request, ResellerWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        $reseller = $withdrawal->reseller;
        if (!$reseller) {
            return back()->with('error', 'Reseller not found.');
        }

        if ($withdrawal->amount > $reseller->available_balance) {
            return back()->with('error', 'Reseller does not have sufficient available balance (৳' . number_format($reseller->available_balance, 2) . ') to approve this request.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $withdrawal->update([
            'status'       => 'approved',
            'admin_note'   => $request->admin_note,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal request of ৳' . number_format($withdrawal->amount, 2) . ' for ' . $reseller->name . ' has been approved. Profit deducted successfully.');
    }

    /** Reject a reseller withdrawal request without deducting profit */
    public function rejectWithdrawal(Request $request, ResellerWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $withdrawal->update([
            'status'       => 'rejected',
            'admin_note'   => $request->admin_note ?: 'Rejected by administrator',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal request of ৳' . number_format($withdrawal->amount, 2) . ' has been rejected. No profit was deducted.');
    }
}

