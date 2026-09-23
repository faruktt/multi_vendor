@extends('reseller.layouts.app')
@section('title', 'Cart')
@section('heading', 'Shopping Cart')

@section('content')
<div class="py-4 max-w-3xl">

    @if($lines->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <i class="fas fa-shopping-cart text-5xl text-slate-200 mb-4 block"></i>
            <p class="text-slate-500 font-medium mb-4">Your cart is empty</p>
            <a href="{{ route('reseller.products.index') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold transition-colors">
                <i class="fas fa-box-open"></i> Browse Products
            </a>
        </div>
    @else
        <div class="space-y-3 mb-6">
            @foreach($lines as $line)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-4">
                    {{-- Image --}}
                    <a href="{{ route('reseller.products.show', $line['product']->id) }}" class="w-16 h-16 rounded-xl overflow-hidden bg-slate-50 flex-shrink-0 block hover:opacity-90 transition-opacity">
                        @if($line['image_url'])
                            <img src="{{ $line['image_url'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </a>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('reseller.products.show', $line['product']->id) }}" class="font-bold text-slate-800 text-sm truncate block hover:text-indigo-600 transition-colors">
                            {{ $line['product']->name }}
                        </a>
                        @if($line['variant'])
                            <span class="text-xs text-slate-400">{{ $line['variant']->variant_name }}</span>
                        @endif
                        <div class="text-indigo-600 font-bold text-sm mt-0.5">৳{{ number_format($line['price'], 0) }} / {{ $line['product']->unit }}</div>
                    </div>

                    {{-- Qty update --}}
                    <form method="POST" action="{{ route('reseller.cart.update') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="key" value="{{ $line['key'] }}">
                        <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                            <button type="submit" name="qty" value="{{ max(0, $line['qty'] - 1) }}"
                                    class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-sm font-bold transition-colors">-</button>
                            <span class="px-3 text-sm font-semibold text-slate-700">{{ $line['qty'] }}</span>
                            <button type="submit" name="qty" value="{{ $line['qty'] + 1 }}"
                                    class="px-2 py-1 text-slate-500 hover:bg-slate-100 text-sm font-bold transition-colors">+</button>
                        </div>
                    </form>

                    {{-- Subtotal --}}
                    <div class="text-right flex-shrink-0">
                        <div class="font-bold text-slate-800">৳{{ number_format($line['subtotal'], 0) }}</div>
                    </div>

                    {{-- Remove --}}
                    <form method="POST" action="{{ route('reseller.cart.remove', $line['key']) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 transition-colors ml-1">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- Summary --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex justify-between items-center mb-4">
                <span class="text-slate-600 font-medium">Subtotal</span>
                <span class="text-xl font-bold text-slate-800">৳{{ number_format($subtotal, 0) }}</span>
            </div>
            <a href="{{ route('reseller.orders.create') }}"
               class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-center flex items-center justify-center gap-2 transition-colors">
                <i class="fas fa-credit-card"></i> Proceed to Checkout
            </a>
            <a href="{{ route('reseller.products.index') }}"
               class="mt-2 w-full border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Continue Shopping
            </a>
        </div>
    @endif
</div>
@endsection
