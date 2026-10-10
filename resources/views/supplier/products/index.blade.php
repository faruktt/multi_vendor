@extends('supplier.layouts.app')
@section('title', 'My Products')
@section('heading', 'My Products Catalog')

@section('content')
<div class="space-y-5">

    {{-- Top Action Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Search & Filters --}}
        <form method="GET" action="{{ route('supplier.products.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by product name, SKU or barcode..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-600">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <select name="stock" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-600">
                <option value="">All Stock</option>
                <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock (≤ 5)</option>
                <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
            </select>

            @if(request()->anyFilled(['search', 'status', 'stock']))
            <a href="{{ route('supplier.products.index') }}"
               class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                Reset
            </a>
            @endif
        </form>

        {{-- Add Product button --}}
        <div class="flex-shrink-0">
            <a href="{{ route('supplier.products.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                <i class="fas fa-plus"></i>
                <span>Upload New Product</span>
            </a>
        </div>
    </div>

    {{-- Approval Status Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="{{ route('supplier.products.index') }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ !request('approval_status') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <span>All Products</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ !request('approval_status') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $counts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('supplier.products.index', ['approval_status' => 'pending']) }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ request('approval_status') === 'pending' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-clock text-[11px]"></i>
            <span>Pending Approval</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('approval_status') === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $counts['pending'] ?? 0 }}</span>
        </a>
        <a href="{{ route('supplier.products.index', ['approval_status' => 'approved']) }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ request('approval_status') === 'approved' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-check-circle text-[11px]"></i>
            <span>Approved &amp; Live</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('approval_status') === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">{{ $counts['approved'] ?? 0 }}</span>
        </a>
        <a href="{{ route('supplier.products.index', ['approval_status' => 'rejected']) }}"
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ request('approval_status') === 'rejected' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/30' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-times-circle text-[11px]"></i>
            <span>Rejected</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ request('approval_status') === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">{{ $counts['rejected'] ?? 0 }}</span>
        </a>
    </div>

    {{-- Product Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Product</th>
                        <th class="px-4 py-3.5 font-semibold">Category</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Price</th>
                        <th class="px-4 py-3.5 font-semibold">Approval &amp; Commission</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Stock</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Sold</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80 transition">
                        {{-- Product Info --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($product->first_image_url)
                                        <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-image text-slate-300 text-lg"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 max-w-[220px]">
                                    <div class="font-bold text-slate-900 text-sm truncate" title="{{ $product->name }}">
                                        {{ $product->name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center gap-2">
                                        <span>{{ $product->sku }}</span>
                                        @if($product->barcode)
                                            <span class="text-slate-300">&bull;</span>
                                            <span>{{ $product->barcode }}</span>
                                        @endif
                                    </div>
                                    @if($product->variants->count() > 0)
                                    <div class="text-[10px] text-indigo-600 font-semibold mt-0.5">
                                        <i class="fas fa-layer-group text-[9px]"></i> {{ $product->variants->count() }} Variants
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Category --}}
                        <td class="px-4 py-3.5 text-slate-600 font-medium">
                            {{ $product->category?->name ?: 'General' }}
                        </td>

                        {{-- Price --}}
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-sm">৳{{ number_format($product->price, 2) }}</div>
                            @if($product->has_old_price)
                                <div class="text-[10.5px] text-slate-400 line-through">৳{{ number_format($product->old_price, 2) }}</div>
                            @endif
                            @if($product->cost_price > 0)
                                <div class="text-[10px] text-slate-400 font-medium">Cost: ৳{{ number_format($product->cost_price, 2) }}</div>
                            @endif
                        </td>

                        {{-- Approval & Commission Breakdown --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($product->isPendingApproval())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-clock text-[10px]"></i> Pending Approval
                                </span>
                                <div class="text-[10.5px] text-slate-400 mt-1">Pending admin commission & approval</div>
                            @elseif($product->isApproved())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-check-circle text-[10px]"></i> Approved
                                </span>
                                <div class="text-[11px] text-slate-700 font-semibold mt-1">
                                    Admin Fee: <span class="text-rose-600 font-bold">{{ $product->effective_admin_commission_rate }}%</span>
                                    <span class="text-slate-400 font-normal">(৳{{ number_format($product->admin_commission_per_unit, 2) }})</span>
                                </div>
                                <div class="text-[11px] font-extrabold text-emerald-700 mt-0.5">
                                    You Earn: ৳{{ number_format($product->supplier_earning_per_unit, 2) }} <span class="text-[10px] font-normal text-slate-500">/ unit</span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fas fa-times-circle text-[10px]"></i> Rejected
                                </span>
                                @if($product->rejection_reason)
                                    <div class="text-[10.5px] text-rose-600 mt-1 max-w-[200px] leading-tight" title="{{ $product->rejection_reason }}">
                                        Reason: {{ $product->rejection_reason }}
                                    </div>
                                @endif
                            @endif
                        </td>

                        {{-- Stock --}}
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                {{ $product->stock_qty > 5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                   ($product->stock_qty > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                {{ $product->stock_qty }} {{ $product->unit }}
                            </span>
                        </td>

                        {{-- Sold --}}
                        <td class="px-4 py-3.5 text-center font-bold text-slate-800 whitespace-nowrap">
                            {{ (int)$product->sold_qty }}
                        </td>

                        {{-- Status toggle --}}
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @if(!$product->isApproved())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-medium bg-slate-100 text-slate-500" title="Waiting for admin approval">
                                    <i class="fas fa-lock text-[9px]"></i> Inactive
                                </span>
                            @else
                                <form method="POST" action="{{ route('supplier.products.toggle', $product->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition
                                            {{ $product->status === 'active'
                                                ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                                            title="Click to toggle status">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $product->status === 'active' ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                                        <span>{{ ucfirst($product->status) }}</span>
                                    </button>
                                </form>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-right space-x-1 whitespace-nowrap">
                            {{-- View Live --}}
                            <a href="{{ route('shop.products.show', $product->slug) }}" target="_blank"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition"
                               title="View on Storefront">
                                <i class="fas fa-eye text-xs"></i>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('supplier.products.edit', $product->id) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition"
                               title="Edit Product">
                                <i class="fas fa-pen text-xs"></i>
                            </a>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('supplier.products.destroy', $product->id) }}" class="inline-block"
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition"
                                        title="Delete Product">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto flex items-center justify-center text-2xl text-slate-300 mb-3">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">No products found</div>
                            <p class="text-xs text-slate-400 mt-1">Try changing your search filters or upload your first product.</p>
                            <a href="{{ route('supplier.products.create') }}"
                               class="inline-block mt-4 px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition">
                                Upload Product Now
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
