<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('vendor');
        return response()->json($this->transformUser($user));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'email'          => 'sometimes|email|unique:users,email,' . $user->id,
            'vendor_name'    => 'sometimes|string|max:255',
            'vendor_address' => 'nullable|string|max:500',
            'vendor_phone'   => 'nullable|string|max:50',
            'system_name'    => 'nullable|string|max:255',
            'logo'           => 'nullable|image|max:2048',
        ]);

        if ($request->filled('name'))  $user->name  = $request->name;
        if ($request->filled('email')) $user->email = $request->email;
        $user->save();

        if ($user->vendor) {
            $v = $user->vendor;
            if ($request->has('vendor_name'))    $v->name        = $request->vendor_name;
            if ($request->has('vendor_address')) $v->address     = $request->vendor_address;
            if ($request->has('vendor_phone'))   $v->phone       = $request->vendor_phone;
            if ($request->has('system_name'))    $v->system_name = $request->system_name;

            if ($request->hasFile('logo')) {
                if ($v->logo) Storage::disk('uploads')->delete($v->logo);
                $v->logo = $request->file('logo')->store('logos', 'uploads');
            }

            $v->save();
        }

        $user = $user->fresh()->load('vendor');
        return response()->json($this->transformUser($user));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    private function transformUser($user)
    {
        $data = $user->toArray();
        if (!empty($data['vendor']['logo'])) {
            $data['vendor']['logo'] = Storage::disk('uploads')->url($data['vendor']['logo']);
        }
        return $data;
    }
}
