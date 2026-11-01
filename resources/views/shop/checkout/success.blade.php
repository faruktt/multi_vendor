@extends('shop.layout')
@section('title', 'Order Confirmed — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full')

@php
    $currency = $appSettings['currency'] ?? '৳';
    $rawPhone = preg_replace('/\D/', '', $appSettings['phone'] ?? '');
    $whatsappNumber = $rawPhone ? '880' . ltrim($rawPhone, '0') : null;
    $customerPhone = $sale->customer?->phone ?? $sale->customer_phone;
@endphp

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">Order Confirmation</span>
@endsection

@section('content')
<div class="max-w-[1440px] mx-auto px-4 py-4 sm:py-8">
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- ══ TOP CELEBRATION HERO ══ --}}
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-200/80 p-5 sm:p-6 text-center shadow-sm">
            {{-- Ambient background decorative accents --}}
            <div class="absolute -top-16 -left-16 w-44 h-44 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -right-16 w-44 h-44 bg-brand/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center">
                {{-- Animated Success Checkmark Ring --}}
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-emerald-100 flex items-center justify-center ring-8 ring-emerald-50 shadow-inner mb-3 transition-transform hover:scale-105">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Order Placed Successfully</span>
                </div>
            </div>
        </div>

        {{-- ══ IMPORTANT NOTICE & POLICIES CARD (REQUESTED TEXT) ══ --}}
        <div class="rounded-3xl bg-gradient-to-br from-emerald-50/60 via-white to-amber-50/40 border-2 border-emerald-300/80 p-5 sm:p-7 shadow-sm space-y-5">

            {{-- 1. Confirmation Highlight --}}
            <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-emerald-600 text-white shadow-sm">
                <span class="text-2xl flex-shrink-0 leading-none">📌</span>
                <div class="text-sm sm:text-base font-extrabold leading-relaxed">
                    ধন্যবাদ! আপনার অর্ডারটি সফলভাবে কনফার্ম হয়েছে। অনুগ্রহ করে পার্সেলটি গ্রহণ করবেন।
                </div>
            </div>

            {{-- 2. Return Policy --}}
            <div class="rounded-2xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-2xs">
                <div class="flex items-center gap-2 text-sm sm:text-base font-black text-gray-900 mb-1.5">
                    <span class="text-lg">🔄</span>
                    <span>রিটার্ন নীতিমালা:</span>
                </div>
                <p class="text-xs sm:text-sm text-gray-700 font-semibold leading-relaxed pl-7">
                    যদি কোনো কারণে পার্সেলটি রিটার্ন করেন, তবে ডেলিভারি চার্জ প্রদান করে রিটার্ন করতে হবে।
                </p>
            </div>

            {{-- 3. Delivery Charges Breakdown --}}
            <div class="rounded-2xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                    <div class="flex items-center gap-2 text-sm sm:text-base font-black text-gray-900">
                        <span class="text-lg">🚚</span>
                        <span>ডেলিভারি চার্জ:</span>
                    </div>
                    <span class="text-[11px] font-bold text-gray-400">অর্ডার অনুযায়ী প্রযোজ্য চার্জ</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Inside Dhaka --}}
                    <div class="relative p-3.5 rounded-2xl border transition-all text-center {{ $sale->delivery_zone === 'inside' ? 'border-brand bg-brand-light/50 ring-2 ring-brand/30 shadow-xs' : 'border-gray-200 bg-gray-50/60 hover:bg-white' }}">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-brand-dark flex items-center justify-center mx-auto mb-1.5 text-xs">
                            <i class="fas fa-city"></i>
                        </div>
                        <div class="text-xs text-gray-600 font-bold">ঢাকা সিটির মধ্যে</div>
                        <div class="text-lg sm:text-xl font-black text-brand-dark mt-0.5">৮০ টাকা</div>
                        @if($sale->delivery_zone === 'inside')
                            <span class="inline-block mt-1 text-[10px] font-black uppercase tracking-wider text-brand bg-white px-2 py-0.5 rounded-full border border-brand/30 shadow-2xs">
                                ✓ আপনার জোন
                            </span>
                        @endif
                    </div>

                    {{-- Sub Dhaka --}}
                    <div class="relative p-3.5 rounded-2xl border transition-all text-center {{ $sale->delivery_zone === 'sub_dhaka' ? 'border-brand bg-brand-light/50 ring-2 ring-brand/30 shadow-xs' : 'border-gray-200 bg-gray-50/60 hover:bg-white' }}">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center mx-auto mb-1.5 text-xs">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="text-xs text-gray-600 font-bold">ঢাকা উপজেলার মধ্যে</div>
                        <div class="text-lg sm:text-xl font-black text-brand-dark mt-0.5">১২০ টাকা</div>
                        @if($sale->delivery_zone === 'sub_dhaka')
                            <span class="inline-block mt-1 text-[10px] font-black uppercase tracking-wider text-brand bg-white px-2 py-0.5 rounded-full border border-brand/30 shadow-2xs">
                                ✓ আপনার জোন
                            </span>
                        @endif
                    </div>

                    {{-- Outside Dhaka --}}
                    <div class="relative p-3.5 rounded-2xl border transition-all text-center {{ $sale->delivery_zone === 'outside' ? 'border-brand bg-brand-light/50 ring-2 ring-brand/30 shadow-xs' : 'border-gray-200 bg-gray-50/60 hover:bg-white' }}">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-1.5 text-xs">
                            <i class="fas fa-map-location-dot"></i>
                        </div>
                        <div class="text-xs text-gray-600 font-bold">ঢাকা সিটির বাইরে</div>
                        <div class="text-lg sm:text-xl font-black text-brand-dark mt-0.5">১৫০ টাকা</div>
                        @if($sale->delivery_zone === 'outside')
                            <span class="inline-block mt-1 text-[10px] font-black uppercase tracking-wider text-brand bg-white px-2 py-0.5 rounded-full border border-brand/30 shadow-2xs">
                                ✓ আপনার জোন
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ ORDER DETAILS RECEIPT ══ --}}
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <i class="fas fa-receipt text-brand text-sm"></i>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-gray-900">Order Summary</h2>
                    <span class="font-mono text-xs font-bold text-gray-700 bg-white border border-gray-200 px-2.5 py-0.5 rounded-full shadow-2xs">Invoice #{{ $sale->invoice_no }}</span>
                </div>
                <span class="text-xs text-gray-500 font-semibold">
                    {{ $sale->created_at ? $sale->created_at->format('d M Y, h:i A') : now()->format('d M Y') }}
                </span>
            </div>

            <div class="p-5 sm:p-6 space-y-4">
                {{-- Product Items List --}}
                <div class="divide-y divide-gray-100">
                    @foreach($sale->saleItems as $item)
                    @php
                        $itemImg = $item->product?->first_image_url;
                    @endphp
                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-center gap-3.5">
                        {{-- Thumbnail --}}
                        <div class="w-14 h-16 rounded-xl bg-gray-50 border border-gray-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center p-1">
                            @if($itemImg)
                                <img src="{{ $itemImg }}" alt="{{ $item->product?->name }}" class="max-w-full max-h-full object-contain">
                            @else
                                <i class="fas fa-box text-gray-300 text-lg"></i>
                            @endif
                        </div>

                        {{-- Item info --}}
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs sm:text-sm font-bold text-gray-900 truncate">
                                {{ $item->product?->name ?? 'Product' }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                @if($item->variant_name)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-gray-100 text-gray-700">
                                        {{ $item->variant_name }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-500 font-semibold">
                                    Qty: <strong class="text-gray-800">{{ $item->quantity }}</strong> &times; {{ $currency }}{{ number_format($item->price, 0) }}
                                </span>
                            </div>
                        </div>

                        {{-- Subtotal --}}
                        <div class="text-right flex-shrink-0">
                            <span class="font-extrabold text-xs sm:text-sm text-gray-900">
                                {{ $currency }} {{ number_format($item->subtotal, 0) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Price Calculations --}}
                <div class="border-t border-gray-100 pt-4 space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>আইটেম সাবটোটাল (Subtotal)</span>
                        <span class="font-bold text-gray-800">{{ $currency }} {{ number_format($sale->subtotal, 0) }}</span>
                    </div>

                    <div class="flex justify-between text-gray-600">
                        <span>
                            ডেলিভারি চার্জ
                            <span class="text-[11px] text-gray-400">
                                ({{ $sale->delivery_zone === 'inside' ? 'ঢাকা সিটি' : ($sale->delivery_zone === 'sub_dhaka' ? 'ঢাকা উপজেলা' : 'ঢাকা সিটির বাইরে') }})
                            </span>
                        </span>
                        <span class="font-bold text-gray-800">{{ $currency }} {{ number_format($sale->delivery_charge, 0) }}</span>
                    </div>

                    @if($sale->discount > 0)
                    <div class="flex justify-between text-emerald-600 font-semibold">
                        <span>কুপন ডিসকাউন্ট {{ $sale->coupon_code ? '(' . $sale->coupon_code . ')' : '' }}</span>
                        <span>- {{ $currency }} {{ number_format($sale->discount, 0) }}</span>
                    </div>
                    @endif

                    {{-- Total Banner --}}
                    <div class="flex justify-between items-center text-sm sm:text-base font-black text-gray-900 bg-brand-light/60 border border-brand/20 p-3.5 rounded-2xl mt-2">
                        <span class="text-brand-dark">সর্বমোট প্রদেয় বিল (Total Payable)</span>
                        <span class="text-lg sm:text-xl text-brand font-black">{{ $currency }} {{ number_format($sale->total, 0) }}</span>
                    </div>

                    <div class="text-right text-[11px] text-gray-500 font-semibold pt-1">
                        <i class="fas fa-hand-holding-dollar text-emerald-600 mr-1"></i> পেমেন্ট মেথড: <strong>ক্যাশ অন ডেলিভারি (Cash on Delivery)</strong>
                    </div>
                </div>

                {{-- Shipping & Customer Details --}}
                @if($sale->district || $sale->customer?->address || $sale->customer_address || $sale->thana)
                <div class="border-t border-gray-100 pt-4 bg-gray-50/70 -mx-5 -mb-5 sm:-mx-6 sm:-mb-6 p-4 sm:p-5 rounded-b-3xl space-y-2">
                    <div class="text-xs font-black uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                        <i class="fas fa-truck-fast text-brand"></i>
                        <span>ডেলিভারি ঠিকানা ও গ্রাহকের তথ্য</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-gray-600 pt-1">
                        <div>
                            <span class="text-gray-400 block text-[11px]">প্রাপকের নাম:</span>
                            <strong class="text-gray-900 text-sm">{{ $sale->customer?->name ?? $sale->customer_name }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">মোবাইল নাম্বার:</span>
                            <strong class="text-gray-900 text-sm font-mono">{{ $customerPhone }}</strong>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-gray-400 block text-[11px]">ঠিকানা:</span>
                            <p class="text-gray-800 font-medium leading-relaxed">
                                {{ implode(', ', array_filter([$sale->customer?->address ?? $sale->customer_address, $sale->thana, $sale->district])) }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ══ ACTION BUTTONS ══ --}}
        <div class="space-y-3 pt-2">
            <div class="flex flex-col sm:flex-row gap-3">
                @if($customerPhone)
                <a href="{{ route('shop.track.index') }}?phone={{ $customerPhone }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 h-12 bg-white border-2 border-gray-300 hover:border-brand-dark text-gray-800 hover:text-brand-dark rounded-2xl text-sm font-bold shadow-2xs hover:shadow-md transition-all">
                    <i class="fas fa-truck text-xs"></i>
                    <span>অর্ডার ট্র্যাক করুন (Track Order)</span>
                </a>
                @endif

                <a href="{{ route('root') }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 h-12 bg-brand hover:bg-brand-dark active:scale-95 text-white rounded-2xl text-sm font-extrabold shadow-md shadow-brand/20 transition-all">
                    <i class="fas fa-bag-shopping text-xs"></i>
                    <span>আরও কেনাকাটা করুন (Continue Shopping)</span>
                </a>
            </div>

            {{-- WhatsApp Support Button --}}
            @if($whatsappNumber)
            <div class="text-center pt-2">
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode('Hello, I have placed an order with Invoice #' . $sale->invoice_no . '. Can you please confirm it?') }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-xs font-bold text-gray-600 hover:text-[#25D366] transition-colors py-1 px-3">
                    <i class="fab fa-whatsapp text-[#25D366] text-base"></i>
                    <span>অর্ডার সম্পর্কে কোনো জিজ্ঞাসা থাকলে WhatsApp-এ মেসেজ দিন</span>
                </a>
            </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
@php
    $saleTrackingItems = ($sale->saleItems ?? collect())->map(function($item) {
        $prod = is_array($item) ? ($item['product'] ?? null) : ($item->product ?? null);
        $prodId = is_array($item) ? ($item['product_id'] ?? '') : ($item->product_id ?? '');
        $price = is_array($item) ? ($item['price'] ?? 0) : ($item->price ?? 0);
        $qty = is_array($item) ? ($item['qty'] ?? 1) : ($item->qty ?? 1);
        return [
            'id'       => $prodId,
            'name'     => $prod?->name ?? 'Product',
            'price'    => (float) $price,
            'quantity' => (int) $qty,
            'category' => $prod?->category?->name ?? '',
        ];
    })->values()->all();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.trackPurchase === 'function') {
        window.trackPurchase({
            transaction_id: '{{ $sale->invoice_no }}',
            value: {{ (float) $sale->total }},
            tax: 0,
            shipping: {{ (float) ($sale->delivery_charge ?? 0) }},
            currency: 'BDT',
            items: @json($saleTrackingItems)
        });
    }
});
</script>
@endpush
@endsection
