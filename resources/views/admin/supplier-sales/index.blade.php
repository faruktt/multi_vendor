@extends('layouts.app')
@section('title', 'Supplier Sales')
@section('heading', 'Supplier Sales & Marketplace Orders')

@section('content')
<div class="space-y-5">

    {{-- Top KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-boxes-packing"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Supplier Orders</p>
                <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ number_format($kpis['total_orders']) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Items Sold</p>
                <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ number_format($kpis['total_items']) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-bangladeshi-taka-sign"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Gross Sales</p>
                <h3 class="text-xl font-black text-emerald-700 mt-0.5">৳{{ number_format($kpis['total_gross'], 2) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-amber-200 bg-amber-50/40 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                <i class="fas fa-percent"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-amber-900 uppercase tracking-wider">Admin Commission</p>
                <h3 class="text-xl font-black text-amber-700 mt-0.5">৳{{ number_format($kpis['total_commission'], 2) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-indigo-200 bg-indigo-50/40 p-4 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Supplier Net Payable</p>
                <h3 class="text-xl font-black text-indigo-700 mt-0.5">৳{{ number_format($kpis['total_net'], 2) }}</h3>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.supplier-sales.index') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Supplier Filter Dropdown --}}
            <div class="min-w-[220px] flex-1 sm:flex-initial">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter by Supplier</label>
                <div class="relative">
                    <select name="supplier_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 font-medium focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none transition">
                        <option value="">All Suppliers ({{ $suppliers->sum('orders_count') }} total orders)</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->company_name ?: $s->name }} ({{ $s->orders_count }} orders) - {{ $s->commission_percentage }}% fee
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Search Input --}}
            <div class="min-w-[180px] flex-1 sm:flex-initial">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, customer, phone..."
                           class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none transition">
                </div>
            </div>

            {{-- Order Status --}}
            <div class="w-36 flex-shrink-0">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Order Status</label>
                <select name="order_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none transition">
                    <option value="">All Statuses</option>
                    @foreach($orderStatuses as $os)
                        <option value="{{ $os->key }}" {{ request('order_status') === $os->key ? 'selected' : '' }}>{{ $os->label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Range --}}
            <div class="w-32 flex-shrink-0">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none transition">
            </div>

            <div class="w-32 flex-shrink-0">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none transition">
            </div>

            <div class="flex items-end gap-2 pt-5">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl text-sm shadow-sm flex items-center gap-1.5 transition">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                @if(request()->hasAny(['supplier_id','search','order_status','from','to']))
                    <a href="{{ route('admin.supplier-sales.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold px-3.5 py-2 rounded-xl text-sm transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Supplier Sales Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-800 text-base">Supplier Orders List</h3>
                <p class="text-xs text-slate-500 mt-0.5">Showing {{ $sales->total() }} supplier orders placed across the marketplace</p>
            </div>
            @if(request('supplier_id'))
                @php $activeSup = $suppliers->firstWhere('id', request('supplier_id')); @endphp
                @if($activeSup)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-bold text-xs">
                        <i class="fas fa-store"></i> Filtered by: {{ $activeSup->company_name ?: $activeSup->name }}
                        <a href="{{ route('admin.supplier-sales.index') }}" class="text-blue-500 hover:text-blue-800 ml-1">&times;</a>
                    </span>
                @endif
            @endif
        </div>

        <div class="lg:hidden flex items-center justify-between px-3.5 py-2 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100/60 text-[11px] text-amber-800 font-medium">
            <span class="flex items-center gap-1.5"><i class="fas fa-arrows-left-right text-amber-600 animate-pulse"></i> Scroll horizontally to view all details</span>
            <span class="text-amber-700/80 font-mono text-[10px]">{{ $sales->total() }} records</span>
        </div>
        <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Order Info</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Ordered Items</th>
                        <th class="px-4 py-3 text-right">Gross Total</th>
                        <th class="px-4 py-3 text-right">Admin Comm.</th>
                        <th class="px-4 py-3 text-right">Supplier Net</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                        @php
                            $supplierItems = $sale->saleItems->filter(fn($item) => $item->supplier_id !== null);
                            if (request('supplier_id')) {
                                $supplierItems = $supplierItems->filter(fn($item) => $item->supplier_id == request('supplier_id'));
                            }
                            $grossAmount = $supplierItems->sum('subtotal');
                            $adminComm   = $supplierItems->sum('admin_commission_amount');
                            $netAmount   = $supplierItems->sum('supplier_earning');
                            $primarySupplier = $sale->supplier ?: $supplierItems->first()?->supplier;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            {{-- Order Info --}}
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.supplier-sales.show', $sale) }}" class="font-extrabold text-blue-600 hover:underline">
                                    {{ $sale->invoice_no }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $sale->created_at->format('d M Y, h:i A') }}
                                </div>
                            </td>

                            {{-- Supplier Badge --}}
                            <td class="px-4 py-3.5">
                                @if($primarySupplier)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs">
                                            {{ substr($primarySupplier->company_name ?: $primarySupplier->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.supplier-sales.index', ['supplier_id' => $primarySupplier->id]) }}"
                                               class="font-bold text-slate-800 hover:text-blue-600 text-xs block leading-tight">
                                                {{ $primarySupplier->company_name ?: $primarySupplier->name }}
                                            </a>
                                            <span class="text-[10px] text-slate-500">
                                                Rate: {{ $primarySupplier->commission_percentage }}%
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Various Suppliers</span>
                                @endif
                            </td>

                            {{-- Customer --}}
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-800 text-xs">{{ $sale->customer?->name ?? 'Guest' }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1 font-mono">
                                    <i class="fas fa-phone text-[9px] text-slate-400"></i>
                                    <span>{{ $sale->customer?->phone ?: '—' }}</span>
                                </div>
                                @php
                                    $custAddress = $sale->customer?->address ?: ($sale->address ?: '');
                                @endphp
                                @if($custAddress)
                                    <div class="text-[10.5px] text-slate-600 font-medium mt-1 leading-snug max-w-[180px]" title="{{ $custAddress }}">
                                        <i class="fas fa-location-dot text-[9px] text-blue-600 mr-0.5"></i>
                                        {{ $custAddress }}
                                    </div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $sale->thana ? $sale->thana . ', ' : '' }}{{ $sale->district }}
                                </div>
                            </td>

                            {{-- Ordered Items with Product Thumbnails --}}
                            <td class="px-4 py-3.5">
                                <div class="space-y-1.5 min-w-[210px]">
                                    @foreach($supplierItems->take(2) as $item)
                                        <div class="flex items-center gap-2">
                                            <div class="w-9 h-9 rounded-lg border border-slate-200 overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                                                @if($item->product?->first_image_url)
                                                    <img src="{{ $item->product->first_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <i class="fas fa-box text-slate-300 text-xs"></i>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs text-slate-800 font-bold truncate max-w-[130px]" title="{{ $item->product?->name }}">
                                                    {{ $item->product?->name ?? 'Product' }}
                                                </div>
                                                <div class="text-[10.5px] text-slate-500 flex items-center gap-1.5">
                                                    @if($item->variant_name)
                                                        <span class="text-indigo-600 font-semibold">{{ $item->variant_name }} &bull;</span>
                                                    @endif
                                                    <span class="font-bold">x{{ $item->quantity }}</span>
                                                    <span class="font-semibold text-slate-700">৳{{ number_format($item->subtotal, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($supplierItems->count() > 2)
                                        <div class="text-[10px] text-blue-600 font-bold pl-1">+{{ $supplierItems->count() - 2 }} more items</div>
                                    @endif
                                </div>
                            </td>

                            {{-- Gross Amount --}}
                            <td class="px-4 py-3.5 text-right font-extrabold text-slate-800">
                                ৳{{ number_format($grossAmount, 2) }}
                            </td>

                            {{-- Admin Commission --}}
                            <td class="px-4 py-3.5 text-right font-black text-amber-700 bg-amber-50/20">
                                ৳{{ number_format($adminComm, 2) }}
                            </td>

                            {{-- Supplier Net --}}
                            <td class="px-4 py-3.5 text-right font-black text-indigo-700 bg-indigo-50/20">
                                ৳{{ number_format($netAmount, 2) }}
                            </td>

                            {{-- Order Status --}}
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $stColors = match($sale->order_status) {
                                        'delivered'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'cancelled'   => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'shipped'     => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'confirmed'   => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        default       => 'bg-amber-50 text-amber-800 border-amber-200',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $stColors }}">
                                    {{ ucfirst($sale->order_status ?: 'pending') }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.supplier-sales.show', $sale) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 text-xs font-bold transition"
                                       title="View Details">
                                        <i class="fas fa-eye text-[10px]"></i> View
                                    </a>
                                    <a href="{{ route('admin.supplier-sales.invoice', $sale) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition border border-blue-200"
                                       title="Print Invoice">
                                        <i class="fas fa-print text-[10px]"></i> Print
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400">
                                <i class="fas fa-box-open text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">No supplier orders found matching current criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
