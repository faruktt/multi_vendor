@extends('reseller.layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="py-4 space-y-6">

    {{-- Wallet Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @php
            $dashReseller = auth('reseller')->user();
            $availBal = $dashReseller->available_balance;
            $retCharge = $dashReseller->total_return_charge;
        @endphp
        <div class="bg-gradient-to-br {{ $availBal < 0 ? 'from-rose-700 to-rose-900' : 'from-indigo-600 to-indigo-800' }} rounded-2xl shadow-sm p-5 text-white relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 opacity-20">
                <i class="fas fa-wallet text-9xl"></i>
            </div>
            <div class="relative z-10">
                <span class="{{ $availBal < 0 ? 'text-rose-200' : 'text-indigo-100' }} text-sm font-medium">Available Balance</span>
                <div class="text-3xl font-black mt-1 tracking-tight">
                    {{ $availBal < 0 ? '-৳' . number_format(abs($availBal), 2) : '৳' . number_format($availBal, 2) }}
                </div>
                @if($availBal < 0)
                    <div class="text-xs text-rose-200 mt-1 flex items-center gap-1 font-medium">
                        <i class="fas fa-circle-exclamation"></i> রিটার্ন ডেলিভারি চার্জ বাবদ ঋণাত্মক
                    </div>
                @elseif($dashReseller->pending_withdrawals > 0)
                    <div class="text-xs text-amber-200 mt-1 flex items-center gap-1">
                        <i class="fas fa-clock"></i> ৳{{ number_format($dashReseller->pending_withdrawals, 2) }} pending approval
                    </div>
                @else
                    <div class="text-xs text-indigo-200 mt-1">Added from completed orders</div>
                @endif
            </div>
            <div class="relative z-10 mt-4">
                @if($dashReseller->withdrawable_balance >= 10)
                    <a href="{{ route('reseller.withdrawals.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-xs rounded-xl shadow-sm transition">
                        <i class="fas fa-hand-holding-dollar"></i> Withdraw Profit
                    </a>
                @elseif($availBal < 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-black/20 text-rose-100 font-semibold text-xs rounded-xl">
                        পরবর্তী লাভ থেকে সমন্বয় হবে
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-white/20 text-white/80 font-semibold text-xs rounded-xl">
                        উত্তোলনযোগ্য ব্যালেন্স নেই
                    </span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-sm font-medium">Total Profit Earned</span>
                <div class="text-2xl font-bold text-slate-800 mt-1">৳{{ number_format($dashReseller->total_profit, 2) }}</div>
                @if($retCharge > 0)
                    <div class="text-xs text-rose-600 mt-1 font-semibold flex items-center gap-1">
                        <i class="fas fa-rotate-left text-[10px]"></i> রিটার্ন চার্জ: -৳{{ number_format($retCharge, 2) }}
                    </div>
                @endif
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <i class="fas fa-chart-line text-lg"></i>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-sm font-medium">Total Withdrawn</span>
                <div class="text-2xl font-bold text-slate-800 mt-1">৳{{ number_format(auth('reseller')->user()->total_withdrawn, 2) }}</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600">
                <i class="fas fa-money-bill-transfer text-lg"></i>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-receipt text-indigo-600"></i>
                </div>
                <span class="text-slate-500 text-sm font-medium">Total Orders</span>
            </div>
            <div class="text-2xl font-bold text-slate-800">{{ number_format($totalOrders) }}</div>
            <div class="text-xs text-slate-400 mt-1">This month: {{ $thisMonthOrders }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-taka-sign text-emerald-600"></i>
                </div>
                <span class="text-slate-500 text-sm font-medium">Total Amount</span>
            </div>
            <div class="text-2xl font-bold text-slate-800">৳{{ number_format($totalAmount, 0) }}</div>
            <div class="text-xs text-slate-400 mt-1">This month: ৳{{ number_format($thisMonthAmount, 0) }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600"></i>
                </div>
                <span class="text-slate-500 text-sm font-medium">Total Paid</span>
            </div>
            <div class="text-2xl font-bold text-blue-700">৳{{ number_format($totalPaid, 0) }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fas fa-clock text-red-500"></i>
                </div>
                <span class="text-slate-500 text-sm font-medium">Total Due</span>
            </div>
            <div class="text-2xl font-bold text-red-600">৳{{ number_format($totalDue, 0) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-700 mb-4">6-Month Overview</h3>
            <canvas id="revenueChart" height="100"></canvas>
        </div>

        {{-- Order Status Breakdown --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-700 mb-4">Order Status</h3>
            @php
                $statusColors = [
                    'pending'   => 'bg-yellow-100 text-yellow-700',
                    'confirmed' => 'bg-blue-100 text-blue-700',
                    'shipped'   => 'bg-purple-100 text-purple-700',
                    'delivered' => 'bg-emerald-100 text-emerald-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                ];
            @endphp
            @forelse($statusBreakdown as $s)
                <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$s->order_status] ?? 'bg-slate-100 text-slate-600' }}">
                        {{ ucfirst($s->order_status) }}
                    </span>
                    <div class="text-right">
                        <div class="text-sm font-bold text-slate-700">{{ $s->cnt }} orders</div>
                        <div class="text-xs text-slate-400">৳{{ number_format($s->rev, 0) }}</div>
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-sm text-center py-4">No orders yet</p>
            @endforelse

            <a href="{{ route('reseller.orders.create') }}"
               class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 text-sm font-bold flex items-center justify-center gap-2 transition-colors">
                <i class="fas fa-plus"></i> Place New Order
            </a>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-700">Recent Orders</h3>
            <a href="{{ route('reseller.orders.index') }}" class="text-indigo-600 text-sm font-medium hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Invoice</th>
                        <th class="px-5 py-3 text-left">Items</th>
                        <th class="px-5 py-3 text-left">Total</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3">
                                <a href="{{ route('reseller.orders.show', $order) }}" class="font-mono text-indigo-600 hover:underline font-semibold">{{ $order->invoice_no }}</a>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $order->saleItems->count() }} item(s)</td>
                            <td class="px-5 py-3 font-bold text-slate-700">৳{{ number_format($order->total, 0) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusColors[$order->order_status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('reseller.orders.show', $order) }}" class="text-slate-400 hover:text-indigo-600 px-1.5 py-1 text-xs" title="View Order">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('reseller.orders.invoice', $order) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 px-1.5 py-1 text-xs" title="Print Invoice">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Revenue (৳)',
                data: @json($chartRevenue),
                backgroundColor: 'rgba(99,102,241,0.2)',
                borderColor: '#6366f1',
                borderWidth: 2,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => '৳' + v.toLocaleString() } } }
    }
});
</script>
@endpush
