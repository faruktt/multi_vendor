<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GlobalSettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all();
        $user = auth()->user();
        if ($user->image) $user->image_url = Storage::disk('uploads')->url($user->image);
        return view('settings.global', compact('settings', 'user'));
    }

    public function updateProfile(Request $request)
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

    public function update(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'tagline'         => 'nullable|string|max:255',
            'address'         => 'nullable|string|max:500',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'currency'        => 'nullable|string|max:5',
            'footer_text'     => 'nullable|string|max:500',
            'logo'            => 'nullable|image|max:2048',
            'favicon'         => 'nullable|mimes:ico,png,jpg,jpeg,svg|max:512',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'og_image'        => 'nullable|image|max:2048',
            'facebook'        => 'nullable|string|max:255',
            'youtube'         => 'nullable|string|max:255',
            'twitter'         => 'nullable|string|max:255',
            'instagram'       => 'nullable|string|max:255',
            'tiktok'          => 'nullable|string|max:255',
        ]);

        $data = $request->only('name', 'tagline', 'address', 'phone', 'email', 'currency', 'footer_text', 'seo_title', 'seo_description', 'facebook', 'youtube', 'twitter', 'instagram', 'tiktok');

        if ($request->hasFile('logo')) {
            $old = AppSetting::get('logo_path');
            if ($old) Storage::disk('uploads')->delete($old);
            $path = $request->file('logo')->store('system', 'uploads');
            $data['logo']      = Storage::disk('uploads')->url($path);
            $data['logo_path'] = $path;
        }

        if ($request->hasFile('favicon')) {
            $old = AppSetting::get('favicon_path');
            if ($old) Storage::disk('uploads')->delete($old);
            $path = $request->file('favicon')->store('system', 'uploads');
            $data['favicon']      = Storage::disk('uploads')->url($path);
            $data['favicon_path'] = $path;
        }

        if ($request->hasFile('og_image')) {
            $old = AppSetting::get('og_image_path');
            if ($old) Storage::disk('uploads')->delete($old);
            $path = $request->file('og_image')->store('system', 'uploads');
            $data['og_image']      = Storage::disk('uploads')->url($path);
            $data['og_image_path'] = $path;
        }

        AppSetting::set($data);

        return back()->with('success', 'System settings updated successfully.');
    }

    public function changePassword(Request $request)
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
}
