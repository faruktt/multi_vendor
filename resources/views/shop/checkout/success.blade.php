@extends('shop.layout')
@section('title', 'Order Confirmed — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full')

@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">Order Confirmation</span>
@endsection

@section('content')
<div class="max-w-[1440px] mx-auto px-4 py-4">
<div class="max-w-lg mx-auto py-6 text-center">
    <div class="w-16 h-16 bg-brand-light rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-check-circle text-brand text-3xl"></i>
    </div>
    <h1 class="text-xl font-extrabold tracking-tight text-gray-900 mb-1">Order Placed Successfully!</h1>
    <p class="text-gray-500 text-sm mb-6">Thank you, {{ $sale->customer?->name }}. We'll contact you shortly to confirm delivery.</p>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 text-left space-y-3 mb-6">
        <div class="flex justify-between text-sm border-b border-gray-100 pb-3">
            <span class="text-gray-500">Invoice No</span>
            <span class="font-mono font-bold text-gray-900">{{ $sale->invoice_no }}</span>
        </div>

        <div class="space-y-2">
            @foreach($sale->saleItems as $item)
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ $item->product?->name ?? 'N/A' }}@if($item->variant_name) ({{ $item->variant_name }})@endif &times;{{ $item->quantity }}</span>
                <span class="font-semibold text-gray-800">{{ $currency }} {{ number_format($item->subtotal, 0) }}</span>
            </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 pt-3 space-y-1.5 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Subtotal</span><span>{{ $currency }} {{ number_format($sale->subtotal, 0) }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Delivery ({{ $sale->delivery_zone === 'inside' ? 'Inside Dhaka' : ($sale->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }})</span>
                <span>{{ $currency }} {{ number_format($sale->delivery_charge, 0) }}</span>
            </div>
            <div class="flex justify-between font-extrabold text-gray-900 border-t border-gray-100 pt-2 mt-1">
                <span>Total</span><span>{{ $currency }} {{ number_format($sale->total, 0) }}</span>
            </div>
        </div>

        @if($sale->district || $sale->customer?->address)
        <div class="border-t border-gray-100 pt-3 text-xs text-gray-600">
            <p class="font-bold text-gray-800 mb-0.5"><i class="fas fa-location-dot text-brand mr-1"></i> Delivery Address:</p>
            <p>{{ implode(', ', array_filter([$sale->customer?->address, $sale->thana, $sale->district])) }}</p>
        </div>
        @endif
    </div>

    <div class="flex gap-3">
        <a href="{{ route('shop.track.index') }}?invoice={{ $sale->invoice_no }}"
           class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
            Track Order
        </a>
        <a href="{{ route('root') }}"
           class="flex-1 bg-brand hover:bg-brand-dark text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
            Continue Shopping
        </a>
    </div>
</div>
</div>
@endsection
