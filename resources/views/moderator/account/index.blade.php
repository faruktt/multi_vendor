@extends('moderator.layouts.app')
@section('title', 'My Account - Work & Salary Withdrawals')

@section('content')
<div class="space-y-6" x-data="{
    withdrawModalOpen: false,
    withdrawAmount: '{{ $availableBalance > 0 ? $availableBalance : '' }}',
    paymentMethod: 'bkash',
    paymentDetails: '',
    note: '',
    openWithdraw() {
        this.withdrawModalOpen = true;
    }
}">

    {{-- Account Header Banner --}}
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($moderator->image_url)
                <img src="{{ $moderator->image_url }}" alt="{{ $moderator->name }}"
                     class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-100 shadow-sm flex-shrink-0">
            @else
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-2xl shadow-sm shadow-indigo-200 flex-shrink-0">
                    {{ strtoupper(substr($moderator->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900">{{ $moderator->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $moderator->isActive() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                        {{ $moderator->isActive() ? 'Active Account' : 'Inactive Account' }}
                    </span>
                    @if($moderator->isWorkingNow())
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            On Duty
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                    <span><i class="far fa-envelope mr-1 text-slate-400"></i>{{ $moderator->email }}</span>
                    <span>•</span>
                    <span><i class="fas fa-phone-alt mr-1 text-slate-400"></i>{{ $moderator->phone ?: 'No phone number' }}</span>
                    <span>•</span>
                    <span class="font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">Rate: ৳{{ number_format($ratePerMinute, 2) }} / min</span>
                </div>
            </div>
        </div>

        {{-- Withdraw Request Trigger Button --}}
        <div>
            @if($availableBalance >= 10)
                <button type="button" @click="openWithdraw()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold px-6 py-3.5 rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:scale-105 active:scale-95 transition-all text-xs sm:text-sm cursor-pointer">
                    <i class="fas fa-paper-plane text-sm"></i>
                    <span>Request Salary Withdrawal</span>
                </button>
            @else
                <button type="button" disabled
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-100 text-slate-400 font-bold px-5 py-3 rounded-2xl text-xs cursor-not-allowed border border-slate-200"
                        title="Available balance must be at least ৳10 to withdraw">
                    <i class="fas fa-lock text-xs"></i>
                    <span>Insufficient balance for withdrawal (min ৳10)</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ── 6 Core KPI Financial Metrics ────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        {{-- Total Minutes Worked --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Work Duration</span>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="text-lg sm:text-xl font-black text-slate-900 font-mono">
                {{ number_format($totalMinutes, 1) }}
                <span class="text-xs font-semibold text-slate-500 font-sans">min</span>
            </div>
            <div class="text-[10px] text-slate-400 mt-1 truncate">({{ $moderator->formattedTotalWorkTime() }})</div>
        </div>

        {{-- Rate per Minute --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Rate Per Minute</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-tag"></i>
                </div>
            </div>
            <div class="text-lg sm:text-xl font-black text-slate-900 font-mono">
                ৳{{ number_format($ratePerMinute, 2) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1 truncate">1 min = ৳{{ number_format($ratePerMinute, 2) }}</div>
        </div>

        {{-- Total Earned --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Earned</span>
                <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="text-lg sm:text-xl font-black text-teal-600 font-mono">
                ৳{{ number_format($totalEarned, 2) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1 truncate">Lifetime shift earnings</div>
        </div>

        {{-- Total Withdrawn --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Withdrawn</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
            <div class="text-lg sm:text-xl font-black text-blue-600 font-mono">
                ৳{{ number_format($totalWithdrawn, 2) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1 truncate">Paid out to date</div>
        </div>

        {{-- Pending Withdrawals --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Pending Requests</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="text-lg sm:text-xl font-black text-amber-600 font-mono">
                ৳{{ number_format($pendingPayout, 2) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-1 truncate">Awaiting admin approval</div>
        </div>

        {{-- Available Balance --}}
        <div class="bg-gradient-to-tr from-emerald-600 to-teal-700 text-white rounded-2xl p-4 shadow-md shadow-emerald-600/20 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-emerald-100">Available Balance</span>
                <div class="w-7 h-7 rounded-lg bg-white/20 text-white flex items-center justify-center text-xs">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black font-mono">
                ৳{{ number_format($availableBalance, 2) }}
            </div>
            <div class="text-[10px] text-emerald-100 mt-1 font-medium">Available for withdrawal</div>
        </div>
    </div>

    {{-- ── Withdraw Requests History Section ──────────────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base sm:text-lg flex items-center gap-2">
                    <i class="fas fa-history text-indigo-600"></i>
                    <span>Salary Withdrawal History</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">All payout requests, approval status and transaction notes</p>
            </div>
            @if($availableBalance >= 10)
                <button type="button" @click="openWithdraw()"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-plus"></i>
                    <span>New Withdrawal Request</span>
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Date & Time</th>
                        <th class="px-5 py-3.5 text-right">Amount (৳)</th>
                        <th class="px-5 py-3.5">Payment Method</th>
                        <th class="px-5 py-3.5">Account / Payment Details</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5">Admin Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($withdrawals as $w)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-4 font-mono text-slate-700">
                            <div>{{ $w->created_at->timezone('Asia/Dhaka')->format('d M, Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $w->created_at->timezone('Asia/Dhaka')->format('h:i:s A') }}</div>
                        </td>
                        <td class="px-5 py-4 text-right font-black font-mono text-sm {{ $w->isApproved() ? 'text-emerald-600' : ($w->isRejected() ? 'text-rose-500 line-through' : 'text-amber-600') }}">
                            ৳{{ number_format($w->amount, 2) }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $w->payment_method === 'bkash' ? 'bg-pink-50 text-pink-700 border border-pink-200' : ($w->payment_method === 'nagad' ? 'bg-orange-50 text-orange-700 border border-orange-200' : ($w->payment_method === 'rocket' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200')) }}">
                                {{ $w->payment_method }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-mono text-xs text-slate-800 font-semibold">{{ $w->payment_details }}</div>
                            @if($w->note)
                                <div class="text-[11px] text-slate-400 mt-0.5">Note: {{ $w->note }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($w->isPending())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    Pending
                                </span>
                            @elseif($w->isApproved())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-check-circle text-[10px]"></i>
                                    Approved
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="fas fa-times-circle text-[10px]"></i>
                                    Rejected
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($w->admin_note)
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-700">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">
                                        Admin Note:
                                    </div>
                                    <span class="font-semibold">{{ $w->admin_note }}</span>
                                </div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            <i class="fas fa-wallet text-3xl text-slate-300 mb-2 block"></i>
                            <p>No salary withdrawal requests found yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $withdrawals->links() }}
        </div>
        @endif
    </div>

    {{-- ── Completed Work Shifts & Earnings Breakdown ─────────────── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 text-base sm:text-lg flex items-center gap-2">
                    <i class="fas fa-chart-bar text-indigo-600"></i>
                    <span>Completed Shifts & Earnings Breakdown</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Details of all finished work sessions and earned compensation</p>
            </div>
            <a href="{{ route('moderator.reports') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors">
                <span>Full History</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Start & End Time</th>
                        <th class="px-5 py-3 text-center">Duration</th>
                        <th class="px-5 py-3 text-right">Minute Rate</th>
                        <th class="px-5 py-3 text-right">Earned (৳)</th>
                        <th class="px-5 py-3">Summary</th>
                        <th class="px-5 py-3 text-center">Report</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sessions as $s)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-slate-800">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('d M, Y') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('h:i A') }}
                            -
                            {{ $s->ended_at ? $s->ended_at->timezone('Asia/Dhaka')->format('h:i A') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">
                                {{ $s->formattedDuration() }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">({{ round($s->duration_seconds / 60, 1) }} min)</span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono text-slate-500">
                            ৳{{ number_format($s->rate_per_minute ?? $ratePerMinute, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-black font-mono text-sm text-emerald-600">
                            ৳{{ number_format($s->earned_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 max-w-xs truncate" title="{{ $s->tasks_summary }}">
                            {{ $s->tasks_summary ?: 'No summary provided' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($s->work_report)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Submitted
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                    None
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            No completed work shifts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

    {{-- ── Salary Withdrawal Modal ────────────────────────────────── --}}
    <div x-show="withdrawModalOpen" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="withdrawModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]" @click.outside="withdrawModalOpen = false">
            
            {{-- Modal Header --}}
            <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-teal-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-200">
                        <i class="fas fa-hand-holding-usd text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Request Salary Withdrawal</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Available Balance: <b class="text-emerald-700 font-mono">৳{{ number_format($availableBalance, 2) }}</b></p>
                    </div>
                </div>
                <button type="button" @click="withdrawModalOpen = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/80">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('moderator.withdrawals.store') }}" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf

                {{-- Amount Field --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Withdrawal Amount (৳) *</label>
                        <button type="button" @click="withdrawAmount = '{{ $availableBalance }}'"
                                class="text-xs font-bold text-emerald-600 hover:text-emerald-800 hover:underline cursor-pointer">
                            Withdraw Full Balance (৳{{ number_format($availableBalance, 2) }})
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">৳</span>
                        <input type="number" step="0.01" min="10" max="{{ $availableBalance }}" name="amount" x-model="withdrawAmount" required
                                class="w-full text-base font-black font-mono rounded-xl border border-slate-200 pl-8 pr-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="e.g. 500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Minimum withdrawal ৳10 • Maximum ৳{{ number_format($availableBalance, 2) }}</p>
                </div>

                {{-- Payment Method Selection --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Payment Method *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="border rounded-2xl p-3 text-center cursor-pointer transition-all"
                               :class="paymentMethod === 'bkash' ? 'border-pink-500 bg-pink-50 text-pink-700 font-extrabold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_method" value="bkash" x-model="paymentMethod" class="sr-only">
                            <span class="block text-sm">bKash</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Personal</span>
                        </label>

                        <label class="border rounded-2xl p-3 text-center cursor-pointer transition-all"
                               :class="paymentMethod === 'nagad' ? 'border-orange-500 bg-orange-50 text-orange-700 font-extrabold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_method" value="nagad" x-model="paymentMethod" class="sr-only">
                            <span class="block text-sm">Nagad</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Personal</span>
                        </label>

                        <label class="border rounded-2xl p-3 text-center cursor-pointer transition-all"
                               :class="paymentMethod === 'rocket' ? 'border-purple-500 bg-purple-50 text-purple-700 font-extrabold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_method" value="rocket" x-model="paymentMethod" class="sr-only">
                            <span class="block text-sm">Rocket</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Personal</span>
                        </label>

                        <label class="border rounded-2xl p-3 text-center cursor-pointer transition-all"
                               :class="paymentMethod === 'bank' ? 'border-blue-500 bg-blue-50 text-blue-700 font-extrabold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                            <input type="radio" name="payment_method" value="bank" x-model="paymentMethod" class="sr-only">
                            <span class="block text-sm">Bank</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Transfer</span>
                        </label>
                    </div>
                </div>

                {{-- Payment Account Details --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Account / Payment Details *
                    </label>
                    <textarea name="payment_details" rows="2" required x-model="paymentDetails"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono"
                              placeholder="Mobile banking number (e.g. 01XXXXXXXXX) or Bank Name, Account Name, Account No, Branch..."></textarea>
                </div>

                {{-- Optional Note --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Remarks / Note (Optional)</label>
                    <input type="text" name="note" x-model="note"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                           placeholder="Any additional notes or instructions...">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="withdrawModalOpen = false"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs sm:text-sm font-bold px-6 py-2.5 rounded-xl shadow-md shadow-emerald-200 transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-check-circle"></i>
                        <span>Submit Withdrawal Request</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
