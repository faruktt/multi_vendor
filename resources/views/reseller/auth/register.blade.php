<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    <style>
        body { font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
    </style>
</head>
<body class="min-h-full bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-900 flex items-center justify-center p-4 py-8">

<div class="w-full max-w-lg">
    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-8 shadow-2xl">

        <div class="text-center mb-7">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-500/30">
                <i class="fas fa-user-tag text-white text-2xl"></i>
            </div>
            <h1 class="text-white font-bold text-2xl">Become a Reseller</h1>
            <p class="text-indigo-200 text-sm mt-1">Register and get access to reseller prices</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-500/20 border border-red-400/30 text-red-200 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('reseller.register.submit') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Your name">
                </div>
                <div>
                    <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Business Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}"
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Shop / Company name">
                </div>
            </div>

            <div>
                <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="your@email.com">
            </div>

            <div>
                <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Phone *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                       class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="01XXXXXXXXX">
            </div>

            <div>
                <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Address</label>
                <textarea name="address" rows="2"
                          class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                          placeholder="Your delivery address">{{ old('address') }}</textarea>
            </div>

            {{-- Profile Photo --}}
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/15 space-y-1.5">
                <label class="block text-xs font-bold text-indigo-200">
                    <i class="fas fa-camera text-indigo-400 mr-1"></i> Profile Photo <span class="text-rose-400">*</span>
                </label>
                <input type="file" name="image" accept="image/*" required
                       class="w-full text-xs text-indigo-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/30 file:text-indigo-200 hover:file:bg-indigo-500/40">
            </div>

            {{-- Own NID --}}
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/15 space-y-3">
                <div class="text-xs font-bold text-indigo-200 flex items-center gap-1.5">
                    <i class="fas fa-id-card text-indigo-400"></i>
                    <span>National ID Card (Own NID) <span class="text-rose-400">*</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-indigo-300 mb-1">Front Side *</label>
                        <input type="file" name="nid_front" accept="image/*" required
                               class="w-full text-xs text-indigo-200 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/30 file:text-indigo-200">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-indigo-300 mb-1">Back Side *</label>
                        <input type="file" name="nid_back" accept="image/*" required
                               class="w-full text-xs text-indigo-200 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/30 file:text-indigo-200">
                    </div>
                </div>
            </div>

            {{-- Guardian NID --}}
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/15 space-y-3">
                <div class="text-xs font-bold text-indigo-200 flex items-center gap-1.5">
                    <i class="fas fa-user-shield text-purple-400"></i>
                    <span>Guardian National ID Card <span class="text-rose-400">*</span></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-indigo-300 mb-1">Front Side *</label>
                        <input type="file" name="guardian_nid_front" accept="image/*" required
                               class="w-full text-xs text-indigo-200 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-500/30 file:text-purple-200">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-indigo-300 mb-1">Back Side *</label>
                        <input type="file" name="guardian_nid_back" accept="image/*" required
                               class="w-full text-xs text-indigo-200 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-500/30 file:text-purple-200">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Password *</label>
                    <input type="password" name="password" required
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Min 6 characters">
                </div>
                <div>
                    <label class="block text-indigo-200 text-sm font-semibold mb-1.5">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-white placeholder-indigo-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Repeat password">
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/30 mt-2">
                <i class="fas fa-user-plus mr-2"></i> Register
            </button>
        </form>

        <p class="text-center text-indigo-300 text-sm mt-5">
            Already have an account?
            <a href="{{ route('reseller.login') }}" class="text-indigo-200 font-semibold hover:text-white underline">Login</a>
        </p>

        <div class="mt-4 bg-yellow-500/10 border border-yellow-400/20 rounded-xl px-4 py-3 text-yellow-200 text-xs text-center leading-relaxed">
            <i class="fas fa-info-circle mr-1"></i>
            Upon submission, our admin team will review and approve your registration before you can sign in.
        </div>
    </div>
</div>
</body>
</html>
