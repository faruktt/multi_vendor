@extends('layouts.app')
@section('title','Sales Report')
@section('heading','Sales Report')
@php
    $currency      = $appSettings['currency'] ?? '৳';
    $globalOrders  = collect($branchSummary)->sum('orders');
    $globalRevenue = collect($branchSummary)->sum('revenue');
    $globalDue     = collect($branchSummary)->sum('due');
    $globalPaid    = $globalRevenue - $globalDue;
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')

{{-- ══ KPI CARDS ══ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Orders</p>
            <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-receipt text-blue-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($globalOrders) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">all branches · all time</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Revenue</p>
            <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center">
                <i class="fas fa-chart-line text-violet-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $currency }}{{ number_format($globalRevenue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">gross sales</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide">Collected</p>
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-circle-check text-emerald-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-emerald-600">{{ $currency }}{{ number_format($globalPaid,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">
            {{ $globalRevenue > 0 ? round(($globalPaid/$globalRevenue)*100) : 0 }}% collection rate
        </p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-red-500 uppercase tracking-wide">Outstanding</p>
            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-circle-exclamation text-red-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-red-500">{{ $currency }}{{ number_format($globalDue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">total due</p>
    </div>
</div>

{{-- ══ BRANCH PERFORMANCE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
    <p class="text-[12px] font-bold text-slate-700 mb-4">Branch Performance</p>
    <div class="space-y-3">
        @foreach($branchSummary as $idx => $s)
        @php
            $c = $colors[$idx % count($colors)];
            $pct = $globalRevenue > 0 ? round(($s['revenue'] / $globalRevenue) * 100) : 0;
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                    <a href="{{ route('admin.sales-report') }}?vendor_id={{ $s['vendor']->id }}"
                       class="text-[12.5px] font-semibold text-slate-700 hover:text-blue-600 transition-colors">{{ $s['vendor']->name }}</a>
                </div>
                <div class="flex items-center gap-4 text-right">
                    <span class="text-[11px] text-slate-400 hidden md:inline">{{ number_format($s['orders']) }} orders</span>
                    <span class="text-[12.5px] font-bold text-slate-800">{{ $currency }}{{ number_format($s['revenue'],0) }}</span>
                    @if($s['due'] > 0)
                    <span class="text-[11.5px] font-semibold text-red-400">{{ $currency }}{{ number_format($s['due'],0) }} due</span>
                    @endif
                    <span class="text-[11px] text-slate-400 w-8">{{ $pct }}%</span>
                </div>
            </div>
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%; background:{{ $c }}"></div>
            </div>
        </div>
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
        <a href="{{ route('admin.sales-report') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <button type="button" onclick="window.print()"
                class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2">
            <i class="fas fa-print text-xs"></i> Print
        </button>
        <span class="ml-auto text-[12px] text-slate-400">{{ $sales->total() }} records</span>
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="lg:hidden flex items-center justify-between px-3.5 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100/60 text-[11px] text-blue-700 font-medium">
        <span class="flex items-center gap-1.5"><i class="fas fa-arrows-left-right text-blue-500 animate-pulse"></i> Scroll horizontally to view all details</span>
        <span class="text-blue-500/80 font-mono text-[10px]">{{ $sales->total() }} records</span>
    </div>
    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Date</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Invoice</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Customer</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Paid</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Due</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($sales as $sale)
                @php
                    $stBg = match($sale->payment_status) {
                        'paid'    => 'bg-emerald-100 text-emerald-700',
                        'partial' => 'bg-amber-100 text-amber-700',
                        default   => 'bg-red-100 text-red-600',
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors {{ $sale->payment_status === 'paid' ? '' : ($sale->payment_status === 'partial' ? 'border-l-2 border-l-amber-300' : 'border-l-2 border-l-red-300') }}">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $sales->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-[11.5px] text-slate-600">{{ $sale->created_at->format('d M Y') }}</p>
                        <p class="text-[10.5px] text-slate-400">{{ $sale->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800 text-[12.5px] font-mono">{{ $sale->invoice_no }}</td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $sale->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-700 text-[12.5px] hidden md:table-cell">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $currency }}{{ number_format($sale->total,0) }}</td>
                    <td class="px-4 py-3 text-right text-emerald-600 text-[12.5px] hidden lg:table-cell">{{ $currency }}{{ number_format($sale->paid_amount,0) }}</td>
                    <td class="px-4 py-3 text-right hidden lg:table-cell">
                        @if($sale->due_amount > 0)
                        <span class="font-semibold text-red-500">{{ $currency }}{{ number_format($sale->due_amount,0) }}</span>
                        @else<span class="text-slate-300">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $stBg }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors" title="View">
                                <i class="fas fa-eye text-[11px]"></i>
                            </a>
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}?auto_print=1" target="_blank"
                               class="w-7 h-7 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-700 flex items-center justify-center transition-colors" title="Print">
                                <i class="fas fa-print text-[11px]"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-16 text-center">
                        <i class="fas fa-chart-bar text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No sales records found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($sales->count() > 0)
            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-[12px] font-semibold text-slate-500">
                        Showing {{ $sales->count() }} of {{ $sales->total() }} records
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">
                        {{ $currency }}{{ number_format($sales->sum('total'),0) }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-emerald-600 hidden lg:table-cell">
                        {{ $currency }}{{ number_format($sales->sum('paid_amount'),0) }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-red-500 hidden lg:table-cell">
                        {{ $currency }}{{ number_format($sales->sum('due_amount'),0) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($sales->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $sales->links() }}</div>
    @endif
</div>

@endsection
