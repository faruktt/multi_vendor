@extends('shop.account.layout')
@section('title', 'My Orders — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Filter Header --}}
    <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">Order History</h2>
            <p class="text-xs text-gray-400 mt-0.5">Track, review and view digital invoices for all your purchases</p>
        </div>

        <form method="GET" action="{{ route('shop.customer.orders') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()"
                    class="h-10 border border-gray-200 rounded-xl px-3.5 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-white focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-brand transition-all">
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
            <div class="p-5 sm:p-6 hover:bg-gray-50/50 transition-colors space-y-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-brand/10 text-brand flex items-center justify-center font-bold text-sm border border-brand/15">
                            <i class="fas fa-receipt"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-black text-gray-900 text-sm">#{{ $order->invoice_no }}</span>
                                <span class="text-xs text-gray-400">• {{ $order->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $order->saleItems->count() }} item(s) ordered
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold capitalize
                            {{ $order->order_status === 'delivered' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                              ($order->order_status === 'cancelled' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $order->order_status === 'delivered' ? 'bg-emerald-500' : ($order->order_status === 'cancelled' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                            {{ $order->order_status }}
                        </span>

                        <a href="{{ route('shop.customer.orders.show', $order->id) }}"
                           class="h-9 px-4 rounded-xl bg-brand text-white hover:bg-brand-dark text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-brand/20">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                {{-- Preview Items --}}
                <div class="bg-gray-50/80 rounded-2xl p-3.5 flex flex-wrap gap-2 items-center text-xs text-gray-600 border border-gray-100/80">
                    <span class="font-bold text-gray-700 mr-1">Products:</span>
                    @foreach($order->saleItems as $item)
                        <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200/80 px-2.5 py-1 rounded-lg font-medium text-[11px] shadow-2xs">
                            <i class="fas fa-tag text-[9px] text-brand"></i>
                            <span>{{ $item->product->name ?? 'Product' }}</span>
                            <span class="text-gray-400 font-bold">x{{ $item->quantity }}</span>
                        </span>
                    @endforeach
                    <div class="ml-auto font-black text-gray-900 text-sm">
                        Total: <span class="text-brand">৳{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-gray-400">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-gray-50 text-gray-300 flex items-center justify-center text-2xl mb-3 border border-gray-100">
                    <i class="fas fa-box-open"></i>
                </div>
                <h4 class="text-sm font-bold text-gray-700">No orders found</h4>
                <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">When you place orders, they will appear here with full invoice and tracking details.</p>
                <a href="{{ route('shop.products.index') }}"
                   class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-dark text-white text-xs font-bold shadow-md shadow-brand/20 transition-all">
                    Start Shopping
                </a>
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
