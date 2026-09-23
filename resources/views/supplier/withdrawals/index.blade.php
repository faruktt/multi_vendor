@extends('supplier.layouts.app')
@section('title', 'Earnings & Withdrawals')
@section('heading', 'Earnings & Withdrawals')

@section('content')
<div class="space-y-6" x-data="{
    requestModalOpen: false,
    amount: '',
    paymentMethod: 'bkash',
    paymentDetails: '{{ addslashes($supplier->bkash_number ?: ($supplier->bank_info ?: '')) }}',
    note: '',
    maxWithdrawable: {{ (float) $withdrawableBalance }},
    setFullBalance() {
        this.amount = this.maxWithdrawable.toFixed(2);
    },
    setQuickAmount(val) {
        if (val <= this.maxWithdrawable) {
            this.amount = val;
        } else {
            this.amount = this.maxWithdrawable.toFixed(2);
        }
    }
}">

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-circle-exclamation text-rose-500 text-lg"></i>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
            <div class="font-bold text-sm mb-1 flex items-center gap-2">
                <i class="fas fa-triangle-exclamation text-rose-500"></i> Please correct the following errors:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Financial Header & Action --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 p-6 rounded-2xl text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-6 pointer-events-none">
            <i class="fas fa-money-bill-wave text-8xl"></i>
        </div>
        <div class="relative z-10">
            <div class="text-emerald-300 text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-wallet"></i> Supplier Financial Wallet
            </div>
            <h2 class="text-2xl font-black mt-1">Earnings &amp; Payout Center</h2>
            <p class="text-emerald-100/70 text-xs mt-1 max-w-lg">
                Track your net earnings from sold products, submit withdrawal requests to admin, and review payout status with admin transaction notes.
            </p>
        </div>
        <div class="relative z-10 flex items-center gap-3">
            <button type="button" @click="requestModalOpen = true"
                    @if($withdrawableBalance < 10) disabled @endif
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs shadow-lg transition-all {{ $withdrawableBalance >= 10 ? 'bg-amber-400 hover:bg-amber-300 text-amber-950 shadow-amber-400/20 hover:scale-105 active:scale-95' : 'bg-slate-700 text-slate-400 cursor-not-allowed' }}">
                <i class="fas fa-paper-plane text-sm"></i> Request Withdrawal
            </button>
        </div>
    </div>

    {{-- Financial KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Card 1: Total Net Earned --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Net Earned</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                    <i class="fas fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-800">৳{{ number_format($totalEarned, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <span class="text-emerald-600 font-semibold">After platform fee</span> ({{ $supplier->commission_percentage ?? 5.00 }}%)
                </div>
            </div>
        </div>

        {{-- Card 2: Total Withdrawn (Deducted) --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Withdrawn</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-600">৳{{ number_format($totalWithdrawn, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">Approved &amp; paid by Admin</div>
            </div>
        </div>

        {{-- Card 3: Pending Payout Requests --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Payout</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-amber-600">৳{{ number_format($pendingPayout, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">Awaiting admin review</div>
            </div>
        </div>

        {{-- Card 4: Available / Withdrawable Balance --}}
        <div class="bg-white rounded-2xl border-2 border-emerald-500/40 p-5 shadow-sm hover:shadow-md transition bg-gradient-to-br from-emerald-50/50 to-white">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Withdrawable Balance</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base shadow-md shadow-emerald-500/20">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-700">৳{{ number_format($withdrawableBalance, 2) }}</div>
                <div class="text-xs text-emerald-600 font-semibold mt-1">
                    Ready to request payout
                </div>
            </div>
        </div>
    </div>

    {{-- Withdrawal History Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="font-black text-slate-800 text-base">Withdrawal Request History</h3>
                <p class="text-xs text-slate-500 mt-0.5">List of all your payout requests and admin approval notes</p>
            </div>
            <div>
                <button type="button" @click="requestModalOpen = true"
                        @if($withdrawableBalance < 10) disabled @endif
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold text-xs transition {{ $withdrawableBalance >= 10 ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                    <i class="fas fa-plus text-[10px]"></i> New Request
                </button>
            </div>
        </div>

        @if($withdrawals->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Request Date</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Method &amp; Details</th>
                            <th class="py-3 px-4">Your Note</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Admin Note / Trx</th>
                            <th class="py-3 px-4">Processed Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($withdrawals as $item)
                            <tr class="hover:bg-slate-50/70 transition">
                                {{-- Date & ID --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">#REQ-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $item->created_at->format('d M Y, h:i A') }}</div>
                                </td>

                                {{-- Amount --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="text-sm font-black {{ $item->status === 'approved' ? 'text-emerald-700' : ($item->status === 'pending' ? 'text-amber-700' : 'text-slate-400 line-through') }}">
                                        ৳{{ number_format($item->amount, 2) }}
                                    </div>
                                </td>

                                {{-- Payment Details --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase
                                            {{ $item->payment_method === 'bkash' ? 'bg-pink-100 text-pink-700' : '' }}
                                            {{ $item->payment_method === 'nagad' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $item->payment_method === 'rocket' ? 'bg-purple-100 text-purple-700' : '' }}
                                            {{ $item->payment_method === 'bank' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $item->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700' : '' }}">
                                            {{ $item->payment_method }}
                                        </span>
                                        <span class="font-semibold text-slate-700 truncate max-w-xs">{{ $item->payment_details }}</span>
                                    </div>
                                </td>

                                {{-- Supplier Note --}}
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $item->note ?: '—' }}
                                </td>

                                {{-- Status --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($item->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pending Review
                                        </span>
                                    @elseif($item->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-check text-[10px]"></i>
                                            Approved &amp; Paid
                                        </span>
                                    @elseif($item->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fas fa-times text-[10px]"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                {{-- Admin Note --}}
                                <td class="py-3.5 px-4">
                                    @if($item->admin_note)
                                        <div class="p-2 rounded-xl text-xs {{ $item->status === 'approved' ? 'bg-emerald-50/80 border border-emerald-200/80 text-emerald-900 font-semibold' : 'bg-rose-50/80 border border-rose-200/80 text-rose-900 font-semibold' }}">
                                            <div class="flex items-center gap-1 text-[10px] text-slate-500 mb-0.5">
                                                <i class="fas fa-comment-dots text-slate-400"></i> Admin Note:
                                            </div>
                                            {{ $item->admin_note }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">No note</span>
                                    @endif
                                </td>

                                {{-- Processed At --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                    @if($item->processed_at)
                                        <div>{{ $item->processed_at->format('d M Y, h:i A') }}</div>
                                        @if($item->processedBy)
                                            <div class="text-[10px] text-slate-400">By {{ $item->processedBy->name }}</div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">Not processed yet</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-3 border-t border-slate-200/80 bg-slate-50/50">
                {{ $withdrawals->links() }}
            </div>
        @else
            <div class="py-16 text-center text-slate-400">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
                <div class="font-bold text-slate-700 text-sm">No withdrawal requests yet</div>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                    When you sell products, your net earnings accumulate here. Click "Request Withdrawal" above to request a payout to your bank or mobile wallet.
                </p>
            </div>
        @endif
    </div>

    {{-- Request Withdrawal Modal --}}
    <div x-show="requestModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            {{-- Backdrop --}}
            <div x-show="requestModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="requestModalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            {{-- Modal Panel --}}
            <div x-show="requestModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-slate-100">

                <form method="POST" action="{{ route('supplier.withdrawals.store') }}">
                    @csrf

                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-emerald-800 to-teal-900 px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-300 text-lg">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-base">Request Withdrawal</h3>
                                <p class="text-xs text-emerald-200/70">Submit payout request to admin</p>
                            </div>
                        </div>
                        <button type="button" @click="requestModalOpen = false" class="text-white/70 hover:text-white text-lg">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Balance Highlights --}}
                        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Withdrawable Balance</div>
                                <div class="text-xl font-black text-emerald-700 mt-0.5">৳{{ number_format($withdrawableBalance, 2) }}</div>
                            </div>
                            <button type="button" @click="setFullBalance()"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                                Full Balance
                            </button>
                        </div>

                        {{-- Amount Input --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Withdrawal Amount (৳) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm pointer-events-none">৳</span>
                                <input type="number" step="0.01" min="10" :max="maxWithdrawable" name="amount" x-model="amount" required
                                       placeholder="Enter amount (Min ৳10)"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            </div>
                            {{-- Quick Amount Pills --}}
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="text-[11px] text-slate-400 font-semibold">Quick:</span>
                                <button type="button" @click="setQuickAmount(500)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">৳500</button>
                                <button type="button" @click="setQuickAmount(1000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">৳1,000</button>
                                <button type="button" @click="setQuickAmount(2000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">৳2,000</button>
                                <button type="button" @click="setQuickAmount(5000)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">৳5,000</button>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label class="cursor-pointer border rounded-xl p-2.5 flex flex-col items-center gap-1 transition text-center"
                                       :class="paymentMethod === 'bkash' ? 'border-pink-500 bg-pink-50/50 text-pink-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="payment_method" value="bkash" x-model="paymentMethod" class="sr-only">
                                    <span class="text-xs font-bold">bKash</span>
                                    <span class="text-[10px] text-slate-400">Personal / Merchant</span>
                                </label>

                                <label class="cursor-pointer border rounded-xl p-2.5 flex flex-col items-center gap-1 transition text-center"
                                       :class="paymentMethod === 'nagad' ? 'border-orange-500 bg-orange-50/50 text-orange-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="payment_method" value="nagad" x-model="paymentMethod" class="sr-only">
                                    <span class="text-xs font-bold">Nagad</span>
                                    <span class="text-[10px] text-slate-400">Personal</span>
                                </label>

                                <label class="cursor-pointer border rounded-xl p-2.5 flex flex-col items-center gap-1 transition text-center"
                                       :class="paymentMethod === 'rocket' ? 'border-purple-500 bg-purple-50/50 text-purple-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="payment_method" value="rocket" x-model="paymentMethod" class="sr-only">
                                    <span class="text-xs font-bold">Rocket</span>
                                    <span class="text-[10px] text-slate-400">Personal</span>
                                </label>

                                <label class="cursor-pointer border rounded-xl p-2.5 flex flex-col items-center gap-1 transition text-center"
                                       :class="paymentMethod === 'bank' ? 'border-blue-500 bg-blue-50/50 text-blue-700 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-600'">
                                    <input type="radio" name="payment_method" value="bank" x-model="paymentMethod" class="sr-only">
                                    <span class="text-xs font-bold">Bank Account</span>
                                    <span class="text-[10px] text-slate-400">BEFTN / Routing</span>
                                </label>
                            </div>
                        </div>

                        {{-- Payment Details --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Account / Payment Details <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="payment_details" x-model="paymentDetails" required rows="2"
                                      placeholder="e.g. bKash Personal: 017XXXXXXXX OR Bank: City Bank, A/C: 123456789, Branch: Dhanmondi"
                                      class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Admin will disburse your payout to this account or number.</p>
                        </div>

                        {{-- Optional Note --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Note to Admin (Optional)
                            </label>
                            <input type="text" name="note" x-model="note"
                                   placeholder="Any special remarks or urgency instructions"
                                   class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="requestModalOpen = false"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition">
                            <i class="fas fa-check mr-1.5"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
