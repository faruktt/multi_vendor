<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitle = trim($__env->yieldContent('title', $appSettings['seo_title'] ?? $branch->system_name ?? $branch->name));
        $pageDescription = trim($__env->yieldContent('og_description', $appSettings['seo_description'] ?? $appSettings['tagline'] ?? ''));
        $pageImage = $appSettings['og_image'] ?? $appSettings['logo'] ?? null;
    @endphp
    <title>{{ $pageTitle }}</title>
    @if(!empty($appSettings['favicon']))
    <link rel="icon" href="{{ $appSettings['favicon'] }}">
    @endif
    @if($pageDescription)
    <meta name="description" content="{{ $pageDescription }}">
    @endif
    <meta property="og:title" content="{{ $pageTitle }}">
    @if($pageDescription)
    <meta property="og:description" content="{{ $pageDescription }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($pageImage)
    <meta property="og:image" content="{{ $pageImage }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['SolaimanLipi', 'sans-serif'],
                    },
                    colors: {
                        brand: { DEFAULT: '#2E7D34', dark: '#1F5A24', light: '#E8F3E6' },
                        deal:  { DEFAULT: '#E07C1A', dark: '#A85A0E', soft: '#FCEEDC' },
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    <style>
        body { font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
        [x-cloak] { display: none !important; }
        .scroll-smooth::-webkit-scrollbar { display: none; }
        .scroll-smooth { scrollbar-width: none; -ms-overflow-style: none; }
    </style>
</head>
<body class="h-full bg-[#F2F4EF] text-[#1A1E19] overflow-x-hidden" x-data="{ cartOpen: false, categoryDrawer: false, compareOpen: false, quickViewOpen: false, wishlistOpen: false, compareCount: 0, wishlistCount: 0 }" x-init="$nextTick(() => { window.shopSyncCompareBadges && window.shopSyncCompareBadges(); window.shopSyncWishlistBadges && window.shopSyncWishlistBadges(); })">

<div class="sticky top-0 z-40">

<!-- ══ Header row 1 ══ -->
<div class="w-full bg-brand">
    <div class="max-w-[1440px] mx-auto px-3 sm:px-6 py-2.5 sm:py-3">
        {{-- ── Mobile Header View (Logo + Login in Row 1, Search + Icons in Row 2) ── --}}
        <div class="flex md:hidden flex-col gap-2">
            {{-- Mobile Row 1: Logo & Login --}}
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('root') }}" class="flex items-center flex-shrink-0">
                    @if(!empty($appSettings['logo']))
                    <img src="{{ $appSettings['logo'] }}"
                         alt="{{ $branch->system_name ?? $branch->name }}" class="h-9 sm:h-10 max-h-11 w-auto max-w-[180px] sm:max-w-[220px] object-contain">
                    @elseif($branch->logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('uploads')->url($branch->logo) }}"
                         alt="{{ $branch->system_name ?? $branch->name }}" class="h-9 sm:h-10 max-h-11 w-auto max-w-[180px] sm:max-w-[220px] object-contain">
                    @else
                    <strong class="text-white text-base sm:text-xl font-extrabold tracking-tight">{{ $branch->system_name ?? $branch->name }}</strong>
                    @endif
                </a>

                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Reseller Panel shortcut if already logged in as reseller --}}
                    @if(auth('reseller')->check())
                        <a href="{{ route('reseller.dashboard') }}"
                           class="inline-flex items-center gap-1 text-white text-xs font-bold bg-white/10 hover:bg-white/20 px-2.5 py-1.5 rounded-full transition-colors border border-white/15"
                           title="Reseller Dashboard">
                            <i class="fas fa-user-tag text-amber-300 text-xs"></i>
                            <span>Reseller</span>
                        </a>
                    @endif

                    {{-- Customer Auth / Account Dropdown if logged in as customer --}}
                    @if(auth('customer')->check())
                        @php $currentCust = auth('customer')->user(); @endphp
                        <div class="relative" x-data="{ mobUserMenu: false }" @click.outside="mobUserMenu = false">
                            <button type="button" @click="mobUserMenu = !mobUserMenu"
                                    class="flex items-center gap-1.5 text-white text-xs font-bold bg-white/10 hover:bg-white/20 {{ $currentCust->avatar_url ? 'p-0.5 pr-2' : 'px-2.5 py-1.5' }} rounded-full transition-all border border-white/15 shadow-sm"
                                    title="{{ $currentCust->name }}">
                                @if($currentCust->avatar_url)
                                    <img src="{{ $currentCust->avatar_url }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover ring-2 ring-white/60 shadow-sm flex-shrink-0">
                                @else
                                    <i class="fas fa-user-circle text-sm text-white"></i>
                                    <span class="max-w-[70px] truncate">{{ $currentCust->name }}</span>
                                @endif
                                <i class="fas fa-chevron-down text-[9px] opacity-70 transition-transform duration-200" :class="{ 'rotate-180': mobUserMenu }"></i>
                            </button>
                            <div x-show="mobUserMenu" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-xl shadow-black/10 border border-gray-100/90 p-1.5 z-50 ring-1 ring-black/5 text-gray-800 text-xs font-semibold">
                                <div class="p-2.5 border-b border-gray-100 bg-gray-50/70 rounded-xl mb-1 flex items-center gap-2.5">
                                    @if($currentCust->avatar_url)
                                        <img src="{{ $currentCust->avatar_url }}" alt="{{ $currentCust->name }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-brand/20 flex-shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold text-sm flex-shrink-0">
                                            {{ strtoupper(substr($currentCust->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-gray-900 truncate leading-tight">{{ $currentCust->name }}</div>
                                        <div class="text-[10px] text-gray-400 truncate mt-0.5">{{ $currentCust->phone ?? $currentCust->email }}</div>
                                    </div>
                                </div>
                                <a href="{{ route('shop.customer.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-700 transition-colors">
                                    <i class="fas fa-th-large text-brand w-4"></i> Dashboard
                                </a>
                                <a href="{{ route('shop.customer.orders') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-700 transition-colors">
                                    <i class="fas fa-box text-brand w-4"></i> My Orders
                                </a>
                                <a href="{{ route('shop.customer.profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-700 transition-colors">
                                    <i class="fas fa-id-card text-brand w-4"></i> Profile
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('shop.customer.logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 font-bold text-left transition-colors">
                                        <i class="fas fa-sign-out-alt w-4"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Unified Login Dropdown --}}
                    <div class="relative" x-data="{ mobLoginOpen: false }" @click.outside="mobLoginOpen = false">
                        <button type="button" @click="mobLoginOpen = !mobLoginOpen"
                                class="inline-flex items-center gap-1.5 text-white text-xs font-bold bg-white/10 hover:bg-white/20 active:bg-white/25 px-3 py-1.5 rounded-full transition-all border border-white/20 shadow-sm"
                                title="Login Portals">
                            <i class="fas fa-sign-in-alt text-amber-300 text-xs"></i>
                            <span>Login</span>
                            <i class="fas fa-chevron-down text-[9px] opacity-80 transition-transform duration-200" :class="{ 'rotate-180': mobLoginOpen }"></i>
                        </button>

                        <div x-show="mobLoginOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl shadow-black/10 border border-gray-100/80 p-1.5 z-50 ring-1 ring-black/5 space-y-1">

                            <a href="{{ route('shop.customer.login') }}"
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-emerald-50/80 text-gray-700 hover:text-emerald-700 transition-all group">
                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                    <i class="fas fa-user text-[11px]"></i>
                                </span>
                                <span class="text-xs font-semibold tracking-tight flex-1">Customer Login</span>
                                <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-emerald-600 transition-all duration-150"></i>
                            </a>

                            <a href="{{ route('reseller.login') }}"
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-indigo-50/80 text-gray-700 hover:text-indigo-700 transition-all group">
                                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                    <i class="fas fa-user-tag text-[11px]"></i>
                                </span>
                                <span class="text-xs font-semibold tracking-tight flex-1">Reseller Login</span>
                                <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-indigo-600 transition-all duration-150"></i>
                            </a>

                            <a href="{{ route('supplier.login') }}"
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-amber-50/80 text-gray-700 hover:text-amber-700 transition-all group">
                                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                    <i class="fas fa-store text-[11px]"></i>
                                </span>
                                <span class="text-xs font-semibold tracking-tight flex-1">Supplier Login</span>
                                <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-amber-600 transition-all duration-150"></i>
                            </a>

                            <a href="{{ route('moderator.login') }}"
                               class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-rose-50/80 text-gray-700 hover:text-rose-700 transition-all group">
                                <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                    <i class="fas fa-user-shield text-[11px]"></i>
                                </span>
                                <span class="text-xs font-semibold tracking-tight flex-1">Moderator Login</span>
                                <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-rose-600 transition-all duration-150"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Row 2: Search Bar & Icons (Wishlist, Compare, Cart) --}}
            <div class="flex items-center gap-2 pt-0.5">
                <form method="GET" action="{{ route('shop.products.index') }}" class="flex-1 min-w-0">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..."
                               class="w-full h-9 rounded-lg border-0 bg-white pl-9 pr-3 text-xs focus:outline-none focus:ring-2 focus:ring-white/60 shadow-inner" />
                    </div>
                </form>

                <div class="flex items-center gap-0.5 flex-shrink-0">
                    {{-- Wishlist Button --}}
                    <button type="button" @click="wishlistOpen = true; window.shopRenderWishlistDrawer()"
                            class="relative text-white hover:text-amber-300 p-2 transition-colors"
                            title="Wishlist">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                        </svg>
                        <span class="wishlist-header-badge absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold min-w-[16px] h-[16px] rounded-full flex items-center justify-center px-0.5 border-2 border-brand"
                              x-show="wishlistCount > 0"
                              x-text="wishlistCount"
                              style="display:none">0</span>
                    </button>

                    {{-- Compare Button --}}
                    <button type="button" @click="compareOpen = true; window.shopRenderCompareDrawer()"
                            class="relative text-white hover:text-amber-300 p-2 transition-colors"
                            title="Compare Products">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                        </svg>
                        <span class="compare-header-badge absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold min-w-[16px] h-[16px] rounded-full flex items-center justify-center px-0.5 border-2 border-brand"
                              x-show="compareCount > 0"
                              x-text="compareCount"
                              style="display:none">0</span>
                    </button>

                    {{-- Cart Button --}}
                    <button type="button" @click="cartOpen = true" class="relative text-white hover:text-amber-300 p-2 transition-colors" title="Shopping Cart">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        <span class="cart-header-badge absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold min-w-[16px] h-[16px] rounded-full flex items-center justify-center px-0.5 border-2 border-brand"
                              style="{{ ($cartCount ?? 0) > 0 ? '' : 'display:none' }}">{{ $cartCount ?? 0 }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Desktop Header View (Single Row: Logo + Search + Actions) ── --}}
        <div class="hidden md:flex items-center gap-4 lg:gap-6">
            <a href="{{ route('root') }}" class="flex items-center flex-shrink-0">
                @if(!empty($appSettings['logo']))
                <img src="{{ $appSettings['logo'] }}"
                     alt="{{ $branch->system_name ?? $branch->name }}" class="h-11 md:h-12 lg:h-14 max-h-14 w-auto max-w-[230px] lg:max-w-[270px] object-contain transition-transform hover:scale-[1.02]">
                @elseif($branch->logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('uploads')->url($branch->logo) }}"
                     alt="{{ $branch->system_name ?? $branch->name }}" class="h-11 md:h-12 lg:h-14 max-h-14 w-auto max-w-[230px] lg:max-w-[270px] object-contain transition-transform hover:scale-[1.02]">
                @else
                <strong class="text-white text-xl lg:text-2xl font-extrabold tracking-tight">{{ $branch->system_name ?? $branch->name }}</strong>
                @endif
            </a>

            <form method="GET" action="{{ route('shop.products.index') }}" class="flex-1 max-w-[560px] mx-2 lg:mx-4 min-w-0">
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search for products.."
                           class="w-full h-11 rounded-lg border-0 bg-white pl-11 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-white/60 shadow-inner" />
                </div>
            </form>

            <div class="flex items-center gap-2 lg:gap-3 flex-shrink-0">
                {{-- Reseller Panel shortcut if already logged in as reseller --}}
                @if(auth('reseller')->check())
                    <a href="{{ route('reseller.dashboard') }}"
                       class="inline-flex items-center gap-1.5 text-white text-xs lg:text-sm font-bold bg-white/10 hover:bg-white/20 px-3 py-2 rounded-full transition-colors border border-white/15"
                       title="Reseller Dashboard">
                        <i class="fas fa-user-tag text-amber-300 text-xs"></i>
                        <span>Reseller Panel</span>
                    </a>
                @endif

                {{-- Customer Auth / Account Dropdown if logged in as customer --}}
                @if(auth('customer')->check())
                    @php $currentCust = auth('customer')->user(); @endphp
                    <div class="relative" x-data="{ userMenu: false }" @click.outside="userMenu = false">
                        <button type="button" @click="userMenu = !userMenu"
                                class="flex items-center gap-2 text-white text-xs lg:text-sm font-bold bg-white/10 hover:bg-white/20 {{ $currentCust->avatar_url ? 'p-1 pr-2.5' : 'px-3.5 py-2' }} rounded-full transition-all border border-white/15 shadow-sm"
                                title="{{ $currentCust->name }}">
                            @if($currentCust->avatar_url)
                                <img src="{{ $currentCust->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover ring-2 ring-white/60 shadow-sm flex-shrink-0">
                            @else
                                <i class="fas fa-user-circle text-base text-white"></i>
                                <span class="max-w-[120px] truncate">{{ $currentCust->name }}</span>
                            @endif
                            <i class="fas fa-chevron-down text-[10px] opacity-70 transition-transform duration-200" :class="{ 'rotate-180': userMenu }"></i>
                        </button>
                        <div x-show="userMenu" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-xl shadow-black/10 border border-gray-100/90 p-1.5 z-50 ring-1 ring-black/5 text-gray-800 text-xs font-semibold">
                            <div class="p-3 border-b border-gray-100 bg-gray-50/70 rounded-xl mb-1 flex items-center gap-3">
                                @if($currentCust->avatar_url)
                                    <img src="{{ $currentCust->avatar_url }}" alt="{{ $currentCust->name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-brand/20 flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($currentCust->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-gray-900 truncate text-sm leading-tight">{{ $currentCust->name }}</div>
                                    <div class="text-[10px] text-gray-400 truncate mt-0.5">{{ $currentCust->phone ?? $currentCust->email }}</div>
                                </div>
                            </div>
                            <a href="{{ route('shop.customer.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-700 transition-colors">
                                <i class="fas fa-th-large text-brand w-4"></i> Dashboard
                            </a>
                            <a href="{{ route('shop.customer.orders') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-700 transition-colors">
                                <i class="fas fa-box text-brand w-4"></i> My Orders
                            </a>
                            <a href="{{ route('shop.customer.profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 text-gray-700 transition-colors">
                                <i class="fas fa-id-card text-brand w-4"></i> Profile &amp; Security
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('shop.customer.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 font-bold text-left transition-colors">
                                    <i class="fas fa-sign-out-alt w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Unified Login Button with Dropdown --}}
                <div class="relative" x-data="{ loginOpen: false }" @click.outside="loginOpen = false">
                    <button type="button" @click="loginOpen = !loginOpen"
                            class="inline-flex items-center gap-2 text-white text-xs lg:text-sm font-bold bg-white/10 hover:bg-white/20 active:bg-white/25 px-4 py-2.5 rounded-full transition-all border border-white/20 shadow-sm"
                            title="Login Portals">
                        <i class="fas fa-sign-in-alt text-amber-300 text-xs lg:text-sm"></i>
                        <span>Login</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-80 transition-transform duration-200" :class="{ 'rotate-180': loginOpen }"></i>
                    </button>

                    <div x-show="loginOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-xl shadow-black/10 border border-gray-100/80 p-1.5 z-50 ring-1 ring-black/5 space-y-1">

                        <a href="{{ route('shop.customer.login') }}"
                           class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-emerald-50/80 text-gray-700 hover:text-emerald-700 transition-all group">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                <i class="fas fa-user text-[11px]"></i>
                            </span>
                            <span class="text-xs font-semibold tracking-tight flex-1">Customer Login</span>
                            <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-emerald-600 transition-all duration-150"></i>
                        </a>

                        <a href="{{ route('reseller.login') }}"
                           class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-indigo-50/80 text-gray-700 hover:text-indigo-700 transition-all group">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                <i class="fas fa-user-tag text-[11px]"></i>
                            </span>
                            <span class="text-xs font-semibold tracking-tight flex-1">Reseller Login</span>
                            <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-indigo-600 transition-all duration-150"></i>
                        </a>

                        <a href="{{ route('supplier.login') }}"
                           class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-amber-50/80 text-gray-700 hover:text-amber-700 transition-all group">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                <i class="fas fa-store text-[11px]"></i>
                            </span>
                            <span class="text-xs font-semibold tracking-tight flex-1">Supplier Login</span>
                            <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-amber-600 transition-all duration-150"></i>
                        </a>

                        <a href="{{ route('moderator.login') }}"
                           class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl hover:bg-rose-50/80 text-gray-700 hover:text-rose-700 transition-all group">
                            <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-600 group-hover:text-white group-hover:shadow-sm transition-all duration-200">
                                <i class="fas fa-user-shield text-[11px]"></i>
                            </span>
                            <span class="text-xs font-semibold tracking-tight flex-1">Moderator Login</span>
                            <i class="fas fa-chevron-right text-[9px] text-gray-300 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-rose-600 transition-all duration-150"></i>
                        </a>
                    </div>
                </div>

                {{-- Header Wishlist Button --}}
                <button type="button" @click="wishlistOpen = true; window.shopRenderWishlistDrawer()"
                        class="relative text-white hover:text-amber-300 flex-shrink-0 ml-1 lg:ml-2 p-2 transition-colors"
                        title="Wishlist">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    </svg>
                    <span class="wishlist-header-badge absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold min-w-[17px] h-[17px] rounded-full flex items-center justify-center px-1 border-2 border-brand"
                          x-show="wishlistCount > 0"
                          x-text="wishlistCount"
                          style="display:none">0</span>
                </button>

                {{-- Header Compare Button --}}
                <button type="button" @click="compareOpen = true; window.shopRenderCompareDrawer()"
                        class="relative text-white hover:text-amber-300 flex-shrink-0 ml-1 lg:ml-2 p-2 transition-colors"
                        title="Compare Products">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                    </svg>
                    <span class="compare-header-badge absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold min-w-[17px] h-[17px] rounded-full flex items-center justify-center px-1 border-2 border-brand"
                          x-show="compareCount > 0"
                          x-text="compareCount"
                          style="display:none">0</span>
                </button>

                {{-- Header Cart Button --}}
                <button type="button" @click="cartOpen = true" class="relative text-white hover:text-amber-300 flex-shrink-0 ml-1 p-2 transition-colors" title="Shopping Cart">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span class="cart-header-badge absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold min-w-[17px] h-[17px] rounded-full flex items-center justify-center px-1 border-2 border-brand"
                          style="{{ ($cartCount ?? 0) > 0 ? '' : 'display:none' }}">{{ $cartCount ?? 0 }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══ Header row 2 ══ -->
<div class="w-full bg-white border-b border-gray-200">
    <div class="max-w-[1440px] mx-auto flex items-center flex-wrap gap-x-4 sm:gap-x-6 gap-y-1.5 px-4 sm:px-6 py-2.5">
        <div class="relative flex-shrink-0" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
            <button type="button" @click="categoryDrawer = true" class="flex items-center gap-2 text-[13px] sm:text-sm font-extrabold uppercase tracking-wide text-gray-900">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                Shop by Category
            </button>
            <div x-show="open" x-cloak
                 class="hidden lg:block absolute left-0 top-full w-64 max-w-[calc(100vw-2rem)] bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-30">
                @forelse(($categories ?? collect()) as $cat)
                <div class="relative group/cat">
                    <a href="{{ route('shop.products.category', $cat->slug) }}"
                       class="flex items-center justify-between gap-2 px-4 py-2 text-[13px] font-semibold text-gray-700 hover:bg-gray-50 hover:text-brand-dark">
                        <span>{{ $cat->name }}</span>
                        @if($cat->children->isNotEmpty())
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 flex-shrink-0"><path d="M9 18l6-6-6-6"/></svg>
                        @endif
                    </a>
                    @if($cat->children->isNotEmpty())
                    <div class="hidden group-hover/cat:block absolute left-full top-0 ml-1 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-2">
                        @foreach($cat->children as $child)
                        <a href="{{ route('shop.products.category', $child->slug) }}"
                           class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-brand-dark">{{ $child->name }}</a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <p class="px-4 py-2 text-[13px] text-gray-400">No categories yet.</p>
                @endforelse
            </div>
        </div>
        <nav class="flex items-center gap-6 text-sm font-semibold text-gray-700 whitespace-nowrap">
            <a href="{{ route('shop.products.index') }}" class="hover:text-brand-dark {{ request()->routeIs('shop.products.*') ? 'text-brand-dark' : '' }}">All Products</a>
            <a href="{{ route('shop.track.index') }}" class="hover:text-brand-dark {{ request()->routeIs('shop.track.*') ? 'text-brand-dark' : '' }}">Track Order</a>
        </nav>

    </div>
</div>

</div>

@hasSection('breadcrumb')
<div class="max-w-[1440px] mx-auto px-4 pt-4">
    <div class="bg-white rounded-2xl shadow-sm px-5 py-3 flex items-center gap-2 text-[13px] text-gray-500">
        @yield('breadcrumb')
    </div>
</div>
@endif

<main class="@yield('main-class', 'max-w-6xl mx-auto px-4 py-6')">
    @yield('content')
</main>

@php
    $rawPhone = preg_replace('/\D/', '', $appSettings['phone'] ?? '');
    $whatsappNumber = $rawPhone ? '880' . ltrim($rawPhone, '0') : null;
@endphp

<!-- ══ Footer ══ -->
<footer class="w-full bg-[#12261A] text-white mt-8">

    <div class="bg-gradient-to-r from-brand-dark to-brand">
        <div class="max-w-[1440px] mx-auto px-6 py-6 flex items-center justify-between gap-6 flex-wrap">
            <div class="flex items-center gap-3.5">
                <span class="w-11 h-11 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-headset text-lg"></i>
                </span>
                <div>
                    <p class="text-lg font-extrabold">Need help with an order?</p>
                    <p class="text-sm text-white/80 mt-0.5">Our team replies fast on WhatsApp or by phone.</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 h-11 px-5 rounded-lg bg-[#25D366] hover:bg-[#1fb958] text-white text-sm font-bold transition-colors">
                    <i class="fab fa-whatsapp"></i> WhatsApp Us
                </a>
                @endif
                @if(!empty($appSettings['phone']))
                <a href="tel:+{{ $whatsappNumber }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 h-11 px-5 rounded-lg bg-gray-900 hover:bg-black text-white text-sm font-bold transition-colors">
                    <i class="fas fa-phone"></i> {{ $appSettings['phone'] }}
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-[1440px] mx-auto px-6 py-10 grid grid-cols-2 md:grid-cols-4 gap-8">

        <div class="col-span-2 md:col-span-1">
            @if(!empty($appSettings['logo']))
            <img src="{{ $appSettings['logo'] }}" alt="{{ $branch->system_name ?? $branch->name }}" class="h-10 sm:h-12 max-h-14 w-auto max-w-[230px] object-contain mb-3">
            @elseif($branch->logo)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('uploads')->url($branch->logo) }}"
                 alt="{{ $branch->system_name ?? $branch->name }}" class="h-10 sm:h-12 max-h-14 w-auto max-w-[230px] object-contain mb-3">
            @else
            <strong class="text-xl font-extrabold block mb-3">{{ $branch->system_name ?? $branch->name }}</strong>
            @endif

            @if(!empty($appSettings['address']))
            <p class="flex items-start gap-2.5 text-[13px] text-white/70 mt-3 max-w-[260px] leading-relaxed">
                <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-location-dot text-[10px]"></i>
                </span>
                <span>{{ $appSettings['address'] }}</span>
            </p>
            @endif
        </div>

        <div>
            <h4 class="text-[11px] font-extrabold uppercase tracking-widest text-white/45 mb-4">Shop</h4>
            <a href="{{ route('shop.products.index') }}" class="block text-[13px] text-white/70 hover:text-white hover:translate-x-0.5 transition-all mb-2.5">All Products</a>
            @foreach(($categories ?? collect())->take(4) as $cat)
            <a href="{{ route('shop.products.category', $cat->slug) }}" class="block text-[13px] text-white/70 hover:text-white hover:translate-x-0.5 transition-all mb-2.5">{{ $cat->name }}</a>
            @endforeach
        </div>

        <div>
            <h4 class="text-[11px] font-extrabold uppercase tracking-widest text-white/45 mb-4">Customer Service</h4>
            <a href="{{ route('shop.track.index') }}" class="block text-[13px] text-white/70 hover:text-white hover:translate-x-0.5 transition-all mb-2.5">Track Order</a>
            @if(isset($footerPages) && $footerPages->isNotEmpty())
                @foreach($footerPages as $footerPage)
                <a href="{{ route('shop.pages.show', $footerPage->slug) }}" class="block text-[13px] text-white/70 hover:text-white hover:translate-x-0.5 transition-all mb-2.5 truncate" title="{{ $footerPage->title }}">{{ $footerPage->title }}</a>
                @endforeach
            @else
                <a href="#" class="block text-[13px] text-white/70 hover:text-white hover:translate-x-0.5 transition-all mb-2.5">Returns &amp; Refunds</a>
                <a href="#" class="block text-[13px] text-white/70 hover:text-white hover:translate-x-0.5 transition-all mb-2.5">FAQ</a>
            @endif
        </div>

        <div>
            <h4 class="text-[11px] font-extrabold uppercase tracking-widest text-white/45 mb-4">Get in Touch</h4>
            @if(!empty($appSettings['phone']))
            <a href="tel:+{{ $whatsappNumber }}" class="flex items-center gap-2.5 text-[13px] text-white/70 hover:text-white mb-3">
                <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-phone text-[10px]"></i>
                </span>
                {{ $appSettings['phone'] }}
            </a>
            @endif
            @if(!empty($appSettings['email']))
            <a href="mailto:{{ $appSettings['email'] }}" class="flex items-center gap-2.5 text-[13px] text-white/70 hover:text-white mb-3">
                <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-envelope text-[10px]"></i>
                </span>
                {{ $appSettings['email'] }}
            </a>
            @endif

            {{-- Social Icons in Flex Row --}}
            @php
                $socialLinks = [
                    'facebook'  => ['url' => $appSettings['facebook'] ?? null,  'icon' => 'fa-brands fa-facebook-f', 'title' => 'Facebook',  'hover' => 'hover:bg-[#1877F2] hover:border-[#1877F2]'],
                    'youtube'   => ['url' => $appSettings['youtube'] ?? null,   'icon' => 'fa-brands fa-youtube',    'title' => 'YouTube',   'hover' => 'hover:bg-[#FF0000] hover:border-[#FF0000]'],
                    'twitter'   => ['url' => $appSettings['twitter'] ?? null,   'icon' => 'fa-brands fa-x-twitter',  'title' => 'X (Twitter)', 'hover' => 'hover:bg-white hover:text-black hover:border-white'],
                    'instagram' => ['url' => $appSettings['instagram'] ?? null, 'icon' => 'fa-brands fa-instagram',  'title' => 'Instagram', 'hover' => 'hover:bg-[#E4405F] hover:border-[#E4405F]'],
                    'tiktok'    => ['url' => $appSettings['tiktok'] ?? null,    'icon' => 'fa-brands fa-tiktok',     'title' => 'TikTok',    'hover' => 'hover:bg-black hover:border-black hover:text-white'],
                ];
                $activeSocialLinks = collect($socialLinks)->filter(fn($item) => !empty($item['url']));
            @endphp

            @if($activeSocialLinks->isNotEmpty())
            <div class="mt-4 pt-3 border-t border-white/10">
                <p class="text-[10.5px] font-bold text-white/40 uppercase tracking-wider mb-2.5">Follow Us</p>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($activeSocialLinks as $key => $soc)
                    <a href="{{ $soc['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $soc['title'] }}"
                       class="w-8 h-8 rounded-full bg-white/10 border border-white/15 flex items-center justify-center text-white/80 hover:text-white transition-all duration-200 hover:scale-110 {{ $soc['hover'] }}">
                        <i class="{{ $soc['icon'] }} text-[12px]"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-[1440px] mx-auto px-6 py-5 flex items-center justify-between gap-4 flex-wrap text-[11.5px] text-white/50">
            <div>
                &copy; {{ date('Y') }} <span class="text-white/85 font-semibold">{{ $appSettings['name'] ?? $branch->system_name ?? $branch->name }}</span>. All rights reserved.
            </div>
            <div class="text-white/60">
                Developed by <a href="https://sarkarit.com/" target="_blank" rel="noopener noreferrer" class="text-white font-bold hover:underline transition-colors">SARKAR IT</a>
            </div>
        </div>
    </div>
</footer>

<div x-data
     class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)] pointer-events-none">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl shadow-xl border text-sm font-medium"
             :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
                'bg-red-50     border-red-200     text-red-800':     toast.type === 'error',
             }"
             x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-10 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-10 scale-95">
            <i class="mt-0.5 flex-shrink-0 text-sm"
               :class="toast.type === 'success' ? 'fas fa-circle-check text-emerald-500' : 'fas fa-circle-xmark text-red-500'"></i>
            <span class="flex-1 leading-snug" x-text="toast.message"></span>
            <button @click="$store.toast.dismiss(toast.id)" class="flex-shrink-0 opacity-50 hover:opacity-100 transition-opacity ml-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    </template>
</div>

{{-- ══ Mobile category drawer ══ --}}
<div x-show="categoryDrawer" x-cloak
     class="fixed inset-0 z-50 bg-black/40 lg:hidden"
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="categoryDrawer = false">
    <div @click.stop
         class="absolute left-0 top-0 h-full w-full max-w-xs bg-white shadow-2xl flex flex-col transition-transform duration-200 ease-in-out"
         :class="categoryDrawer ? 'translate-x-0' : '-translate-x-full'">

        <div class="flex items-center justify-between px-5 py-4 bg-brand flex-shrink-0">
            <h2 class="font-extrabold text-white text-sm uppercase tracking-wide">Shop by Category</h2>
            <button type="button" @click="categoryDrawer = false" class="w-8 h-8 rounded-lg text-white/70 hover:text-white hover:bg-white/10 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-2">
            <a href="{{ route('shop.products.index') }}" @click="categoryDrawer = false"
               class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 font-bold text-[13.5px] text-gray-900 hover:bg-gray-50">
                All Products
            </a>
            @forelse(($categories ?? collect()) as $cat)
            <div x-data="{ expanded: false }" class="border-b border-gray-100">
                <div class="flex items-center">
                    <a href="{{ route('shop.products.category', $cat->slug) }}" @click="categoryDrawer = false"
                       class="flex-1 px-5 py-3 text-[13.5px] font-semibold text-gray-700 hover:bg-gray-50 hover:text-brand-dark">
                        {{ $cat->name }}
                    </a>
                    @if($cat->children->isNotEmpty())
                    <button type="button" @click="expanded = !expanded" class="px-4 py-3 text-gray-400 hover:text-brand-dark">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             class="transition-transform" :class="expanded ? 'rotate-180' : ''"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    @endif
                </div>
                @if($cat->children->isNotEmpty())
                <div x-show="expanded" x-cloak class="bg-gray-50">
                    @foreach($cat->children as $child)
                    <a href="{{ route('shop.products.category', $child->slug) }}" @click="categoryDrawer = false"
                       class="block pl-9 pr-5 py-2.5 text-[13px] text-gray-600 hover:text-brand-dark">
                        {{ $child->name }}
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <p class="px-5 py-3 text-[13px] text-gray-400">No categories yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ Cart drawer ══ --}}
@php $currency = $appSettings['currency'] ?? '৳'; @endphp
<div x-show="cartOpen" x-cloak
     class="fixed inset-0 z-50 bg-black/40"
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="cartOpen = false">
    <div @click.stop
         class="absolute right-0 top-0 h-full w-full max-w-sm bg-white shadow-2xl flex flex-col transition-transform duration-200 ease-in-out"
         :class="cartOpen ? 'translate-x-0' : 'translate-x-full'">

        <div class="flex items-center justify-between px-5 py-4 bg-brand flex-shrink-0">
            <h2 class="font-extrabold text-white text-sm uppercase tracking-wide">Your Cart <span id="cart-drawer-count">@if(($cartCount ?? 0) > 0)({{ $cartCount }})@endif</span></h2>
            <button type="button" @click="cartOpen = false" class="w-8 h-8 rounded-lg text-white/70 hover:text-white hover:bg-white/10 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="cart-drawer-body" class="flex-1 min-h-0 flex flex-col">
            @include('shop.partials.cart-drawer-body', ['cartLines' => $cartLines ?? collect(), 'cartSubtotal' => $cartSubtotal ?? 0])
        </div>
    </div>
</div>

{{-- ══ Compare drawer (Slides in from right) ══ --}}
<div x-show="compareOpen" x-cloak
     class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs"
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="compareOpen = false">
    <div @click.stop
         class="absolute right-0 top-0 h-full w-full max-w-sm sm:max-w-md bg-white shadow-2xl flex flex-col transition-transform duration-200 ease-in-out"
         :class="compareOpen ? 'translate-x-0' : 'translate-x-full'">

        <div class="flex items-center justify-between px-5 py-4 bg-brand flex-shrink-0 text-white">
            <div class="flex items-center gap-2">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>
                </svg>
                <h2 class="font-extrabold text-sm uppercase tracking-wide">Compare Products (<span id="compare-drawer-count" x-text="compareCount">0</span>)</h2>
            </div>
            <button type="button" @click="compareOpen = false" class="w-8 h-8 rounded-lg text-white/80 hover:text-white hover:bg-white/10 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="compare-drawer-body" class="flex-1 min-h-0 overflow-y-auto p-4 space-y-3">
            <!-- Rendered dynamically by window.shopRenderCompareDrawer() -->
        </div>

        <div id="compare-drawer-footer" class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
            <button type="button" onclick="window.shopClearCompare()" class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline flex items-center gap-1">
                <i class="fas fa-trash text-[10px]"></i> Clear All
            </button>
            <button type="button" @click="compareOpen = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ══ Wishlist drawer (Slides in from right) ══ --}}
<div x-show="wishlistOpen" x-cloak
     class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs"
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="wishlistOpen = false">
    <div @click.stop
         class="absolute right-0 top-0 h-full w-full max-w-sm sm:max-w-md bg-white shadow-2xl flex flex-col transition-transform duration-200 ease-in-out"
         :class="wishlistOpen ? 'translate-x-0' : 'translate-x-full'">

        <div class="flex items-center justify-between px-5 py-4 bg-brand flex-shrink-0 text-white">
            <div class="flex items-center gap-2">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                </svg>
                <h2 class="font-extrabold text-sm uppercase tracking-wide">Wishlist (<span id="wishlist-drawer-count" x-text="wishlistCount">0</span>)</h2>
            </div>
            <button type="button" @click="wishlistOpen = false" class="w-8 h-8 rounded-lg text-white/80 hover:text-white hover:bg-white/10 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="wishlist-drawer-body" class="flex-1 min-h-0 overflow-y-auto p-4 space-y-3">
            <!-- Rendered dynamically by window.shopRenderWishlistDrawer() -->
        </div>

        <div id="wishlist-drawer-footer" class="p-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
            <button type="button" onclick="window.shopClearWishlist()" class="text-xs text-rose-600 hover:text-rose-800 font-bold hover:underline flex items-center gap-1">
                <i class="fas fa-trash text-[10px]"></i> Clear All
            </button>
            <button type="button" @click="wishlistOpen = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-xs font-bold transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ══ Quick View Drawer (Slides in from right - Matches Screenshot 2) ══ --}}
<div x-show="quickViewOpen" x-cloak
     class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex justify-end"
     x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="quickViewOpen = false">
    <div @click.stop
         class="relative w-full max-w-3xl lg:max-w-4xl bg-white shadow-2xl h-full flex flex-col overflow-y-auto transition-transform duration-300 ease-out"
         :class="quickViewOpen ? 'translate-x-0' : 'translate-x-full'">

        {{-- Close Button --}}
        <button type="button" @click="quickViewOpen = false"
                class="absolute top-4 right-4 z-30 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-gray-700 hover:text-black shadow-md flex items-center justify-center transition-all border border-gray-200">
            <i class="fas fa-times text-sm"></i>
        </button>

        <div id="quick-view-container" class="p-6 sm:p-8 flex-1">
            <!-- Loaded dynamically via AJAX -->
        </div>
    </div>
</div>

{{-- ══ Floating Live Chat & Scroll-to-Top ══ --}}
<div class="fixed right-4 sm:right-6 bottom-5 z-40 flex flex-col items-end gap-3"
     x-data="shopLiveChatWidget()"
     x-init="initWidget()">

    {{-- ══ Live Chat Popup Window (Messenger Style) ══ --}}
    <div x-show="chatOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="w-[calc(100vw-1.5rem)] sm:w-[420px] md:w-[460px] h-[560px] max-h-[85vh] bg-white rounded-3xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden mb-2">

        {{-- Widget Header --}}
        <div class="bg-brand px-4 py-3.5 flex items-center justify-between text-white flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center text-base font-bold border border-white/20 shadow-xs">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h3 class="font-black text-sm sm:text-base leading-tight">{{ $branch->system_name ?? $branch->name }} Support</h3>
                    <div class="flex items-center gap-1.5 text-[11px] text-white/80 font-medium mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Online • We reply instantly</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-1">
                @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                   class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-sm transition-colors"
                   title="Open WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                @endif
                <button type="button" @click="chatOpen = false"
                        class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-sm transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Widget Body --}}
        @if(auth('customer')->check())
            {{-- Authenticated Customer Chat Screen --}}
            <div class="flex-1 flex flex-col min-h-0 bg-[#F4F6F3]">

                {{-- Messages List --}}
                <div id="customer-chat-messages"
                     class="flex-1 overflow-y-auto p-4 space-y-3">

                    <div class="text-center my-1">
                        <span class="px-3 py-1 rounded-full bg-white/90 border border-gray-200/80 text-[11px] font-bold text-gray-500 shadow-xs inline-flex items-center gap-1.5">
                            <i class="fas fa-shield-alt text-brand"></i> Support chat for {{ auth('customer')->user()->name }}
                        </span>
                    </div>

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex flex-col" :class="msg.sender_type === 'customer' ? 'items-end' : 'items-start'">
                            <div class="max-w-[85%] rounded-2xl p-3 shadow-xs text-xs space-y-1.5"
                                 :class="msg.sender_type === 'customer' ? 'bg-brand text-white rounded-tr-xs' : 'bg-white text-gray-800 rounded-tl-xs border border-gray-200/80'">

                                {{-- Image preview if attached --}}
                                <template x-if="msg.image_url">
                                    <div class="rounded-xl overflow-hidden cursor-pointer my-1 border border-black/10 bg-black/5"
                                         @click="openLightbox(msg.image_url)">
                                        <img :src="msg.image_url" alt="Photo"
                                             loading="lazy"
                                             class="max-h-52 w-auto max-w-full object-cover rounded-xl hover:opacity-90 transition-opacity">
                                    </div>
                                </template>

                                {{-- Text message --}}
                                <template x-if="msg.message">
                                    <p class="whitespace-pre-wrap leading-relaxed text-[13px]"
                                       :class="msg.sender_type === 'customer' ? 'text-white' : 'text-gray-900'"
                                       x-text="msg.message"></p>
                                </template>

                                <div class="text-[10px] text-right flex items-center justify-end gap-1"
                                     :class="msg.sender_type === 'customer' ? 'text-white/70' : 'text-gray-400'">
                                    <span x-text="msg.formatted_time"></span>
                                    <template x-if="msg.sender_type === 'customer'">
                                        <i class="fas fa-check text-[9px]"></i>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="messages.length === 0 && !loadingMessages" class="p-8 text-center text-gray-400">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-2 text-lg">
                            <i class="fas fa-comments"></i>
                        </div>
                        <p class="text-xs font-semibold text-gray-500">No messages yet</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Send a message below to start chatting with support!</p>
                    </div>

                    <div x-show="loadingMessages" class="p-4 text-center text-gray-400">
                        <i class="fas fa-spinner fa-spin text-base text-brand"></i>
                    </div>
                </div>

                {{-- Image preview before sending --}}
                <template x-if="imagePreview">
                    <div class="p-2.5 bg-gray-100 border-t border-gray-200 flex items-center gap-3">
                        <div class="relative w-14 h-14 rounded-xl overflow-hidden border border-gray-300 shadow-xs bg-white flex-shrink-0">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                            <button type="button" @click="removeImage()"
                                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="text-xs">
                            <span class="font-bold text-gray-700 block">Photo attached</span>
                            <span class="text-[11px] text-gray-400">Ready to send with your message</span>
                        </div>
                    </div>
                </template>

                {{-- Input Bar (Spacious & Full Width) --}}
                <div class="p-3 bg-white border-t border-gray-200/90 shadow-xs">
                    <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                        {{-- Image upload trigger --}}
                        <label class="w-10 h-10 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center cursor-pointer transition-colors flex-shrink-0"
                               title="Send photo">
                            <i class="fas fa-camera text-sm"></i>
                            <input type="file" @change="onImageSelected($event)" accept="image/*" class="sr-only" x-ref="custFileInput">
                        </label>

                        <div class="flex-1 min-w-0">
                            <input type="text" x-model="newMessage"
                                   placeholder="Type a message..."
                                   :disabled="isSending"
                                   class="w-full h-10.5 p-2 rounded-2xl border border-gray-300 px-4 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/60 focus:bg-white transition-all">
                        </div>

                        <button type="submit"
                                :disabled="isSending || (!newMessage.trim() && !imageFile)"
                                class="h-10.5 px-4 bg-brand hover:bg-brand-dark text-white rounded-2xl text-xs font-bold transition-all shadow-md shadow-brand/20 flex items-center justify-center gap-1.5 disabled:opacity-40 flex-shrink-0">
                            <span x-show="!isSending"><i class="fas fa-paper-plane"></i></span>
                            <span x-show="isSending"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </form>
                </div>

            </div>
        @else
            {{-- Guest Login Required Screen --}}
            <div class="flex-1 p-6 flex flex-col items-center justify-center text-center bg-[#F8FAF6]">
                <div class="w-16 h-16 rounded-3xl bg-brand/10 text-brand flex items-center justify-center text-2xl mb-4 border border-brand/20 shadow-sm">
                    <i class="fas fa-comments"></i>
                </div>
                <h4 class="text-base font-black text-gray-900 mb-1.5">Sign In to Start Live Chat</h4>
                <p class="text-xs text-gray-500 leading-relaxed mb-6 max-w-xs">
                    Please log in with your customer account to message our support team directly and track your conversation history.
                </p>

                <div class="w-full space-y-2.5">
                    <a href="{{ route('shop.customer.login') }}"
                       class="w-full h-11 bg-brand hover:bg-brand-dark text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-brand/20 flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt text-xs"></i> Sign In to Account
                    </a>

                    <a href="{{ route('shop.customer.register') }}"
                       class="w-full h-11 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-user-plus text-xs"></i> Create Free Account
                    </a>
                </div>

                @if($whatsappNumber)
                    <div class="mt-6 pt-5 border-t border-gray-200/80 w-full text-center">
                        <span class="text-[11px] text-gray-400 block mb-2">Or reach us directly on:</span>
                        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#25D366] hover:underline">
                            <i class="fab fa-whatsapp text-sm"></i> WhatsApp Support &rarr;
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- ══ Floating Launcher Buttons ══ --}}
    <div class="flex items-center gap-2">
        {{-- WhatsApp Quick Launcher --}}
      

        {{-- Live Chat Button --}}
        <button type="button" @click="toggleChat()"
                class="h-13 px-4 sm:px-5 rounded-full bg-brand hover:bg-brand-dark text-white shadow-xl shadow-brand/30 flex items-center gap-2.5 font-black text-xs sm:text-sm hover:scale-105 transition-all relative">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping absolute top-2 left-2" x-show="!chatOpen"></span>
            <i class="fas" :class="chatOpen ? 'fa-times text-base' : 'fa-comment-dots text-base'"></i>
            <span x-text="chatOpen ? 'Close Chat' : 'Live Chat'"></span>

            <template x-if="unreadCount > 0">
                <span class="w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-black flex items-center justify-center border-2 border-white"
                      x-text="unreadCount"></span>
            </template>
        </button>

        {{-- Scroll to top --}}
        <button type="button" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                x-show="showTop" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="w-10 h-10 rounded-full bg-white border border-gray-200 shadow-md flex items-center justify-center text-gray-500 hover:text-brand-dark hover:border-brand transition-colors"
                aria-label="Scroll to top">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
        </button>
    </div>

    {{-- Customer Chat Lightbox Modal --}}
    <template x-teleport="body">
        <div x-show="lightboxUrl" x-cloak
             class="fixed inset-0 z-[999999] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
             @click="lightboxUrl = null">
            <button type="button" @click="lightboxUrl = null"
                    class="absolute top-5 right-5 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 text-white text-base flex items-center justify-center transition-colors">
                <i class="fas fa-times"></i>
            </button>
            <img :src="lightboxUrl" class="max-h-[90vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl" @click.stop>
        </div>
    </template>
</div>

@stack('scripts')
<script>
function shopLiveChatWidget() {
    return {
        chatOpen: false,
        showTop: false,
        messages: [],
        newMessage: '',
        imageFile: null,
        imagePreview: null,
        isSending: false,
        loadingMessages: false,
        unreadCount: 0,
        lightboxUrl: null,
        pollingTimer: null,
        initialized: false,
        isPolling: false,
        isAuth: {{ auth('customer')->check() ? 'true' : 'false' }},

        initWidget() {
            window.addEventListener('scroll', () => this.showTop = window.scrollY > 400);

            if (this.isAuth) {
                // Poll every 3s
                this.pollingTimer = setInterval(() => {
                    this.pollMessages();
                }, 3000);
            }
        },

        toggleChat() {
            this.chatOpen = !this.chatOpen;
            if (this.chatOpen && this.isAuth) {
                if (!this.initialized) {
                    this.initChat();
                } else {
                    this.$nextTick(() => this.scrollToBottom());
                }
            }
        },

        mergeMessages(newMsgs) {
            if (!newMsgs || !newMsgs.length) return;
            const map = new Map();
            this.messages.forEach(m => {
                if (m && m.id) map.set(m.id, m);
            });
            newMsgs.forEach(m => {
                if (m && m.id) map.set(m.id, m);
            });
            this.messages = Array.from(map.values()).sort((a, b) => a.id - b.id);
        },

        initChat() {
            this.loadingMessages = true;
            fetch('{{ route('shop.customer.chat.init') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                this.loadingMessages = false;
                if (data.authenticated) {
                    this.initialized = true;
                    this.mergeMessages(data.messages || []);
                    this.unreadCount = 0;
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(() => { this.loadingMessages = false; });
        },

        pollMessages() {
            if (this.isPolling) return;
            this.isPolling = true;

            const lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;
            const isOpen = this.chatOpen ? 1 : 0;

            fetch(`{{ route('shop.customer.chat.poll') }}?last_id=${lastId}&is_open=${isOpen}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                this.isPolling = false;
                if (data.authenticated) {
                    if (data.messages && data.messages.length > 0) {
                        const oldLen = this.messages.length;
                        this.mergeMessages(data.messages);
                        if (this.messages.length > oldLen && this.chatOpen) {
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    }
                    this.unreadCount = data.unread_count || 0;
                }
            })
            .catch(() => { this.isPolling = false; });
        },

        onImageSelected(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.imageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        removeImage() {
            this.imageFile = null;
            this.imagePreview = null;
            if (this.$refs.custFileInput) this.$refs.custFileInput.value = '';
        },

        sendMessage() {
            if (!this.newMessage.trim() && !this.imageFile) return;

            const textToSend = this.newMessage.trim();
            const fileToSend = this.imageFile;

            this.isSending = true;
            const formData = new FormData();
            if (textToSend) formData.append('message', textToSend);
            if (fileToSend) formData.append('image', fileToSend);

            // Optimistically clear the input bar
            this.newMessage = '';
            this.removeImage();

            fetch('{{ route('shop.customer.chat.send') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => r.json())
            .then(data => {
                this.isSending = false;
                if (data.success && data.message) {
                    this.mergeMessages([data.message]);
                    this.$nextTick(() => this.scrollToBottom());
                }
            })
            .catch(() => {
                this.isSending = false;
            });
        },

        scrollToBottom() {
            const container = document.getElementById('customer-chat-messages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        openLightbox(url) {
            this.lightboxUrl = url;
        }
    }
}
</script>
<script>
// Generic product-shelf carousel: pages by the track's own visible width
window.shelfScroll = function (trackId, dir) {
    const el = document.getElementById(trackId);
    if (el) el.scrollBy({ left: el.clientWidth * dir, behavior: 'smooth' });
};

// Helper to update both mobile and desktop cart badges
window.shopUpdateCartBadges = function(count) {
    document.querySelectorAll('.cart-header-badge').forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? '' : 'none';
    });
    const legacyBadge = document.getElementById('cart-header-badge');
    if (legacyBadge) {
        legacyBadge.textContent = count;
        legacyBadge.style.display = count > 0 ? '' : 'none';
    }
};

// Submits a cart update/remove form via fetch so only the drawer refreshes, not the whole page.
window.submitCartForm = function (form) {
    const formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        const checkoutLines = document.getElementById('checkout-cart-lines');

        // Emptying the cart from the checkout page itself: leave, same as a fresh page load would.
        if (checkoutLines && data.count === 0) {
            window.location.href = '{{ route('shop.products.index') }}';
            return;
        }

        const body = document.getElementById('cart-drawer-body');
        body.innerHTML = data.html;
        if (window.Alpine) Alpine.initTree(body);

        if (checkoutLines) {
            checkoutLines.innerHTML = data.checkout_lines_html;
            if (window.Alpine) Alpine.initTree(checkoutLines);
        }

        const orderForm = document.getElementById('checkout-order-form');
        if (orderForm && window.Alpine) Alpine.$data(orderForm).subtotal = data.subtotal;

        const countEl = document.getElementById('cart-drawer-count');
        if (countEl) countEl.textContent = data.count > 0 ? '(' + data.count + ')' : '';

        window.shopUpdateCartBadges(data.count);
    })
    .catch(() => form.submit());
};

window.updateCheckoutQty = function (key, qty) {
    const token = document.querySelector('meta[name=csrf-token]')?.content || '';
    const formData = new FormData();
    formData.append('_token', token);
    formData.append('qty[' + key + ']', qty);

    fetch('{{ route('shop.cart.update') }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        const checkoutLines = document.getElementById('checkout-cart-lines');
        if (checkoutLines && data.count === 0) {
            window.location.href = '{{ route('shop.products.index') }}';
            return;
        }

        const body = document.getElementById('cart-drawer-body');
        if (body) {
            body.innerHTML = data.html;
            if (window.Alpine) Alpine.initTree(body);
        }

        if (checkoutLines) {
            checkoutLines.innerHTML = data.checkout_lines_html;
            if (window.Alpine) Alpine.initTree(checkoutLines);
        }

        const orderForm = document.getElementById('checkout-order-form');
        if (orderForm && window.Alpine) {
            Alpine.$data(orderForm).subtotal = Number(data.subtotal) || 0;
        }

        const countEl = document.getElementById('cart-drawer-count');
        if (countEl) countEl.textContent = data.count > 0 ? '(' + data.count + ')' : '';

        window.shopUpdateCartBadges(data.count);
    })
    .catch(err => {
        console.error(err);
        window.location.reload();
    });
};

window.removeCheckoutQty = function (key) {
    const token = document.querySelector('meta[name=csrf-token]')?.content || '';
    const formData = new FormData();
    formData.append('_token', token);
    formData.append('_method', 'DELETE');

    fetch('{{ url('/cart/remove') }}/' + encodeURIComponent(key), {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        const checkoutLines = document.getElementById('checkout-cart-lines');
        if (checkoutLines && data.count === 0) {
            window.location.href = '{{ route('shop.products.index') }}';
            return;
        }

        const body = document.getElementById('cart-drawer-body');
        if (body) {
            body.innerHTML = data.html;
            if (window.Alpine) Alpine.initTree(body);
        }

        if (checkoutLines) {
            checkoutLines.innerHTML = data.checkout_lines_html;
            if (window.Alpine) Alpine.initTree(checkoutLines);
        }

        const orderForm = document.getElementById('checkout-order-form');
        if (orderForm && window.Alpine) {
            Alpine.$data(orderForm).subtotal = Number(data.subtotal) || 0;
        }

        const countEl = document.getElementById('cart-drawer-count');
        if (countEl) countEl.textContent = data.count > 0 ? '(' + data.count + ')' : '';

        window.shopUpdateCartBadges(data.count);
    })
    .catch(err => {
        console.error(err);
        window.location.reload();
    });
};

// ═════════════════════════════════════════════════════════════════
// Shop Quick Add to Cart, Quick View & Compare System
// ═════════════════════════════════════════════════════════════════
window.shopQuickAddToCart = function(productId, variantId = null, qty = 1) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const formData = new FormData();
    formData.append('_token', token);
    formData.append('product_id', productId);
    if (variantId) formData.append('variant_id', variantId);
    formData.append('qty', qty);

    return fetch('{{ route('shop.cart.add') }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const body = document.getElementById('cart-drawer-body');
            if (body && data.html) {
                body.innerHTML = data.html;
                if (window.Alpine) Alpine.initTree(body);
            }
            const countEl = document.getElementById('cart-drawer-count');
            if (countEl) countEl.textContent = data.count > 0 ? '(' + data.count + ')' : '';

            window.shopUpdateCartBadges(data.count);

            // Open Cart Drawer & close quick view
            const bodyEl = document.querySelector('body');
            if (bodyEl && window.Alpine) {
                const alpineData = Alpine.$data(bodyEl);
                if (alpineData) {
                    alpineData.cartOpen = true;
                    alpineData.quickViewOpen = false;
                }
            }

            showToast(data.message || 'Product added to cart!', 'success');
        } else {
            showToast(data.message || 'Could not add product to cart', 'error');
        }
        return data;
    })
    .catch(err => {
        console.error(err);
        showToast('Something went wrong adding to cart.', 'error');
    });
};

window.shopOpenQuickView = function(productSlug) {
    const bodyEl = document.querySelector('body');
    if (bodyEl && window.Alpine) {
        Alpine.$data(bodyEl).quickViewOpen = true;
    }

    const container = document.getElementById('quick-view-container');
    if (!container) return;

    container.innerHTML = `
        <div class="h-96 flex items-center justify-center text-gray-400">
            <div class="flex flex-col items-center gap-3">
                <i class="fas fa-circle-notch fa-spin text-3xl text-brand"></i>
                <span class="text-xs font-semibold">Loading product details...</span>
            </div>
        </div>
    `;

    fetch('{{ url('/shop/quick-view') }}/' + encodeURIComponent(productSlug), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => {
        if (!res.ok) throw new Error('Failed to load product');
        return res.text();
    })
    .then(html => {
        container.innerHTML = html;
        if (window.Alpine) Alpine.initTree(container);
    })
    .catch(err => {
        console.error(err);
        container.innerHTML = `
            <div class="h-64 flex flex-col items-center justify-center text-center p-6 text-gray-500">
                <i class="fas fa-circle-exclamation text-3xl text-rose-500 mb-2"></i>
                <p class="text-sm font-bold">Could not load preview</p>
                <button type="button" onclick="window.shopOpenQuickView('${productSlug}')" class="mt-3 px-4 py-1.5 bg-brand text-white rounded-lg text-xs font-bold">Retry</button>
            </div>
        `;
    });
};

const COMPARE_STORAGE_KEY = 'shop_compare_products_v1';

window.shopGetCompareList = function() {
    try {
        return JSON.parse(localStorage.getItem(COMPARE_STORAGE_KEY)) || [];
    } catch (e) {
        return [];
    }
};

window.shopSaveCompareList = function(list) {
    try {
        localStorage.setItem(COMPARE_STORAGE_KEY, JSON.stringify(list));
    } catch (e) {}
    window.shopSyncCompareBadges();
};

window.shopSyncCompareBadges = function() {
    const list = window.shopGetCompareList();
    const count = list.length;

    document.querySelectorAll('.compare-header-badge').forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? '' : 'none';
    });
    const legacyBadge = document.getElementById('compare-header-badge');
    if (legacyBadge) {
        legacyBadge.textContent = count;
        legacyBadge.style.display = count > 0 ? '' : 'none';
    }

    const drawerCount = document.getElementById('compare-drawer-count');
    if (drawerCount) drawerCount.textContent = count;

    const bodyEl = document.querySelector('body');
    if (bodyEl && window.Alpine) {
        const alpineData = Alpine.$data(bodyEl);
        if (alpineData) alpineData.compareCount = count;
    }
};

window.shopToggleCompare = function(product) {
    let list = window.shopGetCompareList();
    const existsIndex = list.findIndex(item => item.id == product.id);

    if (existsIndex > -1) {
        list.splice(existsIndex, 1);
        window.shopSaveCompareList(list);
        window.shopRenderCompareDrawer();
        showToast(product.name + ' removed from comparison', 'info');
    } else {
        if (list.length >= 6) {
            showToast('You can compare up to 6 products at a time', 'error');
            return;
        }
        list.push(product);
        window.shopSaveCompareList(list);
        window.shopRenderCompareDrawer();
        showToast(product.name + ' added to comparison list', 'success');
    }
};

window.shopRemoveCompareItem = function(id) {
    let list = window.shopGetCompareList();
    list = list.filter(item => item.id != id);
    window.shopSaveCompareList(list);
    window.shopRenderCompareDrawer();
};

window.shopClearCompare = function() {
    window.shopSaveCompareList([]);
    window.shopRenderCompareDrawer();
    showToast('Comparison list cleared', 'info');
};

window.shopRenderCompareDrawer = function() {
    const container = document.getElementById('compare-drawer-body');
    if (!container) return;

    const list = window.shopGetCompareList();
    if (!list.length) {
        container.innerHTML = `
            <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3 text-2xl">
                    <i class="fas fa-arrows-rotate"></i>
                </div>
                <p class="text-sm font-bold text-gray-700">No products in comparison</p>
                <p class="text-xs text-gray-400 mt-1 max-w-[220px]">Hover on any product card and click the comparison icon to add products here.</p>
            </div>
        `;
        return;
    }

    let html = '<div class="divide-y divide-gray-100">';
    list.forEach(item => {
        html += `
            <div class="py-3 flex items-start gap-3 group">
                <div class="w-16 h-20 rounded-lg bg-[#F8F9FA] border border-gray-100 overflow-hidden flex items-center justify-center p-1 flex-shrink-0">
                    <img src="${item.image || ''}" alt="${item.name}" class="max-w-full max-h-full object-contain">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium">${item.category || ''}</div>
                    <a href="/shop/${item.slug}" class="text-xs font-bold text-gray-900 hover:text-brand line-clamp-2 leading-tight mt-0.5">${item.name}</a>
                    <div class="text-xs font-extrabold text-brand-dark mt-1">${item.price}৳</div>
                    <div class="mt-2 flex items-center gap-2">
                        <button type="button" onclick="window.shopQuickAddToCart(${item.id})" class="px-2.5 py-1 bg-brand hover:bg-brand-dark text-white text-[11px] font-bold rounded-md transition-colors flex items-center gap-1">
                            <i class="fas fa-bag-shopping text-[9px]"></i> Add to Cart
                        </button>
                        <a href="/shop/${item.slug}" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-semibold rounded-md transition-colors">
                            Details
                        </a>
                    </div>
                </div>
                <button type="button" onclick="window.shopRemoveCompareItem(${item.id})" class="text-gray-300 hover:text-rose-500 p-1 transition-colors" title="Remove">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
};

document.addEventListener('DOMContentLoaded', () => {
    window.shopSyncCompareBadges();
    window.shopSyncWishlistBadges();
});

const WISHLIST_STORAGE_KEY = 'shop_wishlist_products_v1';

window.shopGetWishlist = function() {
    try {
        return JSON.parse(localStorage.getItem(WISHLIST_STORAGE_KEY)) || [];
    } catch (e) {
        return [];
    }
};

window.shopSaveWishlist = function(list) {
    try {
        localStorage.setItem(WISHLIST_STORAGE_KEY, JSON.stringify(list));
    } catch (e) {}
    window.shopSyncWishlistBadges();
};

window.shopIsInWishlist = function(id) {
    const list = window.shopGetWishlist();
    return list.some(item => item.id == id);
};

window.shopSyncWishlistBadges = function() {
    const list = window.shopGetWishlist();
    const count = list.length;

    document.querySelectorAll('.wishlist-header-badge').forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? '' : 'none';
    });
    const legacyBadge = document.getElementById('wishlist-header-badge');
    if (legacyBadge) {
        legacyBadge.textContent = count;
        legacyBadge.style.display = count > 0 ? '' : 'none';
    }

    const drawerCount = document.getElementById('wishlist-drawer-count');
    if (drawerCount) drawerCount.textContent = count;

    const bodyEl = document.querySelector('body');
    if (bodyEl && window.Alpine) {
        const alpineData = Alpine.$data(bodyEl);
        if (alpineData) alpineData.wishlistCount = count;
    }
};

window.shopToggleWishlist = function(product) {
    let list = window.shopGetWishlist();
    const existsIndex = list.findIndex(item => item.id == product.id);
    let isLiked = false;

    if (existsIndex > -1) {
        list.splice(existsIndex, 1);
        window.shopSaveWishlist(list);
        window.shopRenderWishlistDrawer();
        showToast(product.name + ' removed from wishlist', 'info');
        isLiked = false;
    } else {
        list.push(product);
        window.shopSaveWishlist(list);
        window.shopRenderWishlistDrawer();
        showToast(product.name + ' added to wishlist', 'success');
        isLiked = true;
    }
    return isLiked;
};

window.shopRemoveWishlistItem = function(id) {
    let list = window.shopGetWishlist();
    list = list.filter(item => item.id != id);
    window.shopSaveWishlist(list);
    window.shopRenderWishlistDrawer();
};

window.shopClearWishlist = function() {
    window.shopSaveWishlist([]);
    window.shopRenderWishlistDrawer();
    showToast('Wishlist cleared', 'info');
};

window.shopRenderWishlistDrawer = function() {
    const container = document.getElementById('wishlist-drawer-body');
    if (!container) return;

    const list = window.shopGetWishlist();
    if (!list.length) {
        container.innerHTML = `
            <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                <div class="w-14 h-14 rounded-full bg-rose-50 flex items-center justify-center text-rose-400 mb-3 text-2xl">
                    <i class="far fa-heart"></i>
                </div>
                <p class="text-sm font-bold text-gray-700">Your wishlist is empty</p>
                <p class="text-xs text-gray-400 mt-1 max-w-[220px]">Click the heart icon on any product to save it to your wishlist.</p>
            </div>
        `;
        return;
    }

    let html = '<div class="divide-y divide-gray-100">';
    list.forEach(item => {
        html += `
            <div class="py-3 flex items-start gap-3 group">
                <div class="w-16 h-20 rounded-lg bg-[#F8F9FA] border border-gray-100 overflow-hidden flex items-center justify-center p-1 flex-shrink-0">
                    <img src="${item.image || ''}" alt="${item.name}" class="max-w-full max-h-full object-contain">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium">${item.category || ''}</div>
                    <a href="/shop/${item.slug}" class="text-xs font-bold text-gray-900 hover:text-brand line-clamp-2 leading-tight mt-0.5">${item.name}</a>
                    <div class="text-xs font-extrabold text-brand-dark mt-1">${item.price}৳</div>
                    <div class="mt-2 flex items-center gap-2">
                        <button type="button" onclick="window.shopQuickAddToCart(${item.id})" class="px-2.5 py-1 bg-brand hover:bg-brand-dark text-white text-[11px] font-bold rounded-md transition-colors flex items-center gap-1">
                            <i class="fas fa-bag-shopping text-[9px]"></i> Add to Cart
                        </button>
                        <a href="/shop/${item.slug}" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-semibold rounded-md transition-colors">
                            Details
                        </a>
                    </div>
                </div>
                <button type="button" onclick="window.shopRemoveWishlistItem(${item.id})" class="text-gray-300 hover:text-rose-500 p-1 transition-colors" title="Remove">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
};

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],
        add(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type, visible: true });
            setTimeout(() => this.dismiss(id), 4500);
        },
        dismiss(id) {
            const t = this.items.find(t => t.id === id);
            if (t) t.visible = false;
            setTimeout(() => { this.items = this.items.filter(t => t.id !== id); }, 250);
        }
    });
});
function showToast(message, type = 'success') {
    if (window.Alpine) Alpine.store('toast').add(message, type);
}
document.addEventListener('alpine:initialized', () => {
    @if(session('success')) showToast(@json(session('success')), 'success'); @endif
    @if(session('error'))   showToast(@json(session('error')),   'error');   @endif
    @if($errors->any())
        @foreach($errors->all() as $e) showToast(@json($e), 'error'); @endforeach
    @endif
});
</script>
</body>
</html>
