@extends('reseller.layouts.app')
@section('title', 'Profile Settings — প্রোফাইল সেটিংস')
@section('heading', 'Profile Settings / প্রোফাইল সেটিংস')

@section('content')
<div class="py-4 max-w-5xl mx-auto space-y-6">

    {{-- Flash Validation Errors --}}
    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-sm">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="fas fa-exclamation-circle text-rose-500"></i> তথ্যে কিছু ত্রুটি রয়েছে:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Avatar & Account Summary --}}
        <div class="space-y-6">
            
            {{-- Profile Card --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 text-center relative overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-24 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>

                <div class="relative z-10 pt-6">
                    <div class="inline-block relative">
                        @if($reseller->image_url)
                            <img src="{{ $reseller->image_url }}" alt="{{ $reseller->name }}"
                                 class="w-24 h-24 rounded-full object-cover ring-4 ring-white shadow-lg mx-auto">
                        @else
                            <div class="w-24 h-24 rounded-full bg-indigo-600 text-white font-extrabold text-3xl flex items-center justify-center ring-4 ring-white shadow-lg mx-auto">
                                {{ strtoupper(substr($reseller->name, 0, 1)) }}
                            </div>
                        @endif

                        @if($reseller->isActive())
                            <span class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full" title="Active"></span>
                        @endif
                    </div>

                    <h3 class="font-extrabold text-lg text-slate-800 mt-3">{{ $reseller->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $reseller->email }}</p>

                    @if($reseller->business_name)
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 mt-2">
                            <i class="fas fa-store text-[10px]"></i> {{ $reseller->business_name }}
                        </div>
                    @endif

                    <div class="border-t border-slate-100 mt-5 pt-4 text-left space-y-2 text-xs text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">রিসেলার আইডি:</span>
                            <span class="font-mono font-bold text-slate-700">#RES-{{ str_pad($reseller->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">একাউন্ট স্ট্যাটাস:</span>
                            @if($reseller->isActive())
                                <span class="font-bold text-emerald-600 flex items-center gap-1">
                                    <i class="fas fa-circle-check"></i> একটিভ
                                </span>
                            @else
                                <span class="font-bold text-amber-600 flex items-center gap-1">
                                    <i class="fas fa-clock"></i> পেন্ডিং
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">যোগদানের তারিখ:</span>
                            <span class="font-semibold text-slate-700">{{ $reseller->created_at->format('d M, Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('reseller.account') }}"
                           class="w-full py-2.5 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition">
                            <i class="fas fa-wallet"></i> My Account (বিস্তারিত হিসাব দেখুন)
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick Payout Stats Card --}}
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl p-5 text-white shadow-sm space-y-3">
                <div class="text-xs font-bold text-indigo-300 uppercase tracking-wider">উপলব্ধ ব্যালেন্স</div>
                <div class="text-2xl font-black">৳{{ number_format($reseller->available_balance, 2) }}</div>
                <div class="text-[11px] text-slate-300">
                    মোট অর্জিত লাভ: <strong class="text-white">৳{{ number_format($reseller->total_profit, 2) }}</strong>
                </div>
                <a href="{{ route('reseller.withdrawals.index') }}"
                   class="inline-block mt-2 w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-center font-bold text-xs rounded-xl shadow-xs transition">
                    টাকা উত্তোলনের রিকোয়েস্ট দিন
                </a>
            </div>

        </div>

        {{-- Right Column: Edit Profile & Change Password Forms --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Edit Profile Information & Photo Form --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6"
                 x-data="{
                    imagePreview: null,
                    removeImage: false,
                    previewFile(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.imagePreview = e.target.result;
                                this.removeImage = false;
                            };
                            reader.readAsDataURL(file);
                        }
                    },
                    clearPhoto() {
                        this.imagePreview = null;
                        this.removeImage = true;
                        $refs.fileInput.value = '';
                    }
                 }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-base">প্রোফাইল তথ্য ও ছবি পরিবর্তন</h3>
                        <p class="text-xs text-slate-400 mt-0.5">আপনার নাম, ফোন, ব্যবসার নাম, ঠিকানা এবং প্রোফাইল ছবি আপডেট করুন</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-user-pen"></i>
                    </div>
                </div>

                <form method="POST" action="{{ route('reseller.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Image Upload Section --}}
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <label class="block text-xs font-bold text-slate-700">প্রোফাইল ছবি (Avatar Photo)</label>

                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            {{-- Photo preview box --}}
                            <div class="relative flex-shrink-0">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" alt="Preview"
                                         class="w-20 h-20 rounded-2xl object-cover ring-2 ring-indigo-500 shadow-sm">
                                </template>

                                <template x-if="!imagePreview && !removeImage">
                                    @if($reseller->image_url)
                                        <img src="{{ $reseller->image_url }}" alt="{{ $reseller->name }}"
                                             class="w-20 h-20 rounded-2xl object-cover ring-2 ring-slate-200 shadow-sm">
                                    @else
                                        <div class="w-20 h-20 rounded-2xl bg-indigo-100 text-indigo-700 font-bold text-2xl flex items-center justify-center">
                                            {{ strtoupper(substr($reseller->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </template>

                                <template x-if="!imagePreview && removeImage">
                                    <div class="w-20 h-20 rounded-2xl bg-slate-200 text-slate-500 font-bold text-xs flex items-center justify-center text-center p-2">
                                        No Image
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-2 flex-1 text-center sm:text-left">
                                <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                                    {{-- File Input Trigger Button --}}
                                    <button type="button"
                                            @click="$refs.fileInput.click()"
                                            class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                                        <i class="fas fa-upload"></i> নতুন ছবি পছন্দ করুন
                                    </button>

                                    {{-- Remove button --}}
                                    @if($reseller->image)
                                        <button type="button"
                                                @click="clearPhoto()"
                                                class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition flex items-center gap-1 border border-rose-200">
                                            <i class="fas fa-trash-can"></i> ছবি মুছে ফেলুন
                                        </button>
                                    @endif
                                </div>

                                <input type="file"
                                       name="image"
                                       x-ref="fileInput"
                                       @change="previewFile"
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       class="hidden">

                                <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">

                                <p class="text-[11px] text-slate-400">
                                    সমর্থিত ফরম্যাট: JPG, PNG, WEBP। সর্বোচ্চ সাইজ: 2MB। পরিষ্কার স্কয়ার সাইজের ছবি সবচেয়ে ভালো দেখায়।
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        {{-- Name --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">আপনার নাম <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $reseller->name) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ফোন নম্বর</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone', $reseller->phone) }}"
                                   placeholder="01XXXXXXXXX"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>

                        {{-- Business Name --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ব্যবসার নাম / পেইজের নাম</label>
                            <input type="text"
                                   name="business_name"
                                   value="{{ old('business_name', $reseller->business_name) }}"
                                   placeholder="যেমন: Fayaz Fashion House"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>

                        {{-- Email (Readonly) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ইমেইল এড্রেস</label>
                            <input type="email"
                                   value="{{ $reseller->email }}"
                                   disabled
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-100 text-slate-500 cursor-not-allowed">
                            <span class="text-[10px] text-slate-400 mt-1 block">লগইন ইমেইল পরিবর্তনযোগ্য নয়</span>
                        </div>

                    </div>

                    {{-- Address --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">আপনার ঠিকানা / শপ লোকেশন</label>
                        <textarea name="address"
                                  rows="2"
                                  placeholder="আপনার বিস্তারিত ঠিকানা লিখুন..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">{{ old('address', $reseller->address) }}</textarea>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition">
                            <i class="fas fa-check"></i> প্রোফাইল সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. Change Password Form --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-base">পাসওয়ার্ড পরিবর্তন</h3>
                        <p class="text-xs text-slate-400 mt-0.5">নিরাপত্তার জন্য নিয়মিত নতুন এবং শক্তিশালী পাসওয়ার্ড ব্যবহার করুন</p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <form method="POST" action="{{ route('reseller.password.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">বর্তমান পাসওয়ার্ড <span class="text-rose-500">*</span></label>
                        <input type="password"
                               name="current_password"
                               required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">নতুন পাসওয়ার্ড <span class="text-rose-500">*</span></label>
                            <input type="password"
                                   name="password"
                                   required
                                   placeholder="কমপক্ষে ৬ অক্ষর"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">নতুন পাসওয়ার্ড নিশ্চিত করুন <span class="text-rose-500">*</span></label>
                            <input type="password"
                                   name="password_confirmation"
                                   required
                                   placeholder="পুনরায় পাসওয়ার্ড লিখুন"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition">
                            <i class="fas fa-key"></i> পাসওয়ার্ড আপডেট করুন
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
