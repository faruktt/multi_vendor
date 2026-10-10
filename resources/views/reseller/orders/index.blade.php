@extends('reseller.layouts.app')
@section('title', 'My Orders')
@section('heading', 'My Orders')

@section('content')
<div class="py-4">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div class="min-w-[140px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
            <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                <option value="">All</option>
                @foreach(['pending','confirmed','shipped','delivered','cancelled','return'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50"></div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
    </form>

    {{-- Table --}}
    @php
        $statusColors = [
            'pending'   => 'bg-yellow-100 text-yellow-700',
            'confirmed' => 'bg-blue-100 text-blue-700',
            'shipped'   => 'bg-purple-100 text-purple-700',
            'delivered' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-red-100 text-red-700',
            'return'    => 'bg-rose-100 text-rose-700 font-bold',
            'returned'  => 'bg-rose-100 text-rose-700 font-bold',
        ];
    @endphp

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Invoice</th>
                        <th class="px-5 py-3 text-left">Customer</th>
                        <th class="px-5 py-3 text-left">Branch</th>
                        <th class="px-5 py-3 text-left">Items</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3">
                                <span class="font-mono font-bold text-indigo-600">{{ $order->invoice_no }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-800">{{ $order->customer?->name ?? '—' }}</div>
                                @if($order->customer?->phone)
                                    <div class="text-xs text-slate-400 font-mono">{{ $order->customer->phone }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $order->vendor->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $order->saleItems->count() }} item(s)</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-700">৳{{ number_format($order->total, 0) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->order_status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                                @if(in_array($order->order_status, ['return', 'returned']))
                                    <div class="text-[11px] text-rose-600 font-bold mt-1">
                                        -৳{{ number_format($order->delivery_charge, 0) }} Fee Deducted
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('reseller.orders.show', $order) }}"
                                       class="inline-flex items-center gap-1 text-slate-600 hover:text-indigo-600 bg-slate-100 hover:bg-indigo-50 px-2.5 py-1 rounded-lg text-xs font-semibold transition"
                                       title="View Order Details">
                                        <i class="fas fa-eye text-[10px]"></i> View
                                    </a>
                                    <a href="{{ route('reseller.orders.invoice', $order) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg text-xs font-bold transition"
                                       title="Print Invoice">
                                        <i class="fas fa-print text-[10px]"></i> Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            <i class="fas fa-receipt text-3xl mb-3 block text-slate-200"></i>
                            No orders yet
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
