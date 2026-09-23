@extends('shop.account.layout')
@section('title', 'My Orders — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">

    {{-- Filter Header --}}
    <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-gray-900">Order History</h2>
            <p class="text-xs text-gray-400">Track and view invoices for all your purchases</p>
        </div>

        <form method="GET" action="{{ route('shop.customer.orders') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()"
                    class="h-9 border border-gray-200 rounded-xl px-3 text-xs font-semibold text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </form>
    </div>

    {{-- Orders List --}}
    <div class="divide-y divide-gray-100">
        @forelse($orders as $order)
            <div class="p-4 sm:p-5 hover:bg-gray-50/50 transition-colors space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center font-bold text-xs">
                            <i class="fas fa-receipt"></i>
                        </span>
                        <div>
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $order->invoice_no }}</span>
                            <span class="text-xs text-gray-400 block sm:inline sm:ml-2">Ordered on {{ $order->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold capitalize
                            {{ $order->order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                              ($order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $order->order_status === 'delivered' ? 'bg-emerald-600' : ($order->order_status === 'cancelled' ? 'bg-red-600' : 'bg-amber-600') }}"></span>
                            {{ $order->order_status }}
                        </span>

                        <a href="{{ route('shop.customer.orders.show', $order->id) }}"
                           class="h-8 px-3.5 rounded-lg bg-brand text-white hover:bg-brand-dark text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                            View Order <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                {{-- Preview Items --}}
                <div class="bg-gray-50/80 rounded-xl p-3 flex flex-wrap gap-2 items-center text-xs text-gray-600">
                    <span class="font-bold text-gray-700">Items:</span>
                    @foreach($order->saleItems as $item)
                        <span class="inline-flex items-center gap-1 bg-white border border-gray-200 px-2 py-0.5 rounded-md font-medium text-[11px]">
                            {{ $item->product->name ?? 'Product' }} (x{{ $item->quantity }})
                        </span>
                    @endforeach
                    <div class="ml-auto font-black text-gray-900 text-sm">
                        Total: ৳{{ number_format($order->total, 2) }}
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-gray-400">
                <i class="fas fa-box-open text-4xl mb-2 text-gray-300"></i>
                <p class="text-sm font-semibold text-gray-500">No orders found</p>
                <p class="text-xs text-gray-400 mt-1">When you place orders, they will show up here.</p>
            </div>
        @endforelse
    </div>

    @if($orders->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
