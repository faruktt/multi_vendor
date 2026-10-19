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
    $pageCustomerPhones = $sales->map(function($s) {
        $p = $s->customer?->phone ?? $s->reseller?->phone ?? $s->supplier?->phone ?? '';
        $clean = preg_replace('/[^\d]/', '', (string)$p);
        if (str_starts_with($clean, '880')) $clean = substr($clean, 2);
        if (strlen($clean) > 11) $clean = substr($clean, -11);
        return strlen($clean) >= 10 ? $clean : null;
    })->filter()->unique()->values()->all();
@endphp

@section('content')
<div x-data="salesAdminPage(@js($pageCustomerPhones))">

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
    @php
        $globalDelivered = \App\Models\Sale::whereIn('order_status', ['delivered', 'completed'])->count();
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Delivered (Paid)</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($globalDelivered) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">auto-paid upon delivery</p>
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
        <i class="fas fa-boxes-stacked text-[11px]"></i> All Sales
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('source') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $channelStats['total'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'website'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'website' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-globe text-[11px] text-blue-500"></i> Website Sales
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'website' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700' }}">{{ $channelStats['website'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'pos'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'pos' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-cash-register text-[11px] text-emerald-500"></i> POS Sales
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'pos' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">{{ $channelStats['pos'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'reseller'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'reseller' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-handshake text-[11px] text-purple-500"></i> Reseller Sales
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'reseller' ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-700' }}">{{ $channelStats['reseller'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.all-sales', array_merge(request()->except('source', 'page'), ['source' => 'supplier'])) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('source') === 'supplier' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
        <i class="fas fa-store text-[11px] text-amber-500"></i> Supplier Sales
        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'supplier' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700' }}">{{ $channelStats['supplier'] ?? 0 }}</span>
    </a>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, customer, phone, reseller, supplier..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-52">
        <select name="source" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Sources</option>
            <option value="website"  {{ request('source')=='website'  ? 'selected':'' }}>🌐 Website</option>
            <option value="pos"      {{ request('source')=='pos'      ? 'selected':'' }}>🧾 POS</option>
            <option value="reseller" {{ request('source')=='reseller' ? 'selected':'' }}>🤝 Reseller</option>
            <option value="supplier" {{ request('source')=='supplier' ? 'selected':'' }}>🏪 Supplier</option>
        </select>
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <select name="order_status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            @foreach($orderStatuses as $os)
            <option value="{{ $os->key }}" {{ (request('order_status') == $os->key || request('status') == $os->key) ? 'selected' : '' }}>
                {{ $os->label }}
            </option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-sales') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <button type="button"
                @click="syncCourierStatuses()"
                :disabled="syncingCourier"
                title="কুরিয়ারে পাঠানো অর্ডারগুলোর লাইভ স্ট্যাটাস চেক ও অটো-ক্যানসেল সিঙ্ক করুন"
                class="inline-flex items-center gap-1.5 border border-emerald-300 text-emerald-700 bg-emerald-50/80 hover:bg-emerald-100 px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-2xs cursor-pointer">
            <i class="fas fa-arrows-rotate text-emerald-600 text-xs" :class="{ 'fa-spin': syncingCourier }"></i>
            <span x-text="syncingCourier ? 'সিঙ্ক হচ্ছে...' : 'কুরিয়ার স্ট্যাটাস সিঙ্ক'"></span>
        </button>
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
                    <th class="px-1.5 py-2 text-center w-7">
                        <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected()" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer" title="Select All on page">
                    </th>
                    <th class="px-1 py-2 text-center text-[10px] font-semibold text-slate-400 uppercase w-6">#</th>
                    <th class="px-2 py-2 text-center text-[10px] font-semibold text-slate-400 uppercase">Courier</th>
                    <th class="px-2 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase">Invoice</th>
                    <th class="px-2 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase">Source / Type</th>
                    <th class="px-2 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase hidden md:table-cell">Branch</th>
                    <th class="px-2 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase">Customer</th>
                    <th class="px-2 py-2 text-left text-[10px] font-semibold text-slate-400 uppercase">Products</th>
                    <th class="px-2 py-2 text-right text-[10px] font-semibold text-slate-400 uppercase">Total</th>
                    <th class="px-1.5 py-2 text-center text-[10px] font-semibold text-slate-400 uppercase">Payment</th>
                    <th class="px-2 py-2 text-center text-[10px] font-semibold text-slate-400 uppercase">Order Status</th>
                    <th class="px-2 py-2 text-center text-[10px] font-semibold text-slate-400 uppercase">Actions</th>
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
                <tr x-data="{
                    orderStatus: '{{ $sale->order_status }}',
                    isPaid: {{ (in_array(strtolower((string)$sale->order_status), ['delivered', 'completed', 'complete']) || $sale->payment_status === 'paid') ? 'true' : 'false' }},
                }"
                class="hover:bg-slate-50/60 transition-colors" :class="selectedIds.includes({{ $sale->id }}) ? 'bg-blue-50/40' : ''">
                    <td class="px-1.5 py-1.5 text-center" @click.stop>
                        <input type="checkbox" :value="{{ $sale->id }}" x-model.number="selectedIds" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer">
                    </td>
                    <td class="px-1 py-1.5 text-center text-slate-400 text-[10.5px]">{{ $sales->firstItem() + $loop->index }}</td>

                    {{-- ── Courier Column (In place of Date at front) ── --}}
                    <td class="px-2 py-1.5 text-center" id="courier-cell-{{ $sale->id }}">
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
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-{{ $cColor }}-50 text-{{ $cColor }}-700 border border-{{ $cColor }}-200 shadow-2xs">
                                    <i class="{{ $cIcon }} text-[8.5px]"></i>
                                    {{ $sale->courier_display_name }}
                                </span>
                                <div class="flex items-center gap-1 text-[10.5px] font-mono text-slate-700">
                                    <span title="Tracking: {{ $sale->courier_tracking_code }}">{{ Str::limit($sale->courier_tracking_code, 12) }}</span>
                                    <button type="button" @click="copyText('{{ $sale->courier_tracking_code }}')" title="Copy Tracking Code"
                                            class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                                        <i class="fas fa-copy text-[9.5px]"></i>
                                    </button>
                                </div>
                                @if($sale->courier_status)
                                <span class="text-[9px] px-1 py-0.2 rounded font-medium capitalize {{ in_array(strtolower($sale->courier_status), ['cancelled', 'return', 'returned', 'failed', 'delivery_failed']) ? 'bg-rose-100 text-rose-700 font-bold' : 'bg-slate-100 text-slate-600' }}" id="cstatus-{{ $sale->id }}">
                                    {{ str_replace('_', ' ', $sale->courier_status) }}
                                </span>
                                @else
                                <span class="text-[9px] px-1 py-0.2 rounded bg-slate-100 text-slate-400 font-medium capitalize" id="cstatus-{{ $sale->id }}">
                                    pending
                                </span>
                                @endif
                                <button type="button" 
                                        @click="checkCourierStatus({{ $sale->id }})" 
                                        :disabled="checkingCourier === {{ $sale->id }}"
                                        title="কুরিয়ার লাইভ স্ট্যাটাস চেক করুন (কুরিয়ার বাতিল করলে ওয়েবসাইট অটো Cancelled হবে)"
                                        class="inline-flex items-center gap-1 text-[8.5px] text-blue-600 hover:text-blue-800 font-bold px-1.5 py-0.5 rounded bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-all cursor-pointer mt-0.5">
                                    <i class="fas fa-arrows-rotate text-[7.5px]" :class="{ 'fa-spin': checkingCourier === {{ $sale->id }} }"></i>
                                    <span>Sync</span>
                                </button>
                                @if($sale->courier_sent_at)
                                <span class="text-[8.5px] text-slate-400 font-mono" title="Sent: {{ $sale->courier_sent_at->format('d M Y, h:i A') }}">
                                    {{ $sale->courier_sent_at->format('d M, h:i A') }}
                                </span>
                                @endif
                            </div>
                        @else
                            @php
                                $rName = $sale->customer?->name ?? ($sale->reseller ? $sale->reseller->name : ($sale->supplier ? $sale->supplier->name : 'Walk-in Customer'));
                                $rPhone = $sale->customer?->phone ?? $sale->reseller?->phone ?? $sale->supplier?->phone ?? '';
                                $rAddr = $sale->customer?->address ?: trim(($sale->thana ? $sale->thana . ', ' : '') . ($sale->district ?? ''));

                                // Product Price + Shipping Charge auto calculation for COD
                                $prodPrice = max(0, (float)($sale->subtotal ?? 0) - (float)($sale->discount ?? 0));
                                if ($prodPrice <= 0 && (float)$sale->total > 0) {
                                    $prodPrice = max(0, (float)$sale->total - (float)($sale->delivery_charge ?? 0));
                                }
                                $shipCharge = (float)($sale->delivery_charge ?? 0);
                                $calculatedCod = round($prodPrice + $shipCharge, 2);
                            @endphp
                            @if(($activeCouriers ?? collect())->isEmpty())
                                <span class="text-[10px] text-slate-300" title="No active courier. Configure in Admin > Courier Settings">—</span>
                            @elseif(($activeCouriers ?? collect())->count() === 1)
                                @php $singleCourier = ($activeCouriers ?? collect())->first(); @endphp
                                <button type="button"
                                        @click="openCourierModal({
                                            saleId: {{ $sale->id }},
                                            invoice: '{{ $sale->invoice_no }}',
                                            courierCode: '{{ $singleCourier->code }}',
                                            courierName: '{{ $singleCourier->name }}',
                                            recipientName: @js($rName),
                                            recipientPhone: @js($rPhone),
                                            recipientAddress: @js($rAddr),
                                            district: @js($sale->district ?? ''),
                                            thana: @js($sale->thana ?? ''),
                                            productPrice: {{ $prodPrice }},
                                            shippingCharge: {{ $shipCharge }},
                                            codAmount: {{ $calculatedCod }},
                                            note: @js($sale->note ?? '')
                                        })"
                                        class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-all hover:scale-105 shadow-2xs cursor-pointer whitespace-nowrap">
                                    <i class="fas fa-paper-plane text-[8.5px]"></i>
                                    <span>Send to {{ $singleCourier->name }}</span>
                                </button>
                            @else
                                <button type="button"
                                        @click="openCourierModal({
                                            saleId: {{ $sale->id }},
                                            invoice: '{{ $sale->invoice_no }}',
                                            courierCode: '{{ ($activeCouriers ?? collect())->first()->code }}',
                                            courierName: '{{ ($activeCouriers ?? collect())->first()->name }}',
                                            recipientName: @js($rName),
                                            recipientPhone: @js($rPhone),
                                            recipientAddress: @js($rAddr),
                                            district: @js($sale->district ?? ''),
                                            thana: @js($sale->thana ?? ''),
                                            productPrice: {{ $prodPrice }},
                                            shippingCharge: {{ $shipCharge }},
                                            codAmount: {{ $calculatedCod }},
                                            note: @js($sale->note ?? '')
                                        })"
                                        class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-all hover:scale-105 shadow-2xs cursor-pointer whitespace-nowrap">
                                    <i class="fas fa-truck-fast text-[8.5px]"></i>
                                    <span>Send to Courier</span>
                                </button>
                            @endif
                        @endif
                    </td>

                    {{-- ── Invoice Column (With Date & Time underneath) ── --}}
                    <td class="px-2 py-1.5">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                               class="w-5 h-5 rounded-md bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white border border-blue-200/80 flex items-center justify-center flex-shrink-0 transition-all hover:scale-105 shadow-2xs"
                               title="View Sale">
                                <i class="fas fa-eye text-[8.5px]"></i>
                            </a>
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                                class="font-bold text-slate-800 hover:text-blue-600 text-[12px] font-mono tracking-tight transition-colors">
                                {{ $sale->invoice_no }}
                            </a>
                        </div>

                        {{-- Date & Time under Invoice --}}
                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 flex items-center gap-1 flex-wrap">
                            <span class="inline-flex items-center gap-0.5 text-slate-500">
                                <i class="far fa-calendar-alt text-[8.5px] text-slate-400"></i>
                                <span>{{ $sale->created_at->format('d M Y') }}</span>
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="text-slate-400 text-[9.5px] font-mono">{{ $sale->created_at->format('h:i A') }}</span>
                        </div>
                    </td>

                    {{-- ── Sale Type / Source (Channel & Partner) ── --}}
                    <td class="px-2 py-1.5">
                        @if($isSupplier)
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200/80 shadow-2xs">
                                    <i class="fas fa-store text-[8px] text-amber-500"></i> Supplier
                                </span>
                                @if($supplierObj)
                                    <p class="text-[10.5px] font-semibold text-slate-800 truncate max-w-[120px] leading-tight" title="{{ $supplierObj->display_name }}">
                                        {{ $supplierObj->display_name }}
                                    </p>
                                @endif
                            </div>
                        @elseif($isReseller)
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200/80 shadow-2xs">
                                    <i class="fas fa-handshake text-[8px] text-purple-500"></i> Reseller
                                </span>
                                @if($sale->reseller)
                                    <p class="text-[10.5px] font-semibold text-slate-800 truncate max-w-[120px] leading-tight" title="{{ $sale->reseller->name }}">
                                        {{ $sale->reseller->name }}
                                    </p>
                                    @if($sale->reseller_profit > 0)
                                        <p class="text-[8.5px] text-emerald-600 font-bold leading-none">
                                            Profit: ৳{{ number_format($sale->reseller_profit, 2) }}
                                        </p>
                                    @endif
                                @endif
                            </div>
                        @elseif($isWeb)
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200/80 shadow-2xs">
                                    <i class="fas fa-globe text-[8px] text-blue-500"></i> Website
                                </span>
                                <p class="text-[9.5px] text-slate-400 font-medium">Storefront</p>
                            </div>
                        @else
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80 shadow-2xs">
                                    <i class="fas fa-cash-register text-[8px] text-emerald-500"></i> POS
                                </span>
                                <p class="text-[9.5px] text-slate-400 font-medium">Counter</p>
                            </div>
                        @endif
                    </td>

                    <td class="px-2 py-1.5 hidden md:table-cell">
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 text-[9.5px] font-semibold px-2 py-0.5 rounded-md border border-slate-200/80 whitespace-nowrap">
                            <i class="fas fa-building text-[8px] text-slate-400"></i>
                            {{ $sale->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-1.5">
                        @php
                            $cPhone = $sale->customer?->phone ?? $sale->reseller?->phone ?? $sale->supplier?->phone;
                            $cName  = $sale->customer?->name ?? ($sale->reseller ? 'Reseller: '.$sale->reseller->name : ($sale->supplier ? 'Supplier: '.$sale->supplier->name : 'Walk-in'));
                            $cWa    = $sale->customer?->whatsapp_number ?? ($cPhone ? preg_replace('/[^\d]/', '', (string)$cPhone) : null);
                            $cleanPhone = '';
                            if ($cPhone) {
                                $cleanPhone = preg_replace('/[^\d]/', '', (string)$cPhone);
                                if (str_starts_with($cleanPhone, '880')) $cleanPhone = substr($cleanPhone, 2);
                                if (strlen($cleanPhone) > 11) $cleanPhone = substr($cleanPhone, -11);
                            }
                        @endphp
                        <p class="text-[12px] text-slate-800 font-semibold leading-tight truncate max-w-[150px]" title="{{ $cName }}">{{ $cName }}</p>
                        @if($cPhone)
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[11px] font-mono text-slate-700 font-medium">{{ $cPhone }}</span>
                            <button type="button"
                                    @click.stop="openEpbxCallModal({ id: {{ $sale->id }}, invoice: '{{ $sale->invoice_no }}', customer: @js($cName), phone: '{{ $cPhone }}', amount: '{{ round($sale->total ?? 0) }}' })"
                                    title="Direct Call (Cloud PBX / MicroSIP)"
                                    class="w-4.5 h-4.5 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200/80 flex items-center justify-center flex-shrink-0 transition-all hover:scale-110 shadow-2xs cursor-pointer">
                                <i class="fas fa-phone text-[8.5px] pointer-events-none"></i>
                            </button>
                            @if($cWa)
                            <a href="https://wa.me/{{ $cWa }}" target="_blank" rel="noopener" title="Chat on WhatsApp"
                               class="w-4.5 h-4.5 rounded bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fab fa-whatsapp text-[10px]"></i>
                            </a>
                            @endif
                        </div>

                        {{-- Repeat Order indicator if multiple orders exist for this phone number --}}
                        @php
                            $phoneOrderCount = $repeatOrderCounts[$cleanPhone] ?? 0;
                        @endphp
                        @if($phoneOrderCount > 1)
                        <div class="mt-1">
                            <a href="{{ route('admin.all-sales') }}?search={{ $cleanPhone }}"
                               title="Repeat Customer: {{ $phoneOrderCount }} total orders found for this phone number. Click to filter all orders."
                               class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs hover:bg-amber-200 transition-colors">
                                <i class="fas fa-arrows-rotate text-[8px] text-amber-600"></i>
                                <span>Repeat Order ({{ $phoneOrderCount }})</span>
                            </a>
                        </div>
                        @endif

                        {{-- BD Courier Fraud Check Percentage directly under phone number --}}
                        <div class="mt-1">
                            <template x-if="fraudLoading && !fraudChecks['{{ $cleanPhone }}']">
                                <span class="inline-flex items-center gap-1 text-[9px] text-slate-400 bg-slate-50 border border-slate-200/60 rounded px-1.5 py-0.2 animate-pulse">
                                    <i class="fas fa-circle-notch fa-spin text-[8px] text-indigo-400"></i>
                                    <span>Fraud checking...</span>
                                </span>
                            </template>
                            <template x-if="fraudChecks['{{ $cleanPhone }}']">
                                <button type="button"
                                        @click.stop="openBdCheck('{{ $cPhone }}', @js($cName))"
                                        :title="'Cancelled: ' + (fraudChecks['{{ $cleanPhone }}'].cancelled_parcel ?? 0) + ' (' + (fraudChecks['{{ $cleanPhone }}'].cancel_ratio ?? 0) + '%) | Delivered: ' + (fraudChecks['{{ $cleanPhone }}'].success_parcel ?? 0) + ' (' + (fraudChecks['{{ $cleanPhone }}'].delivery_ratio ?? 0) + '%) - Click for full report'"
                                        class="inline-flex items-center rounded-full overflow-hidden border border-slate-300/80 shadow-2xs hover:scale-105 transition-all cursor-pointer text-[10px] font-bold leading-tight">
                                    {{-- Red segment: Cancel orders & % --}}
                                    <span class="inline-flex items-center gap-0.5 bg-rose-600 text-white px-2 py-0.5">
                                        <span x-text="fraudChecks['{{ $cleanPhone }}'].cancelled_parcel ?? 0"></span>
                                        <span class="text-[8.5px] opacity-90 font-medium" x-text="'(' + (fraudChecks['{{ $cleanPhone }}'].cancel_ratio ?? 0) + '%)'"></span>
                                    </span>
                                    {{-- Green segment: Delivered orders & % --}}
                                    <span class="inline-flex items-center gap-0.5 bg-emerald-600 text-white px-2 py-0.5">
                                        <span x-text="fraudChecks['{{ $cleanPhone }}'].success_parcel ?? 0"></span>
                                        <span class="text-[8.5px] opacity-90 font-medium" x-text="'(' + (fraudChecks['{{ $cleanPhone }}'].delivery_ratio ?? 0) + '%)'"></span>
                                    </span>
                                </button>
                            </template>
                            <template x-if="!fraudLoading && !fraudChecks['{{ $cleanPhone }}']">
                                <button type="button"
                                        @click.stop="openBdCheck('{{ $cPhone }}', @js($cName))"
                                        title="Click to check BD Courier"
                                        class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-medium bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200 transition-colors cursor-pointer">
                                    <i class="fas fa-shield-halved text-[8px]"></i>
                                    <span>Check Fraud</span>
                                </button>
                            </template>
                        </div>
                        @endif
                    </td>

                    {{-- Products --}}
                    <td class="px-2 py-1.5">
                        <div class="space-y-1 max-w-[190px]">
                            @foreach($sale->saleItems as $item)
                            @php
                                $img = $item->product?->first_image_url;
                            @endphp
                            <div class="flex items-center gap-1.5">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $item->product?->name }}"
                                         class="w-6 h-6 rounded object-cover border border-slate-200 flex-shrink-0 shadow-2xs">
                                @else
                                    <div class="w-6 h-6 rounded bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0 text-slate-400">
                                        <i class="fas fa-box text-[9px] text-slate-300"></i>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-800 text-[10.5px] leading-tight truncate" title="{{ $item->product?->name }}">
                                        {{ $item->product?->name ?? 'Product' }}
                                    </p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        @if($item->variant_name)
                                            <span class="text-[8px] font-medium text-purple-700 bg-purple-50 border border-purple-100 px-1 py-0.1 rounded truncate max-w-[80px]" title="{{ $item->variant_name }}">
                                                {{ $item->variant_name }}
                                            </span>
                                        @endif
                                        <span class="text-[9px] font-bold text-slate-500 bg-slate-100 px-1 py-0.1 rounded">
                                            ×{{ $item->quantity }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </td>

                    <td class="px-2 py-1.5 text-right font-bold text-slate-800 text-[11.5px] whitespace-nowrap">{{ $currency }}{{ number_format($sale->total,0) }}</td>
                    <td class="px-1.5 py-1.5 text-center whitespace-nowrap">
                        <span x-show="orderStatus === 'delivered' || orderStatus === 'completed' || isPaid"
                              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                            <i class="fas fa-check-circle text-[9px]"></i> Paid
                        </span>
                        <span x-show="orderStatus !== 'delivered' && orderStatus !== 'completed' && !isPaid"
                              class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">
                            Unpaid
                        </span>
                    </td>
                    {{-- Order Status Dropdown --}}
                    <td class="px-2 py-1.5 text-center">
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
                                    orderStatus = s;
                                    if (s === 'delivered' || s === 'completed') {
                                        isPaid = true;
                                    }
                                    showToast('Order status updated!', 'success');
                                } else {
                                    showToast('Failed to update status.', 'error');
                                }
                                openStatusRow = null;
                            }
                        }"
                             @order-status-updated-{{ $sale->id }}.window="status = $event.detail; orderStatus = $event.detail; if ($event.detail === 'delivered' || $event.detail === 'completed') isPaid = true;"
                             @click.outside="if (openStatusRow === {{ $sale->id }}) openStatusRow = null"
                             class="relative inline-block text-left">
                            <button type="button" @click.stop="openStatusRow = (openStatusRow === {{ $sale->id }} ? null : {{ $sale->id }})"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold transition-all cursor-pointer hover:opacity-80 shadow-2xs border"
                                    :class="statusColors[status] || 'bg-slate-100 text-slate-600 border-slate-200'">
                                <span x-text="statusLabels[status] || status"></span>
                                <i class="fas fa-chevron-down text-[7.5px]"></i>
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

                    <td class="px-2 py-1.5">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Print --}}
                            <a href="{{ route('branch.sales.show', [$sale->vendor_id, $sale]) }}?auto_print=1" target="_blank"
                               class="w-6 h-6 rounded-md bg-purple-100 hover:bg-purple-200 text-purple-700 flex items-center justify-center transition-colors" title="Print">
                                <i class="fas fa-print text-[10px]"></i>
                            </a>
                            {{-- Edit --}}
                            <a href="{{ route('branch.sales.edit', [$sale->vendor_id, $sale]) }}"
                                class="w-6 h-6 rounded-md bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </a>
                            {{-- Note --}}
                            <button type="button"
                                    @click.stop="openNotes({{ $sale->id }}, '{{ $sale->invoice_no }}', @js($sale->customer?->name ?? 'Walk-in'))"
                                    class="relative w-6 h-6 rounded-md border flex items-center justify-center transition-colors cursor-pointer {{ $sale->notes_count > 0 ? 'bg-amber-100 border-amber-300 text-amber-700 hover:bg-amber-200 shadow-2xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 border-slate-200' }}" title="Order Notes">
                                <i class="fas fa-sticky-note text-[10px] pointer-events-none"></i>
                                <span id="note-badge-{{ $sale->id }}"
                                      class="absolute -top-1 -right-1 min-w-[13px] h-[13px] px-0.5 rounded-full bg-amber-500 text-white text-[8px] font-bold flex items-center justify-center leading-none {{ $sale->notes_count > 0 ? '' : 'hidden' }}">
                                    {{ $sale->notes_count ?? 0 }}
                                </span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 py-16 text-center text-slate-400">
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
                <p class="text-sm font-medium text-slate-700">Loading courier intelligence...</p>
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
                            <h4 class="font-bold text-red-800 text-sm">Failed to load courier data</h4>
                            <p class="text-red-700 text-xs mt-1 leading-relaxed" x-text="bdError"></p>
                            <button type="button" @click="checkBdCourier()"
                                    class="mt-3 text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition-colors">
                                <i class="fas fa-rotate-right text-[10px]"></i> Retry
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
                            <p class="text-[11px] font-bold text-blue-600/90 mt-1.5">Total Parcels</p>
                            <p class="text-[9px] text-slate-400">All Couriers</p>
                        </div>

                        {{-- Delivered --}}
                        <div class="bg-emerald-50/80 border border-emerald-100 rounded-2xl p-3.5 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-1.5">
                                <i class="fas fa-circle-check text-xs"></i>
                            </div>
                            <p class="text-2xl font-black text-emerald-700 leading-none" x-text="bdRiskInfo.delivered"></p>
                            <p class="text-[11px] font-bold text-emerald-600/90 mt-1.5">Delivered</p>
                            <p class="text-[9px] text-slate-400">Successful</p>
                        </div>

                        {{-- Cancelled --}}
                        <div class="bg-rose-50/80 border border-rose-100 rounded-2xl p-3.5 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center mb-1.5">
                                <i class="fas fa-circle-xmark text-xs"></i>
                            </div>
                            <p class="text-2xl font-black text-rose-700 leading-none" x-text="bdRiskInfo.cancelled"></p>
                            <p class="text-[11px] font-bold text-rose-600/90 mt-1.5">Cancelled</p>
                            <p class="text-[9px] text-slate-400">Returned</p>
                        </div>
                    </div>

                    {{-- Courier-wise Breakdown --}}
                    <div x-show="bdCourierRows.length > 0" class="border border-slate-100 rounded-2xl p-4 bg-white">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">Courier Breakdown</p>
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
                            <span>Reports & Flags</span>
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
                <p class="text-xs">Loading notes...</p>
            </div>

            {{-- Empty State --}}
            <template x-if="!notesLoading && notesList.length === 0">
                <div class="py-10 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-2.5">
                        <i class="fas fa-note-sticky text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-600">No notes added yet</p>
                    <p class="text-xs text-slate-400 mt-1">Add any order instructions or updates below.</p>
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
                <span class="text-slate-400 text-[10px] font-semibold whitespace-nowrap mr-0.5">Quick Notes:</span>
                <button type="button" @click="insertQuickNote('Customer will not receive product')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    ❌ Refused delivery
                </button>
                <button type="button" @click="insertQuickNote('Requested call back later')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    📞 Call later
                </button>
                <button type="button" @click="insertQuickNote('Requested address change')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    📍 Address update
                </button>
                <button type="button" @click="insertQuickNote('Delivery confirmed by customer')"
                        class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-800 transition-colors whitespace-nowrap">
                    ✅ Confirmed delivery
                </button>
            </div>

            <form @submit.prevent="submitNote" class="space-y-2.5">
                <textarea x-model="newNoteText" rows="2" required placeholder="Write internal note for this order..."
                          class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent resize-none leading-relaxed"></textarea>
                
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">
                        <i class="fas fa-user-pen mr-1"></i>Saved under your name
                    </span>
                    <button type="submit" :disabled="submittingNote || !newNoteText.trim()"
                            class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors shadow-sm shadow-amber-200">
                        <i class="fas fa-paper-plane text-[10px]" x-show="!submittingNote"></i>
                        <i class="fas fa-spinner fa-spin text-[10px]" x-show="submittingNote"></i>
                        <span x-text="submittingNote ? 'Saving...' : 'Add Note'"></span>
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
                    <p class="text-xs text-slate-500 mt-0.5">Create parcel and confirm courier booking</p>
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
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider">COD Collection Amount (৳) *</label>
                        <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded" x-show="courierForm.productPrice || courierForm.shippingCharge">
                            প্রোডাক্ট ৳<span x-text="courierForm.productPrice"></span> + শিপিং ৳<span x-text="courierForm.shippingCharge"></span>
                        </span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">৳</span>
                        <input type="number" step="any" min="0" x-model="courierForm.codAmount" required
                               class="w-full text-xs rounded-xl border border-slate-200 pl-7 pr-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">প্রোডাক্ট প্রাইজ ও শিপিং চার্জ যোগ হয়ে অটো ক্যালকুলেট হয়েছে</p>
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
                       placeholder="e.g. Handle with care / Call before delivery">
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
function salesAdminPage(initialPhones = []) {
    return {
        openEpbxCallModal(data) {
            if (typeof window.openEpbxCallModal === 'function') {
                window.openEpbxCallModal(data);
            } else {
                window.dispatchEvent(new CustomEvent('open-epbx-modal', { detail: data }));
            }
        },

        pageSaleIds: @json($sales->pluck('id')->values()),
        pagePhones: initialPhones || [],
        fraudChecks: {},
        fraudLoading: false,

        init() {
            if (this.pagePhones && this.pagePhones.length > 0) {
                this.loadFraudChecks();
            }
        },

        async loadFraudChecks() {
            if (!this.pagePhones || this.pagePhones.length === 0) return;
            this.fraudLoading = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name=csrf-token]')?.content;
                const res = await fetch('{{ route('admin.bd-courier-check.batch') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ phones: this.pagePhones })
                });
                const data = await res.json();
                if (res.ok && data.success && data.results) {
                    this.fraudChecks = data.results;
                }
            } catch (e) {
                console.error('Failed to load fraud checks:', e);
            } finally {
                this.fraudLoading = false;
            }
        },

        fraudBadgeClass(info) {
            if (!info) return 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200';
            switch (info.color) {
                case 'emerald':
                    return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100';
                case 'sky':
                    return 'bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100';
                case 'amber':
                    return 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100';
                case 'red':
                    return 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100';
                default:
                    return 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200';
            }
        },

        fraudBadgeIcon(info) {
            if (!info) return 'fas fa-shield-halved text-slate-400';
            switch (info.color) {
                case 'emerald':
                    return 'fas fa-shield-check text-emerald-600';
                case 'sky':
                    return 'fas fa-shield-halved text-sky-600';
                case 'amber':
                    return 'fas fa-triangle-exclamation text-amber-500';
                case 'red':
                    return 'fas fa-circle-exclamation text-red-500';
                default:
                    return 'fas fa-shield-halved text-slate-400';
            }
        },

        fraudBadgeText(info) {
            if (!info) return 'No Data';
            return info.label || (info.success_ratio + '%');
        },

        fraudCheckTitle(info) {
            if (!info) return 'Click to check courier delivery history';
            return `Success Rate: ${info.success_ratio}% | Total Parcels: ${info.total_parcel} | Delivered: ${info.success_parcel} | Cancelled: ${info.cancelled_parcel} (Click for full report)`;
        },

        selectedIds: [],
        openStatusRow: null,
        statusLabels: @json($orderStatuses->pluck('label', 'key')),
        statusColors: @json($orderStatuses->mapWithKeys(fn($s) => [$s->key => "bg-{$s->color}-100 text-{$s->color}-700 border border-{$s->color}-200"])),

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
            productPrice: 0,
            shippingCharge: 0,
            codAmount: 0,
            note: ''
        },

        openCourierModal(data) {
            this.courierError = null;
            const prodPrice = Number(data.productPrice || 0);
            const shipCharge = Number(data.shippingCharge || 0);
            const calculatedCod = Number(data.codAmount !== undefined ? data.codAmount : (prodPrice + shipCharge));

            this.courierForm = {
                saleId: data.saleId,
                invoice: data.invoice,
                courierCode: data.courierCode || '{{ ($activeCouriers ?? collect())->first()?->code ?? "" }}',
                courierName: data.courierName || '',
                recipientName: data.recipientName || '',
                recipientPhone: data.recipientPhone || '',
                recipientAddress: data.recipientAddress || [data.thana, data.district].filter(Boolean).join(', '),
                deliveryArea: data.district || data.thana || 'Dhaka',
                productPrice: prodPrice,
                shippingCharge: shipCharge,
                codAmount: calculatedCod,
                note: data.note || ''
            };
            this.courierModal = true;
        },

        async submitCourierDispatch() {
            this.courierSending = true;
            this.courierError = null;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name=csrf-token]')?.content;
                const res = await fetch(`/admin/courier/orders/${this.courierForm.saleId}/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        courier_code: this.courierForm.courierCode,
                        recipient_name: this.courierForm.recipientName,
                        recipient_phone: this.courierForm.recipientPhone,
                        recipient_address: this.courierForm.recipientAddress,
                        delivery_area: this.courierForm.deliveryArea,
                        cod_amount: this.courierForm.codAmount,
                        note: this.courierForm.note
                    })
                });

                let data;
                try {
                    data = await res.json();
                } catch (parseErr) {
                    throw new Error(`Server response error (${res.status} ${res.statusText})`);
                }

                if (res.ok && data.success) {
                    this.courierModal = false;
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    } else {
                        alert(data.message);
                    }

                    const cell = document.getElementById('courier-cell-' + this.courierForm.saleId);
                    if (cell) {
                        const cColor = data.courier_code === 'steadfast' ? 'teal' : (data.courier_code === 'pathao' ? 'rose' : (data.courier_code === 'redx' ? 'indigo' : 'blue'));
                        const cIcon = data.courier_code === 'steadfast' ? 'fas fa-shipping-fast' : (data.courier_code === 'pathao' ? 'fas fa-motorcycle' : 'fas fa-truck-fast');
                        cell.innerHTML = `
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-${cColor}-50 text-${cColor}-700 border border-${cColor}-200 shadow-2xs">
                                    <i class="${cIcon} text-[8.5px]"></i>
                                    ${data.courier_name}
                                </span>
                                <div class="flex items-center gap-1 text-[10.5px] font-mono text-slate-700">
                                    <span>${data.tracking_code}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('${data.tracking_code}'); if(typeof showToast==='function'){showToast('Tracking copied!', 'info');}else{alert('Tracking copied!');}" title="Copy Tracking Code"
                                            class="text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                                        <i class="fas fa-copy text-[9.5px]"></i>
                                    </button>
                                </div>
                                <span class="text-[9px] px-1 py-0.2 rounded bg-slate-100 text-slate-600 font-medium capitalize" id="cstatus-${this.courierForm.saleId}">
                                    ${(data.status || 'pending').replace(/_/g, ' ')}
                                </span>
                                <button type="button" 
                                        onclick="window.dispatchEvent(new CustomEvent('check-courier', { detail: ${this.courierForm.saleId} }))" 
                                        title="Sync Status"
                                        class="inline-flex items-center gap-1 text-[8.5px] text-blue-600 hover:text-blue-800 font-bold px-1.5 py-0.5 rounded bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-all cursor-pointer mt-0.5">
                                    <i class="fas fa-arrows-rotate text-[7.5px]"></i>
                                    <span>Sync</span>
                                </button>
                            </div>
                        `;
                    }
                } else {
                    this.courierError = data.message || 'Failed to dispatch order to courier.';
                }
            } catch (err) {
                this.courierError = err.message || 'Server error: Connection failed.';
            } finally {
                this.courierSending = false;
            }
        },

        checkingCourier: null,
        syncingCourier: false,

        async checkCourierStatus(saleId) {
            this.checkingCourier = saleId;
            try {
                const res = await fetch(`/admin/all-sales/${saleId}/courier-status`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const statusEl = document.getElementById('cstatus-' + saleId);
                    if (statusEl && data.courier_status) {
                        statusEl.textContent = data.courier_status.replace(/_/g, ' ');
                        if (data.is_cancelled) {
                            statusEl.className = 'text-[9px] px-1 py-0.2 rounded bg-rose-100 text-rose-700 font-bold capitalize';
                        } else if (data.is_delivered) {
                            statusEl.className = 'text-[9px] px-1 py-0.2 rounded bg-emerald-100 text-emerald-700 font-bold capitalize';
                        }
                    }

                    if (data.order_status) {
                        window.dispatchEvent(new CustomEvent('order-status-updated-' + saleId, { detail: data.order_status }));
                    }

                    if (data.is_cancelled) {
                        if (typeof showToast === 'function') {
                            showToast(`অর্ডারটি কুরিয়ার থেকে বাতিল হয়েছে! ওয়েবসাইট অর্ডার স্ট্যাটাস অটোমেটিক 'Cancelled' করা হয়েছে।`, 'warning');
                        } else {
                            alert(`অর্ডারটি কুরিয়ার থেকে বাতিল হয়েছে! ওয়েবসাইট অর্ডার স্ট্যাটাস অটোমেটিক 'Cancelled' করা হয়েছে।`);
                        }
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(data.message || `কুরিয়ার স্ট্যাটাস: ${data.courier_status}`, 'info');
                        }
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'কুরিয়ার স্ট্যাটাস চেক ব্যর্থ হয়েছে।', 'error');
                    } else {
                        alert(data.message || 'কুরিয়ার স্ট্যাটাস চেক ব্যর্থ হয়েছে।');
                    }
                }
            } catch (err) {
                if (typeof showToast === 'function') {
                    showToast('কুরিয়ার সার্ভারে সংযোগ সমস্যা হয়েছে।', 'error');
                }
            } finally {
                this.checkingCourier = null;
            }
        },

        async syncCourierStatuses() {
            this.syncingCourier = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name=csrf-token]')?.content;
                const res = await fetch('{{ route('admin.all-sales.courier-sync-batch') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ sale_ids: this.pageSaleIds || [] })
                });

                const data = await res.json();
                if (data.success) {
                    if (data.updates && data.updates.length) {
                        data.updates.forEach(u => {
                            const statusEl = document.getElementById('cstatus-' + u.sale_id);
                            if (statusEl && u.courier_status) {
                                statusEl.textContent = u.courier_status.replace(/_/g, ' ');
                                if (u.is_cancelled) {
                                    statusEl.className = 'text-[9px] px-1 py-0.2 rounded bg-rose-100 text-rose-700 font-bold capitalize';
                                }
                            }
                            if (u.order_status) {
                                window.dispatchEvent(new CustomEvent('order-status-updated-' + u.sale_id, { detail: u.order_status }));
                            }
                        });
                    }

                    if (typeof showToast === 'function') {
                        showToast(data.message, data.cancelled > 0 ? 'warning' : 'success');
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert(data.message || 'সিঙ্ক ব্যর্থ হয়েছে।');
                }
            } catch (err) {
                alert('সার্ভার সংযোগ সমস্যা।');
            } finally {
                this.syncingCourier = false;
            }
        },

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

        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                if (typeof showToast === 'function') {
                    showToast('Tracking code copied: ' + text, 'info');
                } else {
                    alert('Tracking code copied: ' + text);
                }
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
            this.bdPhone    = phone || '';
            this.bdName     = name || '';
            this.bdResult   = null;
            this.bdError    = null;
            this.bdModal    = true;
            if (this.bdPhone) {
                this.checkBdCourier();
            } else {
                this.bdError = 'No customer phone number available.';
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
                excellent: { label: 'Safe Customer',     message: 'Excellent parcel delivery success rate.',  ring: '#22c55e', text: 'text-emerald-600', badgeBg: 'bg-emerald-100 text-emerald-700 border border-emerald-200' },
                good:      { label: 'Good Customer',     message: 'Reliable parcel delivery history.',         ring: '#0ea5e9', text: 'text-sky-600',     badgeBg: 'bg-sky-100 text-sky-700 border border-sky-200' },
                average:   { label: 'Moderate Risk',     message: 'Verify order before parcel dispatch.',       ring: '#f59e0b', text: 'text-amber-600',   badgeBg: 'bg-amber-100 text-amber-700 border border-amber-200' },
                risky:     { label: 'High Risk',         message: 'Higher likelihood of return or refusal.',    ring: '#ef4444', text: 'text-red-600',     badgeBg: 'bg-red-100 text-red-700 border border-red-200' },
                none:      { label: 'New Customer',      message: 'No previous courier delivery record found.', ring: '#94a3b8', text: 'text-slate-500',   badgeBg: 'bg-slate-100 text-slate-600 border border-slate-200' },
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
                    if (data.summary && data.phone) {
                        const s = data.summary;
                        const total = Number(s.total_parcel || 0);
                        const ratio = Number(s.success_ratio || 0);
                        const delCount = Number(s.success_parcel || 0);
                        const canCount = Number(s.cancelled_parcel || 0);
                        const delRatio = total > 0 ? Math.round((delCount / total) * 100) : Math.round(ratio);
                        const canRatio = total > 0 ? Math.round((canCount / total) * 100) : (delRatio > 0 ? Math.max(0, 100 - delRatio) : 0);

                        let color = 'slate';
                        let label = '0% New';
                        if (total > 0 || ratio > 0) {
                            if (delRatio >= 80) { color = 'emerald'; label = delRatio + '% Safe'; }
                            else if (delRatio >= 60) { color = 'sky'; label = delRatio + '% Good'; }
                            else if (delRatio >= 40) { color = 'amber'; label = delRatio + '% Medium'; }
                            else { color = 'red'; label = delRatio + '% Risky'; }
                        }
                        this.fraudChecks[data.phone] = {
                            phone: data.phone,
                            total_parcel: total,
                            success_parcel: delCount,
                            cancelled_parcel: canCount,
                            success_ratio: ratio,
                            delivery_ratio: delRatio,
                            cancel_ratio: canRatio,
                            color: color,
                            label: label
                        };
                    }
                } else {
                    this.bdError = data.error || 'Failed to fetch courier details.';
                }
            } catch (e) {
                this.bdError = 'Connection lost to server.';
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
                    showToast(data.message || 'Note saved successfully', 'success');

                    // Update badge on row
                    const badge = document.getElementById('note-badge-' + this.activeSaleId);
                    if (badge) {
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    }
                } else {
                    showToast(data.message || data.error || 'Failed to save note.', 'error');
                }
            } catch (e) {
                showToast('Server error!', 'error');
            }

            this.submittingNote = false;
        }
    };
}
</script>
@endpush