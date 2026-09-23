@extends('layouts.app')
@section('title','All Customers')
@section('heading','All Customers')
@php
    $currency     = $appSettings['currency'] ?? '৳';
    $totalCust    = collect($branchSummary)->sum('count');
    $totalRevenue = collect($branchSummary)->sum('revenue');
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')
<div x-data="customersAdminPage()">

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Customers</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalCust) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">across all branches</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Revenue</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $currency }}{{ number_format($totalRevenue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">from all customers</p>
    </div>
</div>

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.all-customers') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[175px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div>
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[115px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ $s['count'] }}</span> customers
                    &nbsp;·&nbsp;<span class="font-bold text-emerald-600">{{ $currency }}{{ number_format($s['revenue'],0) }}</span>
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or phone..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-customers') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <span class="ml-auto text-[12px] text-slate-400">{{ $customers->total() }} customers</span>
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Phone</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Email</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Orders</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Total Spent</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($customers as $c)
                @php
                    $initials = strtoupper(substr($c->name,0,2));
                    $avatarColors = ['bg-blue-500','bg-violet-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-cyan-500'];
                    $av = $avatarColors[$loop->index % count($avatarColors)];
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $customers->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl {{ $av }} flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0">
                                {{ $initials }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 text-[12.5px]">{{ $c->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $c->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-[12.5px] hidden md:table-cell">{{ $c->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500 text-[12px] hidden lg:table-cell">{{ $c->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-slate-700 text-[13px]">{{ $c->sales_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">
                        {{ $currency }}{{ number_format($c->total_spent ?? 0, 0) }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Report --}}
                            <a href="{{ route('branch.customers.report', [$c->vendor_id, $c]) }}"
                               class="w-7 h-7 rounded-lg bg-indigo-100 hover:bg-indigo-200 text-indigo-700 flex items-center justify-center transition-colors" title="Report">
                                <i class="fas fa-chart-line text-[11px]"></i>
                            </a>
                            {{-- Edit --}}
                            <button @click="openEdit({{ json_encode(['id'=>$c->id,'vendor_id'=>$c->vendor_id,'name'=>$c->name,'phone'=>$c->phone,'email'=>$c->email,'address'=>$c->address]) }})"
                                    class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </button>
                            {{-- Delete --}}
                            <button @click="openDelete({{ $c->id }}, {{ $c->vendor_id }}, '{{ addslashes($c->name) }}')"
                                    class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors" title="Delete">
                                <i class="fas fa-trash text-[11px]"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <i class="fas fa-users text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No customers found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $customers->links() }}</div>
    @endif
</div>

{{-- ══ EDIT MODAL ══ --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.outside="editOpen = false">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-user-pen text-amber-600 text-sm"></i>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800">Edit Customer</h3>
        </div>
        <form :action="editUrl" method="POST" class="p-5 space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editName" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label>
                    <input type="text" name="phone" x-model="editPhone"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                    <input type="email" name="email" x-model="editEmail"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Address</label>
                    <input type="text" name="address" x-model="editAddress"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="editOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DELETE MODAL ══ --}}
<div x-show="deleteOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="deleteOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="deleteOpen = false">
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash text-red-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-bold text-slate-800 mb-1">Remove Customer?</h3>
            <p class="text-[12.5px] text-slate-500 mb-5">
                <span class="font-semibold text-slate-700" x-text="deleteName"></span> and their data will be removed.
            </p>
        </div>
        <form :action="deleteUrl" method="POST" class="flex gap-3">
            @csrf @method('DELETE')
            <button type="button" @click="deleteOpen = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Remove</button>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
function customersAdminPage() {
    return {
        editOpen: false, editUrl: '', editName: '', editPhone: '', editEmail: '', editAddress: '',
        deleteOpen: false, deleteName: '', deleteUrl: '',
        openEdit(c) {
            this.editName    = c.name    || '';
            this.editPhone   = c.phone   || '';
            this.editEmail   = c.email   || '';
            this.editAddress = c.address || '';
            this.editUrl     = `/branch/${c.vendor_id}/customers/${c.id}`;
            this.editOpen    = true;
        },
        openDelete(id, vendor, name) {
            this.deleteName = name;
            this.deleteUrl  = `/branch/${vendor}/customers/${id}`;
            this.deleteOpen = true;
        }
    };
}
</script>
@endpush
