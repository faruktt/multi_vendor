<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Login — {{ config('app.name', 'Marketplace') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    <style>
        body { font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
    </style>
</head>
<body class="min-h-full bg-gradient-to-br from-slate-950 via-[#042017] to-slate-900 flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-white relative overflow-x-hidden">

    <!-- Ambient Glowing Lights -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-emerald-500/20 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-teal-500/15 rounded-full blur-[130px] pointer-events-none"></div>

    <div class="w-full max-w-[430px] relative z-10 py-6">

        <!-- ── Main Card ── -->
        <div class="bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl p-7 sm:p-9 shadow-2xl shadow-black/70 relative">

            <!-- Card Header (Inside Card) -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center mx-auto mb-3.5 shadow-xl shadow-emerald-500/30">
                    <i class="fas fa-boxes-stacked text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">Supplier Sign In</h1>
                <p class="text-emerald-200/80 text-xs mt-1 font-medium">Access your Supplier Portal</p>
            </div>

            <!-- Alerts -->
            @if(session('success'))
            <div class="mb-4 bg-emerald-500/20 border border-emerald-400/40 text-emerald-200 rounded-2xl px-4 py-3 text-xs flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-400 flex-shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 bg-rose-500/20 border border-rose-400/40 text-rose-200 rounded-2xl px-4 py-3 text-xs flex items-start gap-2">
                <i class="fas fa-circle-exclamation text-rose-400 flex-shrink-0 mt-0.5"></i>
                <div class="space-y-0.5 font-medium">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('supplier.login.submit') }}" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">Email Address *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="supplier@mail.com"
                               class="w-full bg-white/10 border border-white/20 rounded-2xl pl-11 pr-4 py-3 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">Password *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="supplier_password" name="password" required
                               placeholder="••••••••"
                               class="w-full bg-white/10 border border-white/20 rounded-2xl pl-11 pr-11 py-3 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        <button type="button" onclick="togglePass('supplier_password', this)"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-emerald-200/60 hover:text-white text-sm focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-emerald-100/80 hover:text-white">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/30 bg-white/10 text-emerald-500 focus:ring-emerald-400 focus:ring-offset-0">
                        <span>Remember me</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2 mt-2">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Card Footer -->
            <div class="mt-6 pt-5 border-t border-white/15 text-center">
                <p class="text-xs text-emerald-100/80">
                    Don't have an account?
                    <a href="{{ route('supplier.register') }}" class="text-white font-bold ml-1 hover:underline underline-offset-2">
                        Register here
                    </a>
                </p>
            </div>

        </div>

        <!-- Back to Website -->
        <div class="text-center mt-5">
            <a href="{{ url('/') }}" class="text-xs text-emerald-200/60 hover:text-white inline-flex items-center gap-1.5 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> Back to Storefront
            </a>
        </div>

    </div>

    <script>
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
