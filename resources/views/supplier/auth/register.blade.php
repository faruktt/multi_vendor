<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Registration — {{ config('app.name', 'Marketplace') }}</title>
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
<body class="min-h-full bg-slate-900 flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-white relative overflow-hidden py-10">

{{-- Background Glow --}}
<div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
<div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

<div class="w-full max-w-lg relative z-10">

    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white text-2xl shadow-xl shadow-emerald-500/25 mb-3">
            <i class="fas fa-handshake"></i>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">Become a Supplier / Vendor</h1>
        <p class="text-slate-400 text-xs mt-1">Register your business to sell products on our marketplace</p>
    </div>

    {{-- Card --}}
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-7 shadow-2xl">

        {{-- Notice --}}
        <div class="mb-5 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-start gap-2.5">
            <i class="fas fa-shield-halved text-base flex-shrink-0 mt-0.5 text-amber-400"></i>
            <div>
                <span class="font-bold">Approval Notice:</span> After registration, an Administrator will review your account. Once approved, you can log in and start uploading products.
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-start gap-2.5">
            <i class="fas fa-circle-exclamation text-base flex-shrink-0 mt-0.5"></i>
            <div class="space-y-0.5">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('supplier.register.submit') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Contact Person Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Your Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Full Name"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>

                {{-- Company / Brand Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Business / Store Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                           placeholder="e.g. Apex Traders"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address <span class="text-rose-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="store@example.com"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone Number <span class="text-rose-400">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           placeholder="01XXXXXXXXX"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Business / Warehouse Address</label>
                <textarea name="address" rows="2"
                          placeholder="District, Thana, Full Address..."
                          class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all resize-none">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password <span class="text-rose-400">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           placeholder="At least 6 characters"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm Password <span class="text-rose-400">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="6"
                           placeholder="Repeat password"
                           class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2 mt-2">
                <span>Submit Supplier Application</span>
                <i class="fas fa-paper-plane text-xs"></i>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-700/60 text-center">
            <p class="text-xs text-slate-400">
                Already registered?
                <a href="{{ route('supplier.login') }}" class="text-emerald-400 hover:text-emerald-300 font-bold ml-1 hover:underline">
                    Login Here
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
