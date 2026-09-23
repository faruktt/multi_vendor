@extends('layouts.app')
@section('title', 'POS Terminal')
@section('heading', 'POS Terminal')

@push('styles')
<style>
/* Screen: hide the receipt div */
#receipt { display: none; }

/* Print: show ONLY the receipt, hide everything else on the POS page */
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
<div x-data="posApp()">
    <div class="flex gap-4 h-[calc(100vh-140px)]" @click="openVariantId = null">

    {{-- ============ LEFT: Products ============ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Search & Filter Bar --}}
        <div class="flex gap-2 mb-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model.debounce.300ms="search" @input="loadProducts()"
                       placeholder="Search product or scan barcode..."
                       class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm placeholder-slate-400">
            </div>
            <select x-model="categoryFilter" @change="loadProducts()"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm min-w-[140px] text-slate-600">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Products Grid --}}
        <div class="flex-1 overflow-y-auto pr-0.5">

            {{-- Loading Skeleton --}}
            <div x-show="loading" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
                <template x-for="n in 18" :key="n">
                    <div class="bg-white rounded-xl border border-slate-100 p-2.5 animate-pulse">
                        <div class="aspect-square rounded-lg bg-slate-200 mb-2"></div>
                        <div class="h-2 bg-slate-200 rounded-full mb-1.5 w-4/5"></div>
                        <div class="h-2 bg-slate-200 rounded-full w-2/5 mb-2"></div>
                        <div class="h-5 bg-slate-200 rounded-lg"></div>
                    </div>
                </template>
            </div>

            {{-- Product Cards --}}
            <div x-show="!loading" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
                <template x-for="product in products" :key="product.id">
                    <div class="bg-white border rounded-xl p-2.5 transition-all duration-150 relative flex flex-col"
                         :class="product.stock_qty === 0
                             ? 'border-slate-100 opacity-55'
                             : 'border-slate-100 hover:border-blue-200 hover:shadow-md'">

                        {{-- Image --}}
                        <div class="aspect-square rounded-lg mb-2 overflow-hidden flex items-center justify-center relative flex-shrink-0"
                             :class="product.image ? 'bg-slate-100' : 'bg-gradient-to-br from-slate-50 to-blue-50'">
                            <img x-show="product.image" :src="product.image" :alt="product.name"
                                 class="w-full h-full object-cover">
                            <div x-show="!product.image" class="flex items-center justify-center w-full h-full">
                                <i class="fas fa-box text-blue-200 text-2xl"></i>
                            </div>
                            {{-- Stock badge --}}
                            <span x-show="product.stock_qty === 0"
                                  class="absolute top-1 right-1 text-[9px] font-bold bg-red-500 text-white px-1 py-0.5 rounded-md leading-none">Out</span>
                            <span x-show="product.stock_qty > 0 && product.stock_qty <= 5"
                                  class="absolute top-1 right-1 text-[9px] font-bold bg-amber-400 text-white px-1 py-0.5 rounded-md leading-none"
                                  x-text="product.stock_qty"></span>
                            {{-- In-cart dot --}}
                            <span x-show="cart.some(c => c.id === product.id)"
                                  class="absolute top-1 left-1 w-2 h-2 bg-blue-500 rounded-full border-2 border-white"></span>
                        </div>

                        {{-- Name & Price --}}
                        <div class="flex-1 mb-2">
                            <p class="text-[11.5px] font-semibold text-slate-700 leading-tight line-clamp-2 mb-1" x-text="product.name"></p>
                            <p class="text-[13px] font-bold text-blue-600 leading-none">৳<span x-text="Number(product.price).toLocaleString()"></span></p>
                        </div>

                        {{-- Button: no variants → Add, variants → Select dropdown --}}
                        <div class="relative" @click.stop>
                            {{-- Simple Add button --}}
                            <button x-show="!product.variants || product.variants.length === 0"
                                    @click="addSimple(product)"
                                    :disabled="product.stock_qty === 0"
                                    class="w-full flex items-center justify-center gap-1 py-1.5 rounded-lg text-[11px] font-semibold transition-all"
                                    :class="product.stock_qty === 0
                                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                        : 'bg-blue-600 hover:bg-blue-700 active:scale-95 text-white shadow-sm shadow-blue-200'">
                                <i class="fas fa-plus text-[9px]"></i>
                                <span>Add</span>
                            </button>

                            {{-- Variant Select button --}}
                            <button x-show="product.variants && product.variants.length > 0"
                                    @click="openVariantId = openVariantId === product.id ? null : product.id"
                                    :disabled="product.stock_qty === 0"
                                    class="w-full flex items-center justify-center gap-1 py-1.5 rounded-lg text-[11px] font-semibold transition-all"
                                    :class="openVariantId === product.id
                                        ? 'bg-indigo-600 text-white shadow-sm'
                                        : product.stock_qty === 0
                                            ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                            : 'bg-indigo-500 hover:bg-indigo-600 active:scale-95 text-white shadow-sm shadow-indigo-200'">
                                <i class="fas fa-layer-group text-[9px]"></i>
                                <span>Select</span>
                                <i class="fas fa-chevron-down text-[8px] transition-transform duration-150"
                                   :class="openVariantId === product.id ? 'rotate-180' : ''"></i>
                            </button>

                            {{-- Variant Dropdown --}}
                            <div x-show="openVariantId === product.id"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute bottom-full left-0 right-0 mb-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden min-w-[160px]"
                                 style="width: max(100%, 160px)">
                                <div class="px-3 py-2 border-b border-slate-100 bg-slate-50">
                                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Select Variant</p>
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    <template x-for="v in product.variants" :key="v.id">
                                        <button @click="addVariant(product, v)"
                                                :disabled="v.stock_qty === 0"
                                                class="w-full flex items-center justify-between px-3 py-2 text-left hover:bg-blue-50 transition-colors border-b border-slate-50 last:border-0"
                                                :class="v.stock_qty === 0 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'">
                                            <div>
                                                <p class="text-[12.5px] font-semibold text-slate-700" x-text="v.variant_name"></p>
                                                <p class="text-[11px] text-slate-400" x-text="v.stock_qty + ' in stock'"></p>
                                            </div>
                                            <p class="text-[13px] font-bold text-blue-600 flex-shrink-0 ml-3">৳<span x-text="Number(v.price).toLocaleString()"></span></p>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="!loading && products.length === 0"
                     class="col-span-full flex flex-col items-center justify-center py-16 text-slate-400">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
                        <i class="fas fa-box-open text-2xl text-slate-300"></i>
                    </div>
                    <p class="font-medium text-slate-500 text-sm">No products found</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ RIGHT: Cart Panel ============ --}}
    <div class="w-[330px] flex-shrink-0 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col" @click.stop>

        {{-- Cart Header --}}
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xs"></i>
                </div>
                <span class="font-bold text-slate-700 text-sm">Cart</span>
                <span x-show="cart.length > 0"
                      class="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                      x-text="cart.length + ' items'"></span>
            </div>
            <button @click="clearCart()" x-show="cart.length > 0"
                    class="text-xs text-red-400 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg transition-colors flex items-center gap-1">
                <i class="fas fa-trash text-[10px]"></i> Clear
            </button>
        </div>

        {{-- Customer Searchable Dropdown --}}
        <div class="px-3 py-2.5 border-b border-slate-100 relative" @click.outside="customerOpen = false">
            <button type="button" @click="customerOpen = !customerOpen"
                    class="w-full flex items-center gap-2 border rounded-xl px-3 py-2 text-sm transition-all"
                    :class="customerOpen ? 'border-blue-400 ring-2 ring-blue-100 bg-blue-50/30' : 'border-slate-200 hover:border-slate-300 bg-white'">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                     :class="customerId ? 'bg-blue-100' : 'bg-slate-100'">
                    <i class="fas fa-user text-[10px]" :class="customerId ? 'text-blue-600' : 'text-slate-400'"></i>
                </div>
                <span class="flex-1 text-left truncate text-[13px]"
                      :class="customerId ? 'text-slate-800 font-semibold' : 'text-slate-400'"
                      x-text="customerId ? customerName : 'Walk-in Customer'"></span>
                <i class="fas fa-chevron-down text-slate-400 text-[10px] transition-transform"
                   :class="customerOpen ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="customerOpen" x-cloak
                 class="absolute left-3 right-3 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                <div class="p-2 border-b border-slate-100">
                    <div class="relative">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        <input type="text" x-model="customerSearch" placeholder="Search name or phone..."
                               @click.stop
                               class="w-full pl-7 pr-3 py-1.5 text-[12px] border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>
                <div class="max-h-48 overflow-y-auto">
                    <div @click="customerId = ''; customerName = ''; customerOpen = false; customerSearch = ''"
                         class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50 transition-colors"
                         :class="!customerId ? 'bg-blue-50' : ''">
                        <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center">
                            <i class="fas fa-walking text-slate-500 text-[9px]"></i>
                        </div>
                        <span class="text-[12.5px] font-medium text-slate-600 flex-1">Walk-in Customer</span>
                        <i x-show="!customerId" class="fas fa-check text-blue-500 text-xs"></i>
                    </div>
                    <template x-for="c in filteredCustomers" :key="c.id">
                        <div @click="customerId = c.id; customerName = c.name; customerOpen = false; customerSearch = ''"
                             class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors border-t border-slate-50"
                             :class="customerId == c.id ? 'bg-blue-50' : ''">
                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-600 text-[10px] font-bold" x-text="c.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] font-semibold text-slate-700 truncate" x-text="c.name"></p>
                                <p class="text-[10.5px] text-slate-400" x-show="c.phone" x-text="c.phone"></p>
                            </div>
                            <i x-show="customerId == c.id" class="fas fa-check text-blue-500 text-xs flex-shrink-0"></i>
                        </div>
                    </template>
                    <div x-show="filteredCustomers.length === 0 && customerSearch !== ''"
                         class="px-3 py-3 text-center text-slate-400 text-xs border-t border-slate-50">
                        No customer found
                    </div>
                </div>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto px-3 py-2">
            <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-slate-400 py-6">
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fas fa-shopping-cart text-xl text-slate-300"></i>
                </div>
                <p class="font-semibold text-sm">Cart is empty</p>
                <p class="text-xs text-slate-300 mt-1">Click a product to add</p>
            </div>

            <template x-for="(item, i) in cart" :key="item._key">
                <div class="flex items-center gap-2 py-2 border-b border-slate-50 last:border-0 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-600 font-bold text-sm" x-text="item.name.charAt(0).toUpperCase()"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-slate-700 truncate" x-text="item.name"></p>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="text-[11px] text-slate-400">৳<span x-text="Number(item.price).toLocaleString()"></span></span>
                            <template x-if="item.variant_name">
                                <span class="text-[9.5px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-full font-medium" x-text="item.variant_name"></span>
                            </template>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <div class="flex items-center gap-0.5 bg-slate-100 rounded-lg p-0.5">
                            <button @click="decrementItem(i)"
                                    class="w-5 h-5 rounded-md bg-white shadow-sm hover:bg-red-50 hover:text-red-500 text-slate-500 text-xs font-bold flex items-center justify-center transition-colors">−</button>
                            <input type="number" min="1" :max="item.stock_qty" x-model.number="item.quantity"
                                   @change="setItemQty(i, item.quantity)" @click.stop
                                   class="w-11 text-center text-xs font-bold text-slate-700 bg-transparent border-0 focus:outline-none focus:ring-1 focus:ring-blue-400 rounded [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <button @click="incrementItem(i)"
                                    class="w-5 h-5 rounded-md bg-white shadow-sm hover:bg-blue-50 hover:text-blue-600 text-slate-500 text-xs font-bold flex items-center justify-center transition-colors">+</button>
                        </div>
                        <span class="text-[11.5px] font-bold text-blue-600">৳<span x-text="Number(item.price * item.quantity).toLocaleString()"></span></span>
                    </div>
                    <button @click="removeItem(i)"
                            class="w-5 h-5 rounded-full text-slate-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
            </template>
        </div>

        {{-- Summary & Checkout --}}
        <div class="px-4 py-3 border-t border-slate-100 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Subtotal</span>
                <span class="font-semibold text-slate-700">৳<span x-text="Number(subtotal).toLocaleString()"></span></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Discount (৳)</span>
                <input type="number" x-model.number="discount" min="0" :max="subtotal"
                       class="w-24 text-right border border-slate-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Tax (৳)</span>
                <input type="number" x-model.number="tax" min="0"
                       class="w-24 text-right border border-slate-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex justify-between items-center pt-1.5 border-t border-slate-100">
                <span class="font-bold text-slate-800">Total</span>
                <span class="font-bold text-xl text-blue-600">৳<span x-text="Number(total).toLocaleString()"></span></span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 mb-1">Paid Amount <span class="text-red-500">*</span></label>
                    <input type="number" x-model.number="paidAmount" min="0" placeholder="Required, e.g. 0"
                           class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-500 mb-1">Payment</label>
                    <select x-model="paymentMethod" @change="if (paymentMethod === 'due') paidAmount = 0"
                            class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div x-show="paidAmount > 0 && paidAmount > total"
                 class="flex justify-between text-sm bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-1.5">
                <span class="text-emerald-700 font-medium">Change</span>
                <span class="text-emerald-700 font-bold">৳<span x-text="Number(Math.max(0, paidAmount - total)).toLocaleString()"></span></span>
            </div>
            <button @click="checkout()" :disabled="cart.length === 0 || saving || paidAmount === ''"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-white py-3 rounded-xl font-bold text-sm transition-all shadow-md shadow-blue-200/60">
                <span x-show="!saving" class="flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    Checkout
                    <span x-show="cart.length > 0" class="bg-white/20 rounded-lg px-2 py-0.5 text-xs font-bold">
                        ৳<span x-text="Number(total).toLocaleString()"></span>
                    </span>
                </span>
                <span x-show="saving" class="flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Processing...
                </span>
            </button>
        </div>
    </div>
</div>

{{-- ============ Success Modal ============ --}}
<div x-show="invoice !== null" x-cloak
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
        <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check-circle text-emerald-500 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-1">Sale Complete!</h3>
        <p class="text-slate-400 text-sm mb-4">Payment recorded successfully</p>
        <div class="bg-slate-50 rounded-xl p-3 text-left space-y-2 mb-4">
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Invoice</span>
                <span class="font-bold text-slate-800 font-mono text-xs" x-text="invoice?.invoice_no"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Total</span>
                <span class="font-bold text-slate-800">৳<span x-text="Number(invoice?.total || 0).toLocaleString()"></span></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Paid</span>
                <span class="font-bold text-emerald-600">৳<span x-text="Number(invoice?.paid || 0).toLocaleString()"></span></span>
            </div>
            <div x-show="invoice?.change > 0" class="flex justify-between text-sm border-t border-slate-200 pt-2">
                <span class="text-slate-500">Change</span>
                <span class="font-bold text-blue-600">৳<span x-text="Number(invoice?.change || 0).toLocaleString()"></span></span>
            </div>
            <div x-show="invoice?.due > 0" class="flex justify-between text-sm border-t border-slate-200 pt-2">
                <span class="text-slate-500">Due</span>
                <span class="font-bold text-red-500">৳<span x-text="Number(invoice?.due || 0).toLocaleString()"></span></span>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" @click="window.print()"
                    class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-600 py-2.5 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1.5">
                <i class="fas fa-print text-xs"></i> Print Slip
            </button>
            <a :href="'{{ route('branch.sales.show', [$branch, '_ID_']) }}'.replace('_ID_', invoice?.sale_id)"
               target="_blank"
               class="flex-1 border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 py-2.5 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-1.5">
                <i class="fas fa-file-invoice text-xs"></i> View Invoice
            </a>
            <button @click="newSale()"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5">
                <i class="fas fa-plus text-xs"></i> New Sale
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     PRINT RECEIPT — 80mm thermal style
     Visible ONLY when printing (hidden on screen via display:none)
     ════════════════════════════════════════════════════════════════ --}}
<div id="receipt" x-show="lastSale !== null">
    <template x-if="lastSale !== null">
        <div>
            {{-- Header --}}
            <div style="text-align:center; padding-bottom:6px; border-bottom:1px dashed #000; margin-bottom:8px;">
                <div style="font-size:14px; font-weight:700; letter-spacing:1px;">{{ strtoupper($branch->system_name ?? $branch->name) }}</div>
                @if($branch->phone)  <div style="margin-top:2px;">Tel: {{ $branch->phone }}</div> @endif
                @if($branch->address) <div style="margin-top:1px; font-size:10px;">{{ $branch->address }}</div> @endif
            </div>

            {{-- Invoice info --}}
            <div style="margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between;">
                    <span>Invoice:</span><span style="font-weight:700;" x-text="invoice?.invoice_no"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Date:</span><span x-text="lastSale?.printedAt"></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Cashier:</span><span>{{ auth()->user()?->name ?? 'Cashier' }}</span>
                </div>
                <template x-if="lastSale?.customerName">
                    <div style="display:flex; justify-content:space-between;">
                        <span>Customer:</span><span x-text="lastSale?.customerName"></span>
                    </div>
                </template>
            </div>

            {{-- Items --}}
            <div style="border-top:1px dashed #000; border-bottom:1px dashed #000; padding:6px 0; margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; font-weight:700; margin-bottom:4px; font-size:10px;">
                    <span style="flex:1;">ITEM</span>
                    <span style="width:30px; text-align:center;">QTY</span>
                    <span style="width:55px; text-align:right;">PRICE</span>
                    <span style="width:60px; text-align:right;">TOTAL</span>
                </div>
                <template x-for="(item, idx) in (lastSale?.items || [])" :key="idx">
                    <div style="margin-bottom:4px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <template x-if="item.image">
                                <img :src="item.image" style="width:26px; height:26px; object-fit:cover; border-radius:3px; border:1px solid #ddd; flex-shrink:0;">
                            </template>
                            <div style="font-weight:600; font-size:11px; line-height:1.2;" x-text="item.name"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; color:#333; margin-top:2px;">
                            <span style="flex:1;"></span>
                            <span style="width:30px; text-align:center;" x-text="item.qty"></span>
                            <span style="width:55px; text-align:right;" x-text="Number(item.price).toFixed(2)"></span>
                            <span style="width:60px; text-align:right; font-weight:600;" x-text="Number(item.subtotal).toFixed(2)"></span>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Totals --}}
            <div style="margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between;">
                    <span>Subtotal:</span><span>৳ <span x-text="Number(lastSale?.subtotal || 0).toFixed(2)"></span></span>
                </div>
                <template x-if="lastSale?.discount > 0">
                    <div style="display:flex; justify-content:space-between;">
                        <span>Discount:</span><span>-৳ <span x-text="Number(lastSale?.discount).toFixed(2)"></span></span>
                    </div>
                </template>
                <template x-if="lastSale?.tax > 0">
                    <div style="display:flex; justify-content:space-between;">
                        <span>Tax:</span><span>৳ <span x-text="Number(lastSale?.tax).toFixed(2)"></span></span>
                    </div>
                </template>
                <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; border-top:1px dashed #000; padding-top:4px; margin-top:4px;">
                    <span>TOTAL:</span><span>৳ <span x-text="Number(invoice?.total || 0).toFixed(2)"></span></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Paid (<span x-text="lastSale?.paymentMethod"></span>):</span><span>৳ <span x-text="Number(invoice?.paid || 0).toFixed(2)"></span></span>
                </div>
                <template x-if="invoice?.due > 0">
                    <div style="display:flex; justify-content:space-between; font-weight:700;">
                        <span>DUE:</span><span>৳ <span x-text="Number(invoice?.due).toFixed(2)"></span></span>
                    </div>
                </template>
                <template x-if="!(invoice?.due > 0)">
                    <div style="display:flex; justify-content:space-between;">
                        <span>Change:</span><span>৳ <span x-text="Number(invoice?.change || 0).toFixed(2)"></span></span>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div style="text-align:center; border-top:1px dashed #000; padding-top:8px; font-size:10px; color:#444;">
                <div>*** Thank you for shopping! ***</div>
                <div style="margin-top:2px;">Please come again</div>
                <div style="margin-top:4px; font-size:9px; color:#888;">Powered by {{ $appSettings['name'] ?? 'Super POS' }}</div>
            </div>
        </div>
    </template>
</div>
</div>

@endsection

@push('scripts')
<script>
function posApp() {
    return {
        products: [],
        cart: [],
        search: '',
        categoryFilter: '',
        loading: false,
        saving: false,
        customerId: '',
        customerName: '',
        customerSearch: '',
        customerOpen: false,
        openVariantId: null,
        discount: 0,
        tax: 0,
        paidAmount: '',
        paymentMethod: 'cash',
        invoice: null,
        lastSale: null,
        allCustomers: @json($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone ?? ''])),

        get subtotal() {
            return this.cart.reduce((s, i) => s + i.price * i.quantity, 0);
        },
        get total() {
            return Math.max(0, this.subtotal - this.discount + this.tax);
        },
        get filteredCustomers() {
            if (!this.customerSearch) return this.allCustomers;
            const q = this.customerSearch.toLowerCase();
            return this.allCustomers.filter(c =>
                c.name.toLowerCase().includes(q) || c.phone.toLowerCase().includes(q)
            );
        },

        storageKey: 'pos_cart_branch_{{ $branch->id }}',

        persist() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify({
                    cart: this.cart,
                    discount: this.discount,
                    tax: this.tax,
                    paidAmount: this.paidAmount,
                    paymentMethod: this.paymentMethod,
                    customerId: this.customerId,
                    customerName: this.customerName,
                }));
            } catch (e) {}
        },

        restore() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (!raw) return;
                const data = JSON.parse(raw);
                this.cart = data.cart || [];
                this.discount = data.discount || 0;
                this.tax = data.tax || 0;
                this.paidAmount = data.paidAmount ?? '';
                this.paymentMethod = data.paymentMethod || 'cash';
                this.customerId = data.customerId || '';
                this.customerName = data.customerName || '';
            } catch (e) {}
        },

        async loadProducts() {
            this.loading = true;
            const params = new URLSearchParams({ search: this.search, category_id: this.categoryFilter });
            const res = await fetch(`{{ route('branch.pos.products', $branch) }}?${params}`, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            this.products = await res.json();
            this.loading = false;
        },

        addSimple(product) {
            if (product.stock_qty === 0) return;
            this._pushItem({
                id: product.id,
                name: product.name,
                price: Number(product.price) || 0,
                stock_qty: product.stock_qty,
                variant_id: null,
                variant_name: null,
                image: product.image || null,
            });
        },

        addVariant(product, v) {
            if (v.stock_qty === 0) return;
            this._pushItem({
                id: product.id,
                name: product.name,
                price: Number(v.price) || 0,
                stock_qty: v.stock_qty,
                variant_id: v.id,
                variant_name: v.variant_name,
                image: product.image || null,
            });
            this.openVariantId = null;
        },

        _pushItem(item) {
            const key = `${item.id}_${item.variant_id ?? 'base'}`;
            const existing = this.cart.find(c => c._key === key);
            if (existing) {
                if (existing.quantity < existing.stock_qty) existing.quantity++;
            } else {
                this.cart.push({ ...item, _key: key, quantity: 1 });
            }
        },

        incrementItem(i) { if (this.cart[i].quantity < this.cart[i].stock_qty) this.cart[i].quantity++; },
        decrementItem(i) { this.cart[i].quantity > 1 ? this.cart[i].quantity-- : this.removeItem(i); },
        setItemQty(i, val) {
            val = parseInt(val);
            if (isNaN(val) || val < 1) { this.removeItem(i); return; }
            this.cart[i].quantity = Math.min(val, this.cart[i].stock_qty);
        },
        removeItem(i) { this.cart.splice(i, 1); },
        clearCart() {
            this.cart = []; this.discount = 0; this.tax = 0;
            this.paidAmount = ''; this.customerId = ''; this.customerName = '';
        },

        async checkout() {
            if (this.cart.length === 0) return;
            if (this.paidAmount === '') {
                showToast('Please enter the paid amount (0 if fully due).', 'error');
                return;
            }
            this.saving = true;
            try {
                const payload = {
                    customer_id: this.customerId || null,
                    items: this.cart.map(i => ({
                        product_id: i.id,
                        product_variant_id: i.variant_id || null,
                        quantity: i.quantity,
                        unit_price: Number(i.price) || 0,
                    })),
                    discount: Number(this.discount) || 0,
                    tax: Number(this.tax) || 0,
                    paid_amount: Number(this.paidAmount) || 0,
                    payment_method: this.paymentMethod,
                };
                const res = await fetch('{{ route('branch.pos.sale', $branch) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json().catch(() => null);
                if (!res.ok || !data || !data.success) {
                    let errMsg = 'Sale could not be completed.';
                    if (data && data.message) errMsg = data.message;
                    else if (data && data.errors) errMsg = Object.values(data.errors).flat().join(', ');
                    else if (!res.ok) errMsg = 'Server error (' + res.status + ')';
                    showToast(errMsg, 'error');
                    this.saving = false;
                    return;
                }

                const currentCart = [...this.cart];
                const currentSubtotal = this.subtotal;
                const currentDiscount = Number(this.discount) || 0;
                const currentTax = Number(this.tax) || 0;
                const currentCustName = this.customerId ? this.customerName : '';
                const currentPayMethod = this.paymentMethod;

                this.invoice = data;
                this.lastSale = {
                    items: currentCart.map(i => {
                        const uPrice = Number(i.price) || 0;
                        const qty = Number(i.quantity) || 1;
                        return {
                            name: i.name + (i.variant_name ? ' (' + i.variant_name + ')' : ''),
                            image: i.image || null,
                            qty: qty,
                            price: uPrice,
                            subtotal: uPrice * qty,
                        };
                    }),
                    subtotal: currentSubtotal,
                    discount: currentDiscount,
                    tax: currentTax,
                    customerName: currentCustName,
                    paymentMethod: currentPayMethod,
                    printedAt: new Date().toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
                };
                this.clearCart();
                this.loadProducts();
                showToast('Sale completed!', 'success');
                this.$nextTick(() => setTimeout(() => window.print(), 400));
            } catch (e) {
                showToast('Connection error: ' + (e.message || 'Please try again.'), 'error');
            }
            this.saving = false;
        },

        newSale() { this.invoice = null; },
        init() {
            this.restore();
            this.loadProducts();
            this.$watch('cart', () => this.persist());
            this.$watch('discount', () => this.persist());
            this.$watch('tax', () => this.persist());
            this.$watch('paidAmount', () => this.persist());
            this.$watch('paymentMethod', () => this.persist());
            this.$watch('customerId', () => this.persist());
            this.$watch('customerName', () => this.persist());
        }
    };
}
</script>
@endpush
