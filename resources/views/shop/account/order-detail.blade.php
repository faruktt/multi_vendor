@extends('shop.account.layout')
@section('title', 'Order ' . $order->invoice_no . ' — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="space-y-6">

    {{-- Order Header & Status Card --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Invoice</span>
                    <h2 class="font-mono font-black text-gray-900 text-lg sm:text-xl">{{ $order->invoice_no }}</h2>
                </div>
                <div class="text-xs text-gray-400 mt-0.5">
                    Placed on {{ $order->created_at->format('d M Y, h:i A') }}
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold capitalize
                    {{ $order->order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                      ($order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $order->order_status === 'delivered' ? 'bg-emerald-600' : ($order->order_status === 'cancelled' ? 'bg-red-600' : 'bg-amber-600') }}"></span>
                    Status: {{ ucfirst($order->order_status) }}
                </span>

                <a href="{{ route('shop.track.index', ['phone' => $customer->phone, 'invoice' => $order->invoice_no]) }}"
                   class="h-8 px-3 rounded-xl bg-brand/10 hover:bg-brand/20 text-brand text-xs font-bold transition-all flex items-center gap-1.5">
                    <i class="fas fa-location-crosshairs"></i> Track Live
                </a>
            </div>
        </div>

        {{-- Order Items Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="py-2.5 text-left">Item Details</th>
                        <th class="py-2.5 text-center">Unit Price</th>
                        <th class="py-2.5 text-center">Qty</th>
                        <th class="py-2.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($order->saleItems as $item)
                        @php
                            $pImg = $item->product?->first_image_url;
                        @endphp
                        <tr>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    @if($pImg)
                                        <img src="{{ $pImg }}" alt="{{ $item->product?->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center flex-shrink-0 text-gray-400">
                                            <i class="fas fa-box text-sm"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $item->product->name ?? 'Product' }}</div>
                                        @if($item->variant_name)
                                            <div class="text-xs text-gray-500 font-medium">Variant: {{ $item->variant_name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-center text-gray-600">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 text-center font-bold text-gray-800">{{ $item->quantity }}</td>
                            <td class="py-3 text-right font-black text-gray-900">৳{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary Breakdown --}}
        <div class="border-t border-gray-100 mt-4 pt-4 flex flex-col sm:flex-row justify-between gap-6">
            <div class="text-xs space-y-1.5 text-gray-600 sm:max-w-xs">
                <div><span class="font-bold text-gray-700">Payment Method:</span> <span class="capitalize">{{ $order->payment_method ?? 'Cash on Delivery' }}</span></div>
                <div><span class="font-bold text-gray-700">Payment Status:</span> <span class="capitalize font-semibold text-emerald-700">{{ $order->payment_status }}</span></div>
                @if($order->delivery_zone)
                <div>
                    <span class="font-bold text-gray-700">Delivery Area:</span>
                    <span class="font-semibold text-blue-700">{{ $order->delivery_zone === 'inside' ? 'Inside Dhaka' : ($order->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }}</span>
                </div>
                @endif
                @if($order->district || $order->customer?->address)
                <div>
                    <span class="font-bold text-gray-700">Delivery Address:</span>
                    <span>{{ implode(', ', array_filter([$order->customer?->address, $order->thana, $order->district])) }}</span>
                </div>
                @endif
                @if($order->note)
                    <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200/80 mt-2">
                        <span class="font-bold text-gray-700 block mb-0.5">Order Note:</span>
                        {{ $order->note }}
                    </div>
                @endif
            </div>

            <div class="space-y-1.5 text-sm min-w-[220px]">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span>৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount > 0)
                    <div class="flex justify-between text-emerald-600 font-semibold">
                        <span>Coupon Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span>
                        <span>-৳{{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-500">
                    <span>Delivery Charge</span>
                    <span>৳{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                <div class="flex justify-between font-black text-gray-900 text-base border-t border-gray-100 pt-2">
                    <span>Total Amount</span>
                    <span>৳{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center">
        <a href="{{ route('shop.customer.orders') }}" class="text-xs font-bold text-gray-600 hover:text-brand flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

</div>
@endsection
