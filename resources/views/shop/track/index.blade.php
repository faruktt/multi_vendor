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
<div class="max-w-[1440px] mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">

        {{-- ══ TRACKING HEADER ══ --}}
        <div class="text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand-dark flex items-center justify-center mx-auto mb-3 shadow-xs">
                <i class="fas fa-truck-fast text-xl"></i>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Track Your Orders</h1>
            <p class="text-sm text-gray-500 mt-1">
                আপনার মোবাইল নম্বর দিয়ে অর্ডারের বর্তমান অবস্থা এবং ডেলিভারি বিস্তারিত দেখুন।
            </p>
        </div>

        {{-- ══ SEARCH FORM (BY PHONE NUMBER) ══ --}}
        <div class="bg-white border border-gray-200/90 rounded-2xl shadow-sm p-4 sm:p-5 mb-8">
            <form method="GET" action="{{ route('shop.track.index') }}" class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-phone text-sm"></i>
                    </div>
                    <input type="text" name="phone" value="{{ $input ?? request('phone') ?? request('number') ?? request('invoice') }}"
                           placeholder="আপনার মোবাইল নম্বর দিন (যেমনঃ 017XXXXXXXX)" required
                           class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent bg-gray-50/50 focus:bg-white transition-all">
                </div>
                <button type="submit"
                        class="bg-brand hover:bg-brand-dark text-white px-6 py-3 rounded-xl text-sm font-bold shadow-sm shadow-brand/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fas fa-magnifying-glass text-xs"></i>
                    <span>Track Orders</span>
                </button>
            </form>
            <div class="flex items-center gap-2 mt-2.5 text-xs text-gray-400">
                <i class="fas fa-circle-info text-[11px] text-brand"></i>
                <span>মোবাইল নম্বর অথবা ইনভয়েস নম্বর (Invoice No) উভয় দিয়েই ট্র্যাক করতে পারবেন।</span>
            </div>
        </div>

        {{-- ══ SEARCH RESULTS ══ --}}
        @if($searched)
            @if($notFound)
            <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center shadow-xs">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-circle-xmark text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800">কোনো অর্ডার পাওয়া যায়নি</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    "<strong>{{ $input }}</strong>" নম্বরে কোনো অর্ডার খুঁজে পাওয়া যায়নি। দয়া করে সঠিক মোবাইল নম্বর দিয়ে আবার চেষ্টা করুন।
                </p>
            </div>
            @else
            {{-- Summary bar --}}
            <div class="flex items-center justify-between bg-brand/5 border border-brand/20 rounded-xl px-4 py-3 mb-5">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand"></span>
                    <span class="text-xs sm:text-sm font-bold text-gray-800">
                        Orders for: <span class="font-mono text-brand-dark">{{ $input }}</span>
                    </span>
                </div>
                <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-brand text-white shadow-2xs">
                    {{ $sales->count() }} {{ Str::plural('Order', $sales->count()) }} Found
                </span>
            </div>

            {{-- Order List --}}
            <div class="space-y-6">
                @foreach($sales as $sale)
                @php
                    $statusObj = $orderStatuses->get($sale->order_status);
                    $statusLabel = $statusObj->label ?? ucfirst(str_replace('_', ' ', $sale->order_status));
                    $color = $statusObj->color ?? 'gray';

                    // Color mapping for badge
                    $badgeClasses = match($color) {
                        'emerald', 'teal', 'green' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'blue', 'sky', 'indigo'    => 'bg-blue-100 text-blue-800 border-blue-200',
                        'orange', 'amber'          => 'bg-amber-100 text-amber-800 border-amber-200',
                        'purple', 'violet'         => 'bg-purple-100 text-purple-800 border-purple-200',
                        'red', 'rose'              => 'bg-rose-100 text-rose-800 border-rose-200',
                        default                    => 'bg-gray-100 text-gray-800 border-gray-200',
                    };

                    // Stepper calculation
                    $isCancelled = in_array($sale->order_status, ['cancelled', 'return']);
                    $currentStatus = $sale->order_status;
                    $statusHierarchy = [
                        'pending'          => 1,
                        'processing'       => 2,
                        'sent_to_courier'  => 3,
                        'out_for_delivery' => 4,
                        'delivered'        => 5,
                        'completed'        => 5,
                    ];
                    $currentStep = $statusHierarchy[$currentStatus] ?? 1;
                @endphp

                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hover:border-gray-300 transition-colors">
                    {{-- Card Header --}}
                    <div class="bg-gray-50/70 border-b border-gray-100 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white border border-gray-200 text-brand flex items-center justify-center font-bold text-xs shadow-2xs">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-extrabold text-gray-900 text-sm tracking-wide">{{ $sale->invoice_no }}</span>
                                    @if($sale->channel === 'reseller')
                                        <span class="text-[9.5px] uppercase font-bold px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">Reseller</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $sale->created_at->format('d M Y, h:i A') }} ({{ $sale->created_at->diffForHumans() }})
                                </p>
                            </div>
                        </div>

                        {{-- Status Pill --}}
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClasses }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- Stepper Progress Bar (if not cancelled) --}}
                    <div class="px-5 py-4 border-b border-gray-100 bg-white">
                        @if($isCancelled)
                        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-3 flex items-center gap-2.5 text-xs font-bold">
                            <i class="fas fa-triangle-exclamation text-base text-rose-500"></i>
                            <span>এই অর্ডারটি বাতিল অথবা রিটার্ন করা হয়েছে (Order {{ ucfirst($sale->order_status) }})।</span>
                        </div>
                        @else
                        <div class="w-full py-2">
                            <div class="flex items-center justify-between relative">
                                {{-- Background Line --}}
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 z-0 rounded-full"></div>
                                {{-- Active Progress Line --}}
                                @php
                                    $progressPercent = match($currentStep) {
                                        1 => 5,
                                        2 => 30,
                                        3 => 60,
                                        4 => 85,
                                        5 => 100,
                                        default => 10,
                                    };
                                @endphp
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand z-0 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%;"></div>

                                {{-- Step 1: Placed --}}
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 1 ? 'bg-brand text-white shadow-xs' : 'bg-gray-200 text-gray-500' }}">
                                        @if($currentStep > 1) <i class="fas fa-check text-[10px]"></i> @else 1 @endif
                                    </div>
                                    <span class="text-[10.5px] font-bold mt-1.5 text-gray-700">Placed</span>
                                </div>

                                {{-- Step 2: Processing --}}
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 2 ? 'bg-brand text-white shadow-xs' : 'bg-gray-200 text-gray-500' }}">
                                        @if($currentStep > 2) <i class="fas fa-check text-[10px]"></i> @else 2 @endif
                                    </div>
                                    <span class="text-[10.5px] font-bold mt-1.5 {{ $currentStep >= 2 ? 'text-gray-800' : 'text-gray-400' }}">Processing</span>
                                </div>

                                {{-- Step 3: Courier --}}
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 3 ? 'bg-brand text-white shadow-xs' : 'bg-gray-200 text-gray-500' }}">
                                        @if($currentStep > 3) <i class="fas fa-check text-[10px]"></i> @else 3 @endif
                                    </div>
                                    <span class="text-[10.5px] font-bold mt-1.5 {{ $currentStep >= 3 ? 'text-gray-800' : 'text-gray-400' }}">In Courier</span>
                                </div>

                                {{-- Step 4: Out for Delivery --}}
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 4 ? 'bg-brand text-white shadow-xs' : 'bg-gray-200 text-gray-500' }}">
                                        @if($currentStep > 4) <i class="fas fa-check text-[10px]"></i> @else 4 @endif
                                    </div>
                                    <span class="text-[10.5px] font-bold mt-1.5 {{ $currentStep >= 4 ? 'text-gray-800' : 'text-gray-400' }}">On the Way</span>
                                </div>

                                {{-- Step 5: Delivered --}}
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 5 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-200 text-gray-500' }}">
                                        @if($currentStep >= 5) <i class="fas fa-check text-[10px]"></i> @else 5 @endif
                                    </div>
                                    <span class="text-[10.5px] font-bold mt-1.5 {{ $currentStep >= 5 ? 'text-emerald-700' : 'text-gray-400' }}">Delivered</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Courier Tracking Details (if assigned) --}}
                    @if($sale->isSentToCourier())
                    <div class="bg-amber-50/50 border-b border-amber-100 px-5 py-3 flex flex-wrap items-center justify-between gap-2.5 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                                <i class="fas fa-truck-fast text-[11px]"></i>
                            </span>
                            <div>
                                <span class="font-bold text-gray-800">Courier:</span>
                                <span class="font-semibold text-amber-800">{{ $sale->courier_display_name }}</span>
                                @if($sale->courier_tracking_code)
                                    &nbsp;·&nbsp;<span class="text-gray-500">Tracking Code:</span>
                                    <span class="font-mono font-bold text-gray-900 bg-white px-1.5 py-0.5 rounded border border-amber-200">{{ $sale->courier_tracking_code }}</span>
                                @endif
                                @if($sale->courier_consignment_id)
                                    &nbsp;·&nbsp;<span class="text-gray-500">CID:</span>
                                    <span class="font-mono font-bold text-gray-900 bg-white px-1.5 py-0.5 rounded border border-amber-200">{{ $sale->courier_consignment_id }}</span>
                                @endif
                            </div>
                        </div>
                        @if($sale->courier_sent_at)
                        <span class="text-gray-400 text-[11px]">
                            Sent: {{ $sale->courier_sent_at->format('d M Y') }}
                        </span>
                        @endif
                    </div>
                    @endif

                    {{-- Items List --}}
                    <div class="p-5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Ordered Items</h4>
                        <div class="divide-y divide-gray-100">
                            @foreach($sale->saleItems as $item)
                            <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                                        @if($item->product && $item->product->image_url)
                                            <img src="{{ $item->product->image_url }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-box text-gray-400 text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="truncate">
                                        <p class="font-semibold text-gray-800 truncate">{{ $item->product?->name ?? 'Product' }}</p>
                                        @if($item->variant)
                                            <p class="text-[10px] text-gray-400">Variant: {{ $item->variant->name ?? $item->variant->sku }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-gray-500">{{ $currency }} {{ number_format($item->unit_price, 0) }} &times; {{ $item->quantity }} = </span>
                                    <span class="font-bold text-gray-900">{{ $currency }} {{ number_format($item->subtotal, 0) }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Order Financial Breakdown --}}
                        <div class="border-t border-gray-100 mt-4 pt-3 space-y-1.5 text-xs text-gray-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>{{ $currency }} {{ number_format($sale->subtotal, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Delivery Charge ({{ $sale->district ?? 'Standard' }})</span>
                                <span>{{ $currency }} {{ number_format($sale->delivery_charge, 0) }}</span>
                            </div>
                            @if($sale->discount > 0)
                            <div class="flex justify-between text-emerald-600 font-semibold">
                                <span>Discount {{ $sale->coupon_code ? '(' . $sale->coupon_code . ')' : '' }}</span>
                                <span>- {{ $currency }} {{ number_format($sale->discount, 0) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-sm font-extrabold text-gray-900 border-t border-gray-100 pt-2 mt-1">
                                <span>Total Payable</span>
                                <span class="text-brand-dark">{{ $currency }} {{ number_format($sale->total, 0) }}</span>
                            </div>
                        </div>

                        {{-- Delivery Address & Payment Method --}}
                        <div class="border-t border-gray-100 mt-3 pt-3 flex flex-wrap items-center justify-between gap-3 text-[11px] text-gray-500">
                            <div>
                                <span class="font-bold text-gray-700"><i class="fas fa-location-dot text-brand mr-1"></i> Address:</span>
                                <span>{{ implode(', ', array_filter([$sale->customer?->address, $sale->thana, $sale->district])) ?: 'Standard Delivery' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-700">Payment:</span>
                                <span class="uppercase font-semibold text-gray-800">{{ $sale->payment_method ?? 'COD' }}</span>
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $sale->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($sale->payment_status ?? 'unpaid') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @else
        {{-- Helpful info before search --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 text-center shadow-xs">
            <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand-dark flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-search text-lg"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">অর্ডার ট্র্যাক করতে মোবাইল নম্বর দিন</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto leading-relaxed">
                অর্ডার করার সময় যে মোবাইল নম্বর ব্যবহার করেছিলেন, সেটি উপরের বক্সে লিখে "Track Orders" বাটনে ক্লিক করুন। আপনার ওই নম্বরের সকল অর্ডারের বর্তমান অবস্থা পেয়ে যাবেন।
            </p>
        </div>
        @endif

    </div>
</div>
@endsection
