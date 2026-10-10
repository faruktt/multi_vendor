@extends('shop.layout')
@section('title', 'Sign In — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full max-w-md mx-auto px-4 py-10 sm:py-16')

@section('content')
<div class="bg-white rounded-3xl border border-gray-100 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.08),0_10px_25px_-5px_rgba(0,0,0,0.04)] overflow-hidden ring-1 ring-black/[0.03]">

    {{-- Top Luxury Emerald Accent Bar --}}
    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700"></div>

    <div class="p-6 sm:p-9">

        {{-- Clean Confident Header --}}
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Sign In</h1>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm shadow-xs border border-emerald-100/60">
                <i class="fas fa-arrow-right-to-bracket"></i>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-100 text-rose-700 text-xs rounded-xl p-3.5 mb-5 space-y-1">
                @foreach($errors->all() as $error)
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 flex-shrink-0"></span>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl p-3.5 mb-5 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('shop.customer.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                    Phone or Email
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="01XXXXXXXXX or email"
                           class="w-full h-11 rounded-xl border border-gray-200/90 pl-10 pr-3.5 text-sm text-gray-900 bg-slate-50/50 hover:border-gray-300 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all outline-none">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-700">
                        Password
                    </label>
                </div>
                <div class="relative" x-data="{ showPass: false }">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input :type="showPass ? 'text' : 'password'" name="password" required
                           placeholder="••••••••"
                           class="w-full h-11 rounded-xl border border-gray-200/90 pl-10 pr-10 text-sm text-gray-900 bg-slate-50/50 hover:border-gray-300 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all outline-none">
                    <button type="button" @click="showPass = !showPass"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs p-1">
                        <i class="far" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-gray-600 select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-700 focus:ring-emerald-600/20 border-gray-300 accent-emerald-700">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full h-11 bg-gradient-to-r from-emerald-700 via-emerald-800 to-emerald-900 hover:from-emerald-800 hover:via-emerald-900 hover:to-emerald-950 active:scale-[0.99] text-white rounded-xl text-sm font-semibold tracking-wide transition-all shadow-md shadow-emerald-900/20 mt-2">
                Sign In
            </button>
        </form>

        <div class="border-t border-gray-100 mt-6 pt-5 text-center">
            <p class="text-xs text-gray-500">
                Don't have an account?
                <a href="{{ route('shop.customer.register') }}" class="font-bold text-emerald-800 hover:text-emerald-950 underline decoration-emerald-800/30 underline-offset-4 hover:decoration-emerald-950 ml-1">
                    Register
                </a>
            </p>
        </div>

    </div>

</div>
@endsection
