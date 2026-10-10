@extends('shop.account.layout')
@section('title', 'My Account — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="space-y-6">

    {{-- Stats Cards (Ultra-Premium Gradients & Shadows) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        {{-- Total Orders --}}
        <div class="relative overflow-hidden bg-white rounded-3xl border border-gray-100 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Total Orders</span>
                <span class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fas fa-bag-shopping"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">{{ $totalOrders }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                <i class="fas fa-chart-line text-[10px] text-blue-500"></i> Lifetime purchases
            </div>
        </div>

        {{-- Total Spent --}}
        <div class="relative overflow-hidden bg-white rounded-3xl border border-gray-100 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Total Spent</span>
                <span class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fas fa-bangladeshi-taka-sign"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 tracking-tight">৳{{ number_format($totalSpent, 0) }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                <i class="fas fa-circle-check text-[10px] text-emerald-500"></i> Successful orders
            </div>
        </div>

        {{-- In Progress --}}
        <div class="relative overflow-hidden bg-white rounded-3xl border border-gray-100 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">In Progress</span>
                <span class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fas fa-clock-rotate-left"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 tracking-tight">{{ $pendingOrders }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                <i class="fas fa-spinner text-[10px] text-amber-500"></i> Processing queue
            </div>
        </div>

        {{-- Delivered --}}
        <div class="relative overflow-hidden bg-white rounded-3xl border border-gray-100 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider">Delivered</span>
                <span class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform shadow-xs">
                    <i class="fas fa-circle-check"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-purple-600 tracking-tight">{{ $deliveredOrders }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                <i class="fas fa-truck text-[10px] text-purple-500"></i> Completed deliveries
            </div>
        </div>
    </div>

    {{-- Quick Shortcuts --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <a href="{{ route('shop.customer.orders') }}"
           class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs hover:border-brand/40 hover:shadow-sm transition-all flex items-center gap-3.5 group">
            <span class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center text-sm group-hover:bg-brand group-hover:text-white transition-all">
                <i class="fas fa-receipt"></i>
            </span>
            <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 group-hover:text-brand transition-colors">Order History</div>
                <div class="text-[11px] text-gray-400">View all past purchases</div>
            </div>
        </a>

        <a href="{{ route('shop.track.index') }}"
           class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs hover:border-brand/40 hover:shadow-sm transition-all flex items-center gap-3.5 group">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm group-hover:bg-emerald-600 group-hover:text-white transition-all">
                <i class="fas fa-truck-fast"></i>
            </span>
            <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Track Order</div>
                <div class="text-[11px] text-gray-400">Check live parcel status</div>
            </div>
        </a>

        <a href="{{ route('shop.customer.profile') }}"
           class="p-4 rounded-2xl bg-white border border-gray-100 shadow-xs hover:border-brand/40 hover:shadow-sm transition-all flex items-center gap-3.5 group">
            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm group-hover:bg-purple-600 group-hover:text-white transition-all">
                <i class="fas fa-id-card"></i>
            </span>
            <div class="min-w-0">
                <div class="text-xs font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Profile &amp; Photo</div>
                <div class="text-[11px] text-gray-400">Update photo and address</div>
            </div>
        </a>
    </div>

    {{-- Recent Orders Card --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="font-black text-gray-900 text-base tracking-tight">Recent Orders</h3>
                <p class="text-xs text-gray-400 mt-0.5">Your recent transactions from {{ $branch->system_name ?? $branch->name }}</p>
            </div>
            <a href="{{ route('shop.customer.orders') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gray-50 hover:bg-brand/10 text-gray-700 hover:text-brand text-xs font-bold transition-all border border-gray-200/80">
                <span>All Orders</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($recentOrders as $order)
                <div class="p-4 sm:p-5 hover:bg-gray-50/60 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-brand/10 text-brand flex items-center justify-center font-bold text-sm flex-shrink-0 border border-brand/15">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-black text-gray-900 text-sm">#{{ $order->invoice_no }}</span>
                                <span class="text-xs text-gray-400">• {{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                <span>{{ $order->saleItems->count() }} item(s)</span>
                                <span class="text-gray-300">|</span>
                                <span>Total: <strong class="text-gray-900 font-bold">৳{{ number_format($order->total, 2) }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold capitalize
                            {{ $order->order_status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                              ($order->order_status === 'cancelled' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $order->order_status === 'delivered' ? 'bg-emerald-500' : ($order->order_status === 'cancelled' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                            {{ $order->order_status }}
                        </span>

                        <a href="{{ route('shop.customer.orders.show', $order->id) }}"
                           class="h-8 px-3.5 rounded-xl bg-gray-100 hover:bg-brand hover:text-white text-gray-700 text-xs font-bold transition-all flex items-center gap-1.5 shadow-2xs">
                            <span>Details</span>
                            <i class="fas fa-chevron-right text-[9px]"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-gray-400">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-gray-50 text-gray-300 flex items-center justify-center text-2xl mb-3 border border-gray-100">
                        <i class="fas fa-bag-shopping"></i>
                    </div>
                    <h4 class="text-sm font-bold text-gray-700">No orders placed yet</h4>
                    <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">Explore our exclusive collections and place your first order with ease.</p>
                    <a href="{{ route('shop.products.index') }}"
                       class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-dark text-white text-xs font-bold shadow-md shadow-brand/20 transition-all">
                        <i class="fas fa-shopping-cart text-[11px]"></i> Start Shopping
                    </a>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
