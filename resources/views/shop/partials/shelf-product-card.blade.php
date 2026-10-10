{{-- Expects: $product (with ->category loaded), $branch. Optional: $widthClass --}}
@php
    $out = $product->stock_qty <= 0;
    $currency = '৳';
    // 6 items per row on desktop (lg/xl), matching reference screenshot
    $widthClass = $widthClass ?? 'snap-start flex-shrink-0 w-[calc((100%-16px)/2)] sm:w-[calc((100%-2*16px)/3)] md:w-[calc((100%-3*16px)/4)] lg:w-[calc((100%-5*16px)/6)] min-w-[140px]';
    $isOnFlash = $product->is_on_flash_sale;

    // Images (supports multi-image hover preview)
    $images = $product->image_urls ?? [];
    $firstImg = $images[0] ?? $product->first_image_url;
    $secondImg = $images[1] ?? null;

    // Subtitle / Tags line (e.g. "KURTI, NEW ARRIVAL, WOMAN")
    $tags = [];
    if (!empty($product->category?->name)) {
        $tags[] = $product->category->name;
    }
    $tags[] = 'NEW ARRIVAL';
    if (!empty($product->unit)) {
        $tags[] = $product->unit;
    } elseif (!empty($product->supplier?->display_name)) {
        $tags[] = $product->supplier->display_name;
    } else {
        $tags[] = 'WOMAN';
    }
    $tagsText = strtoupper(implode(', ', array_unique($tags)));

    // Pricing & Variants check
    $variantPrices = $product->relationLoaded('variants')
        ? $product->variants->pluck('price')->filter(fn($p) => (float)$p > 0)
        : collect();

    $hasVariantRange = false;
    $minVariantPrice = 0;
    $maxVariantPrice = 0;
    if ($variantPrices->isNotEmpty()) {
        $minVariantPrice = (float) $variantPrices->min();
        $maxVariantPrice = (float) $variantPrices->max();
        if ($minVariantPrice < $maxVariantPrice) {
            $hasVariantRange = true;
        }
    }
@endphp
<div class="{{ $widthClass }} group flex flex-col text-left transition-all"
     x-data="{ liked: false }"
     x-init="$nextTick(() => liked = window.shopIsInWishlist ? window.shopIsInWishlist({{ $product->id }}) : false)">

    {{-- Image Container (Compact 4:5 ratio, No Crop - Real Size Contain) --}}
    <div class="relative w-full aspect-[4/5] overflow-hidden bg-[#F8F9FA] rounded-sm flex items-center justify-center">
        <a href="{{ route('shop.products.show', $product->slug) }}" class="w-full h-full flex items-center justify-center p-1">
            @if($firstImg)
                <img src="{{ $firstImg }}" alt="{{ $product->name }}"
                     loading="lazy"
                     class="max-w-full max-h-full w-auto h-auto object-contain group-hover:scale-105 transition-transform duration-300 ease-out {{ $out ? 'opacity-50' : '' }}">
                @if($secondImg && !$out)
                <img src="{{ $secondImg }}" alt="{{ $product->name }}"
                     loading="lazy"
                     class="absolute inset-0 max-w-full max-h-full w-auto h-auto m-auto object-contain opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                @endif
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-300">
                    <i class="fas fa-box text-2xl"></i>
                </div>
            @endif
        </a>

        {{-- Flash / Discount Badge (Top Left) --}}
        @if($isOnFlash)
        <span class="absolute top-2 left-2 z-10 bg-[#FF5A5F] text-white text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full shadow-sm">
            SALE
        </span>
        @elseif($product->has_old_price && $product->old_price_discount_percentage > 0)
        <span class="absolute top-2 left-2 z-10 bg-[#FF5A5F] text-white text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full shadow-sm">
            -{{ round($product->old_price_discount_percentage) }}%
        </span>
        @endif

        {{-- Wishlist Heart Button (Top Right - Syncs with Header Counter & Drawer) --}}
        <button type="button"
                @click.prevent.stop="liked = window.shopToggleWishlist ? window.shopToggleWishlist({
                    id: {{ $product->id }},
                    name: @js($product->name),
                    slug: @js($product->slug),
                    price: @js(number_format($product->price, 2)),
                    image: @js($firstImg),
                    category: @js($product->category?->name ?? 'General'),
                    in_stock: {{ $out ? 0 : 1 }}
                }) : !liked"
                class="absolute top-2 right-2 text-gray-400 hover:text-rose-500 transition-colors z-20 p-1"
                title="Wishlist">
            <i :class="liked ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart text-gray-400'" class="text-sm"></i>
        </button>

        {{-- Out of Stock Overlay --}}
        @if($out)
        <div class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-10 pointer-events-none">
            <span class="bg-black text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-sm shadow">
                OUT OF STOCK
            </span>
        </div>
        @endif

        {{-- Floating Action Toolbar (Add to Cart, Quick View, Compare) - Matches Reference Image --}}
        @if(!$out)
        <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 z-20 opacity-0 translate-y-3 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200 pointer-events-none group-hover:pointer-events-auto">
            <div class="bg-white rounded-md shadow-lg border border-gray-100 flex items-center divide-x divide-gray-100 overflow-hidden">
                {{-- 1. Add to Cart --}}
                <button type="button"
                        @click.prevent.stop="window.shopQuickAddToCart({{ $product->id }})"
                        class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-gray-700 hover:text-brand hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        title="Add to Cart">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </button>

                {{-- 2. Quick View --}}
                <button type="button"
                        @click.prevent.stop="window.shopOpenQuickView('{{ $product->slug }}')"
                        class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-gray-700 hover:text-brand hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        title="Quick View">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>

                {{-- 3. Compare --}}
                <button type="button"
                        @click.prevent.stop="window.shopToggleCompare({
                            id: {{ $product->id }},
                            name: @js($product->name),
                            slug: @js($product->slug),
                            price: @js(number_format($product->price, 2)),
                            image: @js($firstImg),
                            category: @js($product->category?->name ?? 'General'),
                            in_stock: {{ $out ? 0 : 1 }}
                        })"
                        class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-gray-700 hover:text-brand hover:bg-gray-50 active:bg-gray-100 transition-colors"
                        title="Compare">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                    </svg>
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- Details (Matching Screenshot) --}}
    <div class="pt-1.5 pb-1 flex flex-col text-left">
        {{-- Tags line (e.g. "KURTI, NEW ARRIVAL, WOMAN") --}}
        <div class="text-[9.5px] sm:text-[10px] text-gray-400 uppercase tracking-wider font-normal truncate">
            {{ $tagsText }}
        </div>

        {{-- Product Name line (e.g. "RONG KURTI || WKD-8...") --}}
        <a href="{{ route('shop.products.show', $product->slug) }}"
           class="text-[12.5px] sm:text-[13px] font-bold text-gray-800 hover:text-black truncate leading-tight mt-1 transition-colors"
           title="{{ $product->name }}">
            {{ $product->name }}
        </a>

        {{-- Price line (e.g. "2,155.00৳") --}}
        <div class="text-[12.5px] sm:text-[13px] text-gray-800 font-normal leading-tight mt-1">
            @if($hasVariantRange)
                <span>{{ number_format($minVariantPrice, 2) }}৳ – {{ number_format($maxVariantPrice, 2) }}৳</span>
            @elseif($isOnFlash)
                <span class="text-rose-600 font-medium">{{ number_format($product->flash_price, 2) }}৳</span>
                <span class="text-gray-400 line-through text-[11px] ml-1">{{ number_format($product->price, 2) }}৳</span>
            @elseif($product->has_old_price)
                <span class="font-medium">{{ number_format($product->price, 2) }}৳</span>
                <span class="text-gray-400 line-through text-[11px] ml-1">{{ number_format($product->old_price, 2) }}৳</span>
            @else
                <span>{{ number_format($product->price, 2) }}৳</span>
            @endif
        </div>
    </div>

</div>
