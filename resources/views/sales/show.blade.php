@extends('layouts.app')
@section('title', 'Invoice — ' . $sale->invoice_no)
@section('heading', 'Invoice: ' . $sale->invoice_no)

@push('styles')
<style>
/* Screen: hide the receipt div */
#receipt { display: none; }

/* Print: show ONLY the receipt, hide everything else */
@media print {
    @page { size: 80mm auto; margin: 3mm 4mm; }
    body * { visibility: hidden !important; }
    #receipt {
        display: block !important;
        visibility: visible !important;
        position: absolute !important;
        top: 0; left: 0;
        width: 72mm;
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        color: #000;
        background: #fff;
    }
    #receipt * { visibility: visible !important; }
}
</style>
@endpush

@section('content')
<div x-data="showSale()">

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2.5 mb-5 no-print">
    <a href="{{ route('branch.sales.index', $branch) }}"
       class="flex items-center gap-1.5 text-slate-500 hover:text-slate-700 border border-slate-200 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>

    <a href="{{ route('branch.sales.edit', [$branch, $sale]) }}"
       class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-sm transition-colors">
        <i class="fas fa-pen text-xs"></i> Edit
    </a>

    <button onclick="window.print()"
            class="flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm transition-colors">
        <i class="fas fa-print text-xs"></i> Print
    </button>

    @php $anyReturnable = $sale->saleItems->sum(fn($i) => $i->quantity - $i->returnItems->sum('quantity')) > 0; @endphp
    <button @click="returnModal = true" @disabled(!$anyReturnable)
            class="flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-xl text-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
        <i class="fas fa-rotate-left text-xs"></i> Return
    </button>

    {{-- Order Status Dropdown --}}
    <div class="relative ml-auto" x-data="{
        open: false,
        status: '{{ $sale->order_status }}',
        async change(s) {
            const res = await fetch('{{ route('branch.sales.status', [$branch, $sale]) }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({order_status: s})
            });
            if (res.ok) {
                this.status = s;
                showToast('Order status updated!', 'success');
            } else {
                showToast('Failed to update status.', 'error');
            }
            this.open = false;
        }
    }">
        <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-2 border rounded-xl px-4 py-2 text-sm font-semibold transition-colors"
                :class="orderStatusColors[status]">
            <i class="fas fa-circle text-[8px]"></i>
            <span x-text="orderStatusLabels[status] || status"></span>
            <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="open" x-cloak
             class="absolute right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-30 w-48 overflow-hidden py-1">
            @foreach($orderStatuses as $s)
            <button @click="change('{{ $s->key }}')"
                    class="w-full text-left px-4 py-2.5 text-sm transition-colors"
                    :class="status === '{{ $s->key }}' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-slate-600 hover:bg-slate-50'">
                {{ $s->label }}
            </button>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Screen Invoice ───────────────────────────────────────────── --}}
<div class="no-print max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Brand header --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 text-center">
            <h2 class="text-xl font-bold text-white">{{ $branch->system_name ?? $branch->name }}</h2>
            @if($branch->phone)  <p class="text-blue-200 text-sm mt-0.5">{{ $branch->phone }}</p> @endif
            @if($branch->address) <p class="text-blue-200 text-xs mt-0.5">{{ $branch->address }}</p> @endif
        </div>

        <div class="px-6 py-4">
            {{-- Invoice meta --}}
            <div class="flex justify-between items-start mb-4 pb-4 border-b border-slate-100">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-0.5">Invoice No</p>
                    <p class="font-mono font-bold text-slate-800 text-lg">{{ $sale->invoice_no }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-0.5">Date</p>
                    <p class="font-medium text-slate-700 text-sm">{{ $sale->created_at->format('d M Y') }}</p>
                    <p class="text-xs text-slate-400">{{ $sale->created_at->format('h:i A') }}</p>
                </div>
            </div>

            {{-- Customer --}}
            @if($sale->customer)
            <div class="bg-slate-50 rounded-xl p-4 mb-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-600 font-bold text-sm">{{ substr($sale->customer->name, 0, 1) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <p class="font-semibold text-slate-800">{{ $sale->customer->name }}</p>
                        @if($sale->delivery_zone)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $sale->delivery_zone === 'inside' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($sale->delivery_zone === 'sub_dhaka' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            <i class="fas fa-truck text-[9px]"></i>
                            {{ $sale->delivery_zone === 'inside' ? 'Inside Dhaka' : ($sale->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }}
                        </span>
                        @endif
                    </div>
                    @if($sale->customer->phone)
                    <div class="flex items-center gap-2 mt-1">
                        <p class="text-sm font-mono text-slate-600 flex items-center">
                            <i class="fas fa-phone text-xs mr-1.5 text-slate-400"></i>
                            <span class="font-semibold">{{ $sale->customer->phone }}</span>
                        </p>
                        <button type="button"
                                onclick="openEpbxCallModal({ id: {{ $sale->id }}, invoice: '{{ $sale->invoice_no }}', customer: @js($sale->customer->name ?? 'Customer'), phone: '{{ $sale->customer->phone }}', amount: '{{ round($sale->total ?? 0) }}' })"
                                title="MicroSIP / Zoiper Direct Call (096XX Cloud PBX)"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 text-xs font-semibold transition-all hover:scale-105 shadow-2xs cursor-pointer">
                            <i class="fas fa-phone-volume text-xs"></i>
                            <span>MicroSIP / IP Call</span>
                        </button>
                        <a href="https://wa.me/{{ $sale->customer->whatsapp_number ?? preg_replace('/[^\d]/', '', $sale->customer->phone) }}" target="_blank" rel="noopener" title="Chat on WhatsApp"
                           class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors">
                            <i class="fab fa-whatsapp text-xs"></i>
                        </a>
                    </div>
                    @endif
                    @if($sale->district || $sale->customer->district)
                    <p class="text-xs text-blue-700 font-semibold mt-1 flex items-center gap-1">
                        <i class="fas fa-map-pin text-[10px] text-blue-500"></i>
                        <span>{{ implode(' · ', array_filter([$sale->district ?? $sale->customer->district, $sale->thana ?? $sale->customer->thana])) }}</span>
                    </p>
                    @endif
                    @if($sale->customer->address) <p class="text-sm text-slate-600 mt-0.5"><i class="fas fa-location-dot text-xs mr-1 text-slate-400"></i>{{ $sale->customer->address }}</p> @endif
                </div>
            </div>
            @endif

            {{-- Items --}}
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="border-b-2 border-slate-200 text-slate-500 text-xs uppercase tracking-wide">
                        <th class="pb-2 text-left font-semibold">Item</th>
                        <th class="pb-2 text-center font-semibold w-12">Qty</th>
                        <th class="pb-2 text-right font-semibold">Price</th>
                        <th class="pb-2 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->saleItems as $item)
                    @php
                        $imgUrl = $item->product?->first_image_url;
                        // Resolve color & size from variant relation
                        $variant      = $item->variant;
                        $colorLabel   = $variant?->color_label;
                        $sizeLabel    = $variant?->size_label;
                        $colorHex     = $variant?->color?->hex_code;
                        $variantLabel = $colorLabel || $sizeLabel
                            ? implode(' / ', array_filter([$colorLabel, $sizeLabel]))
                            : ($item->variant_name ?: null);
                    @endphp
                    <tr class="border-b border-slate-50">
                        <td class="py-2.5">
                            <div class="flex items-center gap-3">
                                {{-- Product thumbnail --}}
                                @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $item->product?->name }}"
                                     class="w-10 h-10 rounded-lg object-cover border border-slate-200 flex-shrink-0">
                                @else
                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-box text-slate-300 text-sm"></i>
                                </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $item->product?->name ?? 'N/A' }}</p>
                                    @if($variantLabel)
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if($colorHex)
                                        <span class="w-3 h-3 rounded-full border border-slate-300 flex-shrink-0"
                                              style="background-color: {{ $colorHex }}"></span>
                                        @endif
                                        <p class="text-xs text-purple-600 font-medium">{{ $variantLabel }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 text-center text-slate-600">{{ $item->quantity }}</td>
                        <td class="py-2.5 text-right text-slate-500">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2.5 text-right font-semibold text-slate-800">৳{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="border-t-2 border-slate-200 pt-3 space-y-1.5 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span><span>৳{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                @if($sale->discount > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Discount</span><span>−৳{{ number_format($sale->discount, 2) }}</span>
                </div>
                @endif
                @if($sale->tax > 0)
                <div class="flex justify-between text-slate-600">
                    <span>Tax</span><span>৳{{ number_format($sale->tax, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-lg border-t border-slate-200 pt-2 mt-1">
                    <span class="text-slate-800">Total</span>
                    <span class="text-blue-600">৳{{ number_format($sale->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Paid</span><span class="text-emerald-600 font-semibold">৳{{ number_format($sale->paid_amount, 2) }}</span>
                </div>
                @if($sale->due_amount > 0)
                <div class="flex justify-between items-center text-red-600 font-bold bg-red-50 rounded-xl px-3 py-2 mt-1">
                    <span>Due</span>
                    <div class="flex items-center gap-2">
                        <span>৳{{ number_format($sale->due_amount, 2) }}</span>
                        <button @click="dueModal = true"
                                class="bg-red-600 text-white text-xs px-2.5 py-1 rounded-lg font-medium hover:bg-red-700 transition-colors">
                            <i class="fas fa-plus text-[9px] mr-0.5"></i> Pay
                        </button>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer meta --}}
            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-400">
                <span>Payment: <strong class="text-slate-600">{{ ucfirst($sale->payment_method) }}</strong></span>
                <span>Cashier: <strong class="text-slate-600">{{ $sale->createdBy?->name ?? '—' }}</strong></span>
            </div>

            {{-- Payment history --}}
            @if($sale->payments && $sale->payments->count() > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Payment History</p>
                <div class="space-y-1.5">
                    @foreach($sale->payments as $payment)
                    <div class="flex justify-between items-center text-xs text-slate-600 bg-slate-50 px-3 py-2 rounded-lg">
                        <span>{{ $payment->paid_at?->format('d M Y, h:i A') }}</span>
                        <span class="text-slate-400">{{ ucfirst($payment->method) }}</span>
                        <span class="font-bold text-emerald-600">৳{{ number_format($payment->amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Return history --}}
            @if($sale->returns->count() > 0)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Return History</p>
                <div class="space-y-1.5">
                    @foreach($sale->returns as $return)
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

            {{-- Order Notes Card --}}
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2.5">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fas fa-sticky-note text-amber-500"></i>
                        <span>Order Notes & Remarks</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-amber-100 text-amber-800" x-text="notes.length"></span>
                    </p>
                </div>

                {{-- Notes timeline --}}
                <div class="space-y-2 mb-3 max-h-56 overflow-y-auto pr-1">
                    <template x-if="notes.length === 0">
                        <p class="text-xs text-slate-400 italic py-2 text-center">কোনো নোট যোগ করা হয়নি।</p>
                    </template>
                    <template x-for="n in notes" :key="n.id">
                        <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-2.5 text-xs text-slate-700">
                            <div class="flex items-center justify-between mb-1 text-[10.5px]">
                                <span class="font-bold text-slate-800">
                                    <span x-text="n.user_name"></span>
                                    <span class="text-[9px] font-normal text-slate-500 bg-slate-100 px-1 py-0.2 rounded ml-1" x-text="n.user_role"></span>
                                </span>
                                <span class="text-slate-400 text-[10px]" x-text="n.created_at_formatted"></span>
                            </div>
                            <p class="whitespace-pre-wrap leading-relaxed" x-text="n.note"></p>
                        </div>
                    </template>
                </div>

                {{-- Quick notes chips --}}
                <div class="flex items-center gap-1 overflow-x-auto pb-1.5 mb-2 text-[10.5px]">
                    <button type="button" @click="addQuickNote('কাস্টমার প্রোডাক্ট নিবে না')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-100 text-slate-600 transition-colors whitespace-nowrap">❌ প্রোডাক্ট নিবে না</button>
                    <button type="button" @click="addQuickNote('পরে কল দিতে বলেছে')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-100 text-slate-600 transition-colors whitespace-nowrap">📞 পরে কল দিবে</button>
                    <button type="button" @click="addQuickNote('ডেলিভারি কনফার্ম করেছে')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-100 text-slate-600 transition-colors whitespace-nowrap">✅ ডেলিভারি কনফার্ম</button>
                </div>

                {{-- Add Note Form --}}
                <form @submit.prevent="submitNote" class="flex gap-2">
                    <input type="text" x-model="noteText" placeholder="নোট লিখুন (যেমন: কাস্টমার প্রোডাক্ট নিবে না)..." required
                           class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <button type="submit" :disabled="noteSaving || !noteText.trim()"
                            class="bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition-colors flex-shrink-0">
                        <span x-show="!noteSaving">যোগ করুন</span>
                        <span x-show="noteSaving"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Due Payment Modal (screen only) ────────────────────────── --}}
@if($sale->due_amount > 0)
<div x-show="dueModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 no-print"
     @keydown.escape.window="dueModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.outside="dueModal = false">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-red-500"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-800">Collect Payment</p>
                    <p class="text-xs text-slate-400">Due: ৳{{ number_format($sale->due_amount, 2) }}</p>
                </div>
            </div>
            <button @click="dueModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('branch.sales.add-payment', [$branch, $sale]) }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount</label>
                <input type="number" name="amount" value="{{ $sale->due_amount }}"
                       max="{{ $sale->due_amount }}" min="0.01" step="0.01"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-right focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Method</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['cash','bkash','nagad','card','bank','other'] as $m)
                    <label class="cursor-pointer">
                        <input type="radio" name="method" value="{{ $m }}" {{ $m === 'cash' ? 'checked' : '' }} class="sr-only">
                        <div class="border rounded-xl px-2 py-2 text-center text-xs font-semibold border-slate-200 text-slate-600 hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700 transition-all">
                            {{ ucfirst($m) }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="dueModal = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">
                    <i class="fas fa-check text-xs mr-1"></i> Confirm
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ── Return Items Modal (screen only) ──────────────────────── --}}
<div x-show="returnModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 no-print"
     @keydown.escape.window="returnModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.outside="returnModal = false">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fas fa-rotate-left text-red-500"></i>
                </div>
                <p class="font-bold text-slate-800">Return Items</p>
            </div>
            <button @click="returnModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5 space-y-4">
            <div class="space-y-2.5">
                <template x-for="item in returnItems" :key="item.sale_item_id">
                    <div class="flex items-center gap-3 border border-slate-100 rounded-xl px-3 py-2.5" :class="item.max === 0 ? 'opacity-40' : ''">
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-slate-700 truncate" x-text="item.name"></p>
                            <p class="text-[11px] text-slate-400" x-text="'৳' + item.unit_price.toLocaleString() + ' each · ' + item.max + ' returnable'"></p>
                        </div>
                        <input type="number" x-model.number="item.qty" min="0" :max="item.max" :disabled="item.max === 0"
                               class="w-16 border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                </template>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Reason (optional)</label>
                <textarea x-model="returnReason" rows="2" placeholder="e.g. Wrong size, defective..."
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

</div>{{-- end x-data --}}


{{-- ════════════════════════════════════════════════════════════════
     PRINT RECEIPT — 80mm thermal style
     Visible ONLY when printing (hidden on screen via display:none)
     ════════════════════════════════════════════════════════════════ --}}
<div id="receipt">
    {{-- Header --}}
    <div style="text-align:center; padding-bottom:6px; border-bottom:1px dashed #000; margin-bottom:8px;">
        <div style="font-size:14px; font-weight:700; letter-spacing:1px;">{{ strtoupper($branch->system_name ?? $branch->name) }}</div>
        @if($branch->phone)  <div style="margin-top:2px;">Tel: {{ $branch->phone }}</div> @endif
        @if($branch->address) <div style="margin-top:1px; font-size:10px;">{{ $branch->address }}</div> @endif
    </div>

    {{-- Invoice info --}}
    <div style="margin-bottom:8px;">
        <div style="display:flex; justify-content:space-between;">
            <span>Invoice:</span><span style="font-weight:700;">{{ $sale->invoice_no }}</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
            <span>Date:</span><span>{{ $sale->created_at->format('d/m/Y h:iA') }}</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
            <span>Cashier:</span><span>{{ $sale->createdBy?->name ?? '—' }}</span>
        </div>
        @if($sale->customer)
        <div style="display:flex; justify-content:space-between;">
            <span>Customer:</span><span>{{ $sale->customer->name }}</span>
        </div>
        @if($sale->customer->phone)
        <div style="display:flex; justify-content:space-between;">
            <span>Phone:</span><span>{{ $sale->customer->phone }}</span>
        </div>
        @endif
        @endif
    </div>

    {{-- Items --}}
    <div style="border-top:1px dashed #000; border-bottom:1px dashed #000; padding:6px 0; margin-bottom:8px;">
        <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:4px; font-size:10px;">
            <span style="flex:1;">ITEM</span>
            <span style="width:30px; text-align:center;">QTY</span>
            <span style="width:55px; text-align:right;">PRICE</span>
            <span style="width:60px; text-align:right;">TOTAL</span>
        </div>
        @foreach($sale->saleItems as $item)
        @php
            $itemImg = $item->product?->first_image_url;
        @endphp
        <div style="margin-bottom:5px;">
            <div style="display:flex; align-items:center; gap:6px;">
                @if($itemImg)
                <img src="{{ $itemImg }}" alt="{{ $item->product?->name }}"
                     style="width:28px; height:28px; object-fit:cover; border-radius:4px; border:1px solid #ddd; flex-shrink:0;">
                @endif
                <div style="font-weight:600; font-size:11px; line-height:1.2;">
                    {{ $item->product?->name ?? 'N/A' }}@if($item->variant_name) ({{ $item->variant_name }})@endif
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; color:#333; margin-top:2px;">
                <span style="flex:1;"></span>
                <span style="width:30px; text-align:center;">{{ $item->quantity }}</span>
                <span style="width:55px; text-align:right;">{{ number_format($item->unit_price, 2) }}</span>
                <span style="width:60px; text-align:right; font-weight:600;">{{ number_format($item->subtotal, 2) }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Totals --}}
    <div style="margin-bottom:8px;">
        <div style="display:flex; justify-content:space-between;">
            <span>Subtotal:</span><span>{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        @if($sale->discount > 0)
        <div style="display:flex; justify-content:space-between;">
            <span>Discount:</span><span>-{{ number_format($sale->discount, 2) }}</span>
        </div>
        @endif
        @if($sale->tax > 0)
        <div style="display:flex; justify-content:space-between;">
            <span>Tax:</span><span>{{ number_format($sale->tax, 2) }}</span>
        </div>
        @endif
        <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; border-top:1px dashed #000; padding-top:4px; margin-top:4px;">
            <span>TOTAL:</span><span>৳ {{ number_format($sale->total, 2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
            <span>Paid ({{ ucfirst($sale->payment_method) }}):</span><span>{{ number_format($sale->paid_amount, 2) }}</span>
        </div>
        @if($sale->due_amount > 0)
        <div style="display:flex; justify-content:space-between; font-weight:700;">
            <span>DUE:</span><span>{{ number_format($sale->due_amount, 2) }}</span>
        </div>
        @else
        <div style="display:flex; justify-content:space-between;">
            <span>Change:</span><span>{{ number_format($sale->paid_amount - $sale->total, 2) }}</span>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div style="text-align:center; border-top:1px dashed #000; padding-top:8px; font-size:10px; color:#444;">
        <div>*** Thank you for shopping! ***</div>
        <div style="margin-top:2px;">Please come again</div>
        <div style="margin-top:4px; font-size:9px; color:#888;">Powered by Super POS</div>
    </div>
</div>

{{-- ── 096XX Cloud PBX & IP Telephony Call Center Modal ── --}}
@include('partials.epbx-call-modal')

@endsection

@php
$returnItemsData = $sale->saleItems->map(function ($i) {
    $suffix = $i->variant_name ? ' (' . $i->variant_name . ')' : '';
    return [
        'sale_item_id' => $i->id,
        'name'         => $i->product?->name . $suffix,
        'unit_price'   => (float) $i->unit_price,
        'max'          => $i->quantity - $i->returnItems->sum('quantity'),
        'qty'          => 0,
    ];
});
$orderStatusLabelsData = $orderStatuses->pluck('label', 'key');
$orderStatusColorsData = $orderStatuses->mapWithKeys(function ($s) {
    return [$s->key => "bg-{$s->color}-100 text-{$s->color}-700"];
});
$notesData = ($sale->notes ?? collect())->map(function ($n) {
    return [
        'id'                   => $n->id,
        'note'                 => $n->note,
        'user_name'            => $n->user ? $n->user->name : 'User #' . $n->user_id,
        'user_role'            => ucfirst($n->user ? ($n->user->roles->first()?->name ?? 'Staff') : 'Staff'),
        'created_at_formatted' => $n->created_at ? $n->created_at->format('d M Y, h:i A') : '',
        'time_ago'             => $n->created_at ? $n->created_at->diffForHumans() : '',
    ];
});
@endphp
@push('scripts')
<script>
function showSale() {
    return {
        dueModal: false,
        orderStatusLabels: @json($orderStatusLabelsData),
        orderStatusColors: @json($orderStatusColorsData),
        returnModal: false,
        returnSaving: false,
        returnReason: '',
        returnMethod: 'cash',
        returnItems: @json($returnItemsData),

        get returnTotal() {
            return this.returnItems.reduce((s, i) => s + (i.qty || 0) * i.unit_price, 0);
        },

        async submitReturn() {
            const items = this.returnItems.filter(i => i.qty > 0).map(i => ({ sale_item_id: i.sale_item_id, quantity: i.qty }));
            if (items.length === 0) return;
            this.returnSaving = true;
            try {
                const res = await fetch('{{ route('branch.sales.return', [$branch, $sale]) }}', {
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

        notes: @json($notesData),
        noteText: '',
        noteSaving: false,

        addQuickNote(msg) {
            this.noteText = this.noteText.trim() === '' ? msg : (this.noteText + ', ' + msg);
        },

        async submitNote() {
            if (!this.noteText.trim()) return;
            this.noteSaving = true;
            try {
                const res = await fetch('{{ route('branch.sales.notes.store', [$branch, $sale]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ note: this.noteText }),
                });

                const data = await res.json();
                if (data.success) {
                    this.notes.unshift(data.note);
                    this.noteText = '';
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Error saving note', 'error');
                }
            } catch (e) {
                showToast('Connection error', 'error');
            }
            this.noteSaving = false;
        },

        init() {
            @if(request()->has('auto_print'))
            window.addEventListener('load', () => setTimeout(() => window.print(), 400));
            @endif
        }
    };
}
</script>
@endpush
