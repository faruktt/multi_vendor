<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Login — {{ config('app.name', 'Marketplace') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        supplier: {
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full bg-slate-900 flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-white relative overflow-hidden">

{{-- Background Glow / Mesh --}}
<div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
<div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

<div class="w-full max-w-md relative z-10">

    {{-- Header / Logo --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white text-2xl shadow-xl shadow-emerald-500/25 mb-3">
            <i class="fas fa-store"></i>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">Supplier Portal</h1>
        <p class="text-slate-400 text-xs mt-1">Sign in to manage your products, stock, and orders</p>
    </div>

    {{-- Card --}}
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-7 shadow-2xl">

        {{-- Alerts --}}
        @if(session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-start gap-2.5">
            <i class="fas fa-circle-check text-base flex-shrink-0 mt-0.5"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-start gap-2.5">
            <i class="fas fa-circle-exclamation text-base flex-shrink-0 mt-0.5"></i>
            <div>
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('supplier.login.submit') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 text-xs pointer-events-none">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="supplier@store.com"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 text-xs pointer-events-none">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 text-emerald-600 focus:ring-emerald-500 bg-slate-900">
                    <span class="text-xs text-slate-400">Remember me</span>
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2">
                <span>Sign In to Portal</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-700/60 text-center">
            <p class="text-xs text-slate-400">
                Don't have a supplier account yet?
                <a href="{{ route('supplier.register') }}" class="text-emerald-400 hover:text-emerald-300 font-bold ml-1 hover:underline">
                    Register Here
                </a>
            </p>
        </div>

    </div>

    {{-- Back to shop --}}
    <div class="text-center mt-6">
        <a href="{{ url('/') }}" class="text-xs text-slate-500 hover:text-slate-300 inline-flex items-center gap-1.5 transition-colors">
            <i class="fas fa-arrow-left text-[10px]"></i> Back to Main Website
        </a>
    </div>

</div>

</body>
</html>
