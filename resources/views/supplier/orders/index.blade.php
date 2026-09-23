@extends('supplier.layouts.app')
@section('title', 'Sales & Orders')
@section('heading', 'Sales & Customer Orders')

@section('content')
<div class="space-y-5">

    {{-- Stats Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-sack-dollar"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gross Sales</div>
                <div class="text-2xl font-black text-slate-800">৳{{ number_format($totalGrossSales, 2) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ number_format($totalOrdersCount) }} total orders</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-percent"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Admin Commission</div>
                <div class="text-2xl font-black text-rose-600">-৳{{ number_format($totalAdminCommission, 2) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Rate: {{ $supplier->commission_percentage ?? 5.00 }}%</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-sm flex items-center gap-4 bg-gradient-to-br from-white to-emerald-50/60">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl flex-shrink-0 shadow-md shadow-emerald-500/25">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Your Net Earnings</div>
                <div class="text-2xl font-black text-emerald-700">৳{{ number_format($totalNetEarnings, 2) }}</div>
                <div class="text-[11px] text-emerald-600 font-bold mt-0.5">Net receivable balance</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('supplier.orders.index') }}" class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search invoice number, customer name or phone..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>

            {{-- Status Filter --}}
            <select name="status" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-600">
                <option value="">All Order Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            {{-- Date Range --}}
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <input type="date" name="from" value="{{ request('from') }}"
                       class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <span>to</span>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                Filter
            </button>

            @if(request()->anyFilled(['search', 'status', 'from', 'to']))
            <a href="{{ route('supplier.orders.index') }}"
               class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Invoice No</th>
                        <th class="px-4 py-3.5 font-semibold">Date</th>
                        <th class="px-4 py-3.5 font-semibold min-w-[190px]">Customer &amp; Address</th>
                        <th class="px-4 py-3.5 font-semibold min-w-[160px]">Products</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Gross Sale</th>
                        <th class="px-4 py-3.5 font-semibold text-right text-rose-600">Admin Comm.</th>
                        <th class="px-4 py-3.5 font-semibold text-right text-emerald-700">Your Net</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right min-w-[160px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($orders as $order)
                    @php
                        $subtotal = $order->saleItems->sum('subtotal');
                        $adminComm = $order->saleItems->sum('admin_commission_amount');
                        $netEarnings = $order->saleItems->sum('supplier_earning');
                        $qty = $order->saleItems->sum('quantity');
                        $fullAddress = $order->customer?->address ?: ($order->address ?: '');
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        {{-- Invoice --}}
                        <td class="px-5 py-3.5">
                            <a href="{{ route('supplier.orders.show', $order->id) }}" class="font-mono font-bold text-emerald-700 text-sm hover:underline">
                                {{ $order->invoice_no }}
                            </a>
                            <div class="text-[10px] text-slate-400">Channel: {{ ucfirst($order->channel ?: 'Online') }}</div>
                        </td>

                        {{-- Date --}}
                        <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">
                            <div class="font-semibold">{{ $order->created_at->format('d M, Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $order->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- Customer & Address --}}
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-800 text-xs">{{ $order->customer?->name ?: 'Customer' }}</div>
                            <div class="text-[11px] text-slate-600 font-mono flex items-center gap-1 mt-0.5">
                                <i class="fas fa-phone text-[9px] text-slate-400"></i>
                                <span>{{ $order->customer?->phone ?: '—' }}</span>
                            </div>
                            @if($fullAddress)
                                <div class="text-[10.5px] text-slate-600 font-medium mt-1 leading-snug max-w-[200px]" title="{{ $fullAddress }}">
                                    <i class="fas fa-location-dot text-[9px] text-emerald-600 mr-0.5"></i>
                                    {{ $fullAddress }}
                                </div>
                            @endif
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $order->thana ? $order->thana . ', ' : '' }}{{ $order->district }}
                            </div>
                        </td>

                        {{-- Ordered Products & Images --}}
                        <td class="px-4 py-3.5">
                            <div class="space-y-1.5 min-w-[160px]">
                                @foreach($order->saleItems->take(2) as $item)
                                    <div class="flex items-center gap-2">
                                        <div class="w-9 h-9 rounded-lg border border-slate-200 overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                                            @if($item->product?->first_image_url)
                                                <img src="{{ $item->product->first_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-box text-slate-300 text-xs"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs text-slate-800 font-bold truncate max-w-[120px]" title="{{ $item->product?->name }}">
                                                {{ $item->product?->name ?? 'Product' }}
                                            </div>
                                            <div class="text-[10px] text-slate-500">
                                                @if($item->variant_name)
                                                    <span class="text-indigo-600 font-semibold">{{ $item->variant_name }} &bull;</span>
                                                @endif
                                                <span class="font-bold">x{{ $item->quantity }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($order->saleItems->count() > 2)
                                    <div class="text-[10px] text-emerald-700 font-bold pl-1">
                                        +{{ $order->saleItems->count() - 2 }} more items
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Gross Subtotal --}}
                        <td class="px-4 py-3.5 text-right font-bold text-slate-800 whitespace-nowrap">
                            ৳{{ number_format($subtotal, 2) }}
                        </td>

                        {{-- Admin Commission --}}
                        <td class="px-4 py-3.5 text-right font-semibold text-rose-600 whitespace-nowrap">
                            -৳{{ number_format($adminComm, 2) }}
                        </td>

                        {{-- Net Earnings --}}
                        <td class="px-4 py-3.5 text-right font-black text-emerald-700 whitespace-nowrap">
                            ৳{{ number_format($netEarnings, 2) }}
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                {{ $order->order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                                   ($order->order_status === 'cancelled' ? 'bg-rose-100 text-rose-800' :
                                   ($order->order_status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800')) }}">
                                {{ $order->order_status }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('supplier.orders.show', $order->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition"
                                   title="View Order Details">
                                    <i class="fas fa-eye text-[11px]"></i>
                                    <span>Details</span>
                                </a>
                                <a href="{{ route('supplier.orders.invoice', $order->id) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 transition"
                                   title="Print A4 Invoice">
                                    <i class="fas fa-print text-[11px]"></i>
                                    <span>Print</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto flex items-center justify-center text-2xl text-slate-300 mb-3">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">No sales or orders found</div>
                            <p class="text-xs text-slate-400 mt-1">When customers order your products from the online storefront, they will be listed here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
