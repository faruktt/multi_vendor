<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();

        return view('admin.payment-methods', compact('paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'   => 'required|string|max:100|unique:payment_methods,label',
            'details' => 'nullable|string|max:1000',
        ]);

        $key = Str::slug($request->label, '_');
        if (PaymentMethod::where('key', $key)->exists()) {
            return back()->with('error', 'A payment method with a matching internal key already exists.');
        }

        PaymentMethod::create([
            'key'        => $key,
            'label'      => $request->label,
            'details'    => $request->details,
            'is_active'  => true,
            'sort_order' => (int) PaymentMethod::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Payment method added.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'label'   => 'required|string|max:100|unique:payment_methods,label,' . $paymentMethod->id,
            'details' => 'nullable|string|max:1000',
        ]);

        $paymentMethod->update($request->only('label', 'details'));

        return back()->with('success', 'Payment method updated.');
    }

    public function toggleActive(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->is_protected && $paymentMethod->is_active) {
            return back()->with('error', 'This payment method is required by the system and cannot be deactivated.');
        }

        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        return back()->with('success', 'Payment method updated.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            PaymentMethod::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Order updated.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->is_protected) {
            return back()->with('error', 'This payment method is required by the system and cannot be deleted.');
        }

        $inUse = Sale::withoutGlobalScopes()->where('payment_method', $paymentMethod->key)->count()
            + Purchase::withoutGlobalScopes()->where('payment_method', $paymentMethod->key)->count()
            + SaleReturn::withoutGlobalScopes()->where('refund_method', $paymentMethod->key)->count()
            + PurchaseReturn::withoutGlobalScopes()->where('refund_method', $paymentMethod->key)->count();

        if ($inUse > 0) {
            return back()->with('error', "Cannot delete — {$inUse} record(s) currently use this payment method.");
        }

        $paymentMethod->delete();

        return back()->with('success', 'Payment method deleted.');
    }
}
