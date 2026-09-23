@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ══ ROW 1: Quick Stats ══════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">

    {{-- Today Revenue --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-600 text-sm"></i>
            </div>
            @if($todayVsYest !== null)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $todayVsYest >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $todayVsYest >= 0 ? '+' : '' }}{{ $todayVsYest }}%
            </span>
            @endif
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($todayRev, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Today's Revenue</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $todayOrders }} orders today</p>
    </div>

    {{-- This Month --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-chart-line text-emerald-600 text-sm"></i>
            </div>
            @if($monthVsLast !== null)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $monthVsLast >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $monthVsLast >= 0 ? '+' : '' }}{{ $monthVsLast }}%
            </span>
            @endif
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($monthRev, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">This Month</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $monthOrders }} orders</p>
    </div>

    {{-- Customer Due --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-user-clock text-red-500 text-sm"></i>
            </div>
            @if($totalDue > 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg bg-red-100 text-red-600">Due</span>
            @else
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-700">Clear</span>
            @endif
        </div>
        <p class="text-xl font-bold {{ $totalDue > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $currency }}{{ number_format($totalDue, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Customer Due</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $currency }}{{ number_format($totalPaid, 0) }} collected</p>
    </div>

    {{-- Stock / Inventory --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-boxes-stacked text-amber-600 text-sm"></i>
            </div>
            @if($outOfStock > 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg bg-red-100 text-red-600">{{ $outOfStock }} out</span>
            @endif
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($stockValue, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Stock Value</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $totalProducts }} products · {{ number_format($totalStock) }} units</p>
    </div>

</div>

{{-- ══ ROW 2: Secondary Stats ══════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-coins text-purple-600 text-xs"></i>
        </div>
        <div>
            <p class="text-[13px] font-bold text-slate-800">{{ $currency }}{{ number_format($allRevenue, 0) }}</p>
            <p class="text-[10.5px] text-slate-400">All-Time Revenue · {{ number_format($allOrders) }} orders</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-cyan-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-users text-cyan-600 text-xs"></i>
        </div>
        <div>
            <p class="text-[13px] font-bold text-slate-800">{{ $totalCustomers }}</p>
            <p class="text-[10.5px] text-slate-400">Customers · +{{ $newThisMonth }} this month</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-teal-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-truck text-teal-600 text-xs"></i>
        </div>
        <div>
            <p class="text-[13px] font-bold text-slate-800">{{ $currency }}{{ number_format($monthPurchases, 0) }}</p>
            <p class="text-[10.5px] text-slate-400">Month Purchases · {{ $currency }}{{ number_format($supplierDue, 0) }} due</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 flex items-center gap-3">
        @php $finNet = $monthIncome - $monthExpense; @endphp
        <div class="w-8 h-8 rounded-xl {{ $finNet >= 0 ? 'bg-emerald-100' : 'bg-red-100' }} flex items-center justify-center flex-shrink-0">
            <i class="fas fa-scale-balanced {{ $finNet >= 0 ? 'text-emerald-600' : 'text-red-500' }} text-xs"></i>
        </div>
        <div>
            <p class="text-[13px] font-bold {{ $finNet >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                {{ $finNet >= 0 ? '+' : '' }}{{ $currency }}{{ number_format(abs($finNet), 0) }}
            </p>
            <p class="text-[10.5px] text-slate-400">Month Finance Net</p>
        </div>
    </div>

</div>

{{-- ══ ROW 3: Chart + Right Column ════════════════════════════════ --}}
<div class="flex gap-4 mb-4">

    {{-- 30-day chart --}}
    <div class="flex-[3] bg-white rounded-2xl border border-slate-100 shadow-sm p-5 min-w-0">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-[13.5px] font-bold text-slate-800">Revenue — Last 30 Days</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    {{ $currency }}{{ number_format($monthRev, 0) }} this month
                    @if($monthVsLast !== null)
                    <span class="{{ $monthVsLast >= 0 ? 'text-emerald-600' : 'text-red-500' }} font-semibold">
                        ({{ $monthVsLast >= 0 ? '+' : '' }}{{ $monthVsLast }}% vs last)
                    </span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-blue-500 rounded inline-block"></span> Revenue</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-300 inline-block"></span> Orders</span>
            </div>
        </div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    {{-- Right side --}}
    <div class="w-[240px] flex-shrink-0 space-y-3">

        {{-- Payment status --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h3 class="text-[13px] font-bold text-slate-800 mb-0.5">Payment Status</h3>
            <p class="text-[11px] text-slate-400 mb-3">All-time orders</p>
            <canvas id="paymentChart" height="100"></canvas>
            <div class="mt-3 space-y-1.5">
                @php
                    $statusCfg = ['paid'=>['bg-emerald-500','Paid'],'partial'=>['bg-amber-400','Partial'],'pending'=>['bg-red-400','Pending']];
                @endphp
                @foreach($statusCfg as $key => [$color, $label])
                @php $row = $paymentStatus->get($key); @endphp
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-[11.5px] text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $color }} inline-block"></span>
                        {{ $label }}
                    </span>
                    <div class="text-right">
                        <span class="text-[11.5px] font-bold text-slate-800">{{ $row?->cnt ?? 0 }}</span>
                        <span class="text-[10px] text-slate-400 ml-1">{{ $currency }}{{ number_format($row?->rev ?? 0, 0) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-[12px] font-bold text-slate-700 mb-2.5">Quick Actions</p>
            <div class="space-y-2">
                <a href="{{ route('branch.pos.index', $branch) }}"
                   class="flex items-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-cash-register text-sm"></i> New Sale (POS)
                </a>
                <a href="{{ route('branch.customers.index', $branch) }}"
                   class="flex items-center gap-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                    <i class="fas fa-users text-cyan-500 text-sm"></i> Customers
                </a>
                <a href="{{ route('branch.reports.index', $branch) }}"
                   class="flex items-center gap-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors">
                    <i class="fas fa-chart-bar text-amber-500 text-sm"></i> Full Report
                </a>
            </div>
        </div>

    </div>
</div>

{{-- ══ ROW 4: Recent Sales + Top Products ═════════════════════════ --}}
<div class="flex gap-4 mb-4">

    {{-- Recent Sales --}}
    <div class="flex-[3] bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden min-w-0">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-receipt text-blue-600 text-xs"></i>
                </div>
                <h3 class="text-[13.5px] font-bold text-slate-800">Recent Sales</h3>
            </div>
            <a href="{{ route('branch.sales.index', $branch) }}"
               class="text-[11.5px] text-blue-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentSales as $sale)
            @php
                $bc = match($sale->payment_status) {
                    'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'partial' => 'bg-amber-100 text-amber-700 border-amber-200',
                    default   => 'bg-red-100 text-red-600 border-red-200',
                };
            @endphp
            <a href="{{ route('branch.sales.show', [$branch, $sale]) }}"
               class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50/80 transition-colors">
                <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-receipt text-slate-400 text-[10px]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] font-semibold text-slate-800 font-mono">{{ $sale->invoice_no }}</p>
                    <p class="text-[11px] text-slate-400 truncate">
                        {{ $sale->customer?->name ?? 'Walk-in' }}
                        <span class="mx-1">·</span>
                        {{ $sale->created_at->format('d M, h:i A') }}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-[13px] font-bold text-slate-800">{{ $currency }}{{ number_format($sale->total, 0) }}</p>
                    @if($sale->due_amount > 0)
                    <p class="text-[10px] text-red-500 font-medium">{{ $currency }}{{ number_format($sale->due_amount, 0) }} due</p>
                    @endif
                </div>
                <span class="border {{ $bc }} px-2 py-0.5 rounded-lg text-[10px] font-semibold capitalize flex-shrink-0">
                    {{ $sale->payment_status }}
                </span>
            </a>
            @empty
            <div class="px-5 py-10 text-center text-slate-400 text-sm">No sales yet. <a href="{{ route('branch.pos.index', $branch) }}" class="text-blue-600 hover:underline">Create first sale →</a></div>
            @endforelse
        </div>
    </div>

    {{-- Right: Top Products + Payment Methods --}}
    <div class="w-[260px] flex-shrink-0 space-y-3">

        {{-- Top Products --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-[13px] font-bold text-slate-800">Top Products</h3>
                <a href="{{ route('branch.reports.index', $branch) }}" class="text-[11px] text-blue-600">Report →</a>
            </div>
            @php $maxSold = $topProducts->max('total_sold') ?: 1; @endphp
            <div class="space-y-3">
                @forelse($topProducts as $i => $p)
                <div class="flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-lg text-[10px] font-bold flex items-center justify-center flex-shrink-0
                                 {{ $i === 0 ? 'bg-amber-400 text-white' : ($i === 1 ? 'bg-slate-300 text-slate-700' : ($i === 2 ? 'bg-orange-300 text-white' : 'bg-slate-100 text-slate-500')) }}">
                        {{ $i+1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11.5px] font-semibold text-slate-700 truncate">{{ $p->name }}</p>
                        <div class="h-1 bg-slate-100 rounded-full mt-1 overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width:{{ round(($p->total_sold / $maxSold) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[11px] font-bold text-slate-700">{{ $p->total_sold }}<span class="text-slate-400 font-normal text-[10px]"> sold</span></p>
                        <p class="text-[10px] text-slate-400">{{ $currency }}{{ number_format($p->revenue, 0) }}</p>
                    </div>
                </div>
                @empty
                <p class="text-[12px] text-slate-400 text-center py-3">No sales data</p>
                @endforelse
            </div>
        </div>

        {{-- Payment Methods --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h3 class="text-[13px] font-bold text-slate-800 mb-3">Payment Methods</h3>
            @php $colors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444']; @endphp
            <div class="space-y-2">
                @php $maxRev = $paymentMethods->max('rev') ?: 1; @endphp
                @foreach($paymentMethods as $i => $pm)
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-semibold text-slate-500 w-16 truncate capitalize">{{ str_replace('_',' ',$pm->payment_method) }}</span>
                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ round(($pm->rev/$maxRev)*100) }}%; background:{{ $colors[$i % count($colors)] }}"></div>
                    </div>
                    <span class="text-[10.5px] font-bold text-slate-700 w-14 text-right">{{ $currency }}{{ number_format($pm->rev,0) }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

{{-- ══ ROW 5: Low Stock Alert ══════════════════════════════════════ --}}
@if($lowStockItems->count() > 0)
<div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden mb-4">
    <div class="px-5 py-3 border-b border-amber-100 bg-amber-50/50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                <i class="fas fa-triangle-exclamation text-amber-600 text-xs"></i>
            </div>
            <h3 class="text-[13px] font-bold text-amber-800">Stock Alert</h3>
            @if($outOfStock > 0)
            <span class="text-[10px] font-bold bg-red-100 text-red-600 border border-red-200 px-2 py-0.5 rounded-full">{{ $outOfStock }} out of stock</span>
            @endif
            @if($lowStock > 0)
            <span class="text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full">{{ $lowStock }} low stock</span>
            @endif
        </div>
        <a href="{{ route('branch.stock.index', $branch) }}" class="text-[11.5px] text-amber-700 hover:underline font-medium">Manage Stock →</a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-slate-100">
        @foreach($lowStockItems as $item)
        <div class="px-4 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl {{ $item->stock_qty <= 0 ? 'bg-red-100' : 'bg-amber-50' }} flex items-center justify-center flex-shrink-0">
                <i class="fas fa-box text-[11px] {{ $item->stock_qty <= 0 ? 'text-red-500' : 'text-amber-500' }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[12px] font-semibold text-slate-700 truncate">{{ $item->name }}</p>
                <p class="text-[11px] {{ $item->stock_qty <= 0 ? 'text-red-500 font-bold' : 'text-amber-600 font-semibold' }}">
                    {{ $item->stock_qty <= 0 ? 'Out of stock' : $item->stock_qty . ' ' . ($item->unit ?? 'pcs') . ' left' }}
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const currency = '{{ $currency }}';
Chart.defaults.font.family = "'Inter','Segoe UI',system-ui,sans-serif";
Chart.defaults.color = '#94a3b8';

new Chart(document.getElementById('revenueChart'), {
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                type: 'line', label: 'Revenue',
                data: @json($chartRevenue),
                borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.07)',
                borderWidth: 2, tension: 0.4, fill: true,
                pointRadius: 0, pointHoverRadius: 5,
                yAxisID: 'y',
            },
            {
                type: 'bar', label: 'Orders',
                data: @json($chartOrders),
                backgroundColor: 'rgba(167,139,250,.3)',
                borderRadius: 3, yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b', titleColor: '#94a3b8', bodyColor: '#f1f5f9', padding: 10,
                callbacks: {
                    label: ctx => ctx.dataset.label === 'Revenue'
                        ? ' ' + currency + Number(ctx.parsed.y).toLocaleString()
                        : ' ' + ctx.parsed.y + ' orders'
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 11 } } },
            y: { position: 'left', grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 11 }, callback: v => currency + (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v) } },
            y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 } } }
        }
    }
});

const paymentData = @json($paymentStatus);
new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: ['Paid', 'Partial', 'Pending'],
        datasets: [{
            data: [paymentData['paid']?.cnt ?? 0, paymentData['partial']?.cnt ?? 0, paymentData['pending']?.cnt ?? 0],
            backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
            borderWidth: 0, hoverOffset: 4,
        }]
    },
    options: {
        responsive: true, cutout: '72%',
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: '#1e293b' }
        }
    }
});
</script>
@endpush
