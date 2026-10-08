<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $appSettings['name'] ?? 'Super POS' }}</title>
    @if(!empty($appSettings['favicon']))
    <link rel="icon" href="{{ $appSettings['favicon'] }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }

        .sidebar { background: linear-gradient(180deg, #0f172a 0%, #0e1e38 100%); }

        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 8px;
            color: #94a3b8; font-size: 13.5px; font-weight: 500;
            text-decoration: none; transition: all .15s ease;
        }
        .nav-link:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
        .nav-link.is-active { background: #1d4ed8; color: #fff; box-shadow: 0 2px 10px rgba(29,78,216,.35); }
        .nav-link .icon { width: 18px; text-align: center; flex-shrink: 0; }

        .branch-btn {
            display: flex; align-items: center; gap: 9px; width: 100%;
            padding: 7px 10px; border-radius: 8px;
            color: #94a3b8; font-size: 13px; font-weight: 500;
            background: none; border: none; cursor: pointer; transition: all .15s ease;
        }
        .branch-btn:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
        .branch-btn.branch-open { color: #e2e8f0; }

        .sub-link {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 10px 6px 12px; border-radius: 6px;
            color: #64748b; font-size: 12.5px; font-weight: 500;
            text-decoration: none; transition: all .15s ease;
        }
        .sub-link:hover { background: rgba(255,255,255,.05); color: #cbd5e1; }
        .sub-link.sub-active { background: rgba(37,99,235,.22); color: #93c5fd; }

        .section-label {
            padding: 14px 12px 5px; font-size: 10px; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase; color: #334155;
        }
        .nav-divider { height: 1px; background: rgba(255,255,255,.06); margin: 8px 0; }

        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-center: center;
            font-size: 13px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }

        .sidebar-scroll::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 2px; }

        html, body {
            overflow-x: clip;
        }

        .overflow-x-auto {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            touch-action: pan-x pan-y;
        }

        /* Horizontal scrollbar for tables */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media (max-width: 1023px) {
            .sidebar-offcanvas { transform: translateX(-100%); transition: transform .25s ease; }
            .sidebar-offcanvas.is-open { transform: translateX(0); }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-100" x-data="{ sidebarOpen: false }" x-effect="if (sidebarOpen) setTimeout(() => window.dispatchEvent(new CustomEvent('sidebar-opened')), 100)">

{{-- Mobile overlay --}}
<div x-show="sidebarOpen" @click="sidebarOpen = false"
     x-transition:enter="transition-opacity duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 z-20 lg:hidden" x-cloak></div>

{{-- ────────────── SIDEBAR ────────────── --}}
<aside class="sidebar sidebar-offcanvas fixed top-0 left-0 h-screen w-[260px] z-30 flex flex-col"
       :class="{ 'is-open': sidebarOpen }">

    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-[18px] border-b border-white/[.07] flex-shrink-0">
        @if(!empty($appSettings['logo']))
            <img src="{{ $appSettings['logo'] }}" alt="{{ $appSettings['name'] ?? 'Logo' }}" class="h-9 max-w-[180px] object-contain flex-shrink-0">
        @else
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center flex-shrink-0 shadow-lg">
                <i class="fas fa-store text-white text-[13px]"></i>
            </div>
            <div class="min-w-0">
                <div class="text-white font-bold text-[13.5px] leading-tight truncate">{{ $appSettings['name'] ?? 'Super POS' }}</div>
                <div class="text-slate-500 text-[11px] truncate mt-0.5">{{ $appSettings['tagline'] ?? 'Multi-Branch POS' }}</div>
            </div>
        @endif
    </div>

    {{-- Navigation --}}
    <nav id="adminSidebarNav" class="flex-1 overflow-y-auto sidebar-scroll py-3 px-3 space-y-0.5">
        @auth
        @php
            $authUser     = auth()->user();
            $isSuperAdmin = $authUser->hasRole('super-admin');
            $colors       = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
        @endphp

        @if($isSuperAdmin)
            {{-- Overview --}}
            <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.index') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-chart-pie text-[11px]"></i></span> Overview
            </a>

            {{-- Branches --}}
            <div class="section-label">Branches</div>

            @foreach(($branches ?? collect())->sortBy(fn($b) => $b->is_warehouse ? 1 : 0)->values() as $idx => $b)
            @php
                $dotColor = $colors[$idx % count($colors)];
                $isActive = isset($branch) && $branch && $b->id === $branch->id;
            @endphp
            <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                <button @click="open = !open" :class="open ? 'branch-open' : ''" class="branch-btn">
                    @if($b->is_warehouse)
                    <i class="fas fa-warehouse text-[10px] text-orange-400 flex-shrink-0"></i>
                    @else
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};flex-shrink:0;display:inline-block;"></span>
                    @endif
                    <span class="flex-1 text-left truncate">{{ $b->name }}</span>
                    <i class="fas fa-chevron-right text-[9px] text-slate-600 transition-transform duration-200"
                       :class="open ? 'rotate-90' : ''"></i>
                </button>
                <div x-show="open" x-cloak
                     x-transition:enter="transition-all duration-150 ease-out"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-0.5 ml-[17px] pl-3 border-l border-slate-700/50 space-y-0.5 pb-1.5">

                    <a href="{{ $b->is_warehouse ? route('admin.warehouse.dashboard', 'warehouse') : route('branch.dashboard', $b) }}"
                       class="sub-link {{ $isActive && (request()->routeIs('branch.dashboard') || request()->routeIs('admin.warehouse.dashboard')) ? 'sub-active' : '' }}">
                        <i class="fas fa-gauge-high text-[10px] w-3.5 text-center"></i> Dashboard
                    </a>

                    @unless($b->is_warehouse)
                    <a href="{{ route('branch.pos.index', $b) }}"
                       class="sub-link {{ $isActive && request()->routeIs('branch.pos.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-cash-register text-[10px] w-3.5 text-center"></i> POS Terminal
                    </a>
                    <a href="{{ route('branch.sales.index', $b) }}"
                       class="sub-link {{ $isActive && request()->routeIs('branch.sales.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-receipt text-[10px] w-3.5 text-center"></i> Sales
                    </a>
                    @endunless

                    {{-- Divider --}}
                    <div style="height:1px;background:rgba(255,255,255,.06);margin:4px 0;"></div>

                    {{-- Management --}}
                    <a href="{{ $b->is_warehouse ? route('admin.warehouse.products.index', 'warehouse') : route('branch.products.index', $b) }}"
                       class="sub-link {{ $isActive && (request()->routeIs('branch.products.*') || request()->routeIs('admin.warehouse.products.*')) ? 'sub-active' : '' }}">
                        <i class="fas fa-box text-[10px] w-3.5 text-center"></i> Products
                    </a>
                    @if($b->is_warehouse)
                    <a href="{{ route('admin.warehouse.categories.index', 'warehouse') }}"
                       class="sub-link {{ $isActive && request()->routeIs('admin.warehouse.categories.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-tags text-[10px] w-3.5 text-center"></i> Categories
                    </a>
                    @endif

                    @if($b->is_warehouse)
                    <a href="{{ route('admin.warehouse.purchases.index', 'warehouse') }}"
                       class="sub-link {{ $isActive && request()->routeIs('admin.warehouse.purchases.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-truck text-[10px] w-3.5 text-center"></i> Purchases
                    </a>
                    <a href="{{ route('admin.warehouse.suppliers.index', 'warehouse') }}"
                       class="sub-link {{ $isActive && request()->routeIs('admin.warehouse.suppliers.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-industry text-[10px] w-3.5 text-center"></i> Suppliers
                    </a>
                    <a href="{{ route('admin.warehouse.transfer', 'warehouse') }}"
                       class="sub-link {{ request()->routeIs('admin.warehouse.transfer*') ? 'sub-active' : '' }}">
                        <i class="fas fa-right-left text-[10px] w-3.5 text-center"></i> Stock Transfer
                    </a>
                    @endif

                    <a href="{{ $b->is_warehouse ? route('admin.warehouse.stock.index', 'warehouse') : route('branch.stock.index', $b) }}"
                       class="sub-link {{ $isActive && (request()->routeIs('branch.stock.*') || request()->routeIs('admin.warehouse.stock.*')) ? 'sub-active' : '' }}">
                        <i class="fas fa-boxes-stacked text-[10px] w-3.5 text-center"></i> Stock
                    </a>

                    @unless($b->is_warehouse)
                    <a href="{{ route('branch.customers.index', $b) }}"
                       class="sub-link {{ $isActive && request()->routeIs('branch.customers.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-users text-[10px] w-3.5 text-center"></i> Customers
                    </a>
                    <a href="{{ route('branch.finance.index', $b) }}"
                       class="sub-link {{ $isActive && request()->routeIs('branch.finance.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-coins text-[10px] w-3.5 text-center"></i> Finance
                    </a>
                    <a href="{{ route('branch.reports.index', $b) }}"
                       class="sub-link {{ $isActive && request()->routeIs('branch.reports.*') ? 'sub-active' : '' }}">
                        <i class="fas fa-chart-bar text-[10px] w-3.5 text-center"></i> Report
                    </a>
                    @endunless

                    <a href="{{ $b->is_warehouse ? route('admin.warehouse.staff.index', 'warehouse') : route('branch.staff.index', $b) }}"
                       class="sub-link {{ $isActive && (request()->routeIs('branch.staff.*') || request()->routeIs('admin.warehouse.staff.*')) ? 'sub-active' : '' }}">
                        <i class="fas fa-user-gear text-[10px] w-3.5 text-center"></i> Staff
                    </a>
                    <a href="{{ $b->is_warehouse ? route('admin.warehouse.settings.index', 'warehouse') : route('branch.settings.index', $b) }}"
                       class="sub-link {{ $isActive && (request()->routeIs('branch.settings.*') || request()->routeIs('admin.warehouse.settings.*')) ? 'sub-active' : '' }}">
                        <i class="fas fa-sliders text-[10px] w-3.5 text-center"></i> Settings
                    </a>
                </div>
            </div>
            @endforeach

            {{-- Admin tools --}}
            <div class="nav-divider !mt-3"></div>
            <div class="section-label">Cross-Branch Data</div>
            <a href="{{ route('admin.all-customers') }}" class="nav-link {{ request()->routeIs('admin.all-customers*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-users text-[11px]"></i></span> All Customers
            </a>
            <a href="{{ route('admin.all-sales') }}" class="nav-link {{ request()->routeIs('admin.all-sales*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-receipt text-[11px]"></i></span> All Sales
            </a>
            <a href="{{ route('admin.all-products') }}" class="nav-link {{ request()->routeIs('admin.all-products*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-box text-[11px]"></i></span> All Products
            </a>
            <a href="{{ route('admin.all-categories') }}" class="nav-link {{ request()->routeIs('admin.all-categories*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-tags text-[11px]"></i></span> All Categories
            </a>
            <a href="{{ route('admin.all-purchases') }}" class="nav-link {{ request()->routeIs('admin.all-purchases*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-truck text-[11px]"></i></span> All Purchases
            </a>
            <a href="{{ route('admin.all-suppliers') }}" class="nav-link {{ request()->routeIs('admin.all-suppliers*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-industry text-[11px]"></i></span> Purchase Suppliers
            </a>
            <a href="{{ route('admin.stock-report') }}" class="nav-link {{ request()->routeIs('admin.stock-report*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-warehouse text-[11px]"></i></span> Stock Report
            </a>
            <a href="{{ route('admin.sales-report') }}" class="nav-link {{ request()->routeIs('admin.sales-report*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-chart-line text-[11px]"></i></span> Sales Report
            </a>
            <a href="{{ route('admin.financial-report') }}" class="nav-link {{ request()->routeIs('admin.financial-report*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-coins text-[11px]"></i></span> Financial Report
            </a>
            <a href="{{ route('admin.ads-cost.index') }}" class="nav-link {{ request()->routeIs('admin.ads-cost.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-rectangle-ad text-[11px] text-amber-400"></i></span> Ads Cost
            </a>
            <a href="{{ route('admin.fraud-check') }}" class="nav-link {{ request()->routeIs('admin.fraud-check*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-shield-halved text-[11px]"></i></span> Fraud Check
            </a>

            {{-- ────────────── RESELLER HUB ────────────── --}}
            <div class="nav-divider !mt-3"></div>
            <div class="flex items-center justify-between px-3 pt-2 pb-1">
                <div class="flex items-center gap-1.5 text-[10.5px] font-extrabold uppercase tracking-wider text-purple-400">
                    <span class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.8)]"></span>
                    <span>Reseller Hub</span>
                </div>
                <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    Reseller
                </span>
            </div>

            @php
                $pendingResellerCount = \App\Models\Reseller::where('status', 'pending')->count();
                $pendingResellerOrdersCount = \App\Models\Sale::withoutGlobalScopes()
                    ->where('channel', 'reseller')
                    ->where('order_status', 'pending')
                    ->count();
                $pendingResellerWithdrawalsCount = \App\Models\ResellerWithdrawal::where('status', 'pending')->count();
            @endphp

            <a href="{{ route('admin.resellers.index') }}"
               class="nav-link border-l-2 border-purple-500/30 hover:border-purple-400 {{ request()->routeIs('admin.resellers.index') ? 'is-active !border-purple-400' : '' }}">
                <span class="icon"><i class="fas fa-handshake text-[11px] text-purple-400"></i></span>
                <span class="truncate">All Resellers</span>
                @if($pendingResellerCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] rounded-full bg-purple-500 text-white font-black animate-pulse" title="Pending Resellers">{{ $pendingResellerCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.resellers.orders') }}"
               class="nav-link border-l-2 border-purple-500/30 hover:border-purple-400 {{ request()->routeIs('admin.resellers.orders*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-cart-flatbed text-[11px] text-violet-400"></i></span>
                <span class="truncate">Reseller Orders</span>
                @if($pendingResellerOrdersCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] rounded-full bg-blue-500 text-white font-black" title="Pending Orders">{{ $pendingResellerOrdersCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.resellers.withdrawals') }}"
               class="nav-link border-l-2 border-purple-500/30 hover:border-purple-400 {{ request()->routeIs('admin.resellers.withdrawals*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-wallet text-[11px] text-fuchsia-400"></i></span>
                <span class="truncate">Reseller Withdrawals</span>
                @if($pendingResellerWithdrawalsCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] rounded-full bg-amber-400 text-amber-950 font-black animate-pulse" title="Pending Withdrawals">{{ $pendingResellerWithdrawalsCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.resellers.report') }}"
               class="nav-link border-l-2 border-purple-500/30 hover:border-purple-400 {{ request()->routeIs('admin.resellers.report*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-chart-pie text-[11px] text-indigo-400"></i></span>
                <span class="truncate">Reseller Reports</span>
            </a>

            {{-- ────────────── SUPPLIER MARKETPLACE ────────────── --}}
            <div class="nav-divider !mt-3"></div>
            <div class="flex items-center justify-between px-3 pt-2 pb-1">
                <div class="flex items-center gap-1.5 text-[10.5px] font-extrabold uppercase tracking-wider text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    <span>Supplier Hub</span>
                </div>
                <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    Supplier
                </span>
            </div>

            @php
                $pendingSupplierCount = \App\Models\Supplier::where('status', 'pending')->count();
                $pendingSupplierSalesCount = \App\Models\Sale::withoutGlobalScopes()
                    ->where(function($q) {
                        $q->whereNotNull('supplier_id')
                          ->orWhereHas('saleItems', fn($sq) => $sq->whereNotNull('supplier_id'));
                    })
                    ->where('order_status', 'pending')
                    ->count();
                $pendingSupplierProductsCount = \App\Models\Product::withoutGlobalScopes()
                    ->whereNotNull('supplier_id')
                    ->where('approval_status', 'pending')
                    ->count();
                $pendingSupplierWithdrawalsCount = \App\Models\SupplierWithdrawal::where('status', 'pending')->count();
            @endphp

            <a href="{{ route('admin.suppliers.manage') }}" class="nav-link {{ request()->routeIs('admin.suppliers.manage*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-store text-[11px]"></i></span> Suppliers / Vendors
                @if($pendingSupplierCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] rounded-full bg-amber-400 text-amber-950 font-black animate-pulse">{{ $pendingSupplierCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.suppliers.withdrawals') }}" class="nav-link {{ request()->routeIs('admin.suppliers.withdrawals*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-wallet text-[11px] text-emerald-400"></i></span> Supplier Withdrawals
                @if($pendingSupplierWithdrawalsCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] rounded-full bg-amber-400 text-amber-950 font-black animate-pulse">{{ $pendingSupplierWithdrawalsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.supplier-sales.index') }}" class="nav-link {{ request()->routeIs('admin.supplier-sales.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-boxes-packing text-[11px] text-amber-400"></i></span> Supplier Sales
                @if($pendingSupplierSalesCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-[10px] rounded-full bg-blue-500 text-white font-black">{{ $pendingSupplierSalesCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.messages.index') }}" class="nav-link {{ request()->routeIs('admin.messages.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-comments text-[11px]"></i></span> Live Chat / Messages
            </a>
            <div class="nav-divider !mt-2"></div>
            <div class="section-label">Admin</div>
            <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-users text-[11px]"></i></span> All Staff
            </a>
            <a href="{{ route('admin.moderators.index') }}" class="nav-link {{ request()->routeIs('admin.moderators.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-user-shield text-[11px]"></i></span> Moderators &amp; Work
            </a>
            <a href="{{ route('admin.roles-permissions') }}" class="nav-link {{ request()->routeIs('admin.roles-permissions*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-shield-halved text-[11px]"></i></span> Roles &amp; Permissions
            </a>
            <a href="{{ route('admin.order-statuses.index') }}" class="nav-link {{ request()->routeIs('admin.order-statuses*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-truck-fast text-[11px]"></i></span> Order Statuses
            </a>
            <a href="{{ route('admin.payment-methods.index') }}" class="nav-link {{ request()->routeIs('admin.payment-methods*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-money-bill-wave text-[11px]"></i></span> Payment Methods
            </a>
            <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-image text-[11px]"></i></span> Homepage Banners
            </a>
            <a href="{{ route('admin.home-contents.index') }}" class="nav-link {{ request()->routeIs('admin.home-contents.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-file-lines text-[11px] text-teal-400"></i></span> Homepage Content
            </a>
            <a href="{{ route('admin.flash-sales.index') }}" class="nav-link {{ request()->routeIs('admin.flash-sales.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-bolt text-[11px] text-amber-500"></i></span> Flash Sale
            </a>
            <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-ticket-alt text-[11px]"></i></span> Coupons
            </a>
            <a href="{{ route('admin.shipping-charges.index') }}" class="nav-link {{ request()->routeIs('admin.shipping-charges*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-truck text-[11px]"></i></span> Shipping Charges
            </a>
            <a href="{{ route('admin.couriers.index') }}" class="nav-link {{ request()->routeIs('admin.couriers*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-truck-fast text-[11px]"></i></span> Courier Settings
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-gear text-[11px]"></i></span> System Settings
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-clock-rotate-left text-[11px]"></i></span> Activity Logs
            </a>
            <a href="{{ route('admin.user-activity.index') }}" class="nav-link {{ request()->routeIs('admin.user-activity.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-chart-line text-[11px] text-cyan-400"></i></span> User Activity
            </a>

        @else
            {{-- Branch staff: permission-gated menu --}}
            @isset($branch)
            <div class="px-3 pb-2 pt-0.5">
                <div class="text-white text-[12.5px] font-semibold truncate">{{ $branch->name }}</div>
                <div class="text-slate-500 text-[11px] mt-0.5">Branch Menu</div>
            </div>
            <div class="nav-divider"></div>

            @if($authUser->can('view_dashboard'))
            <a href="{{ $branch->is_warehouse ? route('admin.warehouse.dashboard') : route('branch.dashboard', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.dashboard') || request()->routeIs('admin.warehouse.dashboard') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-gauge-high text-[11px]"></i></span> Dashboard
            </a>
            @endif

            @if($authUser->can('create_sales') && !$branch->is_warehouse)
            <a href="{{ route('branch.pos.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.pos.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-cash-register text-[11px]"></i></span> POS Terminal
            </a>
            @endif

            @if($authUser->can('view_sales') && !$branch->is_warehouse)
            <a href="{{ route('branch.sales.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.sales.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-receipt text-[11px]"></i></span> Sales
            </a>
            @endif

            @if($authUser->can('view_reports') && !$branch->is_warehouse)
            <a href="{{ route('branch.reports.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.reports.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-chart-bar text-[11px]"></i></span> Report
            </a>
            @endif

            {{-- Website Manager — only shown on the branch flagged as the online store --}}
            @if($branch->is_online_store)
            <div class="nav-divider !mt-3"></div>
            <div class="section-label">Website Manager</div>

            @if($authUser->can('view_sales'))
            <a href="{{ route('branch.sales.index', $branch) }}?channel=web"
               class="nav-link {{ request()->routeIs('branch.sales.*') && request('channel') === 'web' ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-globe text-[11px]"></i></span> Website Orders
            </a>
            @endif

            <a href="{{ route('root') }}" target="_blank" class="nav-link">
                <span class="icon"><i class="fas fa-arrow-up-right-from-square text-[11px]"></i></span> View Storefront
            </a>
            @endif

            {{-- Management section — only show if at least one item is accessible --}}
            @php
                $hasManagement = $authUser->canAny([
                    'manage_products','manage_categories','manage_suppliers',
                    'manage_inventory','manage_customers','manage_payments','view_reports','manage_users'
                ]);
            @endphp
            @if($hasManagement)
            <div class="nav-divider !mt-3"></div>
            <div class="section-label">Management</div>
            @endif

            @if($authUser->can('manage_products'))
            <a href="{{ $branch->is_warehouse ? route('admin.warehouse.products.index') : route('branch.products.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.products.*') || request()->routeIs('admin.warehouse.products.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-box text-[11px]"></i></span> Products
            </a>
            @endif

            @if($authUser->can('manage_categories') && $branch->is_warehouse)
            <a href="{{ route('admin.warehouse.categories.index') }}"
               class="nav-link {{ request()->routeIs('admin.warehouse.categories.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-tags text-[11px]"></i></span> Categories
            </a>
            @endif

            @if($authUser->can('manage_suppliers') && $branch->is_warehouse)
            <a href="{{ route('admin.warehouse.purchases.index') }}"
               class="nav-link {{ request()->routeIs('admin.warehouse.purchases.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-truck text-[11px]"></i></span> Purchases
            </a>
            <a href="{{ route('admin.warehouse.suppliers.index') }}"
               class="nav-link {{ request()->routeIs('admin.warehouse.suppliers.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-industry text-[11px]"></i></span> Suppliers
            </a>
            <a href="{{ route('admin.warehouse.transfer', 'warehouse') }}"
               class="nav-link {{ request()->routeIs('admin.warehouse.transfer*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-right-left text-[11px]"></i></span> Stock Transfer
            </a>
            @endif

            @if($authUser->can('manage_inventory'))
            <a href="{{ $branch->is_warehouse ? route('admin.warehouse.stock.index') : route('branch.stock.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.stock.*') || request()->routeIs('admin.warehouse.stock.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-boxes-stacked text-[11px]"></i></span> Stock
            </a>
            @endif

            @if($authUser->can('manage_customers') && !$branch->is_warehouse)
            <a href="{{ route('branch.customers.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.customers.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-users text-[11px]"></i></span> Customers
            </a>
            @endif

            @if($authUser->can('manage_payments') && !$branch->is_warehouse)
            <a href="{{ route('branch.finance.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.finance.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-coins text-[11px]"></i></span> Finance
            </a>
            @endif

            @if($authUser->can('view_reports'))
            <a href="{{ route('branch.fraud-check.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.fraud-check.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-shield-halved text-[11px]"></i></span> Fraud Check
            </a>
            @endif

            @if($authUser->can('manage_users'))
            <a href="{{ $branch->is_warehouse ? route('admin.warehouse.staff.index') : route('branch.staff.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.staff.*') || request()->routeIs('admin.warehouse.staff.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-user-gear text-[11px]"></i></span> Staff
            </a>
            @endif

            {{-- Settings always visible (profile/password always accessible) --}}
            <a href="{{ $branch->is_warehouse ? route('admin.warehouse.settings.index') : route('branch.settings.index', $branch) }}"
               class="nav-link {{ request()->routeIs('branch.settings.*') || request()->routeIs('admin.warehouse.settings.*') ? 'is-active' : '' }}">
                <span class="icon"><i class="fas fa-sliders text-[11px]"></i></span> Settings
            </a>
            @endisset
        @endif
        @endauth
    </nav>

    {{-- User footer --}}
    @auth
    <div class="border-t border-white/[.07] px-4 py-3.5 flex-shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="user-avatar flex items-center justify-center overflow-hidden">
                @if(auth()->user()->image)
                <img src="{{ Storage::disk('uploads')->url(auth()->user()->image) }}" class="w-full h-full object-cover" alt="avatar">
                @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-[12px] font-semibold truncate leading-tight">{{ auth()->user()->name }}</div>
                <div class="text-slate-500 text-[11px] mt-0.5 truncate">
                    {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" title="Logout"
                        class="text-slate-500 hover:text-red-400 transition-colors p-2 rounded-lg hover:bg-red-400/10">
                    <i class="fas fa-right-from-bracket text-[11px]"></i>
                </button>
            </form>
        </div>
    </div>
    @endauth

</aside>
{{-- ────────────── END SIDEBAR ────────────── --}}

{{-- ── Sidebar Scroll Position & Active Item View Manager ── --}}
<script>
(function() {
    function getStoredScroll() {
        try { return sessionStorage.getItem('admin_sidebar_scroll'); } catch(e) { return null; }
    }
    function setStoredScroll(val) {
        try { sessionStorage.setItem('admin_sidebar_scroll', val); } catch(e) {}
    }

    function syncSidebarScroll(smooth) {
        var nav = document.getElementById('adminSidebarNav') || document.querySelector('aside.sidebar nav');
        if (!nav) return;

        // 1. Restore previous scroll position from sessionStorage
        var savedScroll = getStoredScroll();
        if (savedScroll !== null) {
            nav.scrollTop = parseInt(savedScroll, 10);
        }

        // 2. Identify active item in the sidebar
        var activeItem = nav.querySelector('.nav-link.is-active, .sub-link.sub-active, a.is-active, a.sub-active');
        if (activeItem) {
            var navRect = nav.getBoundingClientRect();
            var itemRect = activeItem.getBoundingClientRect();

            if (navRect.height > 0 && itemRect.height > 0) {
                // Check if active item is comfortably visible in the viewport of nav
                var isVisible = (
                    itemRect.top >= navRect.top + 24 &&
                    itemRect.bottom <= navRect.bottom - 24
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

    // Run immediately so sidebar renders scrolled before first paint (no jarring jump)
    syncSidebarScroll(false);

    // Re-check after DOM is ready, Alpine is initialized, and window is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { syncSidebarScroll(false); });
    }
    document.addEventListener('alpine:initialized', function() {
        setTimeout(function() { syncSidebarScroll(false); }, 60);
    });
    window.addEventListener('load', function() { syncSidebarScroll(false); });
    window.addEventListener('sidebar-opened', function() { syncSidebarScroll(false); });
    window.addEventListener('resize', function() { syncSidebarScroll(false); });

    // Save scroll position when user scrolls or clicks any link inside the sidebar
    function bindSidebarScrollHandlers() {
        var nav = document.getElementById('adminSidebarNav') || document.querySelector('aside.sidebar nav');
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
        document.addEventListener('DOMContentLoaded', bindSidebarScrollHandlers);
    } else {
        bindSidebarScrollHandlers();
    }
})();
</script>

{{-- ────────────── MAIN ────────────── --}}
<div class="lg:ml-[260px] min-h-screen flex flex-col min-w-0">

    {{-- Top Header --}}
    @php
        $currency  = $appSettings['currency'] ?? '৳';
        $authUser  = auth()->user();
        $userRole  = $authUser?->getRoleNames()->first() ?? 'user';
        $warehouseId = \App\Models\Vendor::warehouse()?->id;
        $isSuperAdmin = $authUser?->hasRole('super-admin');

        // Super-admin's "Settings" always means their own account — branch settings for
        // every branch are already one click away on the left sidebar, so this dropdown
        // shouldn't duplicate that (and would otherwise jump around per branch being viewed).
        $settingsRoute = $isSuperAdmin
            ? route('admin.settings.index')
            : ((isset($branch) && $branch)
                ? ($branch->is_warehouse ? route('admin.warehouse.settings.index') : route('branch.settings.index', $branch))
                : '#');

        // Notification data — branch-scoped for staff, global for super-admin
        $notifData = ['pendingSales' => collect(), 'lowStock' => collect(), 'supplierDues' => collect(),
                      'pendingCount' => 0, 'lowStockCount' => 0, 'supplierDueCount' => 0,
                      'isSuperAdmin' => false];
        $notifData['isSuperAdmin'] = $isSuperAdmin;

        $salesQuery   = \App\Models\Sale::withoutGlobalScopes()->where('payment_status','!=','paid');
        $productQuery = \App\Models\Product::withoutGlobalScopes()->where('stock_qty','<=',10);
        $purchQuery   = \App\Models\Purchase::withoutGlobalScopes()->where('payment_status','!=','paid');

        if (isset($branch) && $branch) {
            $salesQuery->where('vendor_id', $branch->id);
            $productQuery->where('vendor_id', $branch->id);
            $purchQuery->where('vendor_id', $branch->id);
        }

        if (isset($branch) || $isSuperAdmin) {
            $notifData['pendingSales'] = (clone $salesQuery)
                ->with('customer', 'vendor:id,name')->latest()->limit(5)->get();
            $notifData['pendingCount'] = (clone $salesQuery)->count();

            $notifData['lowStock'] = (clone $productQuery)
                ->with('vendor:id,name')
                ->orderBy('stock_qty')->limit(5)
                ->get(['id','name','stock_qty','unit','vendor_id']);
            $notifData['lowStockCount'] = (clone $productQuery)->count();

            $notifData['supplierDues'] = (clone $purchQuery)
                ->with('supplier:id,name,phone', 'vendor:id,name')
                ->select('vendor_id', 'supplier_id',
                    \Illuminate\Support\Facades\DB::raw('SUM(due_amount) as total_due'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as invoice_count'))
                ->groupBy('vendor_id','supplier_id')
                ->orderByDesc('total_due')
                ->limit(5)
                ->get();
            $notifData['supplierDueCount'] = (clone $purchQuery)
                ->distinct('supplier_id')->count('supplier_id');
        }
        $totalNotif = $notifData['pendingCount'] + $notifData['lowStockCount'] + $notifData['supplierDueCount'];
    @endphp

    <header class="bg-white border-b border-slate-200 px-4 py-0 flex items-center justify-between sticky top-0 z-10 flex-shrink-0 h-[54px]"
            x-data="headerApp()">

        {{-- Left: hamburger + heading --}}
        <div class="flex items-center gap-3 min-w-0">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden text-slate-500 hover:text-slate-700 w-8 h-8 rounded-xl hover:bg-slate-100 flex items-center justify-center transition-colors flex-shrink-0">
                <i class="fas fa-bars text-sm"></i>
            </button>
            <div class="min-w-0">
                <h1 class="text-[14px] font-bold text-slate-800 leading-tight truncate">@yield('heading', 'Dashboard')</h1>
                @if(isset($branch) && $branch)
                <p class="text-[10.5px] text-slate-400 leading-none truncate">{{ $branch->name }}</p>
                @endif
            </div>
        </div>

        {{-- Right: tools --}}
        <div class="flex items-center gap-1.5 flex-shrink-0">

            {{-- Live clock --}}
            <div class="hidden md:flex items-center gap-1.5 text-[11.5px] text-slate-400 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl select-none">
                <i class="fas fa-clock text-[10px]"></i>
                <span x-text="clock"></span>
            </div>

            {{-- ══ NOTIFICATION BELL ══ --}}
            @if(isset($branch) || $isSuperAdmin)
            <div class="relative" x-data="{ nOpen: false, tab: 'sales' }" @click.outside="nOpen = false">

                {{-- Bell button --}}
                <button @click="nOpen = !nOpen"
                        class="relative w-9 h-9 rounded-xl flex items-center justify-center transition-colors
                               {{ $totalNotif > 0 ? 'bg-red-50 hover:bg-red-100 text-red-500' : 'bg-slate-50 hover:bg-slate-100 text-slate-500' }}">
                    <i class="fas fa-bell text-sm" :class="nOpen ? 'text-red-500' : ''"></i>
                    @if($totalNotif > 0)
                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-1 leading-none shadow-sm">
                        {{ $totalNotif > 99 ? '99+' : $totalNotif }}
                    </span>
                    @endif
                </button>

                {{-- Dropdown panel --}}
                <div x-show="nOpen" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 top-[calc(100%+8px)] w-[340px] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50">

                    {{-- Header --}}
                    <div class="px-4 pt-3.5 pb-0 border-b border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-bell text-red-500 text-[10px]"></i>
                                </div>
                                <span class="text-[13px] font-bold text-slate-800">Notifications</span>
                            </div>
                            @if($totalNotif > 0)
                            <span class="text-[10px] font-bold bg-red-500 text-white px-2 py-0.5 rounded-full">
                                {{ $totalNotif }} alerts
                            </span>
                            @else
                            <span class="text-[10px] text-slate-400">All clear</span>
                            @endif
                        </div>

                        {{-- Tabs --}}
                        <div class="flex gap-0.5">
                            <button @click="tab = 'sales'"
                                    :class="tab === 'sales' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                                    class="flex items-center gap-1.5 px-3 py-2 text-[11.5px] font-semibold transition-colors">
                                <i class="fas fa-receipt text-[10px]"></i>
                                Due Sales
                                @if($notifData['pendingCount'] > 0)
                                <span class="bg-red-100 text-red-600 text-[9px] font-bold px-1.5 rounded-full">{{ $notifData['pendingCount'] }}</span>
                                @endif
                            </button>
                            <button @click="tab = 'stock'"
                                    :class="tab === 'stock' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                                    class="flex items-center gap-1.5 px-3 py-2 text-[11.5px] font-semibold transition-colors">
                                <i class="fas fa-boxes-stacked text-[10px]"></i>
                                Low Stock
                                @if($notifData['lowStockCount'] > 0)
                                <span class="bg-amber-100 text-amber-600 text-[9px] font-bold px-1.5 rounded-full">{{ $notifData['lowStockCount'] }}</span>
                                @endif
                            </button>
                            <button @click="tab = 'supplier'"
                                    :class="tab === 'supplier' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-400 hover:text-slate-600'"
                                    class="flex items-center gap-1.5 px-3 py-2 text-[11.5px] font-semibold transition-colors">
                                <i class="fas fa-industry text-[10px]"></i>
                                Supplier Due
                                @if($notifData['supplierDueCount'] > 0)
                                <span class="bg-orange-100 text-orange-600 text-[9px] font-bold px-1.5 rounded-full">{{ $notifData['supplierDueCount'] }}</span>
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- TAB: Due Sales --}}
                    <div x-show="tab === 'sales'" class="max-h-64 overflow-y-auto divide-y divide-slate-50">
                        @forelse($notifData['pendingSales'] as $ps)
                        @php
                            $psBc   = $ps->payment_status === 'partial'
                                ? ['bg-amber-100 text-amber-700', 'bg-amber-400']
                                : ['bg-red-100 text-red-600', 'bg-red-500'];
                            $dueAmt = $ps->due_amount ?? ($ps->total - $ps->paid_amount);
                            $psHref = $isSuperAdmin
                                ? route('branch.sales.show', [$ps->vendor_id, $ps])
                                : route('branch.sales.show', [$branch, $ps]);
                        @endphp
                        <a href="{{ $psHref }}" @click="nOpen = false"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                            <div class="w-2 h-2 rounded-full {{ $psBc[1] }} flex-shrink-0 mt-0.5"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-semibold text-slate-700 font-mono leading-tight">{{ $ps->invoice_no }}</p>
                                <p class="text-[10.5px] text-slate-400 truncate">
                                    {{ $ps->customer?->name ?? 'Walk-in' }}
                                    @if($isSuperAdmin && $ps->vendor) · <span class="text-blue-500">{{ $ps->vendor->name }}</span>@endif
                                    · {{ $ps->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-[11.5px] font-bold text-red-500">{{ $currency }}{{ number_format($dueAmt, 0) }}</p>
                                <span class="text-[9.5px] font-semibold {{ $psBc[0] }} px-1.5 py-0.5 rounded-md capitalize">{{ $ps->payment_status }}</span>
                            </div>
                        </a>
                        @empty
                        <div class="px-4 py-10 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-check text-emerald-500 text-sm"></i>
                            </div>
                            <p class="text-[12px] text-slate-400 font-medium">All sales settled!</p>
                        </div>
                        @endforelse
                        @if($notifData['pendingCount'] > 5)
                        <div class="px-4 py-2.5 bg-slate-50/80">
                            @if($isSuperAdmin)
                            <a href="{{ route('admin.all-sales') }}" @click="nOpen = false"
                               class="text-[11.5px] text-blue-600 hover:underline font-medium">
                                +{{ $notifData['pendingCount'] - 5 }} more across all branches →
                            </a>
                            @else
                            <a href="{{ route('branch.sales.index', $branch) }}" @click="nOpen = false"
                               class="text-[11.5px] text-blue-600 hover:underline font-medium">
                                +{{ $notifData['pendingCount'] - 5 }} more pending →
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- TAB: Low Stock --}}
                    <div x-show="tab === 'stock'" class="max-h-64 overflow-y-auto divide-y divide-slate-50">
                        @forelse($notifData['lowStock'] as $item)
                        @php
                            $isOut    = $item->stock_qty <= 0;
                            $itemHref = $isSuperAdmin
                                ? ($item->vendor_id === $warehouseId ? route('admin.warehouse.products.index', 'warehouse') : route('branch.products.index', $item->vendor_id))
                                : ($branch->is_warehouse ? route('admin.warehouse.products.index', 'warehouse') : route('branch.products.index', $branch));
                        @endphp
                        <a href="{{ $itemHref }}" @click="nOpen = false"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                            <div class="w-8 h-8 rounded-xl {{ $isOut ? 'bg-red-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-[10px] {{ $isOut ? 'text-red-500' : 'text-amber-500' }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-semibold text-slate-700 truncate leading-tight">{{ $item->name }}</p>
                                <p class="text-[10.5px] text-slate-400">
                                    {{ $isOut ? 'Out of stock' : $item->stock_qty . ' ' . ($item->unit ?? 'pcs') . ' remaining' }}
                                    @if($isSuperAdmin && $item->vendor)
                                    · <span class="text-blue-500">{{ $item->vendor->name }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg flex-shrink-0
                                         {{ $isOut ? 'bg-red-100 text-red-600 border border-red-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                                {{ $isOut ? 'OUT' : 'LOW' }}
                            </span>
                        </a>
                        @empty
                        <div class="px-4 py-10 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-check text-emerald-500 text-sm"></i>
                            </div>
                            <p class="text-[12px] text-slate-400 font-medium">All products stocked!</p>
                        </div>
                        @endforelse
                        @if($notifData['lowStockCount'] > 5)
                        <div class="px-4 py-2.5 bg-slate-50/80">
                            @if($isSuperAdmin)
                            <a href="{{ route('admin.stock-report') }}" @click="nOpen = false"
                               class="text-[11.5px] text-blue-600 hover:underline font-medium">
                                +{{ $notifData['lowStockCount'] - 5 }} more across all branches →
                            </a>
                            @else
                            <a href="{{ $branch->is_warehouse ? route('admin.warehouse.stock.index') : route('branch.stock.index', $branch) }}" @click="nOpen = false"
                               class="text-[11.5px] text-blue-600 hover:underline font-medium">
                                +{{ $notifData['lowStockCount'] - 5 }} more low-stock items →
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- TAB: Supplier Dues --}}
                    <div x-show="tab === 'supplier'" class="max-h-64 overflow-y-auto divide-y divide-slate-50">
                        @forelse($notifData['supplierDues'] as $sup)
                        @php
                            // Purchases only ever belong to the Warehouse now, regardless of who's viewing.
                            $supHref = route('admin.warehouse.purchases.index', 'warehouse');
                        @endphp
                        <a href="{{ $supHref }}" @click="nOpen = false"
                           class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                            <div class="w-8 h-8 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-industry text-orange-500 text-[10px]"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-semibold text-slate-700 truncate leading-tight">
                                    {{ $sup->supplier?->name ?? 'Unknown Supplier' }}
                                </p>
                                <p class="text-[10.5px] text-slate-400">
                                    {{ $sup->invoice_count }} unpaid{{ $sup->invoice_count > 1 ? '' : '' }}
                                    @if($isSuperAdmin && $sup->vendor) · <span class="text-blue-500">{{ $sup->vendor->name }}</span>@endif
                                    @if(!$isSuperAdmin && $sup->supplier?->phone) · {{ $sup->supplier->phone }}@endif
                                </p>
                            </div>
                            <p class="text-[12px] font-bold text-orange-500 flex-shrink-0">
                                {{ $currency }}{{ number_format($sup->total_due, 0) }}
                            </p>
                        </a>
                        @empty
                        <div class="px-4 py-10 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-check text-emerald-500 text-sm"></i>
                            </div>
                            <p class="text-[12px] text-slate-400 font-medium">No supplier dues!</p>
                        </div>
                        @endforelse
                        @if($notifData['supplierDueCount'] > 5)
                        <div class="px-4 py-2.5 bg-slate-50/80">
                            @if($isSuperAdmin)
                            <a href="{{ route('admin.all-purchases') }}" @click="nOpen = false"
                               class="text-[11.5px] text-blue-600 hover:underline font-medium">
                                +{{ $notifData['supplierDueCount'] - 5 }} more across branches →
                            </a>
                            @else
                            <a href="{{ route('admin.warehouse.purchases.index', 'warehouse') }}" @click="nOpen = false"
                               class="text-[11.5px] text-blue-600 hover:underline font-medium">
                                +{{ $notifData['supplierDueCount'] - 5 }} more suppliers with dues →
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    @if($totalNotif === 0)
                    <div class="px-4 py-3 border-t border-slate-100 bg-emerald-50/50 flex items-center gap-2">
                        <i class="fas fa-shield-check text-emerald-500 text-xs"></i>
                        <span class="text-[11.5px] text-emerald-600 font-medium">Everything looks great!</span>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- Profile --}}
            <div class="relative" x-data="{ pOpen: false }">
                <button @click="pOpen = !pOpen" @click.outside="pOpen = false"
                        class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-slate-100 transition-colors group">
                    <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0 shadow-sm overflow-hidden">
                        @if(auth()->user()->image)
                        <img src="{{ Storage::disk('uploads')->url(auth()->user()->image) }}" class="w-full h-full object-cover" alt="avatar">
                        @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        @endif
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-[12px] font-semibold text-slate-700 leading-tight">{{ Str::limit(auth()->user()->name ?? '', 14) }}</p>
                        <p class="text-[10px] text-slate-400 capitalize leading-none">{{ str_replace('-',' ', $userRole) }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-[9px] text-slate-400 hidden sm:block transition-transform group-hover:text-slate-600" :class="pOpen ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="pOpen" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 top-[calc(100%+6px)] w-56 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50">

                    {{-- User card --}}
                    <div class="px-4 py-3.5 bg-gradient-to-br from-blue-600 to-violet-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-sm font-bold flex-shrink-0 overflow-hidden">
                                @if(auth()->user()->image)
                                <img src="{{ Storage::disk('uploads')->url(auth()->user()->image) }}" class="w-full h-full object-cover" alt="avatar">
                                @else
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-white text-[13px] font-bold truncate">{{ auth()->user()->name }}</p>
                                <p class="text-blue-200 text-[10.5px] capitalize truncate">{{ str_replace('-',' ',$userRole) }}</p>
                                @if(isset($branch) && $branch)
                                <p class="text-blue-300 text-[10px] truncate mt-0.5">{{ $branch->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Links --}}
                    <div class="py-1.5">
                        @if($settingsRoute !== '#')
                        <a href="{{ $settingsRoute }}" @click="pOpen = false"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-[12.5px] text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <i class="fas fa-sliders text-slate-400 text-xs w-4 text-center"></i> Settings
                        </a>
                        @endif
                        @if(isset($branch) && $branch && $authUser->can('manage_users') && !$isSuperAdmin)
                        <a href="{{ $branch->is_warehouse ? route('admin.warehouse.staff.index') : route('branch.staff.index', $branch) }}" @click="pOpen = false"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-[12.5px] text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            <i class="fas fa-user-gear text-slate-400 text-xs w-4 text-center"></i> Staff
                        </a>
                        @endif
                        <div class="h-px bg-slate-100 my-1 mx-3"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-[12.5px] text-red-600 hover:bg-red-50 transition-colors text-left">
                                <i class="fas fa-right-from-bracket text-xs w-4 text-center"></i> Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </header>

    {{-- Flash messages → handled by toast system below --}}

    {{-- Page Content --}}
    <main class="flex-1 p-3 sm:p-4 lg:p-6 min-w-0">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white/70 border-t border-slate-200 text-center text-[11px] text-slate-400 py-3 flex-shrink-0">
        {{ $appSettings['footer_text'] ?? 'POS System' }} &mdash; &copy; {{ date('Y') }} {{ $appSettings['name'] ?? '' }}
    </footer>

</div>
{{-- ────────────── END MAIN ────────────── --}}

@stack('scripts')

{{-- ════ TOAST NOTIFICATION SYSTEM ════ --}}
<div x-data
     class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 w-80 max-w-[calc(100vw-2rem)] pointer-events-none">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl shadow-xl border text-sm font-medium"
             :class="{
                'bg-emerald-50  border-emerald-200 text-emerald-800': toast.type === 'success',
                'bg-red-50      border-red-200     text-red-800':     toast.type === 'error',
                'bg-amber-50    border-amber-200   text-amber-800':   toast.type === 'warning',
                'bg-blue-50     border-blue-200    text-blue-800':    toast.type === 'info',
             }"
             x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-10 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-10 scale-95">
            <i class="mt-0.5 flex-shrink-0 text-sm"
               :class="{
                'fas fa-circle-check    text-emerald-500': toast.type === 'success',
                'fas fa-circle-xmark    text-red-500':     toast.type === 'error',
                'fas fa-triangle-exclamation text-amber-500': toast.type === 'warning',
                'fas fa-circle-info     text-blue-500':    toast.type === 'info',
               }"></i>
            <span class="flex-1 leading-snug" x-text="toast.message"></span>
            <button @click="$store.toast.dismiss(toast.id)"
                    class="flex-shrink-0 opacity-50 hover:opacity-100 transition-opacity ml-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    </template>
</div>

<script>
/* ── Header Alpine components ────────────────────────────── */
function headerApp() {
    return {
        clock: '',
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },
        tick() {
            const now = new Date();
            const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            const d = days[now.getDay()];
            const date = String(now.getDate()).padStart(2,'0');
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const m = months[now.getMonth()];
            const h = String(now.getHours()).padStart(2,'0');
            const min = String(now.getMinutes()).padStart(2,'0');
            this.clock = `${d} ${date} ${m} · ${h}:${min}`;
        }
    };
}

/* ── Toast Store ─────────────────────────────────────────── */
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

/* ── Global helper callable from anywhere ────────────────── */
function showToast(message, type = 'success') {
    if (window.Alpine) Alpine.store('toast').add(message, type);
}

/* ── Fire session flashes as toasts ─────────────────────── */
document.addEventListener('alpine:initialized', () => {
    @if(session('success'))   showToast(@json(session('success')), 'success'); @endif
    @if(session('error'))     showToast(@json(session('error')),   'error');   @endif
    @if(session('warning'))   showToast(@json(session('warning')), 'warning'); @endif
    @if($errors->any())
        @foreach($errors->all() as $e) showToast(@json($e), 'error'); @endforeach
    @endif
});
</script>
</body>
</html>
