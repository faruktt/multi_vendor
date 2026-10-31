<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $landingPage->title }} — {{ $appSettings['name'] ?? $branch->system_name ?? 'Exclusive Offer' }}</title>
    @if(!empty($appSettings['favicon']))
    <link rel="icon" href="{{ $appSettings['favicon'] }}">
    @endif

    {{-- Tailwind CSS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">

    {{-- Global & Custom Tracking (GTM, DataLayer, Meta Pixel) --}}
    @php
        $originalFbPixel = $appSettings['fb_pixel_id'] ?? null;
        if (!empty($landingPage->fb_pixel_id)) {
            $appSettings['fb_pixel_id'] = $landingPage->fb_pixel_id;
        }
    @endphp
    @include('shop.partials.analytics')
    @php
        $appSettings['fb_pixel_id'] = $originalFbPixel;
    @endphp

    {{-- TikTok Pixel if configured --}}
    @if(!empty($landingPage->tiktok_pixel_id))
    <script>
        !function (w, d, t) {
            w.TiktokAnalyticsObject=t;var tt=w[t]=w[t]||[];tt.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],tt.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<tt.methods.length;i++)tt.setAndDefer(tt,tt.methods[i]);tt.instance=function(t){for(var e=tt._i[t]||[],n=0;n<tt.methods.length;n++)tt.setAndDefer(e,tt.methods[n]);return e},tt.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";tt._i=tt._i||{},tt._i[e]=[],tt._i[e]._u=i,s=d.createElement("script"),s.type="text/javascript",s.async=!0,s.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(s,a)};
            tt.load('{{ $landingPage->tiktok_pixel_id }}');
            tt.page();
        }(window, document, 'ttq');
    </script>
    @endif

    @php
        $theme = $landingPage->theme_config;
        $primaryColor = $theme['primary'];
        $rawPhone = preg_replace('/\D/', '', $appSettings['phone'] ?? '');
        $whatsappNumber = $rawPhone ? '880' . ltrim($rawPhone, '0') : null;
    @endphp

    <style>
        body { font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
        [x-cloak] { display: none !important; }

        :root {
            --primary: {{ $theme['primary'] }};
            --primary-dark: {{ $theme['primary_dark'] }};
            --accent: {{ $theme['accent'] }};
        }

        .theme-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .theme-glow {
            box-shadow: 0 10px 25px -5px {{ $theme['primary'] }}40;
        }

        .pulse-subtle {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 10px {{ $theme['primary'] }}60); }
            50% { transform: scale(1.02); filter: drop-shadow(0 0 18px {{ $theme['primary'] }}90); }
        }

        {!! $landingPage->custom_css !!}
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased selection:bg-emerald-500 selection:text-white"
      x-data="landingOrderApp()">

{{-- Google Tag Manager (noscript) --}}
@if(!empty($appSettings['gtm_id']))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $appSettings['gtm_id'] }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

    {{-- ── 1. Top Urgency Banner ───────────────────────────────────────── --}}
    <div class="theme-gradient text-white text-xs sm:text-sm font-bold py-2.5 px-4 text-center sticky top-0 z-50 shadow-md">
        <div class="max-w-4xl mx-auto flex items-center justify-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black uppercase tracking-wider text-amber-300">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                সীমিত সময়ের ধামাকা অফার
            </span>
            <span>সারা দেশে ক্যাশ অন হোম ডেলিভারি সুবিধা!</span>
            <a href="#orderFormSection" class="underline underline-offset-2 hover:text-amber-200 transition-colors font-extrabold ml-1">
                অর্ডার করতে নিচে যান &rarr;
            </a>
        </div>
    </div>

    {{-- ── 2. Brand Header ────────────────────────────────────────────── --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 py-3.5 px-4 sticky top-[37px] z-40 transition-all">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="{{ route('root') }}" class="flex items-center gap-2">
                @if(!empty($appSettings['logo']))
                    <img src="{{ $appSettings['logo'] }}" alt="{{ $appSettings['name'] ?? 'Logo' }}" class="h-9 sm:h-11 max-w-[180px] object-contain">
                @else
                    <span class="text-xl font-black tracking-tight text-slate-900">{{ $appSettings['name'] ?? $branch->system_name ?? 'PREMIUM STORE' }}</span>
                @endif
            </a>

            {{-- Quick Order / Call button --}}
            <div class="flex items-center gap-3">
                @if(!empty($appSettings['phone']))
                <a href="tel:{{ $appSettings['phone'] }}"
                   class="hidden sm:inline-flex items-center gap-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition-colors">
                    <i class="fas fa-phone-volume text-emerald-600"></i>
                    <span>{{ $appSettings['phone'] }}</span>
                </a>
                @endif

                <a href="#orderFormSection"
                   class="inline-flex items-center gap-2 text-xs sm:text-sm font-extrabold text-white {{ $theme['btn_bg'] }} px-4 sm:px-5 py-2.5 rounded-xl shadow-md transition-all hover:scale-105">
                    <i class="fas fa-bag-shopping text-xs"></i>
                    <span>অর্ডার করুন</span>
                </a>
            </div>
        </div>
    </header>

    {{-- ── 3. HERO SECTION ────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden pt-6 pb-12 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto space-y-6">

            {{-- Urgency Badge & Countdown Timer --}}
            <div class="text-center space-y-3">
                @if(!empty($landingPage->hero_badge))
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full {{ $theme['badge_bg'] }} border text-xs sm:text-sm font-black shadow-xs tracking-wide">
                    <span>{{ $landingPage->hero_badge }}</span>
                </div>
                @endif

                {{-- Live Countdown Timer --}}
                <div class="flex items-center justify-center gap-2 sm:gap-3 pt-1">
                    <span class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider">অফার শেষ হতে বাকি:</span>
                    <div class="flex items-center gap-1.5 font-mono text-white text-xs sm:text-sm font-black">
                        <div class="bg-slate-900 px-2.5 py-1.5 rounded-lg shadow-inner min-w-[38px] text-center">
                            <span x-text="hours">11</span>
                            <div class="text-[8px] font-sans text-slate-400 font-bold">ঘণ্টা</div>
                        </div>
                        <span class="text-slate-900 font-bold">:</span>
                        <div class="bg-slate-900 px-2.5 py-1.5 rounded-lg shadow-inner min-w-[38px] text-center">
                            <span x-text="minutes">45</span>
                            <div class="text-[8px] font-sans text-slate-400 font-bold">মিনিট</div>
                        </div>
                        <span class="text-slate-900 font-bold">:</span>
                        <div class="bg-slate-900 px-2.5 py-1.5 rounded-lg shadow-inner min-w-[38px] text-center">
                            <span x-text="seconds" class="text-amber-400">20</span>
                            <div class="text-[8px] font-sans text-slate-400 font-bold">সেকেন্ড</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hero Headline --}}
            <div class="text-center space-y-3">
                <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-slate-900 leading-tight sm:leading-tight">
                    {!! nl2br(e($landingPage->hero_title)) !!}
                </h1>

                @if(!empty($landingPage->hero_subtitle))
                <p class="text-slate-600 text-sm sm:text-lg max-w-2xl mx-auto leading-relaxed">
                    {!! nl2br(e($landingPage->hero_subtitle)) !!}
                </p>
                @endif
            </div>

            {{-- Hero Media Showcase (Video or Image) --}}
            <div class="rounded-3xl overflow-hidden border-2 border-slate-200/90 shadow-2xl bg-white p-2">
                @if(!empty($landingPage->hero_video_url))
                    @php
                        // Convert YouTube URL to embed
                        $videoUrl = $landingPage->hero_video_url;
                        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $videoUrl, $matches)) {
                            $videoUrl = 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=0&rel=0';
                        }
                    @endphp
                    <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black shadow-inner">
                        <iframe src="{{ $videoUrl }}" class="w-full h-full" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                @elseif(!empty($landingPage->hero_image_url))
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-[16/9] bg-slate-100 flex items-center justify-center">
                        <img src="{{ $landingPage->hero_image_url }}" alt="{{ $landingPage->title }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif
            </div>

            {{-- Quick Trust Checklist Pills --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2">
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs text-xs font-bold text-slate-700">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 text-[11px]">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>ক্যাশ অন ডেলিভারি</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs text-xs font-bold text-slate-700">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-[11px]">
                        <i class="fas fa-truck-fast"></i>
                    </span>
                    <span>সারা দেশে ডেলিভারি</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs text-xs font-bold text-slate-700">
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 text-[11px]">
                        <i class="fas fa-eye"></i>
                    </span>
                    <span>দেখে নেওয়ার সুযোগ</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs text-xs font-bold text-slate-700">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0 text-[11px]">
                        <i class="fas fa-shield-halved"></i>
                    </span>
                    <span>১০০% কোয়ালিটি গ্যারান্টি</span>
                </div>
            </div>

            {{-- Main CTA Button --}}
            <div class="text-center pt-2">
                <a href="#orderFormSection"
                   class="pulse-subtle inline-flex items-center justify-center gap-3 w-full sm:w-auto text-base sm:text-xl font-black text-white {{ $theme['btn_bg'] }} px-8 sm:px-12 py-4 sm:py-5 rounded-2xl shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-1">
                    <i class="fas fa-cart-arrow-down text-xl"></i>
                    <span>{{ $landingPage->cta_button_text }}</span>
                </a>
                <div class="text-xs text-slate-500 font-semibold mt-2">
                    ⚡ পণ্য হাতে পেয়ে টাকা পরিশোধ করুন • কোনো অগ্রিম টাকা লাগবে না
                </div>
            </div>

        </div>
    </section>

    {{-- ── 4. MULTI-PRODUCT / PACKAGE SELECTOR ─────────────────────────── --}}
    @if($landingPage->landingPageProducts->isNotEmpty())
    <section class="py-10 px-4 sm:px-6 bg-slate-100/70 border-y border-slate-200/80">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="text-center space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    প্যাকেজ অফার
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    আপনার পছন্দের প্যাকেজ নির্বাচন করুন
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    একাধিক নিলে পাচ্ছেন বিশেষ ছাড়! আপনার সুবিধামত যেকোনো একটি প্যাকেজে ক্লিক করুন
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($landingPage->landingPageProducts as $idx => $pPkg)
                @php
                    $pkgProd = $pPkg->product;
                    $effPrice = $pPkg->effective_price;
                    $oldPrice = $pPkg->effective_old_price;
                    $savings = ($oldPrice && $oldPrice > $effPrice) ? ($oldPrice - $effPrice) : 0;
                    $thumb = $pkgProd?->image_url ?: asset('images/placeholder.png');
                @endphp
                <div @click="selectPackage({{ $pPkg->id }}, {{ $effPrice }}, '{{ addslashes($pPkg->package_title ?: $pkgProd?->name) }}', '{{ $thumb }}')"
                     :class="selectedPackageId === {{ $pPkg->id }} ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-500/30 shadow-lg scale-[1.02]' : 'border-slate-200 bg-white hover:border-slate-300 shadow-xs'"
                     class="relative rounded-2xl border-2 p-5 cursor-pointer transition-all duration-200 flex flex-col justify-between group">
                    
                    {{-- Most Popular / Default Ribbon --}}
                    @if($pPkg->is_default || $loop->first)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 text-[10px] font-black uppercase tracking-wider px-3 py-0.5 rounded-full shadow-xs">
                            সবচেয়ে জনপ্রিয়
                        </span>
                    </div>
                    @endif

                    <div class="space-y-3">
                        {{-- Radio & Image --}}
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                     :class="selectedPackageId === {{ $pPkg->id }} ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300 bg-white'">
                                    <div class="w-2 h-2 rounded-full bg-white" x-show="selectedPackageId === {{ $pPkg->id }}"></div>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900 group-hover:text-emerald-700 transition-colors">
                                        {{ $pPkg->package_title ?: $pkgProd?->name }}
                                    </h3>
                                    @if($pPkg->package_subtitle)
                                    <p class="text-xs text-slate-500 font-semibold">{{ $pPkg->package_subtitle }}</p>
                                    @endif
                                </div>
                            </div>

                            <img src="{{ $thumb }}" alt="package" class="w-12 h-12 rounded-xl object-cover border border-slate-100 flex-shrink-0 shadow-2xs">
                        </div>

                        {{-- Pricing Box --}}
                        <div class="pt-3 border-t border-slate-100 flex items-baseline justify-between gap-2">
                            <div>
                                <span class="text-2xl font-black text-slate-900">৳{{ number_format($effPrice, 0) }}</span>
                                @if($oldPrice && $oldPrice > $effPrice)
                                <span class="text-xs text-slate-400 line-through ml-1.5 font-bold">৳{{ number_format($oldPrice, 0) }}</span>
                                @endif
                            </div>

                            @if($savings > 0)
                            <span class="text-[11px] font-black text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-md">
                                সাশ্রয় ৳{{ number_format($savings, 0) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Click CTA --}}
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <button type="button"
                                :class="selectedPackageId === {{ $pPkg->id }} ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 group-hover:bg-slate-200'"
                                class="w-full py-2 rounded-xl text-xs font-black transition-colors text-center">
                            <span x-text="selectedPackageId === {{ $pPkg->id }} ? '✓ নির্বাচিত প্যাকেজ' : 'এই প্যাকেজটি নিন'"></span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>
    @endif

    {{-- ── 5. FEATURES & HIGHLIGHTS ───────────────────────────────────── --}}
    @if(!empty($landingPage->features) && count($landingPage->features) > 0)
    <section class="py-12 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto space-y-8">
            <div class="text-center space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    বিশেষ সুবিধাসমূহ
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    কেন এই পণ্যটি আপনার জন্য সেরা?
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                @foreach($landingPage->features as $feat)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-shadow flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $theme['gradient'] }} text-white flex items-center justify-center flex-shrink-0 text-base shadow-sm">
                        <i class="fas {{ $feat['icon'] ?? 'fa-check' }}"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">{{ $feat['title'] }}</h3>
                        @if(!empty($feat['desc']))
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $feat['desc'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ── 6. GALLERY SHOWCASE ────────────────────────────────────────── --}}
    @if(!empty($landingPage->gallery_image_urls) && count($landingPage->gallery_image_urls) > 0)
    <section class="py-10 px-4 sm:px-6 bg-slate-50 border-t border-slate-200/80">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="text-center space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-purple-600 bg-purple-50 px-3 py-1 rounded-full border border-purple-200">
                    রিয়েল ফটো গ্যালারি
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    পণ্যের আসল লুক ও ফিনিশিং দেখুন
                </h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($landingPage->gallery_image_urls as $imgUrl)
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-2xs group cursor-pointer">
                    <img src="{{ $imgUrl }}" alt="gallery image"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ── 7. DETAILED SPECIFICATIONS ─────────────────────────────────── --}}
    @if(!empty($landingPage->specifications))
    <section class="py-10 px-4 sm:px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-4">
            <h3 class="text-xl font-extrabold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-list-check text-emerald-600"></i>
                <span>পণ্যের বিস্তারিত বিবরণ ও স্পেসিফিকেশন</span>
            </h3>
            <div class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed">
                {!! $landingPage->specifications !!}
            </div>
        </div>
    </section>
    @endif

    {{-- ── 8. CUSTOMER REVIEWS & TESTIMONIALS ─────────────────────────── --}}
    @if(!empty($landingPage->testimonials) && count($landingPage->testimonials) > 0)
    <section class="py-12 px-4 sm:px-6 bg-slate-100/60 border-t border-slate-200/80">
        <div class="max-w-4xl mx-auto space-y-8">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center gap-1.5 text-amber-500 text-sm">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <span class="text-slate-800 font-bold ml-1 text-xs">৪.৯/৫ রেটিং (৫০০+ সন্তুষ্ট ক্রেতা)</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    আমাদের কাস্টমারদের মতামত
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($landingPage->testimonials as $t)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs">
                                {{ mb_substr($t['name'] ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">{{ $t['name'] }}</h4>
                                <span class="text-[10px] text-slate-400">{{ $t['city'] ?? 'ঢাকা' }} • ভেরিফাইড ক্রেতা</span>
                            </div>
                        </div>
                        <div class="text-amber-400 text-xs">
                            @for($s = 0; $s < ($t['rating'] ?? 5); $s++)
                                <i class="fas fa-star"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed italic">
                        "{!! nl2br(e($t['comment'])) !!}"
                    </p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ── 9. DIRECT 1-CLICK ORDER FORM ───────────────────────────────── --}}
    <section id="orderFormSection" class="py-12 px-4 sm:px-6">
        <div class="max-w-2xl mx-auto">
            
            <div class="bg-white rounded-3xl border-2 border-emerald-500/80 shadow-2xl overflow-hidden">
                
                {{-- Form Header Banner --}}
                <div class="theme-gradient p-6 text-white text-center space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-bold text-amber-300 mb-1">
                        <i class="fas fa-lock text-[10px]"></i> ১০০% নিরাপদ ক্যাশ অন ডেলিভারি
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                        {{ $landingPage->order_form_title }}
                    </h2>
                    <p class="text-xs text-white/90">
                        নিচের তথ্যগুলো দিয়ে "অর্ডার নিশ্চিত করুন" বাটনে ক্লিক করুন।
                    </p>
                </div>

                {{-- Form Content --}}
                <form method="POST" action="{{ route('landing.order', $landingPage->slug) }}" class="p-6 sm:p-8 space-y-5" @submit="submitOrder">
                    @csrf
                    <input type="hidden" name="package_id" :value="selectedPackageId">

                    {{-- Selected Package Summary Card --}}
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img :src="selectedPackageThumb" class="w-12 h-12 rounded-xl object-cover border border-slate-200">
                            <div>
                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">নির্বাচিত প্যাকেজ</span>
                                <h4 class="text-sm font-extrabold text-slate-900" x-text="selectedPackageTitle"></h4>
                                <div class="text-xs font-black text-slate-800" x-text="'৳' + selectedPackagePrice"></div>
                            </div>
                        </div>

                        {{-- Quantity Controller --}}
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500">পরিমাণ:</span>
                            <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden shadow-2xs">
                                <button type="button" @click="if(quantity > 1) quantity--" class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold">-</button>
                                <input type="number" name="quantity" x-model="quantity" min="1" max="20" class="w-8 text-center text-xs font-black text-slate-800 border-x border-slate-200 focus:outline-none">
                                <button type="button" @click="quantity++" class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Info Inputs --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                আপনার পূর্ণ নাম <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" name="name" required placeholder="আপনার পুরো নাম লিখুন"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                মোবাইল নম্বর <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="tel" name="phone" required placeholder="০১XXXXXXXXX"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                আপনার সম্পূর্ণ ঠিকানা <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-location-dot absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                                <textarea name="address" rows="2" required placeholder="বাসা নং, রোড নং, এলাকা, উপজেলা ও জেলা লিখুন..."
                                          class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                            </div>
                        </div>

                        {{-- Delivery Zone Radio Selector --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                ডেলিভারি এরিয়া নির্বাচন করুন <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <label class="p-3 rounded-xl border cursor-pointer transition-all flex items-center justify-between"
                                       :class="deliveryZone === 'inside' ? 'border-emerald-600 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-slate-200 bg-slate-50'">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="delivery_zone" value="inside" x-model="deliveryZone" class="text-emerald-600">
                                        <span class="text-xs font-bold text-slate-800">ঢাকার ভেতরে</span>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600" x-text="freeShipping ? 'ফ্রি' : '৳' + deliveryCharges.inside"></span>
                                </label>

                                <label class="p-3 rounded-xl border cursor-pointer transition-all flex items-center justify-between"
                                       :class="deliveryZone === 'sub_dhaka' ? 'border-emerald-600 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-slate-200 bg-slate-50'">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="delivery_zone" value="sub_dhaka" x-model="deliveryZone" class="text-emerald-600">
                                        <span class="text-xs font-bold text-slate-800">ঢাকা সাব-এরিয়া</span>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600" x-text="freeShipping ? 'ফ্রি' : '৳' + deliveryCharges.sub_dhaka"></span>
                                </label>

                                <label class="p-3 rounded-xl border cursor-pointer transition-all flex items-center justify-between"
                                       :class="deliveryZone === 'outside' ? 'border-emerald-600 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-slate-200 bg-slate-50'">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="delivery_zone" value="outside" x-model="deliveryZone" class="text-emerald-600">
                                        <span class="text-xs font-bold text-slate-800">ঢাকার বাইরে</span>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600" x-text="freeShipping ? 'ফ্রি' : '৳' + deliveryCharges.outside"></span>
                                </label>
                            </div>
                        </div>

                        {{-- Optional Note --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">
                                বিশেষ কোনো নির্দেশনা থাকলে লিখুন (ঐচ্ছিক)
                            </label>
                            <input type="text" name="note" placeholder="যেমন: সকাল ১০টার পর ডেলিভারি দিন"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-medium text-slate-700">
                        </div>
                    </div>

                    {{-- Calculation Breakdown Box --}}
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs font-semibold">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>পণ্যের সাবটোটাল:</span>
                            <span class="font-bold text-slate-800" x-text="'৳' + (selectedPackagePrice * quantity).toLocaleString()"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>ডেলিভারি চার্জ:</span>
                            <span class="font-bold" :class="currentDeliveryCharge === 0 ? 'text-emerald-600' : 'text-slate-800'"
                                  x-text="currentDeliveryCharge === 0 ? 'ফ্রি ডেলিভারি' : '৳' + currentDeliveryCharge"></span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-base font-black text-slate-900">
                            <span>সর্বমোট পরিশোধযোগ্য:</span>
                            <span class="text-xl text-emerald-600" x-text="'৳' + totalPayable.toLocaleString()"></span>
                        </div>
                    </div>

                    {{-- Big Animated Submit Button --}}
                    <button type="submit" :disabled="isSubmitting"
                            class="w-full py-4 sm:py-5 rounded-2xl text-white text-base sm:text-lg font-black {{ $theme['btn_bg'] }} shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-3">
                        <span x-show="!isSubmitting">
                            <i class="fas fa-lock text-sm"></i>
                            অর্ডার নিশ্চিত করুন — ৳<span x-text="totalPayable.toLocaleString()"></span>
                        </span>
                        <span x-show="isSubmitting" class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i> অর্ডার প্রক্রিয়াধীন...
                        </span>
                    </button>

                    <p class="text-[11.5px] text-center text-slate-400 font-medium">
                        🛡️ আপনার তথ্য সম্পূর্ণ সুরক্ষিত। অর্ডার করার পর আমাদের কাস্টমার প্রতিনিধি আপনার সাথে যোগাযোগ করবেন।
                    </p>

                </form>

            </div>

        </div>
    </section>

    {{-- ── 10. FAQ ACCORDION ──────────────────────────────────────────── --}}
    @if(!empty($landingPage->faq) && count($landingPage->faq) > 0)
    <section class="py-12 px-4 sm:px-6 bg-slate-50 border-t border-slate-200/80">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="text-center space-y-1">
                <span class="text-xs font-black uppercase tracking-widest text-slate-500">FAQ</span>
                <h2 class="text-2xl font-black text-slate-900">সাধারণ কিছু প্রশ্নোত্তর</h2>
            </div>

            <div class="space-y-3" x-data="{ openQ: 0 }">
                @foreach($landingPage->faq as $fIdx => $qa)
                <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
                    <button type="button" @click="openQ = (openQ === {{ $fIdx }} ? null : {{ $fIdx }})"
                            class="w-full p-4 text-left font-extrabold text-sm text-slate-800 flex items-center justify-between gap-4">
                        <span>{{ $qa['question'] }}</span>
                        <i class="fas fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                           :class="openQ === {{ $fIdx }} ? 'rotate-180 text-emerald-600' : ''"></i>
                    </button>
                    <div x-show="openQ === {{ $fIdx }}" x-cloak class="px-4 pb-4 pt-1 text-xs text-slate-600 leading-relaxed border-t border-slate-100">
                        {!! nl2br(e($qa['answer'])) !!}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ── 11. FOOTER ─────────────────────────────────────────────────── --}}
    <footer class="bg-slate-950 text-white py-8 px-4 text-center text-xs space-y-2">
        <div class="font-extrabold text-sm">{{ $appSettings['name'] ?? $branch->system_name ?? 'PREMIUM STORE' }}</div>
        <p class="text-slate-400">&copy; {{ date('Y') }} All Rights Reserved. Developed for High-Conversion Ecommerce.</p>
    </footer>

    {{-- ── 12. MOBILE STICKY BOTTOM ORDER BAR ─────────────────────────── --}}
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 z-40 flex items-center justify-between gap-3 shadow-2xl">
        <div>
            <div class="text-[10px] text-slate-400 font-bold uppercase">সর্বমোট মূল্য:</div>
            <div class="text-lg font-black text-emerald-600 leading-none">
                ৳<span x-text="totalPayable.toLocaleString()"></span>
            </div>
        </div>

        <a href="#orderFormSection"
           class="flex-1 py-3 px-4 rounded-xl text-center text-xs font-black text-white {{ $theme['btn_bg'] }} shadow-md flex items-center justify-center gap-1.5">
            <i class="fas fa-bag-shopping text-xs"></i>
            <span>এখনই অর্ডার করুন</span>
        </a>
    </div>

    {{-- ── 13. FLOATING WHATSAPP & CALL SHORTCUTS ─────────────────────── --}}
    <div class="fixed bottom-16 sm:bottom-6 left-4 z-40 flex flex-col gap-2.5">
        @if($whatsappNumber)
        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode('আসসালামু আলাইকুম, আমি ' . $landingPage->title . ' সম্পর্কে জানতে চাই।') }}"
           target="_blank" rel="noopener"
           class="w-12 h-12 rounded-full bg-[#25D366] text-white flex items-center justify-center text-2xl shadow-xl hover:scale-110 transition-transform"
           title="WhatsApp-এ যোগাযোগ করুন">
            <i class="fab fa-whatsapp"></i>
        </a>
        @endif

        @if(!empty($appSettings['phone']))
        <a href="tel:{{ $appSettings['phone'] }}"
           class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg shadow-xl hover:scale-110 transition-transform"
           title="ফোন করুন">
            <i class="fas fa-phone"></i>
        </a>
        @endif
    </div>

    {{-- Alpine Order Logic --}}
    <script>
    function landingOrderApp() {
        @php
            $defaultPkg = $landingPage->default_package;
            $initialPrice = $defaultPkg ? $defaultPkg->effective_price : 0;
            $initialTitle = $defaultPkg ? ($defaultPkg->package_title ?: $defaultPkg->product?->name) : '';
            $initialThumb = $defaultPkg?->product?->image_url ?: ($landingPage->hero_image_url ?: '/images/placeholder.png');
        @endphp

        return {
            selectedPackageId: {{ $defaultPkg?->id ?? 0 }},
            selectedPackagePrice: {{ (float) $initialPrice }},
            selectedPackageTitle: @json($initialTitle),
            selectedPackageThumb: @json($initialThumb),
            quantity: 1,
            deliveryZone: 'inside',
            freeShipping: @json((bool)$landingPage->free_shipping),
            deliveryCharges: {
                inside: {{ (float)$deliveryInside }},
                sub_dhaka: {{ (float)$deliverySubDhaka }},
                outside: {{ (float)$deliveryOutside }},
            },
            isSubmitting: false,

            // Timer countdown
            hours: {{ (int)$landingPage->countdown_hours }},
            minutes: 45,
            seconds: 30,

            init() {
                // Countdown interval
                setInterval(() => {
                    if (this.seconds > 0) {
                        this.seconds--;
                    } else {
                        this.seconds = 59;
                        if (this.minutes > 0) {
                            this.minutes--;
                        } else {
                            this.minutes = 59;
                            if (this.hours > 0) {
                                this.hours--;
                            } else {
                                this.hours = 12; // Reset cycle
                            }
                        }
                    }
                }, 1000);
            },

            selectPackage(id, price, title, thumb) {
                this.selectedPackageId = id;
                this.selectedPackagePrice = price;
                this.selectedPackageTitle = title;
                this.selectedPackageThumb = thumb;

                // Smooth scroll to order form on selection
                const el = document.getElementById('orderFormSection');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            },

            get currentDeliveryCharge() {
                if (this.freeShipping) return 0;
                return this.deliveryCharges[this.deliveryZone] || 60;
            },

            get totalPayable() {
                return (this.selectedPackagePrice * this.quantity) + this.currentDeliveryCharge;
            },

            submitOrder(e) {
                this.isSubmitting = true;
                if (typeof window.trackInitiateCheckout === 'function') {
                    window.trackInitiateCheckout([{
                        id: this.selectedPackageId,
                        name: this.selectedPackageTitle,
                        price: this.selectedPackagePrice,
                        quantity: this.quantity
                    }], this.totalPayable);
                }
            }
        };
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        @php
            $firstPkg = $landingPage->landingPageProducts->first();
        @endphp
        @if($firstPkg)
        if (typeof window.trackViewContent === 'function') {
            window.trackViewContent({
                id: '{{ $firstPkg->product_id }}',
                name: @json($firstPkg->package_title ?: $firstPkg->product?->name),
                price: {{ (float) $firstPkg->effective_price }},
                category: @json($firstPkg->product?->category?->name ?? 'Landing Page'),
                quantity: 1
            });
        }
        @endif
    });
    </script>
</body>
</html>

