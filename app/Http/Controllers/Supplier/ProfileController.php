<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $supplier = auth('supplier')->user();
        return view('supplier.profile', compact('supplier'));
    }

    public function update(Request $request)
    {
        $supplier = auth('supplier')->user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'required|string|max:30',
            'address'      => 'nullable|string|max:500',
            'bank_info'    => 'nullable|string|max:1000',
            'bkash_number' => 'nullable|string|max:30',
            'logo'         => 'nullable|image|max:2048',
        ]);

        $data = [
            'name'         => $request->name,
            'company_name' => $request->company_name ?: $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'bank_info'    => $request->bank_info,
            'bkash_number' => $request->bkash_number,
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'supplier_' . $supplier->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/suppliers'), $filename);
            $data['logo'] = 'suppliers/' . $filename;
        }

        $supplier->update($data);

        return back()->with('success', 'Profile and store settings updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $supplier = auth('supplier')->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $supplier->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $supplier->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
