@extends('shop.layout')
@section('title', 'Customer Login — ' . ($branch->system_name ?? $branch->name))
@section('main-class', 'w-full max-w-md mx-auto px-4 py-8')

@section('content')
<div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm p-6 sm:p-8">

    <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-brand/10 text-brand flex items-center justify-center mx-auto mb-3 text-xl font-bold shadow-sm">
            <i class="fas fa-user-circle"></i>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-gray-900">Customer Sign In</h1>
        <p class="text-xs text-gray-500 mt-1">Sign in to track orders, manage your profile and view order history</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-xl p-3.5 mb-5 space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-1.5"><i class="fas fa-circle-exclamation text-xs"></i> {{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl p-3.5 mb-5 flex items-center gap-1.5">
            <i class="fas fa-circle-check text-xs"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('shop.customer.login.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                Phone Number or Email Address *
            </label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                    <i class="fas fa-phone-alt"></i>
                </span>
                <input type="text" name="login" value="{{ old('login') }}" required autofocus
                       placeholder="e.g. 017XXXXXXXX or name@example.com"
                       class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Password *
                </label>
            </div>
            <div class="relative" x-data="{ showPass: false }">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                    <i class="fas fa-lock"></i>
                </span>
                <input :type="showPass ? 'text' : 'password'" name="password" required
                       placeholder="Enter your password"
                       class="w-full h-11 rounded-xl border border-gray-300 pl-10 pr-10 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand bg-gray-50/50">
                <button type="button" @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                    <i class="fas" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 cursor-pointer text-gray-600 font-medium">
                <input type="checkbox" name="remember" class="rounded text-brand focus:ring-brand border-gray-300">
                <span>Remember me</span>
            </label>
        </div>

        <button type="submit"
                class="w-full h-11 bg-brand hover:bg-brand-dark text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-brand/20 flex items-center justify-center gap-2">
            <i class="fas fa-sign-in-alt text-xs"></i> Sign In
        </button>
    </form>

    <div class="border-t border-gray-100 mt-6 pt-5 text-center">
        <p class="text-xs text-gray-500">
            Don't have an account yet?
            <a href="{{ route('shop.customer.register') }}" class="font-bold text-brand hover:underline">
                Create an Account &rarr;
            </a>
        </p>
    </div>

</div>
@endsection
