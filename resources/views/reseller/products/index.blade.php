@extends('reseller.layouts.app')
@section('title', 'Products')
@section('heading', 'Browse Products')

@section('content')
<div class="py-4">

    {{-- ══ TOP FLASH SALE SHELF (Exact match to Website Index Page) ══ --}}
    @if(isset($flashSaleProducts) && $flashSaleProducts->isNotEmpty() && $products->currentPage() == 1)
    <div class="mb-6">
        <div class="rounded-2xl border-2 border-amber-300 bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-amber-500/10 p-4 shadow-sm relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-36 h-36 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>

            {{-- Shelf Header --}}
            <div class="flex items-center justify-between mb-3 relative z-10">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500 text-white shadow-sm font-black text-sm">
                        ⚡
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                            Flash Sale & Super Deals
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-rose-600 text-white animate-pulse">LIMITED TIME</span>
                        </h2>
                        <p class="text-xs text-gray-500 font-medium">Grab these hot discounts before time runs out!</p>
                    </div>
                </div>
                <a href="{{ route('reseller.products.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1">
                    View All <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Horizontal Track & Overlay Scroll Arrows --}}
            <div class="relative z-10">
                <button type="button" onclick="shelfScroll('shelf-flash-track', -1)"
                        class="flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-700 hover:text-amber-600 hover:shadow-lg transition-shadow">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" onclick="shelfScroll('shelf-flash-track', 1)"
                        class="flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white border border-gray-200 shadow-md items-center justify-center text-gray-700 hover:text-amber-600 hover:shadow-lg transition-shadow">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>

                <div id="shelf-flash-track" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1 no-scrollbar">
                    @foreach($flashSaleProducts as $product)
                        @php
                            $out = $product->stock_qty <= 0;
                            $isOnFlash = true;

                            // Display reseller flash pricing strictly
                            $flashPrice   = $product->reseller_flash_price;
                            $regularPrice = (float) ($product->reseller_price ?? $product->price);
                            $displayFlash = $flashPrice ?? $regularPrice;
                            $displayReg   = $regularPrice;
                        @endphp
                        <div class="snap-start flex-shrink-0 w-[calc((100%-5*16px)/6)] min-w-[150px] bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col relative"
                             x-data="{ qty: 1, active: false, max: {{ max(1, $product->stock_qty) }}, added: false }">

                            {{-- Flash Sale Badge --}}
                            <div class="absolute top-2 left-2 z-20 bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white text-[9.5px] font-black tracking-wider px-2 py-0.5 rounded-full shadow-md flex items-center gap-1">
                                <i class="fas fa-bolt text-yellow-200 text-[9px] animate-pulse"></i> FLASH SALE
                            </div>

                            {{-- Card Art / Image --}}
                            <div class="card-art relative h-[220px] bg-gray-50 border-b border-gray-100">
                                <a href="{{ route('reseller.products.show', $product->id) }}" class="absolute inset-0 flex items-center justify-center">
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

                                {{-- Floating Action Button / Stepper (Exact Green #2E7D34 Match) --}}
                                @if($out)
                                <button type="button" disabled
                                        class="absolute -bottom-3.5 right-3 w-7 h-7 rounded-full bg-gray-300 text-white border-2 border-white shadow flex items-center justify-center cursor-not-allowed">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                                @else
                                <button type="button" x-show="!active" @click="active = true"
                                        class="absolute -bottom-3.5 right-3 w-7 h-7 rounded-full bg-[#2E7D34] hover:bg-[#1F5A24] text-white border-2 border-white shadow flex items-center justify-center transition-all hover:scale-105" title="Add to cart">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>

                                <form x-show="active" x-cloak
                                      @submit.prevent="
                                        fetch('{{ route('reseller.cart.add') }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ product_id: {{ $product->id }}, qty: qty })
                                        }).then(r => r.json()).then(d => {
                                            added = true;
                                            setTimeout(() => { added = false; active = false; }, 1500);
                                            if (d && d.count !== undefined) {
                                                const b = document.querySelector('header a[href*=\'cart\'] span');
                                                if (b) {
                                                    b.innerText = d.count;
                                                } else {
                                                    const cartLink = document.querySelector('header a[href*=\'cart\']');
                                                    if (cartLink) {
                                                        const newBadge = document.createElement('span');
                                                        newBadge.className = 'absolute -top-1 -right-1 bg-indigo-600 text-white text-[9px] font-bold px-1 rounded-full';
                                                        newBadge.innerText = d.count;
                                                        cartLink.appendChild(newBadge);
                                                    }
                                                }
                                            }
                                        });
                                      "
                                      class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 bg-[#2E7D34] rounded-full flex items-center shadow-lg overflow-hidden z-20 transition-all">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="qty" :value="qty">
                                    <button type="button" @click="qty > 1 ? qty-- : (active = false, qty = 1)"
                                            class="w-7 h-7 flex items-center justify-center text-white hover:bg-[#1F5A24]">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                                    </button>
                                    <span class="min-w-[22px] text-center text-[12px] font-extrabold text-white" x-text="qty"></span>
                                    <button type="button" @click="qty = Math.min(qty + 1, max)"
                                            class="w-7 h-7 flex items-center justify-center text-white hover:bg-[#1F5A24]">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                    </button>
                                    <button type="submit" class="w-7 h-7 flex items-center justify-center text-white/90 hover:text-white border-l border-white/20 hover:bg-[#1F5A24]" title="Add to cart">
                                        <svg x-show="!added" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        <i x-show="added" class="fas fa-check text-xs text-white"></i>
                                    </button>
                                </form>
                                @endif
                            </div>

                            {{-- Product Title & Pricing (Matching Image 1:1) --}}
                            <div class="pt-6 px-3 pb-3 flex flex-col gap-1.5 flex-1">
                                <a href="{{ route('reseller.products.show', $product->id) }}" class="text-[12.5px] font-medium leading-tight text-gray-800 line-clamp-2 min-h-[32px] hover:text-[#2E7D34]">{{ $product->name }}</a>
                                <div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-[16px] font-black text-rose-600">৳ {{ number_format($displayFlash, 0) }}</span>
                                        <span class="text-[11.5px] text-gray-400 line-through">৳ {{ number_format($displayReg, 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or barcode..."
                   class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
        </div>
        <div class="min-w-[180px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Category</label>
            <select name="category_id" class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-semibold transition-colors">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        @if(request()->anyFilled(['search','category_id']))
            <a href="{{ route('reseller.products.index') }}" class="border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">Clear</a>
        @endif
    </form>

    {{-- Products Grid Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold text-slate-800">Website Products</h3>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100/80">
                <i class="fas fa-globe text-[11px] mr-1"></i>{{ $website->name ?? 'Website' }}
            </span>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ $products->total() }} items</span>
        </div>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse($products as $product)
            @php
                $out                  = $product->stock_qty <= 0;
                $isOnFlash            = $product->isOnResellerFlashSale();
                $flashPrice           = $product->reseller_flash_price;
                $regularResellerPrice = (float) ($product->reseller_price ?? $product->price);
                $effectivePrice       = ($isOnFlash && $flashPrice !== null) ? $flashPrice : $regularResellerPrice;
                $hasDiscount          = !$isOnFlash && $product->reseller_price && $product->reseller_price < $product->price;
            @endphp
            <div class="bg-white border {{ $isOnFlash ? 'border-amber-300 ring-1 ring-amber-200' : 'border-gray-200' }} rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col relative"
                 x-data="{ qty: 1, active: false, max: {{ max(1, $product->stock_qty) }}, added: false }">

                {{-- Flash / Discount Badge --}}
                @if($isOnFlash)
                <div class="absolute top-2 left-2 z-20 bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white text-[9.5px] font-black tracking-wider px-2 py-0.5 rounded-full shadow-md flex items-center gap-1">
                    <i class="fas fa-bolt text-yellow-200 text-[9px] animate-pulse"></i> FLASH SALE
                </div>
                @elseif($hasDiscount)
                
                @endif

                {{-- Image Area --}}
                <div class="card-art relative h-[220px] bg-gray-50 border-b border-gray-100">
                    <a href="{{ route('reseller.products.show', $product->id) }}" class="absolute inset-0 flex items-center justify-center">
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

                    {{-- Floating Action Button / Stepper --}}
                    @if($out)
                    <button type="button" disabled
                            class="absolute -bottom-3.5 right-3 w-7 h-7 rounded-full bg-gray-300 text-white border-2 border-white shadow flex items-center justify-center cursor-not-allowed">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    @else
                    <button type="button" x-show="!active" @click="active = true"
                            class="absolute -bottom-3.5 right-3 w-7 h-7 rounded-full bg-[#2E7D34] hover:bg-[#1F5A24] text-white border-2 border-white shadow flex items-center justify-center transition-all hover:scale-105" title="Add to cart">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>

                    <form x-show="active" x-cloak
                          @submit.prevent="
                            fetch('{{ route('reseller.cart.add') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ product_id: {{ $product->id }}, qty: qty })
                            }).then(r => r.json()).then(d => {
                                added = true;
                                setTimeout(() => { added = false; active = false; }, 1500);
                                if (d && d.count !== undefined) {
                                    const b = document.querySelector('header a[href*=\'cart\'] span');
                                    if (b) {
                                        b.innerText = d.count;
                                    } else {
                                        const cartLink = document.querySelector('header a[href*=\'cart\']');
                                        if (cartLink) {
                                            const newBadge = document.createElement('span');
                                            newBadge.className = 'absolute -top-1 -right-1 bg-indigo-600 text-white text-[9px] font-bold px-1 rounded-full';
                                            newBadge.innerText = d.count;
                                            cartLink.appendChild(newBadge);
                                        }
                                    }
                                }
                            });
                          "
                          class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 bg-[#2E7D34] rounded-full flex items-center shadow-lg overflow-hidden z-20 transition-all">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="qty" :value="qty">
                        <button type="button" @click="qty > 1 ? qty-- : (active = false, qty = 1)"
                                class="w-7 h-7 flex items-center justify-center text-white hover:bg-[#1F5A24]">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="min-w-[22px] text-center text-[12px] font-extrabold text-white" x-text="qty"></span>
                        <button type="button" @click="qty = Math.min(qty + 1, max)"
                                class="w-7 h-7 flex items-center justify-center text-white hover:bg-[#1F5A24]">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <button type="submit" class="w-7 h-7 flex items-center justify-center text-white/90 hover:text-white border-l border-white/20 hover:bg-[#1F5A24]" title="Add to cart">
                            <svg x-show="!added" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <i x-show="added" class="fas fa-check text-xs text-white"></i>
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="pt-6 px-3 pb-3 flex flex-col gap-1.5 flex-1">
                    <a href="{{ route('reseller.products.show', $product->id) }}" class="text-[12.5px] font-medium leading-tight text-gray-800 line-clamp-2 min-h-[32px] hover:text-[#2E7D34] transition-colors">
                        {{ $product->name }}
                    </a>
                    <div>
                        @if($isOnFlash)
                        <div class="flex items-baseline gap-1.5 flex-wrap">
                            <span class="text-[16px] font-black text-rose-600">৳ {{ number_format($effectivePrice, 0) }}</span>
                            <span class="text-[11.5px] text-gray-400 line-through">৳ {{ number_format($regularResellerPrice, 0) }}</span>
                        </div>
                        @else
                        <div class="text-[15px] font-extrabold text-gray-900">
                            ৳ {{ number_format($effectivePrice, 0) }}
                            @if($hasDiscount)
                            <span class="text-[11.5px] text-gray-400 font-normal line-through ml-1"></span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-slate-400">
                <i class="fas fa-box-open text-4xl mb-3 block text-slate-300"></i>
                No products found.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<script>
    window.shelfScroll = function (trackId, dir) {
        const el = document.getElementById(trackId);
        if (el) el.scrollBy({ left: Math.max(260, el.clientWidth * 0.75) * dir, behavior: 'smooth' });
    };
</script>
@endpush
