<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('vendor_id', $request->user()->vendor_id)
            ->where('id', '!=', $request->user()->id)
            ->with('roles')
            ->latest()
            ->get()
            ->map(fn($u) => array_merge($u->toArray(), ['roles' => $u->getRoleNames()]));

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:cashier,staff',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'vendor_id' => $request->user()->vendor_id,
        ]);

        $user->assignRole($validated['role']);

        return response()->json(
            array_merge($user->toArray(), ['roles' => $user->getRoleNames()]),
            201
        );
    }

    public function update(Request $request, User $user)
    {
        if ($user->vendor_id !== $request->user()->vendor_id) abort(403);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role'     => 'sometimes|in:cashier,staff',
        ]);

        $updateData = array_filter([
            'name'  => $validated['name']  ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json(
            array_merge($user->fresh()->toArray(), ['roles' => $user->getRoleNames()])
        );
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->vendor_id !== $request->user()->vendor_id) abort(403);
        if ($user->id === $request->user()->id) abort(403, 'Cannot delete your own account');

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
