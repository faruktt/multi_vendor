<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAccountController extends Controller
{
    /** Customer Account Dashboard */
    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        $branch   = Vendor::onlineStore();

        $ordersQuery = Sale::withoutGlobalScopes()
            ->where('customer_id', $customer->id);

        $totalOrders   = (clone $ordersQuery)->count();
        $totalSpent    = (float) (clone $ordersQuery)->where('order_status', '!=', 'cancelled')->sum('total');
        $pendingOrders = (clone $ordersQuery)->where('order_status', 'pending')->count();
        $deliveredOrders = (clone $ordersQuery)->where('order_status', 'delivered')->count();

        $recentOrders = (clone $ordersQuery)
            ->with(['saleItems.product', 'vendor'])
            ->latest()
            ->take(5)
            ->get();

        return view('shop.account.dashboard', compact(
            'customer',
            'branch',
            'totalOrders',
            'totalSpent',
            'pendingOrders',
            'deliveredOrders',
            'recentOrders'
        ));
    }

    /** Customer Orders List */
    public function orders(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $branch   = Vendor::onlineStore();

        $query = Sale::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->with(['saleItems.product', 'saleItems.variant'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('shop.account.orders', compact('customer', 'branch', 'orders'));
    }

    /** View Single Customer Order Details */
    public function orderDetail($id)
    {
        $customer = Auth::guard('customer')->user();
        $branch   = Vendor::onlineStore();

        $order = Sale::withoutGlobalScopes()
            ->where('customer_id', $customer->id)
            ->with(['saleItems.product', 'saleItems.variant', 'coupon', 'vendor'])
            ->findOrFail($id);

        return view('shop.account.order-detail', compact('customer', 'branch', 'order'));
    }

    /** Profile Management */
    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        $branch   = Vendor::onlineStore();

        return view('shop.account.profile', compact('customer', 'branch'));
    }

    /** Update Customer Profile */
    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $branch   = Vendor::onlineStore();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:30',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $phone = trim($request->phone);
        $email = $request->filled('email') ? trim($request->email) : null;

        // Check unique phone for other accounts
        $existingPhone = Customer::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('phone', $phone)
            ->where('id', '!=', $customer->id)
            ->exists();

        if ($existingPhone) {
            return back()->withErrors(['phone' => 'This phone number is already registered to another account.']);
        }

        if ($email) {
            $existingEmail = Customer::withoutGlobalScopes()
                ->where('vendor_id', $branch->id)
                ->where('email', $email)
                ->where('id', '!=', $customer->id)
                ->exists();

            if ($existingEmail) {
                return back()->withErrors(['email' => 'This email is already registered to another account.']);
            }
        }

        $customer->update([
            'name'    => $validated['name'],
            'phone'   => $phone,
            'email'   => $email,
            'address' => $validated['address'],
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    /** Update Customer Password */
    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(6)],
        ]);

        if (!Hash::check($request->current_password, (string) $customer->password)) {
            return back()->withErrors(['current_password' => 'Your current password does not match our records.']);
        }

        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
