@extends('shop.layout')
@section('title', 'Cash on Delivery Checkout — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full bg-slate-100/80 min-h-screen py-6 sm:py-12 px-4 sm:px-6')

@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')
<div class="max-w-[580px] mx-auto w-full">

    {{-- Main Checkout Card --}}
    <div id="checkout-order-form"
         class="bg-white rounded-3xl border border-blue-100 shadow-xl overflow-hidden"
         x-data="{
            subtotal: {{ $subtotal }},
            deliveryInside: {{ $deliveryInside }},
            deliverySubDhaka: {{ $deliverySubDhaka }},
            deliveryOutside: {{ $deliveryOutside }},
            zone: 'inside',

            couponCode: '',
            appliedCoupon: null,
            discount: 0,
            couponMessage: '',
            couponError: '',
            loadingCoupon: false,

            get deliveryCharge() {
                if (this.zone === 'inside') return this.deliveryInside;
                if (this.zone === 'sub_dhaka') return this.deliverySubDhaka;
                return this.deliveryOutside;
            },

            get total() {
                return Math.max(0, this.subtotal - this.discount) + this.deliveryCharge;
            },

            applyCoupon() {
                if (!this.couponCode.trim()) return;
                this.loadingCoupon = true;
                this.couponError = '';
                this.couponMessage = '';

                const phoneInput = document.querySelector('input[name=phone]');
                const phone = phoneInput ? phoneInput.value : '';

                fetch('{{ route('shop.checkout.coupon') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        code: this.couponCode,
                        phone: phone
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.loadingCoupon = false;
                    if (data.success) {
                        this.appliedCoupon = data.code;
                        this.discount = parseFloat(data.discount_amount) || 0;
                        this.couponMessage = `Coupon ${data.code} applied successfully! Discount: ৳${this.discount.toFixed(2)}`;
                    } else {
                        this.couponError = data.message || 'Invalid or expired coupon code';
                        this.appliedCoupon = null;
                        this.discount = 0;
                    }
                })
                .catch(err => {
                    this.loadingCoupon = false;
                    this.couponError = 'Failed to verify coupon code';
                });
            },

            removeCoupon() {
                this.appliedCoupon = null;
                this.discount = 0;
                this.couponCode = '';
                this.couponMessage = '';
                this.couponError = '';
            }
         }">

        {{-- Card Header matching reference image --}}
        <div class="bg-gradient-to-r from-blue-50/90 to-indigo-50/50 px-6 py-5 sm:px-8 sm:py-6 border-b border-blue-100/70 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 border border-blue-200/80 flex items-center justify-center text-blue-600 shadow-2xs flex-shrink-0">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900 leading-tight">Cash on Delivery Order</h2>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-1">Enter your delivery details</p>
                </div>
            </div>
            <a href="{{ route('root') }}"
               class="w-8 h-8 rounded-full bg-white hover:bg-slate-100 text-slate-400 hover:text-slate-600 border border-slate-200/80 flex items-center justify-center transition-colors shadow-2xs"
               title="Close">
                <i class="fas fa-times text-xs"></i>
            </a>
        </div>

        {{-- Checkout Form Container with explicit padding --}}
        <div class="p-6 sm:p-8 md:p-9">
            <form method="POST" action="{{ route('shop.checkout.store') }}" class="space-y-5 sm:space-y-6">
                @csrf
            <input type="hidden" name="payment_method" value="cash">
            <input type="hidden" name="coupon_code" :value="appliedCoupon">

            @if(auth('customer')->check())
            <div class="bg-blue-50/70 border border-blue-100 rounded-xl px-4 py-3 flex items-center justify-between text-xs text-blue-900">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-circle text-blue-600 text-sm"></i>
                    <span class="font-bold">Ordering as {{ auth('customer')->user()->name }}</span>
                </div>
                <span class="text-[11px] text-blue-700 font-medium">{{ auth('customer')->user()->phone }}</span>
            </div>
            @endif

            {{-- 1. Full Name * --}}
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-pencil-alt text-xs"></i>
                    </span>
                    <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required placeholder="Your full name"
                           class="w-full pl-11 pr-4 py-3 sm:py-3.5 text-sm bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-slate-400 font-medium">
                </div>
                @error('name') <p class="text-xs text-red-500 mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Phone Number * --}}
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-2">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-phone-alt text-xs"></i>
                    </span>
                    <input type="tel" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" required placeholder="11-digit mobile number"
                           class="w-full pl-11 pr-4 py-3 sm:py-3.5 text-sm bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-slate-400 font-medium">
                </div>
                @error('phone') <p class="text-xs text-red-500 mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Delivery Address * (District and Thana dropdowns removed) --}}
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-2">
                    Delivery Address <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute top-3.5 left-4 flex items-start pointer-events-none text-slate-400">
                        <i class="fas fa-location-dot text-xs"></i>
                    </span>
                    <textarea name="address" rows="2" required placeholder="Your district, thana and full delivery address"
                              class="w-full pl-11 pr-4 py-3 sm:py-3.5 text-sm bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder:text-slate-400 resize-none font-medium">{{ old('address', $customer->address ?? '') }}</textarea>
                </div>
                @error('address') <p class="text-xs text-red-500 mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>

            {{-- 4. Cart Items Section (Product details matching image) --}}
            <div id="checkout-cart-lines">
                @include('shop.partials.checkout-cart-lines', ['lines' => $lines])
            </div>

            {{-- 5. Delivery Area (Delivery Charge / Shipping Area) --}}
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-2.5">
                    Delivery Area
                </label>
                <div class="space-y-2.5">
                    {{-- Dhaka City --}}
                    <label class="block cursor-pointer select-none">
                        <input type="radio" name="delivery_zone" value="inside" x-model="zone" class="sr-only">
                        <div class="flex items-center justify-between px-4 py-3.5 sm:px-5 sm:py-4 rounded-xl border transition-all"
                             :class="zone === 'inside' ? 'border-2 border-blue-600 bg-blue-50/70 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300'">
                            <div class="flex items-center gap-3">
                                <span class="w-4.5 h-4.5 rounded-full border flex items-center justify-center transition-colors"
                                      :class="zone === 'inside' ? 'border-blue-600 bg-blue-600' : 'border-slate-300 bg-white'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="zone === 'inside'"></span>
                                </span>
                                <span class="text-xs sm:text-sm" :class="zone === 'inside' ? 'text-blue-950 font-extrabold' : 'text-slate-700 font-semibold'">Inside Dhaka</span>
                            </div>
                            <span class="text-xs sm:text-sm font-mono" :class="zone === 'inside' ? 'text-blue-900 font-black' : 'text-slate-800 font-bold'">
                                Tk <span x-text="deliveryInside.toFixed(2)"></span>
                            </span>
                        </div>
                    </label>

                    {{-- Outside Dhaka --}}
                    <label class="block cursor-pointer select-none">
                        <input type="radio" name="delivery_zone" value="outside" x-model="zone" class="sr-only">
                        <div class="flex items-center justify-between px-4 py-3.5 sm:px-5 sm:py-4 rounded-xl border transition-all"
                             :class="zone === 'outside' ? 'border-2 border-blue-600 bg-blue-50/70 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300'">
                            <div class="flex items-center gap-3">
                                <span class="w-4.5 h-4.5 rounded-full border flex items-center justify-center transition-colors"
                                      :class="zone === 'outside' ? 'border-blue-600 bg-blue-600' : 'border-slate-300 bg-white'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="zone === 'outside'"></span>
                                </span>
                                <span class="text-xs sm:text-sm" :class="zone === 'outside' ? 'text-blue-950 font-extrabold' : 'text-slate-700 font-semibold'">Outside Dhaka</span>
                            </div>
                            <span class="text-xs sm:text-sm font-mono" :class="zone === 'outside' ? 'text-blue-900 font-black' : 'text-slate-800 font-bold'">
                                Tk <span x-text="deliveryOutside.toFixed(2)"></span>
                            </span>
                        </div>
                    </label>

                    {{-- Sub Dhaka (Upazila) --}}
                    <label class="block cursor-pointer select-none">
                        <input type="radio" name="delivery_zone" value="sub_dhaka" x-model="zone" class="sr-only">
                        <div class="flex items-center justify-between px-4 py-3.5 sm:px-5 sm:py-4 rounded-xl border transition-all"
                             :class="zone === 'sub_dhaka' ? 'border-2 border-blue-600 bg-blue-50/70 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300'">
                            <div class="flex items-start sm:items-center gap-3 min-w-0 flex-1 pr-2">
                                <span class="w-4.5 h-4.5 rounded-full border flex items-center justify-center transition-colors flex-shrink-0 mt-0.5 sm:mt-0"
                                      :class="zone === 'sub_dhaka' ? 'border-blue-600 bg-blue-600' : 'border-slate-300 bg-white'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="zone === 'sub_dhaka'"></span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <span class="text-xs sm:text-sm block leading-tight" :class="zone === 'sub_dhaka' ? 'text-blue-950 font-extrabold' : 'text-slate-700 font-semibold'">Sub Dhaka</span>
                                    @if(!empty($subDhakaUpazilaList))
                                        <span class="text-[10.5px] text-slate-400 block mt-0.5 leading-snug break-words">
                                             {{ implode(', ', $subDhakaUpazilaList) }}
                                        </span>
                                    @else
                                        <span class="text-[10.5px] text-slate-400 block mt-0.5">Savar, Demra, Keraniganj & designated upazilas</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm font-mono flex-shrink-0" :class="zone === 'sub_dhaka' ? 'text-blue-900 font-black' : 'text-slate-800 font-bold'">
                                Tk <span x-text="deliverySubDhaka.toFixed(2)"></span>
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 6. Order Summary Box --}}
            <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-5 sm:p-6 space-y-3 text-xs sm:text-sm">
                <div class="flex items-center justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-semibold font-mono">Tk <span x-text="subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                </div>
                <div x-show="discount > 0" class="flex items-center justify-between text-emerald-600 font-bold" x-cloak>
                    <span>Coupon Discount (<span x-text="appliedCoupon"></span>)</span>
                    <span class="font-mono">-Tk <span x-text="discount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span>Shipping</span>
                    <span class="font-semibold font-mono">Tk <span x-text="deliveryCharge.toFixed(2)"></span></span>
                </div>
                <div class="pt-3 border-t border-blue-100 flex items-center justify-between text-slate-900">
                    <span class="font-black text-sm sm:text-base">Total</span>
                    <span class="font-black text-base sm:text-lg font-mono">Tk <span x-text="total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                </div>
            </div>

            {{-- 7. Coupon Code (User requested: image-er note-er jaygay coupon add korar option) --}}
            <div>
                <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-2 flex items-center justify-between">
                    <span><i class="fas fa-ticket-alt text-blue-600 mr-1.5"></i> Coupon Code (Optional)</span>
                    <span x-show="appliedCoupon" class="text-xs text-emerald-600 font-bold" x-cloak>Applied!</span>
                </label>
                <div class="flex gap-2.5">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-tag text-xs"></i>
                        </span>
                        <input type="text" x-model="couponCode" placeholder="Enter coupon code"
                               :disabled="appliedCoupon !== null"
                               @keydown.enter.prevent="applyCoupon()"
                               class="w-full pl-11 pr-4 py-3 sm:py-3.5 text-xs sm:text-sm uppercase font-mono font-bold bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition disabled:bg-slate-100 placeholder:normal-case placeholder:font-normal">
                    </div>
                    <button type="button" @click="appliedCoupon ? removeCoupon() : applyCoupon()"
                            :disabled="loadingCoupon || (!appliedCoupon && !couponCode.trim())"
                            class="px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl text-xs sm:text-sm font-bold transition-all disabled:opacity-50 flex items-center justify-center flex-shrink-0 cursor-pointer shadow-xs"
                            :class="appliedCoupon ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200' : 'bg-blue-600 hover:bg-blue-700 text-white'">
                        <span x-show="!loadingCoupon" x-text="appliedCoupon ? 'Remove' : 'Apply'"></span>
                        <span x-show="loadingCoupon"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
                <div x-show="couponMessage" x-text="couponMessage" class="text-xs font-bold text-emerald-600 mt-2 flex items-center gap-1" x-cloak></div>
                <div x-show="couponError" x-text="couponError" class="text-xs font-bold text-red-600 mt-2 flex items-center gap-1" x-cloak></div>
            </div>

            {{-- 8. Submit Order Button matching reference image --}}
            <div class="pt-3 pb-2">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white py-4 sm:py-4.5 rounded-xl sm:rounded-2xl text-sm sm:text-base font-extrabold transition-all shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 cursor-pointer">
                    <span>Confirm Order — TK <span x-text="total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                </button>
                <p class="text-center text-[11px] sm:text-xs text-slate-400 mt-3 font-medium">
                    Cash on delivery — pay when you receive your order
                </p>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
