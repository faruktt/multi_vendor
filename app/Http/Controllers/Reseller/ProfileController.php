<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show reseller profile settings page
     */
    public function index()
    {
        $reseller = auth('reseller')->user();
        return view('reseller.profile.index', compact('reseller'));
    }

    /**
     * Update reseller profile information and photo
     */
    public function update(Request $request)
    {
        $reseller = auth('reseller')->user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:30',
            'business_name' => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:500',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'remove_image'  => 'nullable|boolean',
        ], [
            'name.required' => 'আপনার নাম প্রদান করুন।',
            'image.image'   => 'প্রোফাইল ছবি একটি বৈধ ইমেজ ফাইল হতে হবে।',
            'image.mimes'   => 'ছবি অবশ্যই jpeg, png, jpg, webp বা gif ফরম্যাটের হতে হবে।',
            'image.max'     => 'ছবির সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।',
        ]);

        $updateData = [
            'name'          => $request->name,
            'phone'         => $request->phone,
            'business_name' => $request->business_name,
            'address'       => $request->address,
        ];

        // Handle image removal if requested
        if ($request->boolean('remove_image')) {
            if ($reseller->image && Storage::disk('uploads')->exists($reseller->image)) {
                Storage::disk('uploads')->delete($reseller->image);
            }
            $updateData['image'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($reseller->image && Storage::disk('uploads')->exists($reseller->image)) {
                Storage::disk('uploads')->delete($reseller->image);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'reseller_' . $reseller->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('resellers', $filename, 'uploads');

            $updateData['image'] = $path;
        }

        $reseller->update($updateData);

        return back()->with('success', 'প্রোফাইল তথ্য ও ছবি সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * Change reseller account password
     */
    public function changePassword(Request $request)
    {
        $reseller = auth('reseller')->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড প্রদান করুন।',
            'password.required'         => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'              => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed'        => 'নতুন পাসওয়ার্ডের সাথে কনফার্ম পাসওয়ার্ড মেলেনি।',
        ]);

        if (!Hash::check($request->current_password, $reseller->password)) {
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।']);
        }

        $reseller->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!');
    }
}
