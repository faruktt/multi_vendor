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
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'vendor_id' => $branch->id,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'Staff member added.');
    }

    public function update(Request $request, Vendor $branch, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role'     => 'required|exists:roles,name',
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email]);
        if ($request->password) $user->update(['password' => Hash::make($request->password)]);
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
