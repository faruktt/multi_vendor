@extends('shop.layout')
@section('main-class', 'max-w-6xl mx-auto px-4 py-6 sm:py-8')

@section('content')
<div class="space-y-6">

    {{-- Top Greeting Card --}}
    <div class="bg-white rounded-3xl border border-gray-200/80 p-5 sm:p-6 shadow-sm flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-brand/10 text-brand flex items-center justify-center text-xl font-bold border border-brand/20 flex-shrink-0">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900">{{ $customer->name }}</h1>
                <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5 flex-wrap font-medium">
                    <span><i class="fas fa-phone-alt text-[10px] text-brand mr-1"></i> {{ $customer->phone ?? 'No phone' }}</span>
                    @if($customer->email)
                        <span><i class="fas fa-envelope text-[10px] text-brand mr-1"></i> {{ $customer->email }}</span>
                    @endif
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active Customer
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('shop.products.index') }}"
               class="h-10 px-4 rounded-xl bg-brand/10 hover:bg-brand/20 text-brand text-xs font-bold transition-all flex items-center gap-1.5">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
            <form method="POST" action="{{ route('shop.customer.logout') }}">
                @csrf
                <button type="submit"
                        class="h-10 px-4 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold transition-all flex items-center gap-1.5">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Main Grid: Navigation Sidebar + Tab Content --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        {{-- Sidebar --}}
        <div class="md:col-span-1 space-y-2">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-3 shadow-sm space-y-1">
                <a href="{{ route('shop.customer.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('shop.customer.dashboard') ? 'bg-brand text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="fas fa-th-large text-sm w-4"></i> Dashboard
                </a>

                <a href="{{ route('shop.customer.orders') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('shop.customer.orders*') ? 'bg-brand text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="fas fa-box text-sm w-4"></i> My Orders
                </a>

                <a href="{{ route('shop.customer.profile') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('shop.customer.profile') ? 'bg-brand text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="fas fa-id-card text-sm w-4"></i> Profile &amp; Security
                </a>

                <a href="{{ route('shop.track.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition-all">
                    <i class="fas fa-location-crosshairs text-sm w-4"></i> Live Tracking
                </a>
            </div>
        </div>

        {{-- Main Account Content --}}
        <div class="md:col-span-3">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl p-4 mb-4 flex items-center gap-2">
                    <i class="fas fa-circle-check text-emerald-600 text-sm"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-2xl p-4 mb-4 space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-center gap-1.5"><i class="fas fa-circle-exclamation"></i> {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('account_content')
        </div>

    </div>

</div>
@endsection
