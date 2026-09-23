@extends('layouts.app')
@section('title', 'Warehouse Dashboard')
@section('heading', 'Warehouse Dashboard')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

<div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl p-4 mb-4 flex items-center gap-3 text-white">
    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-warehouse text-lg"></i>
    </div>
    <div>
        <p class="font-bold text-[14.5px]">{{ $branch->name }}</p>
        <p class="text-[12px] text-orange-50">Central warehouse — purchases products from suppliers and transfers stock to branches. No direct sales here.</p>
    </div>
</div>

{{-- ══ ROW 1: Quick Stats ══════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">

    {{-- Stock value --}}
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

    {{-- Month purchases --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-teal-100 flex items-center justify-center">
                <i class="fas fa-truck text-teal-600 text-sm"></i>
            </div>
            @if($monthVsLast !== null)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $monthVsLast >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $monthVsLast >= 0 ? '+' : '' }}{{ $monthVsLast }}%
            </span>
            @endif
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($monthPurchases, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">This Month's Purchases</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $totalOrders }} orders all-time</p>
    </div>

    {{-- Supplier due --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-hand-holding-dollar text-red-500 text-sm"></i>
            </div>
            @if($supplierDue > 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg bg-red-100 text-red-600">Due</span>
            @else
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-700">Clear</span>
            @endif
        </div>
        <p class="text-xl font-bold {{ $supplierDue > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $currency }}{{ number_format($supplierDue, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Owed to Suppliers</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $currency }}{{ number_format($allPurchases, 0) }} purchased all-time</p>
    </div>

    {{-- Transferred out --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-right-left text-blue-600 text-sm"></i>
            </div>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($monthTransferredQty) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Units Transferred This Month</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ number_format($allTransferredQty) }} units all-time</p>
    </div>

</div>

{{-- ══ ROW 2: Chart + Right Column ════════════════════════════════ --}}
<div class="flex flex-col lg:flex-row gap-4 mb-4">

    {{-- 30-day purchase chart --}}
    <div class="flex-[3] bg-white rounded-2xl border border-slate-100 shadow-sm p-5 min-w-0">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-[13.5px] font-bold text-slate-800">Purchases — Last 30 Days</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $currency }}{{ number_format($monthPurchases, 0) }} this month</p>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-teal-500 rounded inline-block"></span> Amount</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-300 inline-block"></span> Orders</span>
            </div>
        </div>
        <canvas id="purchaseChart" height="100"></canvas>
    </div>

    {{-- Low stock alert --}}
    <div class="w-full lg:w-[280px] flex-shrink-0 bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <h3 class="text-[13px] font-bold text-slate-800 mb-0.5">Low / Out of Stock</h3>
        <p class="text-[11px] text-slate-400 mb-3">{{ $lowStock }} low · {{ $outOfStock }} out</p>
        <div class="space-y-2">
            @forelse($lowStockItems as $item)
            <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl {{ $item->stock_qty <= 0 ? 'bg-red-50' : 'bg-amber-50' }}">
                <p class="text-[12px] font-semibold text-slate-700 truncate">{{ $item->name }}</p>
                <span class="text-[11px] font-bold {{ $item->stock_qty <= 0 ? 'text-red-600' : 'text-amber-600' }} flex-shrink-0">
                    {{ $item->stock_qty }} {{ $item->unit }}
                </span>
            </div>
            @empty
            <p class="text-[12px] text-slate-400 text-center py-6">All products well stocked.</p>
            @endforelse
        </div>
    </div>

</div>

{{-- ══ ROW 3: Recent Purchases + Recent Transfers ══════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <p class="font-bold text-slate-800 text-[13.5px]">Recent Purchases</p>
            <a href="{{ route('admin.warehouse.purchases.index') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentPurchases as $p)
            <div class="px-4 py-3 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[12.5px] font-semibold text-slate-800 truncate">{{ $p->supplier->name ?? 'No supplier' }}</p>
                    <p class="text-[11px] text-slate-400">{{ $p->created_at->format('d M Y') }} · {{ $p->invoice_no }}</p>
                </div>
                <span class="text-[12.5px] font-bold text-slate-800 flex-shrink-0">{{ $currency }}{{ number_format($p->total, 0) }}</span>
            </div>
            @empty
            <div class="px-4 py-10 text-center">
                <i class="fas fa-truck text-slate-200 text-3xl mb-2 block"></i>
                <p class="text-slate-400 text-[12.5px]">No purchases yet</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <p class="font-bold text-slate-800 text-[13.5px]">Recent Transfers Out</p>
            <a href="{{ route('admin.warehouse.transfer') }}" class="text-[11px] font-semibold text-blue-600 hover:underline">Transfer stock</a>
        </div>
        <div class="divide-y divide-slate-50">
            @forelse($recentTransfers as $t)
            <div class="px-4 py-3 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[12.5px] font-semibold text-slate-800 truncate">{{ $t->product->name ?? 'Deleted product' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ Str::after($t->note, ' to ') }} · {{ $t->created_at->format('d M Y') }}</p>
                </div>
                <span class="text-[11px] font-bold text-white bg-blue-500 rounded-full px-2 py-0.5 flex-shrink-0">{{ $t->quantity }}</span>
            </div>
            @empty
            <div class="px-4 py-10 text-center">
                <i class="fas fa-right-left text-slate-200 text-3xl mb-2 block"></i>
                <p class="text-slate-400 text-[12.5px]">No transfers yet</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const currency = '{{ $currency }}';
Chart.defaults.font.family = "'Inter','Segoe UI',system-ui,sans-serif";
Chart.defaults.color = '#94a3b8';

new Chart(document.getElementById('purchaseChart'), {
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                type: 'line', label: 'Amount',
                data: @json($chartAmounts),
                borderColor: '#0d9488', backgroundColor: 'rgba(13,148,136,.07)',
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
                    label: ctx => ctx.dataset.label === 'Amount'
                        ? ' ' + currency + Number(ctx.parsed.y).toLocaleString()
                        : ' ' + ctx.parsed.y + ' orders'
                }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: { position: 'left', grid: { color: '#f1f5f9' } },
            y1: { position: 'right', grid: { display: false }, beginAtZero: true }
        }
    }
});
</script>
@endpush
