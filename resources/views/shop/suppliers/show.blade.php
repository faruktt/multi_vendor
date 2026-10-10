@extends('shop.layout')
@section('title', $supplier->display_name . ' — Verified Supplier Store')

@section('breadcrumb')
<a href="{{ route('shop.products.index') }}" class="hover:text-brand-dark">Shop</a>
<span class="text-gray-300">/</span>
<span class="text-gray-500">Suppliers</span>
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">{{ $supplier->display_name }}</span>
@endsection

@section('content')
<div class="max-w-[1440px] mx-auto px-4 py-6 space-y-6">

    {{-- Supplier Store Header Banner --}}
    <div class="rounded-3xl bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 text-white p-6 sm:p-8 shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white border-2 border-emerald-400/40 p-1 overflow-hidden flex-shrink-0 shadow-lg flex items-center justify-center">
                    @if($supplier->logo_url)
                        <img src="{{ $supplier->logo_url }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover rounded-xl">
                    @else
                        <div class="w-full h-full bg-emerald-600 rounded-xl flex items-center justify-center text-white font-black text-3xl">
                            {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold mb-2 border border-emerald-400/30">
                        <i class="fas fa-certificate text-[10px]"></i> Verified Marketplace Vendor
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $supplier->display_name }}</h1>
                    @if($supplier->address)
                    <p class="text-emerald-100/70 text-xs mt-1 flex items-center gap-1.5">
                        <i class="fas fa-location-dot text-emerald-400"></i> {{ $supplier->address }}
                    </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-6 border-t md:border-t-0 md:border-l border-white/10 pt-4 md:pt-0 md:pl-8">
                <div>
                    <div class="text-2xl sm:text-3xl font-black text-white">{{ $products->total() }}</div>
                    <div class="text-xs text-emerald-200/70 font-medium">Active Products</div>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-400">100%</div>
                    <div class="text-xs text-emerald-200/70 font-medium">Authentic Quality</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Controls --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Products from this Supplier</h2>
            <p class="text-xs text-gray-500">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</p>
        </div>

        <form method="GET" action="{{ route('shop.supplier.show', $supplier->id) }}" class="flex flex-wrap items-center gap-2.5">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search products..."
                       class="bg-white border border-gray-200 rounded-xl pl-3.5 pr-8 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 w-48 sm:w-60">
                @if(request('q'))
                    <a href="{{ route('shop.supplier.show', $supplier->id) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                        &times;
                    </a>
                @endif
            </div>

            <select name="sort" onchange="this.form.submit()"
                    class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-700 font-medium">
                <option value="">Sort: Newest</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Product Name</option>
            </select>
        </form>
    </div>

    {{-- Product Grid --}}
    @if($products->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-3.5 gap-y-6 sm:gap-x-4 sm:gap-y-7">
        @foreach($products as $product)
            @include('shop.partials.product-card', ['product' => $product])
        @endforeach
    </div>

    <div class="pt-6">
        {{ $products->links() }}
    </div>
    @else
    <div class="text-center py-16 bg-white rounded-3xl border border-gray-200 p-8">
        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-2xl mb-3">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <h3 class="text-base font-bold text-gray-800">No products found</h3>
        <p class="text-xs text-gray-500 mt-1">This supplier has not published matching products yet.</p>
        <a href="{{ route('shop.products.index') }}" class="inline-block mt-4 px-4 py-2 rounded-xl bg-gray-900 text-white text-xs font-bold hover:bg-black transition">
            Browse Main Shop
        </a>
    </div>
    @endif

</div>
@endsection
