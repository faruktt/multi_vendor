@extends('reseller.layouts.app')
@section('title', 'Checkout')
@section('heading', 'Checkout')

@section('content')
<div class="py-4" x-data="checkoutSummary()">
    <form method="POST" action="{{ route('reseller.orders.store') }}">
        @csrf
        <input type="hidden" name="coupon_code" :value="appliedCoupon">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl">

            {{-- Delivery & Customer Form --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="font-bold text-slate-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-indigo-500"></i> Customer & Delivery Information
                    </h3>

                    @if(isset($errors) && $errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-red-700 text-sm">
                            @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Customer / Recipient Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Customer full name" required
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Customer Phone Number *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required
                                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                        </div>
                    </div>

                    {{-- Cascading District & Thana --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">District (জেলা) *</label>
                            <div class="relative">
                                <select name="district" x-model="selectedDistrict" @change="onDistrictChange()" required
                                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="" disabled>Select District</option>
                                    @foreach($allDistricts as $d)
                                        <option value="{{ $d }}" {{ old('district', 'Dhaka') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Thana / Upazila (থানা) *</label>
                            <div class="relative">
                                <select name="thana" x-model="selectedThana" @change="syncZone()" required
                                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                    <option value="" disabled>Select Thana</option>
                                    <template x-for="t in thanas" :key="t">
                                        <option :value="t" x-text="t" :selected="t === selectedThana"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Area Selection Cards --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center justify-between">
                            <span>Delivery Area (ডেলিভারি এরিয়া) *</span>
                            <span class="text-[11px] text-indigo-600 font-medium" x-text="selectedThana ? '(Auto selected for ' + selectedThana + ', ' + selectedDistrict + ')' : '(Auto selected for ' + selectedDistrict + ')'"></span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            {{-- Inside Dhaka --}}
                            <label class="cursor-pointer" @click="zone = 'inside'">
                                <input type="radio" name="delivery_zone" value="inside" x-model="zone" class="sr-only peer">
                                <div class="border-2 rounded-xl p-3 text-center transition-all"
                                     :class="zone === 'inside'
                                         ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 font-bold shadow-xs'
                                         : 'border-slate-200 text-slate-600 hover:border-indigo-300'">
                                    <i class="fas fa-city block mb-1 text-xs"></i>
                                    <span class="block text-xs font-bold truncate">Inside Dhaka</span>
                                    <span class="block text-xs font-extrabold mt-0.5 text-indigo-600">৳<span x-text="deliveryInside"></span></span>
                                </div>
                            </label>

                            {{-- Sub Dhaka --}}
                            <label class="cursor-pointer" @click="zone = 'sub_dhaka'">
                                <input type="radio" name="delivery_zone" value="sub_dhaka" x-model="zone" class="sr-only peer">
                                <div class="border-2 rounded-xl p-3 text-center transition-all"
                                     :class="zone === 'sub_dhaka'
                                         ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 font-bold shadow-xs'
                                         : 'border-slate-200 text-slate-600 hover:border-indigo-300'">
                                    <i class="fas fa-map-marked-alt block mb-1 text-xs"></i>
                                    <span class="block text-xs font-bold truncate">Sub Dhaka</span>
                                    <span class="block text-xs font-extrabold mt-0.5 text-indigo-600">৳<span x-text="deliverySubDhaka"></span></span>
                                </div>
                            </label>

                            {{-- Outside Dhaka --}}
                            <label class="cursor-pointer" @click="zone = 'outside'">
                                <input type="radio" name="delivery_zone" value="outside" x-model="zone" class="sr-only peer">
                                <div class="border-2 rounded-xl p-3 text-center transition-all"
                                     :class="zone === 'outside'
                                         ? 'border-indigo-600 bg-indigo-50/70 text-indigo-700 font-bold shadow-xs'
                                         : 'border-slate-200 text-slate-600 hover:border-indigo-300'">
                                    <i class="fas fa-truck block mb-1 text-xs"></i>
                                    <span class="block text-xs font-bold truncate">Outside Dhaka</span>
                                    <span class="block text-xs font-extrabold mt-0.5 text-indigo-600">৳<span x-text="deliveryOutside"></span></span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Delivery Address (বিস্তারিত ঠিকানা) *</label>
                        <textarea name="address" rows="2" required placeholder="House no, Road no, Area details..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 resize-none">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Order Note (Optional)</label>
                        <textarea name="note" rows="2" placeholder="Any special packaging or customer delivery instructions..."
                                  class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 resize-none">{{ old('note') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-md shadow-indigo-200/60 flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-check-circle"></i> Place Reseller Order
                    </button>
                </div>
            </div>

            {{-- Order Summary & Price Customization --}}
            <div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 sticky top-20">
                    <h3 class="font-bold text-slate-700 mb-4">Set Your Selling Price</h3>
                    
                    <div class="space-y-4">
                        @foreach($lines as $line)
                            <div class="flex flex-col gap-2 border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-50 flex-shrink-0">
                                        @if($line['image_url'])
                                            <img src="{{ $line['image_url'] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fas fa-image text-xs"></i></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-700 truncate">{{ $line['product']->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $line['qty'] }} items (Your cost: ৳{{ number_format($line['price'], 2) }} each)</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Customer Price</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 text-sm">৳</span>
                                        <input type="number" name="selling_prices[{{ $line['key'] }}]" step="0.01" min="{{ $line['price'] }}"
                                               x-model.number="sellingPrices['{{ $line['key'] }}']"
                                               value="{{ old('selling_prices.' . $line['key'], $line['price']) }}" required
                                               class="w-24 border border-slate-200 rounded-lg px-2 py-1 text-sm font-bold text-right focus:outline-none focus:border-indigo-400">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Coupon Code Box --}}
                    <div class="border-t border-slate-100 mt-4 pt-3">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">
                            <i class="fas fa-ticket-alt text-indigo-500 mr-1"></i> Reseller Promo / Coupon Code
                        </label>
                        <div class="flex gap-2">
                            <input type="text" x-model="couponCode" placeholder="Enter coupon"
                                   :disabled="appliedCoupon !== null"
                                   @keydown.enter.prevent="applyCoupon()"
                                   class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold uppercase focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 disabled:bg-slate-100">
                            <button type="button" @click="appliedCoupon ? removeCoupon() : applyCoupon()"
                                    :disabled="loadingCoupon || (!appliedCoupon && !couponCode.trim())"
                                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all disabled:opacity-50"
                                    :class="appliedCoupon ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-200' : 'bg-indigo-600 hover:bg-indigo-700 text-white'">
                                <span x-show="!loadingCoupon" x-text="appliedCoupon ? 'Remove' : 'Apply'"></span>
                                <span x-show="loadingCoupon"><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                        <div x-show="couponMessage" x-text="couponMessage" class="text-xs font-bold text-emerald-600 mt-1.5 flex items-center gap-1"></div>
                        <div x-show="couponError" x-text="couponError" class="text-xs font-bold text-red-600 mt-1.5 flex items-center gap-1"></div>
                    </div>

                    <div class="border-t border-slate-100 mt-4 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Your Base Cost</span>
                            <span class="font-bold text-slate-700">৳<span x-text="baseCost.toFixed(2)"></span></span>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Customer Subtotal</span>
                            <span class="font-bold text-slate-700">৳<span x-text="customerSubtotal.toFixed(2)"></span></span>
                        </div>

                        <div x-show="discount > 0" class="flex justify-between items-center text-sm text-emerald-600 font-semibold">
                            <span>Coupon Discount (<span x-text="appliedCoupon"></span>)</span>
                            <span>-৳<span x-text="discount.toFixed(2)"></span></span>
                        </div>

                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">
                                Delivery Charge (<span class="font-semibold text-indigo-600" x-text="zone === 'inside' ? 'Inside Dhaka' : (zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka')"></span>)
                            </span>
                            <span class="font-bold text-indigo-600">+৳<span x-text="deliveryCharge.toFixed(2)"></span></span>
                        </div>

                        <div class="flex justify-between items-center border-t border-slate-100 pt-2">
                            <div>
                                <span class="font-bold text-slate-800 text-sm block">Total Charged to Customer</span>
                                <span class="text-[10px] text-slate-400 font-medium">(Subtotal + Delivery Charge = COD Amount)</span>
                            </div>
                            <span class="text-xl font-black text-indigo-700">৳<span x-text="total.toFixed(2)"></span></span>
                        </div>

                        <div class="flex justify-between items-center mt-2 text-emerald-700 bg-emerald-50 px-3 py-2.5 rounded-xl border border-emerald-100">
                            <div>
                                <span class="font-bold text-xs block">Your Estimated Profit</span>
                                <span class="text-[10px] text-emerald-600" x-show="discount > 0">Includes ৳<span x-text="discount.toFixed(2)"></span> coupon savings!</span>
                            </div>
                            <span class="font-black text-base">+৳<span x-text="profit.toFixed(2)"></span></span>
                        </div>

                        <p class="text-[11px] text-slate-400 mt-3 text-center leading-relaxed">
                            Profit will be credited to your available balance once the order is marked as completed. Delivery charge is collected from customer for courier shipping.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
function checkoutSummary() {
    return {
        deliveryInside: {{ $deliveryInside }},
        deliverySubDhaka: {{ $deliverySubDhaka }},
        deliveryOutside: {{ $deliveryOutside }},
        subDhakaThanas: @json($subDhakaThanas ?? []),
        allLocations: @json($allLocations),
        allDistricts: @json($allDistricts),
        selectedDistrict: '{{ old('district', 'Dhaka') }}',
        selectedThana: '{{ old('thana', '') }}',
        zone: '{{ old('delivery_zone', 'inside') }}',

        sellingPrices: {
            @foreach($lines as $line)
                '{{ $line['key'] }}': {{ old('selling_prices.' . $line['key'], $line['price']) }},
            @endforeach
        },
        items: [
            @foreach($lines as $line)
                {
                    key: '{{ $line['key'] }}',
                    cost: {{ $line['price'] * $line['qty'] }},
                    unitCost: {{ $line['price'] }},
                    qty: {{ $line['qty'] }}
                },
            @endforeach
        ],

        couponCode: '',
        appliedCoupon: null,
        discount: 0,
        couponMessage: '',
        couponError: '',
        loadingCoupon: false,

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

            if (this.selectedThana && this.isSubDhaka(this.selectedDistrict, this.selectedThana)) {
                this.zone = 'sub_dhaka';
                return;
            }

            if (this.selectedDistrict.toLowerCase() === 'dhaka') {
                this.zone = 'inside';
                return;
            }

            this.zone = 'outside';
        },

        onDistrictChange() {
            if (this.selectedThana && !this.thanas.includes(this.selectedThana)) {
                this.selectedThana = '';
            }
            this.syncZone();
        },

        get deliveryCharge() {
            if (this.zone === 'inside') return parseFloat(this.deliveryInside) || 0;
            if (this.zone === 'sub_dhaka') return parseFloat(this.deliverySubDhaka) || 0;
            return parseFloat(this.deliveryOutside) || 0;
        },

        get customerSubtotal() {
            let sum = 0;
            this.items.forEach(item => {
                const val = parseFloat(this.sellingPrices[item.key]);
                const unitPrice = (!isNaN(val) && val >= 0) ? val : item.unitCost;
                sum += unitPrice * item.qty;
            });
            return sum;
        },

        get baseCost() {
            return this.items.reduce((acc, item) => acc + item.cost, 0);
        },

        get total() {
            return Math.max(0, this.customerSubtotal - this.discount) + this.deliveryCharge;
        },

        get profit() {
            return Math.max(0, (this.customerSubtotal - this.baseCost) + this.discount);
        },

        applyCoupon() {
            if (!this.couponCode.trim()) return;
            this.loadingCoupon = true;
            this.couponError = '';
            this.couponMessage = '';

            fetch('{{ route('reseller.orders.coupon') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: this.couponCode
                })
            })
            .then(r => r.json())
            .then(data => {
                this.loadingCoupon = false;
                if (data.success) {
                    this.appliedCoupon = data.code;
                    this.discount = parseFloat(data.discount_amount) || 0;
                    this.couponMessage = `${data.code} applied! Saved ৳${this.discount.toFixed(2)}`;
                } else {
                    this.couponError = data.message || 'Invalid coupon code';
                    this.appliedCoupon = null;
                    this.discount = 0;
                }
            })
            .catch(err => {
                this.loadingCoupon = false;
                this.couponError = 'Error validating coupon';
            });
        },

        removeCoupon() {
            this.appliedCoupon = null;
            this.discount = 0;
            this.couponCode = '';
            this.couponMessage = '';
            this.couponError = '';
        },

        init() {
            this.$watch('selectedThana', () => this.syncZone());
            this.syncZone();
        }
    }
}
</script>
@endsection
