@extends('layouts.app')
@section('title', 'Landing Pages')
@section('heading', 'Landing Pages')

@section('content')
<div class="space-y-6">

    {{-- ── Top Hero Banner ───────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 p-6 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2 text-blue-200 border border-white/20">
                    <i class="fas fa-wand-magic-sparkles text-amber-300"></i> Marketing &amp; Sales Funnels
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight flex items-center gap-2.5">
                    Landing Page Builder
                </h1>
                <p class="text-blue-100 text-sm mt-1 max-w-xl">
                    উচ্চ রূপান্তরকারী (High-Converting) ল্যান্ডিং পেজ তৈরি করুন। একাধিক প্রোডাক্ট প্যাকেজ, ইনস্ট্যান্ট ক্যাশ অন ডেলিভারি ফর্ম ও প্রিমিয়াম ডিজাইনে সেলস বৃদ্ধি করুন।
                </p>
            </div>

            <a href="{{ route('admin.landing-pages.create') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black px-5 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 text-sm flex-shrink-0">
                <i class="fas fa-plus text-xs"></i>
                <span>নতুন ল্যান্ডিং পেজ তৈরি করুন</span>
            </a>
        </div>
    </div>

    {{-- ── Analytics KPI Cards ───────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-file-lines"></i>
            </div>
            <div>
                <div class="text-[11.5px] font-bold text-slate-400 uppercase tracking-wider">মোট ল্যান্ডিং পেজ</div>
                <div class="text-2xl font-black text-slate-800 leading-tight mt-0.5">{{ number_format($totalCount) }}</div>
            </div>
        </div>

        <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[11.5px] font-bold text-slate-400 uppercase tracking-wider">এক্টিভ পেজ</div>
                <div class="text-2xl font-black text-emerald-600 leading-tight mt-0.5">{{ number_format($activeCount) }}</div>
            </div>
        </div>

        <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-eye"></i>
            </div>
            <div>
                <div class="text-[11.5px] font-bold text-slate-400 uppercase tracking-wider">মোট ভিজিটর (Views)</div>
                <div class="text-2xl font-black text-purple-600 leading-tight mt-0.5">{{ number_format($totalViews) }}</div>
            </div>
        </div>

        <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-cart-shopping"></i>
            </div>
            <div>
                <div class="text-[11.5px] font-bold text-slate-400 uppercase tracking-wider">মোট অর্ডার</div>
                <div class="text-2xl font-black text-amber-600 leading-tight mt-0.5">{{ number_format($totalOrders) }}</div>
            </div>
        </div>
    </div>

    {{-- ── Filters & Search ──────────────────────────────────────────── --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.landing-pages.index') }}" class="flex-1 w-full sm:w-auto flex items-center gap-2">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="শিরোনাম বা স্লাগ দিয়ে খুঁজুন..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <select name="status" onchange="this.form.submit()"
                    class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <option value="">সকল স্ট্যাটাস</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>এক্টিভ</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
            </select>

            @if(request('q') || request('status'))
            <a href="{{ route('admin.landing-pages.index') }}" class="p-2 text-slate-400 hover:text-slate-600 text-xs" title="রিসেট">
                <i class="fas fa-times"></i>
            </a>
            @endif
        </form>

        <div class="text-xs font-semibold text-slate-500">
            মোট {{ $landingPages->total() }} টি পেজ পাওয়া গেছে
        </div>
    </div>

    {{-- ── Landing Pages List ────────────────────────────────────────── --}}
    @if($landingPages->isEmpty())
    <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-2xl mb-4 shadow-inner">
            <i class="fas fa-rocket"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800">এখনো কোনো ল্যান্ডিং পেজ তৈরি করা হয়নি</h3>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-1 mb-6">
            সহজেই আপনার পণ্যগুলোর জন্য আকর্ষণীয় ও হাই-কনভার্টিং ল্যান্ডিং পেজ তৈরি করুন।
        </p>
        <a href="{{ route('admin.landing-pages.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl shadow-md text-sm transition-colors">
            <i class="fas fa-plus"></i> প্রথম ল্যান্ডিং পেজ তৈরি করুন
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($landingPages as $page)
        @php
            $theme = $page->theme_config;
            $primaryProduct = $page->landingPageProducts->first()?->product;
            $heroImg = $page->hero_image_url ?: ($primaryProduct?->image_url ?? asset('images/placeholder.png'));
            $convRate = $page->views_count > 0 ? round(($page->orders_count / $page->views_count) * 100, 1) : 0;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col overflow-hidden group">
            
            {{-- Image & Badge Header --}}
            <div class="relative h-44 bg-slate-100 overflow-hidden">
                <img src="{{ $heroImg }}" alt="{{ $page->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                {{-- Status & Theme Badges --}}
                <div class="absolute top-3 left-3 right-3 flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold shadow-xs backdrop-blur-md {{ $page->status === 'active' ? 'bg-emerald-500/90 text-white' : 'bg-slate-700/90 text-slate-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $page->status === 'active' ? 'bg-white animate-pulse' : 'bg-slate-400' }}"></span>
                        {{ $page->status === 'active' ? 'একটিভ' : 'নিষ্ক্রিয়' }}
                    </span>

                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/90 text-slate-800 shadow-xs backdrop-blur-md border border-white/50">
                        {{ $theme['name'] }}
                    </span>
                </div>

                {{-- Title inside image overlay --}}
                <div class="absolute bottom-3 left-3 right-3">
                    <h3 class="text-white font-extrabold text-base leading-snug drop-shadow-sm line-clamp-1">
                        {{ $page->title }}
                    </h3>
                    <div class="flex items-center gap-2 text-white/80 text-[11.5px] mt-0.5">
                        <i class="fas fa-link text-[10px] text-amber-300"></i>
                        <span class="font-mono truncate">/landing/{{ $page->slug }}</span>
                    </div>
                </div>
            </div>

            {{-- Products & Stats Body --}}
            <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                
                {{-- Products preview --}}
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                        <span>সংযুক্ত প্রোডাক্ট ({{ $page->landingPageProducts->count() }}টি)</span>
                        @if($page->free_shipping)
                            <span class="text-emerald-600 font-bold lowercase bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                ফ্রি শিপিং
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1.5 max-h-24 overflow-y-auto pr-1">
                        @foreach($page->landingPageProducts->take(3) as $lpProduct)
                        <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                @if($lpProduct->product?->image_url)
                                <img src="{{ $lpProduct->product->image_url }}" alt="thumb" class="w-6 h-6 rounded object-cover flex-shrink-0">
                                @else
                                <div class="w-6 h-6 rounded bg-slate-200 flex items-center justify-center text-[9px] text-slate-500">
                                    <i class="fas fa-box"></i>
                                </div>
                                @endif
                                <span class="font-semibold text-slate-700 truncate">
                                    {{ $lpProduct->package_title ?: $lpProduct->product?->name }}
                                </span>
                            </div>
                            <span class="font-bold text-blue-600 flex-shrink-0">
                                ৳{{ number_format($lpProduct->effective_price, 0) }}
                            </span>
                        </div>
                        @endforeach
                        @if($page->landingPageProducts->count() > 3)
                        <div class="text-[10px] text-slate-400 text-center italic">
                            + আরো {{ $page->landingPageProducts->count() - 3 }} টি প্রোডাক্ট
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Performance Counters --}}
                <div class="grid grid-cols-3 gap-2 py-2 px-3 rounded-xl bg-slate-50 border border-slate-100/80 text-center">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">ভিজিটর</div>
                        <div class="text-sm font-black text-slate-800 mt-0.5">{{ number_format($page->views_count) }}</div>
                    </div>
                    <div class="border-x border-slate-200">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">অর্ডার</div>
                        <div class="text-sm font-black text-emerald-600 mt-0.5">{{ number_format($page->orders_count) }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">রূপান্তর</div>
                        <div class="text-sm font-black text-purple-600 mt-0.5">{{ $convRate }}%</div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
                    {{-- Copy Link --}}
                    <button type="button"
                            onclick="navigator.clipboard.writeText('{{ route('landing.show', $page->slug) }}'); alert('ল্যান্ডিং পেজ লিঙ্ক কপি হয়েছে:\n{{ route('landing.show', $page->slug) }}')"
                            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition-colors"
                            title="লিঙ্ক কপি করুন">
                        <i class="fas fa-copy"></i>
                    </button>

                    {{-- Public View --}}
                    <a href="{{ route('landing.show', $page->slug) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition-colors">
                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                        <span>ভিজিট</span>
                    </a>

                    {{-- Toggle Status --}}
                    <form method="POST" action="{{ route('admin.landing-pages.toggle', $page) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="p-2 rounded-xl {{ $page->status === 'active' ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100' }} text-xs transition-colors"
                                title="{{ $page->status === 'active' ? 'নিষ্ক্রিয় করুন' : 'এক্টিভ করুন' }}">
                            <i class="fas {{ $page->status === 'active' ? 'fa-toggle-on text-base' : 'fa-toggle-off text-base' }}"></i>
                        </button>
                    </form>

                    {{-- Edit --}}
                    <a href="{{ route('admin.landing-pages.edit', $page) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition-colors">
                        <i class="fas fa-pen-to-square text-[10px]"></i>
                        <span>এডিট</span>
                    </a>

                    {{-- Delete --}}
                    <form method="POST" action="{{ route('admin.landing-pages.destroy', $page) }}"
                          onsubmit="return confirm('আপনি কি নিশ্চিত এই ল্যান্ডিং পেজটি মুছে ফেলতে চান?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 text-xs transition-colors" title="মুছে ফেলুন">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $landingPages->links() }}
    </div>
    @endif

</div>
@endsection

