<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ModeratorProfileController extends Controller
{
    /**
     * Show moderator profile edit page
     */
    public function index()
    {
        $moderator = auth('moderator')->user();

        // Calculate summary metrics for the profile badge
        $todayStart = Carbon::today();
        $todaySeconds = (int) $moderator->workSessions()
            ->where('status', 'completed')
            ->whereDate('started_at', $todayStart)
            ->sum('duration_seconds');

        $totalSessions = $moderator->workSessions()->where('status', 'completed')->count();
        $totalSeconds  = $moderator->totalWorkSeconds();

        return view('moderator.profile', compact('moderator', 'todaySeconds', 'totalSessions', 'totalSeconds'));
    }

    /**
     * Update moderator profile details & avatar/image
     */
    public function update(Request $request)
    {
        $moderator = auth('moderator')->user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:500',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        $updateData = [
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        // Handle image removal if requested
        if ($request->boolean('remove_image')) {
            if ($moderator->image && Storage::disk('uploads')->exists($moderator->image)) {
                Storage::disk('uploads')->delete($moderator->image);
            }
            $updateData['image'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete previous image if exists
            if ($moderator->image && Storage::disk('uploads')->exists($moderator->image)) {
                Storage::disk('uploads')->delete($moderator->image);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'moderator_' . $moderator->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('moderators', $filename, 'uploads');

            $updateData['image'] = $path;
        }

        $moderator->update($updateData);

        return back()->with('success', 'প্রোফাইল তথ্য ও ছবি সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * Change moderator account password
     */
    public function changePassword(Request $request)
    {
        $moderator = auth('moderator')->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড প্রদান করুন।',
            'password.required'         => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'              => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed'        => 'নতুন পাসওয়ার্ডের সাথে কনফার্ম পাসওয়ার্ড মেলেনি।',
        ]);

        if (!Hash::check($request->current_password, $moderator->password)) {
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।']);
        }

        $moderator->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!');
    }
}
