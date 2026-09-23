<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorAuthController extends Controller
{
    /**
     * Show the moderator login form
     */
    public function showLogin()
    {
        if (Auth::guard('moderator')->check()) {
            return redirect()->route('moderator.dashboard');
        }

        return view('moderator.auth.login');
    }

    /**
     * Handle a moderator login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('moderator')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $moderator = Auth::guard('moderator')->user();

            if (!$moderator->isActive()) {
                Auth::guard('moderator')->logout();
                return back()->with('error', 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় (Inactive) রয়েছে। দয়া করে অ্যাডমিনের সাথে যোগাযোগ করুন।');
            }

            return redirect()->intended(route('moderator.dashboard'))
                ->with('success', "স্বাগতম, {$moderator->name}!");
        }

        return back()->withErrors([
            'email' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।',
        ])->onlyInput('email');
    }

    /**
     * Log the moderator out
     */
    public function logout(Request $request)
    {
        Auth::guard('moderator')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('moderator.login')->with('success', 'সফলভাবে লগআউট হয়েছে।');
    }
}
