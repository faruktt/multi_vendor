@extends('supplier.layouts.app')
@section('title', 'Order ' . $sale->invoice_no)
@section('heading', 'Order Details: ' . $sale->invoice_no)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Invoice</span>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    {{ $sale->order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                       ($sale->order_status === 'cancelled' ? 'bg-rose-100 text-rose-800' :
                       ($sale->order_status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800')) }}">
                    {{ $sale->order_status }}
                </span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 font-mono mt-1">{{ $sale->invoice_no }}</h2>
            <div class="text-xs text-slate-500 mt-1">
                Placed on <strong class="text-slate-700">{{ $sale->created_at->format('d M, Y \a\t h:i A') }}</strong>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('supplier.orders.invoice', $sale->id) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-200 transition">
                <i class="fas fa-print"></i>
                <span>Print Invoice</span>
            </a>
            <a href="{{ route('supplier.orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                <i class="fas fa-arrow-left"></i>
                <span>Back to All Orders</span>
            </a>
        </div>
    </div>

    {{-- Customer & Shipping Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="flex items-center gap-2.5 pb-3 mb-4 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                <i class="fas fa-location-dot"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-sm">Customer & Shipping Information</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="block text-slate-400 font-medium">Customer Name</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ $sale->customer?->name ?: 'Customer' }}</span>
            </div>

            <div>
                <span class="block text-slate-400 font-medium">Contact Phone</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ $sale->customer?->phone ?: '—' }}</span>
            </div>

            <div>
                <span class="block text-slate-400 font-medium">District & Thana</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ $sale->district ?: '—' }}, {{ $sale->thana ?: '—' }}</span>
            </div>

            <div class="sm:col-span-2 md:col-span-3 pt-2">
                <span class="block text-slate-400 font-medium">Shipping Address</span>
                <span class="font-medium text-slate-700 mt-0.5 block">{{ $sale->customer?->address ?: ($sale->address ?: 'Standard Address') }}</span>
            </div>
        </div>
    </div>

    {{-- Supplier Items Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                    <i class="fas fa-boxes-packing"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Your Sold Products in this Order</h3>
                    <p class="text-[11px] text-slate-400">List of items and commission split for {{ $supplier->display_name }}</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-600">
                {{ $supplierQty }} items
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-white text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Product</th>
                        <th class="px-3 py-3.5 font-semibold">Option / Variant</th>
                        <th class="px-3 py-3.5 font-semibold text-right">Unit Price</th>
                        <th class="px-2 py-3.5 font-semibold text-center">Qty</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Gross Subtotal</th>
                        <th class="px-3 py-3.5 font-semibold text-center">Rate</th>
                        <th class="px-4 py-3.5 font-semibold text-right text-rose-600">Admin Comm.</th>
                        <th class="px-5 py-3.5 font-semibold text-right text-emerald-700">Your Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($supplierItems as $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($item->product?->first_image_url)
                                        <img src="{{ $item->product->first_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-box text-slate-300 text-base"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $item->product?->name ?: 'Product #' . $item->product_id }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">SKU: {{ $item->product?->sku ?: '—' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-3 py-3.5 font-semibold text-slate-700">
                            @if($item->variant_name)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                    {{ $item->variant_name }}
                                </span>
                            @else
                                <span class="text-slate-400 font-normal">Default</span>
                            @endif
                        </td>

                        <td class="px-3 py-3.5 text-right font-bold text-slate-800">
                            ৳{{ number_format($item->unit_price, 2) }}
                        </td>

                        <td class="px-2 py-3.5 text-center font-bold text-slate-900">
                            {{ $item->quantity }}
                        </td>

                        <td class="px-4 py-3.5 text-right font-bold text-slate-800">
                            ৳{{ number_format($item->subtotal, 2) }}
                        </td>

                        <td class="px-3 py-3.5 text-center font-semibold text-slate-500">
                            {{ $item->admin_commission_rate ?? $supplier->commission_percentage }}%
                        </td>

                        <td class="px-4 py-3.5 text-right font-semibold text-rose-600">
                            -৳{{ number_format($item->admin_commission_amount, 2) }}
                        </td>

                        <td class="px-5 py-3.5 text-right font-black text-emerald-700 text-sm">
                            ৳{{ number_format($item->supplier_earning, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-slate-800">
                    <tr>
                        <td colspan="4" class="px-5 py-3.5 text-right font-semibold text-slate-500">
                            Totals:
                        </td>
                        <td class="px-4 py-3.5 text-right font-black text-slate-900">
                            ৳{{ number_format($supplierGross, 2) }}
                        </td>
                        <td></td>
                        <td class="px-4 py-3.5 text-right font-black text-rose-600">
                            -৳{{ number_format($supplierCommission, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-black text-base text-emerald-700">
                            ৳{{ number_format($supplierNet, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
