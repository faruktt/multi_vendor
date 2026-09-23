@extends('shop.layout')
@section('title', ($branch->system_name ?? $branch->name) . ' — Online Store')
@section('main-class', 'w-full')

@section('content')

<!-- ══ ROW 1: Hero Banner (Main Slider + Promo Card) ══ -->
<div class="max-w-[1440px] mx-auto px-4 py-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">

        {{-- ══ Hero row: Main slider and side promo banner expand across full width ══ --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1.85fr_1fr] gap-3.5">

            {{-- LEFT: hero slider if banners uploaded, else the original brand/stats tile --}}
            @if($heroBanners->isNotEmpty())
            <div class="relative rounded-xl overflow-hidden border border-gray-200 min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px] bg-gray-50 group">
                <div id="heroTrack" class="flex overflow-x-auto scroll-smooth snap-x snap-mandatory h-full min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px]">
                    @foreach($heroBanners as $banner)
                    @if($banner->link)
                    <a href="{{ $banner->link }}" class="snap-start flex-shrink-0 w-full min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px] block">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-full object-cover">
                    </a>
                    @else
                    <div class="snap-start flex-shrink-0 w-full min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px]">
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
            <div class="relative rounded-xl border border-gray-200 bg-gradient-to-br from-brand-light to-[#F5F8EF] min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px] overflow-hidden px-8 py-6 flex items-center gap-4">
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
            <a href="{{ $promo1Banner->link }}" class="block rounded-xl overflow-hidden border border-gray-200 bg-gray-50 min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px]">
                <img src="{{ $promo1Banner->image_url }}" alt="{{ $promo1Banner->title ?? 'Promo' }}" class="w-full h-full object-cover">
            </a>
            @else
            <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50 min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px]">
                <img src="{{ $promo1Banner->image_url }}" alt="{{ $promo1Banner->title ?? 'Promo' }}" class="w-full h-full object-cover">
            </div>
            @endif
            @else
            <div class="relative rounded-xl min-h-[240px] sm:min-h-[280px] md:min-h-[320px] lg:min-h-[350px] p-6 overflow-hidden bg-gradient-to-br from-brand to-brand-dark flex flex-col justify-center gap-4 text-white">
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

{{-- ══ Homepage Rich Content Section: Reseller & Supplier Cards (Side-by-Side Flex/Grid) ══ --}}
@if((isset($resellerContents) && $resellerContents->isNotEmpty()) || (isset($supplierContents) && $supplierContents->isNotEmpty()) || (isset($homeContents) && $homeContents->isNotEmpty()))
<div class="max-w-[1440px] mx-auto px-4 mt-10 mb-14">

    {{-- Section Header --}}
    <div class="text-center max-w-2xl mx-auto mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-light text-brand-dark text-xs font-black uppercase tracking-wider mb-2">
            <i class="fas fa-handshake-angle text-brand"></i> Partnership Opportunities
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
            আমাদের সাথে ব্যবসা শুরু করুন
        </h2>
        <p class="text-gray-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
            রিসেলার হিসেবে শূন্য বিনিয়োগে ব্যবসা শুরু করুন অথবা বিশ্বস্ত সাপ্লায়ার হয়ে আপনার পণ্যের পরিধি দেশব্যাপী ছড়িয়ে দিন।
        </p>
    </div>

    {{-- 2-Column Side-by-Side Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-stretch">

        {{-- ════ LEFT: RESELLER CARD ════ --}}
        @if(isset($resellerContents) && $resellerContents->isNotEmpty())
            @foreach($resellerContents as $resellerSection)
            <div class="rounded-3xl border border-indigo-100/80 bg-white shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group relative">
                {{-- Top Gradient Accent Bar --}}
                <div class="h-2 w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500"></div>

                <div class="p-6 sm:p-8 flex-1">
                    {{-- Header with Badge & Icon --}}
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white flex items-center justify-center text-lg shadow-md shadow-indigo-200 flex-shrink-0 group-hover:scale-105 transition-transform">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div>
                                <span class="inline-block text-[11px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-50 border border-indigo-200/60 px-2.5 py-0.5 rounded-full mb-1">
                                    Reseller Program
                                </span>
                                @if(!empty($resellerSection->title))
                                <h3 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight leading-snug">
                                    {{ $resellerSection->title }}
                                </h3>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Body Content --}}
                    <div class="home-rich-content reseller-rich text-gray-700 text-xs sm:text-sm leading-relaxed border-t border-gray-100 pt-4">
                        {!! $resellerSection->content !!}
                    </div>
                </div>

                {{-- Bottom Action Area --}}
                <div class="p-5 sm:p-6 bg-gradient-to-br from-indigo-50/50 via-white to-purple-50/30 border-t border-indigo-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[11px] font-bold text-indigo-900 flex-wrap">
                        <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-indigo-100 shadow-2xs">
                            <i class="fas fa-check-circle text-indigo-600 text-[10px]"></i> ০ বিনিয়োগ
                        </span>
                        <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-indigo-100 shadow-2xs">
                            <i class="fas fa-check-circle text-indigo-600 text-[10px]"></i> নিশ্চিত লাভ
                        </span>
                        <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-indigo-100 shadow-2xs">
                            <i class="fas fa-check-circle text-indigo-600 text-[10px]"></i> সহজ প্রসেস
                        </span>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto flex-shrink-0">
                        <a href="{{ route('reseller.register') }}"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md shadow-indigo-200 transition-all hover:shadow-lg hover:scale-102">
                            <span>রিসেলার রেজিস্ট্রেশন</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                        <a href="{{ route('reseller.login') }}"
                           class="inline-flex items-center justify-center text-xs font-bold text-indigo-700 hover:text-indigo-900 px-3 py-2 rounded-xl hover:bg-indigo-100/50 transition-colors">
                            লগইন
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

        {{-- ════ RIGHT: SUPPLIER CARD ════ --}}
        @if(isset($supplierContents) && $supplierContents->isNotEmpty())
            @foreach($supplierContents as $supplierSection)
            <div class="rounded-3xl border border-emerald-100/80 bg-white shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group relative">
                {{-- Top Gradient Accent Bar --}}
                <div class="h-2 w-full bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-500"></div>

                <div class="p-6 sm:p-8 flex-1">
                    {{-- Header with Badge & Icon --}}
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white flex items-center justify-center text-lg shadow-md shadow-emerald-200 flex-shrink-0 group-hover:scale-105 transition-transform">
                                <i class="fas fa-store"></i>
                            </div>
                            <div>
                                <span class="inline-block text-[11px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2.5 py-0.5 rounded-full mb-1">
                                    Supplier & Vendor
                                </span>
                                @if(!empty($supplierSection->title))
                                <h3 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight leading-snug">
                                    {{ $supplierSection->title }}
                                </h3>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Body Content --}}
                    <div class="home-rich-content supplier-rich text-gray-700 text-xs sm:text-sm leading-relaxed border-t border-gray-100 pt-4">
                        {!! $supplierSection->content !!}
                    </div>
                </div>

                {{-- Bottom Action Area --}}
                <div class="p-5 sm:p-6 bg-gradient-to-br from-emerald-50/50 via-white to-teal-50/30 border-t border-emerald-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[11px] font-bold text-emerald-900 flex-wrap">
                        <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-100 shadow-2xs">
                            <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i> দেশব্যাপী সেল
                        </span>
                        <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-100 shadow-2xs">
                            <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i> দ্রুত পেমেন্ট
                        </span>
                        <span class="inline-flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-emerald-100 shadow-2xs">
                            <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i> অটো স্টক
                        </span>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto flex-shrink-0">
                        <a href="{{ route('supplier.register') }}"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md shadow-emerald-200 transition-all hover:shadow-lg hover:scale-102">
                            <span>ভেন্ডর রেজিস্ট্রেশন</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                        <a href="{{ route('supplier.login') }}"
                           class="inline-flex items-center justify-center text-xs font-bold text-emerald-700 hover:text-emerald-900 px-3 py-2 rounded-xl hover:bg-emerald-100/50 transition-colors">
                            পোর্টাল লগইন
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

    </div>
</div>

<style>
.home-rich-content h1 { font-size: 1.4rem; font-weight: 800; margin-top: 0.9rem; margin-bottom: 0.4rem; color: #111827; line-height: 1.3; }
.home-rich-content h2 { font-size: 1.25rem; font-weight: 700; margin-top: 0.9rem; margin-bottom: 0.4rem; color: #111827; line-height: 1.3; }
.home-rich-content h3 { font-size: 1.1rem; font-weight: 700; margin-top: 0.75rem; margin-bottom: 0.35rem; color: #1f2937; }
.home-rich-content h4, .home-rich-content h5, .home-rich-content h6 { font-weight: 700; margin-top: 0.5rem; margin-bottom: 0.25rem; color: #374151; }
.home-rich-content p { margin-bottom: 0.65rem; }
.home-rich-content ul { list-style-type: disc !important; margin-left: 1.25rem !important; margin-bottom: 0.65rem; }
.home-rich-content ol { list-style-type: decimal !important; margin-left: 1.25rem !important; margin-bottom: 0.65rem; }
.home-rich-content li { margin-bottom: 0.3rem; }
.home-rich-content a { color: #0284c7; text-decoration: underline; }
.home-rich-content a:hover { color: #0369a1; }
.home-rich-content table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; font-size: 12px; }
.home-rich-content table, .home-rich-content th, .home-rich-content td { border: 1px solid #e5e7eb; }
.home-rich-content th, .home-rich-content td { padding: 0.4rem 0.6rem; }
.home-rich-content th { background-color: #f9fafb; font-weight: 700; }
.home-rich-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.5rem 0; }
.home-rich-content blockquote { border-left: 4px solid #cbd5e1; padding-left: 0.75rem; color: #64748b; font-style: italic; margin: 0.5rem 0; }

.reseller-rich strong { color: #4338ca; }
.supplier-rich strong { color: #047857; }
</style>
@endif

@endsection

@push('scripts')
<script>
  // Hero slider dot indicators (one dot per slide, since it shows 1-at-a-time)
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
  }
</script>
@endpush
