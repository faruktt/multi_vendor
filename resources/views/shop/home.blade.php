@extends('shop.layout')
@section('title', ($branch->system_name ?? $branch->name) . ' — Online Store')
@section('main-class', 'w-full')

@section('content')

<!-- ══ ROW 1: Hero Banner (Main Slider + Promo Card) ══ -->
<div class="max-w-[1440px] mx-auto px-4 py-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">

        {{-- ══ Hero row: Main slider (1376x768 exact 1.792:1) and side promo banner (768x768 exact 1:1) ══ --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1.792fr_1fr] gap-3.5 items-stretch">

            {{-- LEFT: hero slider if banners uploaded, else the original brand/stats tile --}}
            @if($heroBanners->isNotEmpty())
            <div class="relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50 group w-full aspect-[1376/768]">
                <div id="heroTrack" class="flex overflow-x-auto scroll-smooth snap-x snap-mandatory w-full h-full">
                    @foreach($heroBanners as $banner)
                    @if($banner->link)
                    <a href="{{ $banner->link }}" class="snap-start flex-shrink-0 w-full h-full block">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-full object-cover">
                    </a>
                    @else
                    <div class="snap-start flex-shrink-0 w-full h-full">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-full object-cover">
                    </div>
                    @endif
                    @endforeach
                </div>
                @if($heroBanners->count() > 1)
                <button type="button" onclick="shelfScroll('heroTrack', -1)" class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:text-brand-dark transition-all hover:scale-105">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" onclick="shelfScroll('heroTrack', 1)" class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:text-brand-dark transition-all hover:scale-105">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 z-10" id="heroDots"></div>
                @endif
            </div>
            @else
            <div class="relative rounded-xl border border-gray-200 bg-gradient-to-br from-brand-light to-[#F5F8EF] aspect-[1376/768] overflow-hidden px-8 py-6 flex items-center gap-4">
                <div class="flex-shrink-0 w-[150px] text-center">
                    <div class="text-5xl font-extrabold text-brand leading-none tracking-tight">{{ Str::substr($branch->system_name ?? $branch->name, 0, 1) }}</div>
                    <strong class="block text-lg font-extrabold text-gray-900 -mt-1">{{ $branch->system_name ?? $branch->name }}</strong>
                    <span class="block text-[11px] font-bold text-brand-dark mt-1">{{ $branch->address ?? 'Shop online, delivered fast' }}</span>
                </div>

                <div class="relative flex-1 h-[210px] hidden sm:block">
                    <svg viewBox="0 0 300 210" class="absolute inset-0 w-full h-full">
                        <path d="M 35 158 A 115 115 0 0 1 250 118" fill="none" stroke="#CFE3D0" stroke-width="2" stroke-dasharray="1 6" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute bg-white border border-gray-200 shadow-sm rounded-xl px-3.5 py-2.5 text-center" style="left:35px; top:158px; transform:translate(-50%,-50%)">
                        <strong class="block text-base font-extrabold text-brand-dark leading-none">{{ $stats['categories'] }}</strong>
                        <span class="block text-[9px] font-bold text-gray-500 uppercase tracking-wide mt-1">Categories</span>
                    </div>
                    <div class="absolute bg-white border border-gray-200 shadow-sm rounded-xl px-3.5 py-2.5 text-center" style="left:88px; top:73px; transform:translate(-50%,-50%)">
                        <strong class="block text-base font-extrabold text-brand-dark leading-none">{{ $stats['products'] }}+</strong>
                        <span class="block text-[9px] font-bold text-gray-500 uppercase tracking-wide mt-1">Products</span>
                    </div>
                    <div class="absolute bg-white border border-gray-200 shadow-sm rounded-xl px-3.5 py-2.5 text-center" style="left:180px; top:52px; transform:translate(-50%,-50%)">
                        <strong class="block text-base font-extrabold text-brand-dark leading-none">24h</strong>
                        <span class="block text-[9px] font-bold text-gray-500 uppercase tracking-wide mt-1">Delivery</span>
                    </div>
                    <div class="absolute bg-white border border-gray-200 shadow-sm rounded-xl px-3.5 py-2.5 text-center" style="left:250px; top:118px; transform:translate(-50%,-50%)">
                        <strong class="block text-base font-extrabold text-brand-dark leading-none">{{ $stats['orders'] }}</strong>
                        <span class="block text-[9px] font-bold text-gray-500 uppercase tracking-wide mt-1">Orders Served</span>
                    </div>

                    <p class="absolute right-0 bottom-0 text-2xl font-extrabold text-brand-dark text-right leading-snug">
                        Built on Trust,<br>Delivered with Care
                    </p>
                </div>
            </div>
            @endif

            {{-- RIGHT: Promo Card 1 (Top) if uploaded, else the original deal tile --}}
            @if($promo1Banner)
            @if($promo1Banner->link)
            <a href="{{ $promo1Banner->link }}" class="block rounded-xl overflow-hidden border border-gray-200 bg-gray-50 w-full h-full aspect-square">
                <img src="{{ $promo1Banner->image_url }}" alt="{{ $promo1Banner->title ?? 'Promo' }}" class="w-full h-full object-cover">
            </a>
            @else
            <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50 w-full h-full aspect-square">
                <img src="{{ $promo1Banner->image_url }}" alt="{{ $promo1Banner->title ?? 'Promo' }}" class="w-full h-full object-cover">
            </div>
            @endif
            @else
            <div class="relative rounded-xl w-full h-full aspect-square p-6 overflow-hidden bg-gradient-to-br from-brand to-brand-dark flex flex-col justify-center gap-4 text-white">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-white/75">Every Category</p>
                    <p class="text-2xl font-extrabold leading-tight mt-1">One Cart,<br>One Delivery</p>
                </div>
                <div class="h-px bg-white/20"></div>
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-white/75">Need Help?</p>
                    <p class="text-lg font-bold mt-1">Call {{ $appSettings['phone'] ?? '16247' }}</p>
                    <p class="text-xs text-white/80 mt-0.5">We'll help you place your order</p>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@if(isset($flashSaleProducts) && $flashSaleProducts->isNotEmpty())
<div class="max-w-[1440px] mx-auto px-4 mt-5">
    <div class="rounded-2xl border-2 border-amber-300 bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-amber-500/10 p-4 shadow-sm relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-36 h-36 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex items-center justify-between mb-3 relative z-10">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500 text-white shadow-sm font-black text-sm">
                    ⚡
                </span>
                <div>
                    <h2 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                        Flash Sale & Super Deals
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-rose-600 text-white animate-pulse">Limited Time</span>
                    </h2>
                    <p class="text-xs text-gray-500 font-medium">Grab these hot discounts before time runs out!</p>
                </div>
            </div>
            <a href="{{ route('shop.products.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1">
                View All <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="relative z-10">
            <button type="button" onclick="shelfScroll('shelf-flash-track', -1)" class="flex absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-700 hover:text-amber-600 hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" onclick="shelfScroll('shelf-flash-track', 1)" class="flex absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-700 hover:text-amber-600 hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <div id="shelf-flash-track" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1">
                @foreach($flashSaleProducts as $product)
                    @include('shop.partials.shelf-product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@if($bestSelling->isNotEmpty())
<div class="max-w-[1440px] mx-auto px-4 mt-5">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-baseline justify-between mb-3">
            <h2 class="text-lg font-extrabold tracking-tight">🔥 Best Selling Items</h2>
        </div>
        <div class="relative">
            <button type="button" onclick="shelfScroll('shelf-best-track', -1)" class="flex absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" onclick="shelfScroll('shelf-best-track', 1)" class="flex absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <div id="shelf-best-track" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1">
                @foreach($bestSelling as $product)
                    @include('shop.partials.shelf-product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@if($newArrivals->isNotEmpty())
<div class="max-w-[1440px] mx-auto px-4 mt-5">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-baseline justify-between mb-3">
            <h2 class="text-lg font-extrabold tracking-tight">New Arrival</h2>
        </div>
        <div class="relative">
            <button type="button" onclick="shelfScroll('shelf-new-track', -1)" class="flex absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" onclick="shelfScroll('shelf-new-track', 1)" class="flex absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <div id="shelf-new-track" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1">
                @foreach($newArrivals as $product)
                    @include('shop.partials.shelf-product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══ Mid-page banner (falls back to an honest text CTA if no image is uploaded) ══ --}}
<div class="max-w-[1440px] mx-auto px-4 mt-5">
    @if($midBanner)
        @if($midBanner->link)
        <a href="{{ $midBanner->link }}" class="block rounded-2xl overflow-hidden shadow-sm min-h-[170px] bg-gray-50">
            <img src="{{ $midBanner->image_url }}" alt="{{ $midBanner->title ?? 'Banner' }}" class="w-full h-auto object-cover">
        </a>
        @else
        <div class="rounded-2xl overflow-hidden shadow-sm min-h-[170px] bg-gray-50">
            <img src="{{ $midBanner->image_url }}" alt="{{ $midBanner->title ?? 'Banner' }}" class="w-full h-auto object-cover">
        </div>
        @endif
    @else
    <div class="rounded-2xl overflow-hidden shadow-sm relative min-h-[170px] flex items-center bg-gradient-to-r from-[#FDF3D8] via-[#F4F0C8] to-[#E9F3D8]">
        <div class="flex items-center justify-between w-full px-8 gap-6 flex-wrap">
            <div>
                <p class="text-deal font-extrabold text-lg leading-tight">Prefer to Order by Phone?</p>
                <p class="text-brand-dark font-extrabold text-2xl leading-tight mt-1">Call {{ $appSettings['phone'] ?? '16247' }}</p>
                <p class="text-[13px] text-gray-600 mt-1.5 max-w-md">Our team will help you place an order and answer any questions — cash on delivery available.</p>
            </div>
            <a href="{{ route('shop.products.index') }}" class="bg-brand hover:bg-brand-dark text-white font-extrabold text-sm px-5 py-2.5 rounded-lg flex-shrink-0">Browse All Products</a>
        </div>
    </div>
    @endif
</div>

@foreach($categoryShelves as $shelf)
@php $trackId = 'shelf-cat-' . $shelf->category->id . '-track'; @endphp
<div class="max-w-[1440px] mx-auto px-4 mt-5 {{ $loop->last ? 'mb-10' : '' }}">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-baseline justify-between mb-3">
            <h2 class="text-lg font-extrabold tracking-tight">{{ $shelf->category->name }}</h2>
            <a href="{{ route('shop.products.category', $shelf->category->slug) }}" class="text-xs font-bold text-gray-500 hover:text-brand-dark">See All &rsaquo;</a>
        </div>
        <div class="relative">
            <button type="button" onclick="shelfScroll('{{ $trackId }}', -1)" class="flex absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" onclick="shelfScroll('{{ $trackId }}', 1)" class="flex absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <div id="{{ $trackId }}" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1">
                @foreach($shelf->products as $product)
                    @include('shop.partials.shelf-product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- ══ Homepage Partnership Opportunities: Reseller & Supplier Cards ══ --}}
@if((isset($resellerContents) && $resellerContents->isNotEmpty()) || (isset($supplierContents) && $supplierContents->isNotEmpty()) || (isset($homeContents) && $homeContents->isNotEmpty()))
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 my-10 sm:my-14">
    <div class="relative rounded-3xl bg-gradient-to-b from-[#F7F9F5] via-white to-[#F7F9F5] border border-gray-200/80 p-6 sm:p-8 lg:p-10 shadow-sm overflow-hidden">
        {{-- Subtle ambient mesh glows in background --}}
        <div class="absolute -top-24 -left-24 w-80 h-80 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Section Header --}}
        <div class="relative z-10 text-center max-w-2xl mx-auto mb-8">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white border border-gray-200/90 text-gray-700 text-xs font-bold uppercase tracking-wider shadow-2xs mb-2.5">
                <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                <span>Business Partnerships</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                Partner With Us & Grow
            </h2>
            <p class="text-gray-500 text-xs sm:text-sm mt-1.5 font-normal leading-relaxed">
                Start reselling with zero initial investment, or join as a trusted supplier to distribute your products nationwide.
            </p>
        </div>

        {{-- 2-Column Side-by-Side Grid --}}
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-stretch">

            {{-- ════ LEFT: RESELLER CARDS (DYNAMIC FROM ADMIN PANEL) ════ --}}
            @if(isset($resellerContents) && $resellerContents->isNotEmpty())
                @foreach($resellerContents as $resellerSection)
                <div class="rounded-2xl border border-indigo-100 bg-white shadow-sm hover:shadow-xl hover:border-indigo-200 transition-all duration-300 flex flex-col justify-between overflow-hidden group relative">
                    {{-- Top Accent Bar --}}
                    <div class="h-1 w-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600"></div>

                    <div class="p-6 sm:p-7 flex-1 flex flex-col">
                        {{-- Top Header Row --}}
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100/80 text-indigo-600 flex items-center justify-center text-xl shadow-2xs group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-200 flex-shrink-0">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div>
                                    <span class="text-[11px] font-black uppercase tracking-wider text-indigo-600 block">
                                        Reseller Program
                                    </span>
                                    <h3 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight leading-snug">
                                        {{ $resellerSection->title }}
                                    </h3>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full whitespace-nowrap hidden sm:inline-block">
                                Zero Investment
                            </span>
                        </div>

                        {{-- Dynamic Content Body --}}
                        <div class="partnership-rich reseller-rich text-gray-700 text-xs sm:text-sm leading-relaxed border-t border-gray-100 pt-4 flex-1">
                            {!! $resellerSection->content !!}
                        </div>
                    </div>

                    {{-- Bottom Action Area --}}
                    <div class="p-4 sm:p-6 bg-gradient-to-r from-indigo-50/40 via-white to-purple-50/20 border-t border-indigo-50 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap text-[11px] font-semibold text-indigo-900">
                            <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-indigo-100/80 shadow-2xs">
                                <i class="fas fa-check-circle text-indigo-600 text-[10px]"></i> Zero Investment
                            </span>
                            <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-indigo-100/80 shadow-2xs">
                                <i class="fas fa-check-circle text-indigo-600 text-[10px]"></i> Guaranteed Margin
                            </span>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ route('reseller.register') }}"
                               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl shadow-md shadow-indigo-600/20 hover:shadow-lg transition-all">
                                <span>Reseller Register</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('reseller.login') }}"
                               class="inline-flex items-center justify-center text-xs sm:text-sm font-bold text-indigo-700 hover:text-indigo-900 px-3 py-2 rounded-xl hover:bg-indigo-50 transition-colors">
                                Sign In
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

            {{-- ════ RIGHT: SUPPLIER CARDS (DYNAMIC FROM ADMIN PANEL) ════ --}}
            @if(isset($supplierContents) && $supplierContents->isNotEmpty())
                @foreach($supplierContents as $supplierSection)
                <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm hover:shadow-xl hover:border-emerald-200 transition-all duration-300 flex flex-col justify-between overflow-hidden group relative">
                    {{-- Top Accent Bar --}}
                    <div class="h-1 w-full bg-gradient-to-r from-emerald-500 via-teal-600 to-brand"></div>

                    <div class="p-6 sm:p-7 flex-1 flex flex-col">
                        {{-- Top Header Row --}}
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100/80 text-emerald-600 flex items-center justify-center text-xl shadow-2xs group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-200 flex-shrink-0">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div>
                                    <span class="text-[11px] font-black uppercase tracking-wider text-emerald-600 block">
                                        Supplier & Vendor
                                    </span>
                                    <h3 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight leading-snug">
                                        {{ $supplierSection->title }}
                                    </h3>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full whitespace-nowrap hidden sm:inline-block">
                                Nationwide Reach
                            </span>
                        </div>

                        {{-- Dynamic Content Body --}}
                        <div class="partnership-rich supplier-rich text-gray-700 text-xs sm:text-sm leading-relaxed border-t border-gray-100 pt-4 flex-1">
                            {!! $supplierSection->content !!}
                        </div>
                    </div>

                    {{-- Bottom Action Area --}}
                    <div class="p-4 sm:p-6 bg-gradient-to-r from-emerald-50/40 via-white to-teal-50/20 border-t border-emerald-50 flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap text-[11px] font-semibold text-emerald-900">
                            <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-100/80 shadow-2xs">
                                <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i> Nationwide Reach
                            </span>
                            <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-100/80 shadow-2xs">
                                <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i> Fast Payouts
                            </span>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <a href="{{ route('supplier.register') }}"
                               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all">
                                <span>Supplier Register</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                            <a href="{{ route('supplier.login') }}"
                               class="inline-flex items-center justify-center text-xs sm:text-sm font-bold text-emerald-700 hover:text-emerald-900 px-3 py-2 rounded-xl hover:bg-emerald-50 transition-colors">
                                Supplier Login
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif

        </div>
    </div>
</div>

<style>
/* ── Partnership Dynamic Content Typography System ── */
.partnership-rich {
    font-size: 0.8125rem;
    line-height: 1.65;
    color: #4b5563;
}
.partnership-rich p {
    margin-bottom: 0.65rem;
    color: #4b5563;
}
.partnership-rich > p:first-of-type {
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.6;
    margin-bottom: 0.85rem;
}
.partnership-rich h1,
.partnership-rich h2,
.partnership-rich h3,
.partnership-rich h4 {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.875rem;
    font-weight: 800;
    margin-top: 1rem;
    margin-bottom: 0.45rem;
    letter-spacing: -0.01em;
}
.reseller-rich h1, .reseller-rich h2, .reseller-rich h3, .reseller-rich h4 {
    color: #3730a3;
}
.reseller-rich h1::before, .reseller-rich h2::before, .reseller-rich h3::before, .reseller-rich h4::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 13px;
    border-radius: 9999px;
    background: #4f46e5;
    flex-shrink: 0;
}
.supplier-rich h1, .supplier-rich h2, .supplier-rich h3, .supplier-rich h4 {
    color: #065f46;
}
.supplier-rich h1::before, .supplier-rich h2::before, .supplier-rich h3::before, .supplier-rich h4::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 13px;
    border-radius: 9999px;
    background: #059669;
    flex-shrink: 0;
}
/* Bullet lists: Modern checkmark chips grid */
.partnership-rich ul {
    list-style: none !important;
    padding-left: 0 !important;
    margin: 0.5rem 0 0.85rem 0 !important;
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.4rem;
}
@media (min-width: 640px) {
    .partnership-rich ul {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.4rem 0.65rem;
    }
}
.partnership-rich ul li {
    position: relative;
    padding: 0.4rem 0.6rem 0.4rem 1.6rem;
    background-color: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #374151;
    line-height: 1.35;
    transition: all 0.2s ease;
}
.partnership-rich ul li:hover {
    background-color: #ffffff;
    border-color: #e5e7eb;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.reseller-rich ul li::before {
    content: '✓';
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    font-weight: 900;
    color: #4f46e5;
}
.supplier-rich ul li::before {
    content: '✓';
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    font-weight: 900;
    color: #059669;
}
/* Ordered / Numbered lists */
.partnership-rich ol {
    list-style: none !important;
    counter-reset: p-counter;
    padding-left: 0 !important;
    margin: 0.5rem 0 0.85rem 0 !important;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.partnership-rich ol li {
    counter-increment: p-counter;
    position: relative;
    padding: 0.4rem 0.6rem 0.4rem 1.85rem;
    background-color: #f9fafb;
    border: 1px solid #f3f4f6;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    color: #374151;
}
.reseller-rich ol li::before {
    content: counter(p-counter);
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1rem;
    height: 1rem;
    border-radius: 9999px;
    background-color: #e0e7ff;
    color: #4338ca;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
}
.supplier-rich ol li::before {
    content: counter(p-counter);
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1rem;
    height: 1rem;
    border-radius: 9999px;
    background-color: #d1fae5;
    color: #047857;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 800;
}
.reseller-rich strong { color: #3730a3; font-weight: 700; }
.supplier-rich strong { color: #065f46; font-weight: 700; }
.partnership-rich a { color: #2563eb; text-decoration: underline; }
.partnership-rich a:hover { color: #1d4ed8; }
.partnership-rich table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; font-size: 11px; }
.partnership-rich table, .partnership-rich th, .partnership-rich td { border: 1px solid #e5e7eb; padding: 0.35rem 0.5rem; }
.partnership-rich th { background-color: #f9fafb; font-weight: 700; }
</style>
@endif

@endsection

@push('scripts')
<script>
  // Hero slider dot indicators & auto-rotation
  const heroTrack = document.getElementById('heroTrack');
  const heroDots = document.getElementById('heroDots');
  if (heroTrack && heroDots && heroTrack.children.length > 1) {
    const slideCount = heroTrack.children.length;

    for (let i = 0; i < slideCount; i++) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'h-1.5 rounded-full transition-all ' + (i === 0 ? 'w-6 bg-white' : 'w-1.5 bg-white/60');
      dot.onclick = () => {
        heroTrack.scrollTo({ left: heroTrack.clientWidth * i, behavior: 'smooth' });
      };
      heroDots.appendChild(dot);
    }

    function heroSyncDots() {
      const page = Math.round(heroTrack.scrollLeft / heroTrack.clientWidth);
      [...heroDots.children].forEach((d, i) => {
        d.className = 'h-1.5 rounded-full transition-all ' + (i === page ? 'w-6 bg-white' : 'w-1.5 bg-white/60');
      });
    }
    heroTrack.addEventListener('scroll', () => {
      clearTimeout(window._heroDotTimer);
      window._heroDotTimer = setTimeout(heroSyncDots, 80);
    });

    // Auto-rotate every 5 seconds, pausing on mouse hover
    let autoSlideInterval = setInterval(() => {
      const curPage = Math.round(heroTrack.scrollLeft / heroTrack.clientWidth);
      const nextPage = (curPage + 1) % slideCount;
      heroTrack.scrollTo({ left: heroTrack.clientWidth * nextPage, behavior: 'smooth' });
    }, 5000);

    heroTrack.parentElement.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
    heroTrack.parentElement.addEventListener('mouseleave', () => {
      clearInterval(autoSlideInterval);
      autoSlideInterval = setInterval(() => {
        const curPage = Math.round(heroTrack.scrollLeft / heroTrack.clientWidth);
        const nextPage = (curPage + 1) % slideCount;
        heroTrack.scrollTo({ left: heroTrack.clientWidth * nextPage, behavior: 'smooth' });
      }, 5000);
    });
  }
</script>
@endpush
