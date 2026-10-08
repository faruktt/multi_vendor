<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Vendor $branch)
    {
        $users = User::where('vendor_id', $branch->id)
            ->where('id', '!=', auth()->id())
            ->with('roles')
            ->latest()
            ->paginate(20);

        $roles = Role::whereNotIn('name', ['super-admin'])->get();

        return view('warehouse.staff.index', compact('branch', 'users', 'roles'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users',
            'phone'               => 'nullable|string|max:30',
            'password'            => 'required|min:6',
            'role'                => 'required|exists:roles,name',
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

        $userData = [
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'vendor_id' => $branch->id,
        ];

        if ($request->hasFile('image')) {
            $userData['image'] = $request->file('image')->store('users', 'uploads');
        }
        if ($request->hasFile('nid_front')) {
            $userData['nid_front'] = $request->file('nid_front')->store('user_docs', 'uploads');
        }
        if ($request->hasFile('nid_back')) {
            $userData['nid_back'] = $request->file('nid_back')->store('user_docs', 'uploads');
        }
        if ($request->hasFile('guardian_nid_front')) {
            $userData['guardian_nid_front'] = $request->file('guardian_nid_front')->store('user_docs', 'uploads');
        }
        if ($request->hasFile('guardian_nid_back')) {
            $userData['guardian_nid_back'] = $request->file('guardian_nid_back')->store('user_docs', 'uploads');
        }

        $user = User::create($userData);
        $user->assignRole($request->role);

        return back()->with('success', 'Staff member added.');
    }

    public function update(Request $request, Vendor $branch, User $user)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email,' . $user->id,
            'phone'               => 'nullable|string|max:30',
            'password'            => 'nullable|min:6',
            'role'                => 'required|exists:roles,name',
            'image'               => 'nullable|image|max:5120',
            'nid_front'           => 'nullable|image|max:5120',
            'nid_back'            => 'nullable|image|max:5120',
            'guardian_nid_front'  => 'nullable|image|max:5120',
            'guardian_nid_back'   => 'nullable|image|max:5120',
        ]);

        $updateData = ['name' => $request->name, 'email' => $request->email];
        if ($request->filled('phone')) $updateData['phone'] = $request->phone;
        if ($request->password) $updateData['password'] = Hash::make($request->password);

        if ($request->hasFile('image')) {
            $updateData['image'] = $request->file('image')->store('users', 'uploads');
        }
        if ($request->hasFile('nid_front')) {
            $updateData['nid_front'] = $request->file('nid_front')->store('user_docs', 'uploads');
        }
        if ($request->hasFile('nid_back')) {
            $updateData['nid_back'] = $request->file('nid_back')->store('user_docs', 'uploads');
        }
        if ($request->hasFile('guardian_nid_front')) {
            $updateData['guardian_nid_front'] = $request->file('guardian_nid_front')->store('user_docs', 'uploads');
        }
        if ($request->hasFile('guardian_nid_back')) {
            $updateData['guardian_nid_back'] = $request->file('guardian_nid_back')->store('user_docs', 'uploads');
        }

        $user->update($updateData);
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Staff member updated.');
    }

    public function destroy(Vendor $branch, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'Staff member deleted.');
    }
}
