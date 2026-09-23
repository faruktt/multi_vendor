<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // Branch-level staff management
    public function index(Vendor $branch)
    {
        $users = User::where('vendor_id', $branch->id)
            ->where('id', '!=', auth()->id())
            ->with('roles')
            ->latest()
            ->paginate(20);

        $roles = Role::whereNotIn('name', ['super-admin'])->get();

        return view('users.index', compact('branch', 'users', 'roles'));
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

    // Admin-level cross-branch staff management
    public function adminIndex(Request $request)
    {
        $query = User::with(['roles', 'vendor'])
            ->whereNotNull('vendor_id');

        if ($request->filled('branch')) {
            $query->where('vendor_id', $request->branch);
        }

        $users = $query->latest()->paginate(30);

        $roles    = Role::whereNotIn('name', ['super-admin'])->get();
        $branches = Vendor::orderBy('name')->get();

        return view('admin.staff', compact('users', 'roles', 'branches'));
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
            'role'      => 'required|exists:roles,name',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'vendor_id' => $request->vendor_id,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', 'Staff member added.');
    }

    public function adminUpdate(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:6',
            'role'      => 'required|exists:roles,name',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $user->update(['name' => $request->name, 'email' => $request->email, 'vendor_id' => $request->vendor_id]);
        if ($request->password) $user->update(['password' => Hash::make($request->password)]);
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Staff member updated.');
    }

    public function adminDestroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', 'Cannot delete yourself.');
        $user->delete();
        return back()->with('success', 'Staff member deleted.');
    }

    /** How much this staff member has sold, and what they've earned at their branch's commission rate. */
    public function commission(User $user)
    {
        $user->load('vendor', 'roles');
        $commissionRate = (float) ($user->vendor?->commission_percentage ?? 0);

        $salesQuery = Sale::withoutGlobalScopes()->where('created_by', $user->id);

        $totalCount  = (clone $salesQuery)->count();
        $totalAmount = (float) (clone $salesQuery)->sum('total');

        $monthQuery   = (clone $salesQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        $monthCount   = $monthQuery->count();
        $monthAmount  = (float) (clone $monthQuery)->sum('total');

        $totals = [
            'count'          => $totalCount,
            'amount'         => $totalAmount,
            'month_count'    => $monthCount,
            'month_amount'   => $monthAmount,
            'commission_rate'   => $commissionRate,
            'commission_total' => round($totalAmount * $commissionRate / 100, 2),
            'commission_month' => round($monthAmount * $commissionRate / 100, 2),
        ];

        // Last 6 months, oldest first, for a quick trend view.
        $monthly = collect(range(5, 0))->map(function ($i) use ($salesQuery, $commissionRate) {
            $date  = now()->subMonths($i);
            $count = (clone $salesQuery)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
            $sum   = (float) (clone $salesQuery)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->sum('total');

            return [
                'label'      => $date->format('M Y'),
                'count'      => $count,
                'amount'     => $sum,
                'commission' => round($sum * $commissionRate / 100, 2),
            ];
        });

        return view('admin.staff-commission', compact('user', 'totals', 'monthly'));
    }
}
