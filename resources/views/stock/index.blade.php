@extends('layouts.app')
@section('title', 'Stock')
@section('heading', 'Stock Management')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')

{{-- ── Stats ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-boxes-stacked text-blue-600 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium">Total Products</p>
                <p class="text-xl font-bold text-slate-800">{{ number_format($summary['total_products']) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium">Out of Stock</p>
                <p class="text-xl font-bold text-red-500">{{ $summary['out_of_stock'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-triangle-exclamation text-amber-500 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium">Low Stock (≤10)</p>
                <p class="text-xl font-bold text-amber-500">{{ $summary['low_stock'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-coins text-emerald-600 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-slate-400 font-medium">Inventory Value</p>
                <p class="text-xl font-bold text-emerald-600">{{ $currency }}{{ number_format($summary['total_stock_value'], 0) }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Split Layout ─────────────────────────────────────────────── --}}
<div class="flex gap-4 items-start">

    {{-- ════ LEFT: Stock List (col-8) ════ --}}
    <div class="flex-[3] min-w-0">

        {{-- Filter bar --}}
        <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-3 flex flex-wrap gap-2.5 items-center">
            {{-- preserve movement filters --}}
            @foreach(['mtype','ref_type','from','to','mpage'] as $k)
                @if(request($k))<input type="hidden" name="{{ $k }}" value="{{ request($k) }}">@endif
            @endforeach
            <div class="relative flex-1 min-w-[160px]">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search product..."
                       class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>
            <select name="stock_filter"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600">
                <option value="">All Stock</option>
                <option value="out" {{ request('stock_filter')=='out' ? 'selected':'' }}>Out of Stock</option>
                <option value="low" {{ request('stock_filter')=='low' ? 'selected':'' }}>Low Stock (≤10)</option>
                <option value="ok"  {{ request('stock_filter')=='ok'  ? 'selected':'' }}>In Stock (&gt;10)</option>
            </select>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                <i class="fas fa-filter text-xs mr-1"></i> Filter
            </button>
            @if(request('search') || request('stock_filter'))
            <a href="{{ route('branch.stock.index', $branch) }}"
               class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50">Reset</a>
            @endif
            <span class="text-[11.5px] text-slate-400 ml-auto">{{ $products->total() }} products</span>
        </form>

        {{-- Stock table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Category</th>
                        <th class="px-2.5 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Purchased</th>
                        <th class="px-2.5 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Sold</th>
                        <th class="px-2.5 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">In Stock</th>
                        <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden xl:table-cell">Cost</th>
                        <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden xl:table-cell">Price</th>
                        <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($products as $p)
                    @php $st = $p->stock_qty <= 0 ? 'out' : ($p->stock_qty <= 10 ? 'low' : 'ok'); @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-3 py-2.5 text-slate-400 text-[11px]">{{ $products->firstItem() + $loop->index }}</td>
                        <td class="px-3 py-2.5">
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
                                    <p class="text-[10.5px] text-slate-400 font-mono mt-0.5">{{ $p->sku }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 text-slate-500 text-[12px] hidden lg:table-cell">{{ $p->category?->name ?? '—' }}</td>
                        <td class="px-2.5 py-2.5 text-center">
                            <span class="inline-flex items-center gap-1 font-bold text-[12px] text-blue-700 bg-blue-50 border border-blue-200/80 px-2 py-0.5 rounded-lg shadow-2xs" title="Total Purchased">
                                <i class="fas fa-truck-ramp-box text-[10px] text-blue-500"></i>
                                {{ number_format($p->total_purchased_qty) }}
                            </span>
                        </td>
                        <td class="px-2.5 py-2.5 text-center">
                            <span class="inline-flex items-center gap-1 font-bold text-[12px] text-purple-700 bg-purple-50 border border-purple-200/80 px-2 py-0.5 rounded-lg shadow-2xs" title="Total Sold">
                                <i class="fas fa-cart-shopping text-[10px] text-purple-500"></i>
                                {{ number_format($p->total_sold_qty) }}
                            </span>
                        </td>
                        <td class="px-2.5 py-2.5 text-center">
                            <span class="inline-flex items-center gap-1 font-bold text-[13px] {{ $st==='out' ? 'text-red-700 bg-red-50 border-red-200' : ($st==='low' ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200') }} border px-2 py-0.5 rounded-lg shadow-2xs" title="Current In Stock">
                                <i class="fas fa-boxes-stacked text-[10px] opacity-70"></i>
                                {{ number_format($p->stock_qty) }}
                            </span>
                            @if($p->unit) <span class="text-[10px] text-slate-400 block -mt-0.5">{{ $p->unit }}</span> @endif
                        </td>
                        <td class="px-3 py-2.5 text-right text-slate-500 text-[12px] hidden xl:table-cell">{{ $currency }}{{ number_format($p->cost_price, 0) }}</td>
                        <td class="px-3 py-2.5 text-right text-slate-700 text-[12px] hidden xl:table-cell">{{ $currency }}{{ number_format($p->price, 0) }}</td>
                        <td class="px-3 py-2.5 text-center">
                            @if($st==='out')
                            <span class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-red-100 text-red-600 border border-red-200">Out</span>
                            @elseif($st==='low')
                            <span class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-amber-100 text-amber-600 border border-amber-200">Low</span>
                            @else
                            <span class="px-2 py-0.5 rounded-lg text-[10.5px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">OK</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-14 text-center text-slate-400">
                            <i class="fas fa-box-open text-3xl text-slate-300 mb-3 block"></i>
                            No products found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($products->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 text-sm">{{ $products->links() }}</div>
            @endif
        </div>
    </div>{{-- /left --}}

    {{-- ════ RIGHT: Movements (col-4) ════ --}}
    <div class="flex-[2] min-w-0">

        {{-- Movement filter --}}
        <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-3 space-y-2">
            {{-- preserve stock list filters --}}
            @foreach(['search','stock_filter','ppage'] as $k)
                @if(request($k))<input type="hidden" name="{{ $k }}" value="{{ request($k) }}">@endif
            @endforeach
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-arrow-right-arrow-left text-indigo-600 text-[10px]"></i>
                    </div>
                    <p class="text-[12.5px] font-bold text-slate-700">Stock Movements</p>
                </div>
                <span class="text-[11px] text-slate-400">{{ $movements->total() }} total</span>
            </div>
            <div class="flex gap-2">
                <select name="mtype"
                        class="flex-1 border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-600">
                    <option value="">IN & OUT</option>
                    <option value="in"  {{ request('mtype')=='in'  ? 'selected':'' }}>↑ Stock IN</option>
                    <option value="out" {{ request('mtype')=='out' ? 'selected':'' }}>↓ Stock OUT</option>
                </select>
                <select name="ref_type"
                        class="flex-1 border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-slate-600">
                    <option value="">All Source</option>
                    <option value="purchase"   {{ request('ref_type')=='purchase'   ? 'selected':'' }}>Purchase</option>
                    <option value="sale"       {{ request('ref_type')=='sale'       ? 'selected':'' }}>Sale</option>
                    <option value="adjustment" {{ request('ref_type')=='adjustment' ? 'selected':'' }}>Adjust</option>
                </select>
            </div>
            <div class="flex gap-2">
                <input type="date" name="from" value="{{ request('from') }}"
                       class="flex-1 border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <input type="date" name="to" value="{{ request('to') }}"
                       class="flex-1 border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-1.5 rounded-xl text-xs font-semibold transition-colors">
                    <i class="fas fa-filter text-[10px] mr-1"></i> Filter
                </button>
                @if(request('mtype') || request('ref_type') || request('from') || request('to'))
                <a href="{{ route('branch.stock.index', $branch) }}"
                   class="flex-1 border border-slate-200 text-slate-500 py-1.5 rounded-xl text-xs text-center hover:bg-slate-50">
                    Reset
                </a>
                @endif
            </div>
        </form>

        {{-- Movements list --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-slate-50">
                @forelse($movements as $m)
                @php
                    $refStyles = [
                        'purchase'   => 'bg-blue-100 text-blue-700',
                        'sale'       => 'bg-purple-100 text-purple-700',
                        'adjustment' => 'bg-slate-100 text-slate-600',
                    ];
                    $refStyle = $refStyles[$m->reference_type ?? ''] ?? 'bg-slate-100 text-slate-500';
                @endphp
                <div class="px-4 py-3 flex items-start gap-3 hover:bg-slate-50/50 transition-colors">

                    {{-- IN/OUT icon --}}
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5
                                {{ $m->type === 'in' ? 'bg-emerald-100' : 'bg-red-100' }}">
                        @if($m->type === 'in')
                        <i class="fas fa-arrow-up text-emerald-600 text-[11px]"></i>
                        @else
                        <i class="fas fa-arrow-down text-red-500 text-[11px]"></i>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-[12.5px] font-semibold text-slate-800 truncate leading-tight">
                            {{ $m->product?->name ?? '—' }}
                        </p>
                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                            @if($m->reference_type)
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-md {{ $refStyle }}">
                                {{ ucfirst($m->reference_type) }}
                            </span>
                            @endif
                            @if($m->note)
                            <span class="text-[10.5px] text-slate-400 truncate max-w-[120px]">{{ $m->note }}</span>
                            @endif
                        </div>
                        <p class="text-[10.5px] text-slate-400 mt-1">{{ $m->created_at->format('d M Y, h:i A') }}</p>
                    </div>

                    {{-- Qty --}}
                    <div class="text-right flex-shrink-0">
                        <span class="text-[15px] font-bold {{ $m->type === 'in' ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-4 py-14 text-center text-slate-400">
                    <i class="fas fa-arrow-right-arrow-left text-3xl text-slate-300 mb-3 block"></i>
                    <p class="text-sm">No movements found</p>
                </div>
                @endforelse
            </div>
            @if($movements->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 text-xs">{{ $movements->links() }}</div>
            @endif
        </div>

    </div>{{-- /right --}}
</div>{{-- /split --}}

@endsection
