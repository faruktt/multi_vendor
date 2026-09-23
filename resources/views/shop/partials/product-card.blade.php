{{-- Expects: $product, $branch --}}
@php
    $isOnFlash = $product->is_on_flash_sale;
    $flashPrice = $product->flash_price;
    $regularPrice = (float) $product->price;
    $discountPct = $product->discount_percentage;
@endphp
<a href="{{ route('shop.products.show', $product->slug) }}"
   class="bg-white border border-slate-100 rounded-2xl overflow-hidden hover:shadow-md hover:border-amber-300 transition-all group flex flex-col relative">
    
    {{-- Flash Sale Badge or Discount Badge --}}
    @if($isOnFlash)
    <div class="absolute top-2 left-2 z-10 bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white text-[10px] font-black tracking-wider px-2.5 py-0.5 rounded-full shadow-md flex items-center gap-1">
        <i class="fas fa-bolt text-yellow-200 text-[10px] animate-pulse"></i> FLASH SALE
        @if($discountPct)
        <span class="bg-white/20 px-1 rounded text-[9px]">{{ round($discountPct) }}% OFF</span>
        @endif
    </div>
    @elseif($product->has_old_price && $product->old_price_discount_percentage > 0)
    <div class="absolute top-2 left-2 z-10 bg-rose-500 text-white text-[10px] font-bold tracking-wider px-2.5 py-0.5 rounded-full shadow-md flex items-center gap-1">
        {{ $product->old_price_discount_percentage }}% OFF
    </div>
    @endif

    <div class="aspect-square bg-gradient-to-br from-slate-50 to-blue-50 flex items-center justify-center overflow-hidden relative">
        @if($product->first_image_url)
        <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
        @else
        <i class="fas fa-box text-blue-200 text-3xl"></i>
        @endif
    </div>
    <div class="p-3 flex flex-col flex-1 justify-between">
        <div>
            @if($product->supplier)
            <p class="text-[10.5px] text-emerald-700 font-semibold truncate flex items-center gap-1 mb-1">
                <i class="fas fa-store text-[9px] text-emerald-600"></i> {{ $product->supplier->display_name }}
            </p>
            @elseif($product->category)
            <p class="text-[11px] text-slate-400 mb-1.5">{{ $product->category->name }}</p>
            @endif
        </div>
        <div class="flex items-center justify-between pt-1">
            <div>
                @if($isOnFlash)
                <div class="flex items-baseline gap-1.5">
                    <p class="font-black text-rose-600 text-sm">৳{{ number_format($flashPrice, 0) }}</p>
                    <p class="text-[11px] text-slate-400 line-through">৳{{ number_format($regularPrice, 0) }}</p>
                </div>
                @elseif($product->has_old_price)
                <div class="flex items-baseline gap-1.5">
                    <p class="font-bold text-blue-600 text-sm">৳{{ number_format($product->price, 0) }}</p>
                    <p class="text-[11px] text-slate-400 line-through">৳{{ number_format($product->old_price, 0) }}</p>
                </div>
                @else
                <p class="font-bold text-blue-600 text-sm">৳{{ number_format($product->price, 0) }}</p>
                @endif
            </div>
            @if($product->stock_qty <= 0)
            <span class="text-[10px] font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded-md">Out</span>
            @endif
        </div>
    </div>
</a>
