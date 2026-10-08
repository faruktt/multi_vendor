<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /* ── Register ─────────────────────────── */
    public function showRegister()
    {
        if (auth('supplier')->check()) {
            return redirect()->route('supplier.dashboard');
        }
        return view('supplier.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'company_name'        => 'nullable|string|max:255',
            'email'               => 'required|email|max:255|unique:suppliers,email',
            'password'            => 'required|min:6|confirmed',
            'phone'               => 'required|string|max:30',
            'address'             => 'nullable|string|max:500',
            'image'               => 'required|image|max:5120',
            'nid_front'           => 'required|image|max:5120',
            'nid_back'            => 'required|image|max:5120',
            'guardian_nid_front'  => 'required|image|max:5120',
            'guardian_nid_back'   => 'required|image|max:5120',
        ], [
            'image.required'              => 'নিজের প্রোফাইল ছবি আপলোড করা আবশ্যক।',
            'nid_front.required'          => 'নিজের NID ফ্রন্ট পেজ ছবি আপলোড করা আবশ্যক।',
            'nid_back.required'           => 'নিজের NID ব্যাক পেজ ছবি আপলোড করা আবশ্যক।',
            'guardian_nid_front.required' => 'অভিভাবকের NID ফ্রন্ট পেজ ছবি আপলোড করা আবশ্যক।',
            'guardian_nid_back.required'  => 'অভিভাবকের NID ব্যাক পেজ ছবি আপলোড করা আবশ্যক।',
        ]);

        $onlineVendor = Vendor::onlineStore();

        $data = [
            'vendor_id'    => $onlineVendor->id,
            'name'         => $request->name,
            'company_name' => $request->company_name ?: $request->name,
            'email'        => strtolower(trim($request->email)),
            'password'     => Hash::make($request->password),
            'phone'        => $request->phone,
            'address'      => $request->address,
            'status'       => 'pending',
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('suppliers', 'uploads');
            $data['logo']  = $data['image'];
        }
        if ($request->hasFile('nid_front')) {
            $data['nid_front'] = $request->file('nid_front')->store('supplier_docs', 'uploads');
        }
        if ($request->hasFile('nid_back')) {
            $data['nid_back'] = $request->file('nid_back')->store('supplier_docs', 'uploads');
        }
        if ($request->hasFile('guardian_nid_front')) {
            $data['guardian_nid_front'] = $request->file('guardian_nid_front')->store('supplier_docs', 'uploads');
        }
        if ($request->hasFile('guardian_nid_back')) {
            $data['guardian_nid_back'] = $request->file('guardian_nid_back')->store('supplier_docs', 'uploads');
        }

        Supplier::create($data);

        return redirect()->route('supplier.login')
            ->with('success', 'Registration submitted successfully! Your account is pending admin approval. You will be able to login once approved.');
    }

    /* ── Login ────────────────────────────── */
    public function showLogin()
    {
        if (auth('supplier')->check()) {
            return redirect()->route('supplier.dashboard');
        }
        return view('supplier.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email'    => strtolower(trim($request->email)),
            'password' => $request->password,
        ];

        if (!Auth::guard('supplier')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $supplier = auth('supplier')->user();

        if ($supplier->isPending()) {
            Auth::guard('supplier')->logout();
            return back()->withErrors(['email' => 'Your account is pending admin approval. Please wait for an administrator to activate your account.'])->withInput();
        }

        if (!$supplier->isActive()) {
            Auth::guard('supplier')->logout();
            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact support.'])->withInput();
        }

        $request->session()->regenerate();
        return redirect()->route('supplier.dashboard');
    }

    /* ── Logout ───────────────────────────── */
    public function logout(Request $request)
    {
        Auth::guard('supplier')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('supplier.login')->with('success', 'Logged out successfully.');
    }
}
