@extends('reseller.layouts.app')
@section('title', $product->name . ' — Reseller Product Details')
@section('heading', 'Product Details')

@php
    $isOnFlash            = $product->isOnResellerFlashSale();
    $flashPrice           = $product->reseller_flash_price;
    $regularResellerPrice = (float) ($product->reseller_price ?? $product->price);
    $resellerPrice        = ($isOnFlash && $flashPrice !== null) ? $flashPrice : $regularResellerPrice;
    $retailPrice          = (float) $product->price;
    $hasMargin            = $resellerPrice < $retailPrice;
    $estProfit            = max(0, $retailPrice - $resellerPrice);
    $flashDeal            = $product->active_reseller_flash_sale_record;

    $variantsData = $product->variants->map(fn($v) => [
        'id'             => $v->id,
        'name'           => $v->variant_name,
        'reseller_price' => (float) (($isOnFlash && $flashPrice !== null) ? $flashPrice : ($product->reseller_price ?? ($v->price ?? $product->price))),
        'price'          => (float) ($v->price ?? $product->price),
        'stock'          => (int) $v->stock_qty,
    ]);

    $imageUrls = $product->image_urls;
    if (empty($imageUrls)) {
        $imageUrls = [];
    }
@endphp

@section('content')
<div class="py-4 space-y-6 max-w-6xl"
     x-data="resellerProductShow(@js($variantsData), {{ $product->stock_qty }}, {{ $resellerPrice }}, {{ $retailPrice }}, @js($imageUrls))">

    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="{{ route('reseller.dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
        <span>/</span>
        <a href="{{ route('reseller.products.index') }}" class="hover:text-indigo-600">Products</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('reseller.products.index', ['category_id' => $product->category_id]) }}" class="hover:text-indigo-600">
                {{ $product->category->name }}
            </a>
        @endif
        <span>/</span>
        <span class="text-slate-800 truncate font-bold max-w-[200px] sm:max-w-md">{{ $product->name }}</span>
    </nav>

    {{-- Main Product Card --}}
    <div class="bg-white rounded-3xl border {{ $isOnFlash ? 'border-amber-300 ring-2 ring-amber-200/60' : 'border-slate-200/80' }} shadow-sm p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Image & Gallery (Left Column) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="relative aspect-square rounded-2xl bg-slate-50 border border-slate-200/80 overflow-hidden flex items-center justify-center group">
                    <template x-if="images.length > 0">
                        <img :src="images[activeImageIndex]" :alt="`{{ $product->name }}`"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    </template>
                    <template x-if="images.length === 0">
                        <div class="flex flex-col items-center justify-center text-slate-300 gap-2">
                            <i class="fas fa-image text-5xl"></i>
                            <span class="text-xs">No image available</span>
                        </div>
                    </template>

                    {{-- Badges on Image --}}
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                        @if($isOnFlash)
                            <span class="bg-gradient-to-r from-amber-500 to-rose-600 text-white text-[11px] font-black px-3 py-1 rounded-lg shadow-md flex items-center gap-1.5 animate-pulse">
                                ⚡ FLASH SALE -{{ $product->reseller_discount_percentage }}%
                            </span>
                        @elseif($product->reseller_price && $product->reseller_price < $product->price)
                            <span class="bg-indigo-600 text-white text-[11px] font-black px-2.5 py-1 rounded-lg shadow-sm">
                                RESELLER PRICE
                            </span>
                        @endif
                        @if($product->stock_qty <= 0)
                            <span class="bg-red-600 text-white text-[11px] font-black px-2.5 py-1 rounded-lg shadow-sm">
                                OUT OF STOCK
                            </span>
                        @endif
                    </div>

                    {{-- Gallery Prev / Next Buttons --}}
                    <template x-if="images.length > 1">
                        <div>
                            <button type="button" @click="prevImage()"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 shadow-md border border-slate-200 text-slate-700 hover:text-indigo-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                            <button type="button" @click="nextImage()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 shadow-md border border-slate-200 text-slate-700 hover:text-indigo-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Thumbnails --}}
                <template x-if="images.length > 1">
                    <div class="flex items-center gap-2.5 overflow-x-auto py-1">
                        <template x-for="(img, idx) in images" :key="idx">
                            <button type="button" @click="activeImageIndex = idx"
                                    class="w-16 h-16 rounded-xl overflow-hidden border-2 flex-shrink-0 transition-all"
                                    :class="activeImageIndex === idx ? 'border-indigo-600 ring-2 ring-indigo-200' : 'border-slate-200 opacity-70 hover:opacity-100'">
                                <img :src="img" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </template>

                {{-- Branch & Vendor Info Card --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Fulfilled by Branch</div>
                            <div class="text-sm font-bold text-slate-800">{{ $product->vendor->name ?? 'Main Warehouse' }}</div>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                        Active Inventory
                    </span>
                </div>
            </div>

            {{-- Product Details (Right Column) --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- Reseller Flash Deal Announcement Banner --}}
                @if($isOnFlash)
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-rose-500 to-indigo-600 p-4 text-white shadow-md flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl font-black">
                            ⚡
                        </span>
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-yellow-200">
                                Exclusive Reseller Flash Deal
                            </span>
                            <p class="font-black text-sm md:text-base mt-0.5">Extra Discount: Save ৳{{ number_format(max(0, $regularResellerPrice - $flashPrice), 0) }} off regular wholesale price!</p>
                        </div>
                    </div>
                    @if($flashDeal && $flashDeal->end_time)
                    <div class="text-right flex-shrink-0 bg-white/15 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20 hidden sm:block">
                        <span class="text-[10px] uppercase font-bold text-white/80 block">Deal Ends</span>
                        <span class="text-xs font-black text-yellow-200">{{ $flashDeal->end_time->diffForHumans(['parts' => 2, 'short' => true]) }}</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Header & Title --}}
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        @if($product->category)
                            <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                                {{ $product->category->name }}
                            </span>
                        @endif
                        @if($product->barcode || $product->sku)
                            <span class="text-xs font-mono font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-md">
                                SKU: {{ $product->sku ?? $product->barcode }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl lg:text-3xl font-black text-slate-800 tracking-tight leading-snug">
                        {{ $product->name }}
                    </h1>
                </div>

                {{-- Pricing Box --}}
                <div class="bg-gradient-to-br from-indigo-50/70 via-white to-slate-50 rounded-2xl border {{ $isOnFlash ? 'border-amber-300' : 'border-indigo-100' }} p-5 space-y-3">
                    <div class="flex flex-wrap items-baseline justify-between gap-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider {{ $isOnFlash ? 'text-rose-600' : 'text-indigo-600' }} block mb-1 flex items-center gap-1">
                                <i class="fas {{ $isOnFlash ? 'fa-bolt text-amber-500' : 'fa-tag' }}"></i>
                                {{ $isOnFlash ? 'Flash Reseller Buy Price' : 'Your Reseller Buy Price' }}
                            </span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl lg:text-4xl font-black {{ $isOnFlash ? 'text-rose-600' : 'text-indigo-700' }}">
                                    ৳<span x-text="Number(currentResellerPrice).toLocaleString()"></span>
                                </span>
                                <span class="text-xs font-semibold text-slate-500">/ {{ $product->unit ?? 'unit' }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            @if($isOnFlash)
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-0.5">
                                Reg. Reseller Price
                            </span>
                            <span class="text-lg font-bold text-slate-400 line-through block">
                                ৳{{ number_format($regularResellerPrice, 0) }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium">Retail: ৳{{ number_format($retailPrice, 0) }}</span>
                            @else
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                Regular Retail Price
                            </span>
                            <span class="text-xl font-bold text-slate-400 line-through">
                                ৳<span x-text="Number(currentRetailPrice).toLocaleString()"></span>
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Profit Potential Banner --}}
                    @if($hasMargin)
                        <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl p-3 flex items-center justify-between text-emerald-800">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chart-line text-emerald-600 text-base"></i>
                                <span class="text-xs font-bold">Estimated Profit Margin:</span>
                            </div>
                            <span class="text-xs font-black bg-emerald-600 text-white px-2.5 py-0.5 rounded-lg">
                                +৳{{ number_format($estProfit, 2) }} ({{ round(($estProfit / $retailPrice) * 100) }}%)
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Variant Selector --}}
                @if($product->variants->count() > 0)
                    <div class="space-y-2.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Select Variant / Option:
                        </label>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($product->variants as $v)
                                <button type="button" @click="selectVariant({{ $v->id }})"
                                        class="px-4 py-2.5 rounded-xl text-xs font-bold border transition-all text-left flex items-center gap-2"
                                        :class="selectedVariantId === {{ $v->id }}
                                            ? 'border-indigo-600 bg-indigo-600 text-white shadow-md shadow-indigo-100'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                    <span>{{ $v->variant_name }}</span>
                                    <span class="text-[11px] opacity-80">(Stock: {{ $v->stock_qty }})</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Quantity Selector & Stock Status --}}
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Quantity</label>
                        <span class="text-xs font-bold" :class="currentStock > 0 ? 'text-emerald-600' : 'text-red-500'">
                            <i class="fas" :class="currentStock > 0 ? 'fa-check-circle' : 'fa-times-circle'"></i>
                            <span x-text="currentStock > 0 ? `${currentStock} {{ $product->unit }} Available` : 'Out of Stock'"></span>
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center border-2 border-slate-200 rounded-xl overflow-hidden bg-slate-50 p-1">
                            <button type="button" @click="qty = Math.max(1, qty - 1)"
                                    class="w-10 h-10 rounded-lg bg-white shadow-sm text-slate-600 hover:bg-slate-100 text-base font-bold flex items-center justify-center transition-colors">
                                −
                            </button>
                            <input type="number" x-model="qty" min="1" :max="currentStock"
                                   class="w-14 text-center font-black text-slate-800 text-base border-0 bg-transparent focus:outline-none">
                            <button type="button" @click="qty = Math.min(currentStock, qty + 1)"
                                    class="w-10 h-10 rounded-lg bg-white shadow-sm text-slate-600 hover:bg-slate-100 text-base font-bold flex items-center justify-center transition-colors">
                                +
                            </button>
                        </div>

                        <div class="text-xs text-slate-500">
                            Subtotal: <strong class="text-indigo-700 text-sm">৳<span x-text="(currentResellerPrice * qty).toFixed(2)"></span></strong>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons (Add to Cart & Direct Checkout) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                    {{-- Add to Cart --}}
                    <form method="POST" action="{{ route('reseller.cart.add') }}" class="w-full"
                          @submit.prevent="addToCart(false)">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variant_id" :value="selectedVariantId">
                        <input type="hidden" name="qty" :value="qty">
                        <button type="submit" :disabled="currentStock <= 0"
                                class="w-full h-12 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="added ? 'bg-emerald-600 text-white' : 'bg-slate-900 hover:bg-black text-white'">
                            <i class="fas" :class="added ? 'fa-check' : 'fa-cart-plus'"></i>
                            <span x-text="added ? 'Added to Cart!' : 'Add to Cart'"></span>
                        </button>
                    </form>

                    {{-- Order Now / Buy Now --}}
                    <form method="POST" action="{{ route('reseller.cart.add') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variant_id" :value="selectedVariantId">
                        <input type="hidden" name="qty" :value="qty">
                        <input type="hidden" name="redirect_to" value="checkout">
                        <button type="submit" :disabled="currentStock <= 0"
                                class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-md shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-bolt"></i> Order Now
                        </button>
                    </form>
                </div>

                {{-- Reseller Ordering Process Perks --}}
                <div class="border-t border-slate-100 pt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="p-2.5 rounded-xl bg-slate-50">
                        <i class="fas fa-sliders text-indigo-600 text-sm mb-1 block"></i>
                        <span class="text-[11px] font-bold text-slate-700 block">Custom Markup</span>
                        <span class="text-[10px] text-slate-400">Set customer price at checkout</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50">
                        <i class="fas fa-truck-fast text-emerald-600 text-sm mb-1 block"></i>
                        <span class="text-[11px] font-bold text-slate-700 block">Direct Delivery</span>
                        <span class="text-[10px] text-slate-400">Sent to customer's address</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50">
                        <i class="fas fa-wallet text-amber-600 text-sm mb-1 block"></i>
                        <span class="text-[11px] font-bold text-slate-700 block">Profit on Delivery</span>
                        <span class="text-[10px] text-slate-400">Auto credited to wallet</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Product Description & Specifications Section --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 lg:p-8 space-y-6">
        <h2 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2">
            <i class="fas fa-align-left text-indigo-600"></i> Product Description &amp; Specifications
        </h2>

        @if($product->description)
            <div class="prose max-w-none text-sm text-slate-600 leading-relaxed">
                {!! nl2br(e($product->description)) !!}
            </div>
        @else
            <p class="text-sm text-slate-400 italic">No detailed description provided for this product.</p>
        @endif

        {{-- Specifications Table --}}
        <div class="border-t border-slate-100 pt-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Quick Specifications</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Product Name</span>
                    <div class="text-xs font-bold text-slate-800 mt-0.5">{{ $product->name }}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Category</span>
                    <div class="text-xs font-bold text-indigo-700 mt-0.5">{{ $product->category->name ?? 'General' }}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Branch / Vendor</span>
                    <div class="text-xs font-bold text-slate-800 mt-0.5">{{ $product->vendor->name ?? '—' }}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">SKU / Barcode</span>
                    <div class="text-xs font-mono font-bold text-slate-800 mt-0.5">{{ $product->sku ?? ($product->barcode ?? '—') }}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Unit Type</span>
                    <div class="text-xs font-bold text-slate-800 mt-0.5">{{ $product->unit ?? 'Piece' }}</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Stock Quantity</span>
                    <div class="text-xs font-bold text-slate-800 mt-0.5">{{ $product->stock_qty }} {{ $product->unit ?? 'pcs' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related Products Section --}}
    @if(isset($related) && $related->count() > 0)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-2">
                    <i class="fas fa-boxes text-indigo-600"></i> Other Products from this Branch
                </h2>
                <a href="{{ route('reseller.products.index', ['vendor_id' => $product->vendor_id]) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    View Branch Catalog &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($related as $rel)
                    @php
                        $relPrice = $rel->reseller_price ?? $rel->price;
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all overflow-hidden group">
                        <a href="{{ route('reseller.products.show', $rel->id) }}" class="block aspect-square bg-slate-50 overflow-hidden relative">
                            @if($rel->first_image_url)
                                <img src="{{ $rel->first_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <i class="fas fa-image text-3xl"></i>
                                </div>
                            @endif
                        </a>
                        <div class="p-3.5">
                            <span class="text-[10px] text-indigo-600 font-bold block mb-1">{{ $rel->category->name ?? '' }}</span>
                            <a href="{{ route('reseller.products.show', $rel->id) }}" class="block font-bold text-slate-800 text-xs line-clamp-2 mb-2 hover:text-indigo-600">
                                {{ $rel->name }}
                            </a>
                            <div class="flex items-center justify-between">
                                <span class="text-indigo-700 font-black text-sm">৳{{ number_format($relPrice, 0) }}</span>
                                <span class="text-[10px] text-slate-400">{{ $rel->stock_qty }} in stock</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

{{-- Alpine.js component definition --}}
<script>
function resellerProductShow(variants, baseStock, baseResellerPrice, baseRetailPrice, images) {
    return {
        images: images || [],
        activeImageIndex: 0,
        variants: variants || [],
        selectedVariantId: variants.length > 0 ? variants[0].id : null,
        baseStock: baseStock,
        baseResellerPrice: baseResellerPrice,
        baseRetailPrice: baseRetailPrice,
        qty: 1,
        added: false,

        get currentStock() {
            if (this.selectedVariantId) {
                const v = this.variants.find(item => item.id === this.selectedVariantId);
                return v ? v.stock : this.baseStock;
            }
            return this.baseStock;
        },

        get currentResellerPrice() {
            if (this.selectedVariantId) {
                const v = this.variants.find(item => item.id === this.selectedVariantId);
                return v ? v.reseller_price : this.baseResellerPrice;
            }
            return this.baseResellerPrice;
        },

        get currentRetailPrice() {
            if (this.selectedVariantId) {
                const v = this.variants.find(item => item.id === this.selectedVariantId);
                return v ? v.price : this.baseRetailPrice;
            }
            return this.baseRetailPrice;
        },

        selectVariant(id) {
            this.selectedVariantId = id;
            this.qty = 1;
        },

        nextImage() {
            if (this.images.length > 0) {
                this.activeImageIndex = (this.activeImageIndex + 1) % this.images.length;
            }
        },

        prevImage() {
            if (this.images.length > 0) {
                this.activeImageIndex = (this.activeImageIndex - 1 + this.images.length) % this.images.length;
            }
        },

        addToCart(redirect) {
            fetch('{{ route('reseller.cart.add') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: {{ $product->id }},
                    variant_id: this.selectedVariantId,
                    qty: this.qty
                })
            })
            .then(r => r.json())
            .then(d => {
                this.added = true;
                setTimeout(() => this.added = false, 2500);
            })
            .catch(err => console.error(err));
        }
    }
}
</script>
@endsection
