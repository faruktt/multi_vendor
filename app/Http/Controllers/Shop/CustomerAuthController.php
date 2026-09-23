<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    /** Show customer login form */
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('shop.customer.dashboard');
        }

        $branch = Vendor::onlineStore();
        return view('shop.auth.login', compact('branch'));
    }

    /** Handle customer login */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->login);
        $remember = $request->boolean('remember');

        // Check if input is email or phone
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

        $customerQuery = Customer::withoutGlobalScopes();
        if ($isEmail) {
            $customerQuery->where('email', $login);
        } else {
            $customerQuery->where('phone', $login);
        }

        $customer = $customerQuery->first();

        if (!$customer || !Hash::check($request->password, (string) $customer->password)) {
            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Invalid phone/email or password.']);
        }

        if ($customer->status !== 'active') {
            return back()->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => 'Your customer account is currently inactive. Please contact support.']);
        }

        Auth::guard('customer')->login($customer, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('shop.customer.dashboard'))
            ->with('success', 'Welcome back, ' . $customer->name . '!');
    }

    /** Show customer register form */
    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('shop.customer.dashboard');
        }

        $branch = Vendor::onlineStore();
        return view('shop.auth.register', compact('branch'));
    }

    /** Handle customer registration */
    public function register(Request $request)
    {
        $branch = Vendor::onlineStore();

        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:30',
            'email'    => 'nullable|email|max:255',
            'address'  => 'nullable|string|max:500',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $phone = trim($request->phone);
        $email = $request->filled('email') ? trim($request->email) : null;

        // Check existing customer with same phone or email
        $existingPhone = Customer::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('phone', $phone)
            ->first();

        if ($existingPhone && $existingPhone->password) {
            return back()->withInput()->withErrors([
                'phone' => 'An account already exists with this phone number. Please login instead.'
            ]);
        }

        if ($email) {
            $existingEmail = Customer::withoutGlobalScopes()
                ->where('vendor_id', $branch->id)
                ->where('email', $email)
                ->first();

            if ($existingEmail && $existingEmail->password) {
                return back()->withInput()->withErrors([
                    'email' => 'An account already exists with this email address. Please login instead.'
                ]);
            }
        }

        // If customer was created from guest checkout earlier, update their profile with password
        if ($existingPhone) {
            $existingPhone->update([
                'name'     => $request->name,
                'email'    => $email ?? $existingPhone->email,
                'address'  => $request->address ?? $existingPhone->address,
                'password' => Hash::make($request->password),
                'status'   => 'active',
            ]);
            $customer = $existingPhone;
        } else {
            $customer = Customer::create([
                'vendor_id' => $branch->id,
                'name'      => $request->name,
                'phone'     => $phone,
                'email'     => $email,
                'address'   => $request->address,
                'password'  => Hash::make($request->password),
                'status'    => 'active',
            ]);
        }

        Auth::guard('customer')->login($customer, true);
        $request->session()->regenerate();

        return redirect()->route('shop.customer.dashboard')
            ->with('success', 'Account registered successfully! Welcome to ' . ($branch->system_name ?? $branch->name) . '.');
    }

    /** Log customer out */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('root')->with('success', 'Logged out successfully.');
    }
}
