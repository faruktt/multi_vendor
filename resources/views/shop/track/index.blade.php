@extends('shop.layout')
@section('title', 'Track Order — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full')

@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">Track Order</span>
@endsection

@section('content')
<div class="max-w-[1440px] mx-auto px-4 py-4">
<div class="max-w-lg mx-auto py-6">
    <h1 class="text-xl font-extrabold tracking-tight text-gray-900 mb-1">Track Your Order</h1>
    <p class="text-sm text-gray-500 mb-5">Enter your invoice number to see the current status.</p>

    <form method="GET" class="flex gap-2 mb-6">
        <input type="text" name="invoice" value="{{ request('invoice') }}" placeholder="e.g. INV-AB12CD34" required
               class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-colors">
            Track
        </button>
    </form>

    @if($searched)
        @if($notFound)
        <div class="bg-white border border-gray-200 rounded-2xl py-10 text-center text-gray-400">
            <i class="fas fa-circle-question text-3xl mb-3 opacity-40"></i>
            <p class="font-medium">No order found with that invoice number.</p>
        </div>
        @else
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Invoice</p>
                    <p class="font-mono font-bold text-gray-900">{{ $sale->invoice_no }}</p>
                </div>
                @php $statusColor = $statusInfo->color ?? 'gray'; @endphp
                <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                    {{ $statusInfo->label ?? ucfirst(str_replace('_', ' ', $sale->order_status)) }}
                </span>
            </div>

            <div class="space-y-2 mb-4">
                @foreach($sale->saleItems as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ $item->product?->name ?? 'N/A' }} &times;{{ $item->quantity }}</span>
                    <span class="font-medium text-gray-800">{{ $currency }} {{ number_format($item->subtotal, 0) }}</span>
                </div>
                @endforeach
            </div>

            <div class="border-t border-gray-100 pt-3 space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span><span>{{ $currency }} {{ number_format($sale->subtotal, 0) }}</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Delivery Charge</span><span>{{ $currency }} {{ number_format($sale->delivery_charge, 0) }}</span>
                </div>
                <div class="border-t border-gray-100 pt-2 mt-1 flex justify-between font-extrabold text-gray-900">
                    <span>Total</span><span class="text-brand-dark">{{ $currency }} {{ number_format($sale->total, 0) }}</span>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
</div>
@endsection
