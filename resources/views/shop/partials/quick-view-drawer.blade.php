@php
    $currency = '৳';
    $images = $product->image_urls ?? [];
    $firstImg = $images[0] ?? $product->first_image_url;
    $secondImg = $images[1] ?? null;
    $out = $product->stock_qty <= 0;

    $variants = $product->variants ?? collect();
    $hasVariants = $variants->isNotEmpty();

    $variantsJson = $variants->map(function ($v) {
        return [
            'id' => $v->id,
            'name' => $v->size?->name ?? $v->size?->code ?? $v->variant_name ?? 'Default',
            'price' => (float) $v->price,
            'stock' => (int) $v->stock_qty,
        ];
    });

    // Subtitle / tags
    $tags = [];
    if (!empty($product->category?->name)) $tags[] = $product->category->name;
    $tags[] = 'NEW ARRIVAL';
    if (!empty($product->supplier?->display_name)) $tags[] = $product->supplier->display_name;
    else $tags[] = 'WOMAN';
@endphp

<div class="w-full"
     x-data="{
        qty: 1,
        activeImg: '{{ $firstImg }}',
        variants: @js($variantsJson),
        selectedVariantId: {{ $hasVariants ? ($variants->first()->id ?? 'null') : 'null' }},
        basePrice: {{ (float) $product->price }},
        flashPrice: {{ $product->is_on_flash_sale ? (float) $product->flash_price : 0 }},
        stock: {{ (int) $product->stock_qty }},
        isAdding: false,

        get currentPrice() {
            if (this.flashPrice > 0) return this.flashPrice;
            if (this.selectedVariantId && this.variants.length) {
                const v = this.variants.find(item => item.id === this.selectedVariantId);
                if (v && v.price > 0) return v.price;
            }
            return this.basePrice;
        },

        addToCart() {
            if (this.isAdding || this.stock <= 0) return;
            this.isAdding = true;
            window.shopQuickAddToCart({{ $product->id }}, this.selectedVariantId, this.qty)
                .finally(() => {
                    this.isAdding = false;
                });
        }
     }">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 items-start">

        {{-- Left: Gallery (Matches Screenshot 2: Side-by-side images or Large + Thumbs) --}}
        <div class="space-y-3">
            @if($secondImg)
            {{-- 2 Photos side by side just like Screenshot 2! --}}
            <div class="grid grid-cols-2 gap-2.5">
                <div class="aspect-[3/4] bg-[#F8F9FA] rounded-lg overflow-hidden flex items-center justify-center p-2 border border-gray-100 group">
                    <img src="{{ $firstImg }}" alt="{{ $product->name }}"
                         class="max-w-full max-h-full w-auto h-auto object-contain hover:scale-105 transition-transform duration-300 cursor-pointer"
                         @click="activeImg = '{{ $firstImg }}'">
                </div>
                <div class="aspect-[3/4] bg-[#F8F9FA] rounded-lg overflow-hidden flex items-center justify-center p-2 border border-gray-100 group">
                    <img src="{{ $secondImg }}" alt="{{ $product->name }}"
                         class="max-w-full max-h-full w-auto h-auto object-contain hover:scale-105 transition-transform duration-300 cursor-pointer"
                         @click="activeImg = '{{ $secondImg }}'">
                </div>
            </div>
            @else
            <div class="aspect-[4/5] bg-[#F8F9FA] rounded-lg overflow-hidden flex items-center justify-center p-3 border border-gray-100">
                <img :src="activeImg" alt="{{ $product->name }}"
                     class="max-w-full max-h-full w-auto h-auto object-contain transition-transform duration-300">
            </div>
            @endif

            {{-- Multiple Image Thumbnails if more than 2 --}}
            @if(count($images) > 2)
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($images as $img)
                <button type="button" @click="activeImg = '{{ $img }}'"
                        class="w-14 h-16 rounded-md bg-[#F8F9FA] border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0 p-1 hover:border-brand transition-colors">
                    <img src="{{ $img }}" class="max-w-full max-h-full object-contain">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right: Product Details (Matching Screenshot 2) --}}
        <div class="flex flex-col text-left">

            {{-- Title --}}
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight leading-snug">
                {{ $product->name }}
            </h1>

            {{-- Price --}}
            <div class="text-lg sm:text-xl font-bold text-gray-900 mt-2 flex items-center gap-2">
                <span x-text="Number(currentPrice).toLocaleString('en-US', {minimumFractionDigits: 2}) + '{{ $currency }}'">
                    {{ number_format($product->price, 2) }}{{ $currency }}
                </span>
                @if($product->is_on_flash_sale)
                <span class="text-xs text-gray-400 line-through">
                    {{ number_format($product->price, 2) }}{{ $currency }}
                </span>
                <span class="bg-[#FF5A5F] text-white text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">
                    SALE
                </span>
                @elseif($product->has_old_price)
                <span class="text-xs text-gray-400 line-through">
                    {{ number_format($product->old_price, 2) }}{{ $currency }}
                </span>
                @endif
            </div>

            {{-- Sizes / Variants --}}
            @if($hasVariants)
            <div class="mt-5">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">SIZE</div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="v in variants" :key="v.id">
                        <button type="button"
                                @click="selectedVariantId = v.id; if (v.stock > 0) stock = v.stock"
                                class="min-w-[42px] h-9 px-3 rounded-md text-xs font-bold border transition-all flex items-center justify-center uppercase"
                                :class="selectedVariantId === v.id ? 'border-gray-900 bg-gray-900 text-white shadow-sm' : 'border-gray-300 text-gray-800 hover:border-gray-500 bg-white'">
                            <span x-text="v.name"></span>
                        </button>
                    </template>
                </div>
            </div>
            @else
            {{-- Default Fashion Sizes preview for boutique items --}}
            <div class="mt-5">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">SIZE</div>
                <div class="flex flex-wrap gap-2">
                    @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $sz)
                    <button type="button"
                            class="min-w-[38px] h-8 px-2.5 rounded-md text-[11px] font-bold border border-gray-300 text-gray-700 hover:border-gray-900 transition-colors uppercase">
                        {{ $sz }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actions: Quantity + Add to Cart + Buy Now (Matching Screenshot 2) --}}
            <div class="mt-6 flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                {{-- Quantity Selector [- 1 +] --}}
                <div class="flex items-center border border-gray-300 rounded-md bg-white h-11 px-1">
                    <button type="button" @click="if (qty > 1) qty--"
                            class="w-7 h-full flex items-center justify-center text-gray-500 hover:text-black font-bold text-base transition-colors">−</button>
                    <span class="w-8 text-center text-xs font-bold text-gray-900 select-none" x-text="qty"></span>
                    <button type="button" @click="if (qty < stock) qty++"
                            class="w-7 h-full flex items-center justify-center text-gray-500 hover:text-black font-bold text-base transition-colors">+</button>
                </div>

                {{-- Add to Cart Button (Coral / Red #FF5A5F as in Screenshot 2) --}}
                <button type="button"
                        @click="addToCart()"
                        :disabled="stock <= 0 || isAdding"
                        class="flex-1 h-11 bg-[#FF5A5F] hover:bg-[#e6474c] active:scale-[0.99] disabled:opacity-40 text-white text-xs font-bold uppercase tracking-wider rounded-md transition-all shadow-sm flex items-center justify-center gap-2">
                    <template x-if="isAdding">
                        <i class="fas fa-circle-notch fa-spin text-xs"></i>
                    </template>
                    <span x-text="stock <= 0 ? 'Out of Stock' : (isAdding ? 'Adding...' : 'ADD TO CART')">ADD TO CART</span>
                </button>

                {{-- Buy Now Button (Green #739E00 as in Screenshot 2) --}}
                <form method="POST" action="{{ route('shop.cart.add') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" :value="selectedVariantId">
                    <input type="hidden" name="qty" :value="qty">
                    <input type="hidden" name="redirect_to" value="checkout">
                    <button type="submit"
                            :disabled="stock <= 0"
                            class="w-full h-11 bg-[#739E00] hover:bg-[#608500] active:scale-[0.99] disabled:opacity-40 text-white text-xs font-bold uppercase tracking-wider rounded-md transition-all shadow-sm flex items-center justify-center">
                        BUY NOW
                    </button>
                </form>
            </div>

            {{-- Meta info (SKU, Categories, Tags) --}}
            <div class="mt-6 pt-5 border-t border-gray-100 space-y-1.5 text-xs text-gray-600">
                <div>
                    <span class="font-bold text-gray-800">SKU:</span>
                    <span class="text-gray-500 ml-1">{{ $product->code ?? 'WKD-' . $product->id }}</span>
                </div>
                <div>
                    <span class="font-bold text-gray-800">Categories:</span>
                    <span class="text-gray-500 ml-1">{{ implode(', ', $tags) }}</span>
                </div>
                <div>
                    <span class="font-bold text-gray-800">Tags:</span>
                    <span class="text-gray-500 ml-1">{{ $product->name }}</span>
                </div>
            </div>

            {{-- Social Share Icons (Matching Screenshot 2) --}}
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-3 text-xs text-gray-500">
                <span class="font-bold text-gray-700 flex items-center gap-1.5">
                    <i class="fas fa-share-nodes text-xs"></i> Share
                </span>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('shop.products.show', $product->slug)) }}" target="_blank" rel="noopener"
                   class="w-7 h-7 rounded-full bg-gray-100 hover:bg-black hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-brands fa-x-twitter text-[11px]"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('shop.products.show', $product->slug)) }}" target="_blank" rel="noopener"
                   class="w-7 h-7 rounded-full bg-gray-100 hover:bg-[#1877F2] hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-brands fa-facebook-f text-[11px]"></i>
                </a>
                <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(route('shop.products.show', $product->slug)) }}" target="_blank" rel="noopener"
                   class="w-7 h-7 rounded-full bg-gray-100 hover:bg-[#E60023] hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-brands fa-pinterest-p text-[11px]"></i>
                </a>
                <a href="mailto:?subject={{ urlencode($product->name) }}&body={{ urlencode(route('shop.products.show', $product->slug)) }}"
                   class="w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-800 hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-regular fa-envelope text-[11px]"></i>
                </a>
            </div>

            {{-- Full page link --}}
            <a href="{{ route('shop.products.show', $product->slug) }}"
               class="mt-4 text-xs font-bold text-brand hover:underline inline-flex items-center gap-1">
                View Full Product Details &rarr;
            </a>

        </div>

    </div>

</div>
