<!DOCTYPE html>
<html lang="bn" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Moderator Dashboard') - {{ config('app.name', 'Fayaz') }}</title>

    {{-- Tailwind CSS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800 bg-slate-50">

    {{-- Top Navbar --}}
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                {{-- Left: Brand & Portal Badge --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('moderator.dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-200 group-hover:scale-105 transition-transform">
                            <i class="fas fa-user-shield text-base"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-lg text-slate-900 tracking-tight leading-none block">Moderator Panel</span>
                            <span class="text-[11px] font-semibold text-indigo-600">Work & Shift Portal</span>
                        </div>
                    </a>

                    {{-- Navigation links --}}
                    <nav class="hidden md:flex items-center gap-1 ml-6 pl-6 border-l border-slate-200">
                        <a href="{{ route('moderator.dashboard') }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('moderator.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            <i class="fas fa-tachometer-alt mr-1.5 text-[11px]"></i> Dashboard
                        </a>
                        <a href="{{ route('moderator.account') }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('moderator.account*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            <i class="fas fa-wallet mr-1.5 text-[11px] text-emerald-600"></i> My Account
                        </a>
                        <a href="{{ route('moderator.reports') }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('moderator.reports') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            <i class="fas fa-clipboard-list mr-1.5 text-[11px]"></i> Work History & Reports
                        </a>
                        <a href="{{ route('moderator.profile') }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('moderator.profile') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100' }}">
                            <i class="fas fa-user-circle mr-1.5 text-[11px]"></i> Profile
                        </a>
                    </nav>
                </div>

                {{-- Right: Status & Profile --}}
                <div class="flex items-center gap-3">
                    {{-- Wallet Balance Quick Badge --}}
                    <a href="{{ route('moderator.account') }}"
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition-colors shadow-2xs" title="My Account & Withdraw Balance">
                        <i class="fas fa-coins text-emerald-600 text-[10px]"></i>
                        <span>৳{{ number_format(auth('moderator')->user()?->availableBalance() ?? 0, 2) }}</span>
                    </a>

                    {{-- Real-time working indicator badge --}}
                    @if(auth('moderator')->user()?->isWorkingNow())
                        <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold shadow-2xs">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="hidden sm:inline">On Duty</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-500 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span class="hidden sm:inline">Off Duty</span>
                        </div>
                    @endif

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                            @if(auth('moderator')->user()?->image_url)
                                <img src="{{ auth('moderator')->user()->image_url }}" alt="{{ auth('moderator')->user()->name }}"
                                     class="w-8 h-8 rounded-lg object-cover border border-slate-200 shadow-2xs">
                            @elseif(!empty($appSettings['favicon']))
                                <img src="{{ $appSettings['favicon'] }}" alt="favicon"
                                     class="w-8 h-8 rounded-lg object-cover border border-slate-200 shadow-2xs">
                            @else
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(auth('moderator')->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-xs font-bold text-slate-700 hidden sm:block">{{ auth('moderator')->user()->name }}</span>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50">
                            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center gap-2.5">
                                @if(auth('moderator')->user()?->image_url)
                                    <img src="{{ auth('moderator')->user()->image_url }}" alt="{{ auth('moderator')->user()->name }}"
                                         class="w-9 h-9 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                                @elseif(!empty($appSettings['favicon']))
                                    <img src="{{ $appSettings['favicon'] }}" alt="favicon"
                                         class="w-9 h-9 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr(auth('moderator')->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth('moderator')->user()->name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate font-mono">{{ auth('moderator')->user()->email }}</p>
                                </div>
                            </div>
                            <a href="{{ route('moderator.account') }}" class="flex items-center px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-semibold">
                                <i class="fas fa-wallet mr-2.5 text-emerald-500 w-4"></i> My Account & Withdrawals
                            </a>
                            <a href="{{ route('moderator.profile') }}" class="flex items-center px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-semibold">
                                <i class="fas fa-user-cog mr-2.5 text-indigo-500 w-4"></i> Profile Settings
                            </a>
                            <a href="{{ route('moderator.reports') }}" class="flex items-center px-4 py-2 text-xs text-slate-600 hover:bg-slate-50 font-semibold">
                                <i class="fas fa-history mr-2.5 text-slate-400 w-4"></i> Work History
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('moderator.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-semibold cursor-pointer">
                                    <i class="fas fa-sign-out-alt mr-2.5 text-red-400 w-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    {{-- Page Alerts --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-2xs">
                <i class="fas fa-check-circle text-emerald-500 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-2xs">
                <i class="fas fa-exclamation-circle text-red-500 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200/80 py-4 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Fayaz') }} - Moderator Work Tracking & Management</p>
    </footer>

    @stack('scripts')
</body>
</html>
