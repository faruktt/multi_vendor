@extends('layouts.app')
@section('title', 'Flash Sale Management')
@section('heading', 'Flash Sale Management')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')

{{-- ── Header Banner ──────────────────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-6 text-white shadow-lg mb-6">
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                <i class="fas fa-bolt text-yellow-300"></i> Special Promotion Hub
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight flex items-center gap-2.5">
                Flash Sale Management
            </h1>
            <p class="text-white/90 text-sm mt-1 max-w-xl">
                Set discounted flash prices for customer products (shows on Website Home Page) or reseller exclusive wholesale deals (shows on Reseller Product Page).
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('shop.home') }}" target="_blank"
               class="inline-flex items-center gap-2 bg-white text-orange-600 hover:bg-orange-50 font-bold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all hover:scale-105">
                <i class="fas fa-arrow-up-right-from-square text-xs"></i> View Live Shop
            </a>
        </div>
    </div>
    {{-- Decorative bolt background icon --}}
    <i class="fas fa-bolt absolute -right-6 -bottom-8 text-white/10 text-[180px] pointer-events-none"></i>
</div>

{{-- ── Stat Cards ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600">
                <i class="fas fa-bolt text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Active Deals</p>
                <div class="flex items-center gap-2">
                    <p class="text-2xl font-black text-slate-800">{{ $stats['active_deals'] }}</p>
                    @if($stats['active_deals'] > 0)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 animate-pulse">Live</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center flex-shrink-0 text-sky-600">
                <i class="fas fa-users text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Customer Deals</p>
                <p class="text-2xl font-black text-sky-700">{{ $stats['customer_deals'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600">
                <i class="fas fa-handshake text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Reseller Deals</p>
                <p class="text-2xl font-black text-indigo-700">{{ $stats['reseller_deals'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 text-purple-600">
                <i class="fas fa-piggy-bank text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Customer Savings</p>
                <p class="text-2xl font-black text-purple-600">{{ $currency }}{{ number_format($stats['total_savings'], 0) }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Two-Column Section ────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="flashSaleForm()">

    {{-- ════ LEFT: Add / Create Flash Sale Card (4 cols) ════ --}}
    <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden sticky top-4">
        <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 text-sm">Add Product to Flash Sale</h2>
                    <p class="text-[11px] text-slate-500">Select product & set new discounted price</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.flash-sales.store') }}" method="POST" class="p-5 space-y-4">
            @csrf

            {{-- 0. Target Audience Selector --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                    Target Audience (কার জন্য অফার?) <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                    <label class="cursor-pointer">
                        <input type="radio" name="target_audience" value="customer" class="sr-only"
                               x-model="targetAudience" @change="onAudienceChange()">
                        <div class="py-2 px-3 rounded-lg text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5"
                             :class="targetAudience === 'customer' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                            <i class="fas fa-users"></i>
                            <span>Customer (স্টোরফ্রন্ট)</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="target_audience" value="reseller" class="sr-only"
                               x-model="targetAudience" @change="onAudienceChange()">
                        <div class="py-2 px-3 rounded-lg text-xs font-bold text-center transition-all flex items-center justify-center gap-1.5"
                             :class="targetAudience === 'reseller' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                            <i class="fas fa-handshake"></i>
                            <span>Reseller (রিসেলার)</span>
                        </div>
                    </label>
                </div>

                {{-- Dynamic Audience Scope Notice --}}
                <div class="mt-2 p-2.5 rounded-xl border text-[11.5px] leading-snug transition-all"
                     :class="targetAudience === 'customer' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-indigo-50 border-indigo-200 text-indigo-900'">
                    <template x-if="targetAudience === 'customer'">
                        <div class="flex items-start gap-1.5">
                            <i class="fas fa-circle-check text-amber-600 mt-0.5 flex-shrink-0"></i>
                            <span>এই ফ্ল্যাশ সেলটি <strong>শুধুমাত্র ওয়েবসাইটের হোম পেজে (Customer Storefront)</strong> শো হবে। রিসেলারের প্রোডাক্ট পেজে <strong>শো হবে না</strong>।</span>
                        </div>
                    </template>
                    <template x-if="targetAudience === 'reseller'">
                        <div class="flex items-start gap-1.5">
                            <i class="fas fa-circle-check text-indigo-600 mt-0.5 flex-shrink-0"></i>
                            <span>এই ফ্ল্যাশ সেলটি <strong>শুধুমাত্র রিসেলারদের প্রোডাক্ট পেজে (Reseller Portal)</strong> শো হবে। ওয়েবসাইটের হোম পেজে <strong>শো হবে না</strong>।</span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 1. Product Dropdown --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                    Select Product <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select name="product_id" id="product_select" required
                            @change="onProductSelect($event.target.value)"
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white text-slate-700 appearance-none pr-9">
                        <option value="">-- Choose a Product --</option>
                        @foreach($products as $p)
                        @php
                            $resellerPriceVal = (float) ($p->reseller_price ?? $p->price);
                        @endphp
                        <option value="{{ $p->id }}"
                                data-name="{{ $p->name }}"
                                data-sku="{{ $p->sku ?? 'N/A' }}"
                                data-price="{{ (float) $p->price }}"
                                data-reseller-price="{{ $resellerPriceVal }}"
                                data-stock="{{ $p->stock_qty }}"
                                data-image="{{ $p->first_image_url ?? '' }}"
                                {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} (Retail: {{ $currency }}{{ number_format($p->price, 0) }} | Reseller: {{ $currency }}{{ number_format($resellerPriceVal, 0) }})
                        </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                </div>
            </div>

            {{-- Selected Product Live Preview Card --}}
            <div x-show="selectedProduct" x-cloak
                 class="p-3 rounded-xl border transition-all"
                 :class="targetAudience === 'reseller' ? 'bg-indigo-50/60 border-indigo-200/70' : 'bg-amber-50/60 border-amber-200/70'">
                <div class="flex items-center gap-3">
                    <template x-if="selectedProduct && selectedProduct.image">
                        <img :src="selectedProduct.image" :alt="selectedProduct.name" class="w-12 h-12 rounded-lg object-cover border bg-white flex-shrink-0"
                             :class="targetAudience === 'reseller' ? 'border-indigo-200' : 'border-amber-200'">
                    </template>
                    <template x-if="selectedProduct && !selectedProduct.image">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-base flex-shrink-0"
                             :class="targetAudience === 'reseller' ? 'bg-indigo-100 text-indigo-500' : 'bg-amber-100 text-amber-500'">
                            <i class="fas fa-box"></i>
                        </div>
                    </template>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-800 text-xs truncate" x-text="selectedProduct ? selectedProduct.name : ''"></p>
                        <div class="flex flex-wrap items-center gap-2 mt-0.5 text-[11px]">
                            <template x-if="targetAudience === 'customer'">
                                <span class="text-slate-500">Retail Price: <strong class="text-slate-700 font-semibold">{{ $currency }}<span x-text="selectedProduct ? Number(selectedProduct.price).toLocaleString() : '0'"></span></strong></span>
                            </template>
                            <template x-if="targetAudience === 'reseller'">
                                <span class="text-indigo-700 font-semibold">Reseller Price: <strong>{{ $currency }}<span x-text="selectedProduct ? Number(selectedProduct.resellerPrice).toLocaleString() : '0'"></span></strong> <span class="text-slate-400 text-[10px]">(Retail: {{ $currency }}<span x-text="selectedProduct ? Number(selectedProduct.price).toLocaleString() : '0'"></span>)</span></span>
                            </template>
                            <span class="text-slate-300">|</span>
                            <span class="text-slate-500">Stock: <span :class="selectedProduct && selectedProduct.stock > 0 ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold'" x-text="selectedProduct ? selectedProduct.stock : 0"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. New Flash Sale Price --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                    <span x-text="targetAudience === 'reseller' ? 'New Reseller Flash Price (রিসেলারের নতুন কম দাম)' : 'New Customer Flash Price (কাস্টমারের নতুন দাম)'"></span>
                    <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">{{ $currency }}</span>
                    <input type="number" step="0.01" min="0.01" name="flash_price" id="flash_price" required
                           x-model="flashPrice"
                           @input="calculateDiscount()"
                           :placeholder="targetAudience === 'reseller' ? 'Enter lower price for resellers...' : 'Enter discounted price for customers...'"
                           class="w-full pl-8 pr-3.5 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white text-slate-800 font-bold">
                </div>

                {{-- Real-time Discount Calculation Badge --}}
                <div x-show="discountFeedback" x-cloak class="mt-2">
                    <template x-if="discountValid">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 shadow-2xs">
                            <i class="fas fa-circle-check text-emerald-600 text-xs"></i>
                            <span>Discount: {{ $currency }}<span x-text="discountAmount"></span> (<span x-text="discountPercent"></span>% OFF) 🎉</span>
                        </div>
                    </template>
                    <template x-if="!discountValid && discountFeedback">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-semibold border border-red-200">
                            <i class="fas fa-triangle-exclamation text-xs"></i>
                            <span x-text="discountFeedback"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 3. Schedule Dates (Optional) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                        Start Date &amp; Time
                    </label>
                    <input type="datetime-local" name="start_time"
                           value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white text-slate-700">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                        End Date &amp; Time
                    </label>
                    <input type="datetime-local" name="end_time" id="end_time"
                           value="{{ old('end_time', now()->addDays(3)->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white text-slate-700">
                </div>
            </div>

            {{-- Quick Date Duration Buttons --}}
            <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>Quick end:</span>
                <button type="button" @click="setEndDuration(1)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 font-medium text-slate-700 transition-colors">+24h</button>
                <button type="button" @click="setEndDuration(3)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 font-medium text-slate-700 transition-colors">+3 Days</button>
                <button type="button" @click="setEndDuration(7)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 font-medium text-slate-700 transition-colors">+7 Days</button>
                <button type="button" @click="clearEndDate()" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 font-medium text-slate-500 transition-colors">Never</button>
            </div>

            {{-- 4. Active Status Toggle --}}
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-800">Activate Immediately</p>
                    <p class="text-[11px] text-slate-400">Make this deal live on the website right now</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit"
                    class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md shadow-orange-200 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-bolt text-yellow-200 text-sm"></i> Add to Flash Sale
            </button>
        </form>
    </div>

    {{-- ════ RIGHT: Flash Sales List Table (8 cols) ════ --}}
    <div class="lg:col-span-8 min-w-0">

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
                <div class="relative flex-1 min-w-[180px]">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search product or SKU..."
                           class="w-full pl-9 pr-3.5 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white">
                </div>
                <select name="target_audience"
                        class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-600">
                    <option value="">All Audiences</option>
                    <option value="customer" {{ request('target_audience') === 'customer' ? 'selected' : '' }}>👤 Customer Deals</option>
                    <option value="reseller" {{ request('target_audience') === 'reseller' ? 'selected' : '' }}>🤝 Reseller Deals</option>
                </select>
                <select name="status"
                        class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-600">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Now</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                <button type="submit"
                        class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                    Filter
                </button>
                @if(request('search') || request('status') || request('target_audience'))
                <a href="{{ route('admin.flash-sales.index') }}"
                   class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
                    Reset
                </a>
                @endif
            </form>
            <span class="text-xs text-slate-400 font-medium whitespace-nowrap">
                {{ $flashSales->total() }} flash deals
            </span>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase w-8">#</th>
                            <th class="px-3.5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Product</th>
                            <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Target</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Regular</th>
                            <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Flash Price</th>
                            <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Discount</th>
                            <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Schedule</th>
                            <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Status</th>
                            <th class="px-3.5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($flashSales as $fs)
                        @php
                            $isLive = $fs->isCurrentlyActive();
                            $p = $fs->product;
                            $isReseller = ($fs->target_audience ?? 'customer') === 'reseller';
                            $basePrice = $isReseller ? (float) ($p?->reseller_price ?? $p?->price ?? 0) : (float) ($p?->price ?? 0);
                            $savings = max(0, $basePrice - (float) $fs->flash_price);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isLive ? ($isReseller ? 'bg-indigo-50/15' : 'bg-amber-50/15') : '' }}">
                            <td class="px-3 py-3.5 text-slate-400 text-xs">{{ $flashSales->firstItem() + $loop->index }}</td>

                            {{-- Product info --}}
                            <td class="px-3.5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if($p && $p->first_image_url)
                                        <img src="{{ $p->first_image_url }}" alt="{{ $p->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200/80 bg-white flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 text-sm flex-shrink-0">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 text-[13px] leading-snug truncate max-w-[180px]" title="{{ $p?->name }}">
                                            {{ $p?->name ?? 'Deleted Product' }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($p && $p->sku)
                                            <span class="text-[10.5px] font-mono text-slate-400">{{ $p->sku }}</span>
                                            @endif
                                            @if($p && $p->category)
                                            <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded font-medium">{{ $p->category->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Target Audience --}}
                            <td class="px-3 py-3.5 text-center">
                                @if($isReseller)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-black bg-indigo-100 text-indigo-700 border border-indigo-200">
                                    <i class="fas fa-handshake text-[10px]"></i> Reseller
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-users text-[10px]"></i> Customer
                                </span>
                                @endif
                            </td>

                            {{-- Baseline Price --}}
                            <td class="px-3 py-3.5 text-right font-semibold text-slate-400 line-through text-xs">
                                {{ $currency }}{{ number_format($basePrice, 0) }}
                                <span class="block text-[9.5px] text-slate-400 not-italic no-underline">
                                    {{ $isReseller ? 'Reseller Base' : 'Retail' }}
                                </span>
                            </td>

                            {{-- Flash Price --}}
                            <td class="px-3 py-3.5 text-right font-black text-sm {{ $isReseller ? 'text-indigo-600' : 'text-amber-600' }}">
                                {{ $currency }}{{ number_format($fs->flash_price, 0) }}
                            </td>

                            {{-- Discount badge --}}
                            <td class="px-3 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold {{ $isReseller ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    <i class="fas fa-arrow-down text-[10px]"></i>
                                    {{ $fs->discount_percentage }}%
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">Save {{ $currency }}{{ number_format($savings, 0) }}</span>
                            </td>

                            {{-- Schedule / Countdown --}}
                            <td class="px-3 py-3.5 text-center text-xs">
                                @if($fs->end_time)
                                    @if(now()->gt($fs->end_time))
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-clock"></i> Expired
                                    </span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $fs->end_time->format('d M, h:i A') }}</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-hourglass-half"></i> Ends {{ $fs->end_time->diffForHumans(['parts' => 1]) }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $fs->end_time->format('d M, h:i A') }}</span>
                                    @endif
                                @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                                    <i class="fas fa-infinity text-[10px]"></i> Ongoing
                                </span>
                                @endif
                            </td>

                            {{-- Status Toggle --}}
                            <td class="px-3 py-3.5 text-center">
                                <form action="{{ route('admin.flash-sales.toggle', $fs) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all {{ $fs->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}"
                                            title="Click to toggle status">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $fs->is_active ? 'bg-emerald-500 animate-ping' : 'bg-slate-400' }}"></span>
                                        {{ $fs->is_active ? 'Active' : 'Paused' }}
                                    </button>
                                </form>
                            </td>

                            {{-- Action buttons --}}
                            <td class="px-3.5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit button --}}
                                    <button type="button"
                                            @click="openEditModal({{ json_encode($fs) }}, {{ json_encode([
                                                'name' => $p?->name,
                                                'price' => $p?->price,
                                                'reseller_price' => $p?->reseller_price ?? $p?->price,
                                                'image' => $p?->first_image_url
                                            ]) }})"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-600 flex items-center justify-center transition-colors"
                                            title="Edit Flash Deal">
                                        <i class="fas fa-pencil text-xs"></i>
                                    </button>

                                    {{-- Delete button --}}
                                    <form action="{{ route('admin.flash-sales.destroy', $fs) }}" method="POST"
                                          onsubmit="return confirm('Remove {{ addslashes($p?->name ?? 'this product') }} from Flash Sale?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 flex items-center justify-center transition-colors"
                                                title="Delete Flash Deal">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mx-auto mb-3">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h3 class="font-bold text-slate-700 text-base">No Flash Sale Products Found</h3>
                                <p class="text-slate-400 text-sm max-w-sm mx-auto mt-1">
                                    Use the form on the left to select any product, choose Customer or Reseller, and set a discounted flash sale price!
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($flashSales->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 text-sm">
                {{ $flashSales->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ════ Edit Modal ════ --}}
    <div x-show="editModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="editModalOpen = false"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="px-5 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex items-center justify-between">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-300"></i> Edit Flash Sale Deal
                </h3>
                <button type="button" @click="editModalOpen = false" class="text-white/80 hover:text-white">
                    <i class="fas fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/flash-sales') }}/' + (editingDeal ? editingDeal.id : '')" method="POST" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                {{-- Product summary --}}
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <template x-if="editingProduct && editingProduct.image">
                        <img :src="editingProduct.image" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                    </template>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-slate-800 text-sm truncate" x-text="editingProduct ? editingProduct.name : ''"></p>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase"
                                  :class="editTargetAudience === 'reseller' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-800'"
                                  x-text="editTargetAudience === 'reseller' ? 'Reseller' : 'Customer'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Baseline: <strong>{{ $currency }}<span x-text="editBaselinePrice ? Number(editBaselinePrice).toLocaleString() : ''"></span></strong>
                            <span class="text-[10px] text-slate-400" x-text="editTargetAudience === 'reseller' ? '(Reseller Price)' : '(Retail Price)'"></span>
                        </p>
                    </div>
                </div>

                {{-- Hidden or editable target_audience --}}
                <input type="hidden" name="target_audience" :value="editTargetAudience">

                {{-- Price input --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                        Flash Sale Price ({{ $currency }}) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="flash_price" required
                           x-model="editFlashPrice"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:ring-2 focus:ring-amber-500 focus:bg-white text-slate-800 font-bold">
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Start Time</label>
                        <input type="datetime-local" name="start_time" x-model="editStartTime"
                               class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">End Time</label>
                        <input type="datetime-local" name="end_time" x-model="editEndTime"
                               class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs bg-slate-50">
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-700">Deal Active</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" :checked="editIsActive">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-sm border border-slate-200 text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function flashSaleForm() {
    return {
        targetAudience: 'customer',
        selectedProduct: null,
        flashPrice: '',
        discountAmount: '0',
        discountPercent: '0',
        discountValid: false,
        discountFeedback: '',

        // Edit Modal state
        editModalOpen: false,
        editingDeal: null,
        editingProduct: null,
        editTargetAudience: 'customer',
        editBaselinePrice: 0,
        editFlashPrice: '',
        editStartTime: '',
        editEndTime: '',
        editIsActive: true,

        onAudienceChange() {
            this.calculateDiscount();
        },

        onProductSelect(productId) {
            const sel = document.getElementById('product_select');
            const opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) {
                this.selectedProduct = null;
                this.calculateDiscount();
                return;
            }

            this.selectedProduct = {
                id: opt.value,
                name: opt.dataset.name,
                sku: opt.dataset.sku,
                price: parseFloat(opt.dataset.price) || 0,
                resellerPrice: parseFloat(opt.dataset.resellerPrice) || (parseFloat(opt.dataset.price) || 0),
                stock: opt.dataset.stock,
                image: opt.dataset.image || null,
            };

            this.calculateDiscount();
        },

        calculateDiscount() {
            if (!this.selectedProduct || !this.flashPrice) {
                this.discountFeedback = '';
                this.discountValid = false;
                return;
            }

            const isReseller = this.targetAudience === 'reseller';
            const basePrice = isReseller 
                ? (parseFloat(this.selectedProduct.resellerPrice) || 0)
                : (parseFloat(this.selectedProduct.price) || 0);
            const baseLabel = isReseller ? 'reseller price' : 'regular price';
            const fp = parseFloat(this.flashPrice) || 0;

            if (fp <= 0) {
                this.discountFeedback = 'Price must be greater than 0.';
                this.discountValid = false;
                return;
            }

            if (fp >= basePrice) {
                this.discountFeedback = `Flash price must be less than ${baseLabel} (${basePrice.toLocaleString()}).`;
                this.discountValid = false;
                return;
            }

            const diff = basePrice - fp;
            const pct = ((diff / basePrice) * 100).toFixed(1);

            this.discountAmount = diff.toLocaleString();
            this.discountPercent = pct;
            this.discountValid = true;
            this.discountFeedback = isReseller 
                ? `Reseller discount: Save ${diff.toLocaleString()} (${pct}% OFF reseller price)`
                : `Customer discount: Save ${diff.toLocaleString()} (${pct}% OFF regular price)`;
        },

        setEndDuration(days) {
            const d = new Date();
            d.setDate(d.getDate() + days);
            const iso = d.toISOString().slice(0, 16);
            document.getElementById('end_time').value = iso;
        },

        clearEndDate() {
            document.getElementById('end_time').value = '';
        },

        openEditModal(deal, prod) {
            this.editingDeal = deal;
            this.editingProduct = prod;
            this.editTargetAudience = deal.target_audience || 'customer';
            this.editBaselinePrice = this.editTargetAudience === 'reseller' 
                ? (parseFloat(prod.reseller_price) || parseFloat(prod.price) || 0)
                : (parseFloat(prod.price) || 0);
            this.editFlashPrice = deal.flash_price;
            this.editStartTime = deal.start_time ? deal.start_time.replace(' ', 'T').slice(0, 16) : '';
            this.editEndTime = deal.end_time ? deal.end_time.replace(' ', 'T').slice(0, 16) : '';
            this.editIsActive = Boolean(deal.is_active);
            this.editModalOpen = true;
        }
    }
}
</script>
@endpush

@endsection
