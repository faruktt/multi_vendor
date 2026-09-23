<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Incorrect email or password.',
            ]);
        }

        $request->session()->regenerate();

        $user = auth()->user();

        \App\Services\ActivityLogger::logLogin($user);

        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.index');
        }

        return redirect()->route('branch.dashboard', $user->vendor_id);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'vendor_name' => 'required|string|max:255',
            'owner_name'  => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6|confirmed',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string',
        ]);

        $vendor = Vendor::create([
            'name'       => $request->vendor_name,
            'owner_name' => $request->owner_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
        ]);

        $user = User::create([
            'name'      => $request->owner_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'vendor_id' => $vendor->id,
        ]);

        $user->assignRole('vendor-owner');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('branch.dashboard', $vendor->id)->with('success', 'Welcome! Your account has been created.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
