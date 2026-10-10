<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    <style>
        body { font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-900 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Card --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-8 shadow-2xl">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-500/30">
                <i class="fas fa-user-tag text-white text-2xl"></i>
            </div>
            <h1 class="text-white font-bold text-2xl">Reseller Login</h1>
            <p class="text-indigo-200 text-sm mt-1">Access your reseller account</p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-4 bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-500/20 border border-red-400/30 text-red-200 rounded-xl px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reseller.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                       placeholder="your@email.com">
                @error('email')
                    <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                       placeholder="••••••••">
                @error('password')
                    <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded">
                <label for="remember" class="text-indigo-200 text-sm">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/30 mt-2">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>

        <p class="text-center text-indigo-300 text-sm mt-6">
            Don't have an account?
            <a href="{{ route('reseller.register') }}" class="text-indigo-200 font-semibold hover:text-white underline">Register</a>
        </p>
    </div>
</div>
</body>
</html>
