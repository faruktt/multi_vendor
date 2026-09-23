@extends('reseller.layouts.app')
@section('title', 'Withdrawals & Payouts')
@section('heading', 'Withdrawals & Payouts')

@section('content')
<div class="py-4 space-y-6" x-data="{
    showModal: {{ (isset($errors) && $errors->any()) ? 'true' : 'false' }},
    maxAmount: {{ $withdrawableBalance }},
    amount: '{{ old('amount') }}',
    paymentMethod: '{{ old('payment_method', 'bkash') }}',
    setAmount(val) {
        this.amount = Math.min(val, this.maxAmount);
    }
}">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
            <div class="flex items-center gap-2 mb-1 font-semibold text-sm">
                <i class="fas fa-triangle-exclamation text-rose-500"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top Overview Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Available / Withdrawable Card --}}
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl shadow-sm p-5 text-white relative overflow-hidden flex flex-col justify-between">
            <div class="absolute -right-6 -bottom-6 opacity-15">
                <i class="fas fa-wallet text-8xl"></i>
            </div>
            <div class="relative z-10">
                <span class="text-indigo-200 text-xs font-semibold uppercase tracking-wider">Withdrawable Balance</span>
                <div class="text-3xl font-black mt-1">৳{{ number_format($withdrawableBalance, 2) }}</div>
                @if($pendingWithdrawals > 0)
                    <div class="text-xs text-amber-200 mt-1">
                        ৳{{ number_format($pendingWithdrawals, 2) }} pending request(s)
                    </div>
                @else
                    <div class="text-xs text-indigo-200 mt-1">Ready for withdrawal</div>
                @endif
            </div>
            <div class="relative z-10 mt-4">
                @if($withdrawableBalance >= 10)
                    <button type="button" @click="showModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-xs rounded-xl shadow transition transform active:scale-95">
                        <i class="fas fa-hand-holding-dollar"></i> Request Payout
                    </button>
                @else
                    <button type="button" disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 text-white/70 font-semibold text-xs rounded-xl cursor-not-allowed">
                        <i class="fas fa-lock"></i> Min ৳10 required
                    </button>
                @endif
            </div>
        </div>

        {{-- Pending Withdrawals --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Pending Payouts</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">৳{{ number_format($pendingWithdrawals, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">Waiting for admin review</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 shadow-inner">
                <i class="fas fa-clock text-lg"></i>
            </div>
        </div>

        {{-- Total Withdrawn --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Total Withdrawn</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">৳{{ number_format($totalWithdrawn, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">Successfully approved & paid</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                <i class="fas fa-money-bill-transfer text-lg"></i>
            </div>
        </div>

        {{-- Lifetime Profit --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Lifetime Profit</span>
                <div class="text-2xl font-bold text-slate-800 mt-1">৳{{ number_format($totalProfit, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">From completed orders</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner">
                <i class="fas fa-chart-line text-lg"></i>
            </div>
        </div>

    </div>

    {{-- Withdrawal History Table Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">Withdrawal Request History</h3>
                <p class="text-xs text-slate-400 mt-0.5">Track the status of all your profit payout requests</p>
            </div>
            @if($withdrawableBalance >= 10)
                <button type="button" @click="showModal = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                    <i class="fas fa-plus"></i> New Request
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-5">Request Info</th>
                        <th class="py-3.5 px-4 text-right">Amount</th>
                        <th class="py-3.5 px-4">Payment Method & Details</th>
                        <th class="py-3.5 px-4">Your Note</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-5">Admin Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($withdrawals as $w)
                        <tr class="hover:bg-slate-50/70 transition">
                            {{-- Request Info --}}
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-800">#REQ-{{ str_pad($w->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $w->created_at ? $w->created_at->format('d M, Y - h:i A') : '—' }}
                                </div>
                            </td>

                            {{-- Amount --}}
                            <td class="py-4 px-4 text-right">
                                <span class="text-sm font-black text-slate-900">৳{{ number_format($w->amount, 2) }}</span>
                            </td>

                            {{-- Payment Method & Details --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 mb-1">
                                    @if(strtolower($w->payment_method) === 'bkash')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-700">
                                            <i class="fas fa-mobile-screen"></i> bKash
                                        </span>
                                    @elseif(strtolower($w->payment_method) === 'nagad')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">
                                            <i class="fas fa-mobile-screen"></i> Nagad
                                        </span>
                                    @elseif(strtolower($w->payment_method) === 'rocket')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">
                                            <i class="fas fa-mobile-screen"></i> Rocket
                                        </span>
                                    @elseif(strtolower($w->payment_method) === 'bank')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                            <i class="fas fa-building-columns"></i> Bank
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            <i class="fas fa-wallet"></i> {{ ucfirst($w->payment_method ?? 'Cash') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="font-medium text-slate-800 select-all font-mono text-[11px]">
                                    {{ $w->payment_details ?? 'N/A' }}
                                </div>
                            </td>

                            {{-- Reseller Note --}}
                            <td class="py-4 px-4 max-w-xs">
                                @if($w->note)
                                    <span class="text-slate-600 italic">"{{ $w->note }}"</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-4 px-4 text-center">
                                @if($w->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                @elseif($w->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Admin Feedback / Process Info --}}
                            <td class="py-4 px-5 max-w-xs">
                                @if($w->status === 'approved')
                                    <div class="text-[11px] text-emerald-700">
                                        <i class="fas fa-check text-[10px]"></i> Processed {{ $w->processed_at ? $w->processed_at->diffForHumans() : '' }}
                                    </div>
                                    @if($w->admin_note)
                                        <div class="text-[11px] text-slate-600 mt-0.5">Note: {{ $w->admin_note }}</div>
                                    @endif
                                @elseif($w->status === 'rejected')
                                    <div class="text-[11px] text-rose-700 font-medium">
                                        Reason: {{ $w->admin_note ?: 'No note provided' }}
                                    </div>
                                    @if($w->processed_at)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $w->processed_at->format('d M, Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Awaiting review</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                    <i class="fas fa-receipt text-2xl"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 text-sm">No Withdrawal Requests Yet</h4>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                                    When you earn profit from completed orders, you can request withdrawals here anytime.
                                </p>
                                @if($withdrawableBalance >= 10)
                                    <button type="button" @click="showModal = true"
                                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow transition">
                                        <i class="fas fa-hand-holding-dollar"></i> Request Your First Withdrawal
                                    </button>
                                @endif
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

    {{-- Request Withdrawal Modal --}}
    <div x-show="showModal"
         x-cloak
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="showModal = false"
             x-show="showModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl transition-all border border-slate-100 overflow-hidden"
                 x-show="showModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- Modal Header --}}
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center">
                            <i class="fas fa-hand-holding-dollar text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base">Request Profit Payout</h3>
                            <p class="text-xs text-indigo-100 mt-0.5">Submit request to transfer profit to your account</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="text-indigo-200 hover:text-white transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body / Form --}}
                <form method="POST" action="{{ route('reseller.withdrawals.store') }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Balance Notice Card --}}
                    <div class="p-3.5 bg-indigo-50/70 border border-indigo-100 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wide">Available to Withdraw</div>
                            <div class="text-xl font-black text-indigo-950 mt-0.5">৳{{ number_format($withdrawableBalance, 2) }}</div>
                        </div>
                        <button type="button" @click="setAmount({{ $withdrawableBalance }})"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-white border border-indigo-200 px-3 py-1.5 rounded-lg shadow-2xl hover:bg-indigo-50 transition">
                            Max All
                        </button>
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Withdrawal Amount (৳) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">৳</span>
                            <input type="number"
                                   step="any"
                                   min="10"
                                   max="{{ $withdrawableBalance }}"
                                   name="amount"
                                   x-model="amount"
                                   required
                                   placeholder="Min ৳10"
                                   class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        {{-- Quick suggestions --}}
                        <div class="flex items-center gap-1.5 mt-2">
                            <span class="text-[11px] text-slate-400">Quick:</span>
                            @foreach([500, 1000, 2000, 5000] as $preset)
                                @if($preset <= $withdrawableBalance)
                                    <button type="button" @click="setAmount({{ $preset }})"
                                            class="text-[11px] font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 rounded transition">
                                        ৳{{ number_format($preset) }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Payment Method Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Payment Method / Channel <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition"
                                   :class="paymentMethod === 'bkash' ? 'border-pink-500 bg-pink-50/50 text-pink-700 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                <input type="radio" name="payment_method" value="bkash" x-model="paymentMethod" class="sr-only">
                                <i class="fas fa-mobile-screen text-base mb-1" :class="paymentMethod === 'bkash' ? 'text-pink-600' : 'text-slate-400'"></i>
                                <span class="text-xs">bKash</span>
                            </label>

                            <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition"
                                   :class="paymentMethod === 'nagad' ? 'border-orange-500 bg-orange-50/50 text-orange-700 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                <input type="radio" name="payment_method" value="nagad" x-model="paymentMethod" class="sr-only">
                                <i class="fas fa-mobile-screen text-base mb-1" :class="paymentMethod === 'nagad' ? 'text-orange-600' : 'text-slate-400'"></i>
                                <span class="text-xs">Nagad</span>
                            </label>

                            <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition"
                                   :class="paymentMethod === 'rocket' ? 'border-purple-500 bg-purple-50/50 text-purple-700 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                <input type="radio" name="payment_method" value="rocket" x-model="paymentMethod" class="sr-only">
                                <i class="fas fa-mobile-screen text-base mb-1" :class="paymentMethod === 'rocket' ? 'text-purple-600' : 'text-slate-400'"></i>
                                <span class="text-xs">Rocket</span>
                            </label>

                            <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center justify-center transition"
                                   :class="paymentMethod === 'bank' ? 'border-blue-500 bg-blue-50/50 text-blue-700 font-bold shadow-sm' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                <input type="radio" name="payment_method" value="bank" x-model="paymentMethod" class="sr-only">
                                <i class="fas fa-building-columns text-base mb-1" :class="paymentMethod === 'bank' ? 'text-blue-600' : 'text-slate-400'"></i>
                                <span class="text-xs">Bank Transfer</span>
                            </label>
                        </div>
                    </div>

                    {{-- Account Details --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Account Number / Payment Details <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="payment_details"
                               value="{{ old('payment_details') }}"
                               required
                               placeholder="e.g. 017XXXXXXXX (Personal/Agent) or Bank, Branch, A/C Name & Number"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <p class="text-[11px] text-slate-400 mt-1">Specify whether personal or merchant account, or full bank details.</p>
                    </div>

                    {{-- Reseller Note --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Note for Admin <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <textarea name="note"
                                  rows="2"
                                  placeholder="Any specific instructions or note..."
                                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">{{ old('note') }}</textarea>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition transform active:scale-95">
                            <i class="fas fa-paper-plane mr-1"></i> Submit Request
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>
@endsection
