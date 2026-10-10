@extends('shop.account.layout')
@section('title', 'Profile & Security — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="space-y-6">

    {{-- Profile Details & Avatar --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8 relative overflow-hidden"
         x-data="{
            photoPreview: {{ $customer->avatar_url ? "'" . $customer->avatar_url . "'" : 'null' }},
            removeImage: false,
            onPhotoChange(event) {
                const file = event.target.files[0];
                if (file) {
                    this.removeImage = false;
                    this.photoPreview = URL.createObjectURL(file);
                }
            },
            clearPhoto() {
                this.removeImage = true;
                this.photoPreview = null;
                $refs.photoInput.value = '';
            }
         }">

        <div class="border-b border-gray-100 pb-4 mb-6 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-lg font-black text-gray-900 tracking-tight">Personal Information</h2>
                <p class="text-xs text-gray-400 mt-0.5">Manage your photo, personal details and default shipping address</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-brand/10 text-brand border border-brand/15">
                <i class="fas fa-id-badge"></i> Account ID: #{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}
            </span>
        </div>

        <form method="POST" action="{{ route('shop.customer.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Hidden input for remove_image --}}
            <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">

            {{-- Avatar Upload Section --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-gray-50 to-gray-50/40 border border-gray-100 flex flex-col sm:flex-row items-center sm:items-start gap-5">
                <div class="relative group flex-shrink-0">
                    {{-- Circular Avatar Preview --}}
                    <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-white shadow-md bg-gradient-to-br from-brand/20 to-brand/5 flex items-center justify-center">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Profile Avatar" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!photoPreview">
                            <div class="w-full h-full bg-brand text-white flex items-center justify-center text-3xl font-black">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                        </template>
                    </div>

                    {{-- Camera Overlay Icon on click --}}
                    <button type="button" @click="$refs.photoInput.click()"
                            class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-gray-900 hover:bg-black text-white flex items-center justify-center text-xs shadow-md border-2 border-white transition-all transform active:scale-95"
                            title="Upload new photo">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>

                <div class="flex-1 text-center sm:text-left space-y-2">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Profile Photo</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            This photo will appear in your account header avatar. Recommended: Square image (JPG, PNG, WebP), max 5MB.
                        </p>
                    </div>

                    {{-- Hidden file input --}}
                    <input type="file" x-ref="photoInput" name="image" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
                           class="hidden" @change="onPhotoChange($event)">

                    <div class="flex items-center justify-center sm:justify-start gap-2 pt-1 flex-wrap">
                        <button type="button" @click="$refs.photoInput.click()"
                                class="h-9 px-4 rounded-xl bg-brand text-white hover:bg-brand-dark text-xs font-bold transition-all shadow-sm shadow-brand/20 flex items-center gap-1.5">
                            <i class="fas fa-arrow-up-from-bracket text-[11px]"></i> Upload New Photo
                        </button>

                        <button type="button" x-show="photoPreview" @click="clearPhoto()"
                                class="h-9 px-3.5 rounded-xl bg-white hover:bg-red-50 text-red-600 border border-gray-200 hover:border-red-200 text-xs font-bold transition-all flex items-center gap-1.5">
                            <i class="fas fa-trash text-[10px]"></i> Remove Photo
                        </button>
                    </div>
                </div>
            </div>

            {{-- Form Fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                               class="w-full h-11 border border-gray-200 rounded-xl pl-10 pr-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all font-medium text-gray-900">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                            <i class="fas fa-phone"></i>
                        </span>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                               class="w-full h-11 border border-gray-200 rounded-xl pl-10 pr-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all font-medium text-gray-900">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                           placeholder="youremail@example.com"
                           class="w-full h-11 border border-gray-200 rounded-xl pl-10 pr-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all font-medium text-gray-900">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Default Shipping Address
                </label>
                <div class="relative">
                    <textarea name="address" rows="3" placeholder="House/Road, Area, Thana, District"
                              class="w-full border border-gray-200 rounded-xl p-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all font-medium text-gray-900 resize-none">{{ old('address', $customer->address) }}</textarea>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="h-11 px-6 bg-brand hover:bg-brand-dark text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-brand/20 flex items-center gap-2">
                    <i class="fas fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Update Password Card --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <div class="border-b border-gray-100 pb-4 mb-6">
            <h2 class="text-lg font-black text-gray-900 tracking-tight">Security &amp; Password</h2>
            <p class="text-xs text-gray-400 mt-0.5">Ensure your account uses a strong and unique password</p>
        </div>

        <form method="POST" action="{{ route('shop.customer.password.update') }}" class="space-y-4 max-w-lg">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Current Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="current_password" required
                           placeholder="Enter your current password"
                           class="w-full h-11 border border-gray-200 rounded-xl pl-10 pr-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                            <i class="fas fa-key"></i>
                        </span>
                        <input type="password" name="password" required
                               placeholder="Min 6 characters"
                               class="w-full h-11 border border-gray-200 rounded-xl pl-10 pr-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                            <i class="fas fa-check-double"></i>
                        </span>
                        <input type="password" name="password_confirmation" required
                               placeholder="Re-type new password"
                               class="w-full h-11 border border-gray-200 rounded-xl pl-10 pr-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand bg-gray-50/50 hover:bg-white transition-all">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="h-11 px-6 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-shield-halved"></i> Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
