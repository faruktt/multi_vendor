@php
    $currency = '৳';
    $images = $product->image_urls ?? [];
    $firstImg = $images[0] ?? $product->first_image_url;
    $out = $product->stock_qty <= 0;

    $productFlashPrice = $product->is_on_flash_sale ? (float) $product->flash_price : null;
    $productRegularPrice = (float) $product->price;

    $variants = $product->variants ?? collect();
    $hasVariants = $variants->isNotEmpty();

    $variantsData = $variants->map(function($v) use ($productFlashPrice, $productRegularPrice) {
        $variantRegularPrice = (float) ($v->price > 0 ? $v->price : $productRegularPrice);
        $variantFlashPrice = null;
        if ($productFlashPrice !== null && $productRegularPrice > 0) {
            $discountRatio = $productFlashPrice / $productRegularPrice;
            $variantFlashPrice = round($variantRegularPrice * $discountRatio, 0);
        }
        return [
            'id'          => $v->id,
            'name'        => $v->variant_name,
            'color_id'    => $v->color_id,
            'size_id'     => $v->size_id,
            'color_label' => $v->color_label ?? $v->color?->name,
            'size_label'  => $v->size_label ?? $v->size?->name ?? $v->size?->code ?? $v->variant_name,
            'color_hex'   => $v->color?->hex_code,
            'price'       => $variantRegularPrice,
            'flash_price' => $variantFlashPrice,
            'stock'       => (int) $v->stock_qty,
        ];
    });

    $variantColors = $variants
        ->filter(fn($v) => $v->color_id || $v->color_label)
        ->unique(fn($v) => $v->color_id ?? $v->color_label)
        ->map(fn($v) => [
            'id'    => $v->color_id ?? $v->color_label,
            'label' => $v->color_label ?? $v->color?->name ?? 'Color',
            'hex'   => $v->color?->hex_code ?? null,
        ])
        ->values();

    $variantSizes = $variants
        ->filter(fn($v) => $v->size_id || $v->size_label || $v->variant_name)
        ->unique(fn($v) => $v->size_id ?? $v->size_label ?? $v->variant_name)
        ->map(fn($v) => [
            'id'    => $v->size_id ?? $v->id,
            'label' => $v->size_label ?? $v->size?->name ?? $v->size?->code ?? $v->variant_name ?? 'Size',
        ])
        ->values();

    $hasColors = $variantColors->isNotEmpty();
    $hasSizes  = $variantSizes->isNotEmpty();

    // Subtitle / tags
    $tags = [];
    if (!empty($product->category?->name)) $tags[] = $product->category->name;
    if (!empty($product->subCategory?->name)) $tags[] = $product->subCategory->name;
    $tags[] = 'NEW ARRIVAL';
    if (!empty($product->supplier?->display_name)) $tags[] = $product->supplier->display_name;
    else $tags[] = 'WOMAN';
@endphp

<div class="w-full"
     x-init="if (typeof window.trackViewContent === 'function') { window.trackViewContent({ id: '{{ $product->id }}', name: @js($product->name), price: currentPrice, category: @js($product->category?->name ?? '') }); }"
     x-data="{
        qty: 1,
        activeImg: '{{ $firstImg }}',
        hasVariants: {{ $hasVariants ? 'true' : 'false' }},
        hasColors: {{ $hasColors ? 'true' : 'false' }},
        hasSizes: {{ $hasSizes ? 'true' : 'false' }},
        variants: @js($variantsData),
        variantColors: @js($variantColors),
        variantSizes: @js($variantSizes),

        // Start with NULL — DO NOT AUTO-SELECT
        selectedColorId: null,
        selectedSizeId: null,
        selectedVariantId: null,

        basePrice: {{ (float) $product->price }},
        flashPrice: {{ $product->is_on_flash_sale ? (float) $product->flash_price : 0 }},
        baseStock: {{ (int) $product->stock_qty }},
        isAdding: false,
        validationMsg: '',
        shakeSignal: false,

        get matchedVariant() {
            if (!this.hasVariants || !this.variants.length) return null;
            if (this.selectedVariantId && (!this.hasColors && !this.hasSizes)) {
                return this.variants.find(v => v.id === this.selectedVariantId) || null;
            }
            if (this.hasColors || this.hasSizes) {
                return this.variants.find(v => {
                    const colorMatch = !this.hasColors || (v.color_id === this.selectedColorId || v.color_label === this.selectedColorId);
                    const sizeMatch  = !this.hasSizes  || (v.size_id === this.selectedSizeId  || v.id === this.selectedSizeId);
                    return colorMatch && sizeMatch;
                }) || null;
            }
            return null;
        },

        get effectiveVariantId() {
            if (this.matchedVariant) return this.matchedVariant.id;
            if (this.selectedVariantId) return this.selectedVariantId;
            return null;
        },

        get currentPrice() {
            if (this.matchedVariant) {
                return this.matchedVariant.flash_price || this.matchedVariant.price;
            }
            if (this.flashPrice > 0) return this.flashPrice;
            return this.basePrice;
        },

        get currentStock() {
            if (!this.hasVariants) return this.baseStock;
            if (this.matchedVariant) return Number(this.matchedVariant.stock) || 0;
            return this.baseStock;
        },

        get selectedColorLabel() {
            const c = this.variantColors.find(c => c.id === this.selectedColorId);
            return c ? c.label : '';
        },

        get selectedSizeLabel() {
            const s = this.variantSizes.find(s => s.id === this.selectedSizeId);
            return s ? s.label : '';
        },

        isSizeInStock(sizeId) {
            if (!this.variants.length) return true;
            const matches = this.variants.filter(v => {
                const sizeMatch = (v.size_id === sizeId || v.id === sizeId);
                const colorMatch = !this.hasColors || !this.selectedColorId || (v.color_id === this.selectedColorId || v.color_label === this.selectedColorId);
                return sizeMatch && colorMatch;
            });
            if (!matches.length) return true;
            return matches.some(v => (Number(v.stock) || 0) > 0);
        },

        selectSize(sizeId) {
            this.selectedSizeId = sizeId;
            this.validationMsg = '';
            this.shakeSignal = false;
            if (!this.hasColors) {
                const v = this.variants.find(item => item.size_id === sizeId || item.id === sizeId);
                if (v) this.selectedVariantId = v.id;
            }
        },

        selectColor(colorId) {
            this.selectedColorId = colorId;
            this.validationMsg = '';
            this.shakeSignal = false;
            if (!this.hasSizes) {
                const v = this.variants.find(item => item.color_id === colorId || item.color_label === colorId);
                if (v) this.selectedVariantId = v.id;
            }
        },

        validate() {
            this.validationMsg = '';
            if (this.hasVariants) {
                if (this.hasColors && this.hasSizes && (!this.selectedColorId || !this.selectedSizeId)) {
                    if (!this.selectedColorId && !this.selectedSizeId) {
                        this.validationMsg = 'অনুগ্রহ করে Color এবং Size সিলেক্ট করুন।';
                    } else if (!this.selectedColorId) {
                        this.validationMsg = 'অনুগ্রহ করে Color সিলেক্ট করুন।';
                    } else {
                        this.validationMsg = 'অনুগ্রহ করে Size সিলেক্ট করুন।';
                    }
                    this.shakeSignal = true;
                    setTimeout(() => { this.shakeSignal = false; }, 800);
                    setTimeout(() => { this.validationMsg = ''; }, 3500);
                    return false;
                }
                if (this.hasColors && !this.selectedColorId) {
                    this.validationMsg = 'অনুগ্রহ করে Color সিলেক্ট করুন।';
                    this.shakeSignal = true;
                    setTimeout(() => { this.shakeSignal = false; }, 800);
                    setTimeout(() => { this.validationMsg = ''; }, 3500);
                    return false;
                }
                if (this.hasSizes && !this.selectedSizeId) {
                    this.validationMsg = 'অনুগ্রহ করে Size সিলেক্ট করুন।';
                    this.shakeSignal = true;
                    setTimeout(() => { this.shakeSignal = false; }, 800);
                    setTimeout(() => { this.validationMsg = ''; }, 3500);
                    return false;
                }
                if (!this.effectiveVariantId) {
                    this.validationMsg = 'অনুগ্রহ করে অপশন সিলেক্ট করুন।';
                    this.shakeSignal = true;
                    setTimeout(() => { this.shakeSignal = false; }, 800);
                    setTimeout(() => { this.validationMsg = ''; }, 3500);
                    return false;
                }
            }
            return true;
        },

        addToCart() {
            if (!this.validate()) return;
            if (this.isAdding || this.currentStock <= 0) return;
            this.isAdding = true;
            window.shopQuickAddToCart({{ $product->id }}, this.effectiveVariantId, this.qty)
                .finally(() => {
                    this.isAdding = false;
                });
        }
     }">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 items-start">

        {{-- Left: Gallery — 1 main image + thumbnail strip --}}
        <div class="space-y-3">
            {{-- Main Image --}}
            <div class="aspect-[4/5] bg-white rounded-lg overflow-hidden flex items-center justify-center border border-gray-100">
                <img :src="activeImg" alt="{{ $product->name }}"
                     class="w-full h-full object-contain transition-all duration-300">
            </div>

            {{-- Thumbnail strip (show if more than 1 image) --}}
            @if(count($images) > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                @foreach($images as $img)
                <button type="button"
                        @click="activeImg = '{{ $img }}'"
                        class="w-14 h-16 flex-shrink-0 rounded-md bg-[#F8F9FA] border-2 overflow-hidden flex items-center justify-center p-1 transition-colors cursor-pointer"
                        :class="activeImg === '{{ $img }}' ? 'border-gray-900' : 'border-gray-200 hover:border-gray-500'">
                    <img src="{{ $img }}" alt="{{ $product->name }}" class="max-w-full max-h-full object-contain">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right: Product Details --}}
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

            {{-- Color Selector (if product has colors) --}}
            @if($hasColors)
            <div class="mt-4 transition-all" :class="{ 'ring-2 ring-rose-500 ring-offset-2 p-1.5 rounded-lg bg-rose-50/40': shakeSignal && hasColors && !selectedColorId }">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                        COLOR <span class="text-rose-500 font-semibold" x-text="selectedColorLabel ? '— ' + selectedColorLabel : ''"></span>
                    </div>
                    <template x-if="hasColors && !selectedColorId">
                        <span class="text-[11px] font-semibold text-rose-500 animate-pulse">Select Color</span>
                    </template>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="c in variantColors" :key="c.id">
                        <button type="button"
                                @click="selectColor(c.id)"
                                class="min-w-[42px] h-9 px-3 rounded-md text-xs font-bold border transition-all flex items-center justify-center gap-1.5 uppercase cursor-pointer"
                                :class="selectedColorId === c.id ? 'border-gray-900 bg-gray-900 text-white shadow-sm' : 'border-gray-300 text-gray-800 hover:border-gray-500 bg-white'">
                            <template x-if="c.hex">
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 inline-block" :style="'background-color:' + c.hex"></span>
                            </template>
                            <span x-text="c.label"></span>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            {{-- Size Selector (if product has sizes) --}}
            @if($hasSizes)
            <div class="mt-4 transition-all" :class="{ 'ring-2 ring-rose-500 ring-offset-2 p-1.5 rounded-lg bg-rose-50/40': shakeSignal && hasSizes && !selectedSizeId }">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                        SIZE <span class="text-rose-500 font-semibold" x-text="selectedSizeLabel ? '— ' + selectedSizeLabel : ''"></span>
                    </div>
                    <template x-if="hasSizes && !selectedSizeId">
                        <span class="text-[11px] font-semibold text-rose-500 animate-pulse">Select Size</span>
                    </template>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="s in variantSizes" :key="s.id">
                        <button type="button"
                                @click="selectSize(s.id)"
                                class="min-w-[42px] h-9 px-3 rounded-md text-xs font-bold border transition-all flex items-center justify-center uppercase cursor-pointer"
                                :class="[
                                    selectedSizeId === s.id ? 'border-gray-900 bg-gray-900 text-white shadow-sm' : 'border-gray-300 text-gray-800 hover:border-gray-500 bg-white',
                                    !isSizeInStock(s.id) ? 'opacity-40 line-through text-gray-400 bg-gray-50' : ''
                                ]">
                            <span x-text="s.label"></span>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            {{-- Actions: Quantity + Add to Cart + Buy Now --}}
            <div class="mt-6 flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                {{-- Quantity Selector [- 1 +] --}}
                <div class="flex items-center border border-gray-300 rounded-md bg-white h-11 px-1">
                    <button type="button" @click="if (qty > 1) qty--"
                            class="w-7 h-full flex items-center justify-center text-gray-500 hover:text-black font-bold text-base transition-colors cursor-pointer">−</button>
                    <span class="w-8 text-center text-xs font-bold text-gray-900 select-none" x-text="qty"></span>
                    <button type="button" @click="if (qty < currentStock) qty++"
                            class="w-7 h-full flex items-center justify-center text-gray-500 hover:text-black font-bold text-base transition-colors cursor-pointer">+</button>
                </div>

                {{-- Add to Cart Button (Coral / Red #FF5A5F) --}}
                <button type="button"
                        @click="addToCart()"
                        :disabled="currentStock <= 0 || isAdding"
                        class="flex-1 h-11 bg-[#FF5A5F] hover:bg-[#e6474c] active:scale-[0.99] disabled:opacity-40 text-white text-xs font-bold uppercase tracking-wider rounded-md transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <template x-if="isAdding">
                        <i class="fas fa-circle-notch fa-spin text-xs"></i>
                    </template>
                    <span x-text="currentStock <= 0 ? 'Out of Stock' : (isAdding ? 'Adding...' : 'ADD TO CART')">ADD TO CART</span>
                </button>

                {{-- Buy Now Button (Green #739E00) --}}
                <form method="POST" action="{{ route('shop.cart.add') }}" class="flex-1"
                      @submit.prevent="if(validate()) { $el.submit(); }">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" :value="effectiveVariantId">
                    <input type="hidden" name="qty" :value="qty">
                    <input type="hidden" name="redirect_to" value="checkout">
                    <button type="submit"
                            :disabled="currentStock <= 0"
                            class="w-full h-11 bg-[#739E00] hover:bg-[#608500] active:scale-[0.99] disabled:opacity-40 text-white text-xs font-bold uppercase tracking-wider rounded-md transition-all shadow-sm flex items-center justify-center cursor-pointer">
                        BUY NOW
                    </button>
                </form>
            </div>

            {{-- Validation Signal Alert --}}
            <template x-if="validationMsg">
                <div class="mt-3 flex items-center gap-2 bg-rose-50 border-2 border-rose-300 text-rose-700 text-xs font-bold px-3.5 py-2.5 rounded-lg shadow-xs animate-bounce">
                    <i class="fas fa-circle-exclamation text-rose-500 text-sm flex-shrink-0"></i>
                    <span x-text="validationMsg"></span>
                </div>
            </template>

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

            {{-- Social Share Icons --}}
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
