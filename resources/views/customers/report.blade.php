@extends('layouts.app')
@section('title', $customer->name . ' — Report')
@section('heading', 'Customer Report')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@push('styles')
<style>
@media print {
    @page { size: A4; margin: 15mm; }
    body * { visibility: hidden !important; }
    #print-area, #print-area * { visibility: visible !important; }
    #print-area { position: absolute !important; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
@endpush

@section('content')

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2.5 mb-5 no-print">
    <a href="{{ route('branch.customers.index', $branch) }}"
       class="flex items-center gap-1.5 border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
    <button onclick="window.print()"
            class="flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
        <i class="fas fa-print text-xs"></i> Print Report
    </button>
    <a href="{{ route('branch.pos.index', $branch) }}"
       class="ml-auto flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm shadow-blue-200 transition-colors">
        <i class="fas fa-plus text-xs"></i> New Sale
    </a>
</div>

<div id="print-area">

{{-- ── Customer Profile Card ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
    <div class="flex items-start gap-4">
        @if($customer->image)
        <img src="{{ Storage::url($customer->image) }}" alt="{{ $customer->name }}"
             class="w-16 h-16 rounded-2xl object-cover flex-shrink-0 border-2 border-slate-100 shadow-sm">
        @else
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 text-white text-2xl font-bold shadow-md"
             style="background: linear-gradient(135deg, #06b6d4, #3b82f6)">
            {{ strtoupper(substr($customer->name, 0, 1)) }}
        </div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $customer->name }}</h2>
                    <div class="flex flex-wrap gap-x-5 gap-y-1.5 mt-2">
                        @if($customer->phone)
                        <span class="flex items-center gap-1.5 text-sm text-slate-500">
                            {{ $customer->phone }}
                            <a href="tel:{{ $customer->whatsapp_number }}" title="Call"
                               class="w-6 h-6 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-phone text-[10px]"></i>
                            </a>
                            <a href="https://wa.me/{{ $customer->whatsapp_number }}" target="_blank" rel="noopener" title="Chat on WhatsApp"
                               class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fab fa-whatsapp text-xs"></i>
                            </a>
                        </span>
                        @endif
                        @if($customer->email)
                        <span class="flex items-center gap-1.5 text-sm text-slate-500">
                            <i class="fas fa-envelope text-[10px] text-slate-400 w-3"></i>{{ $customer->email }}
                        </span>
                        @endif
                        @if($customer->address)
                        <span class="flex items-center gap-1.5 text-sm text-slate-500">
                            <i class="fas fa-map-marker-alt text-[10px] text-slate-400 w-3"></i>{{ $customer->address }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="text-right text-xs text-slate-400">
                    <p>Customer since</p>
                    <p class="font-semibold text-slate-600 text-sm mt-0.5">{{ $customer->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Stats Cards ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-purple-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-receipt text-purple-600 text-xs"></i>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total_orders'] }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Orders</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-shopping-bag text-blue-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($stats['total_spent'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Spent</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-check-circle text-emerald-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-emerald-600">{{ $currency }}{{ number_format($stats['total_paid'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Paid</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-clock text-red-500 text-xs"></i>
        </div>
        <p class="text-xl font-bold {{ $stats['total_due'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $currency }}{{ number_format($stats['total_due'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Due</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center col-span-2 lg:col-span-1">
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-chart-line text-amber-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($stats['avg_order'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Avg. Order</p>
    </div>
</div>

{{-- ── Payment settlement bar ───────────────────────────────────── --}}
@if($stats['total_spent'] > 0)
@php $paidPct = round(($stats['total_paid'] / $stats['total_spent']) * 100); @endphp
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4 mb-4">
    <div class="flex justify-between items-center mb-2">
        <p class="text-xs font-semibold text-slate-600">Payment Settlement</p>
        <p class="text-xs font-bold {{ $paidPct >= 100 ? 'text-emerald-600' : 'text-slate-600' }}">{{ $paidPct }}% paid</p>
    </div>
    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
        <div class="h-full rounded-full transition-all {{ $paidPct >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
             style="width: {{ min($paidPct, 100) }}%"></div>
    </div>
    <div class="flex justify-between text-[11px] text-slate-400 mt-1.5">
        <span>{{ $currency }}{{ number_format($stats['total_paid'], 0) }} paid</span>
        @if($stats['total_due'] > 0)
        <span class="text-red-500 font-medium">{{ $currency }}{{ number_format($stats['total_due'], 0) }} remaining</span>
        @else
        <span class="text-emerald-600 font-medium">Fully settled ✓</span>
        @endif
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- ── Top Products ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center">
                <i class="fas fa-star text-amber-500 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Favourite Products</h3>
        </div>
        @if($topProducts->count())
        <div class="divide-y divide-slate-50">
            @foreach($topProducts as $i => $tp)
            <div class="px-4 py-3 flex items-center gap-3">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold flex-shrink-0
                     {{ $i === 0 ? 'bg-amber-100 text-amber-600' : ($i === 1 ? 'bg-slate-100 text-slate-600' : 'bg-orange-50 text-orange-500') }}">
                    {{ $i + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold text-slate-800 truncate">{{ $tp->name }}</p>
                    <p class="text-[10.5px] text-slate-400">Qty: {{ $tp->total_qty }}</p>
                </div>
                <p class="text-[12px] font-bold text-slate-700 flex-shrink-0">{{ $currency }}{{ number_format($tp->total_revenue, 0) }}</p>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-8 text-center text-slate-400 text-sm">No products yet</div>
        @endif
    </div>

    {{-- ── Payment Methods ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden lg:col-span-2">
        <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-wallet text-blue-600 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Payment Methods Used</h3>
        </div>
        @if($paymentBreakdown->count())
        <div class="p-4 grid grid-cols-2 gap-3">
            @foreach($paymentBreakdown as $method => $data)
            @php
                $colors = [
                    'cash'   => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                    'bkash'  => 'bg-pink-50 border-pink-200 text-pink-700',
                    'nagad'  => 'bg-orange-50 border-orange-200 text-orange-700',
                    'card'   => 'bg-blue-50 border-blue-200 text-blue-700',
                    'due'    => 'bg-red-50 border-red-200 text-red-700',
                ];
                $icons = [
                    'cash'  => 'fa-money-bill-wave',
                    'bkash' => 'fa-mobile-alt',
                    'nagad' => 'fa-mobile-alt',
                    'card'  => 'fa-credit-card',
                    'due'   => 'fa-clock',
                ];
                $color = $colors[$method] ?? 'bg-slate-50 border-slate-200 text-slate-700';
                $icon  = $icons[$method]  ?? 'fa-money-bill';
            @endphp
            <div class="border rounded-xl p-3 {{ $color }}">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas {{ $icon }} text-xs"></i>
                    <span class="text-xs font-bold capitalize">{{ $method }}</span>
                </div>
                <p class="text-base font-bold">{{ $currency }}{{ number_format($data['total'], 0) }}</p>
                <p class="text-[10.5px] opacity-70">{{ $data['count'] }} order{{ $data['count'] != 1 ? 's' : '' }}</p>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-8 text-center text-slate-400 text-sm">No data yet</div>
        @endif
    </div>

</div>

{{-- ── Sales History ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center">
                <i class="fas fa-history text-purple-600 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Sales History ({{ $sales->count() }})</h3>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Invoice</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Date</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Items</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Paid</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Due</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide no-print">View</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($sales as $i => $sale)
            @php
                $bc = match($sale->payment_status) {
                    'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'partial' => 'bg-amber-100 text-amber-700 border-amber-200',
                    default   => 'bg-red-100 text-red-600 border-red-200',
                };
            @endphp
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                <td class="px-3 py-3">
                    <p class="font-mono text-[12px] font-semibold text-slate-800">{{ $sale->invoice_no }}</p>
                    <p class="text-[10.5px] text-slate-400 md:hidden mt-0.5">{{ $sale->created_at->format('d M Y') }}</p>
                </td>
                <td class="px-3 py-3 hidden md:table-cell">
                    <p class="text-[12px] text-slate-600">{{ $sale->created_at->format('d M Y') }}</p>
                    <p class="text-[10.5px] text-slate-400">{{ $sale->created_at->format('h:i A') }}</p>
                </td>
                <td class="px-3 py-3 text-center hidden lg:table-cell">
                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-lg text-[11px] font-semibold">
                        {{ $sale->saleItems->count() }}
                    </span>
                </td>
                <td class="px-3 py-3 text-right font-bold text-slate-800 text-[13px]">{{ $currency }}{{ number_format($sale->total, 0) }}</td>
                <td class="px-3 py-3 text-right text-emerald-600 font-semibold text-[12px] hidden md:table-cell">{{ $currency }}{{ number_format($sale->paid_amount, 0) }}</td>
                <td class="px-3 py-3 text-right hidden md:table-cell">
                    @if($sale->due_amount > 0)
                    <span class="text-red-500 font-bold text-[12px]">{{ $currency }}{{ number_format($sale->due_amount, 0) }}</span>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="border {{ $bc }} px-2.5 py-1 rounded-lg text-[11px] font-semibold capitalize">{{ $sale->payment_status }}</span>
                </td>
                <td class="px-3 py-3 text-center no-print">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('branch.sales.show', [$branch, $sale]) }}"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors" title="View">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                        <a href="{{ route('branch.sales.show', [$branch, $sale]) }}?auto_print=1"
                           target="_blank" title="Print"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-100 transition-colors">
                            <i class="fas fa-print text-xs"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                    <i class="fas fa-receipt text-3xl text-slate-300 mb-3 block"></i>
                    <p>No sales yet for this customer</p>
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($sales->count())
        <tfoot class="bg-slate-50 border-t border-slate-200">
            <tr class="font-bold">
                <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-600 hidden lg:table-cell">Totals</td>
                <td colspan="4" class="px-4 py-3 text-right text-sm text-slate-600 lg:hidden">Totals</td>
                <td class="px-3 py-3 text-right text-blue-600 text-sm">{{ $currency }}{{ number_format($stats['total_spent'], 0) }}</td>
                <td class="px-3 py-3 text-right text-emerald-600 text-sm hidden md:table-cell">{{ $currency }}{{ number_format($stats['total_paid'], 0) }}</td>
                <td class="px-3 py-3 text-right hidden md:table-cell">
                    @if($stats['total_due'] > 0)
                    <span class="text-red-500 text-sm">{{ $currency }}{{ number_format($stats['total_due'], 0) }}</span>
                    @else
                    <span class="text-emerald-600 text-sm">{{ $currency }}0</span>
                    @endif
                </td>
                <td colspan="2" class="no-print"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

</div>{{-- #print-area --}}
@endsection
