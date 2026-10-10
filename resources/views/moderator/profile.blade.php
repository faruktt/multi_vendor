@extends('moderator.layouts.app')
@section('title', 'My Profile & Settings - Moderator Portal')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="profileManager()">

    {{-- Top Profile Header Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        {{-- Ambient background glows --}}
        <div class="absolute -right-16 -bottom-16 w-56 h-56 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -top-16 w-56 h-56 bg-violet-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-5">
            {{-- Big Avatar in Header --}}
            <div class="relative group">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-gradient-to-tr from-indigo-500 to-violet-600 border-3 border-white/20 shadow-2xl flex items-center justify-center flex-shrink-0">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Profile Preview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!previewUrl && existingImageUrl">
                        <img :src="existingImageUrl" alt="{{ $moderator->name }}" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!previewUrl && !existingImageUrl">
                        <span class="text-3xl sm:text-4xl font-black text-white">
                            {{ strtoupper(substr($moderator->name, 0, 1)) }}
                        </span>
                    </template>
                </div>
                {{-- Duty indicator dot --}}
                <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full border-2 border-slate-900 {{ $moderator->isWorkingNow() ? 'bg-emerald-500 ring-2 ring-emerald-400/50' : 'bg-slate-400' }}"
                      title="{{ $moderator->isWorkingNow() ? 'On Duty' : 'Off Duty' }}"></span>
            </div>

            {{-- Moderator Identity & Quick Stats --}}
            <div class="text-center sm:text-left flex-1 space-y-2">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $moderator->name }}</h1>
                    @if($moderator->isWorkingNow())
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            On Duty (Shift In Progress)
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white/10 text-slate-300 border border-white/10">
                            Off Duty
                        </span>
                    @endif
                </div>

                <p class="text-xs sm:text-sm text-indigo-200/90 font-mono flex items-center justify-center sm:justify-start gap-1.5">
                    <i class="far fa-envelope text-indigo-300"></i>
                    <span>{{ $moderator->email }}</span>
                    @if($moderator->phone)
                        <span class="text-white/30 mx-1">•</span>
                        <i class="fas fa-phone text-indigo-300"></i>
                        <span>{{ $moderator->phone }}</span>
                    @endif
                </p>

                {{-- Stats Row --}}
                <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs">
                    <div class="bg-white/10 border border-white/10 px-3 py-1.5 rounded-xl backdrop-blur-xs">
                        <span class="text-slate-300 text-[11px] block">Completed Shifts</span>
                        <span class="font-extrabold text-white text-sm font-mono">{{ $totalSessions }} shifts</span>
                    </div>
                    <div class="bg-white/10 border border-white/10 px-3 py-1.5 rounded-xl backdrop-blur-xs">
                        <span class="text-slate-300 text-[11px] block">Total Work Duration</span>
                        <span class="font-extrabold text-white text-sm font-mono">{{ $moderator->formattedTotalWorkTime() }}</span>
                    </div>
                    @php
                        $tH = floor($todaySeconds / 3600);
                        $tM = floor(($todaySeconds % 3600) / 60);
                    @endphp
                    <div class="bg-white/10 border border-white/10 px-3 py-1.5 rounded-xl backdrop-blur-xs">
                        <span class="text-slate-300 text-[11px] block">Today's Work</span>
                        <span class="font-extrabold text-emerald-400 text-sm font-mono">{{ $tH > 0 ? "{$tH}h {$tM}m" : "{$tM}m" }}</span>
                    </div>
                </div>
            </div>

            {{-- Back to Dashboard Button --}}
            <div class="sm:self-start">
                <a href="{{ route('moderator.dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition-all border border-white/10">
                    <i class="fas fa-arrow-left text-[11px]"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Two Column Forms Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Profile Details & Picture Upload --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-7">
                <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold shadow-2xs">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900">Personal Information & Profile Picture</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Update your personal information, contact details, and display photo</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('moderator.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Profile Image Upload Section --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Profile Picture
                        </label>

                        <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-2xl bg-slate-50 border border-dashed border-slate-300/90">
                            {{-- Interactive Preview Circle/Box --}}
                            <div class="relative w-20 h-20 rounded-2xl overflow-hidden bg-slate-200 border-2 border-white shadow-md flex-shrink-0 flex items-center justify-center">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" alt="New Preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!previewUrl && existingImageUrl">
                                    <img :src="existingImageUrl" alt="Current" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!previewUrl && !existingImageUrl">
                                    <i class="fas fa-user text-3xl text-slate-400"></i>
                                </template>
                            </div>

                            <div class="flex-1 text-center sm:text-left space-y-2">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                    {{-- File Input Button --}}
                                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer">
                                        <i class="fas fa-camera text-[11px]"></i>
                                        <span>Upload New Photo</span>
                                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                                               class="hidden" @change="handleFileSelect($event)">
                                    </label>

                                    {{-- Cancel selected preview --}}
                                    <button type="button" x-show="previewUrl" @click="clearPreview()"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                                        <i class="fas fa-undo text-[10px]"></i>
                                        <span>Cancel Preview</span>
                                    </button>

                                    {{-- Remove existing photo --}}
                                    @if($moderator->image)
                                    <button type="button" x-show="!removeImage && !previewUrl" @click="markRemoveImage()"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                        <span>Remove Current Photo</span>
                                    </button>
                                    @endif

                                    <template x-if="removeImage">
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-100 text-rose-700 rounded-xl text-xs font-bold">
                                            <span>Photo will be removed</span>
                                            <button type="button" @click="unmarkRemoveImage()" class="text-rose-900 underline text-[11px]">Cancel</button>
                                        </div>
                                    </template>
                                </div>

                                <input type="hidden" name="remove_image" :value="removeImage ? '1' : '0'">

                                <p class="text-[11px] text-slate-400">
                                    Supported formats: JPG, PNG, WEBP, or GIF (max 2MB). A square (1:1) photo works best.
                                </p>
                                @error('image')
                                    <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Full Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $moderator->name) }}" required
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        @error('name')
                            <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email & Phone Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Login Email
                            </label>
                            <input type="email" value="{{ $moderator->email }}" disabled
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 bg-slate-100/80 px-4 py-2.5 text-slate-500 cursor-not-allowed font-mono">
                            <span class="text-[10.5px] text-slate-400 mt-1 block">Contact your administrator to change your email address.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Phone Number
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $moderator->phone) }}"
                                   placeholder="01XXXXXXXXX"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono">
                            @error('phone')
                                <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Address
                        </label>
                        <textarea name="address" rows="3"
                                  placeholder="Your current address or location..."
                                  class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">{{ old('address', $moderator->address) }}</textarea>
                        @error('address')
                            <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-indigo-200 transition-all cursor-pointer">
                            <i class="fas fa-save"></i>
                            <span>Save Profile & Photo</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- Right 1 Column: Change Password & Security Info --}}
        <div class="space-y-6">

            {{-- Change Password Box --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold shadow-2xs">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-black text-slate-900">Change Password</h2>
                        <p class="text-[11px] text-slate-400">Update your password regularly to keep your account secure</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('moderator.password.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="current_password" required
                               placeholder="••••••••"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('current_password')
                            <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" required minlength="6"
                               placeholder="Minimum 6 characters"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('password')
                            <p class="text-xs text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                            Confirm New Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required minlength="6"
                               placeholder="Re-enter new password"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition-colors cursor-pointer">
                            <i class="fas fa-shield-alt"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Moderator Account Info Card --}}
            <div class="bg-gradient-to-br from-indigo-50/80 to-slate-50 rounded-3xl border border-indigo-100 p-5 space-y-3">
                <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs">
                    <i class="fas fa-info-circle text-indigo-500"></i>
                    <span>Account Guidelines & Instructions</span>
                </div>
                <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                    <li>A clear profile picture helps administrators quickly identify your shift reports.</li>
                    <li>Timers are automatically recorded when starting and stopping each work session.</li>
                    <li>Always submit a brief work report and summary upon concluding your shift.</li>
                </ul>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function profileManager() {
    return {
        existingImageUrl: @js($moderator->image_url),
        previewUrl: null,
        removeImage: false,

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.removeImage = false;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        clearPreview() {
            this.previewUrl = null;
        },

        markRemoveImage() {
            this.removeImage = true;
            this.previewUrl = null;
        },

        unmarkRemoveImage() {
            this.removeImage = false;
        }
    }
}
</script>
@endpush
