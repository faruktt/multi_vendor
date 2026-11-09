@extends('supplier.layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add New Product')
@section('heading', isset($product) ? 'Edit: ' . $product->name : 'Add New Product')

@section('content')
<div x-data="supplierProductForm()">
    <form method="POST"
          action="{{ isset($product) ? route('supplier.products.update', $product->id) : route('supplier.products.store') }}"
          enctype="multipart/form-data"
          @submit.prevent="submitForm($event)">
        @csrf
        @if(isset($product))
            @method('PUT')
        @endif

        <div class="flex flex-col xl:flex-row gap-6">

            {{-- ══ Error Alert Banner ══ --}}
            <div x-show="errorMessage || errorList.length > 0"
                 x-cloak
                 class="w-full xl:col-span-full mb-2 bg-red-50 border-2 border-red-300 rounded-2xl p-4 sm:p-5 text-red-800 shadow-sm transition-all"
                 id="form-error-banner">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0 text-red-600 mt-0.5">
                        <i class="fas fa-circle-exclamation text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm sm:text-base text-red-900" x-text="errorMessage || 'প্রোডাক্ট সংরক্ষণ করা যায়নি'"></h4>
                        <template x-if="errorList.length > 0">
                            <ul class="mt-2.5 list-disc list-inside text-xs sm:text-sm space-y-1 text-red-700 font-medium">
                                <template x-for="(err, idx) in errorList" :key="idx">
                                    <li x-text="err"></li>
                                </template>
                            </ul>
                        </template>
                    </div>
                    <button type="button" @click="errorMessage = ''; errorList = []" class="text-red-400 hover:text-red-700 p-1 rounded-lg hover:bg-red-100 transition-colors">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>
            </div>

            {{-- ══ LEFT COLUMN: Primary Details & Variants ══ --}}
            <div class="flex-1 space-y-5">

                {{-- 1. Basic Information --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                            <i class="fas fa-circle-info"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Basic Information</h3>
                            <p class="text-[11px] text-slate-400">Core product identity and taxonomy</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        {{-- Name --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Product Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                                   placeholder="e.g. Premium Cotton Casual Shirt"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        </div>

                        {{-- Category & Subcategory --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Category <span class="text-rose-500">*</span>
                                </label>
                                <select name="category_id" required x-model="selectedCategoryId" @change="onCategoryChange()"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                                    <option value="">Select a Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Subcategory <span class="text-slate-400 font-normal">(Optional)</span>
                                </label>
                                <select name="sub_category_id" x-model="selectedSubCategoryId"
                                        :disabled="availableSubcategories.length === 0"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all disabled:opacity-50 disabled:bg-slate-100">
                                    <option value="" x-text="availableSubcategories.length === 0 ? (selectedCategoryId ? 'No subcategory' : 'Select category first') : 'Select subcategory'"></option>
                                    <template x-for="sub in availableSubcategories" :key="sub.id">
                                        <option :value="sub.id" :selected="String(sub.id) === String(selectedSubCategoryId)" x-text="sub.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Unit & Barcode --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div x-data="unitField('{{ old('unit', $product->unit ?? 'pcs') }}')">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Unit</label>
                                @php
                                $units = [
                                    'Quantity' => ['pcs' => 'Piece (pcs)', 'dz' => 'Dozen (dz)', 'pair' => 'Pair', 'set' => 'Set', 'box' => 'Box', 'ctn' => 'Carton (ctn)', 'pkt' => 'Packet (pkt)'],
                                    'Weight'   => ['kg' => 'Kilogram (kg)', 'gm' => 'Gram (gm)', 'mg' => 'Milligram (mg)', 'ton' => 'Ton'],
                                    'Volume'   => ['ltr' => 'Liter (ltr)', 'ml' => 'Milliliter (ml)'],
                                    'Length'   => ['mtr' => 'Meter (mtr)', 'cm' => 'Centimeter (cm)', 'ft' => 'Feet (ft)', 'inch' => 'Inch'],
                                    'Other'    => ['btl' => 'Bottle (btl)', 'roll' => 'Roll', 'bag' => 'Bag', 'sht' => 'Sheet', 'custom' => '— Custom —'],
                                ];
                                @endphp
                                <select x-model="selected" @change="onSelect()"
                                        :name="selected !== 'custom' ? 'unit' : '_unit_ignore'"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
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
                                       class="mt-2 w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>

                            {{-- Barcode --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Custom Barcode <span class="text-slate-400 font-normal">(Leave blank to auto-generate)</span>
                                </label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}"
                                       placeholder="e.g. 890123456789"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            </div>
                        </div>

                        {{-- Short Description --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Short Description <span class="text-slate-400 font-normal">(Shows highlighted near product gallery)</span>
                            </label>
                            <textarea name="short_description" rows="2" maxlength="500"
                                      placeholder="Brief summary of fabrics, highlights, or key selling points..."
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all resize-none">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                        </div>

                        {{-- Full Description --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Full Product Description
                            </label>
                            <textarea name="description" rows="4"
                                      placeholder="Detailed product information, specifications, washing instructions, size guide..."
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">{{ old('description', $product->description ?? '') }}</textarea>
                        </div>

                        {{-- Facebook Video / Reel URL --}}

                        {{-- <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                                <i class="fab fa-facebook text-blue-600"></i>
                                <span>Facebook Video / Reel / Review URL</span>
                            </label>
                            <input type="url" name="facebook_video_url" value="{{ old('facebook_video_url', $product->facebook_video_url ?? '') }}"
                                   placeholder="https://www.facebook.com/reel/..."
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        </div> --}}
                    </div>
                </div>

                {{-- 2. Pricing & Stock --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Pricing &amp; Base Inventory</h3>
                            <p class="text-[11px] text-slate-400">Set customer retail price and stock limits</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Selling Price --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Selling Price (৳) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm pointer-events-none">৳</span>
                                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required min="0"
                                       x-model.number="price"
                                       placeholder="0.00"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2.5 text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            </div>
                        </div>

                        {{-- Cost Price --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Cost Price (৳) <span class="text-slate-400 font-normal">(Internal record)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm pointer-events-none">৳</span>
                                <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0"
                                       x-model.number="costPrice"
                                       placeholder="0.00"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            </div>
                        </div>

                        {{-- Reseller Price (Wholesale) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Wholesale Price (৳) <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm pointer-events-none">৳</span>
                                <input type="number" step="0.01" name="reseller_price" value="{{ old('reseller_price', $product->reseller_price ?? '') }}" min="0"
                                       placeholder="0.00"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2.5 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                            </div>
                        </div>

                        {{-- Profit Indicator --}}
                        <div class="sm:col-span-3 flex items-center gap-3 bg-slate-50 rounded-xl px-4 py-2.5 border border-slate-100">
                            <i class="fas fa-chart-line text-emerald-500 text-sm"></i>
                            <div class="flex-1 text-xs text-slate-600">
                                Estimated Profit:
                                <span class="font-bold ml-1"
                                      :class="profit > 0 ? 'text-emerald-600' : 'text-slate-400'"
                                      x-text="profit > 0 ? '+৳' + profit.toFixed(2) + ' (' + profitPct + '%)' : '—'"></span>
                            </div>
                            <div class="text-xs text-slate-400" x-show="variants.length === 0">
                                Margin: <span class="font-semibold text-slate-700" x-text="profitPct + '%'"></span>
                            </div>
                        </div>

                        {{-- Opening Stock Qty (when no variants) --}}
                        <div x-show="variants.length === 0" class="sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Opening Stock Quantity</label>
                            <input type="number" name="stock_qty" min="0"
                                   value="{{ old('stock_qty', $product->stock_qty ?? 0) }}"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                {{-- 3. Product Variants (Matches Admin Variation System) --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Product Variants</h3>
                                <p class="text-[11px] text-slate-400">Custom price and stock per Color + Size combination</p>
                            </div>
                        </div>

                        <button type="button" @click="addVariant()"
                                class="flex items-center gap-1.5 text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                            <i class="fas fa-plus text-[10px]"></i> Add Variant
                        </button>
                    </div>

                    {{-- Color & Size lists passed from controller --}}
                    @php
                        $colorsJson = $colors->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'hex' => $c->hex_code])->values();
                        $sizesJson  = $sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values();
                    @endphp

                    {{-- Variant list --}}
                    <div class="space-y-3">
                        <template x-for="(v, i) in variants" :key="i">
                            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/60 hover:border-purple-300 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-purple-700 text-xs font-bold" x-text="i + 1"></span>
                                    </div>

                                    <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3">

                                        {{-- Color Select --}}
                                        <div>
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">Color</label>
                                            <div class="relative">
                                                <select x-model="v.color_id" @change="onColorChange(v)"
                                                        class="w-full border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white appearance-none">
                                                    <option value="">— Select Color —</option>
                                                    <template x-for="c in availableColors" :key="c.id">
                                                        <option :value="c.id" x-text="c.name" :selected="String(v.color_id) === String(c.id)"></option>
                                                    </template>
                                                </select>
                                                {{-- color swatch dot --}}
                                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full border border-slate-300 pointer-events-none"
                                                      :style="getColorHex(v.color_id) ? 'background-color:' + getColorHex(v.color_id) : 'background:#e2e8f0'"></span>
                                            </div>
                                        </div>

                                        {{-- Size Select --}}
                                        <div>
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">Size</label>
                                            <select x-model="v.size_id" @change="onSizeChange(v)"
                                                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                                                <option value="">— Select Size —</option>
                                                <template x-for="s in availableSizes" :key="s.id">
                                                    <option :value="s.id" x-text="s.name" :selected="String(v.size_id) === String(s.id)"></option>
                                                </template>
                                            </select>
                                        </div>

                                        {{-- Variant Price --}}
                                        <div>
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">Price (৳)</label>
                                            <input type="number" x-model="v.price" step="0.01" min="0"
                                                   placeholder="Base price if empty"
                                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                                        </div>

                                        {{-- Variant Stock --}}
                                        <div>
                                            <label class="block text-[10.5px] font-bold text-slate-500 uppercase tracking-wide mb-1">Stock Qty</label>
                                            <input type="number" x-model.number="v.stock_qty" min="0"
                                                   placeholder="0"
                                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                                        </div>

                                    </div>

                                    <button type="button" @click="removeVariant(i)"
                                            class="w-7 h-7 rounded-lg border border-rose-200 text-rose-500 hover:bg-rose-50 hover:text-rose-700 flex items-center justify-center transition flex-shrink-0 mt-0.5"
                                            title="Remove this variant">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>

                                {{-- Variant label preview chip --}}
                                <div class="mt-2.5 ml-10" x-show="v.color_label || v.size_label">
                                    <span class="inline-flex items-center gap-1.5 bg-purple-50 border border-purple-200 text-purple-700 rounded-full px-3 py-1 text-xs font-bold">
                                        <span class="w-2.5 h-2.5 rounded-full border border-purple-300"
                                              :style="getColorHex(v.color_id) ? 'background-color:' + getColorHex(v.color_id) : 'background:#e2e8f0'"></span>
                                        <span x-text="[v.color_label, v.size_label].filter(Boolean).join(' / ')"></span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <div x-show="variants.length === 0"
                             class="border-2 border-dashed border-slate-200 rounded-2xl py-8 text-center text-slate-400">
                            <i class="fas fa-layer-group text-3xl mb-2 block text-slate-300"></i>
                            <p class="text-xs font-semibold">No variants added — product will use single base price &amp; stock</p>
                            <button type="button" @click="addVariant()"
                                    class="mt-2 text-xs text-purple-600 hover:text-purple-800 font-bold inline-flex items-center gap-1">
                                <i class="fas fa-plus text-[10px]"></i> Add Color or Size Option
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="variants" :value="JSON.stringify(variants)">
                </div>

            </div>

            {{-- ══ RIGHT COLUMN: Images, Summary & Status ══ --}}
            <div class="w-full xl:w-80 flex-shrink-0 space-y-5">

                {{-- 1. Multiple Image Uploader (Matching Admin Component) --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm"
                     x-data="multiImageUploader({{ json_encode(isset($product) ? $product->images_list : []) }})">

                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-xs">
                                <i class="fas fa-images"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Product Images</h3>
                                <p class="text-[10.5px] text-slate-400">Max 10 images &bull; 3MB each</p>
                            </div>
                        </div>

                        <button type="button" @click="$refs.fileInput.click()"
                                class="text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-xl font-bold flex items-center gap-1 transition">
                            <i class="fas fa-plus text-[10px]"></i> Add
                        </button>
                    </div>

                    {{-- Hidden inputs for removed images in edit mode --}}
                    <template x-for="fn in removedImages" :key="fn">
                        <input type="hidden" name="remove_images[]" :value="fn">
                    </template>

                    {{-- Image Grid --}}
                    <div class="grid grid-cols-2 gap-2.5 mb-3">
                        {{-- Existing Saved Images (edit mode) --}}
                        <template x-for="(img, idx) in savedImages" :key="'saved-'+idx">
                            <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200 group bg-slate-100">
                                <img :src="img.includes('/') ? ('{{ asset('uploads') }}/' + img) : ('{{ asset('uploads/products') }}/' + img)" class="w-full h-full object-cover">
                                {{-- Main badge --}}
                                <span x-show="idx === 0"
                                      class="absolute bottom-1 left-1 text-[9px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-bold shadow">
                                    Main
                                </span>
                                {{-- Remove button --}}
                                <button type="button" @click="removeSaved(idx)"
                                        class="absolute top-1 right-1 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow">
                                    <i class="fas fa-times text-[9px]"></i>
                                </button>
                            </div>
                        </template>

                        {{-- New Files Previews --}}
                        <template x-for="(file, idx) in newFiles" :key="'new-'+idx">
                            <div class="relative aspect-square rounded-xl overflow-hidden border border-emerald-300 group bg-slate-100">
                                <img :src="file.preview" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 left-1 text-[9px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-bold shadow">
                                    New
                                </span>
                                <button type="button" @click="removeNew(idx)"
                                        class="absolute top-1 right-1 w-6 h-6 bg-rose-600 hover:bg-rose-700 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow">
                                    <i class="fas fa-times text-[9px]"></i>
                                </button>
                            </div>
                        </template>

                        {{-- Upload Placeholder --}}
                        <button type="button" @click="$refs.fileInput.click()"
                                x-show="savedImages.length + newFiles.length < 10"
                                class="aspect-square rounded-xl border-2 border-dashed border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-emerald-700 transition group">
                            <i class="fas fa-cloud-arrow-up text-xl group-hover:scale-110 transition-transform"></i>
                            <span class="text-[10px] font-bold">Upload</span>
                        </button>
                    </div>

                    {{-- Image Count Indicator --}}
                    <p class="text-[11px] text-slate-400 text-center font-medium"
                       x-text="(savedImages.length + newFiles.length) + ' / 10 images uploaded'"></p>

                    {{-- Hidden multiple file input --}}
                    <input type="file" name="images[]" accept="image/*" multiple x-ref="fileInput"
                           class="hidden" @change="addFiles($event)">
                </div>

                {{-- 2. Financial Summary Card --}}
                <div class="bg-gradient-to-br from-emerald-700 to-teal-800 rounded-2xl p-5 text-white shadow-md shadow-emerald-700/20">
                    <p class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-3">Product Summary</p>
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-emerald-200">Retail Price:</span>
                            <span class="font-black text-sm">৳<span x-text="price > 0 ? Number(price).toLocaleString() : '0.00'"></span></span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-emerald-200">Cost Price:</span>
                            <span class="font-semibold text-emerald-100">৳<span x-text="costPrice > 0 ? Number(costPrice).toLocaleString() : '0.00'"></span></span>
                        </div>
                        <div class="border-t border-emerald-600/70 pt-2 flex justify-between items-center text-xs">
                            <span class="text-emerald-200">Est. Profit:</span>
                            <span class="font-black text-yellow-300 text-sm"
                                  x-text="profit > 0 ? '+৳' + profit.toFixed(2) + ' (' + profitPct + '%)' : '—'"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-1">
                            <span class="text-emerald-200">Total Variants:</span>
                            <span class="font-bold text-white" x-text="variants.length > 0 ? variants.length + ' variants' : 'None (Single Item)'"></span>
                        </div>
                        @if(isset($product) && $product->isApproved())
                        <div class="flex justify-between items-center text-xs pt-1 border-t border-emerald-600/70">
                            <span class="text-emerald-200">Admin Commission:</span>
                            <span class="font-bold text-yellow-300">{{ $product->effective_admin_commission_rate }}% (৳{{ number_format($product->admin_commission_per_unit, 2) }})</span>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-1">
                            <span class="text-emerald-200">Your Earning / Sale:</span>
                            <span class="font-bold text-white">৳{{ number_format($product->supplier_earning_per_unit, 2) }}</span>
                        </div>
                        @else
                        <div class="pt-2 border-t border-emerald-600/70 text-[11px] text-emerald-100/90 leading-relaxed">
                            <i class="fas fa-info-circle text-yellow-300 mr-1"></i>
                            After submitting, admin will review and assign the commission rate before making it live.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- 3. Product Approval Status & Visibility --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
                    <label class="block text-xs font-bold text-slate-700">Approval &amp; Visibility</label>
                    @if(isset($product))
                        @if($product->isPendingApproval())
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                                <i class="fas fa-clock text-amber-600 text-sm"></i>
                                <div>
                                    <span class="font-bold block">Pending Admin Approval</span>
                                    <span class="text-[11px] text-amber-700">Will automatically activate once admin reviews and configures commission.</span>
                                </div>
                            </div>
                        @elseif($product->isRejected())
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
                                <span class="font-bold block text-rose-700">Product Rejected</span>
                                @if($product->rejection_reason)
                                    <p class="text-[11px] text-rose-600 mt-1">Reason: {{ $product->rejection_reason }}</p>
                                @endif
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center gap-2">
                                <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                                <div>
                                    <span class="font-bold block">Approved by Admin</span>
                                    <span class="text-[11px] text-emerald-700">This product is published and live on the storefront.</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Display Status</label>
                                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="active" {{ old('status', $product->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                        Active (Visible in Marketplace)
                                    </option>
                                    <option value="inactive" {{ old('status', $product->status ?? '') === 'inactive' ? 'selected' : '' }}>
                                        Inactive (Temporarily Hidden)
                                    </option>
                                </select>
                            </div>
                        @endif
                    @else
                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-center gap-2">
                            <i class="fas fa-shield-halved text-blue-600 text-sm"></i>
                            <div>
                                <span class="font-bold block">Admin Review Required</span>
                                <span class="text-[11px] text-blue-700">Upon submission, admin will review the product, assign commission, and publish it live.</span>
                            </div>
                        </div>
                    @endif

                    <div class="pt-2">
                        <button type="submit" :disabled="submitting"
                                class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <i class="fas fa-cloud-arrow-up" x-show="!submitting"></i>
                            <i class="fas fa-circle-notch animate-spin" x-show="submitting" x-cloak></i>
                            <span x-text="submitting ? 'Saving Product...' : ({{ isset($product) ? 'true' : 'false' }} ? 'Update Product' : 'Publish Product')"></span>
                        </button>
                    </div>

                    <a href="{{ route('supplier.products.index') }}"
                       class="block w-full py-2 text-center text-xs font-bold text-slate-400 hover:text-slate-700 transition">
                        Cancel &amp; Return
                    </a>
                </div>

            </div>

        </div>
    </form>
</div>
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

function supplierProductForm() {
    return {
        categories: @json($categories),
        selectedCategoryId: '{{ old('category_id', $product->category_id ?? '') }}',
        selectedSubCategoryId: '{{ old('sub_category_id', $product->sub_category_id ?? '') }}',

        get availableSubcategories() {
            if (!this.selectedCategoryId) return [];
            const cat = this.categories.find(c => String(c.id) === String(this.selectedCategoryId));
            return cat ? (cat.sub_categories || cat.children || []) : [];
        },

        onCategoryChange() {
            const valid = this.availableSubcategories.some(s => String(s.id) === String(this.selectedSubCategoryId));
            if (!valid) {
                this.selectedSubCategoryId = '';
            }
        },

        variants: @json(isset($product) ? $product->variants : []),
        price:     {{ old('price', $product->price ?? 0) }},
        costPrice: {{ old('cost_price', $product->cost_price ?? 0) }},
        submitting: false,
        errorMessage: '',
        errorList: [],

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

        onColorChange(v) {
            const found = this.availableColors.find(c => String(c.id) === String(v.color_id));
            v.color_label = found ? found.name : '';
        },

        onSizeChange(v) {
            const found = this.availableSizes.find(s => String(s.id) === String(v.size_id));
            v.size_label = found ? found.name : '';
        },

        getColorHex(colorId) {
            if (!colorId) return null;
            const found = this.availableColors.find(c => String(c.id) === String(colorId));
            return found ? found.hex : null;
        },

        async submitForm(e) {
            this.submitting = true;
            this.errorMessage = '';
            this.errorList = [];

            const form = e.target;

            // Pre-validation checks
            const name = (form.querySelector('[name="name"]')?.value || '').trim();
            const categoryId = form.querySelector('[name="category_id"]')?.value;
            const price = form.querySelector('[name="price"]')?.value;
            const costPrice = form.querySelector('[name="cost_price"]')?.value;

            const clientErrors = [];
            if (!name) clientErrors.push('প্রোডাক্টের নাম (Product Name) দেওয়া আবশ্যক।');
            if (!categoryId) clientErrors.push('ক্যাটাগরি (Category) সিলেক্ট করা আবশ্যক।');
            if (price === '' || isNaN(price) || Number(price) < 0) clientErrors.push('বিক্রয় মূল্য (Price) সঠিক সংখ্যায় দেওয়া আবশ্যক।');
            if (costPrice === '' || isNaN(costPrice) || Number(costPrice) < 0) clientErrors.push('ক্রয় মূল্য (Cost Price) সঠিক সংখ্যায় দেওয়া আবশ্যক।');

            const uploader = window.__productImageUploader;
            if (uploader && uploader.newFiles) {
                uploader.newFiles.forEach((f) => {
                    if (f.file && f.file.size > 10 * 1024 * 1024) {
                        clientErrors.push(`"${f.file.name}" ছবির সাইজ ১০ MB এর বেশি (${(f.file.size / (1024 * 1024)).toFixed(1)} MB)। ছোট সাইজের ছবি আপলোড করুন।`);
                    }
                });
            }

            if (clientErrors.length > 0) {
                this.errorMessage = 'অনুগ্রহ করে নিচের ভুলগুলো সংশোধন করুন:';
                this.errorList = clientErrors;
                this.submitting = false;
                if (window.showToast) window.showToast('প্রোডাক্ট সংরক্ষণে সমস্যা হয়েছে! ভুলগুলো দেখুন।', 'error');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            // Build FormData from form fields
            const fd = new FormData(form);

            // Inject File objects directly from multiImageUploader
            fd.delete('images[]');
            if (uploader && uploader.newFiles) {
                uploader.newFiles.forEach(f => fd.append('images[]', f.file));
            }

            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    redirect: 'follow',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                });

                const text = await resp.text();
                let data = null;
                try {
                    data = JSON.parse(text);
                } catch(err) {}

                if (!resp.ok) {
                    let mainMsg = 'প্রোডাক্ট সংরক্ষণ করা যায়নি।';
                    let list = [];

                    if (data) {
                        if (data.message) mainMsg = data.message;
                        if (data.errors) {
                            list = Object.values(data.errors).flat();
                        }
                    } else if (resp.status === 413) {
                        mainMsg = 'আপলোডকৃত ফাইলের সাইজ অতিরিক্ত বড় (Payload Too Large)। ছবির সাইজ কমান।';
                    } else if (resp.status === 419) {
                        mainMsg = 'সেশন মেয়াদ শেষ হয়ে গেছে (CSRF Token Expired)। অনুগ্রহ করে পেজটি রিলোড দিন।';
                    } else if (resp.status === 500) {
                        mainMsg = 'সার্ভার ইন্টারনাল এরর (500)। অনুগ্রহ করে ফাইল সাইজ চেক করুন বা পুনরায় চেষ্টা করুন।';
                    }

                    this.errorMessage = mainMsg;
                    this.errorList = list;
                    this.submitting = false;

                    if (window.showToast) {
                        window.showToast(mainMsg, 'error');
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                // SUCCESS
                const successMsg = (data && data.message) ? data.message : 'প্রোডাক্ট সফলভাবে সংরক্ষণ করা হয়েছে!';
                try {
                    sessionStorage.setItem('flash_success', successMsg);
                } catch(e) {}

                if (window.showToast) {
                    window.showToast(successMsg, 'success');
                }

                const destUrl = (data && (data.redirect || data.redirect_url)) ? (data.redirect || data.redirect_url) : (resp.url || '{{ route('supplier.products.index') }}');
                setTimeout(() => {
                    window.location.href = destUrl;
                }, 250);

            } catch (err) {
                console.error('Submit error:', err);
                this.errorMessage = 'নেটওয়ার্ক বা সাবমিট এরর: ' + (err.message || 'Unknown error');
                this.errorList = ['ইন্টারনেট কানেকশন চেক করুন এবং পুনরায় চেষ্টা করুন।'];
                this.submitting = false;
                if (window.showToast) window.showToast(this.errorMessage, 'error');
                window.scrollTo({ top: 0, behavior: 'smooth' });
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
