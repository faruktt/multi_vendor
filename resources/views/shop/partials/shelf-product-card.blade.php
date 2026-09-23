{{-- Expects: $product (with ->category loaded), $branch. Optional: $widthClass (default = 6-per-row carousel sizing) --}}
@php
    $out = $product->stock_qty <= 0;
    $currency = $appSettings['currency'] ?? '৳';
    $widthClass = $widthClass ?? 'snap-start flex-shrink-0 w-[calc((100%-5*16px)/6)] min-w-[150px]';
    $isOnFlash = $product->is_on_flash_sale;
@endphp
<div class="{{ $widthClass }} bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col relative"
     x-data="{ qty: 1, active: false, max: {{ $product->stock_qty }} }">

    {{-- Flash Sale Badge or Discount Badge --}}
    @if($isOnFlash)
    <div class="absolute top-2 left-2 z-20 bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white text-[9.5px] font-black tracking-wider px-2 py-0.5 rounded-full shadow-md flex items-center gap-1">
        <i class="fas fa-bolt text-yellow-200 text-[9px] animate-pulse"></i> FLASH SALE
    </div>
    @elseif($product->has_old_price && $product->old_price_discount_percentage > 0)
    <div class="absolute top-2 left-2 z-20 bg-rose-500 text-white text-[9.5px] font-bold tracking-wider px-2 py-0.5 rounded-full shadow-md flex items-center gap-1">
        {{ $product->old_price_discount_percentage }}% OFF
    </div>
    @endif

    <div class="card-art relative h-[220px] bg-gray-50 border-b border-gray-100">
        <a href="{{ route('shop.products.show', $product->slug) }}" class="absolute inset-0 flex items-center justify-center">
            @if($out)
            <div class="absolute top-0 left-0 right-0 bg-red-500 text-white text-center text-[9px] font-extrabold tracking-wide py-1 z-10">OUT OF STOCK</div>
            @endif
            @if($product->first_image_url)
            <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}"
                 class="w-full h-full object-cover {{ $out ? 'opacity-50' : '' }}">
            @else
            <i class="fas fa-box text-gray-300 text-3xl {{ $out ? 'opacity-50' : '' }}"></i>
            @endif
        </a>

        @if($out)
        <button type="button" disabled
                class="absolute -bottom-3.5 right-3 w-7 h-7 rounded-full bg-gray-300 text-white border-2 border-white shadow flex items-center justify-center">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        </button>
        @else
        <button type="button" x-show="!active" @click="active = true"
                class="absolute -bottom-3.5 right-3 w-7 h-7 rounded-full bg-brand text-white border-2 border-white shadow flex items-center justify-center">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        </button>

        <form x-show="active" x-cloak method="POST" action="{{ route('shop.cart.add') }}"
              class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 bg-brand rounded-full flex items-center shadow-lg overflow-hidden z-20">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="qty" :value="qty">
            <button type="button" @click="qty > 1 ? qty-- : (active = false, qty = 1)"
                    class="w-7 h-7 flex items-center justify-center text-white">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
            </button>
            <span class="min-w-[22px] text-center text-[12px] font-extrabold text-white" x-text="qty"></span>
            <button type="button" @click="qty = Math.min(qty + 1, max)"
                    class="w-7 h-7 flex items-center justify-center text-white">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            </button>
            <button type="submit" class="w-7 h-7 flex items-center justify-center text-white/90 hover:text-white border-l border-white/20" title="Add to cart">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </button>
        </form>
        @endif
    </div>

    <div class="pt-6 px-3 pb-3 flex flex-col gap-1.5 flex-1">
        <a href="{{ route('shop.products.show', $product->slug) }}" class="text-[12.5px] font-medium leading-tight text-gray-800 line-clamp-2 min-h-[32px] hover:text-brand-dark">{{ $product->name }}</a>
        <div>
            @if($isOnFlash)
            <div class="flex items-baseline gap-1.5">
                <span class="text-[16px] font-black text-rose-600">{{ $currency }} {{ number_format($product->flash_price, 0) }}</span>
                <span class="text-[11.5px] text-gray-400 line-through">{{ $currency }} {{ number_format($product->price, 0) }}</span>
            </div>
            @elseif($product->has_old_price)
            <div class="flex items-baseline gap-1.5">
                <span class="text-[15px] font-extrabold text-gray-900">{{ $currency }} {{ number_format($product->price, 0) }}</span>
                <span class="text-[11.5px] text-gray-400 line-through">{{ $currency }} {{ number_format($product->old_price, 0) }}</span>
            </div>
            @else
            <div class="text-[15px] font-extrabold text-gray-900">{{ $currency }} {{ number_format($product->price, 0) }}</div>
            @endif
        </div>
    </div>
</div>
