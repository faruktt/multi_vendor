@extends('shop.layout')
@section('main-class', 'max-w-6xl mx-auto px-4 py-6 sm:py-8')

@section('content')
<div class="space-y-6">

    {{-- Top Greeting Banner (Ultra-Premium) --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100/90 shadow-sm p-6 sm:p-7">
        {{-- Subtle decorative ambient background accents --}}
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-brand/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-amber-500/5 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            {{-- User Info --}}
            <div class="flex items-center gap-4 sm:gap-5">
                {{-- Avatar --}}
                <div class="relative flex-shrink-0">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden ring-4 ring-gray-100 shadow-md bg-gradient-to-br from-brand/20 to-brand/5 flex items-center justify-center">
                        @if($customer->avatar_url)
                            <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-brand text-white flex items-center justify-center text-2xl sm:text-3xl font-black">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Active"></span>
                </div>

                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">{{ $customer->name }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[11px] border border-emerald-200/80 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active Member
                        </span>
                    </div>

                    <div class="flex items-center gap-3.5 text-xs text-gray-500 mt-1.5 flex-wrap font-medium">
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fas fa-phone-alt text-[10px] text-brand"></i> {{ $customer->phone ?? 'No phone' }}
                        </span>
                        @if($customer->email)
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-envelope text-[10px] text-brand"></i> {{ $customer->email }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-gray-400">
                            <i class="fas fa-calendar-check text-[10px]"></i> Member since {{ $customer->created_at ? $customer->created_at->format('M Y') : 'Recent' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <a href="{{ route('shop.products.index') }}"
                   class="h-10 px-4 rounded-xl bg-gray-50 hover:bg-brand/10 text-gray-700 hover:text-brand border border-gray-200/80 text-xs font-bold transition-all flex items-center gap-2 shadow-xs">
                    <i class="fas fa-bag-shopping text-brand"></i> Continue Shopping
                </a>
                <a href="{{ route('shop.customer.profile') }}"
                   class="h-10 px-4 rounded-xl bg-brand/10 hover:bg-brand/20 text-brand text-xs font-bold transition-all flex items-center gap-2 border border-brand/20">
                    <i class="fas fa-pen-to-square"></i> Edit Profile
                </a>
                <form method="POST" action="{{ route('shop.customer.logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="h-10 px-4 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 border border-red-100 text-xs font-bold transition-all flex items-center gap-1.5">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Grid: Navigation Sidebar + Tab Content --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">

        {{-- Sidebar --}}
        <div class="md:col-span-1 space-y-4 md:sticky md:top-24">
            <div class="bg-white rounded-3xl border border-gray-100/90 p-3 shadow-sm space-y-1">
                <a href="{{ route('shop.customer.dashboard') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('shop.customer.dashboard') ? 'bg-brand text-white shadow-sm shadow-brand/20' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-layer-group text-sm w-4 text-center"></i>
                        <span>Dashboard</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                </a>

                <a href="{{ route('shop.customer.orders') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('shop.customer.orders*') ? 'bg-brand text-white shadow-sm shadow-brand/20' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-box-open text-sm w-4 text-center"></i>
                        <span>My Orders</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                </a>

                <a href="{{ route('shop.customer.profile') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('shop.customer.profile') ? 'bg-brand text-white shadow-sm shadow-brand/20' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-user-gear text-sm w-4 text-center"></i>
                        <span>Profile &amp; Photo</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                </a>

                <a href="{{ route('shop.track.index') }}"
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-location-crosshairs text-sm w-4 text-center"></i>
                        <span>Live Tracking</span>
                    </div>
                    <i class="fas fa-chevron-right text-[10px] opacity-60"></i>
                </a>
            </div>

            {{-- Support Card --}}
            <div class="bg-gradient-to-br from-brand/5 via-brand/10 to-amber-500/5 rounded-3xl border border-brand/15 p-4 text-center space-y-2">
                <div class="w-10 h-10 mx-auto rounded-xl bg-brand text-white flex items-center justify-center text-sm shadow-md shadow-brand/20">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-900">Need Help?</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Our support team is always ready to assist you</p>
                </div>
                @if(!empty($branch->whatsapp_number))
                    <a href="https://wa.me/{{ $branch->whatsapp_number }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 w-full h-8 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all shadow-xs">
                        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                    </a>
                @elseif(!empty($branch->phone))
                    <a href="tel:{{ $branch->phone }}"
                       class="inline-flex items-center justify-center gap-1.5 w-full h-8 rounded-xl bg-brand hover:bg-brand-dark text-white text-[11px] font-bold transition-all shadow-xs">
                        <i class="fas fa-phone"></i> Call Support
                    </a>
                @endif
            </div>
        </div>

        {{-- Main Account Content --}}
        <div class="md:col-span-3">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200/90 text-emerald-800 text-xs font-bold rounded-2xl p-4 mb-5 flex items-center gap-3 shadow-xs">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs flex-shrink-0">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-2xl p-4 mb-5 space-y-1.5 shadow-xs">
                    <div class="font-bold flex items-center gap-1.5 text-red-800">
                        <i class="fas fa-circle-exclamation"></i> Please check the form errors:
                    </div>
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-2 pl-4 text-red-600">
                            <span class="w-1 h-1 rounded-full bg-red-500"></span> {{ $error }}
                        </div>
                    @endforeach
                </div>
            @endif

            @yield('account_content')
        </div>

    </div>

</div>
@endsection
