@extends('shop.layout')
@section('title', 'Customer Registration — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full max-w-lg mx-auto px-4 py-8')

@section('content')
<div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm p-6 sm:p-8">

    <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-brand/10 text-brand flex items-center justify-center mx-auto mb-3 text-xl font-bold shadow-sm">
            <i class="fas fa-user-plus"></i>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-gray-900">Create Customer Account</h1>
        <p class="text-xs text-gray-500 mt-1">Join to track your deliveries, save addresses and enjoy fast checkouts</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-xl p-3.5 mb-5 space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-1.5"><i class="fas fa-circle-exclamation text-xs"></i> {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('shop.customer.register.submit') }}" class="space-y-4">
        @csrf

        {{-- Name --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                Full Name *
            </label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                    <i class="fas fa-user"></i>
                </span>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="e.g. Mohammad Ali"
                       class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
            </div>
        </div>

        {{-- Phone & Email --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Phone Number *
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                        <i class="fas fa-phone-alt"></i>
                    </span>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           placeholder="017XXXXXXXX"
                           class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Email Address (Optional)
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="name@example.com"
                           class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
                </div>
            </div>
        </div>

        {{-- Delivery Address --}}
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                Delivery Address (Optional)
            </label>
            <textarea name="address" rows="2"
                      placeholder="House, Road, Area, City"
                      class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50 resize-none">{{ old('address') }}</textarea>
        </div>

        {{-- Password & Confirmation --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ showPass: false }">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Password *
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input :type="showPass ? 'text' : 'password'" name="password" required
                           placeholder="Min 6 characters"
                           class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-10 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
                    <button type="button" @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                        <i class="fas" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Confirm Password *
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                        <i class="fas fa-lock-open"></i>
                    </span>
                    <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required
                           placeholder="Re-enter password"
                           class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
                </div>
            </div>
        </div>

        <button type="submit"
                class="w-full h-11 bg-brand hover:bg-brand-dark text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-brand/20 flex items-center justify-center gap-2 mt-2">
            <i class="fas fa-user-check text-xs"></i> Complete Registration
        </button>
    </form>

    <div class="border-t border-gray-100 mt-6 pt-5 text-center">
        <p class="text-xs text-gray-500">
            Already have an account?
            <a href="{{ route('shop.customer.login') }}" class="font-bold text-brand hover:underline">
                Sign In &rarr;
            </a>
        </p>
    </div>

</div>
@endsection
