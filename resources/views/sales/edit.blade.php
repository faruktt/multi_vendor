@extends('layouts.app')
@section('title', 'Edit Invoice — ' . $sale->invoice_no)
@section('heading', 'Edit: ' . $sale->invoice_no)

@section('content')
<div x-data="editApp()" class="flex gap-4 h-[calc(100vh-140px)]">

    {{-- ══ LEFT: Items Management ══════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-pen text-amber-600 text-sm"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-800 text-sm">{{ $sale->invoice_no }}</p>
                    <p class="text-xs text-slate-400">{{ $sale->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
            <a href="{{ route('branch.sales.show', [$branch, $sale]) }}"
               class="text-slate-400 hover:text-slate-600 text-sm flex items-center gap-1.5 hover:bg-slate-50 px-3 py-1.5 rounded-lg transition-colors">
                <i class="fas fa-arrow-left text-xs"></i> Cancel
            </a>
        </div>

        {{-- Product Search to Add --}}
        <div class="px-4 py-3 border-b border-slate-100 flex-shrink-0 relative" @click.outside="searchResults = []">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                       placeholder="Search product to add..."
                       class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50">
                <div x-show="searchLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <i class="fas fa-spinner fa-spin text-slate-400 text-xs"></i>
                </div>
            </div>
            {{-- Search Results Dropdown --}}
            <div x-show="searchResults.length > 0" x-cloak
                 class="absolute left-4 right-4 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-2xl z-30 overflow-hidden max-h-64 overflow-y-auto">
                <template x-for="p in searchResults" :key="p.id">
                    <div @click="addProduct(p)"
                         :class="p.stock_qty === 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:bg-blue-50'"
                         class="flex items-center gap-3 px-4 py-3 border-b border-slate-50 last:border-0 transition-colors">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-box text-blue-300 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 truncate" x-text="p.name"></p>
                            <p class="text-xs text-slate-400" x-text="'Stock: ' + p.stock_qty"></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-blue-600">৳<span x-text="Number(p.price).toLocaleString()"></span></p>
                            <span x-show="p.stock_qty === 0" class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full">Out</span>
                            <span x-show="p.variants && p.variants.length > 0" class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-full">Variants</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Unit Price</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide w-32">Total</th>
                        <th class="px-4 py-3 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <template x-for="(item, i) in cart" :key="item._key">
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800 text-[13px]" x-text="item.name"></p>
                                <template x-if="item.variant_name">
                                    <p class="text-xs text-indigo-500 mt-0.5" x-text="item.variant_name"></p>
                                </template>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button @click="item.quantity > 1 ? item.quantity-- : removeItem(i)"
                                            class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-red-100 hover:text-red-500 text-slate-500 font-bold flex items-center justify-center transition-colors">−</button>
                                    <input type="number" x-model.number="item.quantity" min="1"
                                           class="w-14 text-center border border-slate-200 rounded-lg py-1 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <button @click="item.quantity++"
                                            class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-blue-100 hover:text-blue-600 text-slate-500 font-bold flex items-center justify-center transition-colors">+</button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">৳</span>
                                    <input type="number" x-model.number="item.unit_price" min="0" step="0.01"
                                           class="w-full pl-7 pr-3 py-1.5 border border-slate-200 rounded-lg text-sm font-semibold text-right text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold text-blue-600">৳<span x-text="Number(item.quantity * item.unit_price).toLocaleString()"></span></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button @click="removeItem(i)"
                                        class="w-7 h-7 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-all opacity-0 group-hover:opacity-100">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="cart.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                            <i class="fas fa-box-open text-3xl opacity-30 mb-2 block"></i>
                            <p class="text-sm">No items — search above to add products</p>
                        </td>
                    </tr>
                </tbody>
                <tfoot x-show="cart.length > 0" class="border-t-2 border-slate-200 bg-slate-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Subtotal</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">৳<span x-text="Number(subtotal).toLocaleString()"></span></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ══ RIGHT: Summary & Save ═══════════════════════════════════════ --}}
    <div class="w-[300px] flex-shrink-0 flex flex-col gap-3">

        {{-- Customer --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 relative" @click.outside="customerOpen = false">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Customer</p>
            <button type="button" @click="customerOpen = !customerOpen"
                    class="w-full flex items-center gap-2 border rounded-xl px-3 py-2.5 text-sm transition-all"
                    :class="customerOpen ? 'border-blue-400 ring-2 ring-blue-100' : 'border-slate-200 hover:border-slate-300'">
                <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center"
                     :class="customerId ? 'bg-blue-100' : 'bg-slate-100'">
                    <i class="fas fa-user text-[10px]" :class="customerId ? 'text-blue-600' : 'text-slate-400'"></i>
                </div>
                <span class="flex-1 text-left truncate text-[13px]"
                      :class="customerId ? 'text-slate-800 font-semibold' : 'text-slate-400'"
                      x-text="customerId ? customerName : 'Walk-in Customer'"></span>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="customerOpen ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="customerOpen" x-cloak
                 class="absolute left-4 right-4 top-full mt-0 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 overflow-hidden">
                <div class="p-2 border-b border-slate-100">
                    <input type="text" x-model="customerSearch" placeholder="Search..." @click.stop
                           class="w-full text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="max-h-44 overflow-y-auto">
                    <div @click="customerId=''; customerName=''; customerOpen=false; customerSearch=''"
                         class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-slate-50 transition-colors"
                         :class="!customerId ? 'bg-blue-50' : ''">
                        <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[9px]"><i class="fas fa-walking text-slate-500"></i></div>
                        <span class="text-[13px] text-slate-600 flex-1 font-medium">Walk-in</span>
                        <i x-show="!customerId" class="fas fa-check text-blue-500 text-xs"></i>
                    </div>
                    <template x-for="c in filteredCustomers" :key="c.id">
                        <div @click="customerId=c.id; customerName=c.name; customerOpen=false; customerSearch=''"
                             class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors border-t border-slate-50"
                             :class="customerId==c.id ? 'bg-blue-50' : ''">
                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-600 text-[10px] font-bold" x-text="c.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-semibold text-slate-700 truncate" x-text="c.name"></p>
                                <p class="text-[10.5px] text-slate-400 truncate" x-show="c.phone" x-text="c.phone"></p>
                            </div>
                            <i x-show="customerId==c.id" class="fas fa-check text-blue-500 text-xs flex-shrink-0"></i>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Discount / Tax / Payment --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 font-medium">Subtotal</span>
                <span class="font-bold text-slate-800">৳<span x-text="Number(subtotal).toLocaleString()"></span></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="text-slate-500 font-medium">Discount (৳)</label>
                <input type="number" x-model.number="discount" min="0" :max="subtotal"
                       class="w-28 text-right border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="text-slate-500 font-medium">Tax (৳)</label>
                <input type="number" x-model.number="tax" min="0"
                       class="w-28 text-right border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                <span class="font-bold text-slate-800">Total</span>
                <span class="font-bold text-xl text-blue-600">৳<span x-text="Number(total).toLocaleString()"></span></span>
            </div>
        </div>

        {{-- Payment --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Paid Amount</label>
                <input type="number" x-model.number="paidAmount" min="0" :placeholder="total.toFixed(0)"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm font-bold text-right focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Payment Method</label>
                <select x-model="paymentMethod"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-between text-sm border-t border-slate-100 pt-2">
                <span class="text-slate-500">Due</span>
                <span class="font-bold" :class="due > 0 ? 'text-red-500' : 'text-emerald-600'">
                    ৳<span x-text="Number(Math.max(0,due)).toLocaleString()"></span>
                </span>
            </div>
        </div>

        {{-- Save Button --}}
        <button @click="saveUpdate()" :disabled="saving || cart.length === 0"
                class="w-full bg-amber-500 hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white py-3.5 rounded-2xl font-bold text-sm transition-all shadow-md shadow-amber-200/60 active:scale-[0.98]">
            <span x-show="!saving" class="flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Update Invoice
            </span>
            <span x-show="saving" class="flex items-center justify-center gap-2">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editApp() {
    return {
        cart: @json($initialCart),
        customerId: '{{ $sale->customer_id ?? "" }}',
        customerName: '{{ $sale->customer?->name ?? "" }}',
        customerSearch: '',
        customerOpen: false,
        allCustomers: @json($customers),
        discount: {{ (float)$sale->discount }},
        tax: {{ (float)$sale->tax }},
        paidAmount: {{ (float)$sale->paid_amount }},
        paymentMethod: '{{ $sale->payment_method }}',
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        saving: false,

        get subtotal() {
            return this.cart.reduce((s, i) => s + i.quantity * i.unit_price, 0);
        },
        get total() {
            return Math.max(0, this.subtotal - this.discount + this.tax);
        },
        get due() {
            return Math.max(0, this.total - this.paidAmount);
        },
        get filteredCustomers() {
            if (!this.customerSearch) return this.allCustomers;
            const q = this.customerSearch.toLowerCase();
            return this.allCustomers.filter(c =>
                c.name.toLowerCase().includes(q) || c.phone.toLowerCase().includes(q)
            );
        },

        async searchProducts() {
            if (!this.searchQuery.trim()) { this.searchResults = []; return; }
            this.searchLoading = true;
            const params = new URLSearchParams({ search: this.searchQuery, category_id: '' });
            const res = await fetch(`{{ route('branch.pos.products', $branch) }}?${params}`);
            this.searchResults = await res.json();
            this.searchLoading = false;
        },

        addProduct(product) {
            if (product.stock_qty === 0) return;
            const key = `${product.id}_base`;
            const existing = this.cart.find(c => c._key === key);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    _key: key,
                    product_id: product.id,
                    name: product.name,
                    quantity: 1,
                    unit_price: parseFloat(product.price),
                    variant_id: null,
                    variant_name: null,
                });
            }
            this.searchQuery = '';
            this.searchResults = [];
        },

        removeItem(i) {
            this.cart.splice(i, 1);
        },

        async saveUpdate() {
            this.errorMsg = '';
            if (this.cart.length === 0) { this.errorMsg = 'Add at least one item.'; return; }
            this.saving = true;

            try {
                const res = await fetch('{{ route('branch.sales.update', [$branch, $sale]) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        customer_id:     this.customerId || null,
                        discount:        this.discount,
                        tax:             this.tax,
                        paid_amount:     this.paidAmount || this.total,
                        payment_method:  this.paymentMethod,
                        items: this.cart.map(i => ({
                            product_id:   i.product_id,
                            variant_id:   i.variant_id || null,
                            variant_name: i.variant_name || null,
                            quantity:     parseInt(i.quantity),
                            unit_price:   parseFloat(i.unit_price),
                        })),
                    }),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast('Invoice updated successfully!', 'success');
                    setTimeout(() => { window.location.href = data.redirect; }, 800);
                } else {
                    const msg = data.message || Object.values(data.errors ?? {}).flat().join(', ') || 'Save failed.';
                    showToast(msg, 'error');
                }
            } catch (e) {
                showToast('Connection error. Please try again.', 'error');
            }

            this.saving = false;
        },

        init() {}
    };
}
</script>
@endpush
