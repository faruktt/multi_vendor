@extends('layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('heading', isset($product) ? 'Edit: ' . $product->name : 'Add New Product')

@section('content')
<div x-data="productForm()">
<form method="POST"
      action="{{ isset($product) ? route('branch.products.update', [$branch, $product]) : route('branch.products.store', $branch) }}"
      enctype="multipart/form-data"
      @submit.prevent="submitForm($event)">
    @csrf
    @isset($product) @method('POST') @endisset

<div class="flex flex-col xl:flex-row gap-5">

    {{-- ══ LEFT: Form Fields ════════════════════════════════════════ --}}
    <div class="flex-1 space-y-4">

        {{-- Basic Info --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-info text-blue-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Basic Information</h3>
            </div>
            <div class="space-y-3.5">
                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                           placeholder="e.g. Premium Cotton T-Shirt"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                </div>

                {{-- Category + Unit --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-data="unitField('{{ old('unit', $product->unit ?? 'pcs') }}')">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Unit</label>
                        @php
                        $units = [
                            'Quantity'  => ['pcs' => 'Piece (pcs)', 'dz' => 'Dozen (dz)', 'pair' => 'Pair', 'set' => 'Set', 'box' => 'Box', 'ctn' => 'Carton (ctn)', 'pkt' => 'Packet (pkt)'],
                            'Weight'    => ['kg' => 'Kilogram (kg)', 'gm' => 'Gram (gm)', 'mg' => 'Milligram (mg)', 'ton' => 'Ton'],
                            'Volume'    => ['ltr' => 'Liter (ltr)', 'ml' => 'Milliliter (ml)'],
                            'Length'    => ['mtr' => 'Meter (mtr)', 'cm' => 'Centimeter (cm)', 'ft' => 'Feet (ft)', 'inch' => 'Inch'],
                            'Other'     => ['btl' => 'Bottle (btl)', 'roll' => 'Roll', 'bag' => 'Bag', 'sht' => 'Sheet', 'custom' => '— Custom —'],
                        ];
                        @endphp
                        <select x-model="selected" @change="onSelect()"
                                :name="selected !== 'custom' ? 'unit' : '_unit_ignore'"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                            @foreach($units as $group => $options)
                            <optgroup label="{{ $group }}">
                                @foreach($options as $val => $label)
                                <option value="{{ $val }}" {{ old('unit', $product->unit ?? 'pcs') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        <input x-show="selected === 'custom'" x-cloak
                               type="text" name="unit" x-model="custom"
                               placeholder="Type custom unit..."
                               class="mt-2 w-full border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>
                </div>

                {{-- Barcode + SKU --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}"
                           placeholder="Leave blank to auto-generate"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors font-mono">
                </div>

                {{-- Short Description --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Short Description <span class="text-slate-400 font-normal">(image-এর নিচে দেখাবে)</span></label>
                    <textarea name="short_description" rows="2" maxlength="500" placeholder="সংক্ষিপ্ত বিবরণ — product page-এ image-এর নিচে দেখাবে..."
                              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors resize-none">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Description</label>
                    <textarea name="description" rows="3" placeholder="Optional product description..."
                              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors resize-none">{{ old('description', $product->description ?? '') }}</textarea>
                </div>

                {{-- Facebook Reel / Video URL --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="fab fa-facebook text-blue-600 text-sm"></i>
                            <span>Facebook Reel / Video URL</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-normal">Product details-এ description-এর ডানপাশে দেখাবে</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                            <i class="fab fa-facebook text-blue-500"></i>
                        </span>
                        <input type="url" name="facebook_video_url" value="{{ old('facebook_video_url', $product->facebook_video_url ?? '') }}"
                               placeholder="https://www.facebook.com/reel/... অথবা https://www.facebook.com/watch/?v=..."
                               class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing & Stock --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-tags text-emerald-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Pricing & Stock</h3>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Selling Price (৳) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">৳</span>
                        <input type="number" name="price" step="0.01" min="0"
                               value="{{ old('price', $product->price ?? '') }}" required
                               x-model.number="price"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center justify-between">
                        <span>Old Price (৳)</span>
                        <span class="text-[10px] text-amber-600 font-medium">কাটা দেখাবে</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">৳</span>
                        <input type="number" name="old_price" step="0.01" min="0"
                               value="{{ old('old_price', $product->old_price ?? '') }}"
                               placeholder="পূর্বের মূল্য (ঐচ্ছিক)"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cost Price (৳) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">৳</span>
                        <input type="number" name="cost_price" step="0.01" min="0"
                               value="{{ old('cost_price', $product->cost_price ?? '') }}" required
                               x-model.number="costPrice"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Reseller Price (৳)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">৳</span>
                        <input type="number" name="reseller_price" step="0.01" min="0"
                               value="{{ old('reseller_price', $product->reseller_price ?? '') }}"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>

                {{-- Profit Indicator --}}
                <div class="col-span-2 sm:col-span-4 flex items-center gap-3 bg-slate-50 rounded-xl px-4 py-2.5 border border-slate-100">
                    <i class="fas fa-chart-line text-emerald-500 text-sm"></i>
                    <div class="flex-1 text-xs text-slate-500">
                        Profit:
                        <span class="font-bold ml-1"
                              :class="profit > 0 ? 'text-emerald-600' : 'text-red-500'"
                              x-text="profit > 0 ? '+৳' + profit.toFixed(0) + ' (' + profitPct + '%)' : '—'"></span>
                    </div>
                    <div class="text-xs text-slate-400" x-show="variants.length === 0">
                        Margin: <span class="font-semibold text-slate-600" x-text="profitPct + '%'"></span>
                    </div>
                </div>

                <div x-show="variants.length === 0" class="col-span-2 sm:col-span-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Opening Stock</label>
                    <input type="number" name="stock_qty" min="0"
                           value="{{ old('stock_qty', $product->stock_qty ?? 0) }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                </div>
            </div>
        </div>

        {{-- Variants --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-layer-group text-purple-600 text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-700 text-sm">Product Variants</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Color + Size combination-এ আলাদা price ও stock</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.warehouse.variant-attributes.index', 'warehouse') }}"
                       target="_blank"
                       class="text-[11px] text-blue-500 hover:text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
                        <i class="fas fa-sliders text-[9px]"></i> Manage Colors & Sizes
                    </a>
                    <button type="button" @click="addVariant()"
                            class="flex items-center gap-1.5 text-purple-600 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors">
                        <i class="fas fa-plus text-[10px]"></i> Add Variant
                    </button>
                </div>
            </div>

            {{-- Color & Size lists passed from controller --}}
            @php
                $colorsJson = $colors->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'hex' => $c->hex_code])->values();
                $sizesJson  = $sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values();
            @endphp

            {{-- Variant list --}}
            <div class="space-y-2.5">
                <template x-for="(v, i) in variants" :key="i">
                    <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/50 hover:border-purple-200 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-purple-600 text-xs font-bold" x-text="i + 1"></span>
                            </div>
                            <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-2.5">

                                {{-- Color Select --}}
                                <div>
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Color</label>
                                    <div class="relative">
                                        <select x-model="v.color_id" @change="onColorChange(v)"
                                                class="w-full border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white appearance-none">
                                            <option value="">— Select —</option>
                                            <template x-for="c in availableColors" :key="c.id">
                                                <option :value="c.id" x-text="c.name"></option>
                                            </template>
                                        </select>
                                        {{-- color swatch dot --}}
                                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full border border-slate-300 pointer-events-none"
                                              :style="getColorHex(v.color_id) ? 'background-color:' + getColorHex(v.color_id) : 'background:#e2e8f0'"></span>
                                    </div>
                                </div>

                                {{-- Size Select --}}
                                <div>
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Size</label>
                                    <select x-model="v.size_id" @change="onSizeChange(v)"
                                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                                        <option value="">— Select —</option>
                                        <template x-for="s in availableSizes" :key="s.id">
                                            <option :value="s.id" x-text="s.name"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Price --}}
                                <div>
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Price (৳)</label>
                                    <input type="number" x-model="v.price" step="0.01" min="0"
                                           placeholder="0.00"
                                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                                </div>

                                {{-- Stock --}}
                                <div>
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Stock</label>
                                    <input type="number" x-model="v.stock_qty" min="0"
                                           placeholder="0"
                                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                                </div>

                            </div>
                            <button type="button" @click="removeVariant(i)"
                                    class="w-7 h-7 rounded-lg border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-colors flex-shrink-0 mt-0.5">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>

                        {{-- Variant label preview --}}
                        <div class="mt-2 ml-10" x-show="v.color_label || v.size_label">
                            <span class="inline-flex items-center gap-1.5 bg-purple-50 border border-purple-200 text-purple-700 rounded-full px-3 py-1 text-xs font-semibold">
                                <span class="w-2.5 h-2.5 rounded-full border border-purple-300"
                                      :style="getColorHex(v.color_id) ? 'background-color:' + getColorHex(v.color_id) : 'background:#e2e8f0'"></span>
                                <span x-text="[v.color_label, v.size_label].filter(Boolean).join(' / ')"></span>
                            </span>
                        </div>
                    </div>
                </template>

                <div x-show="variants.length === 0"
                     class="border-2 border-dashed border-slate-200 rounded-xl py-8 text-center text-slate-400">
                    <i class="fas fa-layer-group text-2xl mb-2 block text-slate-300"></i>
                    <p class="text-sm">No variants — product has single price &amp; stock</p>
                </div>
            </div>

            <input type="hidden" name="variants" :value="JSON.stringify(variants)">
        </div>
    </div>

    {{-- ══ RIGHT: Image + Summary ═══════════════════════════════════ --}}
    <div class="w-full xl:w-72 flex-shrink-0 space-y-4">

        {{-- ── Multiple Image Uploader ─────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5"
             x-data="multiImageUploader({{ json_encode(isset($product) ? $product->images_list : []) }})">

            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center">
                        <i class="fas fa-image text-rose-500 text-xs"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-700 text-sm">Product Images</h3>
                        <p class="text-[10.5px] text-slate-400">Max 10 · 2MB each</p>
                    </div>
                </div>
                <button type="button" @click="$refs.fileInput.click()"
                        class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 px-2.5 py-1.5 rounded-lg font-semibold flex items-center gap-1 transition-colors">
                    <i class="fas fa-plus text-[10px]"></i> Add
                </button>
            </div>

            {{-- Hidden inputs for removes (edit mode) --}}
            <template x-for="fn in removedImages" :key="fn">
                <input type="hidden" name="remove_images[]" :value="fn">
            </template>

            {{-- Image grid --}}
            <div class="grid grid-cols-2 gap-2 mb-3">
                {{-- Existing saved images (edit mode) --}}
                <template x-for="(img, idx) in savedImages" :key="'saved-'+idx">
                    <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group">
                        <img :src="'{{ asset('uploads/products/') }}/' + img"
                             class="w-full h-full object-cover">
                        {{-- Primary badge --}}
                        <span x-show="idx === 0"
                              class="absolute bottom-1 left-1 text-[9px] bg-blue-600 text-white px-1.5 py-0.5 rounded-full font-semibold">
                            Main
                        </span>
                        {{-- Remove button --}}
                        <button type="button" @click="removeSaved(idx)"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                            <i class="fas fa-times text-[9px]"></i>
                        </button>
                    </div>
                </template>

                {{-- New files (previews) --}}
                <template x-for="(file, idx) in newFiles" :key="'new-'+idx">
                    <div class="relative aspect-square rounded-xl overflow-hidden border border-blue-200 group">
                        <img :src="file.preview" class="w-full h-full object-cover">
                        <span class="absolute bottom-1 left-1 text-[9px] bg-emerald-500 text-white px-1.5 py-0.5 rounded-full font-semibold">New</span>
                        <button type="button" @click="removeNew(idx)"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow">
                            <i class="fas fa-times text-[9px]"></i>
                        </button>
                    </div>
                </template>

                {{-- Upload placeholder --}}
                <button type="button" @click="$refs.fileInput.click()"
                        x-show="savedImages.length + newFiles.length < 10"
                        class="aspect-square rounded-xl border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50/50 flex flex-col items-center justify-center gap-1.5 text-slate-400 hover:text-blue-500 transition-colors group">
                    <i class="fas fa-plus text-lg group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-medium">Upload</span>
                </button>
            </div>

            {{-- Count indicator --}}
            <p class="text-[11px] text-slate-400 text-center"
               x-text="(savedImages.length + newFiles.length) + ' / 10 images'"></p>

            {{-- Hidden multiple file input --}}
            <input type="file" name="images[]" accept="image/*" multiple x-ref="fileInput"
                   class="hidden" @change="addFiles($event)">
        </div>

        {{-- Quick Summary Card --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 text-white shadow-md shadow-blue-200/40">
            <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide mb-3">Summary</p>
            <div class="space-y-2.5">
                <div class="flex justify-between items-center">
                    <span class="text-blue-200 text-xs">Selling Price</span>
                    <span class="font-bold text-sm">৳<span x-text="price > 0 ? Number(price).toLocaleString() : '—'"></span></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-blue-200 text-xs">Cost Price</span>
                    <span class="font-semibold text-sm text-blue-100">৳<span x-text="costPrice > 0 ? Number(costPrice).toLocaleString() : '—'"></span></span>
                </div>
                <div class="border-t border-blue-500 pt-2.5 flex justify-between items-center">
                    <span class="text-blue-200 text-xs">Est. Profit</span>
                    <span class="font-bold text-emerald-300 text-sm"
                          x-text="profit > 0 ? '+৳' + profit.toFixed(0) + ' (' + profitPct + '%)' : '—'"></span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-blue-200">Variants</span>
                    <span class="font-semibold" x-text="variants.length > 0 ? variants.length + ' added' : 'None'"></span>
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="space-y-2.5">
            <button type="submit" :disabled="submitting"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white py-3 rounded-2xl font-bold text-sm transition-all shadow-md shadow-blue-200/60 active:scale-[0.98] flex items-center justify-center gap-2">
                <span x-show="!submitting" class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ isset($product) ? 'Update Product' : 'Save Product' }}
                </span>
                <span x-show="submitting" class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Saving...
                </span>
            </button>
            <a href="{{ route('branch.products.index', $branch) }}"
               class="w-full border border-slate-200 text-slate-600 py-2.5 rounded-2xl text-sm font-medium hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Cancel
            </a>
        </div>
    </div>

</div>{{-- flex --}}
</form>
</div>{{-- x-data --}}
@endsection

@push('scripts')
<script>
/* ── Unit dropdown with custom option ── */
const knownUnits = ['pcs','dz','pair','set','box','ctn','pkt','kg','gm','mg','ton','ltr','ml','mtr','cm','ft','inch','btl','roll','bag','sht'];

function unitField(initial) {
    return {
        selected: knownUnits.includes(initial) ? initial : (initial ? 'custom' : 'pcs'),
        custom: knownUnits.includes(initial) ? '' : initial,
        onSelect() { if (this.selected !== 'custom') this.custom = ''; }
    };
}

function productForm() {
    return {
        variants: @json(isset($product) ? $product->variants : []),
        price:     {{ old('price', $product->price ?? 0) }},
        costPrice: {{ old('cost_price', $product->cost_price ?? 0) }},
        submitting: false,

        // Color & Size master lists (from controller)
        availableColors: @json($colorsJson ?? []),
        availableSizes:  @json($sizesJson ?? []),

        get profit() {
            return Math.max(0, this.price - this.costPrice);
        },
        get profitPct() {
            if (!this.price) return 0;
            return Math.round(((this.price - this.costPrice) / this.price) * 100);
        },

        addVariant() {
            this.variants.push({
                id: null,
                variant_name: '',
                color_id: '',
                size_id: '',
                color_label: '',
                size_label: '',
                price: '',
                cost_price: '',
                stock_qty: 0
            });
        },
        removeVariant(i) {
            this.variants.splice(i, 1);
        },

        // Called when color dropdown changes — store label snapshot
        onColorChange(v) {
            const found = this.availableColors.find(c => String(c.id) === String(v.color_id));
            v.color_label = found ? found.name : '';
        },

        // Called when size dropdown changes — store label snapshot
        onSizeChange(v) {
            const found = this.availableSizes.find(s => String(s.id) === String(v.size_id));
            v.size_label = found ? found.name : '';
        },

        // Return hex color for a given color_id (for swatch preview)
        getColorHex(colorId) {
            if (!colorId) return null;
            const found = this.availableColors.find(c => String(c.id) === String(colorId));
            return found ? found.hex : null;
        },

        async submitForm(e) {
            this.submitting = true;
            const form = e.target;

            // Build FormData from all form fields (_token, _method, text inputs, hidden inputs…)
            const fd = new FormData(form);

            // DataTransfer file-input sync is unreliable — inject File objects directly instead
            fd.delete('images[]');
            const uploader = window.__productImageUploader;
            if (uploader) {
                uploader.newFiles.forEach(f => fd.append('images[]', f.file));
            }

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    redirect: 'follow',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!resp.ok) {
                    const text = await resp.text();
                    let errMsg = 'Something went wrong while saving.';
                    try {
                        const json = JSON.parse(text);
                        if (json.message) errMsg = json.message;
                        if (json.errors) {
                            errMsg = Object.values(json.errors).flat().join('\n');
                        }
                    } catch(e) {}
                    alert(errMsg);
                    this.submitting = false;
                    return;
                }

                window.location.href = resp.url;
            } catch (err) {
                console.error('Submit error:', err);
                alert('Submit failed: ' + (err.message || 'Unknown error'));
                this.submitting = false;
            }
        }
    };
}

/* ── Multiple Image Uploader ── */
function multiImageUploader(existingImages) {
    return {
        savedImages: existingImages || [],
        newFiles: [],
        removedImages: [],

        init() {
            // Expose this instance so productForm.submitForm() can read newFiles directly
            window.__productImageUploader = this;
        },

        addFiles(event) {
            const max = 10;
            const remaining = max - this.savedImages.length - this.newFiles.length;
            const files = Array.from(event.target.files).slice(0, remaining);

            files.forEach(file => {
                let previewUrl = '';
                if (window.URL && typeof window.URL.createObjectURL === 'function') {
                    try {
                        previewUrl = window.URL.createObjectURL(file);
                    } catch(e) {}
                }

                const item = { file: file, preview: previewUrl };
                const idx = this.newFiles.push(item) - 1;

                if (!previewUrl) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        if (this.newFiles[idx]) {
                            this.newFiles[idx].preview = e.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Clear so the same file can be re-selected next time
            event.target.value = '';
        },

        removeSaved(idx) {
            const fn = this.savedImages[idx];
            this.removedImages.push(fn);
            this.savedImages.splice(idx, 1);
        },

        removeNew(idx) {
            const item = this.newFiles[idx];
            if (item && item.preview && item.preview.startsWith('blob:')) {
                try {
                    window.URL.revokeObjectURL(item.preview);
                } catch(e) {}
            }
            this.newFiles.splice(idx, 1);
        },
    };
}
</script>
@endpush
