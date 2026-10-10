@extends('layouts.app')
@section('title', 'Order & Sales Report')
@section('heading', 'Order & Sales Report')

@php
    $currency = $appSettings['currency'] ?? '৳';
    $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];
@endphp

@section('content')

{{-- ════════════════════════════════════════════════════════════════════════════
     1. TOP HEADER
════════════════════════════════════════════════════════════════════════════ --}}
<div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-slate-200/80 rounded-2xl px-5 py-4 shadow-sm">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base shadow-sm">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div>
            <h1 class="text-lg font-black text-slate-800">Order & Sales Report</h1>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xs text-slate-400">Full Calculation</span>
                <span class="text-slate-300">•</span>
                <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                    Return Shipping Deducted
                </span>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all flex items-center gap-1.5">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('admin.sales-report') }}" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
            <i class="fas fa-rotate"></i> Refresh
        </a>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     2. TOP KPI METRICS
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-5">
    {{-- Total Orders --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Orders</span>
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                <i class="fas fa-boxes-stacked"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-1.5">
            <p class="text-2xl font-black text-slate-800">{{ number_format($globalStats['total_orders']) }}</p>
            <span class="text-xs font-bold text-slate-500">Orders</span>
        </div>
        <p class="text-xs font-extrabold text-blue-600 mt-1 font-mono">
            {{ $currency }}{{ number_format($globalStats['gross_sales'], 2) }}
        </p>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
            <span>Admin: <strong class="text-slate-800">{{ $adminStats['total_orders'] }}</strong></span>
            <span>Supplier: <strong class="text-teal-700">{{ $supplierStats['total_orders'] }}</strong></span>
            <span>Reseller: <strong class="text-purple-700">{{ $resellerStats['total_orders'] }}</strong></span>
        </div>
    </div>

    {{-- Delivered Orders --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Delivered</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-1.5">
            <p class="text-2xl font-black text-emerald-600">{{ number_format($globalStats['delivered_count']) }}</p>
            <span class="text-xs font-bold text-slate-500">Delivered ({{ $globalStats['delivery_rate'] }}%)</span>
        </div>
        <p class="text-xs font-extrabold text-emerald-600 mt-1 font-mono">
            {{ $currency }}{{ number_format($globalStats['delivered_amount'], 2) }}
        </p>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
            <span>Pending: <strong class="text-amber-600">{{ $globalStats['pending_count'] }}</strong></span>
            <span>Cancelled: <strong class="text-slate-600">{{ $globalStats['cancelled_count'] }}</strong></span>
        </div>
    </div>

    {{-- Return Orders & Shipping Loss --}}
    <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-4 bg-gradient-to-br from-white to-rose-50/30">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Returns & Shipping Loss</span>
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                <i class="fas fa-arrow-rotate-left"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-1.5">
            <p class="text-2xl font-black text-rose-600">{{ number_format($globalStats['return_count']) }}</p>
            <span class="text-xs font-bold text-slate-500">Returns ({{ $globalStats['return_rate'] }}%)</span>
        </div>
        <p class="text-xs font-bold text-slate-500 mt-1">
            Order Value: <span class="line-through font-mono">{{ $currency }}{{ number_format($globalStats['return_amount'], 2) }}</span>
        </p>
        <div class="mt-2.5 pt-2 border-t border-rose-100">
            <div class="flex items-center justify-between text-[11.5px] bg-rose-50 text-rose-700 font-bold px-2 py-1 rounded-lg border border-rose-200">
                <span>Shipping Loss:</span>
                <span class="font-black font-mono text-rose-800">-{{ $currency }}{{ number_format($globalStats['return_shipping_charge'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Net Revenue --}}
    <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-4 bg-gradient-to-br from-white to-indigo-50/40">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Net Calculation</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
        </div>
        <div class="flex items-baseline gap-1">
            <p class="text-2xl font-black text-indigo-700 font-mono">{{ $currency }}{{ number_format($globalStats['net_revenue'], 2) }}</p>
        </div>
        <p class="text-[11px] text-slate-500 mt-1 font-medium">
            Delivered Net: <strong class="text-emerald-700 font-mono">{{ $currency }}{{ number_format($globalStats['delivered_net'], 2) }}</strong>
        </p>
        <div class="mt-3 pt-2.5 border-t border-indigo-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
            <span>Collected: <strong class="text-emerald-600 font-mono">{{ $currency }}{{ number_format($globalStats['paid_amount'], 0) }}</strong></span>
            <span>Due: <strong class="text-rose-500 font-mono">{{ $currency }}{{ number_format($globalStats['due_amount'], 0) }}</strong></span>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     3. CALCULATION FORMULAS
════════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
        <i class="fas fa-calculator text-indigo-600"></i> Calculation Formulas
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        {{-- Formula 1 --}}
        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80">
            <div class="text-[11px] font-bold text-slate-600 uppercase mb-1.5">Delivered Net Revenue</div>
            <div class="flex items-center flex-wrap gap-1.5 text-xs font-mono py-1 px-2 bg-white rounded-lg border border-slate-200 text-slate-800">
                <span class="font-bold text-emerald-700">{{ $currency }}{{ number_format($globalStats['delivered_amount'], 2) }}</span>
                <span class="text-slate-400 font-sans">(Delivered)</span>
                <span class="text-rose-600 font-bold font-sans">−</span>
                <span class="font-bold text-rose-600">{{ $currency }}{{ number_format($globalStats['return_shipping_charge'], 2) }}</span>
                <span class="text-slate-400 font-sans">(Return Shipping)</span>
                <span class="text-slate-600 font-bold font-sans">=</span>
                <span class="font-black text-indigo-700 text-sm bg-indigo-50 px-1.5 py-0.5 rounded">{{ $currency }}{{ number_format($globalStats['delivered_net'], 2) }}</span>
            </div>
        </div>

        {{-- Formula 2 --}}
        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80">
            <div class="text-[11px] font-bold text-slate-600 uppercase mb-1.5">Gross Net Order Value</div>
            <div class="flex items-center flex-wrap gap-1.5 text-xs font-mono py-1 px-2 bg-white rounded-lg border border-slate-200 text-slate-800">
                <span class="font-bold text-blue-700">{{ $currency }}{{ number_format($globalStats['gross_sales'], 2) }}</span>
                <span class="text-slate-400 font-sans">(Gross)</span>
                <span class="text-rose-600 font-bold font-sans">−</span>
                <span class="font-bold text-rose-600">{{ $currency }}{{ number_format($globalStats['return_amount'], 2) }}</span>
                <span class="text-slate-400 font-sans">(Returns)</span>
                <span class="text-rose-600 font-bold font-sans">−</span>
                <span class="font-bold text-rose-600">{{ $currency }}{{ number_format($globalStats['return_shipping_charge'], 2) }}</span>
                <span class="text-slate-400 font-sans">(Shipping)</span>
                <span class="text-slate-600 font-bold font-sans">=</span>
                <span class="font-black text-indigo-700 text-sm bg-indigo-50 px-1.5 py-0.5 rounded">{{ $currency }}{{ number_format($globalStats['net_revenue'], 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     4. SOURCE BREAKDOWN: ADMIN VS SUPPLIER VS RESELLER
════════════════════════════════════════════════════════════════════════════ --}}
<div class="mb-5">
    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
        <i class="fas fa-network-wired text-indigo-600"></i> Channel Calculations
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
        {{-- Card 1: Admin In-House --}}
        <div class="bg-white rounded-2xl border border-blue-200/80 shadow-sm p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-crown"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Admin Products</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $adminStats['total_orders'] }} Orders
                    </span>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Gross Total:</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $currency }}{{ number_format($adminStats['gross_sales'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-emerald-600">Delivered:</span>
                        <span class="font-bold text-emerald-700 font-mono">{{ $adminStats['delivered_count'] }} ({{ $currency }}{{ number_format($adminStats['delivered_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-amber-600">Pending / Courier:</span>
                        <span class="font-bold text-amber-700 font-mono">{{ $adminStats['pending_count'] }} ({{ $currency }}{{ number_format($adminStats['pending_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-rose-600">Returns:</span>
                        <span class="font-bold text-rose-600 font-mono">{{ $adminStats['return_count'] }} ({{ $currency }}{{ number_format($adminStats['return_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 px-2 bg-rose-50/80 rounded-lg text-rose-700 border border-rose-100">
                        <span class="font-bold text-[11px]">Return Shipping Loss:</span>
                        <span class="font-black font-mono">-{{ $currency }}{{ number_format($adminStats['return_shipping_charge'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-700">Net Revenue:</span>
                    <span class="font-black text-blue-700 text-sm font-mono">{{ $currency }}{{ number_format($adminStats['net_revenue'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>Paid: {{ $currency }}{{ number_format($adminStats['paid_amount'], 0) }}</span>
                    <span>Due: {{ $currency }}{{ number_format($adminStats['due_amount'], 0) }}</span>
                    <a href="{{ route('admin.sales-report') }}?source=admin" class="text-blue-600 hover:underline font-bold">Filter &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Card 2: Supplier Products --}}
        <div class="bg-white rounded-2xl border border-teal-200/80 shadow-sm p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-industry"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Supplier Products</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        {{ $supplierStats['total_orders'] }} Orders
                    </span>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Gross Total:</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $currency }}{{ number_format($supplierStats['gross_sales'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-emerald-600">Delivered:</span>
                        <span class="font-bold text-emerald-700 font-mono">{{ $supplierStats['delivered_count'] }} ({{ $currency }}{{ number_format($supplierStats['delivered_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-amber-600">Pending / Courier:</span>
                        <span class="font-bold text-amber-700 font-mono">{{ $supplierStats['pending_count'] }} ({{ $currency }}{{ number_format($supplierStats['pending_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-rose-600">Returns:</span>
                        <span class="font-bold text-rose-600 font-mono">{{ $supplierStats['return_count'] }} ({{ $currency }}{{ number_format($supplierStats['return_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 px-2 bg-rose-50/80 rounded-lg text-rose-700 border border-rose-100">
                        <span class="font-bold text-[11px]">Return Shipping Loss:</span>
                        <span class="font-black font-mono">-{{ $currency }}{{ number_format($supplierStats['return_shipping_charge'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-700">Net Revenue:</span>
                    <span class="font-black text-teal-700 text-sm font-mono">{{ $currency }}{{ number_format($supplierStats['net_revenue'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>Paid: {{ $currency }}{{ number_format($supplierStats['paid_amount'], 0) }}</span>
                    <span>Due: {{ $currency }}{{ number_format($supplierStats['due_amount'], 0) }}</span>
                    <a href="{{ route('admin.sales-report') }}?source=supplier" class="text-teal-600 hover:underline font-bold">Filter &rarr;</a>
                </div>
            </div>
        </div>

        {{-- Card 3: Reseller Hub --}}
        <div class="bg-white rounded-2xl border border-purple-200/80 shadow-sm p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-users-line"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Reseller Hub</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        {{ $resellerStats['total_orders'] }} Orders
                    </span>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Gross Total:</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $currency }}{{ number_format($resellerStats['gross_sales'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-emerald-600">Delivered:</span>
                        <span class="font-bold text-emerald-700 font-mono">{{ $resellerStats['delivered_count'] }} ({{ $currency }}{{ number_format($resellerStats['delivered_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-amber-600">Pending / Courier:</span>
                        <span class="font-bold text-amber-700 font-mono">{{ $resellerStats['pending_count'] }} ({{ $currency }}{{ number_format($resellerStats['pending_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-rose-600">Returns:</span>
                        <span class="font-bold text-rose-600 font-mono">{{ $resellerStats['return_count'] }} ({{ $currency }}{{ number_format($resellerStats['return_amount'], 2) }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 px-2 bg-rose-50/80 rounded-lg text-rose-700 border border-rose-100">
                        <span class="font-bold text-[11px]">Return Shipping Loss:</span>
                        <span class="font-black font-mono">-{{ $currency }}{{ number_format($resellerStats['return_shipping_charge'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-2.5 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-slate-700">Net Revenue:</span>
                    <span class="font-black text-purple-700 text-sm font-mono">{{ $currency }}{{ number_format($resellerStats['net_revenue'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>Paid: {{ $currency }}{{ number_format($resellerStats['paid_amount'], 0) }}</span>
                    <span>Due: {{ $currency }}{{ number_format($resellerStats['due_amount'], 0) }}</span>
                    <a href="{{ route('admin.sales-report') }}?source=reseller" class="text-purple-600 hover:underline font-bold">Filter &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     5. RETURNED ORDERS & SHIPPING LOSS TABLE
════════════════════════════════════════════════════════════════════════════ --}}
@if($returnedOrders->count() > 0)
<div class="bg-rose-50/60 rounded-2xl border border-rose-200 p-4 mb-5 shadow-sm">
    <div class="flex items-center justify-between border-b border-rose-200 pb-2.5 mb-3">
        <h3 class="text-xs font-bold text-rose-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fas fa-triangle-exclamation text-rose-600"></i> Returned Orders & Shipping Loss
        </h3>
        <span class="text-xs bg-rose-200 text-rose-900 font-black px-2.5 py-0.5 rounded-full border border-rose-300 font-mono">
            Total Shipping Loss: -{{ $currency }}{{ number_format($globalStats['return_shipping_charge'], 2) }}
        </span>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white border border-rose-200 shadow-sm">
        <table class="w-full text-xs text-left">
            <thead class="bg-rose-100/60 text-rose-900 font-bold border-b border-rose-200">
                <tr>
                    <th class="px-3.5 py-2.5">Date</th>
                    <th class="px-3.5 py-2.5">Invoice</th>
                    <th class="px-3.5 py-2.5">Source</th>
                    <th class="px-3.5 py-2.5">Customer</th>
                    <th class="px-3.5 py-2.5 text-right">Order Value</th>
                    <th class="px-3.5 py-2.5 text-right font-black text-rose-700 bg-rose-100/80">Deducted Shipping</th>
                    <th class="px-3.5 py-2.5 text-center">Status</th>
                    <th class="px-3.5 py-2.5 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-rose-100 font-medium">
                @foreach($returnedOrders as $ret)
                @php
                    $isRes = $ret->channel === 'reseller' || !empty($ret->reseller_id);
                    $isSup = !empty($ret->supplier_id) || $ret->saleItems->contains(fn($i) => !empty($i->supplier_id));
                    $srcBadge = match(true) {
                        $isRes => ['bg' => 'bg-purple-100 text-purple-700', 'label' => 'Reseller: ' . ($ret->reseller->name ?? 'Hub')],
                        $isSup => ['bg' => 'bg-teal-100 text-teal-700', 'label' => 'Supplier: ' . ($ret->supplier->name ?? 'Supplier')],
                        default => ['bg' => 'bg-blue-100 text-blue-700', 'label' => 'Admin'],
                    };
                @endphp
                <tr class="hover:bg-rose-50/40 transition-colors">
                    <td class="px-3.5 py-2 text-slate-500 whitespace-nowrap">{{ $ret->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-3.5 py-2 font-bold font-mono text-slate-800">{{ $ret->invoice_no }}</td>
                    <td class="px-3.5 py-2">
                        <span class="px-2 py-0.5 rounded text-[10.5px] font-bold {{ $srcBadge['bg'] }}">{{ $srcBadge['label'] }}</span>
                    </td>
                    <td class="px-3.5 py-2 text-slate-700">{{ $ret->customer->name ?? 'Customer' }} ({{ $ret->customer->phone ?? '—' }})</td>
                    <td class="px-3.5 py-2 text-right font-bold text-slate-600 line-through font-mono">{{ $currency }}{{ number_format($ret->total, 2) }}</td>
                    <td class="px-3.5 py-2 text-right font-black text-rose-700 font-mono bg-rose-50">
                        -{{ $currency }}{{ number_format($ret->delivery_charge, 2) }}
                    </td>
                    <td class="px-3.5 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 uppercase">
                            Returned
                        </span>
                    </td>
                    <td class="px-3.5 py-2 text-center">
                        <a href="{{ route('branch.sales.show', [$ret->vendor_id, $ret]) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 font-bold underline text-xs">
                            View <i class="fas fa-external-link-alt text-[10px]"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════════
     6. FILTERS
════════════════════════════════════════════════════════════════════════════ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 mb-5">
    <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fas fa-filter text-indigo-500"></i> Filters
        </span>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.sales-report') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            <span class="text-slate-300">|</span>
            <span class="text-xs font-bold text-indigo-600">{{ $sales->total() }} records</span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5">
        <div>
            <label class="block text-[10.5px] font-bold text-slate-500 mb-1">Invoice / Customer</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice / Phone..."
                   class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-[10.5px] font-bold text-slate-500 mb-1">Source</label>
            <select name="source" class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">All Sources</option>
                <option value="admin"    {{ request('source') === 'admin'    ? 'selected' : '' }}>Admin Products</option>
                <option value="supplier" {{ request('source') === 'supplier' ? 'selected' : '' }}>Supplier Products</option>
                <option value="reseller" {{ request('source') === 'reseller' ? 'selected' : '' }}>Reseller Hub</option>
                <option value="return"   {{ request('source') === 'return'   ? 'selected' : '' }}>Returns Only</option>
            </select>
        </div>

        <div>
            <label class="block text-[10.5px] font-bold text-slate-500 mb-1">Branch</label>
            <select name="vendor_id" class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">All Branches</option>
                @foreach($vendors as $v)
                <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10.5px] font-bold text-slate-500 mb-1">Order Status</label>
            <select name="order_status" class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="">All Status</option>
                @foreach($orderStatuses as $os)
                <option value="{{ $os->key }}" {{ request('order_status') === $os->key ? 'selected' : '' }}>{{ $os->label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10.5px] font-bold text-slate-500 mb-1">From Date</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-[10.5px] font-bold text-slate-500 mb-1">To Date</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs font-bold transition-colors shadow-sm flex items-center gap-1.5">
            <i class="fas fa-magnifying-glass"></i> Filter
        </button>
        <button type="button" onclick="window.print()" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</form>

{{-- ════════════════════════════════════════════════════════════════════════════
     7. BRANCH PERFORMANCE
════════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5">
    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5 border-b border-slate-100 pb-2">
        <i class="fas fa-chart-simple text-indigo-500"></i> Branch Performance
    </div>

    <div class="space-y-3">
        @foreach($branchSummary as $idx => $s)
        @php
            $c = $colors[$idx % count($colors)];
            $pct = $globalStats['gross_sales'] > 0 ? round(($s['revenue'] / $globalStats['gross_sales']) * 100) : 0;
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                    <a href="{{ route('admin.sales-report') }}?vendor_id={{ $s['vendor']->id }}"
                       class="text-xs font-bold text-slate-700 hover:text-indigo-600 transition-colors">
                        {{ $s['vendor']->name }}
                    </a>
                </div>
                <div class="flex items-center gap-3 text-right">
                    <span class="text-[11px] text-slate-500 hidden sm:inline">{{ number_format($s['orders']) }} Orders</span>
                    <span class="text-xs font-bold text-slate-800 font-mono">{{ $currency }}{{ number_format($s['revenue'], 2) }}</span>
                    @if($s['return_shipping_charge'] > 0)
                    <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100 font-mono">
                        -{{ $currency }}{{ number_format($s['return_shipping_charge'], 0) }} Loss
                    </span>
                    @endif
                    <span class="text-xs font-extrabold text-indigo-700 font-mono">Net: {{ $currency }}{{ number_format($s['net_revenue'], 0) }}</span>
                    <span class="text-[11px] text-slate-400 font-bold w-9 text-right">{{ $pct }}%</span>
                </div>
            </div>
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%; background:{{ $c }}"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     8. ORDERS TABLE
════════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
    <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-list-check text-indigo-600"></i> Orders List
        </h3>
        <span class="text-xs font-mono font-bold text-slate-500 bg-white px-2 py-0.5 rounded-lg border border-slate-200">
            {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }}
        </span>
    </div>

    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider font-bold text-[11px] border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2.5 text-center w-10">#</th>
                    <th class="px-3 py-2.5 whitespace-nowrap">Date</th>
                    <th class="px-3 py-2.5">Invoice</th>
                    <th class="px-3 py-2.5">Source</th>
                    <th class="px-3 py-2.5">Branch</th>
                    <th class="px-3 py-2.5">Customer</th>
                    <th class="px-3 py-2.5 text-right">Total</th>
                    <th class="px-3 py-2.5 text-right">Delivery Charge</th>
                    <th class="px-3 py-2.5 text-right">Paid</th>
                    <th class="px-3 py-2.5 text-right">Due</th>
                    <th class="px-3 py-2.5 text-center">Status</th>
                    <th class="px-3 py-2.5 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sales as $sale)
                @php
                    $isRes = $sale->channel === 'reseller' || !empty($sale->reseller_id);
                    $isSup = !empty($sale->supplier_id) || $sale->saleItems->contains(fn($i) => !empty($i->supplier_id));
                    $isRet = in_array(strtolower((string)$sale->order_status), ['return', 'returned']);

                    $sourceBadge = match(true) {
                        $isRes => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'fa-users', 'label' => 'Reseller: ' . ($sale->reseller->name ?? 'Hub')],
                        $isSup => ['bg' => 'bg-teal-50 text-teal-700 border-teal-200', 'icon' => 'fa-industry', 'label' => 'Supplier: ' . ($sale->supplier->name ?? 'Supplier')],
                        default => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'fa-crown', 'label' => 'Admin'],
                    };

                    $stBadge = match(strtolower((string)$sale->order_status)) {
                        'delivered', 'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'sent_to_courier'        => 'bg-blue-100 text-blue-800 border-blue-200',
                        'processing'             => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                        'return', 'returned'     => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
                        'cancelled'              => 'bg-slate-100 text-slate-700 border-slate-200',
                        default                  => 'bg-amber-100 text-amber-800 border-amber-200',
                    };
                @endphp
                <tr class="hover:bg-slate-50/70 transition-colors {{ $isRet ? 'bg-rose-50/30' : '' }}">
                    <td class="px-3 py-2 text-center text-slate-400 font-mono">{{ $sales->firstItem() + $loop->index }}</td>
                    <td class="px-3 py-2 text-slate-500 whitespace-nowrap">
                        <p class="font-medium text-slate-700">{{ $sale->created_at->format('d M Y') }}</p>
                        <p class="text-[10px] text-slate-400">{{ $sale->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-3 py-2 font-bold font-mono text-slate-800">{{ $sale->invoice_no }}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold border {{ $sourceBadge['bg'] }}">
                            <i class="fas {{ $sourceBadge['icon'] }} text-[9px]"></i> {{ Str::limit($sourceBadge['label'], 16) }}
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        <span class="text-slate-600 font-semibold">{{ $sale->vendor->name ?? '—' }}</span>
                    </td>
                    <td class="px-3 py-2">
                        <p class="font-medium text-slate-800">{{ $sale->customer->name ?? 'Walk-in' }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $sale->customer->phone ?? '' }}</p>
                    </td>
                    <td class="px-3 py-2 text-right font-bold text-slate-800 font-mono">
                        {{ $currency }}{{ number_format($sale->total, 2) }}
                    </td>
                    <td class="px-3 py-2 text-right font-mono">
                        @if($isRet)
                        <span class="inline-flex items-center gap-0.5 bg-rose-100 text-rose-700 font-black px-1.5 py-0.5 rounded text-[11px] border border-rose-200">
                            -{{ $currency }}{{ number_format($sale->delivery_charge, 2) }}
                        </span>
                        @else
                        <span class="text-slate-600 font-semibold">{{ $currency }}{{ number_format($sale->delivery_charge, 2) }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right font-mono text-emerald-600 font-semibold">
                        {{ $currency }}{{ number_format($sale->paid_amount, 2) }}
                    </td>
                    <td class="px-3 py-2 text-right font-mono">
                        @if($sale->due_amount > 0)
                        <span class="font-bold text-rose-500">{{ $currency }}{{ number_format($sale->due_amount, 2) }}</span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $stBadge }}">
                            {{ ucfirst(str_replace('_', ' ', $sale->order_status ?? 'pending')) }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 flex items-center justify-center transition-colors" title="View Order">
                                <i class="fas fa-eye text-[11px]"></i>
                            </a>
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}?auto_print=1" target="_blank"
                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-purple-50 hover:text-purple-600 text-slate-600 flex items-center justify-center transition-colors" title="Print Invoice">
                                <i class="fas fa-print text-[11px]"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 py-12 text-center text-slate-400">
                        <p class="text-sm font-semibold text-slate-600">No orders found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($sales->count() > 0)
            <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-700">
                <tr>
                    <td colspan="6" class="px-3 py-2.5 text-xs">
                        Page Total ({{ $sales->count() }} Orders):
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-slate-900">
                        {{ $currency }}{{ number_format($sales->sum('total'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-slate-800">
                        {{ $currency }}{{ number_format($sales->sum('delivery_charge'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-emerald-700">
                        {{ $currency }}{{ number_format($sales->sum('paid_amount'), 2) }}
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-rose-600">
                        {{ $currency }}{{ number_format($sales->sum('due_amount'), 2) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($sales->hasPages())
    <div class="p-3 border-t border-slate-100 bg-white">
        {{ $sales->links() }}
    </div>
    @endif
</div>

@endsection
