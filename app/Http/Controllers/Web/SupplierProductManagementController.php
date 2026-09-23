<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierProductManagementController extends Controller
{
    /**
     * Display a listing of supplier products awaiting review or already approved/rejected.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all');
        $supplierId = $request->get('supplier_id');
        $search = trim($request->get('search', ''));

        // Query supplier products without global vendor scopes
        $query = Product::withoutGlobalScopes()
            ->whereNotNull('supplier_id')
            ->with(['supplier', 'category', 'variants.color', 'variants.size'])
            ->latest('id');

        // Apply tab filter
        if ($tab === 'pending') {
            $query->where('approval_status', 'pending');
        } elseif ($tab === 'approved') {
            $query->where('approval_status', 'approved');
        } elseif ($tab === 'rejected') {
            $query->where('approval_status', 'rejected');
        }

        // Apply supplier filter
        if (!empty($supplierId)) {
            $query->where('supplier_id', $supplierId);
        }

        // Apply search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->paginate(20)->withQueryString();

        // Calculate summary counters
        $pendingCount = Product::withoutGlobalScopes()
            ->whereNotNull('supplier_id')
            ->where('approval_status', 'pending')
            ->count();

        $approvedCount = Product::withoutGlobalScopes()
            ->whereNotNull('supplier_id')
            ->where('approval_status', 'approved')
            ->count();

        $rejectedCount = Product::withoutGlobalScopes()
            ->whereNotNull('supplier_id')
            ->where('approval_status', 'rejected')
            ->count();

        $totalCount = Product::withoutGlobalScopes()
            ->whereNotNull('supplier_id')
            ->count();

        // Suppliers list for filter dropdown
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'company_name', 'phone', 'commission_percentage']);

        return view('admin.supplier-products.index', compact(
            'products',
            'tab',
            'supplierId',
            'search',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount',
            'suppliers'
        ));
    }

    /**
     * Approve a supplier product and set its product-level admin commission rate.
     */
    public function approve(Request $request, Product $product)
    {
        $request->validate([
            'admin_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $commissionRate = round((float) $request->input('admin_commission_rate'), 2);

        $product->update([
            'approval_status'       => 'approved',
            'status'                => 'active', // Make product active on storefront
            'admin_commission_rate' => $commissionRate,
            'rejection_reason'      => null,
            'approved_at'           => now(),
        ]);

        if (class_exists(ActivityLog::class)) {
            try {
                ActivityLog::record(
                    action: 'approved_supplier_product',
                    description: "Approved supplier product: {$product->name} (#{$product->id}) with {$commissionRate}% commission",
                    modelType: Product::class,
                    modelId: $product->id
                );
            } catch (\Throwable $e) {
                // Ignore log error
            }
        }

        return back()->with('success', "Product \"{$product->name}\" approved successfully with {$commissionRate}% admin commission! It is now live on the storefront.");
    }

    /**
     * Reject a supplier product with an optional/required reason.
     */
    public function reject(Request $request, Product $product)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $product->update([
            'approval_status'  => 'rejected',
            'status'           => 'inactive', // Remove from live storefront
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        if (class_exists(ActivityLog::class)) {
            try {
                ActivityLog::record(
                    action: 'rejected_supplier_product',
                    description: "Rejected supplier product: {$product->name} (#{$product->id}) - Reason: " . $request->input('rejection_reason'),
                    modelType: Product::class,
                    modelId: $product->id
                );
            } catch (\Throwable $e) {
                // Ignore log error
            }
        }

        return back()->with('success', "Product \"{$product->name}\" has been marked as rejected.");
    }

    /**
     * Update the admin commission percentage for an already approved supplier product.
     */
    public function updateCommission(Request $request, Product $product)
    {
        $request->validate([
            'admin_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $commissionRate = round((float) $request->input('admin_commission_rate'), 2);

        $product->update([
            'admin_commission_rate' => $commissionRate,
        ]);

        return back()->with('success', "Admin commission for \"{$product->name}\" updated to {$commissionRate}%.");
    }
}
