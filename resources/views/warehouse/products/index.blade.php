@extends('layouts.app')
@section('title', 'Products')
@section('heading', 'Product Management')

@section('content')
<div x-data="productList()">

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap gap-2.5 items-center">
    <div class="relative flex-1 min-w-[180px]">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
               class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50">
    </div>
    <select name="stock" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-slate-50">
        <option value="">All Stock</option>
        <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock (≤5)</option>
        <option value="out" {{ request('stock') === 'out'  ? 'selected' : '' }}>Out of Stock</option>
    </select>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 transition-colors">
        <i class="fas fa-filter text-xs"></i> Filter
    </button>
    @if(request()->hasAny(['search','stock']))
    <a href="{{ route('admin.warehouse.products.index') }}" class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50">Reset</a>
    @endif

    <a href="{{ route('admin.warehouse.products.create') }}"
       class="ml-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors shadow-sm shadow-blue-200">
        <i class="fas fa-plus"></i> Add Product
    </a>
</form>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">#</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Branch</th>
                <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Price</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Transferred</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Sold (branches)</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($products as $product)
            <tr class="hover:bg-slate-50/50 transition-colors group" id="prow-{{ $product->id }}">

                {{-- # --}}
                <td class="px-3 py-3 text-slate-400 text-xs">{{ $products->firstItem() + $loop->index }}</td>

                {{-- Product --}}
                <td class="px-3 py-3">
                    <div class="flex items-center gap-3">
                        {{-- Thumbnail --}}
                        <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center border border-slate-100"
                             style="background: linear-gradient(135deg,#f0f4ff,#e8f0fe)">
                            @if(isset($product->image_url))
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
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

                {{-- Branch --}}
                <td class="px-3 py-3">
                    <div class="flex flex-wrap gap-1 max-w-[160px]">
                        @forelse($product->branch_names as $bn)
                        <span class="bg-orange-50 text-orange-700 border border-orange-100 px-2 py-0.5 rounded-lg text-[10.5px] font-medium">
                            {{ $bn }}
                        </span>
                        @empty
                        <span class="text-slate-300 text-xs">—</span>
                        @endforelse
                    </div>
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

                {{-- Transferred to branches --}}
                <td class="px-3 py-3 text-center">
                    <span class="text-[12.5px] font-semibold text-blue-600">{{ number_format($product->transferred_qty ?? 0) }}</span>
                </td>

                {{-- Sold across branches --}}
                <td class="px-3 py-3 text-center">
                    <span class="text-[12.5px] font-semibold text-emerald-600">{{ number_format($product->sold_qty ?? 0) }}</span>
                </td>

                {{-- Actions --}}
                <td class="px-3 py-3">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('admin.warehouse.products.barcode-show', $product) }}"
                           target="_blank" title="Print Barcode"
                           class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 transition-colors">
                            <i class="fas fa-barcode text-xs"></i>
                        </a>
                        <a href="{{ route('admin.warehouse.products.edit', $product) }}"
                           title="Edit product"
                           class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors">
                            <i class="fas fa-pen text-xs"></i>
                        </a>
                        <a href="{{ route('admin.warehouse.products.report', $product) }}"
                           title="Stock movement report"
                           class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-colors">
                            <i class="fas fa-chart-line text-xs"></i>
                        </a>
                        <button type="button" title="Delete"
                                @click="openDelete({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ route('admin.warehouse.products.destroy', $product) }}')"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-colors">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                    <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box text-3xl text-slate-300"></i>
                    </div>
                    <p class="font-medium text-slate-500">No products found.</p>
                    <a href="{{ route('admin.warehouse.products.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-blue-600 text-sm hover:underline">
                        <i class="fas fa-plus text-xs"></i> Add your first product
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($products->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $products->links() }}</div>
    @endif
</div>

{{-- ── Delete Confirm Modal ─────────────────────────────────────── --}}
<div x-show="delModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delModal = false">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trash-alt text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Delete Product?</p>
                <p class="text-sm text-slate-500 mt-0.5">This action cannot be undone.</p>
            </div>
        </div>
        <div class="bg-slate-50 rounded-xl px-4 py-2.5 mb-5 border border-slate-100">
            <p class="text-sm font-semibold text-slate-700" x-text="delName"></p>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fas fa-trash-alt text-xs mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function productList() {
    return {
        delModal: false,
        delName: '',
        delUrl: '',

        openDelete(id, name, url) {
            this.delName = name;
            this.delUrl  = url;
            this.delModal = true;
        }
    };
}
</script>
@endpush
@endsection
