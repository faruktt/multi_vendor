<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /** List all coupons with filters and statistics */
    public function index(Request $request)
    {
        $query = Coupon::withCount('usages')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('applicable_for')) {
            $query->where('applicable_for', $request->applicable_for);
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        $coupons = $query->paginate(15)->withQueryString();

        // Statistics
        $totalCoupons       = Coupon::count();
        $activeCoupons      = Coupon::where('status', 'active')->count();
        $totalRedemptions   = CouponUsage::count();
        $totalDiscountGiven = (float) CouponUsage::sum('discount_amount');

        return view('admin.coupons.index', compact(
            'coupons',
            'totalCoupons',
            'activeCoupons',
            'totalRedemptions',
            'totalDiscountGiven'
        ));
    }

    /** Store a newly created coupon */
    public function store(Request $request)
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->code)),
        ]);

        $validated = $request->validate([
            'code'                 => 'required|string|max:50|unique:coupons,code',
            'name'                 => 'nullable|string|max:255',
            'discount_type'        => 'required|in:percentage,fixed',
            'discount_amount'      => 'required|numeric|min:0.01',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'applicable_for'       => 'required|in:everyone,customer,reseller',
            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'start_date'           => 'nullable|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'status'               => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;

        Coupon::create($validated);

        return back()->with('success', "Coupon '{$validated['code']}' created successfully.");
    }

    /** Update an existing coupon */
    public function update(Request $request, Coupon $coupon)
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->code)),
        ]);

        $validated = $request->validate([
            'code'                 => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name'                 => 'nullable|string|max:255',
            'discount_type'        => 'required|in:percentage,fixed',
            'discount_amount'      => 'required|numeric|min:0.01',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'applicable_for'       => 'required|in:everyone,customer,reseller',
            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'start_date'           => 'nullable|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'status'               => 'required|in:active,inactive',
        ]);

        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;

        $coupon->update($validated);

        return back()->with('success', "Coupon '{$coupon->code}' updated successfully.");
    }

    /** Toggle status active/inactive */
    public function toggleStatus(Coupon $coupon)
    {
        $newStatus = $coupon->status === 'active' ? 'inactive' : 'active';
        $coupon->update(['status' => $newStatus]);

        return back()->with('success', "Coupon '{$coupon->code}' is now " . ucfirst($newStatus) . ".");
    }

    /** Delete a coupon */
    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "Coupon '{$code}' deleted successfully.");
    }
}
