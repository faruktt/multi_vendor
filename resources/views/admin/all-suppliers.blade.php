@extends('layouts.app')
@section('title','All Suppliers')
@section('heading','All Suppliers')
@php
    $currency   = $appSettings['currency'] ?? '৳';
    $totalSup   = collect($branchSummary)->sum('count');
    $totalSpent = collect($branchSummary)->sum('total');
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')
<div x-data="suppliersAdminPage()">

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Suppliers</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalSup) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">across all branches</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Purchased</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $currency }}{{ number_format($totalSpent,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">all time</p>
    </div>
</div>

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.all-suppliers') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[185px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div>
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[120px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ $s['count'] }}</span> suppliers
                    &nbsp;·&nbsp;<span class="font-bold text-slate-700">{{ $currency }}{{ number_format($s['total'],0) }}</span>
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
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-suppliers') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <div class="ml-auto flex items-center gap-3">
            <span class="text-[12px] text-slate-400 hidden sm:inline">{{ $suppliers->total() }} suppliers</span>
            <a href="{{ route('admin.warehouse.suppliers.index', 'warehouse') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
                <i class="fas fa-plus text-xs"></i> Add Supplier
            </a>
        </div>
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Supplier</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Phone</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Orders</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Total Purchased</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Due</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($suppliers as $s)
                @php
                    $initials = strtoupper(substr($s->name,0,2));
                    $avatarColors = ['bg-orange-500','bg-teal-500','bg-indigo-500','bg-pink-500','bg-lime-600','bg-sky-500'];
                    $av = $avatarColors[$loop->index % count($avatarColors)];
                    $hasDue = ($s->total_due ?? 0) > 0;
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors {{ $hasDue ? 'border-l-2 border-l-orange-300' : '' }}">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $suppliers->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl {{ $av }} flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0">
                                {{ $initials }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 text-[12.5px]">{{ $s->name }}</p>
                                @if($s->email)<p class="text-[10.5px] text-slate-400">{{ $s->email }}</p>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $s->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-[12.5px] hidden md:table-cell">{{ $s->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-bold text-slate-700 hidden md:table-cell">{{ $s->purchases_count }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800 hidden lg:table-cell">
                        {{ $currency }}{{ number_format($s->total_purchase ?? 0, 0) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($hasDue)
                        <span class="font-bold text-orange-500 text-[12.5px]">{{ $currency }}{{ number_format($s->total_due, 0) }}</span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Report --}}
                            <a href="{{ route('admin.warehouse.suppliers.report', ['branch' => 'warehouse', 'supplier' => $s]) }}"
                               class="w-7 h-7 rounded-lg bg-indigo-100 hover:bg-indigo-200 text-indigo-700 flex items-center justify-center transition-colors" title="Report">
                                <i class="fas fa-chart-line text-[11px]"></i>
                            </a>
                            {{-- Edit --}}
                            <button @click="openEdit({{ json_encode(['id'=>$s->id,'vendor_id'=>$s->vendor_id,'name'=>$s->name,'phone'=>$s->phone,'email'=>$s->email,'address'=>$s->address]) }})"
                                    class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </button>
                            {{-- Delete --}}
                            <button @click="openDelete({{ $s->id }}, {{ $s->vendor_id }}, '{{ addslashes($s->name) }}')"
                                    class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors" title="Delete">
                                <i class="fas fa-trash text-[11px]"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <i class="fas fa-industry text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No suppliers found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $suppliers->links() }}</div>
    @endif
</div>

{{-- ══ EDIT MODAL ══ --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.outside="editOpen = false">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-industry text-amber-600 text-sm"></i>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800">Edit Supplier</h3>
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
            <h3 class="text-[15px] font-bold text-slate-800 mb-1">Remove Supplier?</h3>
            <p class="text-[12.5px] text-slate-500 mb-5">
                <span class="font-semibold text-slate-700" x-text="deleteName"></span> will be removed from the system.
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
function suppliersAdminPage() {
    return {
        editOpen: false, editUrl: '', editName: '', editPhone: '', editEmail: '', editAddress: '',
        deleteOpen: false, deleteName: '', deleteUrl: '',
        openEdit(s) {
            this.editName    = s.name    || '';
            this.editPhone   = s.phone   || '';
            this.editEmail   = s.email   || '';
            this.editAddress = s.address || '';
            this.editUrl     = `{{ route('admin.warehouse.suppliers.update', ['branch' => 'warehouse', 'supplier' => ':id']) }}`.replace(':id', s.id);
            this.editOpen    = true;
        },
        openDelete(id, vendor, name) {
            this.deleteName = name;
            this.deleteUrl  = `{{ route('admin.warehouse.suppliers.destroy', ['branch' => 'warehouse', 'supplier' => ':id']) }}`.replace(':id', id);
            this.deleteOpen = true;
        }
    };
}
</script>
@endpush
