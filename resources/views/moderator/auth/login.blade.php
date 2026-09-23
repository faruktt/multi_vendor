<!DOCTYPE html>
<html lang="bn" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মডারেটর লগইন - {{ config('app.name', 'Fayaz') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Noto Sans Bengali', sans-serif; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Card --}}
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">

        {{-- Icon & Header --}}
        <div class="text-center mb-8">
            <div class="w-18 h-18 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center mx-auto mb-4 shadow-xl shadow-indigo-500/40">
                <i class="fas fa-user-shield text-white text-3xl"></i>
            </div>
            <h1 class="text-white font-extrabold text-2xl tracking-tight">মডারেটর লগইন</h1>
            <p class="text-indigo-200 text-xs mt-1.5 font-medium">কাজের শিফট শুরু করতে এবং রিপোর্ট জমা দিতে লগইন করুন</p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-4 bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 rounded-2xl px-4 py-3 text-xs flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-400"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-500/20 border border-red-400/30 text-red-200 rounded-2xl px-4 py-3 text-xs flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-400"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('moderator.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-indigo-200 text-xs font-bold mb-1.5 uppercase tracking-wider">ইমেইল এড্রেস</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300/70 text-sm">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-white/10 border border-white/20 rounded-2xl pl-11 pr-4 py-3 text-white placeholder-indigo-300/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"
                           placeholder="moderator@example.com">
                </div>
                @error('email')
                    <p class="text-red-300 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-indigo-200 text-xs font-bold mb-1.5 uppercase tracking-wider">পাসওয়ার্ড</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300/70 text-sm">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" required
                           class="w-full bg-white/10 border border-white/20 rounded-2xl pl-11 pr-4 py-3 text-white placeholder-indigo-300/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all"
                           placeholder="••••••••">
                </div>
                @error('password')
                    <p class="text-red-300 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/20 bg-white/10 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                    <span class="text-indigo-200 text-xs font-medium">আমাকে মনে রাখুন</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold py-3.5 px-4 rounded-2xl transition-all duration-200 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-[1.01] active:scale-[0.99] text-sm flex items-center justify-center gap-2 cursor-pointer mt-2">
                <i class="fas fa-sign-in-alt text-xs"></i>
                <span>লগইন করুন</span>
            </button>
        </form>

        <div class="text-center mt-6 pt-4 border-t border-white/10">
            <p class="text-indigo-300/70 text-xs">অ্যাকাউন্ট না থাকলে অ্যাডমিনের সাথে যোগাযোগ করুন</p>
        </div>

    </div>
</div>

</body>
</html>
