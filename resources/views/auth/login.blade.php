<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoTitle = $appSettings['seo_title'] ?? $appSettings['name'] ?? 'POS System';
        $seoDescription = $appSettings['seo_description'] ?? $appSettings['tagline'] ?? '';
        $seoImage = $appSettings['og_image'] ?? $appSettings['logo'] ?? null;
    @endphp
    <title>Sign In — {{ $seoTitle }}</title>
    @if(!empty($appSettings['favicon']))
    <link rel="icon" href="{{ $appSettings['favicon'] }}">
    @endif
    @if($seoDescription)
    <meta name="description" content="{{ $seoDescription }}">
    @endif
    <meta property="og:title" content="Sign In — {{ $seoTitle }}">
    @if($seoDescription)
    <meta property="og:description" content="{{ $seoDescription }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .bg-pattern {
            background-image: radial-gradient(circle at 20% 50%, rgba(99,102,241,0.15) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(139,92,246,0.15) 0%, transparent 50%),
                              radial-gradient(circle at 50% 80%, rgba(59,130,246,0.1) 0%, transparent 50%);
        }
        .input-field {
            transition: all 0.2s ease;
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.4);
        }
        .btn-primary:active { transform: translateY(0); }
        .card-shadow { box-shadow: 0 25px 60px rgba(0,0,0,0.12), 0 8px 20px rgba(0,0,0,0.08); }
        .floating-card {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        /* Background */
        body {
            background-color: #eef2ff;
            background-image:
                linear-gradient(135deg, #e0e7ff 0%, #ede9fe 40%, #dbeafe 100%);
        }
        .bg-grid {
            background-image:
                linear-gradient(rgba(99,102,241,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.06) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .bg-icon {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0.07;
            color: #4f46e5;
            pointer-events: none;
            user-select: none;
        }
        @keyframes drift {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33%       { transform: translateY(-12px) rotate(3deg); }
            66%       { transform: translateY(6px) rotate(-2deg); }
        }
        .drift-1 { animation: drift 8s ease-in-out infinite; }
        .drift-2 { animation: drift 11s ease-in-out infinite 2s; }
        .drift-3 { animation: drift 9s ease-in-out infinite 1s; }
        .drift-4 { animation: drift 13s ease-in-out infinite 3s; }
        .drift-5 { animation: drift 7s ease-in-out infinite 0.5s; }
        .drift-6 { animation: drift 10s ease-in-out infinite 1.5s; }
        .drift-7 { animation: drift 12s ease-in-out infinite 4s; }
        .drift-8 { animation: drift 9s ease-in-out infinite 2.5s; }
        .drift-9 { animation: drift 14s ease-in-out infinite 0.8s; }
        .drift-10 { animation: drift 8s ease-in-out infinite 3.5s; }
        .drift-11 { animation: drift 11s ease-in-out infinite 1.2s; }
        .drift-12 { animation: drift 10s ease-in-out infinite 4.5s; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 min-h-screen relative overflow-hidden">

{{-- Grid overlay --}}
<div class="bg-grid absolute inset-0 pointer-events-none"></div>

{{-- Floating POS / Accounting icons scattered in background --}}
<i class="bg-icon fas fa-cash-register drift-1"  style="top:8%;  left:5%;  font-size:2.2rem;"></i>
<i class="bg-icon fas fa-receipt       drift-2"  style="top:15%; left:18%; font-size:1.8rem;"></i>
<i class="bg-icon fas fa-barcode       drift-3"  style="top:6%;  left:38%; font-size:2rem;"></i>
<i class="bg-icon fas fa-calculator    drift-4"  style="top:20%; left:58%; font-size:1.6rem;"></i>
<i class="bg-icon fas fa-chart-bar     drift-5"  style="top:10%; left:75%; font-size:2rem;"></i>
<i class="bg-icon fas fa-chart-line    drift-6"  style="top:5%;  left:90%; font-size:1.8rem;"></i>
<i class="bg-icon fas fa-boxes-stacked drift-7"  style="top:35%; left:3%;  font-size:2rem;"></i>
<i class="bg-icon fas fa-tags          drift-8"  style="top:45%; left:92%; font-size:1.8rem;"></i>
<i class="bg-icon fas fa-file-invoice  drift-9"  style="top:65%; left:8%;  font-size:2rem;"></i>
<i class="bg-icon fas fa-coins         drift-10" style="top:72%; left:22%; font-size:1.6rem;"></i>
<i class="bg-icon fas fa-store         drift-11" style="top:80%; left:62%; font-size:2.2rem;"></i>
<i class="bg-icon fas fa-truck         drift-12" style="top:75%; left:85%; font-size:2rem;"></i>
<i class="bg-icon fas fa-credit-card   drift-3"  style="top:55%; left:48%; font-size:1.6rem;"></i>
<i class="bg-icon fas fa-chart-pie     drift-5"  style="top:88%; left:40%; font-size:1.8rem;"></i>
<i class="bg-icon fas fa-qrcode        drift-2"  style="top:30%; left:30%; font-size:1.5rem;"></i>
<i class="bg-icon fas fa-wallet        drift-7"  style="top:60%; left:72%; font-size:1.7rem;"></i>

<div class="w-full max-w-5xl mx-auto relative z-10">
    <div class="grid lg:grid-cols-2 gap-0 card-shadow rounded-3xl overflow-hidden">

        {{-- ── LEFT PANEL ── --}}
        <div class="hidden lg:flex flex-col bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-12 relative overflow-hidden">

            {{-- Background decorations --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <div class="absolute top-1/2 right-8 w-32 h-32 bg-white/5 rounded-2xl rotate-12"></div>

            {{-- Logo --}}
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-16">
                    @if(!empty($appSettings['logo']))
                    <img src="{{ $appSettings['logo'] }}" alt="{{ $appSettings['name'] ?? 'Logo' }}" class="h-10 max-w-[200px] object-contain">
                    @else
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-store text-white text-lg"></i>
                    </div>
                    <span class="text-white font-bold text-xl">{{ $appSettings['name'] ?? 'POS System' }}</span>
                    @endif
                </div>
            </div>

            {{-- Main content --}}
            <div class="relative z-10 flex-1 flex flex-col justify-center">

                {{-- Floating stats cards --}}
                <div class="floating-card mb-8">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-5 mb-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-400/20 flex items-center justify-center">
                                <i class="fas fa-chart-line text-emerald-300 text-sm"></i>
                            </div>
                            <span class="text-white/80 text-sm font-medium">Today's Sales</span>
                        </div>
                        <p class="text-white text-2xl font-bold">৳ 84,250</p>
                        <p class="text-emerald-300 text-xs mt-1"><i class="fas fa-arrow-up mr-1"></i>12% from yesterday</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-4">
                            <i class="fas fa-receipt text-violet-300 text-sm mb-2 block"></i>
                            <p class="text-white font-bold text-lg">142</p>
                            <p class="text-white/60 text-xs">Invoices</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-4">
                            <i class="fas fa-store text-blue-300 text-sm mb-2 block"></i>
                            <p class="text-white font-bold text-lg">5</p>
                            <p class="text-white/60 text-xs">Branches</p>
                        </div>
                    </div>
                </div>

                <h2 class="text-white text-2xl font-bold leading-snug mb-3">
                    Manage your business<br>smarter & faster
                </h2>
                <p class="text-white/60 text-sm leading-relaxed">
                    Multi-vendor POS with real-time inventory, sales reports, and role-based access for your entire team.
                </p>
            </div>

            {{-- Bottom dots --}}
            <div class="relative z-10 flex gap-2 mt-8">
                <div class="w-6 h-1.5 bg-white rounded-full"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
                <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
            </div>
        </div>

        {{-- ── RIGHT PANEL (Form) ── --}}
        <div class="bg-white p-8 lg:p-12 flex flex-col justify-center">

            {{-- Mobile logo --}}
            <div class="flex lg:hidden items-center gap-3 mb-8">
                @if(!empty($appSettings['logo']))
                <img src="{{ $appSettings['logo'] }}" alt="{{ $appSettings['name'] ?? 'Logo' }}" class="h-9 max-w-[180px] object-contain">
                @else
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center">
                    <i class="fas fa-store text-white text-sm"></i>
                </div>
                <span class="text-slate-800 font-bold text-lg">{{ $appSettings['name'] ?? 'POS System' }}</span>
                @endif
            </div>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800 mb-1">Welcome back</h1>
                <p class="text-slate-500 text-sm">Sign in to your account to continue</p>
            </div>

            {{-- Error message --}}
            @if($errors->any())
            <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
                <i class="fas fa-circle-exclamation mt-0.5 flex-shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if(session('status'))
            <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
                <i class="fas fa-circle-check mt-0.5 flex-shrink-0"></i>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                {{-- Email --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="you@example.com"
                               class="input-field w-full border border-slate-200 rounded-xl pl-11 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-400 @error('email') border-red-400 bg-red-50 @enderror">
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-5" x-data="{ show: false }">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password" required
                               placeholder="Enter your password"
                               class="input-field w-full border border-slate-200 rounded-xl pl-11 pr-12 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-400">
                        <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between mb-7">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="remember" class="sr-only peer">
                            <div class="w-4 h-4 rounded border-2 border-slate-300 peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-colors flex items-center justify-center">
                                <i class="fas fa-check text-white text-[8px] hidden peer-checked:block"></i>
                            </div>
                        </div>
                        <span class="text-sm text-slate-600 select-none">Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="btn-primary w-full text-white font-semibold py-3.5 rounded-xl text-sm flex items-center justify-center gap-2"
                        :disabled="loading">
                    <template x-if="!loading">
                        <span><i class="fas fa-arrow-right-to-bracket mr-2"></i>Sign In</span>
                    </template>
                    <template x-if="loading">
                        <span><i class="fas fa-circle-notch fa-spin mr-2"></i>Signing in...</span>
                    </template>
                </button>
            </form>

            {{-- Divider --}}
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-slate-100"></div>
                <span class="text-slate-400 text-xs">or</span>
                <div class="flex-1 h-px bg-slate-100"></div>
            </div>

            {{-- Register link --}}
            <p class="text-center text-sm text-slate-500">
                New vendor?
                <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                    Register your branch
                </a>
            </p>

        </div>
    </div>
</div>

</body>
</html>
