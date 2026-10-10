@extends('layouts.app')
@section('title', 'Homepage Banners')
@section('heading', 'Homepage Banners')

@section('content')

{{-- ══ HEADER INFO ══ --}}
<div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-2xl p-5 mb-5 text-white">
    <div class="flex items-start gap-4">
        <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-images text-white text-lg"></i>
        </div>
        <div>
            <h2 class="font-bold text-[15px] mb-1">Homepage Banners</h2>
            <p class="text-slate-300 text-[12.5px] leading-relaxed">
                <strong class="text-white">Slider</strong> images rotate at the top of the homepage — add as many as you like.
                <strong class="text-white">Mid-page banner</strong> is a single strip banner shown further down the page.
                <strong class="text-white">Promo Card 1 &amp; 2</strong> are the two small cards beside the slider.
                Only active images are shown; inactive banners are hidden from the storefront but not deleted.
            </p>
        </div>
    </div>
</div>

{{-- ══ UPLOAD ══ --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
            <i class="fas fa-upload text-emerald-600 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-semibold text-slate-800">Add a Banner</h2>
            <p class="text-[11.5px] text-slate-400">JPG or PNG, up to 4MB</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="p-6">
        @csrf
        <div class="flex items-center gap-4 mb-4">
            <div class="w-28 h-16 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 flex-shrink-0"
                 id="new-banner-preview-wrap">
                <i class="fas fa-image text-slate-300 text-xl"></i>
            </div>
            <div class="flex-1">
                <label class="cursor-pointer inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[12.5px] font-medium px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-upload text-[11px]"></i> Choose Image
                    <input type="file" name="image" accept="image/*" required class="hidden" id="new-banner-input" onchange="previewNewBanner(this)">
                </label>
            </div>
            <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white px-6 py-2.5 rounded-xl text-[13.5px] font-semibold shadow-sm shadow-emerald-200 transition-colors flex items-center gap-2 flex-shrink-0">
                <i class="fas fa-plus text-[12px]"></i> Add Banner
            </button>
        </div>
        @error('image')<p class="text-[12px] text-red-500 mb-3">{{ $message }}</p>@enderror
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-[12px] font-medium text-slate-600 mb-1.5">Show as</label>
                <select name="position" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    <option value="hero">Homepage Slider (1376 × 768 px)</option>
                    <option value="mid">Mid-page Banner (1400 × 400 px)</option>
                    <option value="promo1">Promo Card 1 (Top) — 768 × 768 px (1:1 Square)</option>
                    <option value="promo2">Promo Card 2 (Bottom) — 768 × 768 px (1:1 Square)</option>
                </select>
            </div>
            <div>
                <label class="block text-[12px] font-medium text-slate-600 mb-1.5">Title <span class="text-slate-400 font-normal">(admin-only)</span></label>
                <input type="text" name="title" placeholder="e.g. Eid Offer Banner"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>
            <div>
                <label class="block text-[12px] font-medium text-slate-600 mb-1.5">Link <span class="text-slate-400 font-normal">(on click)</span></label>
                <input type="text" name="link" placeholder="e.g. /shop or https://..."
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>
        </div>
    </form>
</div>

{{-- ══ SLIDER BANNERS ══ --}}
@include('admin.partials.banner-list', ['group' => 'hero', 'list' => $heroBanners, 'heading' => 'Homepage Slider (1376 × 768 px)', 'emptyText' => 'No slider banners yet. Upload one above — until then that section is hidden on the storefront.'])

{{-- ══ MID-PAGE BANNER ══ --}}
@include('admin.partials.banner-list', ['group' => 'mid', 'list' => $midBanners, 'heading' => 'Mid-page Banner (1400 × 400 px)', 'emptyText' => 'No mid-page banner yet. Upload one above — until then a text CTA is shown instead.'])

{{-- ══ PROMO CARD 1 ══ --}}
@include('admin.partials.banner-list', ['group' => 'promo1', 'list' => $promo1Banners, 'heading' => 'Promo Card 1 (Top) — 768 × 768 px (1:1 Square)', 'emptyText' => 'No image yet. Upload one above — until then this card is hidden on the storefront.'])

{{-- ══ PROMO CARD 2 ══ --}}
@include('admin.partials.banner-list', ['group' => 'promo2', 'list' => $promo2Banners, 'heading' => 'Promo Card 2 (Bottom) — 768 × 768 px (1:1 Square)', 'emptyText' => 'No image yet. Upload one above — until then this card is hidden on the storefront.'])

<form id="reorder-form" method="POST" action="{{ route('admin.banners.reorder') }}" class="hidden">
    @csrf
    <div id="reorder-inputs"></div>
</form>

@endsection

@push('scripts')
<script>
function previewNewBanner(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('new-banner-preview-wrap').innerHTML =
                `<img src="${e.target.result}" class="w-full h-full object-cover" alt="preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

const bannerOrders = {
    hero: @json($heroBanners->pluck('id')),
    mid: @json($midBanners->pluck('id')),
    promo1: @json($promo1Banners->pluck('id')),
    promo2: @json($promo2Banners->pluck('id')),
};

function moveBanner(group, id, dir) {
    const order = [...bannerOrders[group]];
    const from = order.indexOf(id);
    const to = from + dir;
    if (to < 0 || to >= order.length) return;
    [order[from], order[to]] = [order[to], order[from]];

    const container = document.getElementById('reorder-inputs');
    container.innerHTML = '';
    order.forEach(function (bannerId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order[]';
        input.value = bannerId;
        container.appendChild(input);
    });
    document.getElementById('reorder-form').submit();
}
</script>
@endpush
