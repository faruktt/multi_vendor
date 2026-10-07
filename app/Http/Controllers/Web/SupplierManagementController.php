<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\SupplierWithdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\SupplierApprovedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupplierManagementController extends Controller
{
    /** List all suppliers with approval status, filters and stats */
    public function index(Request $request)
    {
        $query = Supplier::query()
            ->withCount(['products' => fn($q) => $q->withoutGlobalScopes()])
            ->withCount(['saleItems'])
            ->withSum('saleItems as total_sales_amount', 'subtotal')
            ->withSum('saleItems as total_commission_earned', 'admin_commission_amount')
            ->withSum('saleItems as total_supplier_earnings', 'supplier_earning')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $suppliers = $query->paginate(20)->withQueryString();

        $totalCount    = Supplier::count();
        $pendingCount  = Supplier::where('status', 'pending')->count();
        $activeCount   = Supplier::where('status', 'active')->count();
        $inactiveCount = Supplier::where('status', 'inactive')->count();

        // Platform-wide supplier commission totals
        $totalPlatformSupplierSales    = (float) SaleItem::whereNotNull('supplier_id')->sum('subtotal');
        $totalPlatformAdminCommission  = (float) SaleItem::whereNotNull('supplier_id')->sum('admin_commission_amount');
        $totalPlatformSupplierPayable  = (float) SaleItem::whereNotNull('supplier_id')->sum('supplier_earning');

        return view('admin.suppliers.index', compact(
            'suppliers',
            'totalCount',
            'pendingCount',
            'activeCount',
            'inactiveCount',
            'totalPlatformSupplierSales',
            'totalPlatformAdminCommission',
            'totalPlatformSupplierPayable'
        ));
    }

    /** Update commission percentage for a supplier */
    public function updateCommission(Request $request, Supplier $supplier)
    {
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $supplier->update([
            'commission_percentage' => $request->commission_percentage,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Commission rate updated to {$request->commission_percentage}%",
            ]);
        }

        return back()->with('success', "Commission rate for \"{$supplier->display_name}\" updated to {$request->commission_percentage}%.");
    }

    /** Detailed financial & commission breakdown for a single supplier */
    public function commissionReport(Request $request, Supplier $supplier)
    {
        $query = $supplier->saleItems()
            ->with(['sale.customer', 'product', 'variant'])
            ->latest();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $saleItems = $query->paginate(30)->withQueryString();

        $totalSalesAmount     = (float) $supplier->saleItems()->sum('subtotal');
        $totalCommissionEarned = (float) $supplier->saleItems()->sum('admin_commission_amount');
        $totalNetEarnings     = (float) $supplier->saleItems()->sum('supplier_earning');
        $totalItemsSold       = (int) $supplier->saleItems()->sum('quantity');

        return view('admin.suppliers.commission-report', compact(
            'supplier',
            'saleItems',
            'totalSalesAmount',
            'totalCommissionEarned',
            'totalNetEarnings',
            'totalItemsSold'
        ));
    }

    /** Approve a pending supplier */
    public function approve(Supplier $supplier)
    {
        $supplier->update([
            'status'      => 'active',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $emailSent = false;
        try {
            if (!empty($supplier->email)) {
                Mail::to($supplier->email)->send(new SupplierApprovedMail($supplier));
                $emailSent = true;
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send supplier approval email to {$supplier->email}: " . $e->getMessage());
        }

        $msg = "Supplier \"{$supplier->display_name}\" approved successfully.";
        if ($emailSent) {
            $msg .= " Confirmation email sent to {$supplier->email}.";
        }

        return back()->with('success', $msg);
    }

    /** Deactivate / Reject an active supplier */
    public function reject(Supplier $supplier)
    {
        $supplier->update([
            'status' => 'inactive',
        ]);

        return back()->with('success', "Supplier \"{$supplier->display_name}\" has been deactivated. Access to dashboard blocked.");
    }

    /** Remove supplier */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->saleItems()->exists()) {
            $supplier->update(['status' => 'inactive']);
            return back()->with('info', "Supplier has existing order records. They have been deactivated instead of permanently deleted.");
        }

        $supplier->delete();
        return back()->with('success', 'Supplier deleted successfully.');
    }

    /**
     * List all supplier withdrawal requests with filters and stats
     */
    public function withdrawals(Request $request)
    {
        $query = SupplierWithdrawal::with(['supplier', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('payment_details', 'like', "%{$s}%")
                  ->orWhere('note', 'like', "%{$s}%")
                  ->orWhere('admin_note', 'like', "%{$s}%")
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        $stats = [
            'pending_count'   => SupplierWithdrawal::where('status', 'pending')->count(),
            'pending_amount'  => (float) SupplierWithdrawal::where('status', 'pending')->sum('amount'),
            'approved_count'  => SupplierWithdrawal::where('status', 'approved')->count(),
            'approved_amount' => (float) SupplierWithdrawal::where('status', 'approved')->sum('amount'),
            'rejected_count'  => SupplierWithdrawal::where('status', 'rejected')->count(),
            'rejected_amount' => (float) SupplierWithdrawal::where('status', 'rejected')->sum('amount'),
        ];

        $withdrawals = $query->latest()->paginate(20)->withQueryString();
        $allSuppliers = Supplier::orderBy('name')->get(['id', 'name', 'company_name', 'phone']);

        return view('admin.suppliers.withdrawals', compact('withdrawals', 'stats', 'allSuppliers'));
    }

    /**
     * Approve supplier withdrawal request with admin note (e.g. Trx ID / remarks)
     * Deducts amount from available balance
     */
    public function approveWithdrawal(Request $request, SupplierWithdrawal $withdrawal)
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

        return back()->with('success', 'সাপ্লায়ারের টাকা উত্তোলনের রিকোয়েস্ট (৳' . number_format($withdrawal->amount, 2) . ') সফলভাবে অনুমোদন করা হয়েছে এবং ব্যালেন্স থেকে কর্তন হয়েছে!');
    }

    /**
     * Reject supplier withdrawal request with admin note / reason
     */
    public function rejectWithdrawal(Request $request, SupplierWithdrawal $withdrawal)
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

        return back()->with('success', 'সাপ্লায়ারের উইথড্র রিকোয়েস্ট বাতিল করা হয়েছে এবং কারণ সংরক্ষণ করা হয়েছে।');
    }
}
