@extends('layouts.app')
@section('title', 'Reseller Orders')
@section('heading', 'Reseller Orders')

@section('content')
<div class="py-4 space-y-6">

    {{-- Sub Navigation Tabs --}}
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.resellers.index') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-users mr-1.5"></i> Resellers
        </a>
        <a href="{{ route('admin.resellers.orders') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.orders') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-shopping-bag mr-1.5"></i> Reseller Orders
        </a>
        <a href="{{ route('admin.resellers.withdrawals') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-wallet mr-1.5"></i> Withdraw Requests
            @php $pendingWithdrawalsTabCount = \App\Models\ResellerWithdrawal::where('status', 'pending')->count(); @endphp
            @if($pendingWithdrawalsTabCount > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ $pendingWithdrawalsTabCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.resellers.report') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.report') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-chart-pie mr-1.5"></i> Reseller Report
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Reseller</label>
                <select name="reseller_id" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    <option value="">All Resellers</option>
                    @foreach($resellers as $r)
                        <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->name }} {{ $r->business_name ? "({$r->business_name})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    <option value="">All Status</option>
                    @foreach(['pending','processing','sent_to_courier','out_for_delivery','delivered','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['reseller_id', 'status', 'from', 'to']))
                    <a href="{{ route('admin.resellers.orders') }}" title="Reset" class="h-11 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-bold transition-colors flex items-center justify-center">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $statusColors = [
            'pending'          => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'processing'       => 'bg-blue-100 text-blue-800 border-blue-200',
            'sent_to_courier'  => 'bg-orange-100 text-orange-800 border-orange-200',
            'out_for_delivery' => 'bg-purple-100 text-purple-800 border-purple-200',
            'delivered'        => 'bg-teal-100 text-teal-800 border-teal-200',
            'completed'        => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'cancelled'        => 'bg-red-100 text-red-800 border-red-200'
        ];
    @endphp

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="lg:hidden flex items-center justify-between px-3.5 py-2 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100/60 text-[11px] text-purple-800 font-medium">
            <span class="flex items-center gap-1.5"><i class="fas fa-arrows-left-right text-purple-600 animate-pulse"></i> Scroll horizontally to view all details</span>
            <span class="text-purple-700/80 font-mono text-[10px]">{{ $orders->total() }} records</span>
        </div>
        <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
            <table class="w-full text-xs sm:text-sm">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wider font-bold">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Invoice</th>
                        <th class="px-5 py-3.5 text-left">Reseller</th>
                        <th class="px-5 py-3.5 text-left">Branch</th>
                        <th class="px-5 py-3.5 text-right">Customer Total</th>
                        <th class="px-5 py-3.5 text-right">Reseller Profit</th>
                        <th class="px-5 py-3.5 text-left">Status</th>
                        <th class="px-5 py-3.5 text-left">Delivery Info</th>
                        <th class="px-5 py-3.5 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-bold text-indigo-600">{{ $order->invoice_no }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('admin.resellers.index', ['reseller_id' => $order->reseller_id]) }}" class="font-bold text-slate-800 hover:text-indigo-600">
                                    {{ $order->reseller->name ?? '—' }}
                                </a>
                                <div class="text-xs text-slate-400">{{ $order->reseller->business_name ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-medium">{{ $order->vendor->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-right font-black text-slate-800">৳{{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-3.5 text-right font-black text-emerald-600">৳{{ number_format($order->reseller_profit, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusColors[$order->order_status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs">
                                @if($order->customer)
                                    <div class="font-bold text-slate-800">{{ $order->customer->name }}</div>
                                    @if($order->customer->phone)
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $order->customer->phone }}</div>
                                    @endif
                                    @if($order->district || $order->customer->district)
                                        <div class="text-[11px] text-indigo-600 font-medium truncate max-w-[220px]">
                                            {{ implode(' · ', array_filter([$order->district ?? $order->customer->district, $order->thana ?? $order->customer->thana])) }}
                                        </div>
                                    @endif
                                    @if($order->customer->address)
                                        <div class="text-[11px] text-slate-400 truncate max-w-[220px]" title="{{ $order->customer->address }}">
                                            {{ $order->customer->address }}
                                        </div>
                                    @endif
                                @elseif($order->note)
                                    <div class="text-slate-600 truncate max-w-[220px]" title="{{ $order->note }}">
                                        {{ $order->note }}
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 text-xs font-medium">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box-open text-4xl mb-2 text-slate-300"></i>
                                    <span class="font-semibold text-slate-500">No reseller orders found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
