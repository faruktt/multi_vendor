@extends('shop.layout')
@section('title', 'Checkout — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full')

@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">Cart &amp; Checkout</span>
@endsection

@section('content')
<div class="max-w-[1440px] mx-auto px-4 py-4">

    <h1 class="text-xl font-extrabold tracking-tight mb-4">Your Cart &amp; Checkout</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Cart lines --}}
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden h-fit">
            <div id="checkout-cart-lines">
                @include('shop.partials.checkout-cart-lines', ['lines' => $lines])
            </div>
        </div>

        {{-- Order form + summary --}}
        <div id="checkout-order-form" class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 h-fit"
             x-data="{
                subtotal: {{ $subtotal }},
                deliveryInside: {{ $deliveryInside }},
                deliverySubDhaka: {{ $deliverySubDhaka }},
                deliveryOutside: {{ $deliveryOutside }},
                subDhakaThanas: {{ json_encode($subDhakaThanas ?? []) }},
                allLocations: {{ json_encode($allLocations) }},
                allDistricts: {{ json_encode($allDistricts) }},
                selectedDistrict: '{{ old('district', $customer->district ?? 'Dhaka') }}',
                selectedThana: '{{ old('thana', $customer->thana ?? '') }}',
                zone: 'inside',

                init() {
                    this.$watch('selectedThana', () => this.syncZone());
                    this.syncZone();
                },

                get thanas() {
                    return this.allLocations[this.selectedDistrict] || [];
                },

                isSubDhaka(district, thana) {
                    if (!this.subDhakaThanas || !district || !thana) return false;
                    for (const [d, list] of Object.entries(this.subDhakaThanas)) {
                        if (d.toLowerCase() === district.toLowerCase() && Array.isArray(list)) {
                            return list.some(t => t.toLowerCase() === thana.toLowerCase());
                        }
                    }
                    return false;
                },

                syncZone() {
                    if (!this.selectedDistrict) {
                        this.zone = 'outside';
                        return;
                    }

                    // If thana is selected, check if it matches sub-dhaka thanas
                    if (this.selectedThana && this.isSubDhaka(this.selectedDistrict, this.selectedThana)) {
                        this.zone = 'sub_dhaka';
                        return;
                    }

                    // If district is Dhaka and thana is not sub-dhaka
                    if (this.selectedDistrict.toLowerCase() === 'dhaka') {
                        this.zone = 'inside';
                        return;
                    }

                    // All other districts & thanas
                    this.zone = 'outside';
                },

                onDistrictChange() {
                    // Reset selected thana if not part of newly chosen district
                    if (this.selectedThana && !this.thanas.includes(this.selectedThana)) {
                        this.selectedThana = '';
                    }
                    this.syncZone();
                },

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
                get total() { return Math.max(0, this.subtotal - this.discount) + this.deliveryCharge; },
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
                            this.couponMessage = `${data.code} applied! Discount: ৳${this.discount.toFixed(2)}`;
                        } else {
                            this.couponError = data.message || 'Invalid coupon code';
                            this.appliedCoupon = null;
                            this.discount = 0;
                        }
                    })
                    .catch(err => {
                        this.loadingCoupon = false;
                        this.couponError = 'Error applying coupon';
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
            <form method="POST" action="{{ route('shop.checkout.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="coupon_code" :value="appliedCoupon">

                @if(auth('customer')->check())
                    <div class="bg-brand/10 border border-brand/20 rounded-xl p-3 flex items-center justify-between text-xs text-brand-dark">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-circle text-base text-brand"></i>
                            <div>
                                <span class="font-bold">Ordering as {{ auth('customer')->user()->name }}</span>
                                <div class="text-[10px] text-gray-500">{{ auth('customer')->user()->phone ?? auth('customer')->user()->email }}</div>
                            </div>
                        </div>
                        <a href="{{ route('shop.customer.dashboard') }}" class="font-bold underline text-[11px]">My Account</a>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200/80 rounded-xl p-3 flex items-center justify-between text-xs text-blue-900">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-600"></i>
                            <span>Have a customer account?</span>
                        </div>
                        <a href="{{ route('shop.customer.login') }}" class="font-bold text-blue-700 hover:underline">
                            Sign In &rarr;
                        </a>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>

                {{-- Cascading District & Thana --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                            District (জেলা) *
                        </label>
                        <div class="relative">
                            <select name="district" x-model="selectedDistrict" @change="onDistrictChange()" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-white">
                                <option value="" disabled>Select District</option>
                                <template x-for="d in allDistricts" :key="d">
                                    <option :value="d" x-text="d" :selected="d === selectedDistrict"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                            Thana / Upazila (থানা) *
                        </label>
                        <div class="relative">
                            <select name="thana" x-model="selectedThana" @change="syncZone()" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-white">
                                <option value="" disabled>Select Thana</option>
                                <template x-for="t in thanas" :key="t">
                                    <option :value="t" x-text="t" :selected="t === selectedThana"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Full Delivery Address *</label>
                    <textarea name="address" rows="2" required placeholder="House no, Road no, Area details..."
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">{{ old('address', $customer->address ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                        <span>Delivery Area</span>
                        <span class="text-[11px] text-brand-dark font-semibold lowercase" x-text="selectedThana ? '(auto selected for ' + selectedThana + ', ' + selectedDistrict + ')' : '(auto selected for ' + selectedDistrict + ')'"></span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        {{-- Inside Dhaka --}}
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_zone" value="inside" x-model="zone" class="sr-only peer">
                            <div class="border rounded-xl p-2 text-center text-xs font-semibold border-gray-200 text-gray-600 hover:border-brand/40 peer-checked:border-brand peer-checked:bg-brand-light peer-checked:text-brand-dark transition-all">
                                <span class="block truncate">Inside Dhaka</span>
                                <span class="block text-[10px] font-bold mt-0.5">{{ $currency }}<span x-text="deliveryInside"></span></span>
                            </div>
                        </label>

                        {{-- Sub Dhaka --}}
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_zone" value="sub_dhaka" x-model="zone" class="sr-only peer">
                            <div class="border rounded-xl p-2 text-center text-xs font-semibold border-gray-200 text-gray-600 hover:border-brand/40 peer-checked:border-brand peer-checked:bg-brand-light peer-checked:text-brand-dark transition-all">
                                <span class="block truncate">Sub Dhaka</span>
                                <span class="block text-[10px] font-bold mt-0.5">{{ $currency }}<span x-text="deliverySubDhaka"></span></span>
                            </div>
                        </label>

                        {{-- Outside Dhaka --}}
                        <label class="cursor-pointer">
                            <input type="radio" name="delivery_zone" value="outside" x-model="zone" class="sr-only peer">
                            <div class="border rounded-xl p-2 text-center text-xs font-semibold border-gray-200 text-gray-600 hover:border-brand/40 peer-checked:border-brand peer-checked:bg-brand-light peer-checked:text-brand-dark transition-all">
                                <span class="block truncate">Outside Dhaka</span>
                                <span class="block text-[10px] font-bold mt-0.5">{{ $currency }}<span x-text="deliveryOutside"></span></span>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Payment Method</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['cash' => 'Cash on Delivery', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'card' => 'Card'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="{{ $val }}" {{ $val === 'cash' ? 'checked' : '' }} class="sr-only peer">
                            <div class="border rounded-xl px-2.5 py-2 text-center text-xs font-semibold border-gray-200 text-gray-600 hover:border-brand/40 peer-checked:border-brand peer-checked:bg-brand-light peer-checked:text-brand-dark transition-all">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Coupon Code Application --}}
                <div class="border-t border-gray-100 pt-3">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                        <i class="fas fa-tag text-brand mr-1"></i> Have a Promo / Coupon Code?
                    </label>
                    <div class="flex gap-2">
                        <input type="text" x-model="couponCode" placeholder="Enter coupon code"
                               :disabled="appliedCoupon !== null"
                               @keydown.enter.prevent="applyCoupon()"
                               class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono font-bold uppercase focus:outline-none focus:ring-2 focus:ring-brand uppercase bg-gray-50 disabled:bg-gray-100">
                        <button type="button" @click="appliedCoupon ? removeCoupon() : applyCoupon()"
                                :disabled="loadingCoupon || (!appliedCoupon && !couponCode.trim())"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all disabled:opacity-50"
                                :class="appliedCoupon ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200' : 'bg-gray-900 hover:bg-black text-white'">
                            <span x-show="!loadingCoupon" x-text="appliedCoupon ? 'Remove' : 'Apply'"></span>
                            <span x-show="loadingCoupon"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                    <div x-show="couponMessage" x-text="couponMessage" class="text-xs font-bold text-emerald-600 mt-1.5 flex items-center gap-1"></div>
                    <div x-show="couponError" x-text="couponError" class="text-xs font-bold text-red-600 mt-1.5 flex items-center gap-1"></div>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Subtotal</span><span>{{ $currency }} <span x-text="subtotal.toLocaleString()"></span></span>
                    </div>
                    <div x-show="discount > 0" class="flex justify-between text-emerald-600 font-semibold">
                        <span>Coupon Discount (<span x-text="appliedCoupon"></span>)</span>
                        <span>-{{ $currency }} <span x-text="discount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Delivery Charge</span><span>{{ $currency }} <span x-text="deliveryCharge.toLocaleString()"></span></span>
                    </div>
                    <div class="flex justify-between font-extrabold text-gray-900 text-base border-t border-gray-100 pt-2 mt-1">
                        <span>Total</span><span>{{ $currency }} <span x-text="total.toLocaleString()"></span></span>
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-brand hover:bg-brand-dark text-white py-3 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-md shadow-brand/20">
                    <i class="fas fa-check-circle text-xs"></i> Place Order
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
