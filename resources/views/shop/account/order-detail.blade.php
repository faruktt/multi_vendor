@extends('shop.account.layout')
@section('title', 'Order #' . $order->invoice_no . ' — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="space-y-6">

    {{-- Order Header & Status Card --}}
    <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-7 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-5 mb-5">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-gray-400 uppercase tracking-wider">Invoice</span>
                    <h2 class="font-mono font-black text-gray-900 text-xl sm:text-2xl">#{{ $order->invoice_no }}</h2>
                </div>
                <div class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                    <span><i class="fas fa-calendar-day text-[10px]"></i> Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold capitalize
                    {{ $order->order_status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                      ($order->order_status === 'cancelled' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $order->order_status === 'delivered' ? 'bg-emerald-500' : ($order->order_status === 'cancelled' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                    Status: {{ ucfirst($order->order_status) }}
                </span>

                <a href="{{ route('shop.track.index', ['phone' => $customer->phone, 'invoice' => $order->invoice_no]) }}"
                   class="h-9 px-4 rounded-xl bg-brand/10 hover:bg-brand/20 text-brand text-xs font-bold transition-all flex items-center gap-1.5 border border-brand/20">
                    <i class="fas fa-location-crosshairs"></i> Track Live
                </a>
            </div>
        </div>

        {{-- Order Items Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-[11px] font-black text-gray-400 uppercase tracking-wider">
                        <th class="py-3 text-left">Item Details</th>
                        <th class="py-3 text-center">Unit Price</th>
                        <th class="py-3 text-center">Quantity</th>
                        <th class="py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($order->saleItems as $item)
                        @php
                            $pImg = $item->product?->first_image_url;
                        @endphp
                        <tr>
                            <td class="py-3.5">
                                <div class="flex items-center gap-3.5">
                                    @if($pImg)
                                        <img src="{{ $pImg }}" alt="{{ $item->product?->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 shadow-2xs flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0 text-gray-400">
                                            <i class="fas fa-box text-sm"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ $item->product->name ?? 'Product' }}</div>
                                        @if($item->variant_name)
                                            <div class="text-xs text-gray-500 font-medium mt-0.5">Variant: {{ $item->variant_name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 text-center text-gray-600 font-medium">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3.5 text-center font-bold text-gray-900">{{ $item->quantity }}</td>
                            <td class="py-3.5 text-right font-black text-gray-900">৳{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary Breakdown --}}
        <div class="border-t border-gray-100 mt-5 pt-5 flex flex-col sm:flex-row justify-between gap-6">
            <div class="text-xs space-y-2 text-gray-600 sm:max-w-sm">
                <div><span class="font-bold text-gray-800">Payment Method:</span> <span class="capitalize">{{ $order->payment_method ?? 'Cash on Delivery' }}</span></div>
                <div><span class="font-bold text-gray-800">Payment Status:</span> <span class="capitalize font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">{{ $order->payment_status }}</span></div>
                @if($order->delivery_zone)
                <div>
                    <span class="font-bold text-gray-800">Delivery Area:</span>
                    <span class="font-bold text-blue-700">{{ $order->delivery_zone === 'inside' ? 'Inside Dhaka' : ($order->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }}</span>
                </div>
                @endif
                @if($order->district || $order->customer?->address)
                <div>
                    <span class="font-bold text-gray-800">Delivery Address:</span>
                    <span class="text-gray-700">{{ implode(', ', array_filter([$order->customer?->address, $order->thana, $order->district])) }}</span>
                </div>
                @endif
                @if($order->note)
                    <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100 mt-2">
                        <span class="font-bold text-gray-800 block mb-0.5">Order Note:</span>
                        <p class="text-gray-600">{{ $order->note }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-2 text-sm min-w-[240px] bg-gray-50/80 p-4 rounded-2xl border border-gray-100">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-medium text-gray-900">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="flex justify-between text-emerald-600 font-semibold">
                        <span>Coupon Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span>
                        <span>-৳{{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-500">
                    <span>Delivery Charge</span>
                    <span class="font-medium text-gray-900">৳{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="flex justify-between font-black text-gray-900 text-base border-t border-gray-200 pt-2.5">
                    <span>Total Amount</span>
                    <span class="text-brand">৳{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <a href="{{ route('shop.customer.orders') }}" class="h-9 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-xs font-bold text-gray-700 hover:text-gray-900 flex items-center gap-2 transition-all">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

</div>
@endsection
