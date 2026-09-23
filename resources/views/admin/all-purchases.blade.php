@extends('layouts.app')
@section('title','All Purchases')
@section('heading','All Purchases')
@php
    $currency      = $appSettings['currency'] ?? '৳';
    $globalOrders  = collect($branchSummary)->sum('count');
    $globalTotal   = collect($branchSummary)->sum('total');
    $globalDue     = collect($branchSummary)->sum('due');
    $globalPaid    = $globalTotal - $globalDue;
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')
<div x-data="purchaseAdminPage()">

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Purchases</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($globalOrders) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">across all branches</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Purchased</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $currency }}{{ number_format($globalTotal,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">all time</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Paid</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $currency }}{{ number_format($globalPaid,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">{{ $globalTotal > 0 ? round(($globalPaid/$globalTotal)*100) : 0 }}% settled</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Supplier Due</p>
        <p class="text-2xl font-bold text-red-500 mt-1">{{ $currency }}{{ number_format($globalDue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">outstanding</p>
    </div>
</div>

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.all-purchases') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[200px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div>
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[130px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ number_format($s['count']) }}</span> orders
                    &nbsp;·&nbsp;<span class="font-bold text-slate-700">{{ $currency }}{{ number_format($s['total'],0) }}</span>
                    @if($s['due']>0)&nbsp;·&nbsp;<span class="text-red-500">{{ $currency }}{{ number_format($s['due'],0) }} due</span>@endif
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice no..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-36">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="paid"    {{ request('status')=='paid'    ? 'selected':'' }}>Paid</option>
            <option value="partial" {{ request('status')=='partial' ? 'selected':'' }}>Partial</option>
            <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Pending</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-purchases') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <div class="ml-auto flex items-center gap-3">
            <span class="text-[12px] text-slate-400 hidden sm:inline">{{ $purchases->total() }} records</span>
            <a href="{{ route('admin.warehouse.purchases.create', 'warehouse') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
                <i class="fas fa-plus text-xs"></i> New Purchase
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
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Date</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Invoice</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Supplier</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Paid</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Due</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($purchases as $p)
                @php
                    $stBg = match($p->payment_status) {
                        'paid'    => 'bg-emerald-100 text-emerald-700',
                        'partial' => 'bg-amber-100 text-amber-700',
                        default   => 'bg-red-100 text-red-600',
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $purchases->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-[11.5px] text-slate-600">{{ $p->created_at->format('d M Y') }}</p>
                        <p class="text-[10.5px] text-slate-400">{{ $p->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800 text-[12.5px] font-mono">{{ $p->invoice_no }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $p->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700 text-[12.5px] hidden md:table-cell">{{ $p->supplier->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $currency }}{{ number_format($p->total,0) }}</td>
                    <td class="px-4 py-3 text-right text-emerald-600 text-[12.5px] hidden lg:table-cell">{{ $currency }}{{ number_format($p->paid_amount,0) }}</td>
                    <td class="px-4 py-3 text-right hidden lg:table-cell">
                        @if($p->due_amount > 0)
                        <span class="font-semibold text-red-500 text-[12.5px]">{{ $currency }}{{ number_format($p->due_amount,0) }}</span>
                        @else<span class="text-slate-300">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $stBg }}">
                            {{ ucfirst($p->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            {{-- View --}}
                            <a href="{{ route('admin.warehouse.purchases.show', ['branch' => 'warehouse', 'purchase' => $p]) }}"
                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors" title="View">
                                <i class="fas fa-eye text-[11px]"></i>
                            </a>
                            {{-- Print --}}
                            <a href="{{ route('admin.warehouse.purchases.show', ['branch' => 'warehouse', 'purchase' => $p]) }}?auto_print=1" target="_blank"
                               class="w-7 h-7 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-700 flex items-center justify-center transition-colors" title="Print">
                                <i class="fas fa-print text-[11px]"></i>
                            </a>
                            {{-- Edit --}}
                            <a href="{{ route('admin.warehouse.purchases.edit', ['branch' => 'warehouse', 'purchase' => $p]) }}"
                               class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </a>
                            {{-- Pay --}}
                            @if($p->due_amount > 0)
                            <button @click="openPay({{ $p->id }}, {{ $p->vendor_id }}, {{ $p->due_amount }})"
                                    class="w-7 h-7 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 flex items-center justify-center transition-colors" title="Add Payment">
                                <i class="fas fa-money-bill-wave text-[11px]"></i>
                            </button>
                            @endif
                            {{-- Delete --}}
                            <button @click="openDelete({{ $p->id }}, {{ $p->vendor_id }}, '{{ addslashes($p->invoice_no) }}')"
                                    class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors" title="Delete">
                                <i class="fas fa-trash text-[11px]"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-16 text-center">
                        <i class="fas fa-shopping-cart text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No purchases found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $purchases->links() }}</div>
    @endif
</div>

{{-- ══ PAY MODAL ══ --}}
<div x-show="payOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="payOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.outside="payOpen = false">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-emerald-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-[14px] font-bold text-slate-800">Supplier Payment</h3>
                <p class="text-[11.5px] text-slate-400" x-text="'Max due: {{ $currency }}' + payMax.toLocaleString()"></p>
            </div>
        </div>
        <form :action="payUrl" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" x-model="payAmount" step="0.01" min="0.01" :max="payMax" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Payment Method</label>
                <select name="payment_method" x-model="payMethod"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="payOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Confirm Payment</button>
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
            <h3 class="text-[15px] font-bold text-slate-800 mb-1">Delete Purchase?</h3>
            <p class="text-[12.5px] text-slate-500 mb-5">
                Invoice <span class="font-mono font-bold text-slate-700" x-text="deleteName"></span> will be permanently removed.
            </p>
        </div>
        <form :action="deleteUrl" method="POST" class="flex gap-3">
            @csrf @method('DELETE')
            <button type="button" @click="deleteOpen = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Delete</button>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
function purchaseAdminPage() {
    return {
        payOpen: false, payMax: 0, payAmount: 0, payMethod: 'cash', payUrl: '',
        deleteOpen: false, deleteName: '', deleteUrl: '',
        openPay(id, vendor, due) {
            this.payMax    = due;
            this.payAmount = due;
            this.payMethod = 'cash';
            this.payUrl    = `{{ route('admin.warehouse.purchases.payment', ['branch' => 'warehouse', 'purchase' => ':id']) }}`.replace(':id', id);
            this.payOpen   = true;
        },
        openDelete(id, vendor, invoice) {
            this.deleteName = invoice;
            this.deleteUrl  = `{{ route('admin.warehouse.purchases.destroy', ['branch' => 'warehouse', 'purchase' => ':id']) }}`.replace(':id', id);
            this.deleteOpen = true;
        }
    };
}
</script>
@endpush
