@extends('layouts.app')
@section('title', 'Purchase — ' . $purchase->invoice_no)
@section('heading', 'Purchase Details')

@push('styles')
<style>
@media print {
    @page { size: 80mm auto; margin: 3mm 4mm; }
    body * { visibility: hidden !important; }
    #pur-receipt, #pur-receipt * { visibility: visible !important; }
    #pur-receipt {
        display: block !important;
        position: absolute !important;
        top: 0; left: 0;
        width: 72mm;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
    }
}
</style>
@endpush

@section('content')
<div x-data="showPurchase()" x-init="init()">

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2.5 mb-5">
    <a href="{{ route('admin.warehouse.purchases.index') }}"
       class="flex items-center gap-1.5 border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
    <a href="{{ route('admin.warehouse.purchases.edit', $purchase) }}"
       class="flex items-center gap-1.5 border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
        <i class="fas fa-pen text-xs"></i> Edit
    </a>
    <button onclick="window.print()"
            class="flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
        <i class="fas fa-print text-xs"></i> Print
    </button>

    @php $anyReturnable = $purchase->items->sum(fn($i) => $i->quantity - $i->returnItems->sum('quantity')) > 0; @endphp
    <button @click="returnModal = true" @disabled(!$anyReturnable)
            class="flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-xl text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
        <i class="fas fa-rotate-left text-xs"></i> Return
    </button>

    @if($purchase->payment_status !== 'paid')
    <button @click="payOpen = true"
            class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors shadow-sm shadow-emerald-200">
        <i class="fas fa-money-bill-wave text-xs"></i> Pay Due
        <span class="bg-white/25 rounded-lg px-1.5 py-0.5 text-xs font-bold ml-0.5">৳{{ number_format($purchase->due_amount, 0) }}</span>
    </button>
    @endif

    <div class="ml-auto">
        @php
            $cls = match($purchase->payment_status) {
                'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'partial' => 'bg-amber-100 text-amber-700 border-amber-200',
                default   => 'bg-red-100 text-red-600 border-red-200',
            };
        @endphp
        <span class="border {{ $cls }} px-3 py-1.5 rounded-xl text-sm font-bold capitalize">
            {{ $purchase->payment_status }}
        </span>
    </div>
</div>

{{-- ── Info Cards ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-2.5 mb-3 pb-3 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-file-invoice text-blue-600 text-xs"></i>
            </div>
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Purchase Info</span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-start">
                <span class="text-slate-400 text-xs">Invoice</span>
                <span class="font-mono font-bold text-slate-800 text-xs">{{ $purchase->invoice_no }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 text-xs">Date</span>
                <span class="font-medium text-slate-700 text-xs">{{ $purchase->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 text-xs">Time</span>
                <span class="text-slate-600 text-xs">{{ $purchase->created_at->format('h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 text-xs">Method</span>
                <span class="capitalize font-medium text-slate-700 text-xs">{{ $purchase->payment_method }}</span>
            </div>
            @if($purchase->createdBy)
            <div class="flex justify-between">
                <span class="text-slate-400 text-xs">Created By</span>
                <span class="text-slate-600 text-xs">{{ $purchase->createdBy->name }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-2.5 mb-3 pb-3 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center">
                <i class="fas fa-truck text-purple-600 text-xs"></i>
            </div>
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Supplier</span>
        </div>
        @if($purchase->supplier)
        <div class="space-y-2">
            <p class="font-bold text-slate-800 text-sm">{{ $purchase->supplier->name }}</p>
            @if($purchase->supplier->phone)
            <p class="text-slate-500 text-xs flex items-center gap-1.5">
                <i class="fas fa-phone text-[9px] text-slate-400 w-3"></i>{{ $purchase->supplier->phone }}
            </p>
            @endif
            @if($purchase->supplier->email)
            <p class="text-slate-500 text-xs flex items-center gap-1.5">
                <i class="fas fa-envelope text-[9px] text-slate-400 w-3"></i>{{ $purchase->supplier->email }}
            </p>
            @endif
            @if($purchase->supplier->address)
            <p class="text-slate-500 text-xs flex items-start gap-1.5">
                <i class="fas fa-map-marker-alt text-[9px] text-slate-400 w-3 mt-0.5"></i>{{ $purchase->supplier->address }}
            </p>
            @endif
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-20 text-slate-300">
            <i class="fas fa-user-slash text-2xl mb-1"></i>
            <p class="text-xs">No supplier linked</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-2.5 mb-3 pb-3 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-wallet text-emerald-600 text-xs"></i>
            </div>
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">Payment Summary</span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-xs">
                <span class="text-slate-400">Subtotal</span>
                <span class="text-slate-700">৳{{ number_format($purchase->subtotal, 2) }}</span>
            </div>
            @if($purchase->discount > 0)
            <div class="flex justify-between text-xs">
                <span class="text-slate-400">Discount</span>
                <span class="text-emerald-600">−৳{{ number_format($purchase->discount, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold border-t border-slate-100 pt-2">
                <span class="text-slate-700 text-sm">Total</span>
                <span class="text-blue-600 text-sm">৳{{ number_format($purchase->total, 2) }}</span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-400">Paid</span>
                <span class="text-emerald-600 font-semibold">৳{{ number_format($purchase->paid_amount, 2) }}</span>
            </div>
            @if($purchase->due_amount > 0)
            <div class="flex justify-between font-bold text-red-500 text-sm bg-red-50 rounded-lg px-3 py-2 -mx-1">
                <span>Due</span>
                <span>৳{{ number_format($purchase->due_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Items Table ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-4">
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-cubes text-blue-600 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Items ({{ $purchase->items->count() }})</h3>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Unit Cost</th>
                <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($purchase->items as $i => $item)
            <tr class="hover:bg-slate-50/50">
                <td class="px-4 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                <td class="px-4 py-3">
                    <p class="font-semibold text-slate-800 text-[13px]">{{ $item->product_name }}</p>
                    @if($item->product?->sku)
                    <p class="text-[10.5px] text-slate-400 font-mono">{{ $item->product->sku }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-lg text-xs font-bold">
                        {{ $item->quantity }}
                        @if($item->product?->unit)<span class="font-normal text-blue-500 ml-0.5">{{ $item->product->unit }}</span>@endif
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-slate-700 text-[13px]">৳{{ number_format($item->unit_cost, 2) }}</td>
                <td class="px-4 py-3 text-right font-bold text-slate-800 text-[13px]">৳{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-slate-50 border-t border-slate-200">
            <tr>
                <td colspan="4" class="px-4 py-3 text-right font-bold text-slate-600 text-sm">Grand Total</td>
                <td class="px-4 py-3 text-right font-bold text-blue-600 text-base">৳{{ number_format($purchase->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Note --}}
@if($purchase->note)
<div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3.5 mb-4 flex items-start gap-3">
    <i class="fas fa-sticky-note text-amber-500 mt-0.5"></i>
    <div>
        <p class="text-xs font-semibold text-amber-700 mb-0.5">Note</p>
        <p class="text-sm text-amber-800">{{ $purchase->note }}</p>
    </div>
</div>
@endif

{{-- Return history --}}
@if($purchase->returns->count() > 0)
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Return History</p>
    <div class="space-y-1.5">
        @foreach($purchase->returns as $return)
        <div class="text-xs text-slate-600 bg-red-50/60 border border-red-100 px-3 py-2 rounded-lg">
            <div class="flex justify-between items-center">
                <span>{{ $return->created_at->format('d M Y, h:i A') }}</span>
                <span class="text-slate-400">{{ ucfirst($return->refund_method) }}</span>
                <span class="font-bold text-red-600">−৳{{ number_format($return->refund_amount, 2) }}</span>
            </div>
            <div class="mt-1 text-slate-500">
                {{ $return->items->map(fn($i) => $i->product?->name . ' ×' . $i->quantity)->implode(', ') }}
            </div>
            @if($return->reason)
            <div class="mt-0.5 text-slate-400 italic">"{{ $return->reason }}"</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ Pay Due Modal ════════════════════════════════════════════════ --}}
<div x-show="payOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="payOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="payOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-money-bill-wave text-emerald-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Update Payment</p>
                <p class="text-xs text-slate-500 font-mono">{{ $purchase->invoice_no }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 bg-slate-50 rounded-xl p-3 mb-5 border border-slate-100 text-center">
            <div>
                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Total</p>
                <p class="font-bold text-slate-700 text-sm mt-0.5">৳{{ number_format($purchase->total, 0) }}</p>
            </div>
            <div class="border-l border-slate-200">
                <p class="text-[10px] text-slate-400 uppercase tracking-wide">Already Paid</p>
                <p class="font-bold text-emerald-600 text-sm mt-0.5">৳{{ number_format($purchase->paid_amount, 0) }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.warehouse.purchases.payment', $purchase) }}"
              class="space-y-3" x-data="{ inp: {{ $purchase->due_amount }}, due: {{ $purchase->due_amount }} }">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount Paying Now (৳)</label>
                <input type="number" name="additional_amount" step="0.01" min="0" max="{{ $purchase->due_amount }}"
                       x-model.number="inp" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-lg font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-800">
                <div class="mt-2.5 flex items-center justify-between px-1">
                    <span class="text-xs text-slate-400">After payment:</span>
                    <template x-if="inp >= due">
                        <span class="flex items-center gap-1 text-emerald-600 font-bold text-xs">
                            <i class="fas fa-check-circle"></i> Fully Paid
                        </span>
                    </template>
                    <template x-if="inp < due && inp > 0">
                        <span class="text-red-500 font-bold text-xs">
                            Due: ৳<span x-text="(due - inp).toFixed(2)"></span> remaining
                        </span>
                    </template>
                    <template x-if="inp <= 0">
                        <span class="text-slate-400 text-xs">Enter amount above</span>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Payment Method</label>
                <select name="payment_method"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}" {{ $purchase->payment_method === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="payOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-sm font-bold">
                    <i class="fas fa-check text-xs mr-1"></i> Save Payment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Return Items Modal (screen only) ════════════════════════════ --}}
<div x-show="returnModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 no-print"
     @keydown.escape.window="returnModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.outside="returnModal = false">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fas fa-rotate-left text-red-500"></i>
                </div>
                <p class="font-bold text-slate-800">Return Items to Supplier</p>
            </div>
            <button @click="returnModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div class="space-y-2.5">
                <template x-for="item in returnItems" :key="item.purchase_item_id">
                    <div class="flex items-center gap-3 border border-slate-100 rounded-xl px-3 py-2.5" :class="item.max === 0 ? 'opacity-40' : ''">
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-slate-700 truncate" x-text="item.name"></p>
                            <p class="text-[11px] text-slate-400" x-text="'৳' + item.unit_cost.toLocaleString() + ' each · ' + item.max + ' returnable'"></p>
                        </div>
                        <input type="number" x-model.number="item.qty" min="0" :max="item.max" :disabled="item.max === 0"
                               class="w-16 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                </template>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Reason (optional)</label>
                <textarea x-model="returnReason" rows="2" placeholder="e.g. Damaged, wrong item shipped..."
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Refund Method</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($paymentMethods as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" x-model="returnMethod" value="{{ $key }}" class="sr-only">
                        <div class="border rounded-xl px-2 py-2 text-center text-xs font-semibold transition-all"
                             :class="returnMethod === '{{ $key }}' ? 'border-red-500 bg-red-50 text-red-700' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                            {{ $label }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-between items-center bg-slate-50 rounded-xl px-3 py-2.5 text-sm">
                <span class="text-slate-500">Refund Total</span>
                <span class="font-bold text-red-600">৳<span x-text="returnTotal.toLocaleString()"></span></span>
            </div>
        </div>
        <div class="px-5 pb-5 flex gap-3">
            <button @click="returnModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button @click="submitReturn()"
                    :disabled="returnSaving || returnTotal <= 0"
                    class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
                <span x-show="!returnSaving" class="flex items-center justify-center gap-1.5">
                    <i class="fas fa-check text-xs"></i> Confirm Return
                </span>
                <span x-show="returnSaving"><i class="fas fa-spinner fa-spin"></i></span>
            </button>
        </div>
    </div>
</div>

{{-- ══ 80mm PRINT RECEIPT ═══════════════════════════════════════════ --}}
<div id="pur-receipt" style="display:none">
    <div style="text-align:center;margin-bottom:8px">
        <p style="font-weight:bold;font-size:14px;margin:0">{{ $branch->name }}</p>
        @if($branch->address)<p style="font-size:10px;margin:2px 0">{{ $branch->address }}</p>@endif
        @if($branch->phone)<p style="font-size:10px;margin:2px 0">{{ $branch->phone }}</p>@endif
    </div>
    <div style="border-top:1px dashed #333;margin:5px 0"></div>
    <p style="text-align:center;font-weight:bold;font-size:12px;margin:3px 0;letter-spacing:1px">PURCHASE ORDER</p>
    <div style="border-top:1px dashed #333;margin:5px 0"></div>
    <table style="width:100%;font-size:10px;line-height:1.6">
        <tr><td>Invoice</td><td style="text-align:right;font-weight:bold">{{ $purchase->invoice_no }}</td></tr>
        <tr><td>Date</td><td style="text-align:right">{{ $purchase->created_at->format('d/m/Y h:i A') }}</td></tr>
        @if($purchase->supplier)
        <tr><td>Supplier</td><td style="text-align:right">{{ $purchase->supplier->name }}</td></tr>
        @if($purchase->supplier->phone)<tr><td>Phone</td><td style="text-align:right">{{ $purchase->supplier->phone }}</td></tr>@endif
        @endif
        <tr><td>Method</td><td style="text-align:right;text-transform:capitalize">{{ $purchase->payment_method }}</td></tr>
    </table>
    <div style="border-top:1px dashed #333;margin:5px 0"></div>
    <table style="width:100%;font-size:10px;border-collapse:collapse">
        <thead>
            <tr style="border-bottom:1px solid #000">
                <th style="text-align:left;padding:2px 0">Product</th>
                <th style="text-align:center;padding:2px 2px">Qty</th>
                <th style="text-align:right;padding:2px 0">Rate</th>
                <th style="text-align:right;padding:2px 0">Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr style="border-bottom:1px dotted #ccc">
                <td style="padding:3px 0;font-weight:bold;font-size:9px">{{ $item->product_name }}</td>
                <td style="text-align:center;padding:3px 2px">{{ $item->quantity }}</td>
                <td style="text-align:right;padding:3px 0">{{ number_format($item->unit_cost, 2) }}</td>
                <td style="text-align:right;padding:3px 0;font-weight:bold">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="border-top:1px dashed #333;margin:5px 0"></div>
    <table style="width:100%;font-size:10px;line-height:1.6">
        <tr><td>Subtotal</td><td style="text-align:right">{{ number_format($purchase->subtotal, 2) }}</td></tr>
        @if($purchase->discount > 0)
        <tr><td>Discount</td><td style="text-align:right">-{{ number_format($purchase->discount, 2) }}</td></tr>
        @endif
        <tr style="font-weight:bold;font-size:12px;border-top:1px solid #333">
            <td style="padding-top:3px">TOTAL</td>
            <td style="text-align:right;padding-top:3px">৳{{ number_format($purchase->total, 2) }}</td>
        </tr>
        <tr><td>Paid</td><td style="text-align:right">৳{{ number_format($purchase->paid_amount, 2) }}</td></tr>
        @if($purchase->due_amount > 0)
        <tr style="font-weight:bold">
            <td>DUE</td>
            <td style="text-align:right">৳{{ number_format($purchase->due_amount, 2) }}</td>
        </tr>
        @endif
    </table>
    @if($purchase->note)
    <div style="border-top:1px dashed #333;margin:5px 0;font-size:9px">
        Note: {{ $purchase->note }}
    </div>
    @endif
    <div style="border-top:1px dashed #333;margin:8px 0;text-align:center;font-size:9px;color:#666">
        <p>Thank you!</p>
        <p style="margin-top:1px">{{ now()->format('d/m/Y h:i A') }}</p>
    </div>
</div>

</div>{{-- x-data --}}

@php
$returnItemsData = $purchase->items->map(function ($i) {
    $alreadyReturned = $i->returnItems->sum('quantity');
    $remaining = $i->quantity - $alreadyReturned;
    $stockCap = $i->product?->stock_qty ?? 0;
    return [
        'purchase_item_id' => $i->id,
        'name'              => $i->product_name,
        'unit_cost'         => (float) $i->unit_cost,
        'max'               => max(0, min($remaining, $stockCap)),
        'qty'               => 0,
    ];
});
@endphp
@push('scripts')
<script>
function showPurchase() {
    return {
        payOpen: false,
        returnModal: false,
        returnSaving: false,
        returnReason: '',
        returnMethod: 'cash',
        returnItems: @json($returnItemsData),

        get returnTotal() {
            return this.returnItems.reduce((s, i) => s + (i.qty || 0) * i.unit_cost, 0);
        },

        async submitReturn() {
            const items = this.returnItems.filter(i => i.qty > 0).map(i => ({ purchase_item_id: i.purchase_item_id, quantity: i.qty }));
            if (items.length === 0) return;
            this.returnSaving = true;
            try {
                const res = await fetch('{{ route('admin.warehouse.purchases.return', $purchase) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ items, reason: this.returnReason, refund_method: this.returnMethod }),
                });
                if (res.ok) {
                    showToast('Return recorded!', 'success');
                    window.location.reload();
                } else {
                    const data = await res.json().catch(() => ({}));
                    const msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Return failed.');
                    showToast(msg, 'error');
                }
            } catch (e) {
                showToast('Connection error.', 'error');
            }
            this.returnSaving = false;
        },

        init() {
            @if(request()->has('auto_print'))
            setTimeout(() => window.print(), 400);
            @endif
        }
    };
}
</script>
@endpush
@endsection
