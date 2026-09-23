<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supplier Portal') — {{ config('app.name', 'Marketplace') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        supplier: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .sup-sidebar { background: linear-gradient(180deg, #064e3b 0%, #022c22 100%); }
        .sup-nav-link { display:flex; align-items:center; gap:11px; padding:9px 14px; border-radius:10px; color:#a7f3d0; font-size:13.5px; font-weight:500; text-decoration:none; transition:all .18s; }
        .sup-nav-link:hover { background:rgba(255,255,255,.08); color:#ffffff; transform:translateX(2px); }
        .sup-nav-link.active { background:#059669; color:#fff; box-shadow:0 3px 12px rgba(5,150,105,.45); font-weight:600; }
        .sup-nav-link .icon { width:18px; text-align:center; flex-shrink:0; font-size:14px; }
        .sup-section { padding:14px 14px 6px; font-size:10px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#34d399; }
        .sidebar-scroll::-webkit-scrollbar { width:3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background:rgba(255,255,255,.15); border-radius:2px; }
        @media (max-width:1023px) {
            .sup-sidebar-offcanvas { transform:translateX(-100%); transition:transform .25s ease-in-out; }
            .sup-sidebar-offcanvas.open { transform:translateX(0); }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen=false" x-cloak
     class="fixed inset-0 bg-black/60 z-30 lg:hidden backdrop-blur-sm"></div>

{{-- SIDEBAR --}}
<aside class="sup-sidebar sup-sidebar-offcanvas fixed top-0 left-0 h-screen w-[250px] z-40 flex flex-col shadow-2xl"
       :class="{ 'open': sidebarOpen }">

    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-[20px] border-b border-emerald-800/60 flex-shrink-0">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-200 flex items-center justify-center shadow-lg flex-shrink-0 text-emerald-950 font-black text-lg">
            <i class="fas fa-store"></i>
        </div>
        <div class="min-w-0">
            <div class="text-white font-black text-[14px] tracking-wide leading-tight truncate">Supplier Portal</div>
            <div class="text-emerald-300 text-[11px] font-medium mt-0.5 truncate">{{ auth('supplier')->user()->display_name }}</div>
        </div>
    </div>

    {{-- Nav Links --}}
    <nav class="flex-1 overflow-y-auto sidebar-scroll py-4 px-3 space-y-1">

        <a href="{{ route('supplier.dashboard') }}"
           class="sup-nav-link {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie icon"></i> Dashboard
        </a>

        <div class="sup-section">Catalog</div>

        <a href="{{ route('supplier.products.index') }}"
           class="sup-nav-link {{ request()->routeIs('supplier.products.index') || request()->routeIs('supplier.products.edit') ? 'active' : '' }}">
            <i class="fas fa-boxes-stacked icon"></i> My Products
        </a>

        <a href="{{ route('supplier.products.create') }}"
           class="sup-nav-link {{ request()->routeIs('supplier.products.create') ? 'active' : '' }}">
            <i class="fas fa-circle-plus icon"></i> Add New Product
        </a>

        <div class="sup-section">Sales & Orders</div>

        <a href="{{ route('supplier.orders.index') }}"
           class="sup-nav-link {{ request()->routeIs('supplier.orders.*') ? 'active' : '' }}">
            <i class="fas fa-receipt icon"></i> Sales & Orders
        </a>

        <div class="sup-section">Finance & Payouts</div>

        <a href="{{ route('supplier.withdrawals.index') }}"
           class="sup-nav-link {{ request()->routeIs('supplier.withdrawals.*') ? 'active' : '' }}">
            <i class="fas fa-wallet icon"></i> Earnings &amp; Withdrawals
        </a>

        <div class="sup-section">Store & Account</div>

        @if(Route::has('shop.supplier.show'))
        <a href="{{ route('shop.supplier.show', auth('supplier')->user()) }}" target="_blank"
           class="sup-nav-link hover:text-emerald-200">
            <i class="fas fa-arrow-up-right-from-square icon"></i> View Live Store
        </a>
        @endif

        <a href="{{ route('supplier.profile') }}"
           class="sup-nav-link {{ request()->routeIs('supplier.profile') ? 'active' : '' }}">
            <i class="fas fa-sliders icon"></i> Store Settings
        </a>

        <div class="sup-section">Support &amp; Help</div>

        @php
            $adminPhone = \App\Helpers\AppSetting::get('phone');
            $cleanAdminPhone = preg_replace('/[^\d]/', '', (string)$adminPhone);
            if (str_starts_with($cleanAdminPhone, '01')) {
                $cleanAdminPhone = '88' . $cleanAdminPhone;
            }
        @endphp
        @if(!empty($cleanAdminPhone))
        <a href="https://wa.me/{{ $cleanAdminPhone }}?text={{ urlencode('Hello Admin, I am contacting you from Supplier Panel.') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="sup-nav-link text-emerald-300 hover:text-emerald-100 hover:bg-emerald-500/20 font-semibold group">
            <i class="fab fa-whatsapp icon text-[#25D366] text-sm group-hover:scale-110 transition-transform"></i> WhatsApp Support
        </a>
        @endif

        <form method="POST" action="{{ route('supplier.logout') }}" class="pt-2">
            @csrf
            <button type="submit" class="sup-nav-link w-full text-left text-red-300 hover:text-red-100 hover:bg-red-500/20">
                <i class="fas fa-arrow-right-from-bracket icon"></i> Logout
            </button>
        </form>

    </nav>

    {{-- WhatsApp Support Button --}}
    @if(!empty($cleanAdminPhone))
    <div class="px-3 pb-2 flex-shrink-0">
        <a href="https://wa.me/{{ $cleanAdminPhone }}?text={{ urlencode('Hello Admin, I am contacting you from Supplier Panel.') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-xs shadow-md shadow-[#25D366]/20 transition-all hover:scale-[1.02] active:scale-95">
            <i class="fab fa-whatsapp text-sm"></i> WhatsApp Support
        </a>
    </div>
    @endif

    {{-- Footer profile badge --}}
    <div class="px-4 py-3 border-t border-emerald-800/60 flex-shrink-0 bg-black/20">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-emerald-500/30 border border-emerald-400/40 flex items-center justify-center text-white font-bold text-sm shadow-inner flex-shrink-0">
                {{ strtoupper(substr(auth('supplier')->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-white text-xs font-semibold truncate">{{ auth('supplier')->user()->name }}</div>
                <div class="text-emerald-400 text-[10px] flex items-center gap-1 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Verified Vendor
                </div>
            </div>
        </div>
    </div>

</aside>

{{-- MAIN WRAPPER --}}
<div class="lg:pl-[250px] min-h-screen flex flex-col">

    {{-- TOP NAVBAR --}}
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-20 shadow-sm">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5">
            {{-- Mobile Toggle --}}
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <div class="font-bold text-slate-800 text-lg hidden sm:block">
                    @yield('heading', 'Supplier Dashboard')
                </div>
            </div>

            {{-- Quick action links --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('supplier.products.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow-emerald-600/30">
                    <i class="fas fa-plus text-[11px]"></i> Add Product
                </a>

                <a href="{{ route('shop.products.index') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-900 text-xs font-medium transition-colors bg-white">
                    <i class="fas fa-globe text-slate-400 text-[11px]"></i> Visit Website
                </a>

                <div class="h-6 w-px bg-slate-200 mx-1"></div>

                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-slate-800">{{ auth('supplier')->user()->display_name }}</div>
                    <div class="text-[10px] text-slate-400">{{ auth('supplier')->user()->email }}</div>
                </div>
            </div>
        </div>
    </header>

    {{-- FLASH MESSAGES --}}
    <div class="px-4 sm:px-6 pt-4">
        @if(session('success'))
        <div class="mb-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2.5 shadow-sm">
            <i class="fas fa-circle-check text-emerald-500 text-base"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2.5 shadow-sm">
            <i class="fas fa-circle-exclamation text-rose-500 text-base"></i>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="mb-3 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center gap-2.5 shadow-sm">
            <i class="fas fa-circle-info text-blue-500 text-base"></i>
            <div>{{ session('info') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-sm">
            <div class="font-bold flex items-center gap-2 mb-1">
                <i class="fas fa-triangle-exclamation text-rose-500"></i> Please check the following errors:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 ml-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    {{-- CONTENT --}}
    <main class="flex-1 px-4 sm:px-6 py-4">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-white border-t border-slate-200 py-3.5 px-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ config('app.name') }} Supplier Marketplace Portal. All rights reserved.
    </footer>

</div>

@stack('scripts')
</body>
</html>
