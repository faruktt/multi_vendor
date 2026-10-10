@extends('reseller.layouts.app')
@section('title', 'My Account — Financial Summary')
@section('heading', 'My Account')

@section('content')
<div class="py-4 space-y-6">

    {{-- Reseller Profile & Account Banner --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 sm:p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-72 h-72 bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-transparent rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="relative group">
                    @if($reseller->image_url)
                        <img src="{{ $reseller->image_url }}" alt="{{ $reseller->name }}"
                             class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-indigo-50 shadow-md">
                    @else
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-extrabold text-3xl flex items-center justify-center ring-4 ring-indigo-50 shadow-md">
                            {{ strtoupper(substr($reseller->name, 0, 1)) }}
                        </div>
                    @endif
                    <a href="{{ route('reseller.profile') }}"
                       class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-sm hover:bg-indigo-700 transition"
                       title="Change Profile Photo">
                        <i class="fas fa-camera"></i>
                    </a>
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-800">{{ $reseller->name }}</h2>
                        @if($reseller->isActive())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active Reseller
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Approval
                            </span>
                        @endif
                    </div>

                    @if($reseller->business_name)
                        <div class="text-sm font-semibold text-indigo-600 flex items-center gap-1.5">
                            <i class="fas fa-store text-xs"></i> {{ $reseller->business_name }}
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 pt-0.5">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-envelope text-slate-400"></i> {{ $reseller->email }}
                        </span>
                        @if($reseller->phone)
                            <span class="flex items-center gap-1">
                                <i class="fas fa-phone text-slate-400"></i> {{ $reseller->phone }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <i class="fas fa-id-badge text-slate-400"></i> ID: #RES-{{ str_pad($reseller->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-calendar-alt text-slate-400"></i> Member Since: {{ $reseller->created_at->format('d M, Y') }}
                        </span>
                    </div>

                    @if($reseller->address)
                        <div class="text-xs text-slate-500 flex items-start gap-1 pt-0.5">
                            <i class="fas fa-location-dot text-slate-400 mt-0.5"></i>
                            <span>{{ $reseller->address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex flex-wrap items-center gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                <a href="{{ route('reseller.withdrawals.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition">
                    <i class="fas fa-hand-holding-dollar"></i> Withdraw Funds
                </a>
                <a href="{{ route('reseller.profile') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    <i class="fas fa-user-pen"></i> Edit Profile
                </a>
                <a href="{{ route('reseller.orders.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition">
                    <i class="fas fa-plus"></i> New Order
                </a>
            </div>
        </div>
    </div>

    {{-- Main Financial Highlights (Total Order, Profit, Withdraw, Balances) --}}
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fas fa-wallet text-indigo-600"></i> Financial Summary
            </h3>
            <span class="text-xs text-slate-400">Live Updates</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- 1. Total Orders --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm relative overflow-hidden group hover:border-indigo-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Orders</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-shopping-bag text-base"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-black text-slate-800">{{ number_format($totalOrders) }}</div>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-[11px] font-medium text-slate-500">
                        <span class="text-emerald-600 font-bold"><i class="fas fa-check-circle text-[10px]"></i> {{ $completedOrdersCount }} Completed</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-amber-600 font-semibold">{{ $processingOrdersCount }} In Progress</span>
                    </div>
                </div>
            </div>

            {{-- 2. Total Profit --}}
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 shadow-sm text-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-15">
                    <i class="fas fa-sack-dollar text-8xl"></i>
                </div>
                <div class="relative z-10 flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Total Profit</span>
                    <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center backdrop-blur-xs">
                        <i class="fas fa-chart-line text-base"></i>
                    </div>
                </div>
                <div class="relative z-10 mt-3">
                    <div class="text-3xl font-black">৳{{ number_format($totalProfit, 2) }}</div>
                    <div class="text-[11px] text-emerald-100/90 mt-2 flex items-center gap-1">
                        <i class="fas fa-coins text-[10px]"></i> Earned from completed orders
                    </div>
                </div>
            </div>

            {{-- 3. Total Withdrawn --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm relative overflow-hidden group hover:border-orange-200 transition-all">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Withdrawn</span>
                    <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-money-bill-transfer text-base"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-3xl font-black text-slate-800">৳{{ number_format($totalWithdrawn, 2) }}</div>
                    <div class="text-[11px] font-medium text-slate-500 mt-2 flex items-center gap-1">
                        <span class="text-orange-600 font-semibold">Successfully Disbursed</span>
                    </div>
                </div>
            </div>

            {{-- 4. Available Balance --}}
            <div class="bg-gradient-to-br {{ $availableBalance < 0 ? 'from-rose-700 via-rose-800 to-red-900' : 'from-indigo-700 via-indigo-800 to-purple-900' }} rounded-2xl p-5 shadow-sm text-white relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-15">
                    <i class="fas fa-vault text-8xl"></i>
                </div>
                <div class="relative z-10 flex items-center justify-between">
                    <span class="text-xs font-bold {{ $availableBalance < 0 ? 'text-rose-200' : 'text-indigo-200' }} uppercase tracking-wider">Available Balance</span>
                    @if($withdrawableBalance >= 10)
                        <a href="{{ route('reseller.withdrawals.index') }}"
                           class="text-[11px] font-bold bg-white text-indigo-800 px-2.5 py-1 rounded-lg hover:bg-indigo-50 shadow-xs transition">
                            Withdraw
                        </a>
                    @elseif($availableBalance < 0)
                        <span class="text-[10px] font-bold bg-white/20 text-rose-100 px-2 py-0.5 rounded-lg">
                            Negative
                        </span>
                    @endif
                </div>
                <div class="relative z-10 mt-3">
                    <div class="text-3xl font-black">
                        {{ $availableBalance < 0 ? '-৳' . number_format(abs($availableBalance), 2) : '৳' . number_format($availableBalance, 2) }}
                    </div>
                    <div class="text-[11px] {{ $availableBalance < 0 ? 'text-rose-200' : 'text-indigo-200' }} mt-2 flex items-center gap-1">
                        @if($availableBalance < 0)
                            <span>Deducted from future profit</span>
                        @else
                            <span>Withdrawable Balance:</span>
                            <strong class="text-white font-bold">৳{{ number_format($withdrawableBalance, 2) }}</strong>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Other Hisab --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        {{-- Balance Breakdown Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fas fa-scale-balanced text-indigo-500"></i> Detailed Balance Breakdown
            </h4>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-circle-check text-emerald-500 text-[10px]"></i> Total Profit Earned
                    </span>
                    <span class="font-bold text-slate-800">৳{{ number_format($totalProfit, 2) }}</span>
                </div>

                @if($totalReturnCharge > 0)
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50 bg-rose-50/50 px-2 rounded-lg">
                    <span class="text-rose-700 font-semibold flex items-center gap-1.5">
                        <i class="fas fa-rotate-left text-rose-500 text-[10px]"></i> Return Shipping Fees ({{ $returnedOrdersCount }} orders)
                    </span>
                    <span class="font-bold text-rose-600">- ৳{{ number_format($totalReturnCharge, 2) }}</span>
                </div>
                @endif

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-arrow-up-right-from-square text-orange-500 text-[10px]"></i> Total Withdrawn
                    </span>
                    <span class="font-bold text-orange-600">- ৳{{ number_format($totalWithdrawn, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50 {{ $availableBalance < 0 ? 'bg-rose-50' : 'bg-indigo-50/50' }} px-2.5 rounded-lg">
                    <span class="font-bold {{ $availableBalance < 0 ? 'text-rose-900' : 'text-indigo-900' }}">Net Available Balance</span>
                    <span class="font-extrabold {{ $availableBalance < 0 ? 'text-rose-700' : 'text-indigo-700' }}">
                        {{ $availableBalance < 0 ? '-৳' . number_format(abs($availableBalance), 2) : '৳' . number_format($availableBalance, 2) }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-clock text-amber-500 text-[10px]"></i> Pending Payout Requests (In Review)
                    </span>
                    <span class="font-bold text-amber-600">
                        @if($pendingWithdrawals > 0)
                            - ৳{{ number_format($pendingWithdrawals, 2) }}
                        @else
                            ৳0.00
                        @endif
                    </span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50 bg-emerald-50/50 px-2.5 rounded-lg">
                    <span class="font-bold text-emerald-900">Current Withdrawable Balance</span>
                    <span class="font-extrabold text-emerald-700">৳{{ number_format($withdrawableBalance, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5 pt-2">
                    <span class="text-slate-500 flex items-center gap-1.5" title="Orders currently in delivery or in transit">
                        <i class="fas fa-hourglass-half text-purple-500 text-[10px]"></i> In-Transit Expected Profit
                    </span>
                    <span class="font-bold text-purple-700">+ ৳{{ number_format($pendingProfit, 2) }}</span>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('reseller.withdrawals.index') }}"
                   class="w-full py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition">
                    <i class="fas fa-arrow-right"></i> View Withdrawal History
                </a>
            </div>
        </div>

        {{-- Sales & Customer Payment Breakdown --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fas fa-file-invoice-dollar text-emerald-500"></i> Sales & Invoicing Overview
            </h4>

            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Gross Sales Amount</span>
                    <span class="font-extrabold text-slate-800">৳{{ number_format($totalSalesAmount, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-circle-check text-blue-500 text-[10px]"></i> Customer Paid Amount
                    </span>
                    <span class="font-bold text-blue-700">৳{{ number_format($totalPaidAmount, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-circle-exclamation text-rose-500 text-[10px]"></i> Customer Outstanding (COD / Due)
                    </span>
                    <span class="font-bold text-rose-600">৳{{ number_format($totalDueAmount, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Average Profit / Order</span>
                    <span class="font-bold text-emerald-700">৳{{ number_format($avgProfitPerOrder, 2) }}</span>
                </div>

                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-500">Delivered Orders</span>
                    <span class="font-bold text-slate-700">{{ number_format($deliveredOrdersCount) }} orders</span>
                </div>

                <div class="flex items-center justify-between py-1.5">
                    <span class="text-slate-500">Cancelled Orders</span>
                    <span class="font-bold text-red-600">{{ number_format($cancelledOrdersCount) }} orders</span>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('reseller.orders.index') }}"
                   class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition">
                    <i class="fas fa-receipt"></i> View All Orders
                </a>
            </div>
        </div>

        {{-- Monthly Performance Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fas fa-calendar-check text-purple-500"></i> Performance ({{ now()->format('F Y') }})
            </h4>

            <div class="p-4 rounded-xl bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 border border-indigo-100/60">
                <div class="text-[11px] font-bold text-indigo-900 uppercase tracking-wider">{{ now()->format('F Y') }} Profit</div>
                <div class="text-2xl font-black text-indigo-700 mt-1">৳{{ number_format($thisMonthProfit, 2) }}</div>
                <div class="text-xs text-slate-600 mt-2 flex items-center justify-between">
                    <span>Total Orders This Month:</span>
                    <strong class="font-bold text-slate-800">{{ $thisMonthOrders }} orders</strong>
                </div>
                <div class="text-xs text-slate-600 mt-1 flex items-center justify-between">
                    <span>Total Sales This Month:</span>
                    <strong class="font-bold text-slate-800">৳{{ number_format($thisMonthSales, 2) }}</strong>
                </div>
            </div>

            <div class="space-y-2 pt-1">
                <div class="text-xs font-bold text-slate-700 mb-1">Order Status Breakdown:</div>
                <div class="flex flex-wrap gap-1.5">
                    @php
                        $badgeStyles = [
                            'pending'           => 'bg-amber-50 text-amber-700 border-amber-200',
                            'confirmed'         => 'bg-blue-50 text-blue-700 border-blue-200',
                            'processing'        => 'bg-purple-50 text-purple-700 border-purple-200',
                            'sent_to_courier'   => 'bg-sky-50 text-sky-700 border-sky-200',
                            'out_for_delivery'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'delivered'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'completed'         => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            'cancelled'         => 'bg-rose-50 text-rose-700 border-rose-200',
                        ];
                    @endphp
                    @forelse($statusCounts as $st => $data)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $badgeStyles[$st] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            <span>{{ ucfirst(str_replace('_', ' ', $st)) }}:</span>
                            <strong class="font-bold">{{ $data->count }}</strong>
                        </span>
                    @empty
                        <span class="text-xs text-slate-400">No orders found yet</span>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- Recent Orders Table with Profit Column --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-indigo-600"></i> Recent Orders & Profit Breakdown
            </h3>
            <a href="{{ route('reseller.orders.index') }}" class="text-indigo-600 text-xs font-bold hover:underline">
                View All Orders →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Invoice</th>
                        <th class="px-5 py-3 text-left">Customer</th>
                        <th class="px-5 py-3 text-left">Items</th>
                        <th class="px-5 py-3 text-right">Sale Amount</th>
                        <th class="px-5 py-3 text-right">Reseller Profit</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3">
                                <a href="{{ route('reseller.orders.show', $order) }}"
                                   class="font-mono font-bold text-indigo-600 hover:underline">
                                    {{ $order->invoice_no }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-semibold text-slate-800">{{ $order->customer?->name ?? 'Direct Customer' }}</div>
                                @if($order->customer?->phone)
                                    <div class="text-[11px] text-slate-400">{{ $order->customer->phone }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500 text-xs">
                                {{ $order->saleItems->count() }} item(s)
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800">
                                ৳{{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="font-black text-emerald-600">
                                    ৳{{ number_format($order->reseller_profit, 2) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $stClass = match($order->order_status) {
                                        'completed', 'complete' => 'bg-emerald-100 text-emerald-800',
                                        'delivered'             => 'bg-emerald-50 text-emerald-700',
                                        'sent_to_courier', 'shipped' => 'bg-sky-100 text-sky-700',
                                        'processing'            => 'bg-purple-100 text-purple-700',
                                        'confirmed'             => 'bg-blue-100 text-blue-700',
                                        'cancelled'             => 'bg-rose-100 text-rose-700',
                                        default                 => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $stClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-5 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('reseller.orders.show', $order) }}"
                                       class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-indigo-50 transition"
                                       title="View Details">
                                        Details
                                    </a>
                                    <a href="{{ route('reseller.orders.invoice', $order) }}" target="_blank"
                                       class="text-slate-500 hover:text-indigo-600 text-xs font-bold px-2 py-1 rounded-lg hover:bg-indigo-50 transition"
                                       title="Print Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-slate-400 text-xs">
                                <i class="fas fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                No orders found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Withdrawals Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-orange-500"></i> Recent Withdrawals
            </h3>
            <a href="{{ route('reseller.withdrawals.index') }}" class="text-indigo-600 text-xs font-bold hover:underline">
                Request Payout →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">ID</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3 text-left">Payment Method</th>
                        <th class="px-5 py-3 text-left">Account Details</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Request Date</th>
                        <th class="px-5 py-3 text-left">Processed Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentWithdrawals as $w)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">#{{ $w->id }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-800">
                                ৳{{ number_format($w->amount, 2) }}
                            </td>
                            <td class="px-5 py-3 font-semibold text-slate-700 capitalize">
                                <i class="fas fa-mobile-screen-button mr-1 text-slate-400"></i>{{ $w->payment_method }}
                            </td>
                            <td class="px-5 py-3 text-slate-600 font-mono text-xs">
                                {{ $w->payment_details }}
                            </td>
                            <td class="px-5 py-3">
                                @if($w->isApproved())
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check text-[9px] mr-1"></i>Approved
                                    </span>
                                @elseif($w->isPending())
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fas fa-clock text-[9px] mr-1"></i>Pending
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fas fa-times text-[9px] mr-1"></i>Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">
                                {{ $w->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">
                                @if($w->processed_at)
                                    {{ $w->processed_at->format('d M Y') }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400 text-xs">
                                No withdrawal history found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
