@extends('layouts.app')
@section('title','All Products')
@section('heading','All Products')
@php
    $currency   = $appSettings['currency'] ?? '৳';
    $totalProds = collect($branchSummary)->sum('count');
    $totalLow   = collect($branchSummary)->sum('low_stock');
    $totalOut   = collect($branchSummary)->sum('out_stock');
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')
<div x-data="productsAdminPage()">

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Products</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalProds) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">across all branches</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wide">Low Stock</p>
        <p class="text-2xl font-bold text-amber-500 mt-1">{{ number_format($totalLow) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">≤ 10 units</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
        <p class="text-[11px] font-semibold text-red-500 uppercase tracking-wide">Out of Stock</p>
        <p class="text-2xl font-bold text-red-500 mt-1">{{ number_format($totalOut) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">zero quantity</p>
    </div>
</div>

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.all-products') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[190px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div>
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[120px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ $s['count'] }}</span> products
                    @if($s['low_stock']>0)&nbsp;·&nbsp;<span class="text-amber-500">{{ $s['low_stock'] }} low</span>@endif
                    @if($s['out_stock']>0)&nbsp;·&nbsp;<span class="text-red-500">{{ $s['out_stock'] }} out</span>@endif
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or SKU..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2 text-sm cursor-pointer hover:bg-slate-50 transition-colors">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked':'' }} class="rounded">
            <span class="text-slate-600">Low Stock Only</span>
        </label>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-products') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <div class="ml-auto flex items-center gap-3 relative" x-data="{ openAdd: false }">
            <span class="text-[12px] text-slate-400 hidden sm:inline">{{ $products->total() }} products</span>

            @if(request('vendor_id'))
                @php $filteredVendor = $vendors->firstWhere('id', request('vendor_id')); @endphp
                <a href="{{ route('branch.products.create', request('vendor_id')) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Add Product ({{ $filteredVendor->name ?? 'Branch' }})
                </a>
            @else
                <div class="relative">
                    <button type="button" @click="openAdd = !openAdd" @click.outside="openAdd = false"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
                        <i class="fas fa-plus text-xs"></i> Add Product
                        <i class="fas fa-chevron-down text-[10px] ml-1 transition-transform" :class="openAdd ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="openAdd" x-cloak
                         class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 animate-in fade-in slide-in-from-top-2">
                        <div class="px-3 py-1.5 border-b border-slate-100 mb-1">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Select Branch to Add In</p>
                        </div>
                        <div class="max-h-60 overflow-y-auto space-y-0.5 px-1.5">
                            @foreach($vendors as $v)
                            <a href="{{ route('branch.products.create', $v->id) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-colors">
                                <i class="fas fa-store text-blue-500 text-xs"></i>
                                <span class="truncate">{{ $v->name }}</span>
                            </a>
                            @endforeach
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="{{ route('admin.warehouse.products.create', 'warehouse') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-purple-700 hover:bg-purple-50 rounded-xl transition-colors">
                                <i class="fas fa-warehouse text-purple-500 text-xs"></i>
                                <span>Central Warehouse</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Category</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Price</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Cost</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Stock</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($products as $product)
                @php
                    $stockStatus = $product->stock_qty == 0 ? 'out' : ($product->stock_qty <= 10 ? 'low' : 'ok');
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors {{ $stockStatus==='out' ? 'bg-red-50/20' : ($stockStatus==='low' ? 'bg-amber-50/20' : '') }}">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $products->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800 text-[12.5px]">{{ $product->name }}</p>
                        <p class="text-[10.5px] text-slate-400 font-mono">{{ $product->sku }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $product->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-[12.5px] hidden md:table-cell">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">{{ $currency }}{{ number_format($product->price,0) }}</td>
                    <td class="px-4 py-3 text-right text-slate-500 text-[12.5px] hidden lg:table-cell">{{ $currency }}{{ number_format($product->cost_price,0) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-[13px] {{ $stockStatus==='out' ? 'text-red-500' : ($stockStatus==='low' ? 'text-amber-500' : 'text-slate-700') }}">
                            {{ $product->stock_qty }}
                        </span>
                        <span class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full
                            {{ $stockStatus==='out' ? 'bg-red-100 text-red-600' : ($stockStatus==='low' ? 'bg-amber-100 text-amber-600' : 'hidden') }}">
                            {{ $stockStatus==='out' ? 'Out' : ($stockStatus==='low' ? 'Low' : '') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold
                            {{ $product->status==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Barcode --}}
                            <a href="{{ route('branch.products.barcode-show', [$product->vendor_id, $product]) }}"
                               class="w-7 h-7 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 flex items-center justify-center transition-colors" title="Barcode">
                                <i class="fas fa-barcode text-[11px]"></i>
                            </a>
                            {{-- Edit --}}
                            <a href="{{ route('branch.products.edit', [$product->vendor_id, $product]) }}"
                               class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </a>
                            {{-- Delete --}}
                            <button @click="openDelete({{ $product->id }}, {{ $product->vendor_id }}, '{{ addslashes($product->name) }}')"
                                    class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors" title="Delete">
                                <i class="fas fa-trash text-[11px]"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-16 text-center">
                        <i class="fas fa-box text-slate-200 text-4xl mb-3 block"></i>
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

{{-- ══ DELETE MODAL ══ --}}
<div x-show="deleteOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="deleteOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="deleteOpen = false">
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash text-red-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-bold text-slate-800 mb-1">Delete Product?</h3>
            <p class="text-[12.5px] text-slate-500 mb-5">
                <span class="font-semibold text-slate-700" x-text="deleteName"></span> will be permanently removed along with all stock movements.
            </p>
        </div>
        <form :action="deleteUrl" method="POST" class="flex gap-3">
            @csrf @method('DELETE')
            <button type="button" @click="deleteOpen = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Delete</button>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
function productsAdminPage() {
    return {
        deleteOpen: false, deleteName: '', deleteUrl: '',
        openDelete(id, vendor, name) {
            this.deleteName = name;
            this.deleteUrl  = `/branch/${vendor}/products/${id}`;
            this.deleteOpen = true;
        }
    };
}
</script>
@endpush
