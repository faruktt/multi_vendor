@extends('shop.layout')
@section('title', $product->name . ' — ' . ($branch->system_name ?? $branch->name))
@section('og_description', Str::limit(strip_tags($product->short_description ?? $product->description ?? ''), 150))
@section('main-class', 'w-full')

@php
    $currency = $appSettings['currency'] ?? '৳';

    // Build variant data for JS — include color_id, size_id, labels, hex
    $variantsData = $product->variants->map(fn($v) => [
        'id'          => $v->id,
        'name'        => $v->variant_name,
        'color_id'    => $v->color_id,
        'size_id'     => $v->size_id,
        'color_label' => $v->color_label,
        'size_label'  => $v->size_label,
        'color_hex'   => $v->color?->hex_code,
        'price'       => (float) ($v->price ?? $product->price),
        'stock'       => $v->stock_qty,
    ]);

    // Unique colors and sizes from available variants
    $variantColors = $product->variants
        ->filter(fn($v) => $v->color_id)
        ->unique('color_id')
        ->map(fn($v) => ['id' => $v->color_id, 'label' => $v->color_label, 'hex' => $v->color?->hex_code])
        ->values();

    $variantSizes = $product->variants
        ->filter(fn($v) => $v->size_id)
        ->unique('size_id')
        ->map(fn($v) => ['id' => $v->size_id, 'label' => $v->size_label])
        ->values();

    $hasColorSize = $variantColors->isNotEmpty() || $variantSizes->isNotEmpty();
@endphp

@php
    $rawPhone = preg_replace('/\D/', '', $appSettings['phone'] ?? '');
    $whatsappNumber = $rawPhone ? '880' . ltrim($rawPhone, '0') : null;
    $whatsappMessage = "Hi, I'm interested in ordering: {$product->name} (" . url()->current() . ')';
@endphp

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<a href="{{ route('shop.products.index') }}" class="hover:text-brand-dark">Shop</a>
@if($product->category)
<span class="text-gray-300">/</span>
<a href="{{ route('shop.products.category', $product->category->slug) }}" class="hover:text-brand-dark">{{ $product->category->name }}</a>
@endif
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold truncate">{{ $product->name }}</span>
@endsection

@section('content')
<div class="max-w-[1440px] mx-auto px-4 py-4">

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
             x-data="productPage(
                @js($variantsData),
                {{ $product->stock_qty }},
                {{ $product->effective_price }},
                @js($variantColors),
                @js($variantSizes),
                @js($product->image_urls)
             )">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Image & Gallery Slider --}}
                <div class="flex flex-col gap-3">
                    <div class="relative aspect-square bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden flex items-center justify-center group">
                        <template x-if="stock <= 0">
                            <div class="absolute top-0 left-0 right-0 bg-red-500 text-white text-center text-[11px] font-extrabold tracking-wide py-1.5 z-10">OUT OF STOCK</div>
                        </template>

                        <template x-if="images.length > 0">
                            <img :src="currentImage" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </template>
                        <template x-if="images.length === 0">
                            <i class="fas fa-box text-gray-300 text-6xl"></i>
                        </template>

                        {{-- Slide buttons --}}
                        <template x-if="images.length > 1">
                            <div>
                                <button type="button" @click="prevImage()"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:text-brand-dark opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                                </button>
                                <button type="button" @click="nextImage()"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:text-brand-dark opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Small Thumbnails --}}
                    <template x-if="images.length > 1">
                        <div class="flex items-center gap-2 overflow-x-auto py-1">
                            <template x-for="(img, index) in images" :key="index">
                                <button type="button" @click="activeImageIndex = index"
                                        class="w-16 h-16 rounded-lg overflow-hidden border-2 flex-shrink-0 transition-colors"
                                        :class="activeImageIndex === index ? 'border-brand' : 'border-gray-200 hover:border-gray-300'">
                                    <img :src="img" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- Short Description (below image) --}}
                    @if($product->short_description)
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $product->short_description }}</p>
                    </div>
                    @endif
                </div>

                {{-- Details --}}
                <div>
                    @if($product->category)
                    <span class="inline-block text-[11px] font-bold text-brand-dark uppercase tracking-wide mb-2 bg-brand-light rounded-full px-2.5 py-1">{{ $product->category->name }}</span>
                    @endif
                    <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 mb-3 leading-snug">{{ $product->name }}</h1>

                    {{-- Flash Sale Deal Banner --}}
                    @if($product->is_on_flash_sale)
                    @php
                        $flashDeal = $product->active_flash_sale_record;
                        $savings = (float)$product->price - (float)$product->flash_price;
                    @endphp
                    <div class="mb-4 p-3.5 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white shadow-md flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-yellow-200 text-base flex-shrink-0">
                                <i class="fas fa-bolt animate-pulse"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <strong class="text-sm font-black tracking-wide uppercase">⚡ Flash Deal Active</strong>
                                    @if($product->discount_percentage)
                                    <span class="bg-yellow-300 text-orange-950 text-[10px] font-black px-1.5 py-0.5 rounded-md">{{ round($product->discount_percentage) }}% OFF</span>
                                    @endif
                                </div>
                                <p class="text-xs text-white/90">Special promotion price! Save {{ $currency }}{{ number_format($savings, 0) }} on this item.</p>
                            </div>
                        </div>
                        @if($flashDeal && $flashDeal->end_time)
                        <div class="bg-black/20 backdrop-blur-md px-3 py-1.5 rounded-xl text-center">
                            <p class="text-[9.5px] uppercase tracking-widest text-white/70 font-bold">Ends In</p>
                            <p class="text-xs font-black tracking-wider text-yellow-300">{{ $flashDeal->end_time->diffForHumans(['parts' => 2, 'short' => true]) }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-gray-100">
                        <div>
                            @if($product->is_on_flash_sale)
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-black text-rose-600">{{ $currency }}<span x-text="Number(price).toLocaleString()"></span></p>
                                <p class="text-base text-gray-400 font-semibold line-through">{{ $currency }}{{ number_format($product->price, 0) }}</p>
                            </div>
                            @elseif($product->has_old_price)
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <p class="text-3xl font-extrabold text-brand-dark">{{ $currency }}<span x-text="Number(price).toLocaleString()"></span></p>
                                <p class="text-base text-gray-400 font-semibold line-through">{{ $currency }}{{ number_format($product->old_price, 0) }}</p>
                                @if($product->old_price_discount_percentage > 0)
                                <span class="bg-rose-50 text-rose-600 border border-rose-200 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    {{ $product->old_price_discount_percentage }}% OFF
                                </span>
                                @endif
                            </div>
                            @else
                            <p class="text-3xl font-extrabold text-brand-dark">{{ $currency }}<span x-text="Number(price).toLocaleString()"></span></p>
                            @endif
                        </div>
                        <span class="inline-block text-[11px] font-bold text-gray-500 border border-gray-200 rounded-full px-2.5 py-1 bg-gray-50">/ {{ $product->unit }}</span>
                        {{-- Stock badge --}}
                         
                    </div>

                    {{-- Color Selector --}}
                    @if($variantColors->isNotEmpty())
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                            Color
                            <span class="normal-case font-semibold text-brand-dark ml-1" x-text="selectedColorLabel ? '— ' + selectedColorLabel : ''"></span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($variantColors as $c)
                            <button type="button"
                                    @click="selectColor({{ $c['id'] }})"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium border transition-all"
                                    :class="selectedColorId === {{ $c['id'] }} ? 'border-brand bg-brand-light text-brand-dark shadow-sm' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                                @if($c['hex'])
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 flex-shrink-0"
                                      style="background-color: {{ $c['hex'] }}"></span>
                                @endif
                                {{ $c['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Size Selector --}}
                    @if($variantSizes->isNotEmpty())
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                            Size
                            <span class="normal-case font-semibold text-brand-dark ml-1" x-text="selectedSizeLabel ? '— ' + selectedSizeLabel : ''"></span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($variantSizes as $s)
                            <button type="button"
                                    @click="selectSize({{ $s['id'] }})"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all"
                                    :class="[
                                        selectedSizeId === {{ $s['id'] }} ? 'border-brand bg-brand-light text-brand-dark shadow-sm' : 'border-gray-200 text-gray-600 hover:border-gray-300',
                                        !isSizeAvailable({{ $s['id'] }}) ? 'opacity-40 cursor-not-allowed' : ''
                                    ]"
                                    :disabled="!isSizeAvailable({{ $s['id'] }})">
                                {{ $s['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Fallback: old-style non-structured variants --}}
                    @if(!$hasColorSize && $product->variants->count() > 0)
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Select Option</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $v)
                            <button type="button" @click="variantId = {{ $v->id }}; qty = 1"
                                    class="px-3.5 py-2 rounded-xl text-sm font-medium border transition-colors"
                                    :class="variantId === {{ $v->id }} ? 'border-brand bg-brand-light text-brand-dark' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                                {{ $v->variant_name }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Quantity</label>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-0.5 bg-gray-50 border border-gray-200 rounded-xl p-1">
                                <button type="button" @click="qty = Math.max(1, qty - 1)"
                                        class="w-9 h-9 rounded-lg bg-white border border-gray-200 shadow-sm hover:bg-red-50 hover:text-red-500 hover:border-red-200 text-gray-500 font-bold flex items-center justify-center transition-colors">−</button>
                                <span class="w-10 text-center font-bold text-gray-700" x-text="qty"></span>
                                <button type="button" @click="qty = Math.min(stock, qty + 1)"
                                        class="w-9 h-9 rounded-lg bg-white border border-gray-200 shadow-sm hover:bg-brand-light hover:text-brand-dark hover:border-brand/40 text-gray-500 font-bold flex items-center justify-center transition-colors">+</button>
                            </div>
                            <p class="text-xs font-bold text-red-500 flex items-center gap-1" x-show="stock <= 0">
                                <i class="fas fa-circle-xmark text-[10px]"></i> Out of stock
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <form method="POST" action="{{ route('shop.cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" :value="variantId">
                            <input type="hidden" name="qty" :value="qty">
                            <button type="submit" :disabled="stock <= 0"
                                    class="w-full h-full bg-deal hover:bg-deal-dark disabled:opacity-40 disabled:cursor-not-allowed text-white py-3.5 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-bag-shopping text-xs"></i> Add to Cart
                            </button>
                        </form>

                        <form method="POST" action="{{ route('shop.cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="variant_id" :value="variantId">
                            <input type="hidden" name="qty" :value="qty">
                            <input type="hidden" name="redirect_to" value="checkout">
                            <button type="submit" :disabled="stock <= 0"
                                    class="w-full h-full bg-gray-900 hover:bg-black disabled:opacity-40 disabled:cursor-not-allowed text-white py-3.5 rounded-xl text-sm font-bold transition-colors">
                                Buy Now
                            </button>
                        </form>

                       {{-- @if($whatsappNumber)
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($whatsappMessage) }}" target="_blank" rel="noopener"
                           class="w-full bg-[#25D366] hover:bg-[#1fb958] text-white py-3.5 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                            <i class="fab fa-whatsapp text-base"></i> Order On WhatsApp
                        </a>
                        @endif

                        @if(!empty($appSettings['phone']))
                        <a href="tel:+{{ $whatsappNumber }}"
                           class="w-full bg-blue-900 hover:bg-blue-950 text-white py-3.5 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-phone text-xs"></i> Call For Order
                        </a>
                        @endif --}}
                    </div>

                    {{-- Supplier / Vendor Info Box --}}


                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5 pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-2.5 text-gray-500">
                            <span class="w-8 h-8 rounded-full bg-brand-light text-brand-dark flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-truck-fast text-xs"></i>
                            </span>
                            <span class="text-xs font-semibold">Fast Delivery</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-gray-500">
                            <span class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-hand-holding-dollar text-xs"></i>
                            </span>
                            <span class="text-xs font-semibold">Cash on Delivery</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-gray-500">
                            <span class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shield-halved text-xs"></i>
                            </span>
                            <span class="text-xs font-semibold">100% Genuine Product</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-5">
            @if(!empty($product->facebook_video_url))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                {{-- Left column: Product Details & Description --}}
                <div class="lg:col-span-7 xl:col-span-8 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-extrabold tracking-tight mb-3 text-gray-900 flex items-center gap-2">
                        <i class="fas fa-circle-info text-brand-dark text-base"></i>
                        <span>Product Details</span>
                    </h2>

                    @if($product->description)
                    <p class="text-sm text-gray-600 leading-relaxed mb-5 whitespace-pre-line">{{ $product->description }}</p>
                    @else
                    <p class="text-sm text-gray-400 italic mb-5">No additional details provided for this product.</p>
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-gray-100 pt-4">
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">SKU</p>
                            <p class="text-sm font-semibold text-gray-800 font-mono">{{ $product->sku ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Category</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $product->category->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Unit</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $product->unit ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Availability</p>
                            <p class="text-sm font-semibold {{ $product->stock_qty > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $product->stock_qty > 0 ? 'In Stock' : 'Out of Stock' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right column: Facebook Reel / Video --}}
                <div class="lg:col-span-5 xl:col-span-4 rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-sm shadow-xs flex-shrink-0">
                                <i class="fab fa-facebook-f"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 leading-none">Product Video / Reel</h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">Facebook Video</p>
                            </div>
                        </div>
                        <a href="{{ $product->facebook_video_url }}" target="_blank" rel="noopener noreferrer"
                           title="Open in Facebook"
                           class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-full transition-colors">
                            <span>Open</span>
                            <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                        </a>
                    </div>

                    {{-- Responsive Facebook Embed Frame --}}
                    <div class="relative w-full rounded-xl overflow-hidden bg-slate-900 aspect-[9/16] max-h-[520px] mx-auto flex items-center justify-center shadow-inner">
                        <iframe src="https://www.facebook.com/plugins/video.php?href={{ urlencode($product->facebook_video_url) }}&show_text=0&width=380"
                                class="w-full h-full border-0"
                                style="border:none;overflow:hidden"
                                scrolling="no"
                                frameborder="0"
                                allowfullscreen="true"
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                        </iframe>
                    </div>
                </div>
            </div>
            @else
            {{-- Standard full-width card when no Facebook video is provided --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-lg font-extrabold tracking-tight mb-3">Product Details</h2>

                @if($product->description)
                <p class="text-sm text-gray-600 leading-relaxed mb-5 whitespace-pre-line">{{ $product->description }}</p>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-gray-100 pt-4">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">SKU</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $product->sku ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Category</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $product->category->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Unit</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $product->unit ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1">Availability</p>
                        <p class="text-sm font-semibold {{ $product->stock_qty > 0 ? 'text-brand-dark' : 'text-red-500' }}">
                            {{ $product->stock_qty > 0 ? 'In Stock' : 'Out of Stock' }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if($related->count() > 0)
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm mt-5 mb-10">
            <h2 class="text-lg font-extrabold tracking-tight mb-3">You May Also Like</h2>
            <div class="relative">
                <button type="button" onclick="shelfScroll('related-track', -1)" class="flex absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" onclick="shelfScroll('related-track', 1)" class="flex absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-500 hover:text-brand-dark hover:shadow-lg transition-shadow">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>
                <div id="related-track" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1">
                    @foreach($related as $r)
                        @include('shop.partials.shelf-product-card', ['product' => $r])
                    @endforeach
                </div>
            </div>
        </div>
        @endif
</div>
@endsection

@push('scripts')
<script>
function productPage(variants, baseStock, basePrice, variantColors, variantSizes, imageUrls) {
    return {
        variants: variants,
        variantColors: variantColors,
        variantSizes: variantSizes,
        images: imageUrls || [],
        activeImageIndex: 0,
        qty: 1,

        // Selected color & size IDs
        selectedColorId: null,
        selectedSizeId: null,

        // Computed labels
        get selectedColorLabel() {
            const c = this.variantColors.find(c => c.id === this.selectedColorId);
            return c ? c.label : '';
        },
        get selectedSizeLabel() {
            const s = this.variantSizes.find(s => s.id === this.selectedSizeId);
            return s ? s.label : '';
        },

        // The matched variant (color + size), or null
        get matchedVariant() {
            if (!this.variants.length) return null;

            const hasColors = this.variantColors.length > 0;
            const hasSizes  = this.variantSizes.length  > 0;

            return this.variants.find(v => {
                const colorMatch = !hasColors || v.color_id === this.selectedColorId;
                const sizeMatch  = !hasSizes  || v.size_id  === this.selectedSizeId;
                return colorMatch && sizeMatch;
            }) || null;
        },

        // The variant_id to submit with the cart form
        get variantId() {
            return this.matchedVariant ? this.matchedVariant.id : null;
        },

        get stock() {
            if (!this.variants.length) return baseStock;
            return this.matchedVariant ? this.matchedVariant.stock : 0;
        },
        get price() {
            if (!this.variants.length) return basePrice;
            return this.matchedVariant ? this.matchedVariant.price : basePrice;
        },

        // Color selector (toggle)
        selectColor(colorId) {
            this.selectedColorId = this.selectedColorId === colorId ? null : colorId;
            this.qty = 1;
        },

        // Size selector (toggle)
        selectSize(sizeId) {
            this.selectedSizeId = this.selectedSizeId === sizeId ? null : sizeId;
            this.qty = 1;
        },

        // Is a size available for the currently selected color?
        isSizeAvailable(sizeId) {
            if (!this.variantColors.length) return true;
            if (!this.selectedColorId) return true;
            return this.variants.some(v => v.color_id === this.selectedColorId && v.size_id === sizeId);
        },

        get currentImage() {
            return this.images[this.activeImageIndex] || null;
        },
        prevImage() {
            this.activeImageIndex = (this.activeImageIndex - 1 + this.images.length) % this.images.length;
        },
        nextImage() {
            this.activeImageIndex = (this.activeImageIndex + 1) % this.images.length;
        }
    };
}
</script>
@endpush
