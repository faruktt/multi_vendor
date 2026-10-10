@extends('layouts.app')
@section('title', 'Commission Report: ' . $supplier->display_name)
@section('heading', 'Supplier Commission & Sales Report')

@section('content')
<div class="py-4 space-y-6" x-data="supplierCommissionReportData()">

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
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
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.suppliers.manage') }}"
               class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center transition shadow-sm">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-900">{{ $supplier->display_name }}</h1>
                    @if($supplier->isActive())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">{{ ucfirst($supplier->status) }}</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Owner: {{ $supplier->name }} &bull; {{ $supplier->email }} &bull; {{ $supplier->phone }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            {{-- Balance & Direct Withdraw Quick Card --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl px-4 py-2.5 shadow-sm flex items-center gap-3">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Available Balance</div>
                    <div class="text-base font-black">৳{{ number_format($supplier->availableBalance(), 2) }}</div>
                </div>
                @if($supplier->isActive())
                    <button type="button" @click="directModalOpen = true"
                            class="px-3 py-1.5 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-bold text-xs shadow-xs transition flex items-center gap-1.5 hover:scale-105 active:scale-95">
                        <i class="fas fa-hand-holding-dollar"></i> Direct Withdraw
                    </button>
                @endif
            </div>

            {{-- Commission Rate Setting Quick Card --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl px-4 py-2.5 shadow-sm flex items-center gap-3">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Commission Rate</div>
                    <div class="text-base font-black text-emerald-600">{{ $supplier->commission_percentage }}%</div>
                </div>
                <form method="POST" action="{{ route('admin.suppliers.commission.update', $supplier->id) }}" class="flex items-center gap-1.5">
                    @csrf
                    <div class="relative w-20">
                        <input type="number" step="0.1" min="0" max="100" name="commission_percentage"
                               value="{{ $supplier->commission_percentage }}"
                               class="w-full px-2 py-1 text-xs font-black text-center rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 bg-slate-50">
                        <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none">%</span>
                    </div>
                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Gross Sales --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gross Sales</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="fas fa-sack-dollar"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">৳{{ number_format($totalSalesAmount, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Total revenue generated</div>
        </div>

        {{-- Items Sold --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Items Sold</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-box-open"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalItemsSold) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Units shipped to customers</div>
        </div>

        {{-- Admin Commission Earned --}}
        <div class="bg-white rounded-2xl border border-emerald-200 p-4 shadow-sm bg-gradient-to-br from-white to-emerald-50/60">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Admin Commission</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-sm shadow-md shadow-emerald-500/20">
                    <i class="fas fa-percentage"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-700 mt-2">+৳{{ number_format($totalCommissionEarned, 2) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">Platform earned profit</div>
        </div>

        {{-- Supplier Net Payout --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Supplier Net Share</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fas fa-wallet"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">৳{{ number_format($totalNetEarnings, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Net payable to supplier</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.suppliers.commission.report', $supplier->id) }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">From:</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">To:</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700 focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                Filter
            </button>

            @if(request()->anyFilled(['from', 'to']))
            <a href="{{ route('admin.suppliers.commission.report', $supplier->id) }}"
               class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Itemized Sales & Commission Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Itemized Order History &amp; Commission Breakdown</h3>
            <span class="text-xs text-slate-400 font-semibold">{{ $saleItems->total() }} recorded transactions</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Order Invoice</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Product / Variant</th>
                        <th class="px-3 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Gross Sale</th>
                        <th class="px-3 py-3 text-center">Comm. %</th>
                        <th class="px-4 py-3 text-right">Admin Commission</th>
                        <th class="px-4 py-3 text-right">Supplier Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($saleItems as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        {{-- Date --}}
                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                            <div>{{ $item->created_at->format('d M, Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- Order Invoice --}}
                        <td class="px-4 py-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                            #{{ $item->sale?->invoice_no ?? 'N/A' }}
                        </td>

                        {{-- Customer --}}
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-800">{{ $item->sale?->customer?->name ?? 'Guest' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->sale?->customer?->phone ?? '' }}</div>
                        </td>

                        {{-- Product --}}
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-900 max-w-[220px] truncate">
                                {{ $item->product?->name ?? 'Unknown Product' }}
                            </div>
                            @if($item->variant_name)
                                <span class="inline-block text-[10px] bg-purple-50 text-purple-700 font-semibold px-2 py-0.5 rounded-full mt-0.5">
                                    {{ $item->variant_name }}
                                </span>
                            @endif
                        </td>

                        {{-- Qty --}}
                        <td class="px-3 py-3 text-center font-bold">
                            {{ $item->quantity }}
                        </td>

                        {{-- Gross Subtotal --}}
                        <td class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap">
                            ৳{{ number_format($item->subtotal, 2) }}
                            <div class="text-[10px] text-slate-400 font-normal">৳{{ number_format($item->unit_price, 2) }} each</div>
                        </td>

                        {{-- Comm. Rate --}}
                        <td class="px-3 py-3 text-center font-bold text-slate-600 whitespace-nowrap">
                            {{ $item->admin_commission_rate ?? $supplier->commission_percentage }}%
                        </td>

                        {{-- Admin Commission Amount --}}
                        <td class="px-4 py-3 text-right font-black text-emerald-600 whitespace-nowrap">
                            +৳{{ number_format($item->admin_commission_amount, 2) }}
                        </td>

                        {{-- Supplier Net --}}
                        <td class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap">
                            ৳{{ number_format($item->supplier_earning, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 mx-auto flex items-center justify-center text-xl text-slate-300 mb-2">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">No sales or commission records yet</div>
                            <p class="text-xs text-slate-400 mt-1">When customers purchase items from this supplier, commission records will automatically show here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($saleItems->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $saleItems->links() }}
        </div>
        @endif
    </div>

    {{-- Direct Profit Withdrawal Modal --}}
    <div x-show="directModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="directModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="directModalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="directModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-slate-100">

                <form action="{{ route('admin.suppliers.withdraw', $supplier) }}" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h3 class="font-bold text-base">Withdraw Profit</h3>
                        </div>
                        <button type="button" @click="directModalOpen = false" class="text-white/70 hover:text-white text-base">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-3.5 max-h-[75vh] overflow-y-auto">
                        {{-- Supplier Info Box --}}
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-slate-800">{{ $supplier->display_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $supplier->phone ?: 'No phone' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Balance</div>
                                <div class="text-base font-black text-emerald-600">৳{{ number_format($supplier->availableBalance(), 2) }}</div>
                            </div>
                        </div>

                        @if($supplier->availableBalance() <= 0)
                            <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-1.5">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i>
                                <span>No available balance</span>
                            </div>
                        @endif

                        {{-- Amount Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">
                                    Amount (৳) <span class="text-rose-500">*</span>
                                </label>
                                @if($supplier->availableBalance() > 0)
                                    <button type="button" @click="setFullDirectBalance()"
                                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800">
                                        Max (৳{{ number_format($supplier->availableBalance(), 2) }})
                                    </button>
                                @endif
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">৳</span>
                                <input type="number" step="0.01" min="0.01"
                                       max="{{ $supplier->availableBalance() }}"
                                       name="amount" x-model="directAmount" required
                                       placeholder="0.00"
                                       class="w-full pl-7 pr-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        {{-- Payment Method Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" name="payment_method" :value="directPaymentMethod">
                            <div class="grid grid-cols-5 gap-1.5">
                                <button type="button" @click="setPaymentMethod('bkash')"
                                        :class="directPaymentMethod === 'bkash' ? 'bg-pink-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    bKash
                                </button>
                                <button type="button" @click="setPaymentMethod('nagad')"
                                        :class="directPaymentMethod === 'nagad' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Nagad
                                </button>
                                <button type="button" @click="setPaymentMethod('rocket')"
                                        :class="directPaymentMethod === 'rocket' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Rocket
                                </button>
                                <button type="button" @click="setPaymentMethod('bank')"
                                        :class="directPaymentMethod === 'bank' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Bank
                                </button>
                                <button type="button" @click="setPaymentMethod('cash')"
                                        :class="directPaymentMethod === 'cash' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Cash
                                </button>
                            </div>
                        </div>

                        {{-- Payment Details / Account / Trx ID --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Account / Trx ID
                            </label>
                            <input type="text" name="payment_details" x-model="directPaymentDetails"
                                   placeholder="Number or Trx ID"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>

                        {{-- Note --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Note (Optional)
                            </label>
                            <input type="text" name="note" x-model="directNote"
                                   placeholder="Enter note..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="directModalOpen = false"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="!directAmount || Number(directAmount) <= 0 || Number(directAmount) > {{ (float) $supplier->availableBalance() }}"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i class="fas fa-check"></i> Withdraw
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function supplierCommissionReportData() {
    return {
        directModalOpen: false,
        directAmount: '',
        directPaymentMethod: '{{ $supplier->bkash_number ? 'bkash' : ($supplier->bank_info ? 'bank' : 'cash') }}',
        directPaymentDetails: '{{ addslashes($supplier->bkash_number ?: ($supplier->bank_info ?: '')) }}',
        directNote: '',
        availableBalance: {{ (float) $supplier->availableBalance() }},
        setPaymentMethod(method) {
            this.directPaymentMethod = method;
            if (method === 'bkash' && '{{ addslashes($supplier->bkash_number ?? '') }}') {
                this.directPaymentDetails = '{{ addslashes($supplier->bkash_number ?? '') }}';
            } else if (method === 'bank' && '{{ addslashes($supplier->bank_info ?? '') }}') {
                this.directPaymentDetails = '{{ addslashes($supplier->bank_info ?? '') }}';
            } else if (method === 'cash') {
                this.directPaymentDetails = 'Cash Payout';
            } else {
                this.directPaymentDetails = '';
            }
        },
        setFullDirectBalance() {
            if (this.availableBalance > 0) {
                this.directAmount = parseFloat(this.availableBalance).toFixed(2);
            }
        }
    };
}
</script>
@endsection
