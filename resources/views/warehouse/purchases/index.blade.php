@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('heading', 'Purchase Orders')

@section('content')
<div x-data="purchaseIndex()"
     @open-pay-modal.window="openPay($event.detail)">

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap gap-2.5 items-center">
    <div class="relative flex-1 min-w-[180px]">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice or supplier..."
               class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50">
    </div>
    <select name="supplier_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-slate-50">
        <option value="">All Suppliers</option>
        @foreach($suppliers as $s)
        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    <select name="payment_status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-slate-50">
        <option value="">All Status</option>
        <option value="paid"    {{ request('payment_status') === 'paid'    ? 'selected' : '' }}>Paid</option>
        <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
    </select>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 transition-colors">
        <i class="fas fa-filter text-xs"></i> Filter
    </button>
    @if(request()->hasAny(['search','supplier_id','payment_status']))
    <a href="{{ route('admin.warehouse.purchases.index') }}" class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50">Reset</a>
    @endif
    <div class="ml-auto">
        <a href="{{ route('admin.warehouse.purchases.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
            <i class="fas fa-plus"></i> New Purchase
        </a>
    </div>
</form>

{{-- ── Summary Stats ─────────────────────────────────────────────── --}}
@php
    $totalAmt = $purchases->sum('total');
    $paidAmt  = $purchases->sum('paid_amount');
    $dueAmt   = $purchases->sum('due_amount');
@endphp
<div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Orders</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $purchases->total() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Value</p>
        <p class="text-xl font-bold text-blue-600 mt-1">৳{{ number_format($totalAmt, 0) }}</p>
        <p class="text-[11px] text-emerald-600 mt-0.5">Paid ৳{{ number_format($paidAmt, 0) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Due</p>
        <p class="text-xl font-bold {{ $dueAmt > 0 ? 'text-red-500' : 'text-emerald-600' }} mt-1">৳{{ number_format($dueAmt, 0) }}</p>
    </div>
</div>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Date</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Invoice</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Supplier</th>
                <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Due</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
        @forelse($purchases as $purchase)
            {{-- Each row has OWN x-data; listens for its own pay-updated event --}}
            <tr class="hover:bg-slate-50/50 transition-colors"
                x-data="{ due: {{ $purchase->due_amount }}, paid: {{ $purchase->paid_amount }}, status: '{{ $purchase->payment_status }}' }"
                @pay-updated-{{ $purchase->id }}.window="due = $event.detail.due_amount; paid = $event.detail.paid_amount; status = $event.detail.payment_status">

                <td class="px-4 py-3 text-slate-400 text-xs">{{ $purchases->firstItem() + $loop->index }}</td>

                <td class="px-3 py-3 hidden md:table-cell">
                    <p class="text-[12px] text-slate-700 font-medium">{{ $purchase->created_at->format('d M Y') }}</p>
                    <p class="text-[11px] text-slate-400">{{ $purchase->created_at->format('h:i A') }}</p>
                </td>

                <td class="px-3 py-3">
                    <p class="font-mono text-[12px] font-bold text-slate-800">{{ $purchase->invoice_no }}</p>
                    <div class="mt-1 space-y-0.5">
                        @foreach($purchase->items->take(2) as $item)
                        <p class="text-[11px] text-slate-600 truncate max-w-[220px]">
                            <span class="font-bold text-slate-800">{{ $item->quantity }}×</span> {{ $item->product_name }}
                        </p>
                        @endforeach
                        @if($purchase->items->count() > 2)
                        <p class="text-[10px] text-blue-600 font-semibold">+{{ $purchase->items->count() - 2 }} more (Total Qty: {{ $purchase->items->sum('quantity') }})</p>
                        @else
                        <p class="text-[10px] text-slate-400 font-medium">Total Qty: <strong class="text-slate-700">{{ $purchase->items->sum('quantity') }}</strong></p>
                        @endif
                    </div>
                </td>

                <td class="px-3 py-3 hidden lg:table-cell">
                    @if($purchase->supplier)
                    <a href="{{ route('admin.warehouse.suppliers.report', $purchase->supplier) }}"
                       class="text-[12px] font-bold text-slate-800 hover:text-blue-600 hover:underline flex items-center gap-1.5">
                        <i class="fas fa-industry text-[10px] text-purple-500"></i>
                        {{ $purchase->supplier->name }}
                    </a>
                    @if($purchase->supplier->phone)
                    <p class="text-[11px] text-slate-400 mt-0.5"><i class="fas fa-phone text-[9px] mr-0.5"></i> {{ $purchase->supplier->phone }}</p>
                    @endif
                    @else
                    <span class="text-slate-400 text-xs italic">Walk-in / No Supplier</span>
                    @endif
                </td>

                <td class="px-3 py-3 text-right">
                    <p class="font-bold text-slate-800 text-[13px]">৳{{ number_format($purchase->total, 0) }}</p>
                    @if($purchase->discount > 0)
                    <p class="text-[10.5px] text-emerald-600">-৳{{ number_format($purchase->discount, 0) }}</p>
                    @endif
                </td>

                {{-- Due cell with Pay button — dispatches to parent --}}
                <td class="px-3 py-3 text-right hidden md:table-cell">
                    <template x-if="due > 0">
                        <div>
                            <p class="text-[12px] font-bold text-red-500">৳<span x-text="Number(due).toLocaleString('en')"></span></p>
                            <button type="button"
                                    @click="$dispatch('open-pay-modal', {
                                        id:      {{ $purchase->id }},
                                        action:  '{{ route('admin.warehouse.purchases.payment', $purchase) }}',
                                        invoice: '{{ $purchase->invoice_no }}',
                                        total:   {{ $purchase->total }},
                                        paid:    paid,
                                        due:     due,
                                        method:  '{{ $purchase->payment_method }}'
                                    })"
                                    class="mt-1 text-[10px] bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 px-2 py-0.5 rounded-lg font-medium transition-colors">
                                Pay Due
                            </button>
                        </div>
                    </template>
                    <template x-if="due <= 0">
                        <span class="text-[11px] text-emerald-600 font-semibold">Settled</span>
                    </template>
                </td>

                {{-- Status badge --}}
                <td class="px-3 py-3 text-center">
                    <span :class="{
                        'bg-emerald-100 text-emerald-700 border-emerald-200': status === 'paid',
                        'bg-amber-100 text-amber-700 border-amber-200':       status === 'partial',
                        'bg-red-100 text-red-600 border-red-200':             status === 'pending'
                    }" class="inline-flex items-center border px-2.5 py-1 rounded-lg text-[11.5px] font-semibold capitalize" x-text="status">
                    </span>
                </td>

                {{-- Actions --}}
                <td class="px-3 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('admin.warehouse.purchases.show', $purchase) }}"
                           title="View"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                        <a href="{{ route('admin.warehouse.purchases.edit', $purchase) }}"
                           title="Edit"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 transition-colors">
                            <i class="fas fa-pen text-xs"></i>
                        </a>
                        <a href="{{ route('admin.warehouse.purchases.show', $purchase) }}?auto_print=1"
                           target="_blank" title="Print"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                            <i class="fas fa-print text-xs"></i>
                        </a>
                        <button type="button" title="Delete"
                                @click="openDelete({{ $purchase->id }}, '{{ $purchase->invoice_no }}', '{{ route('admin.warehouse.purchases.destroy', $purchase) }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-colors">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                    <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shopping-cart text-3xl text-slate-300"></i>
                    </div>
                    <p class="font-medium text-slate-500">No purchase orders found</p>
                    <a href="{{ route('admin.warehouse.purchases.create') }}"
                       class="mt-3 inline-flex items-center gap-1.5 text-blue-600 text-sm hover:underline">
                        <i class="fas fa-plus text-xs"></i> Create first purchase
                    </a>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
    @if($purchases->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $purchases->links() }}</div>
    @endif
</div>

{{-- ══ PAY DUE MODAL (single, outside table, parent scope) ═════════ --}}
<div x-show="payModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="payModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="payModal = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-money-bill-wave text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Update Payment</p>
                <p class="text-xs text-slate-500 font-mono" x-text="payInvoice"></p>
            </div>
        </div>
        {{-- Info: total + paid so far --}}
        <div class="grid grid-cols-2 gap-2 bg-slate-50 rounded-xl p-3 mb-4 border border-slate-100 text-center">
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Total</p>
                <p class="font-bold text-slate-700 text-sm mt-0.5">৳<span x-text="Number(payTotal).toLocaleString('en')"></span></p>
            </div>
            <div class="border-l border-slate-200">
                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Already Paid</p>
                <p class="font-bold text-emerald-600 text-sm mt-0.5">৳<span x-text="Number(payPrevPaid).toLocaleString('en')"></span></p>
            </div>
        </div>
        <form @submit.prevent="submitPay($event)" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount Paying Now (৳)</label>
                <input type="number" name="additional_amount" step="0.01" min="0"
                       :max="payDue" x-model.number="payInputPaid"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-800 text-lg">
                {{-- Live status --}}
                <div class="mt-2.5 flex items-center justify-between px-1">
                    <span class="text-xs text-slate-400">After payment:</span>
                    <template x-if="payInputPaid >= payDue">
                        <span class="flex items-center gap-1 text-emerald-600 font-bold text-xs">
                            <i class="fas fa-check-circle"></i> Fully Paid
                        </span>
                    </template>
                    <template x-if="payInputPaid < payDue && payInputPaid > 0">
                        <span class="text-red-500 font-bold text-xs">
                            Due: ৳<span x-text="(payDue - payInputPaid).toFixed(2)"></span> remaining
                        </span>
                    </template>
                    <template x-if="payInputPaid <= 0">
                        <span class="text-slate-400 text-xs">Enter amount above</span>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Payment Method</label>
                <select name="payment_method"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}" :selected="payMethod === '{{ $key }}'">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="payModal = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50 font-medium">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-bold">
                    <i class="fas fa-check text-xs mr-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Delete Confirm Modal ─────────────────────────────────────── --}}
<div x-show="delModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delModal = false">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trash-alt text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Delete Purchase?</p>
                <p class="text-sm text-slate-500 mt-0.5">Stock will be reversed. Cannot be undone.</p>
            </div>
        </div>
        <div class="bg-slate-50 rounded-xl px-4 py-2.5 mb-5 border border-slate-100">
            <p class="text-sm font-mono font-semibold text-slate-700" x-text="delName"></p>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                Cancel
            </button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">
                    <i class="fas fa-trash-alt text-xs mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

</div>{{-- end x-data purchaseIndex() --}}

@push('scripts')
<script>
function purchaseIndex() {
    return {
        /* Delete modal */
        delModal: false, delName: '', delUrl: '',

        /* Pay Due modal — single instance, parent-owned */
        payModal: false,
        payId: null,
        payAction: '',
        payInvoice: '',
        payTotal: 0,
        payPrevPaid: 0,    // what was previously paid (display only)
        payDue: 0,         // current due from DB (display only)
        payInputPaid: 0,   // x-model for the input field
        payMethod: 'cash',

        openDelete(id, name, url) {
            this.delName = name;
            this.delUrl  = url;
            this.delModal = true;
        },

        openPay(detail) {
            this.payId        = detail.id;
            this.payAction    = detail.action;
            this.payInvoice   = detail.invoice;
            this.payTotal     = Number(detail.total);
            this.payPrevPaid  = Number(detail.paid);
            this.payDue       = Number(detail.due);
            this.payInputPaid = Number(detail.due);   // input starts at due amount
            this.payMethod    = detail.method;
            this.payModal     = true;
        },

        async submitPay(e) {
            const form     = e.target;
            const formData = new FormData(form);
            try {
                const res = await fetch(this.payAction, {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept':       'application/json',
                    },
                    body: formData,
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    /* broadcast to the specific row's listener */
                    window.dispatchEvent(new CustomEvent('pay-updated-' + this.payId, { detail: json }));
                    this.payModal = false;
                    showToast('Payment updated!', 'success');
                } else {
                    showToast(json.message || 'Payment update failed.', 'error');
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
            }
        },
    };
}
</script>
@endpush
@endsection
