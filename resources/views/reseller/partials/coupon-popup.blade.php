@if(isset($popupCoupon) && $popupCoupon)
@php
    $discountLabel = $popupCoupon->discount_type === 'percentage'
        ? (int)$popupCoupon->discount_amount . '% OFF'
        : '৳' . (int)$popupCoupon->discount_amount . ' OFF';
@endphp

<div x-data="resellerCouponPopupModal({{ Js::from([
    'id'               => $popupCoupon->id,
    'code'             => $popupCoupon->code,
    'name'             => $popupCoupon->name,
    'discount_label'   => $discountLabel,
    'min_order_amount' => (float)($popupCoupon->min_order_amount ?? 0),
    'shop_url'         => route('reseller.products.index'),
]) }})"
     x-init="initPopup()"
     x-cloak
     x-show="isOpen"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-300"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="closePopup()">

    {{-- Dark Backdrop overlay --}}
    <div class="fixed inset-0" @click="closePopup()"></div>

    {{-- Modal Box Card --}}
    <div class="relative z-10 w-full max-w-[560px] bg-[#F7EFE5] rounded-3xl shadow-2xl overflow-visible flex flex-col sm:flex-row min-h-[420px] transition-all duration-300 border border-amber-900/10"
         @click.stop
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3">

        {{-- Floating Round Close Button --}}
        <button type="button"
                @click="closePopup()"
                class="absolute -top-3 -right-3 sm:-top-3.5 sm:-right-3.5 w-8 h-8 rounded-full bg-white text-gray-700 hover:text-black flex items-center justify-center shadow-lg border border-gray-200 hover:scale-110 active:scale-95 transition-all cursor-pointer z-50"
                title="Close popup">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        {{-- Left Column: Visual Image (hidden on very small screens, visible on sm+) --}}
        <div class="sm:w-5/12 relative hidden sm:block overflow-hidden rounded-l-3xl">
            <img src="{{ $popupCoupon->image_url }}"
                 alt="{{ $popupCoupon->name ?? 'Special Reseller Offer' }}"
                 class="w-full h-full object-cover object-center" />
            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-[#F7EFE5]/15"></div>
        </div>

        {{-- Right Column: Content area --}}
        <div class="flex-1 p-6 sm:p-7 flex flex-col justify-between items-center text-center">

            {{-- Top Badge --}}
            <div class="mb-1">
                <span class="inline-block px-3 py-1 rounded-full bg-[#5BA89E]/15 text-[#3D7A71] text-[10.5px] font-black uppercase tracking-widest">
                    <i class="fas fa-tag mr-1 text-[10px]"></i> Reseller Exclusive Promo
                </span>
            </div>

            {{-- Headline & Subtitle --}}
            <div class="my-2">
                <h3 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight leading-none uppercase">
                    GET <span class="text-[#5BA89E]" x-text="coupon.discount_label"></span> NOW
                </h3>
                <p class="text-xs sm:text-[13px] text-gray-600 font-medium leading-relaxed mt-2.5 max-w-[260px] mx-auto"
                   x-text="coupon.name ? coupon.name : 'অর্ডার করার সময় এই কুপন কোড ব্যবহার করে অতিরিক্ত ডিসকাউন্ট বা প্রফিট উপভোগ করুন!'">
                </p>
                <template x-if="coupon.min_order_amount > 0">
                    <p class="text-[11px] text-gray-500 font-semibold mt-1">
                        * On orders above ৳<span x-text="Math.round(coupon.min_order_amount)"></span>
                    </p>
                </template>
            </div>

            {{-- Coupon Code Box (with 1-click Copy) --}}
            <div class="w-full my-3">
                <div class="relative w-full rounded-2xl bg-white/95 border border-[#D5C8B8] shadow-inner p-1.5 flex items-center justify-between gap-2 group hover:border-[#5BA89E] transition-colors">
                    <div class="flex-1 text-left pl-3 flex items-center gap-2">
                        <i class="fas fa-ticket-alt text-[#5BA89E] text-xs"></i>
                        <span class="font-mono font-black text-sm tracking-widest text-gray-900 select-all" x-text="coupon.code"></span>
                    </div>
                    <button type="button"
                            @click="copyCouponCode()"
                            class="px-3.5 py-1.5 rounded-xl bg-[#5BA89E] hover:bg-[#4a9187] active:scale-95 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs transition-all cursor-pointer">
                        <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                        <span x-text="copied ? 'COPIED!' : 'COPY'">COPY</span>
                    </button>
                </div>
                <div x-show="copied" x-cloak class="text-[11px] font-bold text-emerald-700 mt-1.5">
                    ✓ কুপন কোড কপি হয়েছে! অর্ডার চেকআউটে পেস্ট করুন।
                </div>
            </div>

            {{-- Bottom Actions --}}
            <div class="w-full flex items-center justify-between pt-3 mt-auto gap-3">
                <button type="button"
                        @click="closePopup()"
                        class="text-xs font-black tracking-wider text-gray-500 hover:text-gray-900 transition-colors uppercase py-2 px-1 cursor-pointer">
                    NO, THANKS!
                </button>

                <button type="button"
                        @click="claimAndShop()"
                        class="px-5 py-2.5 rounded-full bg-[#5BA89E] hover:bg-[#4a9187] active:scale-95 text-white text-xs font-black tracking-wide shadow-sm hover:shadow-md transition-all uppercase flex items-center gap-1.5 cursor-pointer">
                    <span x-text="'GET ' + coupon.discount_label"></span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
function resellerCouponPopupModal(couponData) {
    return {
        isOpen: false,
        copied: false,
        coupon: couponData,

        initPopup() {
            const storageKey = 'fayaz_reseller_coupon_dismissed_' + this.coupon.id;

            @if(session('reseller_just_logged_in'))
                sessionStorage.removeItem(storageKey);
            @endif

            if (sessionStorage.getItem(storageKey)) {
                return;
            }

            // 800ms delay for smooth page entry
            setTimeout(() => {
                this.isOpen = true;
            }, 800);
        },

        closePopup() {
            this.isOpen = false;
            const storageKey = 'fayaz_reseller_coupon_dismissed_' + this.coupon.id;
            sessionStorage.setItem(storageKey, '1');
        },

        copyCouponCode() {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(this.coupon.code);
            } else {
                const temp = document.createElement('input');
                temp.value = this.coupon.code;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
            }
            this.copied = true;
            setTimeout(() => {
                this.copied = false;
            }, 3500);
        },

        claimAndShop() {
            this.copyCouponCode();
            this.closePopup();
            if (window.location.href.includes('/reseller/products')) {
                const target = document.querySelector('.grid, main');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            } else if (this.coupon.shop_url) {
                window.location.href = this.coupon.shop_url;
            }
        }
    };
}
</script>
@endif
