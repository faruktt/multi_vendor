@extends('layouts.app')
@section('title', 'Branch Report')
@section('heading', 'Branch Report')
@php
    $currency = $appSettings['currency'] ?? '৳';
    $pct = fn($now, $prev) => $prev > 0 ? round((($now - $prev) / $prev) * 100) : ($now > 0 ? 100 : 0);
    $presets = [
        'today'      => 'Today',
        'yesterday'  => 'Yesterday',
        'this_week'  => 'This Week',
        'last_week'  => 'Last Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year'  => 'This Year',
        'all_time'   => 'All Time',
    ];
@endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
@media print {
    @page { size: A4; margin: 12mm; }
    body * { visibility: hidden !important; }
    #print-area, #print-area * { visibility: visible !important; }
    #print-area { position: absolute !important; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
@endpush

@section('content')

{{-- ── Date Range Toolbar ───────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 mb-4 no-print">
    <div class="flex flex-wrap gap-2 items-center">
        {{-- Preset buttons --}}
        <div class="flex flex-wrap gap-1.5">
            @foreach($presets as $key => $label)
            <a href="{{ route('branch.reports.index', array_merge([$branch->id], ['preset' => $key])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 transition-all
                      {{ $preset === $key && !request()->hasAny(['from','to'])
                         ? 'bg-blue-600 border-blue-600 text-white'
                         : 'border-slate-200 text-slate-500 hover:border-blue-400 hover:text-blue-600 bg-white' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Custom range --}}
        <form method="GET" class="flex items-center gap-2 ml-auto">
            <span class="text-xs text-slate-400 font-medium">Custom:</span>
            <input type="date" name="from" value="{{ $from }}"
                   class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <span class="text-slate-300 text-xs">→</span>
            <input type="date" name="to" value="{{ $to }}"
                   class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors">
                Apply
            </button>
        </form>

        <button onclick="window.print()"
                class="flex items-center gap-1.5 border border-slate-200 text-slate-500 hover:bg-slate-50 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors no-print">
            <i class="fas fa-print text-[10px]"></i> Print
        </button>
    </div>

    {{-- Active period label --}}
    <div class="mt-2 pt-2 border-t border-slate-50 flex items-center gap-2">
        <i class="fas fa-calendar text-slate-400 text-[10px]"></i>
        <p class="text-[11px] text-slate-400">
            Showing data for:
            <span class="font-semibold text-slate-600">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </span>
        </p>
    </div>
</div>

<div id="print-area">

{{-- ══ ROW 1: Big Stats ════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">

    {{-- Sales Revenue --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-line text-blue-600 text-sm"></i>
            </div>
            @php $sp = $pct($sales['revenue'], $sales['prev_revenue']); @endphp
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $sp >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $sp >= 0 ? '+' : '' }}{{ $sp }}%
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-800 mt-2">{{ $currency }}{{ number_format($sales['revenue'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Sales Revenue</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $sales['orders'] }} orders · avg {{ $currency }}{{ number_format($sales['avg_order'], 0) }}</p>
    </div>

    {{-- Purchases --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center mb-2">
            <i class="fas fa-truck text-purple-600 text-sm"></i>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $currency }}{{ number_format($purchases['total_period'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Purchases</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $purchases['orders_period'] }} orders this period</p>
    </div>

    {{-- Gross Profit --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-10 h-10 rounded-xl {{ $grossProfit >= 0 ? 'bg-emerald-100' : 'bg-orange-100' }} flex items-center justify-center mb-2">
            <i class="fas fa-coins text-sm {{ $grossProfit >= 0 ? 'text-emerald-600' : 'text-orange-500' }}"></i>
        </div>
        <p class="text-2xl font-bold {{ $grossProfit >= 0 ? 'text-emerald-600' : 'text-orange-500' }}">
            {{ $grossProfit >= 0 ? '' : '-' }}{{ $currency }}{{ number_format(abs($grossProfit), 0) }}
        </p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Gross Profit</p>
        <p class="text-[10.5px] text-slate-400 mt-1">Revenue − Purchases</p>
    </div>

    {{-- Outstanding Due --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center mb-2">
            <i class="fas fa-clock text-red-500 text-sm"></i>
        </div>
        <p class="text-2xl font-bold text-red-500">{{ $currency }}{{ number_format($sales['total_due'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Customer Due</p>
        <p class="text-[10.5px] text-slate-400 mt-1">Supplier due: {{ $currency }}{{ number_format($purchases['due_all'], 0) }}</p>
    </div>
</div>

{{-- ══ ROW 2: Secondary Stats ══════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-4">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <p class="text-[11px] text-slate-400 font-medium">Paid (Period)</p>
        <p class="text-lg font-bold text-emerald-600">{{ $currency }}{{ number_format($sales['paid'], 0) }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <p class="text-[11px] text-slate-400 font-medium">Due (Period)</p>
        <p class="text-lg font-bold text-red-500">{{ $currency }}{{ number_format($sales['due_period'], 0) }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <p class="text-[11px] text-slate-400 font-medium">Finance Income</p>
        <p class="text-lg font-bold text-emerald-600">{{ $currency }}{{ number_format($finance['income'], 0) }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <p class="text-[11px] text-slate-400 font-medium">Finance Expense</p>
        <p class="text-lg font-bold text-red-500">{{ $currency }}{{ number_format($finance['expense'], 0) }}</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <p class="text-[11px] text-slate-400 font-medium">New Customers</p>
        <p class="text-lg font-bold text-blue-600">{{ $customers['new'] }}</p>
        <p class="text-[10px] text-slate-400">of {{ $customers['total'] }} total</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
        <p class="text-[11px] text-slate-400 font-medium">Finance Net</p>
        <p class="text-lg font-bold {{ $finance['net'] >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
            {{ $finance['net'] >= 0 ? '+' : '' }}{{ $currency }}{{ number_format($finance['net'], 0) }}
        </p>
    </div>
</div>

{{-- ══ ROW 3: Chart + Payment Breakdown ═══════════════════════════ --}}
<div class="flex gap-4 mb-4">

    {{-- Sales Chart --}}
    <div class="flex-[2] bg-white rounded-2xl border border-slate-100 shadow-sm p-5 min-w-0">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-chart-area text-blue-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Revenue Trend</h3>
            </div>
            <div class="flex items-center gap-3 text-[11px]">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Revenue</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-400 inline-block"></span> Orders</span>
            </div>
        </div>
        @if(count($salesChart))
        <canvas id="salesChart" height="100"></canvas>
        @else
        <div class="h-32 flex items-center justify-center text-slate-400 text-sm">
            <div class="text-center">
                <i class="fas fa-chart-area text-3xl text-slate-300 mb-2 block"></i>
                No sales data for this period
            </div>
        </div>
        @endif
    </div>

    {{-- Payment status + methods --}}
    <div class="w-[280px] flex-shrink-0 space-y-3">

        {{-- Donut --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-circle-half-stroke text-slate-500 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Payment Status</h3>
            </div>
            @if($paymentStatus->count())
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 flex-shrink-0">
                    <canvas id="paymentChart"></canvas>
                </div>
                <div class="space-y-2 flex-1">
                    @foreach($paymentStatus as $item)
                    @php
                        $bc = match(strtolower($item['name'])) {
                            'paid'    => 'bg-emerald-500',
                            'partial' => 'bg-amber-400',
                            'pending' => 'bg-red-400',
                            default   => 'bg-slate-400',
                        };
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $bc }} flex-shrink-0"></span>
                            <span class="text-[11.5px] text-slate-600">{{ $item['name'] }}</span>
                        </div>
                        <span class="text-[12px] font-bold text-slate-800">{{ $item['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-4">No data</p>
            @endif
        </div>

        {{-- Payment methods --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-wallet text-blue-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Payment Methods</h3>
            </div>
            @forelse($paymentMethods as $pm)
            @php
                $methodColors = [
                    'cash'  => 'text-emerald-600 bg-emerald-50',
                    'bkash' => 'text-pink-600 bg-pink-50',
                    'nagad' => 'text-orange-600 bg-orange-50',
                    'card'  => 'text-blue-600 bg-blue-50',
                    'due'   => 'text-red-500 bg-red-50',
                ];
                $mc = $methodColors[$pm->name] ?? 'text-slate-600 bg-slate-50';
            @endphp
            <div class="flex items-center justify-between py-1.5">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold capitalize px-2 py-0.5 rounded-lg {{ $mc }}">{{ $pm->name }}</span>
                    <span class="text-[10.5px] text-slate-400">{{ $pm->orders }} orders</span>
                </div>
                <span class="text-[12px] font-bold text-slate-700">{{ $currency }}{{ number_format($pm->revenue, 0) }}</span>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-3">No data</p>
            @endforelse
        </div>

    </div>
</div>

{{-- ══ ROW 4: Top Products + Inventory + Customers ════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Top Products --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                <i class="fas fa-star text-amber-500 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Top Selling Products</h3>
            <span class="ml-auto text-[11px] text-slate-400">Period: {{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
        </div>
        @if($topProducts->count())
        @php $maxRev = $topProducts->max('revenue'); @endphp
        <div class="divide-y divide-slate-50">
            @foreach($topProducts as $i => $p)
            <div class="px-5 py-2.5 flex items-center gap-3">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold flex-shrink-0
                     {{ $i===0 ? 'bg-amber-100 text-amber-600' : ($i===1 ? 'bg-slate-200 text-slate-600' : ($i===2 ? 'bg-orange-100 text-orange-500' : 'bg-slate-100 text-slate-500')) }}">
                    {{ $i+1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-[12.5px] font-semibold text-slate-800 truncate">{{ $p->name }}</p>
                        <p class="text-[12px] font-bold text-blue-600 flex-shrink-0">{{ $currency }}{{ number_format($p->revenue, 0) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-400 rounded-full" style="width:{{ $maxRev > 0 ? round(($p->revenue/$maxRev)*100) : 0 }}%"></div>
                        </div>
                        <span class="text-[10.5px] text-slate-400 flex-shrink-0">{{ number_format($p->total_sold) }} sold</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-12 text-center text-slate-400">
            <i class="fas fa-box-open text-3xl text-slate-300 mb-2 block"></i>
            No sales in this period
        </div>
        @endif
    </div>

    {{-- Inventory + Customer Summary --}}
    <div class="space-y-3">

        {{-- Inventory --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-boxes-stacked text-indigo-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Inventory</h3>
                <a href="{{ route('branch.stock.index', $branch) }}"
                   class="ml-auto text-[10.5px] text-blue-600 hover:underline">View →</a>
            </div>
            <div class="divide-y divide-slate-50">
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Total Products</span>
                    <span class="text-[13px] font-bold text-slate-800">{{ $inventory['total'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Out of Stock</span>
                    <span class="text-[13px] font-bold text-red-500">{{ $inventory['out_of_stock'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Low Stock (≤10)</span>
                    <span class="text-[13px] font-bold text-amber-500">{{ $inventory['low_stock'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Stock Value</span>
                    <span class="text-[13px] font-bold text-indigo-600">{{ $currency }}{{ number_format($inventory['stock_value'], 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Customers & Suppliers --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-cyan-100 flex items-center justify-center">
                    <i class="fas fa-users text-cyan-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Customers</h3>
                <a href="{{ route('branch.customers.index', $branch) }}"
                   class="ml-auto text-[10.5px] text-blue-600 hover:underline">View →</a>
            </div>
            <div class="divide-y divide-slate-50">
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Total Customers</span>
                    <span class="text-[13px] font-bold text-slate-800">{{ $customers['total'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">New (Period)</span>
                    <span class="text-[13px] font-bold text-emerald-600">+{{ $customers['new'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">With Outstanding Due</span>
                    <span class="text-[13px] font-bold {{ $customers['with_due'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $customers['with_due'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Total Suppliers</span>
                    <span class="text-[13px] font-bold text-purple-600">{{ $suppliersDue }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Supplier Due</span>
                    <span class="text-[13px] font-bold {{ $purchases['due_all'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $currency }}{{ number_format($purchases['due_all'], 0) }}</span>
                </div>
            </div>
        </div>

        {{-- All-time totals --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-infinity text-emerald-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">All-Time</h3>
            </div>
            <div class="divide-y divide-slate-50">
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Total Revenue</span>
                    <span class="text-[13px] font-bold text-blue-600">{{ $currency }}{{ number_format($sales['total_revenue'], 0) }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Total Orders</span>
                    <span class="text-[13px] font-bold text-slate-800">{{ $sales['total_orders'] }}</span>
                </div>
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[12px] text-slate-600">Total Purchases</span>
                    <span class="text-[13px] font-bold text-purple-600">{{ $currency }}{{ number_format($purchases['total_all'], 0) }}</span>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- ══ ROW 5: Recent Sales ════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-clock-rotate-left text-blue-600 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Recent Sales <span class="text-slate-400 font-normal">({{ $recentSales->count() }})</span></h3>
        </div>
        <a href="{{ route('branch.sales.index', $branch) }}"
           class="text-[11px] text-blue-600 hover:underline no-print">View All Sales →</a>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Invoice</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Customer</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Date</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Paid</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Due</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($recentSales as $sale)
            @php
                $bc = match($sale->payment_status) {
                    'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'partial' => 'bg-amber-100 text-amber-700 border-amber-200',
                    default   => 'bg-red-100 text-red-600 border-red-200',
                };
            @endphp
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3">
                    <a href="{{ route('branch.sales.show', [$branch, $sale]) }}"
                       class="font-mono text-[12px] font-semibold text-blue-600 hover:underline no-print">
                        {{ $sale->invoice_no }}
                    </a>
                    <span class="font-mono text-[12px] font-semibold text-slate-700 hidden print:inline">{{ $sale->invoice_no }}</span>
                </td>
                <td class="px-3 py-3 text-[12px] text-slate-600 hidden md:table-cell">
                    {{ $sale->customer?->name ?? 'Walk-in' }}
                </td>
                <td class="px-3 py-3 text-[12px] text-slate-500 hidden lg:table-cell">
                    {{ $sale->created_at->format('d M Y, h:i A') }}
                </td>
                <td class="px-3 py-3 text-right font-bold text-slate-800">{{ $currency }}{{ number_format($sale->total, 0) }}</td>
                <td class="px-3 py-3 text-right text-emerald-600 font-semibold text-[12px] hidden md:table-cell">{{ $currency }}{{ number_format($sale->paid_amount, 0) }}</td>
                <td class="px-3 py-3 text-right hidden md:table-cell">
                    @if($sale->due_amount > 0)
                    <span class="text-red-500 font-bold text-[12px]">{{ $currency }}{{ number_format($sale->due_amount, 0) }}</span>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="border {{ $bc }} px-2.5 py-1 rounded-lg text-[10.5px] font-semibold capitalize">{{ $sale->payment_status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-slate-400">No sales in this period</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</div>{{-- #print-area --}}

@push('scripts')
<script>
@if(count($salesChart))
const salesData = @json($salesChart);
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: salesData.map(d => d.label),
        datasets: [
            {
                label: 'Revenue',
                data: salesData.map(d => d.revenue),
                backgroundColor: 'rgba(59,130,246,0.15)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y',
            },
            {
                label: 'Orders',
                data: salesData.map(d => d.orders),
                type: 'line',
                borderColor: '#a78bfa',
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointBackgroundColor: '#a78bfa',
                pointRadius: 4,
                tension: 0.4,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ctx.dataset.label === 'Revenue'
                        ? ' {{ $currency }}' + ctx.parsed.y.toLocaleString()
                        : ' ' + ctx.parsed.y + ' orders'
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { font: { size: 11 }, callback: v => '{{ $currency }}' + v.toLocaleString() }
            },
            y1: {
                beginAtZero: true, position: 'right',
                grid: { drawOnChartArea: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});
@endif

@if($paymentStatus->count())
const paymentData = @json($paymentStatus->values());
new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: paymentData.map(d => d.name),
        datasets: [{
            data: paymentData.map(d => d.value),
            backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#94a3b8'],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: true,
        cutout: '72%',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => ' ' + c.label + ': ' + c.raw } } }
    }
});
@endif
</script>
@endpush

@endsection
