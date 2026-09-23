@extends('layouts.app')
@section('title', 'Reseller Performance Report')
@section('heading', 'Reseller Performance Report')

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

    {{-- Date Filter Presets --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" class="space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                @foreach(['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'] as $pKey => $pLabel)
                    <a href="{{ route('admin.resellers.report', ['preset' => $pKey]) }}"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($preset === $pKey && !request()->filled('from')) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $pLabel }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Custom From</label>
                    <input type="date" name="from" value="{{ $from }}" class="h-10 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Custom To</label>
                    <input type="date" name="to" value="{{ $to }}" class="h-10 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                </div>
                <button type="submit" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-colors">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Period Orders</span>
                <i class="fas fa-box text-blue-500 text-sm"></i>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($totalOrders) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Period Sales</span>
                <i class="fas fa-taka-sign text-indigo-500 text-sm"></i>
            </div>
            <div class="text-2xl font-black text-indigo-700">৳{{ number_format($totalAmount, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Total revenue generated</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Period Profit</span>
                <i class="fas fa-chart-line text-emerald-500 text-sm"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600">৳{{ number_format($totalProfit, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">From delivered sales</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Period Withdrawn</span>
                <i class="fas fa-hand-holding-dollar text-amber-500 text-sm"></i>
            </div>
            <div class="text-2xl font-black text-amber-700">৳{{ number_format($totalWithdrawn, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Paid out to resellers</div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-black text-slate-800 text-base">Reseller Performance Breakdown</h3>
                <p class="text-xs text-slate-400">Ranked by sales volume during the selected timeframe</p>
            </div>
            <div class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl">
                {{ count($resellers) }} Resellers
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wider font-bold">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Reseller</th>
                        <th class="px-5 py-3.5 text-center">Period Orders</th>
                        <th class="px-5 py-3.5 text-right">Period Sales</th>
                        <th class="px-5 py-3.5 text-right">Period Profit</th>
                        <th class="px-5 py-3.5 text-right">Current Available Profit</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($resellers as $r)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800">{{ $r->name }}</div>
                                <div class="text-xs text-indigo-600 font-medium">{{ $r->business_name ?? '—' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $r->phone ?? $r->email }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-bold text-slate-800">{{ $r->period_orders }}</span>
                                <span class="text-[10px] text-slate-400 block">({{ $r->orders_count }} all-time)</span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-black text-slate-800">
                                ৳{{ number_format($r->period_amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-black text-emerald-600">
                                ৳{{ number_format($r->period_profit, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="font-black text-indigo-700">৳{{ number_format($r->available_balance, 2) }}</div>
                                <div class="text-[10px] text-slate-400">Total Profit: ৳{{ number_format($r->total_profit, 2) }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($r->status === 'active')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Active</span>
                                @elseif($r->status === 'pending')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Pending</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('admin.resellers.index', ['reseller_id' => $r->id]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition-colors">
                                    <i class="fas fa-eye text-xs"></i> Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-chart-line text-4xl mb-2 text-slate-300"></i>
                                    <span class="font-semibold text-slate-500">No activity recorded for this period</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
