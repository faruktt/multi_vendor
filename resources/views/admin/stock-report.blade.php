@extends('layouts.app')
@section('title','Stock Report')
@section('heading','Stock Report')
@php
    $currency   = $appSettings['currency'] ?? '৳';
    $totalProds = collect($branchSummary)->sum('count');
    $totalValue = collect($branchSummary)->sum('value');
    $totalOut   = collect($branchSummary)->sum('out_stock');
    $totalLow   = collect($branchSummary)->sum('low_stock') - $totalOut;
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')

{{-- ══ KPI CARDS ══ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Products</p>
            <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-boxes-stacked text-blue-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ number_format($totalProds) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">across all branches</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Stock Value</p>
            <div class="w-8 h-8 rounded-xl bg-violet-100 flex items-center justify-center">
                <i class="fas fa-coins text-violet-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $currency }}{{ number_format($totalValue,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">at cost price</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wide">Low Stock</p>
            <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-triangle-exclamation text-amber-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-amber-500">{{ number_format($totalLow) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">1–10 units remaining</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-red-500 uppercase tracking-wide">Out of Stock</p>
            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-ban text-red-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-red-500">{{ number_format($totalOut) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">zero quantity</p>
    </div>
</div>

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.stock-report') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[200px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div>
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[130px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ $s['count'] }}</span> products
                    @if($s['out_stock']>0)&nbsp;·&nbsp;<span class="text-red-500">{{ $s['out_stock'] }} out</span>@endif
                    &nbsp;·&nbsp;<span class="font-bold text-blue-700">{{ $currency }}{{ number_format($s['value'],0) }}</span>
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- ══ TABS ══ --}}
<div class="flex gap-2 mb-4">
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'stock']) }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border-2 transition-all
              {{ $tab === 'stock'
                  ? 'bg-blue-600 border-blue-600 text-white shadow-md shadow-blue-200'
                  : 'bg-white border-slate-200 text-slate-500 hover:border-blue-300 hover:text-blue-600' }}">
        <i class="fas fa-boxes-stacked text-[12px]"></i>
        Stock List
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'movements']) }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold border-2 transition-all
              {{ $tab === 'movements'
                  ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-200'
                  : 'bg-white border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600' }}">
        <i class="fas fa-arrow-right-arrow-left text-[12px]"></i>
        Stock Movements
        <span class="ml-0.5 {{ $tab==='movements' ? 'bg-white/30 text-white' : 'bg-slate-100 text-slate-600' }} text-[10px] font-bold px-1.5 py-0.5 rounded-full">
            {{ $movements->total() }}
        </span>
    </a>
</div>

{{-- ══ TAB: STOCK LIST ══ --}}
@if($tab === 'stock')

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <input type="hidden" name="tab" value="stock">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <select name="filter" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Stock</option>
            <option value="out" {{ request('filter')=='out' ? 'selected':'' }}>Out of Stock</option>
            <option value="low" {{ request('filter')=='low' ? 'selected':'' }}>Low Stock (≤10)</option>
            <option value="ok"  {{ request('filter')=='ok'  ? 'selected':'' }}>In Stock (&gt;10)</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.stock-report') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <span class="ml-auto text-[12px] text-slate-400">{{ $products->total() }} products</span>
    </div>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Category</th>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Purchased</th>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Sold</th>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Stock Qty</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Cost</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Sale Price</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Stock Value</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Edit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($products as $p)
                @php
                    $stockVal = $p->stock_qty * $p->cost_price;
                    $status   = $p->stock_qty == 0 ? 'out' : ($p->stock_qty <= 10 ? 'low' : 'ok');
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors {{ $status==='out' ? 'bg-red-50/30 border-l-2 border-l-red-300' : ($status==='low' ? 'bg-amber-50/30 border-l-2 border-l-amber-300' : '') }}">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $products->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            @if($p->first_image_url)
                                <img src="{{ $p->first_image_url }}" alt="{{ $p->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200/80 flex-shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs flex-shrink-0">
                                    <i class="fas fa-box"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800 text-[12.5px] leading-tight truncate max-w-[170px]" title="{{ $p->name }}">{{ $p->name }}</p>
                                @if($p->sku)
                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $p->sku }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $p->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-[12.5px] hidden md:table-cell">{{ $p->category->name ?? '—' }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center gap-1 font-bold text-[12px] text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded-lg shadow-2xs" title="Total Purchased">
                            <i class="fas fa-truck-ramp-box text-[10px] text-blue-500"></i>
                            {{ number_format($p->total_purchased_qty) }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center gap-1 font-bold text-[12px] text-purple-700 bg-purple-50 border border-purple-200/80 px-2 py-0.5 rounded-lg shadow-2xs" title="Total Sold">
                            <i class="fas fa-cart-shopping text-[10px] text-purple-500"></i>
                            {{ number_format($p->total_sold_qty) }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex items-center gap-1 font-bold text-[13px] {{ $status==='out' ? 'text-red-700 bg-red-50 border-red-200' : ($status==='low' ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200') }} border px-2 py-0.5 rounded-lg shadow-2xs" title="Current Stock">
                            <i class="fas fa-boxes-stacked text-[10px] opacity-70"></i>
                            {{ number_format($p->stock_qty) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-slate-500 hidden lg:table-cell">{{ $currency }}{{ number_format($p->cost_price,0) }}</td>
                    <td class="px-4 py-3 text-right text-slate-700 font-semibold hidden lg:table-cell">{{ $currency }}{{ number_format($p->price,0) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800 hidden lg:table-cell">{{ $currency }}{{ number_format($stockVal,0) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($status==='out')
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">Out</span>
                        @elseif($status==='low')
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-600">Low</span>
                        @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">OK</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('branch.products.edit', [$p->vendor_id, $p]) }}"
                           class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 inline-flex items-center justify-center transition-colors" title="Edit Product">
                            <i class="fas fa-pen text-[11px]"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 py-16 text-center">
                        <i class="fas fa-boxes-stacked text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No products found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $products->links() }}</div>
    @endif
</div>

@endif

{{-- ══ TAB: STOCK MOVEMENTS ══ --}}
@if($tab === 'movements')

<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <input type="hidden" name="tab" value="movements">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="prod" value="{{ request('prod') }}" placeholder="Product name..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <select name="type" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">IN &amp; OUT</option>
            <option value="in"  {{ request('type')=='in'  ? 'selected':'' }}>↑ Stock IN</option>
            <option value="out" {{ request('type')=='out' ? 'selected':'' }}>↓ Stock OUT</option>
        </select>
        <select name="ref_type" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Source</option>
            <option value="purchase"   {{ request('ref_type')=='purchase'   ? 'selected':'' }}>Purchase</option>
            <option value="sale"       {{ request('ref_type')=='sale'       ? 'selected':'' }}>Sale</option>
            <option value="adjustment" {{ request('ref_type')=='adjustment' ? 'selected':'' }}>Adjustment</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.stock-report') }}?tab=movements" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <span class="ml-auto text-[12px] text-slate-400">{{ $movements->total() }} movements</span>
    </div>
</form>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Date</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Type</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Qty</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Source</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($movements as $m)
                <tr class="hover:bg-slate-50/60 transition-colors {{ $m->type==='in' ? 'border-l-2 border-l-emerald-300' : 'border-l-2 border-l-red-300' }}">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $movements->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-[11.5px] text-slate-600">{{ $m->created_at->format('d M Y') }}</p>
                        <p class="text-[10.5px] text-slate-400">{{ $m->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800 text-[12.5px]">{{ $m->product?->name ?? '—' }}</p>
                        @if($m->product?->sku)
                        <p class="text-[11px] text-slate-400 font-mono">{{ $m->product->sku }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $m->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($m->type === 'in')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">↑ IN</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-600">↓ OUT</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-[14px] {{ $m->type==='in' ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $m->type==='in' ? '+' : '-' }}{{ $m->quantity }}
                    </td>
                    <td class="px-4 py-3 text-center hidden md:table-cell">
                        @php
                            $ref = $m->reference_type ?? '';
                            $refStyle = match($ref) {
                                'purchase'   => 'bg-blue-100 text-blue-700',
                                'sale'       => 'bg-purple-100 text-purple-700',
                                'adjustment' => 'bg-slate-100 text-slate-600',
                                default      => 'bg-slate-100 text-slate-400',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $refStyle }}">
                            {{ $ref ? ucfirst($ref) : '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-[12px] hidden lg:table-cell">{{ $m->note ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <i class="fas fa-arrow-right-arrow-left text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No stock movements found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $movements->links() }}</div>
    @endif
</div>

@endif

@endsection
