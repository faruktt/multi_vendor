<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Reseller Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .rsidebar { background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%); }
        .rnav-link { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; color:#a5b4fc; font-size:13.5px; font-weight:500; text-decoration:none; transition:all .15s; }
        .rnav-link:hover { background:rgba(255,255,255,.08); color:#e0e7ff; }
        .rnav-link.active { background:#4f46e5; color:#fff; box-shadow:0 2px 10px rgba(79,70,229,.4); }
        .rnav-link .icon { width:18px; text-align:center; flex-shrink:0; }
        .rsection { padding:14px 12px 5px; font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#4338ca; }
        .sidebar-scroll::-webkit-scrollbar { width:3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background:rgba(255,255,255,.1); border-radius:2px; }
        @media (max-width:1023px) {
            .rsidebar-offcanvas { transform:translateX(-100%); transition:transform .25s; }
            .rsidebar-offcanvas.open { transform:translateX(0); }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50" x-data="{ sidebarOpen: false }" x-effect="if (sidebarOpen) setTimeout(() => window.dispatchEvent(new CustomEvent('sidebar-opened')), 100)">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen=false" x-cloak
     class="fixed inset-0 bg-black/60 z-20 lg:hidden"></div>

{{-- SIDEBAR --}}
<aside class="rsidebar rsidebar-offcanvas fixed top-0 left-0 h-screen w-[240px] z-30 flex flex-col"
       :class="{ 'open': sidebarOpen }">

    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-[18px] border-b border-white/10 flex-shrink-0">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center shadow-lg flex-shrink-0">
            <i class="fas fa-user-tag text-white text-sm"></i>
        </div>
        <div>
            <div class="text-white font-bold text-[13px] leading-tight">Reseller Portal</div>
            <div class="text-indigo-300 text-[11px] mt-0.5 truncate">{{ auth('reseller')->user()->name }}</div>
        </div>
    </div>

    {{-- Nav --}}
    <nav id="resellerSidebarNav" class="flex-1 overflow-y-auto sidebar-scroll py-3 px-3 space-y-0.5">

        <a href="{{ route('reseller.dashboard') }}"
           class="rnav-link {{ request()->routeIs('reseller.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home icon"></i> Dashboard
        </a>

        <div class="rsection">Shop</div>

        <a href="{{ route('reseller.products.index') }}"
           class="rnav-link {{ request()->routeIs('reseller.products.*') ? 'active' : '' }}">
            <i class="fas fa-box-open icon"></i> Products
        </a>

        <a href="{{ route('reseller.cart.index') }}"
           class="rnav-link {{ request()->routeIs('reseller.cart.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart icon"></i>
            Cart
            @php $cartCount = \App\Http\Controllers\Reseller\CartController::count(); @endphp
            @if($cartCount > 0)
                <span class="ml-auto bg-indigo-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $cartCount }}</span>
            @endif
        </a>

        <div class="rsection">Orders</div>

        <a href="{{ route('reseller.orders.index') }}"
           class="rnav-link {{ request()->routeIs('reseller.orders.*') ? 'active' : '' }}">
            <i class="fas fa-receipt icon"></i> My Orders
        </a>

        <div class="rsection">Account</div>

        <a href="{{ route('reseller.account') }}"
           class="rnav-link {{ request()->routeIs('reseller.account*') ? 'active' : '' }}">
            <i class="fas fa-user-circle icon"></i> My Account
        </a>

        <a href="{{ route('reseller.profile') }}"
           class="rnav-link {{ request()->routeIs('reseller.profile*') ? 'active' : '' }}">
            <i class="fas fa-user-gear icon"></i> Profile Settings
        </a>

        <a href="{{ route('reseller.withdrawals.index') }}"
           class="rnav-link {{ request()->routeIs('reseller.withdrawals.*') ? 'active' : '' }}">
            <i class="fas fa-wallet icon"></i> Withdrawals / Payouts
        </a>

        <div class="rsection">Support &amp; Help</div>

        @php
            $adminPhone = \App\Helpers\AppSetting::get('phone');
            $cleanAdminPhone = preg_replace('/[^\d]/', '', (string)$adminPhone);
            if (str_starts_with($cleanAdminPhone, '01')) {
                $cleanAdminPhone = '88' . $cleanAdminPhone;
            }
        @endphp
        @if(!empty($cleanAdminPhone))
        <a href="https://wa.me/{{ $cleanAdminPhone }}?text={{ urlencode('Hello Admin, I am contacting you from Reseller Panel.') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="rnav-link text-emerald-300 hover:text-emerald-100 hover:bg-emerald-500/20 font-semibold group">
            <i class="fab fa-whatsapp icon text-[#25D366] text-sm group-hover:scale-110 transition-transform"></i> WhatsApp Support
        </a>
        @endif

        <form method="POST" action="{{ route('reseller.logout') }}">
            @csrf
            <button type="submit" class="rnav-link w-full text-left hover:text-red-300">
                <i class="fas fa-sign-out-alt icon"></i> Logout
            </button>
        </form>

    </nav>

    {{-- WhatsApp Support Button --}}
    @if(!empty($cleanAdminPhone))
    <div class="px-3 pb-2 flex-shrink-0">
        <a href="https://wa.me/{{ $cleanAdminPhone }}?text={{ urlencode('Hello Admin, I am contacting you from Reseller Panel.') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-xs shadow-md shadow-[#25D366]/20 transition-all hover:scale-[1.02] active:scale-95">
            <i class="fab fa-whatsapp text-sm"></i> WhatsApp Support
        </a>
    </div>
    @endif

    {{-- Footer --}}
    <div class="px-4 py-3 border-t border-white/10 flex-shrink-0">
        <a href="{{ route('reseller.account') }}" class="flex items-center gap-2.5 group">
            @if(auth('reseller')->user()->image_url)
                <img src="{{ auth('reseller')->user()->image_url }}" alt="{{ auth('reseller')->user()->name }}"
                     class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-400/40">
            @else
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold ring-2 ring-indigo-400/40">
                    {{ strtoupper(substr(auth('reseller')->user()->name, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <div class="text-white text-xs font-semibold truncate group-hover:text-indigo-200 transition-colors">{{ auth('reseller')->user()->name }}</div>
                <div class="text-indigo-300 text-[10px] truncate">{{ auth('reseller')->user()->email }}</div>
            </div>
        </a>
    </div>
</aside>

{{-- ── Sidebar Scroll Position & Active Item View Manager ── --}}
<script>
(function() {
    function getStoredScroll() {
        try { return sessionStorage.getItem('reseller_sidebar_scroll'); } catch(e) { return null; }
    }
    function setStoredScroll(val) {
        try { sessionStorage.setItem('reseller_sidebar_scroll', val); } catch(e) {}
    }

    function syncSidebarScroll(smooth) {
        var nav = document.getElementById('resellerSidebarNav') || document.querySelector('aside.rsidebar nav');
        if (!nav) return;

        var savedScroll = getStoredScroll();
        if (savedScroll !== null) {
            nav.scrollTop = parseInt(savedScroll, 10);
        }

        var activeItem = nav.querySelector('.rnav-link.active, a.active');
        if (activeItem) {
            var navRect = nav.getBoundingClientRect();
            var itemRect = activeItem.getBoundingClientRect();

            if (navRect.height > 0 && itemRect.height > 0) {
                var isVisible = (
                    itemRect.top >= navRect.top + 20 &&
                    itemRect.bottom <= navRect.bottom - 20
                );

                if (!isVisible) {
                    var currentScroll = nav.scrollTop;
                    var diff = (itemRect.top - navRect.top) - (navRect.height / 2) + (itemRect.height / 2);
                    var targetScroll = Math.max(0, Math.round(currentScroll + diff));

                    if (smooth) {
                        nav.scrollTo({ top: targetScroll, behavior: 'smooth' });
                    } else {
                        nav.scrollTop = targetScroll;
                    }
                    setStoredScroll(nav.scrollTop);
                }
            }
        }
    }

    syncSidebarScroll(false);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { syncSidebarScroll(false); });
    }
    document.addEventListener('alpine:initialized', function() {
        setTimeout(function() { syncSidebarScroll(false); }, 60);
    });
    window.addEventListener('load', function() { syncSidebarScroll(false); });
    window.addEventListener('sidebar-opened', function() { syncSidebarScroll(false); });
    window.addEventListener('resize', function() { syncSidebarScroll(false); });

    function bindResellerSidebarHandlers() {
        var nav = document.getElementById('resellerSidebarNav') || document.querySelector('aside.rsidebar nav');
        if (!nav || nav.dataset.scrollBound) return;
        nav.dataset.scrollBound = '1';

        nav.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (a && nav.contains(a)) {
                setStoredScroll(nav.scrollTop);
            }
        });

        var scrollTimer = null;
        nav.addEventListener('scroll', function() {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                setStoredScroll(nav.scrollTop);
            }, 80);
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindResellerSidebarHandlers);
    } else {
        bindResellerSidebarHandlers();
    }
})();
</script>

{{-- MAIN CONTENT --}}
<div class="lg:ml-[240px] min-h-screen flex flex-col">

    {{-- Top bar --}}
    <header class="bg-white border-b border-slate-100 px-4 sm:px-6 py-2.5 flex items-center gap-3 sticky top-0 z-20 shadow-xs">
        <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <div class="flex-1 min-w-0">
            <h1 class="font-bold text-slate-800 text-[15px] truncate">@yield('heading', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-3">
            {{-- Cart Button --}}
            <a href="{{ route('reseller.cart.index') }}" class="relative p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50/60 rounded-xl transition-colors" title="Cart">
                <i class="fas fa-shopping-cart text-lg"></i>
                @if($cartCount > 0)
                    <span class="absolute top-0.5 right-0.5 bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.2 rounded-full min-w-[18px] text-center shadow-xs">{{ $cartCount }}</span>
                @endif
            </a>

            <div class="h-6 w-px bg-slate-200"></div>

            {{-- User Profile Avatar & Dropdown --}}
            <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                <button @click="profileOpen = !profileOpen"
                        type="button"
                        class="flex items-center gap-2.5 p-1 sm:pr-2.5 rounded-full hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200 focus:outline-none"
                        :class="{ 'bg-slate-100 border-slate-200': profileOpen }">
                    <div class="relative">
                        @if(auth('reseller')->user()->image_url)
                            <img src="{{ auth('reseller')->user()->image_url }}" alt="{{ auth('reseller')->user()->name }}"
                                 class="w-9 h-9 rounded-full object-cover ring-2 ring-indigo-500/30 shadow-xs">
                        @else
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-bold text-xs flex items-center justify-center ring-2 ring-indigo-500/30 shadow-xs">
                                {{ strtoupper(substr(auth('reseller')->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-tight truncate max-w-[120px]">{{ auth('reseller')->user()->name }}</div>
                        <div class="text-[10px] font-medium text-slate-400">Reseller</div>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:block transition-transform duration-200"
                       :class="{ 'rotate-180': profileOpen }"></i>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="profileOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 overflow-hidden">
                    
                    {{-- User Header inside dropdown --}}
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                        @if(auth('reseller')->user()->image_url)
                            <img src="{{ auth('reseller')->user()->image_url }}" alt="{{ auth('reseller')->user()->name }}"
                                 class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-400/30 shadow-xs flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-xs">
                                {{ strtoupper(substr(auth('reseller')->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-bold text-slate-800 truncate">{{ auth('reseller')->user()->name }}</div>
                            <div class="text-xs text-slate-400 truncate">{{ auth('reseller')->user()->email }}</div>
                            @if(auth('reseller')->user()->business_name)
                                <div class="text-[10px] font-semibold text-indigo-600 truncate mt-0.5"><i class="fas fa-store mr-1"></i>{{ auth('reseller')->user()->business_name }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Wallet Mini Badge --}}
                    <div class="px-4 py-2 bg-indigo-50/50 mx-2 my-2 rounded-xl flex items-center justify-between border border-indigo-100/60">
                        <div>
                            <div class="text-[10px] font-medium text-slate-500">Available Balance</div>
                            <div class="text-xs font-bold text-indigo-700">৳{{ number_format(auth('reseller')->user()->available_balance, 2) }}</div>
                        </div>
                        <a href="{{ route('reseller.withdrawals.index') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-white px-2 py-1 rounded-lg shadow-2xs border border-indigo-100">
                            Withdraw
                        </a>
                    </div>

                    {{-- Links --}}
                    <div class="py-1">
                        <a href="{{ route('reseller.account') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                            <div class="w-6 text-center text-indigo-500"><i class="fas fa-user-circle"></i></div>
                            <span>My Account (হিসাব-নিকাশ)</span>
                        </a>

                        <a href="{{ route('reseller.profile') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                            <div class="w-6 text-center text-purple-500"><i class="fas fa-user-gear"></i></div>
                            <span>Profile Settings (প্রোফাইল এডিট)</span>
                        </a>

                        <a href="{{ route('reseller.orders.index') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                            <div class="w-6 text-center text-emerald-500"><i class="fas fa-receipt"></i></div>
                            <span>My Orders (আমার অর্ডার)</span>
                        </a>

                        <a href="{{ route('reseller.withdrawals.index') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                            <div class="w-6 text-center text-amber-500"><i class="fas fa-wallet"></i></div>
                            <span>Withdrawals (টাকা উত্তোলন)</span>
                        </a>
                    </div>

                    <div class="border-t border-slate-100 my-1"></div>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('reseller.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors text-left">
                            <div class="w-6 text-center text-red-500"><i class="fas fa-sign-out-alt"></i></div>
                            <span>Logout (লগআউট)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    <div class="px-4 sm:px-6 pt-4">
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
        @endif
    </div>

    <main class="flex-1 px-4 sm:px-6 pb-8">
        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
