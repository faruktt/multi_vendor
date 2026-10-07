@extends('layouts.app')
@section('title','All Sales')
@section('heading','All Sales')
@php
    $currency      = $appSettings['currency'] ?? '৳';
    $globalOrders  = collect($branchSummary)->sum('orders');
    $globalRevenue = collect($branchSummary)->sum('revenue');
    $globalDue     = collect($branchSummary)->sum('due');
    $globalPaid    = $globalRevenue - $globalDue;
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')
<div x-data="salesAdminPage()">

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Orders</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($globalOrders) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">across all branches</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Revenue</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $currency }}{{ number_format($globalRevenue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">all time</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Collected</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $currency }}{{ number_format($globalPaid,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">{{ $globalRevenue > 0 ? round(($globalPaid/$globalRevenue)*100) : 0 }}% collected</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Due</p>
        <p class="text-2xl font-bold text-red-500 mt-1">{{ $currency }}{{ number_format($globalDue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">outstanding</p>
    </div>
</div>

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.all-sales') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[200px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div class="min-w-0">
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[130px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ number_format($s['orders']) }}</span> orders
                    &nbsp;·&nbsp;<span class="font-bold text-emerald-600">{{ $currency }}{{ number_format($s['revenue'],0) }}</span>
                    @if($s['due']>0)&nbsp;·&nbsp;<span class="text-red-500">{{ $currency }}{{ number_format($s['due'],0) }} due</span>@endif
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- ══ CHANNEL / SOURCE QUICK TABS ══ --}}
<div class="flex items-center gap-2 flex-wrap mb-3">
    <a href="{{ route('admin.all-sales', request()->except('source', 'page')) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ !request('source') ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-boxes-stacked text-[11px]"></i> All Sales (সব সেল)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('source') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $channelStats['total'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'website'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'website' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-globe text-[11px] text-blue-500"></i> Website Sales (ওয়েবসাইট)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'website' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700' }}">{{ $channelStats['website'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'pos'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'pos' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-cash-register text-[11px] text-emerald-500"></i> POS Sales (পিওএস কাউন্টার)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'pos' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">{{ $channelStats['pos'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'reseller'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'reseller' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-handshake text-[11px] text-purple-500"></i> Reseller Sales (রিসেলার)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'reseller' ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-700' }}">{{ $channelStats['reseller'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'supplier'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'supplier' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-store text-[11px] text-amber-500"></i> Supplier Sales (সাপ্লায়ার)
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'supplier' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700' }}">{{ $channelStats['supplier'] ?? 0 }}</span>
    </a>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, customer, phone, reseller, supplier..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-52">
        <select name="source" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Sources (সব উৎস)</option>
            <option value="website"  {{ request('source')=='website'  ? 'selected':'' }}>🌐 Website (ওয়েবসাইট)</option>
            <option value="pos"      {{ request('source')=='pos'      ? 'selected':'' }}>🧾 POS (কাউন্টার)</option>
            <option value="reseller" {{ request('source')=='reseller' ? 'selected':'' }}>🤝 Reseller (রিসেলার)</option>
            <option value="supplier" {{ request('source')=='supplier' ? 'selected':'' }}>🏪 Supplier (সাপ্লায়ার)</option>
        </select>
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="paid"    {{ request('status')=='paid'    ? 'selected':'' }}>Paid</option>
            <option value="partial" {{ request('status')=='partial' ? 'selected':'' }}>Partial</option>
            <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Pending</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-sales') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <span class="ml-auto text-[12px] text-slate-400">{{ $sales->total() }} records</span>
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="lg:hidden flex items-center justify-between px-3.5 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100/60 text-[11px] text-blue-700 font-medium">
        <span class="flex items-center gap-1.5"><i class="fas fa-arrows-left-right text-blue-500 animate-pulse"></i> Scroll horizontally to view all details</span>
        <span class="text-blue-500/80 font-mono text-[10px]">{{ $sales->total() }} records</span>
    </div>
    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-2 py-2.5 text-center w-7">
                        <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected()" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer" title="Select All on page">
                    </th>
                    <th class="px-1.5 py-2.5 text-center text-[10.5px] font-semibold text-slate-400 uppercase w-7">#</th>
                    <th class="px-2.5 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase hidden xl:table-cell">Date</th>
                    <th class="px-2.5 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase">Invoice</th>
                    <th class="px-2.5 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase">Sale Type / Source</th>
                    <th class="px-2.5 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase hidden md:table-cell">Branch</th>
                    <th class="px-2.5 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase">Customer</th>
                    <th class="px-2.5 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase">Products</th>
                    <th class="px-2.5 py-2.5 text-right text-[10.5px] font-semibold text-slate-400 uppercase">Total</th>
                    <th class="px-2 py-2.5 text-right text-[10.5px] font-semibold text-slate-400 uppercase hidden 2xl:table-cell">Paid</th>
                    <th class="px-2 py-2.5 text-right text-[10.5px] font-semibold text-slate-400 uppercase hidden 2xl:table-cell">Due</th>
                    <th class="px-2 py-2.5 text-center text-[10.5px] font-semibold text-slate-400 uppercase">Payment</th>
                    <th class="px-2.5 py-2.5 text-center text-[10.5px] font-semibold text-slate-400 uppercase">Order Status</th>
                    <th class="px-2.5 py-2.5 text-center text-[10.5px] font-semibold text-slate-400 uppercase">Courier</th>
                    <th class="px-2.5 py-2.5 text-center text-[10.5px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($sales as $sale)
                @php
                    $stBg = match($sale->payment_status) {
                        'paid'    => 'bg-emerald-100 text-emerald-700',
                        'partial' => 'bg-amber-100 text-amber-700',
                        default   => 'bg-red-100 text-red-600',
                    };
                    $isSupplier = $sale->supplier_id || $sale->supplier || $sale->saleItems->contains(fn($i) => !empty($i->supplier_id));
                    $supplierObj = $sale->supplier ?: $sale->saleItems->firstWhere('supplier_id')?->supplier;
                    $isReseller = $sale->reseller_id || ($sale->channel === 'reseller') || $sale->reseller;
                    $isWeb = ($sale->channel === 'web');
                    $isPos = ($sale->channel === 'pos') || (!$isWeb && !$isReseller && !$isSupplier);
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors" :class="selectedIds.includes({{ $sale->id }}) ? 'bg-blue-50/40' : ''">
                    <td class="px-2 py-2 text-center" @click.stop>
                        <input type="checkbox" :value="{{ $sale->id }}" x-model.number="selectedIds" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer">
                    </td>
                    <td class="px-1.5 py-2 text-center text-slate-400 text-[11px]">{{ $sales->firstItem() + $loop->index }}</td>
                    <td class="px-2.5 py-2 hidden xl:table-cell">
                        <p class="text-[11px] text-slate-600 whitespace-nowrap">{{ $sale->created_at->format('d M Y') }}</p>
                        <p class="text-[10px] text-slate-400">{{ $sale->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-2.5 py-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                               class="w-6 h-6 rounded-md bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white border border-blue-200/80 flex items-center justify-center flex-shrink-0 transition-all hover:scale-105 shadow-2xs"
                               title="View Sale">
                                <i class="fas fa-eye text-[10px]"></i>
                            </a>
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                               class="font-semibold text-slate-800 hover:text-blue-600 text-[12.5px] font-mono transition-colors">
                                {{ $sale->invoice_no }}
                            </a>
                            @if($isSupplier)
                            <span class="inline-flex items-center gap-1 text-[9px] font-black px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300">
                                <i class="fas fa-store text-[8px] text-amber-600"></i> SUP
                            </span>
                            @elseif($isReseller)
                            <span class="inline-flex items-center gap-1 text-[9px] font-black px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-900 border border-purple-300">
                                <i class="fas fa-handshake text-[8px] text-purple-600"></i> RES
                            </span>
                            @elseif($isWeb)
                            <span class="inline-flex items-center gap-1 text-[9px] font-black px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-900 border border-blue-300">
                                <i class="fas fa-globe text-[8px] text-blue-600"></i> WEB
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[9px] font-black px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-300">
                                <i class="fas fa-cash-register text-[8px] text-emerald-600"></i> POS
                            </span>
                            @endif
                        </div>
                        @if($sale->payment_method)
                        <div class="mt-0.5">
                            <span class="text-[10px] text-slate-400 capitalize">{{ str_replace('_',' ',$sale->payment_method) }}</span>
                        </div>
                        @endif
                    </td>

                    {{-- Sale Type / Source (Channel) --}}
                    <td class="px-2.5 py-2">
                        @if($isSupplier)
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 text-[9.5px] font-black px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-950 border border-amber-300 shadow-2xs">
                                    <i class="fas fa-store text-[8px] text-amber-600"></i> Supplier
                                </span>
                                @if($supplierObj)
                                    <div class="text-[10.5px] font-bold text-amber-900 truncate max-w-[120px]" title="{{ $supplierObj->display_name }}">
                                        {{ $supplierObj->display_name }}
                                    </div>
                                @endif
                                @if($isWeb)
                                    <div><span class="inline-flex items-center text-[8.5px] px-1 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-200 font-medium">via Web</span></div>
                                @endif
                            </div>
                        @elseif($isReseller)
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 text-[9.5px] font-black px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-950 border border-purple-300 shadow-2xs">
                                    <i class="fas fa-handshake text-[8px] text-purple-600"></i> Reseller
                                </span>
                                @if($sale->reseller)
                                    <div class="text-[10.5px] font-bold text-purple-900 truncate max-w-[120px]" title="{{ $sale->reseller->name }}">
                                        {{ $sale->reseller->name }}
                                    </div>
                                    @if($sale->reseller_profit > 0)
                                        <div class="text-[9px] text-purple-700 font-semibold">Profit: ৳{{ number_format($sale->reseller_profit, 2) }}</div>
                                    @endif
                                @endif
                            </div>
                        @elseif($isWeb)
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 text-[9.5px] font-black px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-950 border border-blue-300 shadow-2xs">
                                    <i class="fas fa-globe text-[8px] text-blue-600"></i> Website
                                </span>
                                <div class="text-[10px] font-medium text-slate-500">Storefront</div>
                            </div>
                        @else
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 text-[9.5px] font-black px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-950 border border-emerald-300 shadow-2xs">
                                    <i class="fas fa-cash-register text-[8px] text-emerald-600"></i> POS
                                </span>
                                <div class="text-[10px] font-medium text-slate-500">Counter</div>
                            </div>
                        @endif
                    </td>

                    <td class="px-2.5 py-2 hidden md:table-cell">
                        <span class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $sale->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-2.5 py-2">
                        @php
                            $cPhone = $sale->customer?->phone ?? $sale->reseller?->phone ?? $sale->supplier?->phone;
                            $cName  = $sale->customer?->name ?? ($sale->reseller ? 'Reseller: '.$sale->reseller->name : ($sale->supplier ? 'Supplier: '.$sale->supplier->name : 'Walk-in'));
                            $cWa    = $sale->customer?->whatsapp_number ?? ($cPhone ? preg_replace('/[^\d]/', '', $cPhone) : null);
                        @endphp
                        <p class="text-[12.5px] text-slate-700 font-medium">{{ $cName }}</p>
                        @if($cPhone)
                        <p class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                            <span><i class="fas fa-phone text-[9px] mr-0.5"></i>{{ $cPhone }}</span>
                            <button type="button"
                                    @click.stop="openEpbxCallModal({ id: {{ $sale->id }}, invoice: '{{ $sale->invoice_no }}', customer: @js($cName), phone: '{{ $cPhone }}', amount: '{{ round($sale->total ?? 0) }}' })"
                                    title="MicroSIP / Zoiper Direct Call (096XX Cloud PBX)"
                                    class="w-5 h-5 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200/80 flex items-center justify-center flex-shrink-0 transition-all hover:scale-110 shadow-2xs cursor-pointer">
                                <i class="fas fa-phone-volume text-[9px] pointer-events-none"></i>
                            </button>
                            @if($cWa)
                            <a href="https://wa.me/{{ $cWa }}" target="_blank" rel="noopener" title="Chat on WhatsApp"
                               class="w-5 h-5 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fab fa-whatsapp text-[11px]"></i>
                            </a>
                            @endif
                            <button type="button"
                                    @click.stop="openBdCheck('{{ $cPhone }}', @js($cName))"
                                    title="Check BD Courier Status"
                                    class="w-5 h-5 rounded bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-200/70 flex items-center justify-center flex-shrink-0 transition-all hover:scale-110 shadow-xs cursor-pointer">
                                <i class="fas fa-truck-fast text-[9px] pointer-events-none"></i>
                            </button>
                        </p>
                        @endif
                    </td>

                    {{-- Products --}}
                    <td class="px-2.5 py-2">
                        <div class="space-y-1 max-w-[200px]">
                            @foreach($sale->saleItems as $item)
                            @php
                                $img = $item->product?->first_image_url;
                            @endphp
                            <div class="flex items-center gap-1.5">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $item->product?->name }}"
                                         class="w-7 h-7 rounded-md object-cover border border-slate-200 flex-shrink-0 shadow-2xs">
                                @else
                                    <div class="w-7 h-7 rounded-md bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0 text-slate-400">
                                        <i class="fas fa-box text-[10px] text-slate-300"></i>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-800 text-[11px] leading-tight truncate" title="{{ $item->product?->name }}">
                                        {{ $item->product?->name ?? 'Product' }}
                                    </p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        @if($item->variant_name)
                                            <span class="text-[8.5px] font-medium text-purple-700 bg-purple-50 border border-purple-100 px-1 py-0.2 rounded truncate max-w-[90px]" title="{{ $item->variant_name }}">
                                                {{ $item->variant_name }}
                                            </span>
                                        @endif
                                        <span class="text-[9.5px] font-bold text-slate-500 bg-slate-100 px-1 py-0.2 rounded">
                                            ×{{ $item->quantity }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </td>

                    <td class="px-2.5 py-2 text-right font-bold text-slate-800 whitespace-nowrap">{{ $currency }}{{ number_format($sale->total,0) }}</td>
                    <td class="px-2 py-2 text-right text-emerald-600 text-[11.5px] hidden 2xl:table-cell whitespace-nowrap">{{ $currency }}{{ number_format($sale->paid_amount,0) }}</td>
                    <td class="px-2 py-2 text-right hidden 2xl:table-cell whitespace-nowrap">
                        @if($sale->due_amount > 0)
                        <span class="font-semibold text-red-500 text-[11.5px]">{{ $currency }}{{ number_format($sale->due_amount,0) }}</span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-center">
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $stBg }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </td>
                    {{-- Order Status Dropdown --}}
                    <td class="px-2.5 py-2 text-center">
                        <div x-data="{
                            status: '{{ $sale->order_status }}',
                            async change(s) {
                                const csrf = document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('[name=csrf-token]')?.content;
                                const res = await fetch('{{ route('admin.all-sales.status', $sale) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrf,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: new URLSearchParams({order_status: s})
                                });
                                if (res.ok) {
                                    this.status = s;
                                    showToast('Order status updated!', 'success');
                                } else {
                                    showToast('Failed to update status.', 'error');
                                }
                                openStatusRow = null;
                            }
                        }"
                             @click.outside="if (openStatusRow === {{ $sale->id }}) openStatusRow = null"
                             class="relative inline-block text-left">
                            <button type="button" @click.stop="openStatusRow = (openStatusRow === {{ $sale->id }} ? null : {{ $sale->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all cursor-pointer hover:opacity-80 shadow-2xs border"
                                    :class="statusColors[status] || 'bg-slate-100 text-slate-600 border-slate-200'">
                                <span x-text="statusLabels[status] || status"></span>
                                <i class="fas fa-chevron-down text-[8px]"></i>
                            </button>
                            <div x-show="openStatusRow === {{ $sale->id }}" x-cloak
                                 class="absolute right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-30 w-44 overflow-hidden py-1 text-left">
                                @foreach($orderStatuses as $s)
                                <button type="button" @click="change('{{ $s->key }}')"
                                        class="w-full text-left px-3 py-2 text-xs transition-colors hover:bg-slate-50 flex items-center justify-between"
                                        :class="status === '{{ $s->key }}' ? 'text-indigo-600 font-bold bg-indigo-50' : 'text-slate-600'">
                                    <span>{{ $s->label }}</span>
                                    <span x-show="status === '{{ $s->key }}'"><i class="fas fa-check text-[10px]"></i></span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </td>
                    {{-- Courier --}}
                    <td class="px-2.5 py-2 text-center" id="courier-cell-{{ $sale->id }}">
                        @if($sale->isSentToCourier())
                            @php
                                $cColor = match($sale->courier_name) {
                                    'steadfast' => 'teal',
                                    'pathao'    => 'rose',
                                    'redx'      => 'indigo',
                                    default     => 'blue',
                                };
                                $cIcon = match($sale->courier_name) {
                                    'steadfast' => 'fas fa-shipping-fast',
                                    'pathao'    => 'fas fa-motorcycle',
                                    'redx'      => 'fas fa-truck-fast',
                                    default     => 'fas fa-truck',
                                };
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-{{ $cColor }}-50 text-{{ $cColor }}-700 border border-{{ $cColor }}-200 shadow-2xs">
                                    <i class="{{ $cIcon }} text-[9px]"></i>
                                    {{ $sale->courier_display_name }}
                                </span>
                                <div class="flex items-center gap-1 text-[11px] font-mono text-slate-700">
                                    <span title="Tracking: {{ $sale->courier_tracking_code }}">{{ Str::limit($sale->courier_tracking_code, 13) }}</span>
                                    <button type="button" @click="copyText('{{ $sale->courier_tracking_code }}')" title="Copy Tracking Code"
                                            class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                                        <i class="fas fa-copy text-[10px]"></i>
                                    </button>
                                </div>
                                @if($sale->courier_status)
                                <span class="text-[9.5px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium capitalize" id="cstatus-{{ $sale->id }}">
                                    {{ str_replace('_', ' ', $sale->courier_status) }}
                                </span>
                                @endif
                            </div>
                        @else
                            @if(($activeCouriers ?? collect())->isEmpty())
                                <span class="text-[11px] text-slate-300">—</span>
                            @elseif(($activeCouriers ?? collect())->count() === 1)
                                @php $singleCourier = ($activeCouriers ?? collect())->first(); @endphp
                                <button type="button"
                                        @click="openCourierModal({
                                            saleId: {{ $sale->id }},
                                            invoice: '{{ $sale->invoice_no }}',
                                            courierCode: '{{ $singleCourier->code }}',
                                            courierName: '{{ $singleCourier->name }}',
                                            recipientName: @js($sale->customer?->name ?? 'Walk-in Customer'),
                                            recipientPhone: @js($sale->customer?->phone ?? ''),
                                            recipientAddress: @js($sale->customer?->address ?? ''),
                                            district: @js($sale->district ?? ''),
                                            thana: @js($sale->thana ?? ''),
                                            codAmount: {{ $sale->due_amount > 0 ? $sale->due_amount : ($sale->payment_status === 'paid' ? 0 : $sale->total) }},
                                            note: @js($sale->note ?? '')
                                        })"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-all hover:scale-105 shadow-2xs cursor-pointer">
                                    <i class="fas fa-paper-plane text-[9px]"></i>
                                    <span>Send to {{ $singleCourier->name }}</span>
                                </button>
                            @else
                                <button type="button"
                                        @click="openCourierModal({
                                            saleId: {{ $sale->id }},
                                            invoice: '{{ $sale->invoice_no }}',
                                            courierCode: '{{ ($activeCouriers ?? collect())->first()->code }}',
                                            courierName: '{{ ($activeCouriers ?? collect())->first()->name }}',
                                            recipientName: @js($sale->customer?->name ?? 'Walk-in Customer'),
                                            recipientPhone: @js($sale->customer?->phone ?? ''),
                                            recipientAddress: @js($sale->customer?->address ?? ''),
                                            district: @js($sale->district ?? ''),
                                            thana: @js($sale->thana ?? ''),
                                            codAmount: {{ $sale->due_amount > 0 ? $sale->due_amount : ($sale->payment_status === 'paid' ? 0 : $sale->total) }},
                                            note: @js($sale->note ?? '')
                                        })"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-all hover:scale-105 shadow-2xs cursor-pointer">
                                    <i class="fas fa-truck-fast text-[9px]"></i>
                                    <span>Send to Courier</span>
                                </button>
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Print --}}
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}?auto_print=1" target="_blank"
                               class="w-7 h-7 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-700 flex items-center justify-center transition-colors" title="Print">
                                <i class="fas fa-print text-[11px]"></i>
                            </a>
                            {{-- Edit --}}
                            <a href="{{ route('branch.sales.edit', [$sale->vendor_id, $sale]) }}"
                               class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </a>
                            {{-- Pay --}}
                            @if($sale->due_amount > 0)
                            <button @click="openPay({{ $sale->id }}, {{ $sale->vendor_id }}, {{ $sale->due_amount }})"
                                    class="w-7 h-7 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 flex items-center justify-center transition-colors" title="Add Payment">
                                <i class="fas fa-money-bill-wave text-[11px]"></i>
                            </button>
                            @endif
                            {{-- Note --}}
                            <button type="button"
                                    @click.stop="openNotes({{ $sale->id }}, '{{ $sale->invoice_no }}', @js($sale->customer?->name ?? 'Walk-in'))"
                                    class="relative w-7 h-7 rounded-lg border flex items-center justify-center transition-colors cursor-pointer {{ $sale->notes_count > 0 ? 'bg-amber-100 border-amber-300 text-amber-700 hover:bg-amber-200 shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 border-slate-200' }}" title="Order Notes">
                                <i class="fas fa-sticky-note text-[11px] pointer-events-none"></i>
                                <span id="note-badge-{{ $sale->id }}"
                                      class="absolute -top-1 -right-1 min-w-[14px] h-[14px] px-0.5 rounded-full bg-amber-500 text-white text-[8.5px] font-bold flex items-center justify-center leading-none {{ $sale->notes_count > 0 ? '' : 'hidden' }}">
                                    {{ $sale->notes_count ?? 0 }}
                                </span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="px-4 py-16 text-center text-slate-400">
                        <i class="fas fa-receipt text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No sales found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $sales->links() }}</div>
    @endif
</div>

{{-- ══ PAY MODAL ══ --}}
<div x-show="payOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="payOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.outside="payOpen = false">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-emerald-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-[14px] font-bold text-slate-800">Add Payment</h3>
                <p class="text-[11.5px] text-slate-400" x-text="'Max due: {{ $currency }}' + payMax.toLocaleString()"></p>
            </div>
        </div>
        <form :action="payUrl" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" x-model="payAmount" step="0.01" min="0.01" :max="payMax" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Payment Method</label>
                <select name="payment_method" x-model="payMethod"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                    @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="payOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
                    Confirm Payment
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ── BD Courier Status Modal ─────────────────────────────────────────── --}}
<div x-show="bdModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="bdModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="bdModal = false">
        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-100 flex-shrink-0">
                    <i class="fas fa-truck-fast text-sm"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-bold text-slate-800 text-[15px]">BD Courier Status</p>
                        <span class="text-[10px] font-semibold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">api.bdcourier.com</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span x-show="bdName" x-text="bdName + ' · '" class="font-medium text-slate-700"></span>
                        <span class="font-mono font-semibold text-slate-600" x-text="bdPhone || 'No phone number'"></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" @click="checkBdCourier()" :disabled="bdLoading"
                        title="Re-check / Refresh"
                        class="text-slate-400 hover:text-indigo-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                    <i class="fas fa-arrows-rotate text-xs" :class="bdLoading ? 'fa-spin' : ''"></i>
                </button>
                <button @click="bdModal = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-5">
            {{-- Loading state --}}
            <div x-show="bdLoading" class="py-12 text-center text-slate-400">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-spinner fa-spin text-xl"></i>
                </div>
                <p class="text-sm font-medium text-slate-700">BD Courier থেকে তথ্য লোড হচ্ছে...</p>
                <p class="text-xs text-slate-400 mt-1 font-mono" x-text="bdPhone"></p>
            </div>

            {{-- Error state --}}
            <template x-if="!bdLoading && bdError">
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-triangle-exclamation text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-red-800 text-sm">তথ্য লোড করা যায়নি</h4>
                            <p class="text-red-700 text-xs mt-1 leading-relaxed" x-text="bdError"></p>
                            <button type="button" @click="checkBdCourier()"
                                    class="mt-3 text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition-colors">
                                <i class="fas fa-rotate-right text-[10px]"></i> আবার চেষ্টা করুন
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Data display --}}
            <template x-if="!bdLoading && !bdError && bdRiskInfo">
                <div class="space-y-4">
                    {{-- Status Banner with SVG Gauge --}}
                    <div class="bg-gradient-to-br from-slate-50 to-indigo-50/40 border border-slate-100 rounded-2xl p-4 flex items-center gap-4">
                        {{-- Circular SVG Gauge --}}
                        <div class="relative w-24 h-24 flex-shrink-0">
                            <svg width="96" height="96" viewBox="0 0 100 100" class="-rotate-90">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none" :stroke="bdRiskInfo.ring" stroke-width="8" stroke-linecap="round"
                                        stroke-dasharray="251.32"
                                        :stroke-dashoffset="251.32 * (1 - Math.min(100, Math.max(0, bdRiskInfo.deliverPct)) / 100)"
                                        style="transition: stroke-dashoffset .6s ease, stroke .3s ease"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-lg font-black text-slate-800 leading-none" x-text="bdRiskInfo.deliverPct + '%'"></span>
                                <span class="text-[9px] text-slate-400 mt-0.5">Success</span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                  :class="bdRiskInfo.badgeBg"
                                  x-text="bdRiskInfo.label"></span>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed" x-text="bdRiskInfo.message"></p>
                            <p class="text-[11px] font-mono text-slate-400 mt-1">
                                <i class="fas fa-mobile-screen mr-1"></i><span x-text="bdPhone"></span>
                            </p>
                        </div>
                    </div>

                    {{-- 3 Key Metric Cards --}}
                    <div class="grid grid-cols-3 gap-3">
                        {{-- Total Orders --}}
                        <div class="bg-blue-50/80 border border-blue-100 rounded-2xl p-3.5 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-1.5">
                                <i class="fas fa-boxes-stacked text-xs"></i>
                            </div>
                            <p class="text-2xl font-black text-blue-700 leading-none" x-text="bdRiskInfo.total"></p>
                            <p class="text-[11px] font-bold text-blue-600/90 mt-1.5">মোট অর্ডার</p>
                            <p class="text-[9px] text-slate-400">Total Parcels</p>
                        </div>

                        {{-- Delivered --}}
                        <div class="bg-emerald-50/80 border border-emerald-100 rounded-2xl p-3.5 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-1.5">
                                <i class="fas fa-circle-check text-xs"></i>
                            </div>
                            <p class="text-2xl font-black text-emerald-700 leading-none" x-text="bdRiskInfo.delivered"></p>
                            <p class="text-[11px] font-bold text-emerald-600/90 mt-1.5">ডেলিভার্ড</p>
                            <p class="text-[9px] text-slate-400">Delivered</p>
                        </div>

                        {{-- Cancelled --}}
                        <div class="bg-rose-50/80 border border-rose-100 rounded-2xl p-3.5 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center mb-1.5">
                                <i class="fas fa-circle-xmark text-xs"></i>
                            </div>
                            <p class="text-2xl font-black text-rose-700 leading-none" x-text="bdRiskInfo.cancelled"></p>
                            <p class="text-[11px] font-bold text-rose-600/90 mt-1.5">বাতিল</p>
                            <p class="text-[9px] text-slate-400">Cancelled</p>
                        </div>
                    </div>

                    {{-- Courier-wise Breakdown --}}
                    <div x-show="bdCourierRows.length > 0" class="border border-slate-100 rounded-2xl p-4 bg-white">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">কুরিয়ার ভিত্তিক রিপোর্ট</p>
                            <span class="text-[11px] text-slate-400" x-text="bdCourierRows.length + ' couriers'"></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[11px] text-slate-400">
                                        <th class="py-2 text-left font-semibold">Courier</th>
                                        <th class="py-2 text-center font-semibold">Total</th>
                                        <th class="py-2 text-center font-semibold text-emerald-600">Delivered</th>
                                        <th class="py-2 text-center font-semibold text-rose-500">Cancel</th>
                                        <th class="py-2 text-right font-semibold">Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <template x-for="c in bdCourierRows" :key="c.name">
                                        <tr>
                                            <td class="py-2.5 font-semibold text-slate-700">
                                                <div class="flex items-center gap-2">
                                                    <template x-if="c.logo">
                                                        <img :src="c.logo" :alt="c.name" class="w-4 h-4 object-contain">
                                                    </template>
                                                    <span x-text="c.name"></span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-center font-medium text-slate-600" x-text="c.total_parcel"></td>
                                            <td class="py-2.5 text-center font-bold text-emerald-600" x-text="c.success_parcel"></td>
                                            <td class="py-2.5 text-center font-bold text-rose-500" x-text="c.cancelled_parcel"></td>
                                            <td class="py-2.5 text-right font-bold text-slate-800" x-text="c.success_ratio + '%'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Reports list if any --}}
                    <div x-show="bdResult && bdResult.reports && bdResult.reports.length > 0" class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5">
                        <div class="flex items-center gap-2 mb-2 text-amber-800 font-bold text-xs">
                            <i class="fas fa-triangle-exclamation text-amber-500"></i>
                            <span>রিপোর্ট ও অভিযোগ</span>
                        </div>
                        <ul class="space-y-1 text-xs text-amber-900 list-disc list-inside">
                            <template x-for="(rep, i) in (bdResult?.reports || [])" :key="i">
                                <li x-text="typeof rep === 'string' ? rep : (rep.note || rep.reason || JSON.stringify(rep))"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- ── Order Notes Modal ─────────────────────────────────────────── --}}
<div x-show="notesModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="notesModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden" @click.outside="notesModal = false">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-gradient-to-r from-amber-50/80 to-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-200 flex-shrink-0">
                    <i class="fas fa-sticky-note text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-800 text-[15px]">Order Notes</h3>
                        <span class="font-mono text-xs bg-slate-100 text-slate-700 font-semibold px-2 py-0.5 rounded-md" x-text="'#' + activeInvoice"></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="activeCustomer ? 'Customer: ' + activeCustomer : ''"></p>
                </div>
            </div>
            <button @click="notesModal = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Modal Scrollable Body: Notes List --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-3.5 bg-slate-50/50 min-h-[180px] max-h-[380px]">
            {{-- Loading --}}
            <div x-show="notesLoading" class="py-12 text-center text-slate-400">
                <i class="fas fa-spinner fa-spin text-2xl text-amber-500 mb-2"></i>
                <p class="text-xs">নোট লোড হচ্ছে...</p>
            </div>

            {{-- Empty State --}}
            <template x-if="!notesLoading && notesList.length === 0">
                <div class="py-10 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2.5">
                        <i class="fas fa-note-sticky text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">কোনো নোট যোগ করা হয়নি</p>
                    <p class="text-xs text-slate-400 mt-1">অর্ডার সংক্রান্ত যেকোনো নির্দেশনা বা তথ্য নিচে লিখে যোগ করুন।</p>
                </div>
            </template>

            {{-- Notes Timeline items --}}
            <template x-for="item in notesList" :key="item.id">
                <div class="bg-white border border-slate-100 rounded-xl p-3.5 shadow-xs transition-all hover:border-amber-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold">
                                <span x-text="item.user_name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-800" x-text="item.user_name"></span>
                                <span class="text-[9.5px] font-semibold px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 ml-1" x-text="item.user_role"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block" x-text="item.created_at_formatted"></span>
                            <span class="text-[9.5px] text-amber-600 font-medium" x-text="item.time_ago"></span>
                        </div>
                    </div>
                    <div class="bg-amber-50/40 border border-amber-100/60 rounded-lg p-2.5 text-xs text-slate-700 whitespace-pre-wrap leading-relaxed font-sans" x-text="item.note"></div>
                </div>
            </template>
        </div>

        {{-- Modal Footer: Add Note Form --}}
        <div class="p-4 border-t border-slate-100 bg-white">
            {{-- Quick Chips --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2.5 mb-2 scrollbar-none text-[11px]">
                <span class="text-slate-400 text-[10px] font-semibold whitespace-nowrap mr-0.5">কুইক নোট:</span>
                <button type="button" @click="insertQuickNote('কাস্টমার প্রোডাক্ট নিবে না')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    ❌ প্রোডাক্ট নিবে না
                </button>
                <button type="button" @click="insertQuickNote('পরে কল দিতে বলেছে')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    📞 পরে কল দিবে
                </button>
                <button type="button" @click="insertQuickNote('ঠিকানা পরিবর্তন করতে বলেছে')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    📍 ঠিকানা পরিবর্তন
                </button>
                <button type="button" @click="insertQuickNote('ডেলিভারি কনফার্ম করেছে')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    ✅ ডেলিভারি কনফার্ম
                </button>
            </div>

            <form @submit.prevent="submitNote" class="space-y-2.5">
                <textarea x-model="newNoteText" rows="2" required placeholder="এই অর্ডারের জন্য নোট লিখুন (যেমন: কাস্টমার প্রোডাক্ট নিবে না, ইত্যাদি)..."
                          class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent resize-none leading-relaxed"></textarea>
                
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">
                        <i class="fas fa-user-pen mr-1"></i>নোট আপনার নামে সেভ হবে
                    </span>
                    <button type="submit" :disabled="submittingNote || !newNoteText.trim()"
                            class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors shadow-sm shadow-amber-200">
                        <i class="fas fa-paper-plane text-[10px]" x-show="!submittingNote"></i>
                        <i class="fas fa-spinner fa-spin text-[10px]" x-show="submittingNote"></i>
                        <span x-text="submittingNote ? 'সংরক্ষণ হচ্ছে...' : 'নোট যোগ করুন'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Send to Courier Modal ────────────────────────────────────── --}}
<div x-show="courierModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="courierModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[95vh] flex flex-col overflow-hidden" @click.outside="courierModal = false">
        {{-- Header --}}
        <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-gradient-to-r from-blue-50/70 to-indigo-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-200 flex-shrink-0">
                    <i class="fas fa-truck-fast text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-800 text-[15px]">Send Order to Courier</h3>
                        <span class="font-mono text-xs bg-white text-blue-700 font-semibold px-2 py-0.5 rounded-md border border-blue-200" x-text="'#' + courierForm.invoice"></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">কুরিয়ারে পার্সেল তৈরি ও বুকিং কনফার্ম করুন</p>
                </div>
            </div>
            <button @click="courierModal = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/80 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Body Form --}}
        <form @submit.prevent="submitCourierDispatch()" class="p-5 space-y-3.5 overflow-y-auto flex-1">
            @if(($activeCouriers ?? collect())->count() > 1)
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Select Courier Service</label>
                <div class="grid grid-cols-{{ min(($activeCouriers ?? collect())->count(), 3) }} gap-2">
                    @foreach($activeCouriers as $c)
                    <label class="cursor-pointer border rounded-xl p-2.5 flex items-center gap-2 transition-all"
                           :class="courierForm.courierCode === '{{ $c->code }}' ? 'border-blue-500 bg-blue-50/50 text-blue-900 ring-2 ring-blue-200' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <input type="radio" name="courier_code" value="{{ $c->code }}" x-model="courierForm.courierCode" class="sr-only">
                        <i class="{{ $c->icon }} text-sm text-blue-600"></i>
                        <span class="text-xs font-semibold">{{ $c->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Recipient Name *</label>
                    <input type="text" x-model="courierForm.recipientName" required
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Phone Number *</label>
                    <input type="text" x-model="courierForm.recipientPhone" required
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Delivery Address *</label>
                <textarea x-model="courierForm.recipientAddress" rows="2" required
                          class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="House, Road, Area, Thana, District"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">COD Collection Amount (৳) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">৳</span>
                        <input type="number" step="any" min="0" x-model="courierForm.codAmount" required
                               class="w-full text-xs rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">কাস্টমারের কাছ থেকে প্রদেয় ক্যাশ অন ডেলিভারি টাকা</p>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Delivery Area / City</label>
                    <input type="text" x-model="courierForm.deliveryArea"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g. Dhaka, Chittagong...">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Instruction / Note (Optional)</label>
                <input type="text" x-model="courierForm.note"
                       class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g. Handle with care / ডেলিভারির পূর্বে কল করবেন">
            </div>

            <div x-show="courierError" x-cloak class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-2">
                <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>
                <span x-text="courierError" class="flex-1"></span>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" @click="courierModal = false" :disabled="courierSending"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" :disabled="courierSending"
                        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-semibold px-5 py-2.5 rounded-xl shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-paper-plane text-[10px]" :class="{ 'fa-spin fa-spinner': courierSending }"></i>
                    <span x-text="courierSending ? 'Booking Order...' : 'Confirm & Book Parcel'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ FLOATING BULK ACTIONS BAR ══ --}}
<div x-show="selectedIds.length > 0" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 translate-y-4 scale-95"
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl border border-slate-700 flex items-center gap-4">
    <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
        <span class="text-xs font-semibold">
            <span class="text-blue-400 font-bold" x-text="selectedIds.length"></span> orders selected
        </span>
    </div>

    <div class="h-4 w-px bg-slate-700"></div>

    <button type="button" @click="printBulk()"
            class="bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2 shadow-md cursor-pointer">
        <i class="fas fa-print"></i>
        <span>Print Selected</span>
    </button>

    <button type="button" @click="selectedIds = []"
            class="text-slate-400 hover:text-white text-xs font-medium px-2 py-1 transition-colors cursor-pointer" title="Deselect All">
        <i class="fas fa-xmark mr-1"></i> Clear
    </button>
</div>

</div>{{-- end x-data="salesAdminPage()" --}}

{{-- ── 096XX Cloud PBX & IP Telephony Call Center Modal ── --}}
@include('partials.epbx-call-modal')
@endsection

@push('scripts')
<script>
function salesAdminPage() {
    return {
        openEpbxCallModal(data) {
            if (typeof window.openEpbxCallModal === 'function') {
                window.openEpbxCallModal(data);
            } else {
                window.dispatchEvent(new CustomEvent('open-epbx-modal', { detail: data }));
            }
        },

        pageSaleIds: @json($sales->pluck('id')->values()),
        selectedIds: [],
        openStatusRow: null,
        statusLabels: @json($orderStatuses->pluck('label', 'key')),
        statusColors: @json($orderStatuses->mapWithKeys(fn($s) => [$s->key => "bg-{$s->color}-100 text-{$s->color}-700 border border-{$s->color}-200"])),

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = [...new Set([...this.selectedIds, ...this.pageSaleIds])];
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.pageSaleIds.includes(id));
            }
        },

        isAllSelected() {
            return this.pageSaleIds.length > 0 && this.pageSaleIds.every(id => this.selectedIds.includes(id));
        },

        printBulk() {
            if (this.selectedIds.length === 0) return;
            const url = '{{ route('admin.all-sales.bulk-print') }}?ids=' + this.selectedIds.join(',');
            window.open(url, '_blank');
        },

        payOpen: false, payMax: 0, payAmount: 0, payMethod: 'cash', payUrl: '',
        openPay(id, vendor, due) {
            this.payMax    = due;
            this.payAmount = due;
            this.payMethod = 'cash';
            this.payUrl    = `/branch/${vendor}/sales/${id}/add-payment`;
            this.payOpen   = true;
        },

        courierModal: false,
        courierSending: false,
        courierError: null,
        courierForm: {
            saleId: null,
            invoice: '',
            courierCode: '{{ ($activeCouriers ?? collect())->first()?->code ?? "" }}',
            courierName: '',
            recipientName: '',
            recipientPhone: '',
            recipientAddress: '',
            deliveryArea: '',
            codAmount: 0,
            note: ''
        },

        openCourierModal(data) {
            this.courierError = null;
            this.courierForm = {
                saleId: data.saleId,
                invoice: data.invoice,
                courierCode: data.courierCode || '{{ ($activeCouriers ?? collect())->first()?->code ?? "" }}',
                courierName: data.courierName || '',
                recipientName: data.recipientName || '',
                recipientPhone: data.recipientPhone || '',
                recipientAddress: data.recipientAddress || [data.thana, data.district].filter(Boolean).join(', '),
                deliveryArea: data.district || data.thana || 'Dhaka',
                codAmount: data.codAmount || 0,
                note: data.note || ''
            };
            this.courierModal = true;
        },

        async submitCourierDispatch() {
            this.courierSending = true;
            this.courierError = null;

            try {
                const res = await fetch(`/admin/branch/${this.courierForm.saleId}/send-courier`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]')?.content || document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.courierForm)
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.courierModal = false;
                    alert(data.message);

                    const cell = document.getElementById('courier-cell-' + this.courierForm.saleId);
                    if (cell) {
                        const cColor = data.courier_code === 'steadfast' ? 'teal' : (data.courier_code === 'pathao' ? 'rose' : 'indigo');
                        const cIcon = data.courier_code === 'steadfast' ? 'fas fa-shipping-fast' : (data.courier_code === 'pathao' ? 'fas fa-motorcycle' : 'fas fa-truck-fast');
                        cell.innerHTML = `
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-${cColor}-50 text-${cColor}-700 border border-${cColor}-200 shadow-2xs">
                                    <i class="${cIcon} text-[9px]"></i>
                                    ${data.courier_name}
                                </span>
                                <div class="flex items-center gap-1 text-[11px] font-mono text-slate-700">
                                    <span>${data.tracking_code}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('${data.tracking_code}'); alert('Tracking copied!')" title="Copy Tracking Code"
                                            class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                                        <i class="fas fa-copy text-[10px]"></i>
                                    </button>
                                </div>
                                <span class="text-[9.5px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium capitalize">
                                    ${(data.status || 'pending').replace('_', ' ')}
                                </span>
                            </div>
                        `;
                    }
                } else {
                    this.courierError = data.message || 'Courier এ অর্ডার পাঠাতে সমস্যা হয়েছে।';
                }
            } catch (err) {
                this.courierError = 'সার্ভার এরর: সংযোগ স্থাপন করা যায়নি।';
            } finally {
                this.courierSending = false;
            }
        },

        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Tracking code copied: ' + text);
            });
        },

        bdModal: false,
        bdPhone: '',
        bdName: '',
        bdLoading: false,
        bdResult: null,
        bdError: null,

        openBdCheck(phone, name) {
            this.notesModal = false;
            this.payOpen    = false;
            this.bdPhone    = phone || '';
            this.bdName     = name || '';
            this.bdResult   = null;
            this.bdError    = null;
            this.bdModal    = true;
            if (this.bdPhone) {
                this.checkBdCourier();
            } else {
                this.bdError = 'গ্রাহকের কোনো ফোন নম্বর পাওয়া যায়নি।';
            }
        },

        get bdRiskInfo() {
            if (!this.bdResult || !this.bdResult.summary) return null;
            const s = this.bdResult.summary;
            const total      = Number(s.total_parcel || 0);
            const delivered  = Number(s.success_parcel || 0);
            const cancelled  = Number(s.cancelled_parcel || 0);
            const deliverPct = Number(s.success_ratio !== undefined ? s.success_ratio : (total > 0 ? Math.round((delivered / total) * 1000) / 10 : 0));

            let key = 'none';
            if (total > 0) {
                key = deliverPct >= 80 ? 'excellent' : (deliverPct >= 60 ? 'good' : (deliverPct >= 40 ? 'average' : 'risky'));
            }

            const config = {
                excellent: { label: 'নিরাপদ গ্রাহক (Safe)',           message: 'ডেলিভারি সফলতার হার অত্যন্ত চমৎকার।',        ring: '#22c55e', text: 'text-emerald-600', badgeBg: 'bg-emerald-100 text-emerald-700 border border-emerald-200' },
                good:      { label: 'সন্তোষজনক (Good)',               message: 'মোটামুটি নির্ভরযোগ্য ডেলিভারি ইতিহাস।',        ring: '#0ea5e9', text: 'text-sky-600',     badgeBg: 'bg-sky-100 text-sky-700 border border-sky-200' },
                average:   { label: 'মাঝারি ঝুঁকি (Average)',        message: 'পার্সেল পাঠানোর পূর্বে অর্ডারটি নিশ্চিত করুন।',   ring: '#f59e0b', text: 'text-amber-600',   badgeBg: 'bg-amber-100 text-amber-700 border border-amber-200' },
                risky:     { label: 'উচ্চ ঝুঁকিপূর্ণ (High Risk)',     message: 'বাতিল বা রিটার্ন হওয়ার সম্ভাবনা বেশি।',       ring: '#ef4444', text: 'text-red-600',     badgeBg: 'bg-red-100 text-red-700 border border-red-200' },
                none:      { label: 'নতুন গ্রাহক (No Data)',          message: 'পূর্বে কোনো কুরিয়ার ডেলিভারি ইতিহাস নেই।',      ring: '#94a3b8', text: 'text-slate-500',   badgeBg: 'bg-slate-100 text-slate-600 border border-slate-200' },
            };

            return {
                deliverPct,
                total,
                delivered,
                cancelled,
                ...(config[key] || config.none)
            };
        },

        get bdCourierRows() {
            if (!this.bdResult || !this.bdResult.couriers) return [];
            return this.bdResult.couriers;
        },

        async checkBdCourier() {
            if (!this.bdPhone) return;
            this.bdLoading = true;
            this.bdError   = null;

            try {
                const res = await fetch('{{ route('admin.bd-courier-check') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]')?.content || document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ phone: this.bdPhone }),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.bdResult = data;
                } else {
                    this.bdError = data.error || 'BD Courier তথ্য লোড করা যায়নি।';
                }
            } catch (e) {
                this.bdError = 'সার্ভারের সাথে সংযোগ বিচ্ছিন্ন হয়েছে।';
            }

            this.bdLoading = false;
        },

        notesModal: false,
        activeSaleId: null,
        activeInvoice: '',
        activeCustomer: '',
        notesList: [],
        notesLoading: false,
        newNoteText: '',
        submittingNote: false,

        openNotes(saleId, invoiceNo, customerName) {
            this.bdModal        = false;
            this.payOpen        = false;
            this.activeSaleId   = saleId;
            this.activeInvoice  = invoiceNo;
            this.activeCustomer = customerName;
            this.newNoteText    = '';
            this.notesList      = [];
            this.notesModal     = true;
            this.fetchNotes();
        },

        insertQuickNote(text) {
            if (this.newNoteText.trim() === '') {
                this.newNoteText = text;
            } else {
                this.newNoteText += ', ' + text;
            }
        },

        async fetchNotes() {
            this.notesLoading = true;
            try {
                const res = await fetch(`/admin/all-sales/${this.activeSaleId}/notes`);
                const data = await res.json();
                if (data.success) {
                    this.notesList = data.notes;
                }
            } catch (e) {
                console.error('Failed to load notes', e);
            }
            this.notesLoading = false;
        },

        async submitNote() {
            if (!this.newNoteText.trim()) return;
            this.submittingNote = true;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name=csrf-token]')?.content;
                const res = await fetch(`/admin/all-sales/${this.activeSaleId}/notes`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ note: this.newNoteText }),
                });

                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    this.notesList.unshift(data.note);
                    this.newNoteText = '';
                    showToast(data.message || 'নোট সংরক্ষিত হয়েছে', 'success');

                    // Update badge on row
                    const badge = document.getElementById('note-badge-' + this.activeSaleId);
                    if (badge) {
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    }
                } else {
                    showToast(data.message || data.error || 'নোট সংরক্ষণ করা যায়নি।', 'error');
                }
            } catch (e) {
                showToast('সার্ভার এরর!', 'error');
            }

            this.submittingNote = false;
        }
    };
}
</script>
@endpush