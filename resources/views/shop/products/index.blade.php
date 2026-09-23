@extends('shop.layout')
@section('title', ($activeCategory->name ?? 'All Products') . ' — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full')

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<a href="{{ route('shop.products.index') }}" class="hover:text-brand-dark {{ !$activeCategory ? 'text-gray-900 font-semibold' : '' }}">Shop</a>
@if($activeCategory)
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">{{ $activeCategory->name }}</span>
@endif
@endsection

@section('content')

<div class="max-w-[1440px] mx-auto flex items-start px-4 py-4 gap-4">

    @include('shop.partials.sidebar', ['categories' => $categories, 'activeCategoryId' => $activeCategory?->id, 'showFilters' => true])

    <div class="flex-1 min-w-0">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">

            {{-- Header: title + sort --}}
            <div class="flex items-center justify-between gap-4 flex-wrap mb-4">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight">{{ $activeCategory->name ?? 'All Products' }}</h1>
                    <p class="text-[12.5px] text-gray-500 mt-0.5">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found</p>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2.5 flex-wrap">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    @if(request('min_price'))<input type="hidden" name="min_price" value="{{ request('min_price') }}">@endif
                    @if(request('max_price'))<input type="hidden" name="max_price" value="{{ request('max_price') }}">@endif
                    <select name="sort" onchange="this.form.submit()"
                            class="h-10 border border-gray-200 rounded-lg px-3 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand text-gray-700">
                        <option value="">Newest</option>
                        <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name"       {{ request('sort') === 'name'       ? 'selected' : '' }}>Name</option>
                    </select>
                </form>
            </div>

            @if($products->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($products as $product)
                    @include('shop.partials.shelf-product-card', ['product' => $product, 'widthClass' => 'w-full'])
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
            @else
            <div class="py-20 text-center">
                <i class="fas fa-box-open text-gray-200 text-4xl mb-3 block"></i>
                <p class="text-gray-400 text-sm">No products found{{ request('q') ? ' for "' . request('q') . '"' : '' }}.</p>
                @if($activeCategory)
                <a href="{{ route('shop.products.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-brand-dark text-sm font-semibold hover:underline">
                    <i class="fas fa-arrow-left text-xs"></i> Browse all products
                </a>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

@endsection
