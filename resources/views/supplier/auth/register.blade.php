<!DOCTYPE html>
<html lang="bn" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Registration — {{ config('app.name', 'Marketplace') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-gradient-to-br from-slate-950 via-[#042017] to-slate-900 flex items-center justify-center p-4 sm:p-6 selection:bg-emerald-500 selection:text-white relative overflow-x-hidden py-10">

    <!-- Ambient Glowing Lights -->
    <div class="fixed top-0 left-1/4 w-[500px] h-[500px] bg-emerald-500/15 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/4 w-[500px] h-[500px] bg-teal-500/15 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-2xl relative z-10">

        <!-- ── Main Card (কার্ড স্টাইল) ── -->
        <div class="bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/70 relative">

            <!-- Card Header (Inside Card) -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center mx-auto mb-3.5 shadow-xl shadow-emerald-500/30">
                    <i class="fas fa-handshake text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight">সাপ্লায়ার রেজিস্ট্রেশন</h1>
                <div class="mt-1.5 flex items-center justify-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                        <i class="fas fa-shield-halved text-[10px]"></i> অনুমোদনের পর একাউন্ট সচল হবে
                    </span>
                </div>
            </div>

            <!-- Errors -->
            @if($errors->any())
            <div class="mb-5 bg-rose-500/20 border border-rose-400/40 text-rose-200 rounded-2xl px-4 py-3 text-xs flex items-start gap-2">
                <i class="fas fa-circle-exclamation text-rose-400 flex-shrink-0 mt-0.5"></i>
                <div class="space-y-0.5 font-medium">
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('supplier.register.submit') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Basic Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Name -->
                    <div>
                        <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">পূর্ণ নাম *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="আপনার নাম"
                                   class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-4 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Company Name -->
                    <div>
                        <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">প্রতিষ্ঠান / শপ</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                                <i class="fas fa-store"></i>
                            </span>
                            <input type="text" name="company_name" value="{{ old('company_name') }}"
                                   placeholder="ব্যবসা প্রতিষ্ঠানের নাম"
                                   class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-4 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">ইমেইল *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="mail@example.com"
                                   class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-4 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">মোবাইল নম্বর *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                   placeholder="01XXXXXXXXX"
                                   class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-4 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">ঠিকানা</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-emerald-200/60 text-sm">
                            <i class="fas fa-location-dot"></i>
                        </span>
                        <textarea name="address" rows="2"
                                  placeholder="জেলা, থানা, ঠিকানা..."
                                  class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-4 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all resize-none">{{ old('address') }}</textarea>
                    </div>
                </div>

                <!-- Documents Section (Sub-Card) -->
                <div class="bg-white/[0.05] border border-white/15 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-id-card text-emerald-400 text-sm"></i>
                        <span class="text-xs font-bold text-white uppercase tracking-wider">প্রয়োজনীয় ডকুমেন্টস</span>
                    </div>

                    <!-- Profile Photo -->
                    <label class="relative flex items-center justify-center p-3 rounded-2xl border border-dashed border-white/25 hover:border-emerald-400 bg-white/10 hover:bg-white/15 cursor-pointer transition-all group overflow-hidden h-20">
                        <input type="file" name="image" accept="image/*" required class="hidden" onchange="previewDoc(this, 'prev_image')">
                        <div id="prev_image_empty" class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <i class="fas fa-camera text-sm"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-xs font-bold text-white">নিজের ছবি *</div>
                                <div class="text-[11px] text-emerald-200/70">ছবি সিলেক্ট করুন</div>
                            </div>
                        </div>
                        <div id="prev_image_filled" class="hidden absolute inset-0 bg-slate-900 flex items-center justify-center p-1">
                            <img src="" alt="Preview" class="h-full w-full object-cover rounded-xl">
                            <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] shadow">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                    </label>

                    <!-- User NID Front & Back -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center p-3 rounded-2xl border border-dashed border-white/25 hover:border-emerald-400 bg-white/10 hover:bg-white/15 cursor-pointer transition-all group overflow-hidden h-20">
                            <input type="file" name="nid_front" accept="image/*" required class="hidden" onchange="previewDoc(this, 'prev_nid_front')">
                            <div id="prev_nid_front_empty" class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i class="fas fa-id-card text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-white">NID (সামনে) *</div>
                                    <div class="text-[11px] text-emerald-200/70">ফ্রন্ট পেজ ছবি</div>
                                </div>
                            </div>
                            <div id="prev_nid_front_filled" class="hidden absolute inset-0 bg-slate-900 flex items-center justify-center p-1">
                                <img src="" alt="Preview" class="h-full w-full object-cover rounded-xl">
                                <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] shadow">
                                <i class="fas fa-check"></i>
                            </span>
                            </div>
                        </label>

                        <label class="relative flex items-center justify-center p-3 rounded-2xl border border-dashed border-white/25 hover:border-emerald-400 bg-white/10 hover:bg-white/15 cursor-pointer transition-all group overflow-hidden h-20">
                            <input type="file" name="nid_back" accept="image/*" required class="hidden" onchange="previewDoc(this, 'prev_nid_back')">
                            <div id="prev_nid_back_empty" class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i class="fas fa-id-card text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-white">NID (পেছনে) *</div>
                                    <div class="text-[11px] text-emerald-200/70">ব্যাক পেজ ছবি</div>
                                </div>
                            </div>
                            <div id="prev_nid_back_filled" class="hidden absolute inset-0 bg-slate-900 flex items-center justify-center p-1">
                                <img src="" alt="Preview" class="h-full w-full object-cover rounded-xl">
                                <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] shadow">
                                <i class="fas fa-check"></i>
                            </span>
                            </div>
                        </label>
                    </div>

                    <!-- Guardian NID Front & Back -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-center justify-center p-3 rounded-2xl border border-dashed border-white/25 hover:border-teal-400 bg-white/10 hover:bg-white/15 cursor-pointer transition-all group overflow-hidden h-20">
                            <input type="file" name="guardian_nid_front" accept="image/*" required class="hidden" onchange="previewDoc(this, 'prev_gnid_front')">
                            <div id="prev_gnid_front_empty" class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-500/20 text-teal-300 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i class="fas fa-user-shield text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-white">অভিভাবক NID (সামনে) *</div>
                                    <div class="text-[11px] text-emerald-200/70">ফ্রন্ট পেজ ছবি</div>
                                </div>
                            </div>
                            <div id="prev_gnid_front_filled" class="hidden absolute inset-0 bg-slate-900 flex items-center justify-center p-1">
                                <img src="" alt="Preview" class="h-full w-full object-cover rounded-xl">
                                <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-teal-500 text-white flex items-center justify-center text-[10px] shadow">
                                <i class="fas fa-check"></i>
                            </span>
                            </div>
                        </label>

                        <label class="relative flex items-center justify-center p-3 rounded-2xl border border-dashed border-white/25 hover:border-teal-400 bg-white/10 hover:bg-white/15 cursor-pointer transition-all group overflow-hidden h-20">
                            <input type="file" name="guardian_nid_back" accept="image/*" required class="hidden" onchange="previewDoc(this, 'prev_gnid_back')">
                            <div id="prev_gnid_back_empty" class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-teal-500/20 text-teal-300 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <i class="fas fa-user-shield text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-white">অভিভাবক NID (পেছনে) *</div>
                                    <div class="text-[11px] text-emerald-200/70">ব্যাক পেজ ছবি</div>
                                </div>
                            </div>
                            <div id="prev_gnid_back_filled" class="hidden absolute inset-0 bg-slate-900 flex items-center justify-center p-1">
                                <img src="" alt="Preview" class="h-full w-full object-cover rounded-xl">
                                <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-teal-500 text-white flex items-center justify-center text-[10px] shadow">
                                <i class="fas fa-check"></i>
                            </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Password Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Password -->
                    <div>
                        <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">পাসওয়ার্ড *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="reg_password" name="password" required minlength="6"
                                   placeholder="কমপক্ষে ৬ ডিজিট"
                                   class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-10 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                            <button type="button" onclick="togglePass('reg_password', this)"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 hover:text-white text-sm focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-emerald-100 text-xs font-bold mb-1.5 uppercase tracking-wider">কনফার্ম পাসওয়ার্ড *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 text-sm">
                                <i class="fas fa-lock-open"></i>
                            </span>
                            <input type="password" id="reg_confirm_password" name="password_confirmation" required minlength="6"
                                   placeholder="পাসওয়ার্ড পুনরাবৃত্তি"
                                   class="w-full bg-white/10 border border-white/20 rounded-2xl pl-10 pr-10 py-2.5 text-white placeholder-emerald-100/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                            <button type="button" onclick="togglePass('reg_confirm_password', this)"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-200/60 hover:text-white text-sm focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm shadow-xl shadow-emerald-500/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2 mt-2">
                    <span>আবেদন জমা দিন</span>
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </form>

            <!-- Card Footer -->
            <div class="mt-6 pt-5 border-t border-white/15 text-center">
                <p class="text-xs text-emerald-100/80">
                    ইতিমধ্যে একাউন্ট আছে?
                    <a href="{{ route('supplier.login') }}" class="text-white font-bold ml-1 hover:underline underline-offset-2">
                        লগইন করুন
                    </a>
                </p>
            </div>

        </div>

        <!-- Back to Website -->
        <div class="text-center mt-5">
            <a href="{{ url('/') }}" class="text-xs text-emerald-200/60 hover:text-white inline-flex items-center gap-1.5 transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> ওয়েবসাইটে ফিরে যান
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

        function previewDoc(input, prefix) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const empty = document.getElementById(prefix + '_empty');
                    const filled = document.getElementById(prefix + '_filled');
                    const img = filled.querySelector('img');
                    img.src = e.target.result;
                    empty.classList.add('hidden');
                    filled.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
