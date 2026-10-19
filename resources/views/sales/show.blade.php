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

@php
    $cPhone = trim($sale->customer?->phone ?? '');
    $cName  = $sale->customer?->name ?? 'Customer';
@endphp

{{-- ── Screen Invoice & Courier Intelligence 2-Column Grid ──────────────────────── --}}
<div class="no-print grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- LEFT COLUMN: Invoice Receipt Card --}}
    <div class="lg:col-span-7 xl:col-span-7">
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
                        <p class="text-xs text-slate-400 italic py-2 text-center">No notes added yet.</p>
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
                    <button type="button" @click="addQuickNote('Customer cancelled order')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-100 text-slate-600 transition-colors whitespace-nowrap">❌ Cancelled</button>
                    <button type="button" @click="addQuickNote('Customer requested call back later')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-100 text-slate-600 transition-colors whitespace-nowrap">📞 Call Back</button>
                    <button type="button" @click="addQuickNote('Customer confirmed delivery')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-100 text-slate-600 transition-colors whitespace-nowrap">✅ Confirmed</button>
                </div>

                {{-- Add Note Form --}}
                <form @submit.prevent="submitNote" class="flex gap-2">
                    <input type="text" x-model="noteText" placeholder="Enter note (e.g. Call back later)..." required
                           class="flex-1 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <button type="submit" :disabled="noteSaving || !noteText.trim()"
                            class="bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition-colors flex-shrink-0">
                        <span x-show="!noteSaving">Add Note</span>
                        <span x-show="noteSaving"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>{{-- /LEFT COLUMN: lg:col-span-7 --}}

    {{-- RIGHT COLUMN: Courier Order Percentage & BD Courier Status (Image 2) --}}
    <div class="lg:col-span-5 xl:col-span-5 space-y-4 lg:sticky lg:top-4">

        {{-- BD Courier Status Card --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5 space-y-4">

            {{-- Card Header --}}
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-800 text-[15px]">BD Courier Status</h3>
                            <span class="text-[10px] font-semibold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">api.bdcourier.com</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            <span x-text="bdName" class="font-medium text-slate-700"></span>
                            <span x-show="bdName && bdPhone"> · </span>
                            <span class="font-mono font-semibold text-slate-600" x-text="bdPhone || 'No phone number'"></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="checkBdCourier()" :disabled="bdLoading"
                            title="Re-check / Refresh Courier Records"
                            class="text-slate-400 hover:text-indigo-600 w-8 h-8 flex items-center justify-center rounded-xl hover:bg-indigo-50 transition-colors cursor-pointer">
                        <i class="fas fa-arrows-rotate text-xs" :class="bdLoading ? 'fa-spin text-indigo-600' : ''"></i>
                    </button>
                </div>
            </div>

            {{-- Phone number editor / search if needed --}}
            <div class="bg-slate-50 rounded-2xl p-2.5 border border-slate-100 flex items-center gap-2">
                <div class="w-7 h-7 rounded-xl bg-white text-slate-400 flex items-center justify-center text-xs flex-shrink-0 shadow-2xs">
                    <i class="fas fa-phone"></i>
                </div>
                <input type="text" x-model="bdPhone" @keydown.enter="checkBdCourier()" placeholder="Enter customer phone (e.g. 017xxxxxxxx)..."
                       class="flex-1 bg-transparent border-0 text-xs text-slate-800 font-mono font-semibold focus:outline-none focus:ring-0 placeholder:text-slate-400">
                <button type="button" @click="checkBdCourier()" :disabled="bdLoading || !bdPhone.trim()"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition-colors flex-shrink-0 cursor-pointer">
                    <span x-show="!bdLoading">Check</span>
                    <span x-show="bdLoading"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </div>

            {{-- Loading State --}}
            <div x-show="bdLoading" class="py-10 text-center text-slate-400">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-spinner fa-spin text-xl"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">Checking Courier Records...</p>
                <p class="text-xs text-slate-400 mt-1 font-mono" x-text="bdPhone"></p>
            </div>

            {{-- Error State --}}
            <template x-if="!bdLoading && bdError">
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-triangle-exclamation text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-red-800 text-sm">Courier Check Notice</h4>
                            <p class="text-red-700 text-xs mt-1 leading-relaxed" x-text="bdError"></p>
                            <button type="button" @click="checkBdCourier()"
                                    class="mt-2.5 text-xs bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-xl inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                                <i class="fas fa-rotate-right text-[10px]"></i> Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Main Courier Info (Matches Image 2) --}}
            <template x-if="!bdLoading && (!bdError || bdResult)">
                <div class="space-y-4">

                    {{-- Top Status / Percentage SVG Gauge --}}
                    <div class="bg-gradient-to-br from-slate-50 to-indigo-50/40 border border-slate-100 rounded-2xl p-4 flex items-center gap-4">
                        {{-- Circular SVG Gauge --}}
                        <div class="relative w-24 h-24 flex-shrink-0">
                            <svg width="96" height="96" viewBox="0 0 100 100" class="-rotate-90">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none" :stroke="bdRiskInfo.ring" stroke-width="8" stroke-linecap="round"
                                        stroke-dasharray="251.32"
                                        :stroke-dashoffset="251.32 * (1 - Math.min(100, Math.max(0, bdRiskInfo.deliverPct)) / 100)"
                                        style="transition: stroke-dashoffset .6s ease, stroke .3s ease"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-black text-slate-800 leading-none" x-text="bdRiskInfo.deliverPct + '%'"></span>
                                <span class="text-[9.5px] font-semibold text-slate-400 mt-1">Success</span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                  :class="bdRiskInfo.badgeBg"
                                  x-text="bdRiskInfo.label"></span>
                            <p class="text-xs text-slate-600 mt-1.5 leading-relaxed font-medium" x-text="bdRiskInfo.message"></p>
                            <p class="text-[11.5px] font-mono text-slate-500 mt-1.5 flex items-center gap-1.5">
                                <i class="fas fa-mobile-screen text-slate-400 text-xs"></i>
                                <span x-text="bdPhone"></span>
                            </p>
                        </div>
                    </div>

                    {{-- 3 Key Metric Cards --}}
                    <div class="grid grid-cols-3 gap-2.5">
                        {{-- Total Orders --}}
                        <div class="bg-blue-50/80 border border-blue-100 rounded-2xl p-3 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-1.5 shadow-2xs">
                                <i class="fas fa-boxes-stacked text-xs"></i>
                            </div>
                            <p class="text-xl font-black text-blue-700 leading-none font-mono" x-text="bdRiskInfo.total"></p>
                            <p class="text-[10.5px] font-bold text-blue-600/90 mt-1.5">Total Parcels</p>
                            <p class="text-[9px] text-slate-400 font-medium">All Couriers</p>
                        </div>

                        {{-- Delivered --}}
                        <div class="bg-emerald-50/80 border border-emerald-100 rounded-2xl p-3 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-1.5 shadow-2xs">
                                <i class="fas fa-circle-check text-xs"></i>
                            </div>
                            <p class="text-xl font-black text-emerald-700 leading-none font-mono" x-text="bdRiskInfo.delivered"></p>
                            <p class="text-[10.5px] font-bold text-emerald-600/90 mt-1.5">Delivered</p>
                            <p class="text-[9px] text-slate-400 font-medium">Successful</p>
                        </div>

                        {{-- Cancelled --}}
                        <div class="bg-rose-50/80 border border-rose-100 rounded-2xl p-3 text-center">
                            <div class="w-7 h-7 mx-auto rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center mb-1.5 shadow-2xs">
                                <i class="fas fa-circle-xmark text-xs"></i>
                            </div>
                            <p class="text-xl font-black text-rose-700 leading-none font-mono" x-text="bdRiskInfo.cancelled"></p>
                            <p class="text-[10.5px] font-bold text-rose-600/90 mt-1.5">Cancelled</p>
                            <p class="text-[9px] text-slate-400 font-medium">Returned</p>
                        </div>
                    </div>

                    {{-- Courier-wise Breakdown (7 Couriers) --}}
                    <div class="border border-slate-100 rounded-2xl p-4 bg-white shadow-2xs">
                        <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">Courier Breakdown</p>
                            <span class="text-[11px] text-slate-400 font-medium" x-text="bdCourierRows.length + ' couriers'"></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[10.5px] text-slate-400">
                                        <th class="py-2 text-left font-semibold">Courier</th>
                                        <th class="py-2 text-center font-semibold">Total</th>
                                        <th class="py-2 text-center font-semibold text-emerald-600">Delivered</th>
                                        <th class="py-2 text-center font-semibold text-rose-500">Cancel</th>
                                        <th class="py-2 text-right font-semibold">Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <template x-for="c in bdCourierRows" :key="c.name">
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-2.5 font-semibold text-slate-700">
                                                <div class="flex items-center gap-2">
                                                    <template x-if="c.logo">
                                                        <img :src="c.logo" :alt="c.name" class="w-4 h-4 object-contain flex-shrink-0">
                                                    </template>
                                                    <template x-if="!c.logo">
                                                        <span class="w-4 h-4 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[9px] font-black flex-shrink-0" x-text="c.name.substring(0, 1)"></span>
                                                    </template>
                                                    <span x-text="c.name" class="truncate"></span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-center font-medium text-slate-600 font-mono" x-text="c.total_parcel"></td>
                                            <td class="py-2.5 text-center font-bold text-emerald-600 font-mono" x-text="c.success_parcel"></td>
                                            <td class="py-2.5 text-center font-bold text-rose-500 font-mono" x-text="c.cancelled_parcel"></td>
                                            <td class="py-2.5 text-right font-bold text-slate-800 font-mono" x-text="(c.success_ratio || 0) + '%'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Reports list if any --}}
                    <div x-show="bdResult && bdResult.reports && bdResult.reports.length > 0" class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5">
                        <div class="flex items-center gap-2 mb-2 text-amber-800 font-bold text-xs">
                            <i class="fas fa-triangle-exclamation text-amber-500"></i>
                            <span>Reports &amp; Flags</span>
                        </div>
                        <ul class="space-y-1 text-xs text-amber-900 list-disc list-inside">
                            <template x-for="(rep, i) in (bdResult?.reports || [])" :key="i">
                                <li x-text="typeof rep === 'string' ? rep : (rep.note || rep.reason || JSON.stringify(rep))"></li>
                            </template>
                        </ul>
                    </div>

                </div>
            </template>

        </div>

        {{-- Dispatched Courier Shipment Card (If already booked/sent) --}}
        @if($sale->courier_name || $sale->courier_tracking_code || $sale->courier_consignment_id)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 p-5 space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500 animate-pulse"></span>
                    <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wide">This Order's Shipment</h4>
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 capitalize">
                    {{ $sale->courier_display_name }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs">
                @if($sale->courier_consignment_id)
                <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                    <span class="text-[10px] text-slate-400 block font-medium uppercase tracking-wider">Consignment ID</span>
                    <span class="font-mono font-bold text-slate-800 text-xs">{{ $sale->courier_consignment_id }}</span>
                </div>
                @endif
                @if($sale->courier_tracking_code)
                <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                    <span class="text-[10px] text-slate-400 block font-medium uppercase tracking-wider">Tracking Code</span>
                    <span class="font-mono font-bold text-slate-800 text-xs">{{ $sale->courier_tracking_code }}</span>
                </div>
                @endif
                @if($sale->courier_status)
                <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                    <span class="text-[10px] text-slate-400 block font-medium uppercase tracking-wider">Courier Status</span>
                    <span class="font-bold text-emerald-600 text-xs capitalize">{{ str_replace('_', ' ', $sale->courier_status) }}</span>
                </div>
                @endif
                @if($sale->courier_sent_at)
                <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                    <span class="text-[10px] text-slate-400 block font-medium uppercase tracking-wider">Dispatched Date</span>
                    <span class="text-slate-700 font-medium text-xs">{{ $sale->courier_sent_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>{{-- /RIGHT COLUMN --}}

</div>{{-- /2-Column Grid --}}

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

        bdPhone: '{{ $cPhone }}',
        bdName: @js($cName),
        bdLoading: false,
        bdResult: null,
        bdError: null,

        get bdRiskInfo() {
            if (!this.bdResult || !this.bdResult.summary) {
                return {
                    deliverPct: 0,
                    total: 0,
                    delivered: 0,
                    cancelled: 0,
                    label: 'New Customer',
                    message: 'No previous courier delivery record found.',
                    ring: '#94a3b8',
                    text: 'text-slate-500',
                    badgeBg: 'bg-slate-100 text-slate-600 border border-slate-200'
                };
            }
            const s = this.bdResult.summary;
            const total      = Number(s.total_parcel || 0);
            const delivered  = Number(s.success_parcel || 0);
            const cancelled  = Number(s.cancelled_parcel || 0);
            const deliverPct = Number(s.success_ratio !== undefined ? s.success_ratio : (total > 0 ? Math.round((delivered / total) * 1000) / 10 : 0));

            let key = 'none';
            if (total > 0) {
                key = deliverPct >= 80 ? 'excellent' : (deliverPct >= 60 ? 'good' : (deliverPct >= 40 ? 'average' : 'risky'));
            }

            const config = {
                excellent: { label: 'Safe Customer',     message: 'Excellent parcel delivery success rate.',  ring: '#22c55e', text: 'text-emerald-600', badgeBg: 'bg-emerald-100 text-emerald-700 border border-emerald-200' },
                good:      { label: 'Good Customer',     message: 'Reliable parcel delivery history.',         ring: '#0ea5e9', text: 'text-sky-600',     badgeBg: 'bg-sky-100 text-sky-700 border border-sky-200' },
                average:   { label: 'Moderate Risk',     message: 'Verify order before parcel dispatch.',       ring: '#f59e0b', text: 'text-amber-600',   badgeBg: 'bg-amber-100 text-amber-700 border border-amber-200' },
                risky:     { label: 'High Risk',         message: 'Higher likelihood of return or refusal.',    ring: '#ef4444', text: 'text-red-600',     badgeBg: 'bg-red-100 text-red-700 border border-red-200' },
                none:      { label: 'New Customer',      message: 'No previous courier delivery record found.', ring: '#94a3b8', text: 'text-slate-500',   badgeBg: 'bg-slate-100 text-slate-600 border border-slate-200' },
            };

            return {
                deliverPct,
                total,
                delivered,
                cancelled,
                ...(config[key] || config.none)
            };
        },

        get bdCourierRows() {
            if (this.bdResult && this.bdResult.couriers && this.bdResult.couriers.length > 0) {
                return this.bdResult.couriers;
            }
            return [
                { name: 'Pathao',       total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
                { name: 'SteadFast',    total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
                { name: 'ParcelDex',    total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
                { name: 'CourrierFast', total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
                { name: 'Redx',         total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
                { name: 'PaperFly',     total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
                { name: 'CarryBee',     total_parcel: 0, success_parcel: 0, cancelled_parcel: 0, success_ratio: 0 },
            ];
        },

        async checkBdCourier() {
            const phone = (this.bdPhone || '').trim();
            if (!phone) {
                this.bdError = 'Customer phone number is required to check courier status.';
                return;
            }
            this.bdLoading = true;
            this.bdError   = null;

            try {
                const res = await fetch('{{ route('branch.sales.bd-courier-check', $branch) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]')?.content || document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ phone: phone }),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.bdResult = data;
                } else {
                    this.bdError = data.error || 'Failed to fetch courier details.';
                }
            } catch (e) {
                this.bdError = 'Connection error to BD Courier API.';
            }

            this.bdLoading = false;
        },

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

            if (this.bdPhone && this.bdPhone.trim() !== '') {
                this.checkBdCourier();
            }
        }
    };
}
</script>
@endpush
