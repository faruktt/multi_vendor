{{-- Expects: $cartLines (Collection), $cartSubtotal (float). Re-rendered both on full page load and via AJAX cart updates. --}}
@php $currency = $appSettings['currency'] ?? '৳'; @endphp
@if($cartLines->isEmpty())
<div class="flex-1 flex flex-col items-center justify-center text-center px-6">
    <i class="fas fa-cart-shopping text-gray-200 text-5xl mb-4"></i>
    <p class="text-gray-500 text-sm font-semibold">Your cart is empty</p>
    <a href="{{ route('shop.products.index') }}" @click="cartOpen = false"
       class="mt-4 inline-flex items-center gap-1.5 text-brand-dark text-sm font-bold hover:underline">
        Browse products <i class="fas fa-arrow-right text-xs"></i>
    </a>
</div>
@else
<div class="flex-1 overflow-y-auto divide-y divide-gray-100">
    @foreach($cartLines as $line)
    @php
        $lineMax = $line['variant']->stock_qty ?? $line['product']->stock_qty;
    @endphp
    <div class="flex items-start gap-3 px-5 py-4">
        <div class="w-14 h-14 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
            @if($line['image_url'])
            <img src="{{ $line['image_url'] }}" class="w-full h-full object-cover">
            @else
            <i class="fas fa-box text-gray-300 text-sm"></i>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-[13px] font-semibold text-gray-800 leading-snug">{{ $line['product']->name }}</p>
                <form method="POST" action="{{ route('shop.cart.remove', $line['key']) }}" class="flex-shrink-0" @submit.prevent="submitCartForm($el)">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-6 h-6 rounded text-gray-300 hover:text-red-500 flex items-center justify-center transition-colors">
                        <i class="fas fa-trash text-[11px]"></i>
                    </button>
                </form>
            </div>
            <div class="flex items-center justify-between gap-2 mt-2">
                <div class="flex items-center gap-2">
                    <span class="text-[12px] text-gray-400">{{ $currency }}{{ number_format($line['price'], 0) }}</span>
                    <form method="POST" action="{{ route('shop.cart.update') }}"
                          x-data="{ qty: {{ $line['qty'] }}, max: {{ $lineMax }} }" class="flex items-center border border-gray-200 rounded-lg">
                        @csrf
                        <button type="button" @click="if (qty > 1) { qty--; $nextTick(() => submitCartForm($el.closest('form'))) }"
                                class="w-6 h-7 flex items-center justify-center text-gray-500 hover:text-brand-dark">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="w-6 text-center text-[12px] font-bold" x-text="qty"></span>
                        <button type="button" @click="if (qty < max) { qty++; $nextTick(() => submitCartForm($el.closest('form'))) }"
                                class="w-6 h-7 flex items-center justify-center text-gray-500 hover:text-brand-dark">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <input type="hidden" :name="'qty[{{ $line['key'] }}]'" :value="qty">
                    </form>
                </div>
                <p class="text-[13px] font-extrabold text-gray-900 flex-shrink-0">{{ $currency }}{{ number_format($line['subtotal'], 0) }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="border-t border-gray-100 p-5 flex-shrink-0 space-y-2">
    <div class="flex items-center justify-between text-sm">
        <span class="text-gray-500">Sub-Total</span>
        <span class="text-gray-700">{{ $currency }}{{ number_format($cartSubtotal, 0) }}</span>
    </div>
    <div class="flex items-center justify-between">
        <span class="font-extrabold text-gray-900">Total</span>
        <span class="font-extrabold text-brand-dark text-lg">{{ $currency }}{{ number_format($cartSubtotal, 0) }}</span>
    </div>
    <a href="{{ route('shop.checkout.index') }}"
       class="mt-3 flex items-center justify-center gap-2 w-full text-center bg-brand hover:bg-brand-dark text-white py-3.5 rounded-xl text-sm font-bold transition-colors">
        Checkout <i class="fas fa-arrow-right text-xs"></i>
    </a>
</div>
@endif
