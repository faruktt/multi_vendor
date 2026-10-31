@extends('layouts.app')
@section('title', 'ল্যান্ডিং পেজ এডিট করুন: ' . $landingPage->title)
@section('heading', 'ল্যান্ডিং পেজ এডিট করুন')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        overflow: hidden !important;
        background-color: #ffffff !important;
    }
    .note-toolbar { background-color: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important; }
    .note-editable { min-height: 180px !important; font-size: 14px !important; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-12" x-data="landingPageBuilder()">

    {{-- ── Top Navigation Bar ────────────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.landing-pages.index') }}"
               class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 flex items-center justify-center transition-colors shadow-2xs">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">ল্যান্ডিং পেজ সম্পাদনা</h1>
                    <a href="{{ route('landing.show', $landingPage->slug) }}" target="_blank"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition-colors">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> লাইভ দেখুন
                    </a>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">{{ $landingPage->title }}</p>
            </div>
        </div>

        <button type="button" @click="submitForm()" :disabled="isSubmitting"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all text-sm disabled:opacity-50">
            <i class="fas" :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
            <span x-text="isSubmitting ? 'আপডেট হচ্ছে...' : 'আপডেট করুন'"></span>
        </button>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
        <div class="font-bold mb-1 flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-red-500"></i> ফর্ম পূরণে কিছু ত্রুটি রয়েছে:
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Main Form ─────────────────────────────────────────────────── --}}
    <form id="landingPageForm" method="POST" action="{{ route('admin.landing-pages.update', $landingPage) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── Section 1: Basic Information & Theme ────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-black">1</span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800">প্রাথমিক তথ্য ও কালার থিম</h2>
                    <p class="text-xs text-slate-400">ল্যান্ডিং পেজের শিরোনাম, ইউআরএল ও প্রিমিয়াম ডিজাইন থিম নির্বাচন করুন</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        পেজের শিরোনাম / ক্যাম্পেইন নাম <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" x-model="title" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        স্ট্যাটাস <span class="text-red-500">*</span>
                    </label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="active" {{ $landingPage->status === 'active' ? 'selected' : '' }}>একটিভ (সরাসরি লাইভ)</option>
                        <option value="inactive" {{ $landingPage->status === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয় (ড্রাফট)</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        কাস্টম পেজ লিঙ্ক (URL Slug) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-4 py-2.5 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-slate-500 text-xs font-mono font-semibold">
                            {{ url('/landing') }}/
                        </span>
                        <input type="text" name="slug" x-model="slug" required
                               class="flex-1 bg-slate-50 border border-slate-200 rounded-r-xl px-4 py-2.5 text-sm font-mono font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Theme Picker --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">
                    ডিজাইন কালার থিম নির্বাচন করুন
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @php
                        $availableThemes = [
                            'emerald' => ['name' => 'Emerald Luxury',  'color' => '#059669', 'bg' => 'from-emerald-600 to-teal-800'],
                            'blue'    => ['name' => 'Royal Sapphire',  'color' => '#1d4ed8', 'bg' => 'from-blue-600 to-indigo-800'],
                            'dark'    => ['name' => 'Midnight Carbon', 'color' => '#0f172a', 'bg' => 'from-slate-900 to-black'],
                            'purple'  => ['name' => 'Imperial Violet', 'color' => '#7e22ce', 'bg' => 'from-purple-600 to-violet-800'],
                            'rose'    => ['name' => 'Ruby Crimson',    'color' => '#e11d48', 'bg' => 'from-rose-600 to-red-800'],
                            'amber'   => ['name' => 'Golden Premium',  'color' => '#d97706', 'bg' => 'from-amber-500 to-orange-700'],
                        ];
                    @endphp
                    @foreach($availableThemes as $key => $t)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="page_theme" value="{{ $key }}" x-model="selectedTheme" class="peer sr-only">
                        <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-blue-600 peer-checked:bg-blue-50/40 peer-checked:shadow-sm transition-all text-center">
                            <div class="w-full h-8 rounded-lg bg-gradient-to-r {{ $t['bg'] }} mb-2 shadow-xs"></div>
                            <div class="text-[11.5px] font-bold text-slate-800 leading-tight">{{ $t['name'] }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Section 2: Multiple Products Selection ──────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black">2</span>
                    <div>
                        <h2 class="text-base font-extrabold text-slate-800">প্রোডাক্ট সিলেকশন (একাধিক প্রোডাক্ট যুক্ত করুন)</h2>
                        <p class="text-xs text-slate-400">কাস্টমারকে ১টি, ২টি বা একাধিক অফার প্যাকেজ দেখানোর জন্য পণ্য নির্বাচন করুন</p>
                    </div>
                </div>

                <div class="relative w-64 max-w-full">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="productSearch" placeholder="প্রোডাক্ট খুঁজুন..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>

            {{-- Available products list to pick from --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                    ক্লিক করে প্রোডাক্ট যোগ/বাদ দিন:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-1.5 border border-slate-200 rounded-xl bg-slate-50/50">
                    <template x-for="p in filteredProducts" :key="p.id">
                        <div @click="toggleProduct(p)"
                             :class="isProductSelected(p.id) ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-slate-200 bg-white hover:border-slate-300'"
                             class="flex items-center gap-2.5 p-2 rounded-xl border cursor-pointer transition-all duration-150">
                            <img :src="p.image || '/images/placeholder.png'" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-slate-100">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-slate-800 truncate" x-text="p.name"></div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                    <span class="font-bold text-emerald-600" x-text="'৳' + p.price"></span>
                                    <span class="text-slate-300">|</span>
                                    <span x-text="'স্টক: ' + (p.stock_qty ?? 'N/A')"></span>
                                </div>
                            </div>
                            <div class="w-5 h-5 rounded-md border flex items-center justify-center flex-shrink-0 transition-colors"
                                 :class="isProductSelected(p.id) ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 bg-white'">
                                <i class="fas fa-check text-[10px]" x-show="isProductSelected(p.id)"></i>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Selected Products Configuration Table --}}
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    নির্বাচিত প্রোডাক্ট ও অফার প্যাকেজ কনফিগারেশন:
                </label>

                <div x-show="selectedProducts.length === 0" class="p-6 text-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-xs">
                    <i class="fas fa-hand-pointer text-slate-300 text-2xl mb-1.5"></i>
                    <div>উপরের তালিকা থেকে অন্তত ১টি প্রোডাক্ট সিলেক্ট করুন</div>
                </div>

                <div class="space-y-3" x-show="selectedProducts.length > 0">
                    <template x-for="(sp, idx) in selectedProducts" :key="sp.id">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/40 relative space-y-3">
                            <input type="hidden" :name="`products[${idx}][product_id]`" :value="sp.id">

                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img :src="sp.image || '/images/placeholder.png'" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs">
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-800" x-text="sp.name"></div>
                                        <div class="text-xs text-slate-500">মূল দাম: <span class="font-bold text-slate-700" x-text="'৳' + sp.price"></span></div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-600 cursor-pointer">
                                        <input type="radio" name="default_product_index" :value="idx" :checked="idx === defaultIndex" @change="defaultIndex = idx" class="text-emerald-600">
                                        <span>ডিফল্ট প্যাকেজ</span>
                                    </label>

                                    <button type="button" @click="removeProduct(sp.id)" class="text-slate-400 hover:text-red-600 p-1.5 transition-colors" title="মুছুন">
                                        <i class="fas fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-200/60">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">প্যাকেজ টাইটেল</label>
                                    <input type="text" :name="`products[${idx}][package_title]`" x-model="sp.package_title" placeholder="যেমন: ১টি কিনুন"
                                           class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">সাবটাইটেল / ব্যাজ</label>
                                    <input type="text" :name="`products[${idx}][package_subtitle]`" x-model="sp.package_subtitle" placeholder="যেমন: সেরা বাজেট"
                                           class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">অফার মূল্য (৳)</label>
                                    <input type="number" step="0.01" :name="`products[${idx}][custom_price]`" x-model="sp.custom_price" :placeholder="sp.price"
                                           class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-black text-emerald-600 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">পূর্বের মূল্য (কাটা দাগ)</label>
                                    <input type="number" step="0.01" :name="`products[${idx}][custom_old_price]`" x-model="sp.custom_old_price" :placeholder="sp.old_price || ''"
                                           class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-400 line-through focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── Section 3: Hero Section & Media ─────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">3</span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800">হিরো ব্যানার, ভিডিও ও অফার ডিটেইলস</h2>
                    <p class="text-xs text-slate-400">কাস্টমারের দৃষ্টি আকর্ষণ করার আকর্ষণীয় হেডলাইন, ব্যানার ও টাইমার</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        অফার ব্যাজ টেক্সট
                    </label>
                    <input type="text" name="hero_badge" value="{{ $landingPage->hero_badge }}" placeholder="🔥 সীমিত সময়ের জন্য ধামাকা অফার"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        কাউন্টডাউন টাইমার (ঘণ্টা)
                    </label>
                    <input type="number" name="countdown_hours" value="{{ $landingPage->countdown_hours ?? 12 }}" min="1" max="720"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        প্রধান হেডলাইন (Hero Headline)
                    </label>
                    <textarea name="hero_title" rows="2"
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ $landingPage->hero_title }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        সাব-হেডলাইন / সংক্ষিপ্ত বর্ণনা
                    </label>
                    <textarea name="hero_subtitle" rows="2"
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ $landingPage->hero_subtitle }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        হিরো ব্যানার ইমেজ পরিবর্তন
                    </label>
                    @if($landingPage->hero_image_url)
                    <div class="mb-2 flex items-center gap-2">
                        <img src="{{ $landingPage->hero_image_url }}" class="w-16 h-12 object-cover rounded-lg border">
                        <span class="text-xs text-slate-500">বর্তমান ছবি আপলোড করা আছে</span>
                    </div>
                    @endif
                    <input type="file" name="hero_image" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        ইউটিউব ভিডিও লিঙ্ক (ঐচ্ছিক)
                    </label>
                    <input type="text" name="hero_video_url" value="{{ $landingPage->hero_video_url }}" placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>
        </div>

        {{-- ── Section 4: Features & Description ───────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black">4</span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800">পণ্যর বৈশিষ্ট্য, গ্যালারি ও বিস্তারিত বিবরণ</h2>
                    <p class="text-xs text-slate-400">হাইলাইটেড ফিচার কার্ড, গ্যালারি ছবি ও বিস্তারিত তথ্য দিন</p>
                </div>
            </div>

            {{-- Feature Cards Repeater --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        প্রধান বৈশিষ্ট্যসমূহ (Feature Points)
                    </label>
                    <button type="button" @click="addFeature()"
                            class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plus"></i> ফিচার যোগ করুন
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(feat, fIdx) in features" :key="fIdx">
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200">
                            <input type="text" :name="`features[${fIdx}][icon]`" x-model="feat.icon" placeholder="fa-gem" title="FontAwesome icon class"
                                   class="w-24 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-600 font-mono">
                            <input type="text" :name="`features[${fIdx}][title]`" x-model="feat.title" placeholder="ফিচার শিরোনাম"
                                   class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800">
                            <input type="text" :name="`features[${fIdx}][desc]`" x-model="feat.desc" placeholder="সংক্ষিপ্ত বিবরণ"
                                   class="flex-1 bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-600">
                            <button type="button" @click="removeFeature(fIdx)" class="text-slate-400 hover:text-red-500 p-1.5">
                                <i class="fas fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Existing & New Gallery Images --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    প্রোডাক্ট গ্যালারি ইমেজ
                </label>
                
                @if(!empty($landingPage->gallery_image_urls))
                <div class="flex items-center gap-3 flex-wrap mb-3">
                    @foreach($landingPage->gallery_image_urls as $gIdx => $gUrl)
                    <div class="relative group w-20 h-20 rounded-xl overflow-hidden border border-slate-200">
                        <img src="{{ $gUrl }}" class="w-full h-full object-cover">
                        <input type="hidden" name="existing_gallery[]" value="{{ $landingPage->gallery_images[$gIdx] ?? '' }}">
                    </div>
                    @endforeach
                </div>
                @endif

                <input type="file" name="gallery_images[]" multiple accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
            </div>

            {{-- Rich Specifications / Content --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    বিস্তারিত স্পেসিফিকেশন ও বিবরণ (Rich Content)
                </label>
                <textarea id="specificationsEditor" name="specifications" rows="6"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-800 focus:bg-white focus:outline-none">{{ $landingPage->specifications }}</textarea>
            </div>
        </div>

        {{-- ── Section 5: Delivery, Trust & Social Proof ───────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-black">5</span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800">ডেলিভারি চার্জ ও অর্ডার সেটিংস</h2>
                    <p class="text-xs text-slate-400">ডেলিভারি ফি, ফ্রি শিপিং ও ফর্মের টাইটেল নির্ধারণ করুন</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2 lg:col-span-1 flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50">
                    <input type="checkbox" name="free_shipping" id="free_shipping" value="1" x-model="freeShipping"
                           class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="free_shipping" class="text-xs font-bold text-slate-800 cursor-pointer">
                        ফ্রি ডেলিভারি অফার (Free Shipping)
                    </label>
                </div>

                <div x-show="!freeShipping">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">ঢাকার ভেতর ডেলিভারি (৳)</label>
                    <input type="number" step="0.01" name="delivery_charge_inside" value="{{ $landingPage->delivery_charge_inside ?? ($onlineStore->delivery_charge_inside_dhaka ?? 60) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold">
                </div>

                <div x-show="!freeShipping">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">ঢাকা সাব-এরিয়া (৳)</label>
                    <input type="number" step="0.01" name="delivery_charge_sub_dhaka" value="{{ $landingPage->delivery_charge_sub_dhaka ?? ($onlineStore->delivery_charge_sub_dhaka ?? 100) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold">
                </div>

                <div x-show="!freeShipping">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">ঢাকার বাইরে ডেলিভারি (৳)</label>
                    <input type="number" step="0.01" name="delivery_charge_outside" value="{{ $landingPage->delivery_charge_outside ?? ($onlineStore->delivery_charge_outside_dhaka ?? 150) }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        অর্ডার ফর্মের শিরোনাম
                    </label>
                    <input type="text" name="order_form_title" value="{{ $landingPage->order_form_title ?? 'অর্ডার করতে আপনার সঠিক তথ্য দিন' }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        কল টু অ্যাকশন বাটন টেক্সট
                    </label>
                    <input type="text" name="cta_button_text" value="{{ $landingPage->cta_button_text ?? 'অর্ডার করতে এখানে ক্লিক করুন' }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-800">
                </div>
            </div>

            {{-- Customer Testimonials Repeater --}}
            <div class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        কাস্টমার রিভিউ (Testimonials &amp; Social Proof)
                    </label>
                    <button type="button" @click="addTestimonial()"
                            class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plus"></i> রিভিউ যোগ করুন
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(testi, tIdx) in testimonials" :key="tIdx">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div>
                                <input type="text" :name="`testimonials[${tIdx}][name]`" x-model="testi.name" placeholder="কাস্টমারের নাম"
                                       class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold">
                            </div>
                            <div>
                                <input type="text" :name="`testimonials[${tIdx}][city]`" x-model="testi.city" placeholder="শহর"
                                       class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-600">
                            </div>
                            <div class="sm:col-span-2 flex items-center gap-2">
                                <input type="text" :name="`testimonials[${tIdx}][comment]`" x-model="testi.comment" placeholder="রিভিউ মন্তব্য..."
                                       class="flex-1 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-700">
                                <select :name="`testimonials[${tIdx}][rating]`" x-model="testi.rating" class="bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-bold text-amber-500">
                                    <option value="5">★★★★★</option>
                                    <option value="4">★★★★☆</option>
                                </select>
                                <button type="button" @click="removeTestimonial(tIdx)" class="text-slate-400 hover:text-red-500 p-1">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- FAQ Repeater --}}
            <div class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        সাধারণ প্রশ্নোত্তর (FAQ)
                    </label>
                    <button type="button" @click="addFaq()"
                            class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                        <i class="fas fa-plus"></i> প্রশ্ন যোগ করুন
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(fItem, qIdx) in faqs" :key="qIdx">
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <input type="text" :name="`faq[${qIdx}][question]`" x-model="fItem.question" placeholder="প্রশ্ন"
                                       class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800">
                                <button type="button" @click="removeFaq(qIdx)" class="text-slate-400 hover:text-red-500 p-1">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </div>
                            <textarea :name="`faq[${qIdx}][answer]`" x-model="fItem.answer" rows="2" placeholder="উত্তর লিখুন..."
                                      class="w-full bg-white border border-slate-200 rounded-lg p-2.5 text-xs text-slate-700"></textarea>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── Section 6: Tracking & Custom CSS ────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-black">6</span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800">মার্কেটিং ট্র্যাকিং পিক্সেল (ঐচ্ছিক)</h2>
                    <p class="text-xs text-slate-400">Facebook Pixel &amp; TikTok Pixel ট্র্যাকিং আইডি</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Facebook Pixel ID</label>
                    <input type="text" name="fb_pixel_id" value="{{ $landingPage->fb_pixel_id }}" placeholder="যেমন: 123456789012345"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">TikTok Pixel ID</label>
                    <input type="text" name="tiktok_pixel_id" value="{{ $landingPage->tiktok_pixel_id }}" placeholder="যেমন: C5XXXXXX"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-mono">
                </div>
            </div>
        </div>

        {{-- ── Bottom Submit Bar ───────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('admin.landing-pages.index') }}"
               class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-50 text-sm transition-colors">
                বাতিল করুন
            </a>
            <button type="button" @click="submitForm()" :disabled="isSubmitting"
                    class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold px-8 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all text-sm disabled:opacity-50">
                <i class="fas mr-1.5" :class="isSubmitting ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                <span x-text="isSubmitting ? 'আপডেট হচ্ছে...' : 'পরিবর্তনসমূহ সেভ করুন'"></span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    $('#specificationsEditor').summernote({
        placeholder: 'পণ্য সম্পর্কিত বিস্তারিত তথ্য, সাইজ চার্ট, ম্যাটেরিয়াল বিবরণ এখানে লিখুন...',
        tabsize: 2,
        height: 220,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                for (let i = 0; i < files.length; i++) {
                    uploadSummernoteImage(files[i], $(this));
                }
            }
        }
    });

    function uploadSummernoteImage(file, $editor) {
        let data = new FormData();
        data.append('image', file);
        data.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("admin.landing-pages.upload-editor-image") }}',
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp && resp.url) {
                    $editor.summernote('insertImage', resp.url);
                }
            },
            error: function(err) {
                alert('ছবি আপলোড করতে সমস্যা হয়েছে: ' + (err.responseJSON?.message || 'ইমেজ ফাইল ও সাইজ চেক করুন।'));
            }
        });
    }
});

function landingPageBuilder() {
    @php
        $preselected = $landingPage->landingPageProducts->map(function($lp, $idx) {
            return [
                'id'               => $lp->product_id,
                'name'             => $lp->product?->name ?? 'Unknown Product',
                'sku'              => $lp->product?->sku ?? '',
                'price'            => $lp->product?->price ?? 0,
                'old_price'        => $lp->product?->old_price ?? '',
                'image'            => $lp->product?->image_url ?? $lp->product?->image,
                'package_title'    => $lp->package_title,
                'package_subtitle' => $lp->package_subtitle,
                'custom_price'     => $lp->custom_price,
                'custom_old_price' => $lp->custom_old_price,
                'is_default'       => $lp->is_default,
            ];
        });
        $defaultIdx = $landingPage->landingPageProducts->search(fn($item) => $item->is_default) ?: 0;
    @endphp

    return {
        isSubmitting: false,
        title: @json($landingPage->title),
        slug: @json($landingPage->slug),
        selectedTheme: @json($landingPage->page_theme ?: 'emerald'),
        freeShipping: @json((bool)$landingPage->free_shipping),
        productSearch: '',
        allProducts: @json($products),
        selectedProducts: @json($preselected),
        defaultIndex: {{ (int)$defaultIdx }},
        features: @json($landingPage->features ?: []),
        testimonials: @json($landingPage->testimonials ?: []),
        faqs: @json($landingPage->faq ?: []),

        get filteredProducts() {
            if (!this.productSearch.trim()) return this.allProducts;
            const q = this.productSearch.toLowerCase();
            return this.allProducts.filter(p => p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)));
        },

        isProductSelected(id) {
            return this.selectedProducts.some(p => p.id === id);
        },

        toggleProduct(p) {
            const idx = this.selectedProducts.findIndex(item => item.id === p.id);
            if (idx > -1) {
                this.selectedProducts.splice(idx, 1);
            } else {
                const count = this.selectedProducts.length + 1;
                this.selectedProducts.push({
                    id: p.id,
                    name: p.name,
                    sku: p.sku,
                    price: p.price,
                    old_price: p.old_price,
                    image: p.image_url || p.image,
                    package_title: count === 1 ? '১টি প্যাকেজ' : (count === 2 ? '২টির স্পেশাল কম্বো' : `${count}টির সেভার প্যাক`),
                    package_subtitle: count === 1 ? 'স্ট্যান্ডার্ড চয়েস' : 'জনপ্রিয় অফার',
                    custom_price: p.price,
                    custom_old_price: p.old_price
                });
            }
        },

        removeProduct(id) {
            this.selectedProducts = this.selectedProducts.filter(p => p.id !== id);
        },

        addFeature() {
            this.features.push({ icon: 'fa-check', title: '', desc: '' });
        },
        removeFeature(idx) {
            this.features.splice(idx, 1);
        },

        addTestimonial() {
            this.testimonials.push({ name: '', city: 'ঢাকা', comment: '', rating: 5 });
        },
        removeTestimonial(idx) {
            this.testimonials.splice(idx, 1);
        },

        addFaq() {
            this.faqs.push({ question: '', answer: '' });
        },
        removeFaq(idx) {
            this.faqs.splice(idx, 1);
        },

        submitForm() {
            if (!this.title.trim()) {
                alert('অনুগ্রহ করে ল্যান্ডিং পেজের শিরোনাম লিখুন!');
                return;
            }
            if (this.selectedProducts.length === 0) {
                alert('অনুগ্রহ করে অন্তত ১টি প্রোডাক্ট সিলেক্ট করুন!');
                return;
            }
            if (this.isSubmitting) return;
            this.isSubmitting = true;

            // Sync Summernote content to textarea before submission
            try {
                if (window.$ && $('#specificationsEditor').length) {
                    $('#specificationsEditor').val($('#specificationsEditor').summernote('code'));
                }
            } catch (e) {
                console.error(e);
            }

            document.getElementById('landingPageForm').submit();
        }
    };
}
</script>
@endpush
@endsection

