@extends('layouts.app')
@section('title', 'Supplier Order: ' . $sale->invoice_no)
@section('heading', 'Supplier Order Details: ' . $sale->invoice_no)

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    {{-- Top Action Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.supplier-sales.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-extrabold text-slate-800 text-lg">{{ $sale->invoice_no }}</h2>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Marketplace Order
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $sale->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.supplier-sales.invoice', $sale) }}" target="_blank"
               class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm flex items-center gap-1.5 transition">
                <i class="fas fa-print text-xs"></i>
                <span>Print Invoice</span>
            </a>

            {{-- Status Update Form --}}
            <form method="POST" action="{{ route('admin.supplier-sales.status', $sale) }}" class="flex items-center gap-2">
                @csrf
                <label class="text-xs font-bold text-slate-600">Update Status:</label>
                <select name="order_status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach($orderStatuses as $st)
                        <option value="{{ $st->key }}" {{ $sale->order_status === $st->key ? 'selected' : '' }}>
                            {{ $st->label }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-3 py-1.5 rounded-xl shadow-sm transition">
                    Update
                </button>
            </form>
        </div>
    </div>

    {{-- Info Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Customer & Delivery Details --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-user-tag text-blue-600"></i> Customer &amp; Delivery Information
            </h3>
            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-2 flex justify-between">
                    <span class="text-slate-500">Name:</span>
                    <span class="font-bold text-slate-800">{{ $sale->customer?->name ?? 'Guest' }}</span>
                </div>
                <div class="py-2 flex justify-between">
                    <span class="text-slate-500">Phone:</span>
                    <span class="font-bold text-slate-800">{{ $sale->customer?->phone }}</span>
                </div>
                <div class="py-2 flex justify-between">
                    <span class="text-slate-500">Location:</span>
                    <span class="font-bold text-slate-800 text-right">{{ $sale->thana ? $sale->thana . ', ' : '' }}{{ $sale->district }}</span>
                </div>
                <div class="py-2 flex justify-between">
                    <span class="text-slate-500">Address:</span>
                    <span class="font-medium text-slate-700 text-right max-w-xs">{{ $sale->customer?->address }}</span>
                </div>
                <div class="py-2 flex justify-between items-center">
                    <span class="text-slate-500">Payment:</span>
                    <span class="font-bold px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-700 uppercase">
                        {{ $sale->payment_method ?? 'Cash On Delivery' }} ({{ $sale->payment_status }})
                    </span>
                </div>
            </div>
        </div>

        {{-- Supplier / Vendor Information --}}
        @php
            $supplier = $sale->supplier ?: $sale->saleItems->firstWhere('supplier_id', '!=', null)?->supplier;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-store text-indigo-600"></i> Supplier / Vendor Details
            </h3>
            @if($supplier)
                <div class="divide-y divide-slate-100 text-xs">
                    <div class="py-2 flex justify-between">
                        <span class="text-slate-500">Store / Company:</span>
                        <span class="font-extrabold text-indigo-700">{{ $supplier->company_name ?: $supplier->name }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-slate-500">Contact Person:</span>
                        <span class="font-bold text-slate-800">{{ $supplier->name }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-slate-500">Phone:</span>
                        <span class="font-bold text-slate-800">{{ $supplier->phone }}</span>
                    </div>
                    <div class="py-2 flex justify-between">
                        <span class="text-slate-500">Agreed Commission:</span>
                        <span class="font-extrabold text-amber-700">{{ $supplier->commission_percentage }}% Admin Fee</span>
                    </div>
                    @if($supplier->bkash_number)
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500">bKash Payout:</span>
                            <span class="font-bold text-pink-600">{{ $supplier->bkash_number }}</span>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-xs text-slate-400 italic">No direct primary supplier record associated.</p>
            @endif
        </div>
    </div>

    {{-- Order Items Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-800 text-base">Ordered Items &amp; Commission Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3 text-right">Unit Price</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Gross Total</th>
                        <th class="px-4 py-3 text-right">Admin Fee</th>
                        <th class="px-4 py-3 text-right">Supplier Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $totGross = 0;
                        $totAdmin = 0;
                        $totNet   = 0;
                    @endphp
                    @foreach($sale->saleItems as $item)
                        @php
                            $totGross += $item->subtotal;
                            $totAdmin += $item->admin_commission_amount;
                            $totNet   += $item->supplier_earning;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg border border-slate-200 overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                                        @if($item->product?->first_image_url)
                                            <img src="{{ $item->product->first_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-box text-slate-300 text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 text-xs">{{ $item->product?->name ?? 'Product #' . $item->product_id }}</div>
                                        @if($item->variant_name)
                                            <div class="text-[11px] text-indigo-600 font-medium mt-0.5">↳ {{ $item->variant_name }}</div>
                                        @endif
                                        <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $item->product?->sku ?: '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($item->supplier)
                                    <span class="font-bold text-slate-700 text-xs">{{ $item->supplier->company_name ?: $item->supplier->name }}</span>
                                @else
                                    <span class="text-xs text-slate-400">Direct / Admin</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-slate-600">
                                ৳{{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-slate-800">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-extrabold text-slate-800">
                                ৳{{ number_format($item->subtotal, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-amber-700 bg-amber-50/20">
                                ৳{{ number_format($item->admin_commission_amount, 2) }}
                                <span class="text-[10px] text-slate-400 block font-normal">({{ $item->admin_commission_rate }}%)</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-indigo-700 bg-indigo-50/20">
                                ৳{{ number_format($item->supplier_earning, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-slate-700 font-extrabold">Totals:</td>
                        <td class="px-4 py-3 text-right font-black text-slate-900">৳{{ number_format($totGross, 2) }}</td>
                        <td class="px-4 py-3 text-right font-black text-amber-700 bg-amber-50/40">৳{{ number_format($totAdmin, 2) }}</td>
                        <td class="px-4 py-3 text-right font-black text-indigo-700 bg-indigo-50/40">৳{{ number_format($totNet, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
