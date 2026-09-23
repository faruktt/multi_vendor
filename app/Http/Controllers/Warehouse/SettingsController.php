<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function index(Vendor $branch)
    {
        $user         = auth()->user();
        $vendor       = $branch;
        $isSuperAdmin = $user->hasRole('super-admin');
        $canManageBusiness = $user->can('manage_users');
        if ($user->image) $user->image_url = Storage::disk('uploads')->url($user->image);
        return view('warehouse.settings.index', compact('branch', 'user', 'vendor', 'isSuperAdmin', 'canManageBusiness'));
    }

    public function updateProfile(Request $request, Vendor $branch)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $user->image;
        if ($request->hasFile('image')) {
            if ($user->image) Storage::disk('uploads')->delete($user->image);
            $imagePath = $request->file('image')->store('avatars', 'uploads');
        }

        $user->update(['name' => $request->name, 'email' => $request->email, 'image' => $imagePath]);

        return back()->with('success', 'Profile updated.');
    }

    public function changePassword(Request $request, Vendor $branch)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed.');
    }

    public function updateBranch(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'owner_name'                    => 'required|string|max:255',
            'email'                         => 'nullable|email|max:255',
            'phone'                         => 'nullable|string|max:20',
            'address'                       => 'nullable|string',
            'system_name'                   => 'nullable|string|max:255',
            'logo'                          => 'nullable|image|max:2048',
        ]);

        $logoPath = $branch->logo;
        if ($request->hasFile('logo')) {
            if ($branch->logo) Storage::disk('uploads')->delete($branch->logo);
            $logoPath = $request->file('logo')->store('logos', 'uploads');
        }

        $branch->update([
            'name'        => $request->name,
            'owner_name'  => $request->owner_name,
            'email'       => $request->email ?? $branch->email,
            'phone'       => $request->phone ?? $branch->phone,
            'address'     => $request->address ?? $branch->address,
            'system_name' => $request->system_name ?? $branch->system_name,
            'logo'        => $logoPath,
        ]);

        return back()->with('success', 'Branch information updated.');
    }
}
