@extends('shop.account.layout')
@section('title', 'My Account — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="space-y-6">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Orders</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-bag-shopping"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900">{{ $totalOrders }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5">All time orders placed</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Spent</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-bangladeshi-taka-sign"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">৳{{ number_format($totalSpent, 0) }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Purchases value</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">In Progress</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-clock"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600">{{ $pendingOrders }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Pending processing</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Delivered</span>
                <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fas fa-circle-check"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-purple-600">{{ $deliveredOrders }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Completed orders</div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 text-sm sm:text-base">Recent Orders</h3>
                <p class="text-xs text-gray-400">Your latest purchases from {{ $branch->system_name ?? $branch->name }}</p>
            </div>
            <a href="{{ route('shop.customer.orders') }}" class="text-xs font-bold text-brand hover:underline">
                View All Orders &rarr;
            </a>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($recentOrders as $order)
                <div class="p-4 sm:p-5 hover:bg-gray-50/50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center font-bold text-xs flex-shrink-0">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-bold text-gray-900 text-sm">{{ $order->invoice_no }}</span>
                                <span class="text-xs text-gray-400">• {{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $order->saleItems->count() }} item(s) — Total: <strong class="text-gray-900">৳{{ number_format($order->total, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold capitalize
                            {{ $order->order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                              ($order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $order->order_status === 'delivered' ? 'bg-emerald-600' : ($order->order_status === 'cancelled' ? 'bg-red-600' : 'bg-amber-600') }}"></span>
                            {{ $order->order_status }}
                        </span>

                        <a href="{{ route('shop.customer.orders.show', $order->id) }}"
                           class="h-8 px-3 rounded-lg bg-gray-100 hover:bg-brand hover:text-white text-gray-700 text-xs font-bold transition-all flex items-center gap-1">
                            Details <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <i class="fas fa-shopping-bag text-3xl mb-2 text-gray-300"></i>
                    <p class="text-sm font-semibold text-gray-500">No orders placed yet</p>
                    <p class="text-xs text-gray-400 mt-1">Browse our store and place your first order!</p>
                    <a href="{{ route('shop.products.index') }}"
                       class="inline-block mt-4 px-4 py-2 rounded-xl bg-brand text-white text-xs font-bold shadow-md shadow-brand/20">
                        Start Shopping
                    </a>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
