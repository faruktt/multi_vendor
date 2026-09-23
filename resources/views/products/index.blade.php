@extends('layouts.app')
@section('title', 'Products')
@section('heading', 'Product Management')

@section('content')
<div>

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap gap-2.5 items-center">
    <div class="relative flex-1 min-w-[180px]">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
               class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50">
    </div>
    <select name="category_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-slate-50">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select name="stock" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-slate-50">
        <option value="">All Stock</option>
        <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock (≤5)</option>
        <option value="out" {{ request('stock') === 'out'  ? 'selected' : '' }}>Out of Stock</option>
    </select>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 transition-colors">
        <i class="fas fa-filter text-xs"></i> Filter
    </button>
    @if(request()->hasAny(['search','category_id','stock']))
    <a href="{{ route('branch.products.index', $branch) }}" class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50">Reset</a>
    @endif

    <div class="flex items-center gap-2 ml-auto">
        {{-- Bulk barcode print --}}
        <button id="bulk-print-btn" onclick="bulkPrint()" style="display:none"
                class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2 transition-colors">
            <i class="fas fa-barcode"></i> Print (<span id="selected-count">0</span>)
        </button>
        {{-- Add Product --}}
        <a href="{{ route('branch.products.create', $branch) }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 transition-colors shadow-sm">
            <i class="fas fa-plus text-xs"></i> Add Product
        </a>
    </div>
</form>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="pl-4 pr-2 py-3 w-9">
                    <input type="checkbox" id="select-all" onchange="toggleAll(this)"
                           class="w-4 h-4 rounded border-slate-300 text-blue-600 cursor-pointer">
                </th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">#</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Category</th>
                <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Price</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($products as $product)
            <tr class="hover:bg-slate-50/50 transition-colors group" id="prow-{{ $product->id }}">

                {{-- Checkbox --}}
                <td class="pl-4 pr-2 py-3">
                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                           onchange="updateSelection()"
                           class="product-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 cursor-pointer">
                </td>

                {{-- # --}}
                <td class="px-3 py-3 text-slate-400 text-xs">{{ $products->firstItem() + $loop->index }}</td>

                {{-- Product --}}
                <td class="px-3 py-3">
                    <div class="flex items-center gap-3">
                        {{-- Thumbnail --}}
                        <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center border border-slate-100"
                             style="background: linear-gradient(135deg,#f0f4ff,#e8f0fe)">
                            @if($product->first_image_url)
                                <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-box text-blue-300 text-sm"></i>
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 text-[13px] leading-tight">{{ $product->name }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($product->sku)
                                <span class="text-[10.5px] text-slate-400 font-mono">{{ $product->sku }}</span>
                                @endif
                                @if($product->variants->count())
                                <span class="text-[10px] bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded-full font-medium">
                                    {{ $product->variants->count() }} variants
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Category --}}
                <td class="px-3 py-3 hidden lg:table-cell">
                    @if($product->category)
                    <span class="bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-lg text-[11.5px] font-medium">
                        {{ $product->category->name }}
                    </span>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>

                {{-- Price --}}
                <td class="px-3 py-3 text-right">
                    <p class="font-bold text-slate-800 text-[13px]">৳{{ number_format($product->price, 0) }}</p>
                    @if($product->has_old_price)
                    <p class="text-[11px] text-slate-400 line-through">৳{{ number_format($product->old_price, 0) }}</p>
                    @endif
                    @if($product->cost_price > 0)
                    @php $profit = $product->price > 0 ? round((($product->price - $product->cost_price) / $product->price) * 100) : 0; @endphp
                    <p class="text-[10.5px] {{ $profit > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $profit > 0 ? '+' : '' }}{{ $profit }}% margin
                    </p>
                    @endif
                </td>

                {{-- Stock --}}
                <td class="px-3 py-3 text-center">
                    @php
                        $sq = $product->stock_qty;
                        $cls = $sq <= 0 ? 'bg-red-100 text-red-600 border-red-200'
                             : ($sq <= 5 ? 'bg-amber-100 text-amber-700 border-amber-200'
                             : 'bg-emerald-100 text-emerald-700 border-emerald-200');
                    @endphp
                    <span class="inline-flex items-center gap-1 border px-2.5 py-1 rounded-lg text-[11.5px] font-semibold {{ $cls }}">
                        @if($sq <= 0) <i class="fas fa-exclamation text-[9px]"></i> @endif
                        {{ $sq }} {{ $product->unit }}
                    </span>
                </td>

                {{-- Actions --}}
                <td class="px-3 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('branch.products.barcode-show', [$branch, $product]) }}"
                           target="_blank" title="Print Barcode"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 transition-colors">
                            <i class="fas fa-barcode text-xs"></i>
                        </a>
                        <a href="{{ route('branch.products.edit', [$branch, $product]) }}"
                           title="Edit"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors">
                            <i class="fas fa-pen text-xs"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center text-slate-400">
                    <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box text-3xl text-slate-300"></i>
                    </div>
                    <p class="font-medium text-slate-500">No products found</p>
                    <p class="mt-2 text-[12.5px] text-slate-400">Products are added from the Warehouse and distributed here.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($products->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $products->links() }}</div>
    @endif
</div>

</div>

@push('scripts')
<script>
const bulkUrl = "{{ route('branch.products.barcode-bulk', $branch) }}";

function toggleAll(master) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = master.checked);
    updateSelection();
}
function updateSelection() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const btn = document.getElementById('bulk-print-btn');
    document.getElementById('selected-count').textContent = checked.length;
    btn.style.display = checked.length > 0 ? 'flex' : 'none';
    const all = document.querySelectorAll('.product-checkbox');
    document.getElementById('select-all').indeterminate = checked.length > 0 && checked.length < all.length;
    document.getElementById('select-all').checked = checked.length === all.length && all.length > 0;
}
function bulkPrint() {
    const ids = [...document.querySelectorAll('.product-checkbox:checked')].map(cb => cb.value);
    if (ids.length) window.open(bulkUrl + '?ids=' + ids.join(','), '_blank');
}
</script>
@endpush
@endsection
