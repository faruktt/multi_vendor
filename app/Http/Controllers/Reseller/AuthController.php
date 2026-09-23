<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /* ── Register ─────────────────────────── */
    public function showRegister()
    {
        if (auth('reseller')->check()) return redirect()->route('reseller.dashboard');
        return view('reseller.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:resellers,email',
            'password'      => 'required|min:6|confirmed',
            'phone'         => 'nullable|string|max:30',
            'business_name' => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:500',
        ]);

        Reseller::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'phone'         => $request->phone,
            'business_name' => $request->business_name,
            'address'       => $request->address,
            'status'        => 'pending',
        ]);

        return redirect()->route('reseller.login')
            ->with('success', 'রেজিস্ট্রেশন সফল হয়েছে! অ্যাডমিন অ্যাকাউন্ট যাচাই ও অনুমোদন করার পর আপনি লগইন করতে পারবেন।');
    }

    /* ── Login ────────────────────────────── */
    public function showLogin()
    {
        if (auth('reseller')->check()) return redirect()->route('reseller.dashboard');
        return view('reseller.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('reseller')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $reseller = auth('reseller')->user();

        if ($reseller->isPending()) {
            Auth::guard('reseller')->logout();
            return back()->withErrors(['email' => 'আপনার অ্যাকাউন্টটি এখনো অনুমোদনের অপেক্ষায় রয়েছে। অ্যাডমিন অনুমোদন করার পর লগইন করতে পারবেন।'])->withInput();
        }

        if (!$reseller->isActive()) {
            Auth::guard('reseller')->logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        $request->session()->regenerate();
        return redirect()->route('reseller.dashboard');
    }

    /* ── Logout ───────────────────────────── */
    public function logout(Request $request)
    {
        Auth::guard('reseller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('reseller.login')->with('success', 'Logged out successfully.');
    }
}
