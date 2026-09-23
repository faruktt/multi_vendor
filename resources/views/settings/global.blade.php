@extends('layouts.app')
@section('title', 'System Settings')
@section('heading', 'System Settings')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{ tab: '{{ $errors->hasAny(['current_password', 'password', 'email']) ? 'account' : 'system' }}' }">

    {{-- Tab switcher --}}
    <div class="flex gap-1.5 bg-slate-100 rounded-xl p-1.5 mb-5 w-fit">
        <button type="button" @click="tab = 'system'"
                class="px-4 py-2 rounded-lg text-[13px] font-semibold transition-colors"
                :class="tab === 'system' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
            <i class="fas fa-building text-xs mr-1.5"></i> System Settings
        </button>
        <button type="button" @click="tab = 'account'"
                class="px-4 py-2 rounded-lg text-[13px] font-semibold transition-colors"
                :class="tab === 'account' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
            <i class="fas fa-user text-xs mr-1.5"></i> My Account
        </button>
    </div>

    {{-- ══ TAB: SYSTEM SETTINGS ═══════════════════════════════════════ --}}
    <div x-show="tab === 'system'" x-cloak>
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf

            {{-- Company Identity --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-semibold text-slate-800">Company Identity</h2>
                        <p class="text-[11.5px] text-slate-400">Name and branding shown across the system</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">

                    {{-- Logo --}}
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-2">Company Logo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50"
                                 id="logo-preview-wrap">
                                @if(!empty($settings['logo']))
                                    <img src="{{ $settings['logo'] }}" id="logo-preview" class="w-full h-full object-cover" alt="logo">
                                @else
                                    <i class="fas fa-image text-slate-300 text-2xl" id="logo-placeholder"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="cursor-pointer inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[12.5px] font-medium px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-upload text-[11px]"></i> Upload Logo
                                    <input type="file" name="logo" accept="image/*" class="hidden" id="logo-input"
                                           onchange="previewLogo(this)">
                                </label>
                                <p class="text-[11px] text-slate-400 mt-1.5">PNG, JPG up to 2MB. Recommended: 200×200px</p>
                            </div>
                        </div>
                    </div>

                    {{-- Favicon --}}
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-2">Favicon</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50"
                                 id="favicon-preview-wrap">
                                @if(!empty($settings['favicon']))
                                    <img src="{{ $settings['favicon'] }}" id="favicon-preview" class="w-full h-full object-contain p-2" alt="favicon">
                                @else
                                    <i class="fas fa-star text-slate-300 text-2xl" id="favicon-placeholder"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="cursor-pointer inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[12.5px] font-medium px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-upload text-[11px]"></i> Upload Favicon
                                    <input type="file" name="favicon" accept=".ico,.png,.jpg,.jpeg,.svg" class="hidden" id="favicon-input"
                                           onchange="previewFavicon(this)">
                                </label>
                                <p class="text-[11px] text-slate-400 mt-1.5">ICO, PNG, JPG or SVG up to 512KB. Recommended: 32×32px or 64×64px, square</p>
                            </div>
                        </div>
                    </div>

                    {{-- Name & Tagline --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">
                                System Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $settings['name'] ?? '') }}" required
                                   placeholder="e.g. Super Shop POS"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Tagline</label>
                            <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}"
                                   placeholder="e.g. Multi-Branch Point of Sale"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Contact Information --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i class="fas fa-address-card text-emerald-600 text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-semibold text-slate-800">Contact Information</h2>
                        <p class="text-[11.5px] text-slate-400">Printed on invoices and receipts</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">

                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Address</label>
                        <textarea name="address" rows="2"
                                  placeholder="Full company address"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('address', $settings['address'] ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $settings['phone'] ?? '') }}"
                                   placeholder="+880 17XX XXXXXX"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}"
                                   placeholder="info@yourshop.com"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Regional & Receipt --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                        <i class="fas fa-sliders text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-semibold text-slate-800">Regional & Receipt</h2>
                        <p class="text-[11.5px] text-slate-400">Currency and receipt footer text</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Currency Symbol</label>
                            <input type="text" name="currency" value="{{ old('currency', $settings['currency'] ?? '৳') }}"
                                   placeholder="৳"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <p class="text-[11px] text-slate-400 mt-1">Examples: ৳, $, €, £, ₹</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Receipt Footer Text</label>
                        <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text'] ?? '') }}"
                               placeholder="Thank you for shopping with us!"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                </div>
            </div>

            {{-- SEO & Social Sharing --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <i class="fas fa-share-nodes text-indigo-600 text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-semibold text-slate-800">SEO & Social Sharing</h2>
                        <p class="text-[11.5px] text-slate-400">Controls the title/preview shown when a link to this site is shared or found on Google</p>
                    </div>
                </div>
                <div class="p-6 space-y-4">

                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">SEO Title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}"
                               placeholder="Defaults to System Name if left blank"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Meta Description</label>
                        <textarea name="seo_description" rows="2"
                                  placeholder="A short summary shown in search results and link previews"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-2">Social Share Image</label>
                        <div class="flex items-center gap-4">
                            <div class="w-24 h-14 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-slate-50 flex-shrink-0"
                                 id="og-image-preview-wrap">
                                @if(!empty($settings['og_image']))
                                    <img src="{{ $settings['og_image'] }}" id="og-image-preview" class="w-full h-full object-cover" alt="social share image">
                                @else
                                    <i class="fas fa-image text-slate-300 text-xl" id="og-image-placeholder"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="cursor-pointer inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[12.5px] font-medium px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-upload text-[11px]"></i> Upload Image
                                    <input type="file" name="og_image" accept="image/*" class="hidden" id="og-image-input"
                                           onchange="previewOgImage(this)">
                                </label>
                                <p class="text-[11px] text-slate-400 mt-1.5">PNG, JPG up to 2MB. Recommended: 1200×630px. Falls back to logo if not set.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between">
                <p class="text-[12px] text-slate-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    Changes take effect immediately across all branches.
                </p>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white px-6 py-2.5 rounded-xl text-[13.5px] font-semibold shadow-sm shadow-blue-200 transition-colors flex items-center gap-2">
                    <i class="fas fa-floppy-disk text-[12px]"></i>
                    Save Settings
                </button>
            </div>

        </form>
    </div>

    {{-- ══ TAB: MY ACCOUNT ═════════════════════════════════════════════ --}}
    <div x-show="tab === 'account'" x-cloak class="space-y-5">

        {{-- Profile Information --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-user text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="text-[14px] font-semibold text-slate-800">Profile Information</h2>
                    <p class="text-[11.5px] text-slate-400">Your login name and email</p>
                </div>
                <div class="ml-auto">
                    <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-lg capitalize">
                        {{ str_replace('-', ' ', $user->getRoleNames()->first() ?? 'super admin') }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.settings.profile') }}" enctype="multipart/form-data" class="space-y-4" x-data="{ preview: @js($user->image_url ?? null) }">
                    @csrf
                    <div class="flex items-center gap-4 mb-2 p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="relative w-14 h-14 rounded-2xl flex-shrink-0 cursor-pointer group" @click="$refs.avatarInput.click()">
                            <template x-if="preview">
                                <img :src="preview" class="w-14 h-14 rounded-2xl object-cover" alt="avatar">
                            </template>
                            <template x-if="!preview">
                                <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-xl font-bold">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            </template>
                            <div class="absolute inset-0 rounded-2xl bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <i class="fas fa-camera text-white text-xs"></i>
                            </div>
                            <input type="file" name="image" accept="image/*" x-ref="avatarInput" class="hidden"
                                   @change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
                        </div>
                        <div>
                            <p class="text-[15px] font-bold text-slate-800">{{ $user->name }}</p>
                            <p class="text-[12.5px] text-slate-500 mt-0.5">{{ $user->email }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Member since {{ $user->created_at->format('d M Y') }}</p>
                            <button type="button" @click="$refs.avatarInput.click()" class="text-[11px] font-semibold text-blue-600 hover:underline mt-1">Change photo</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
                            <i class="fas fa-check text-xs"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="fas fa-key text-amber-600 text-sm"></i>
                </div>
                <div>
                    <h2 class="text-[14px] font-semibold text-slate-800">Change Password</h2>
                    <p class="text-[11.5px] text-slate-400">Keep your super admin account secure</p>
                </div>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                        @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">New Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required minlength="6"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-600 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm shadow-amber-200 transition-colors">
                            <i class="fas fa-key text-xs"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('logo-preview-wrap');
            wrap.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="logo">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewOgImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('og-image-preview-wrap');
            wrap.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="social share image">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.getElementById('favicon-preview-wrap');
            wrap.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-contain p-2" alt="favicon">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
