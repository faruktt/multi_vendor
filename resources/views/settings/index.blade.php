@extends('layouts.app')
@section('title', 'Settings')
@section('heading', 'Settings')

@section('content')
<div class="max-w-2xl space-y-4">

{{-- Profile/Password are the logged-in user's OWN account — for super-admin that's
     already managed at /admin/settings, so skip it here to avoid a confusing duplicate. --}}
@unless($isSuperAdmin)
{{-- ══ PROFILE ═════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user text-blue-600 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-bold text-slate-800">Profile Information</h2>
            <p class="text-[11.5px] text-slate-400">Your login name and email</p>
        </div>
        <div class="ml-auto">
            <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-lg capitalize">
                {{ str_replace('-', ' ', $user->getRoleNames()->first() ?? 'user') }}
            </span>
        </div>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('branch.settings.profile', $branch) }}" enctype="multipart/form-data" class="space-y-3" x-data="{ preview: @js($user->image_url ?? null) }">
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
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
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

{{-- ══ PASSWORD ════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-lock text-amber-600 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-bold text-slate-800">Change Password</h2>
            <p class="text-[11.5px] text-slate-400">Keep your account secure</p>
        </div>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('branch.settings.password', $branch) }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Current Password <span class="text-red-500">*</span></label>
                <input type="password" name="current_password" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                @error('current_password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">New Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
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
@endunless

{{-- ══ BUSINESS INFO — owner/manager only, not staff/cashier ═════════ --}}
@if($vendor && $canManageBusiness)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-store text-emerald-600 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-bold text-slate-800">Business Information</h2>
            <p class="text-[11.5px] text-slate-400">Shown on invoices and receipts</p>
        </div>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('branch.settings.branch', $branch) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Logo --}}
            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden bg-white flex-shrink-0"
                     id="logo-preview-wrap">
                    @if($vendor->logo)
                        <img src="{{ Storage::disk('uploads')->url($vendor->logo) }}" class="w-full h-full object-cover" alt="logo" id="logo-img">
                    @else
                        <i class="fas fa-image text-slate-300 text-2xl" id="logo-placeholder"></i>
                    @endif
                </div>
                <div>
                    <p class="text-[12.5px] font-semibold text-slate-700 mb-1.5">Business Logo</p>
                    <label class="cursor-pointer inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-[11.5px] font-semibold px-3.5 py-2 rounded-xl transition-colors">
                        <i class="fas fa-upload text-[10px]"></i> Upload Logo
                        <input type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                    </label>
                    <p class="text-[10.5px] text-slate-400 mt-1.5">PNG/JPG · max 2MB · 200×200px recommended</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Business Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Owner Name <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $vendor->owner_name) }}" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">System / Invoice Name</label>
                    <input type="text" name="system_name" value="{{ old('system_name', $vendor->system_name) }}"
                           placeholder="Shown on invoice header"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sales Commission (%)</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="commission_percentage" value="{{ old('commission_percentage', $vendor->commission_percentage) }}"
                               placeholder="e.g. 2.5"
                               class="w-full border border-slate-200 rounded-xl pl-4 pr-8 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                    </div>
                    <p class="text-[10.5px] text-slate-400 mt-1">Used to calculate each staff member's commission on their sales.</p>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Address</label>
                    <textarea name="address" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white resize-none">{{ old('address', $vendor->address) }}</textarea>
                </div>

                @if($vendor->is_online_store)
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Delivery Charge — Inside Dhaka</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">{{ $appSettings['currency'] ?? '৳' }}</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge_inside_dhaka" value="{{ old('delivery_charge_inside_dhaka', $vendor->delivery_charge_inside_dhaka) }}"
                               class="w-full border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Delivery Charge — Sub Dhaka</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">{{ $appSettings['currency'] ?? '৳' }}</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge_sub_dhaka" value="{{ old('delivery_charge_sub_dhaka', $vendor->delivery_charge_sub_dhaka ?? 100) }}"
                               class="w-full border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Delivery Charge — Outside Dhaka</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">{{ $appSettings['currency'] ?? '৳' }}</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge_outside_dhaka" value="{{ old('delivery_charge_outside_dhaka', $vendor->delivery_charge_outside_dhaka) }}"
                               class="w-full border border-slate-200 rounded-xl pl-8 pr-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white">
                    </div>
                </div>
                @endif
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm shadow-emerald-200 transition-colors">
                    <i class="fas fa-floppy-disk text-xs"></i> Save Business Info
                </button>
            </div>
        </form>
    </div>
</div>
@endif

</div>{{-- max-w --}}

@push('scripts')
<script>
function previewLogo(input) {
    if (!input.files?.[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logo-preview-wrap').innerHTML =
            `<img src="${e.target.result}" class="w-full h-full object-cover rounded-xl" alt="logo">`;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endpush
@endsection
