{{-- Expects: $lines (Collection). Re-rendered both on full page load and via AJAX cart updates. --}}
@php $currency = $appSettings['currency'] ?? '৳'; @endphp
@if($lines->isEmpty())
<div class="p-10 text-center">
    <i class="fas fa-cart-shopping text-gray-200 text-4xl mb-3"></i>
    <p class="text-gray-500 text-sm font-semibold mb-3">Your cart is empty</p>
    <a href="{{ route('shop.products.index') }}" class="inline-flex items-center gap-1.5 text-brand-dark text-sm font-bold hover:underline">
        Browse products <i class="fas fa-arrow-right text-xs"></i>
    </a>
</div>
@else
{{-- Standalone remove forms — a <form> can't nest inside the qty forms below, so each
     remove button targets one of these via the HTML5 form="" attribute instead. --}}
@foreach($lines as $line)
<form id="remove-form-{{ $line['key'] }}" method="POST" action="{{ route('shop.cart.remove', $line['key']) }}" class="hidden" @submit.prevent="submitCartForm($el)">
    @csrf @method('DELETE')
</form>
@endforeach

<div class="divide-y divide-gray-100">
    @foreach($lines as $line)
    @php
        $lineMax = $line['variant']->stock_qty ?? $line['product']->stock_qty;
    @endphp
    <div class="flex items-center gap-3 p-4">
        <div class="w-14 h-14 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
            @if($line['image_url'])
            <img src="{{ $line['image_url'] }}" class="w-full h-full object-cover">
            @else
            <i class="fas fa-box text-gray-300"></i>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ $line['product']->name }}</p>
            @if($line['variant'])
            <p class="text-xs text-purple-600">{{ $line['variant']->variant_name }}</p>
            @endif
            <p class="text-xs text-gray-400">{{ $currency }} {{ number_format($line['price'], 0) }} each</p>
        </div>

        <form method="POST" action="{{ route('shop.cart.update') }}"
              x-data="{ qty: {{ $line['qty'] }}, max: {{ $lineMax }} }" class="flex items-center border border-gray-200 rounded-lg flex-shrink-0">
            @csrf
            <button type="button" @click="if (qty > 1) { qty--; $nextTick(() => submitCartForm($el.closest('form'))) }"
                    class="w-8 h-9 flex items-center justify-center text-gray-500 hover:text-brand-dark">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
            </button>
            <span class="w-7 text-center text-sm font-bold" x-text="qty"></span>
            <button type="button" @click="if (qty < max) { qty++; $nextTick(() => submitCartForm($el.closest('form'))) }"
                    class="w-8 h-9 flex items-center justify-center text-gray-500 hover:text-brand-dark">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </button>
            <input type="hidden" :name="'qty[{{ $line['key'] }}]'" :value="qty">
        </form>

        <p class="w-20 text-right font-extrabold text-gray-900 text-sm flex-shrink-0">{{ $currency }} {{ number_format($line['subtotal'], 0) }}</p>
        <button type="submit" form="remove-form-{{ $line['key'] }}"
                class="w-8 h-8 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors flex-shrink-0">
            <i class="fas fa-trash text-xs"></i>
        </button>
    </div>
    @endforeach
</div>
<div class="p-4 border-t border-gray-100 flex items-center justify-end">
    <a href="{{ route('shop.products.index') }}" class="text-sm text-gray-500 hover:text-brand-dark font-semibold">
        <i class="fas fa-plus text-xs mr-1"></i> Add more items
    </a>
</div>
@endif
