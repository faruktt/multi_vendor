@extends('reseller.layouts.app')
@section('title', 'Order ' . $order->invoice_no)
@section('heading', 'Order Detail')

@section('content')
<div class="py-4 max-w-3xl">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <div class="text-xs text-slate-400 mb-1">Invoice Number</div>
                <div class="font-mono font-bold text-indigo-600 text-lg">{{ $order->invoice_no }}</div>
                <div class="text-slate-500 text-sm mt-1">{{ $order->created_at->format('d M Y, h:i A') }}</div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @php
                    $statusColors = [
                        'pending'   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        'confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'shipped'   => 'bg-purple-100 text-purple-700 border-purple-200',
                        'delivered' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                    ];
                @endphp
                <span class="px-4 py-2 rounded-xl text-sm font-bold border {{ $statusColors[$order->order_status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                    {{ ucfirst($order->order_status) }}
                </span>

                <a href="{{ route('reseller.orders.invoice', $order) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-xs transition">
                    <i class="fas fa-print text-xs"></i>
                    <span>Print Invoice</span>
                </a>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-500 flex items-center justify-between">
            <span>Store: <strong class="text-slate-700">{{ $order->vendor->name ?? 'Website' }}</strong></span>
            <a href="{{ route('reseller.orders.invoice', $order) }}" target="_blank" class="text-indigo-600 hover:underline text-xs font-semibold flex items-center gap-1">
                <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> Open Printable Slip
            </a>
        </div>
    </div>

    {{-- Customer & Delivery Details --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
        <div class="font-bold text-slate-700 text-sm mb-3 flex items-center gap-2">
            <i class="fas fa-user-circle text-indigo-500"></i> Customer & Delivery Details
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-xs text-slate-400 block">Customer Name</span>
                <span class="font-semibold text-slate-800">{{ $order->customer?->name ?? '—' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Phone Number</span>
                @if($order->customer?->phone)
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="font-semibold text-slate-800 font-mono">{{ $order->customer->phone }}</span>
                        <a href="https://wa.me/{{ $order->customer->whatsapp_number ?? preg_replace('/[^\d]/', '', $order->customer->phone) }}" target="_blank" rel="noopener" class="text-emerald-600 hover:text-emerald-700" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                @else
                    <span class="text-slate-500">—</span>
                @endif
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Delivery Area & Zone</span>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($order->delivery_zone)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $order->delivery_zone === 'inside' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($order->delivery_zone === 'sub_dhaka' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            <i class="fas fa-truck text-[9px]"></i>
                            {{ $order->delivery_zone === 'inside' ? 'Inside Dhaka' : ($order->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }}
                        </span>
                    @endif
                    @if($order->district || $order->customer?->district)
                        <span class="text-xs text-slate-600 font-medium">
                            {{ implode(' · ', array_filter([$order->district ?? $order->customer?->district, $order->thana ?? $order->customer?->thana])) }}
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <span class="text-xs text-slate-400 block">Delivery Address</span>
                <span class="text-slate-700 font-medium text-xs">{{ $order->customer?->address ?? ($order->note ?? '—') }}</span>
            </div>
            @if($order->note && $order->customer?->address)
            <div class="sm:col-span-2 pt-2 border-t border-slate-50">
                <span class="text-xs text-slate-400 block">Order Note</span>
                <span class="text-slate-600 text-xs italic">{{ $order->note }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-slate-100 font-bold text-slate-700 text-sm">Order Items</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Product</th>
                    <th class="px-5 py-3 text-center">Qty</th>
                    <th class="px-5 py-3 text-right">Unit Price</th>
                    <th class="px-5 py-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($order->saleItems as $item)
                    @php
                        $rImg = $item->product?->first_image_url;
                    @endphp
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($rImg)
                                    <img src="{{ $rImg }}" alt="{{ $item->product?->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0 text-slate-400">
                                        <i class="fas fa-box text-sm"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-slate-800">{{ $item->product->name ?? 'Product' }}</div>
                                    @if($item->variant_name)
                                        <div class="text-xs text-slate-400">{{ $item->variant_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-center text-slate-600">{{ $item->quantity }}</td>
                        <td class="px-5 py-3 text-right text-slate-600">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-5 py-3 text-right font-bold text-slate-800">৳{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Summary --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Customer Subtotal</span><span class="font-semibold">৳{{ number_format($order->subtotal, 2) }}</span></div>
            @if($order->discount > 0)
            <div class="flex justify-between text-emerald-600 font-medium"><span>Coupon Discount</span><span>-৳{{ number_format($order->discount, 2) }}</span></div>
            @endif
            @if($order->delivery_charge > 0)
            <div class="flex justify-between"><span class="text-slate-500">Delivery Charge</span><span class="font-semibold">৳{{ number_format($order->delivery_charge, 2) }}</span></div>
            @endif
            <div class="flex justify-between border-t border-slate-100 pt-2 mt-2">
                <div>
                    <span class="font-bold text-slate-700 block">Total Charged to Customer</span>
                    <span class="text-[10px] text-slate-400">(Cash on Delivery / COD)</span>
                </div>
                <span class="font-bold text-indigo-700 text-lg">৳{{ number_format($order->total, 2) }}</span>
            </div>
            <div class="flex justify-between items-center mt-3 text-emerald-700 bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-100">
                <span class="font-bold text-xs">Your Estimated Profit</span>
                <span class="font-black text-sm">৳{{ number_format($order->reseller_profit, 2) }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-400 pt-2">
                <span>Payment Status</span>
                <span class="font-semibold text-slate-600 capitalize">{{ $order->payment_status }}</span>
            </div>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <a href="{{ route('reseller.orders.index') }}"
           class="inline-flex items-center gap-2 text-indigo-600 hover:underline text-sm font-medium">
            <i class="fas fa-arrow-left text-xs"></i> Back to Orders
        </a>

        <a href="{{ route('reseller.orders.invoice', $order) }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
            <i class="fas fa-print"></i> Print Invoice
        </a>
    </div>
</div>
@endsection
