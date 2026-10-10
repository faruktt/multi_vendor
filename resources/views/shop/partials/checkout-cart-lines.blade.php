{{-- Expects: $lines (Collection). Re-rendered both on full page load and via AJAX cart updates. --}}
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@if($lines->isEmpty())
<div class="p-8 text-center bg-blue-50/30 rounded-2xl border border-blue-100">
    <i class="fas fa-cart-shopping text-blue-200 text-3xl mb-2 block"></i>
    <p class="text-slate-500 text-xs font-semibold mb-2">Your cart is empty</p>
    <a href="{{ route('shop.products.index') }}" class="inline-flex items-center gap-1.5 text-blue-600 text-xs font-bold hover:underline">
        Continue Shopping <i class="fas fa-arrow-right text-[10px]"></i>
    </a>
</div>
@else
<div class="bg-blue-50/50 border border-blue-100/90 rounded-2xl p-4 sm:p-5 space-y-4">
    @foreach($lines as $line)
    @php
        $lineMax = $line['variant']->stock_qty ?? $line['product']->stock_qty;
    @endphp
    <div class="flex items-center gap-3.5 sm:gap-4">
        {{-- Thumbnail --}}
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white border border-blue-100 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-2xs">
            @if($line['image_url'])
            <img src="{{ $line['image_url'] }}" alt="{{ $line['product']->name }}" class="w-full h-full object-cover">
            @else
            <i class="fas fa-box text-slate-300 text-xl"></i>
            @endif
        </div>

        {{-- Info + Stepper + Price --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1 pr-2">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 truncate leading-snug" title="{{ $line['product']->name }}">
                        {{ $line['product']->name }}
                    </h4>
                    @if($line['variant'])
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5 truncate">{{ $line['variant']->variant_name }}</p>
                    @endif
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs sm:text-sm font-black text-slate-900 font-mono">Tk {{ number_format($line['subtotal'], 2) }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between mt-2.5">
                {{-- Stepper matching screenshot [ - 2 + ] (no nested form tags) --}}
                <div x-data="{ qty: {{ $line['qty'] }}, max: {{ $lineMax }}, loading: false }"
                     class="inline-flex items-center bg-white border border-slate-200 rounded-lg shadow-2xs overflow-hidden">
                    <button type="button"
                            :disabled="loading"
                            @click="if (qty > 1 && !loading) { qty--; loading = true; window.updateCheckoutQty && window.updateCheckoutQty('{{ $line['key'] }}', qty) }"
                            class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-slate-50 transition-colors font-bold text-xs leading-none cursor-pointer disabled:opacity-50">−</button>
                    <span class="w-7 text-center text-xs font-bold text-slate-800 select-none" x-text="qty"></span>
                    <button type="button"
                            :disabled="loading"
                            @click="if (qty < max && !loading) { qty++; loading = true; window.updateCheckoutQty && window.updateCheckoutQty('{{ $line['key'] }}', qty) }"
                            class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-slate-50 transition-colors font-bold text-xs leading-none cursor-pointer disabled:opacity-50">+</button>
                </div>

                <button type="button"
                        @click="window.removeCheckoutQty && window.removeCheckoutQty('{{ $line['key'] }}')"
                        class="text-slate-300 hover:text-red-500 transition-colors p-1 cursor-pointer" title="Remove item">
                    <i class="fas fa-trash-can text-xs"></i>
                </button>
            </div>
        </div>
    </div>
    @if(!$loop->last)
    <div class="border-t border-blue-100/70"></div>
    @endif
    @endforeach

    <div class="pt-2.5 pb-0.5 flex items-center justify-end border-t border-blue-100/60">
        <a href="{{ route('shop.products.index') }}" class="text-[11px] sm:text-xs text-blue-600 hover:text-blue-700 font-bold inline-flex items-center gap-1">
            <i class="fas fa-plus text-[10px]"></i> Add More Products
        </a>
    </div>
</div>
@endif
