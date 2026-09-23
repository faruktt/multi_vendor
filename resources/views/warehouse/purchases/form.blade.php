@extends('layouts.app')
@section('title', isset($purchase) ? 'Edit Purchase' : 'New Purchase')
@section('heading', isset($purchase) ? 'Edit Purchase Order' : 'New Purchase Order')

@section('content')
<div x-data="purchaseForm()">
<form method="POST"
      action="{{ isset($purchase) ? route('admin.warehouse.purchases.update', $purchase) : route('admin.warehouse.purchases.store') }}"
      @submit.prevent="submitForm($event)">
    @csrf

<div class="flex flex-col xl:flex-row gap-5">

    {{-- ══ LEFT ══════════════════════════════════════════════════════ --}}
    <div class="flex-1 space-y-4">

        {{-- Purchase Info --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-file-invoice text-purple-600 text-xs"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Purchase Information</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">

                {{-- Supplier searchable --}}
                <div class="md:col-span-2" x-data="supplierDropdown('{{ old('supplier_id', $purchase->supplier_id ?? '') }}', '{{ old('supplier_id', $purchase->supplier_id ?? '') ? ($purchase->supplier->name ?? '') : '' }}')">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Supplier</label>
                    <div class="relative">
                        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent overflow-hidden">
                            <i class="fas fa-truck text-slate-400 text-xs ml-3.5"></i>
                            <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                   @keydown.escape="open = false" @input="open = true"
                                   placeholder="Search supplier..."
                                   class="flex-1 px-3 py-2.5 text-sm bg-transparent focus:outline-none">
                            <i x-show="selectedId" @click="clear()" class="fas fa-times text-slate-400 text-xs mr-3 cursor-pointer hover:text-red-500"></i>
                        </div>
                        <input type="hidden" name="supplier_id" :value="selectedId">
                        <div x-show="open && filtered.length" x-cloak @click.outside="open = false"
                             class="absolute z-20 top-full mt-1 left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-xl max-h-52 overflow-y-auto">
                            <template x-for="s in filtered" :key="s.id">
                                <div @click="pick(s)" class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0">
                                    <p class="font-semibold text-slate-800 text-sm" x-text="s.name"></p>
                                    <p x-show="s.phone" class="text-xs text-slate-400 mt-0.5" x-text="s.phone"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Payment Method</label>
                    <select name="payment_method"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                        @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_method', $purchase->payment_method ?? 'cash') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Discount (৳)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">৳</span>
                        <input type="number" name="discount" step="0.01" min="0"
                               x-model.number="discount"
                               value="{{ old('discount', $purchase->discount ?? 0) }}"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount Paid (৳) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">৳</span>
                        <input type="number" name="paid_amount" step="0.01" min="0" required
                               x-model.number="paidAmount"
                               value="{{ old('paid_amount', $purchase->paid_amount ?? 0) }}"
                               class="w-full pl-8 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors font-semibold">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Due: <span class="font-semibold" :class="due > 0 ? 'text-red-500' : 'text-emerald-600'"
                                  x-text="due > 0 ? '৳' + due.toFixed(0) : 'Fully paid'"></span>
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Note</label>
                    <textarea name="note" rows="2" placeholder="Optional note..."
                              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors resize-none">{{ old('note', $purchase->note ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-cubes text-blue-600 text-xs"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 text-sm">Purchase Items</h3>
                    <span class="text-[11px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-semibold" x-text="items.length + ' item' + (items.length !== 1 ? 's' : '')"></span>
                </div>
                <button type="button" @click="addItem()"
                        class="flex items-center gap-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors">
                    <i class="fas fa-plus text-[10px]"></i> Add Item
                </button>
            </div>

            {{-- Product Quick Search --}}
            <div class="mb-3" x-data="productSearch()">
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="q" @input.debounce.300ms="search()"
                           @focus="open = results.length > 0"
                           @keydown.escape="open = false"
                           placeholder="Type product name to add quickly..."
                           class="w-full pl-9 pr-4 py-2.5 border border-dashed border-blue-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50/50 focus:bg-white transition-colors">
                    <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <i class="fas fa-spinner fa-spin text-blue-400 text-xs"></i>
                    </div>
                </div>
                <div x-show="open && results.length" x-cloak @click.outside="open = false"
                     class="relative z-20 bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto mt-1">
                    <template x-for="p in results" :key="p.id">
                        <div @click="addProduct(p)" class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-slate-50 last:border-0 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-400">
                                <i class="fas fa-box text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800 text-sm truncate" x-text="p.name"></p>
                                <p class="text-[11px] text-slate-400" x-text="(p.branch ? p.branch + ' · ' : '') + 'Cost: ৳' + (p.cost_price ?? 0)"></p>
                            </div>
                            <span class="text-xs text-blue-600 font-semibold bg-blue-50 px-2 py-0.5 rounded-lg flex-shrink-0">+ Add</span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Item Rows --}}
            <div class="space-y-2.5">
                <template x-for="(item, i) in items" :key="i">
                    <div class="border border-slate-200 rounded-xl p-3.5 hover:border-blue-200 transition-colors bg-slate-50/40">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5 text-blue-700 font-bold text-xs" x-text="i + 1"></div>

                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                                {{-- Product select --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Product <span class="text-red-500">*</span></label>
                                    <select x-model="item.product_id" :name="'items[' + i + '][product_id]'"
                                            @change="onProductChange(i)"
                                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                        <option value="">Select product</option>
                                        @foreach($products->groupBy(fn($p) => $p->category->name ?? 'General') as $catName => $catProducts)
                                        <optgroup label="{{ $catName }}">
                                            @foreach($catProducts as $p)
                                            <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}">{{ $p->name }} (Cost: ৳{{ number_format($p->cost_price, 0) }})</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Qty --}}
                                <div>
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Qty</label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="item.quantity = Math.max(1, item.quantity - 1)"
                                                class="w-7 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 text-xs">−</button>
                                        <input type="number" x-model.number="item.quantity" :name="'items[' + i + '][quantity]'" min="1"
                                               class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white font-semibold">
                                        <button type="button" @click="item.quantity++"
                                                class="w-7 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 text-xs">+</button>
                                    </div>
                                </div>

                                {{-- Unit Cost --}}
                                <div>
                                    <label class="block text-[10.5px] font-semibold text-slate-500 uppercase tracking-wide mb-1">Unit Cost (৳)</label>
                                    <input type="number" x-model.number="item.unit_cost" :name="'items[' + i + '][unit_cost]'" step="0.01" min="0"
                                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white font-semibold text-slate-800">
                                </div>
                            </div>

                            {{-- Subtotal + Remove --}}
                            <div class="flex flex-col items-end gap-1.5 mt-0.5 flex-shrink-0">
                                <p class="text-[11px] text-slate-400">Subtotal</p>
                                <p class="font-bold text-slate-800 text-sm">৳<span x-text="(item.quantity * item.unit_cost).toFixed(0)"></span></p>
                                <button type="button" @click="removeItem(i)" x-show="items.length > 1"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0"
                     class="border-2 border-dashed border-slate-200 rounded-xl py-8 text-center text-slate-400">
                    <i class="fas fa-cubes text-2xl mb-2 block text-slate-300"></i>
                    <p class="text-sm">No items added yet</p>
                </div>
            </div>

            {{-- Subtotal bar --}}
            <div class="flex justify-between items-center bg-blue-50 border border-blue-100 rounded-xl px-4 py-2.5 mt-3">
                <span class="text-xs text-blue-600 font-semibold">Items Subtotal</span>
                <span class="font-bold text-blue-700">৳<span x-text="subtotal.toFixed(2)"></span></span>
            </div>
        </div>

    </div>{{-- left --}}

    {{-- ══ RIGHT: Summary + Save ════════════════════════════════════ --}}
    <div class="w-full xl:w-72 flex-shrink-0 space-y-4">

        {{-- Summary Card --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 text-white shadow-md shadow-blue-200/50">
            <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide mb-4">Order Summary</p>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-blue-200">Items</span>
                    <span class="font-semibold" x-text="items.length + ' item' + (items.length !== 1 ? 's' : '')"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-blue-200">Subtotal</span>
                    <span class="font-semibold">৳<span x-text="subtotal.toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between text-sm" x-show="discount > 0">
                    <span class="text-blue-200">Discount</span>
                    <span class="font-semibold text-emerald-300">−৳<span x-text="discount.toFixed(2)"></span></span>
                </div>
                <div class="border-t border-blue-500 pt-3 flex justify-between">
                    <span class="font-bold">Total</span>
                    <span class="font-bold text-lg">৳<span x-text="total.toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-blue-200">Paid</span>
                    <span class="font-semibold text-emerald-300">৳<span x-text="paidAmount.toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between text-sm" x-show="due > 0">
                    <span class="text-blue-200">Due</span>
                    <span class="font-bold text-red-300">৳<span x-text="due.toFixed(2)"></span></span>
                </div>
                <div x-show="due <= 0" class="flex items-center gap-1.5 text-emerald-300 text-xs">
                    <i class="fas fa-check-circle"></i> Fully paid
                </div>
            </div>
        </div>

        {{-- Status indicator --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide mb-2">Payment Status</p>
            <div x-show="due <= 0" class="flex items-center gap-2 text-emerald-600">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                <span class="font-bold text-sm">Paid</span>
            </div>
            <div x-show="due > 0 && paidAmount > 0" class="flex items-center gap-2 text-amber-600">
                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                <span class="font-bold text-sm">Partial</span>
            </div>
            <div x-show="paidAmount <= 0" class="flex items-center gap-2 text-red-500">
                <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                <span class="font-bold text-sm">Pending</span>
            </div>
        </div>

        {{-- Save --}}
        <div class="space-y-2.5">
            <button type="submit" :disabled="submitting || items.length === 0"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white py-3 rounded-2xl font-bold text-sm shadow-md shadow-blue-200/60 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <span x-show="!submitting" class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ isset($purchase) ? 'Update Purchase' : 'Save Purchase' }}
                </span>
                <span x-show="submitting" class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i> Saving...
                </span>
            </button>
            <a href="{{ route('admin.warehouse.purchases.index') }}"
               class="w-full border border-slate-200 text-slate-600 py-2.5 rounded-2xl text-sm font-medium hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Cancel
            </a>
        </div>
    </div>

</div>{{-- flex --}}
</form>
</div>{{-- x-data --}}
@endsection

@php
    $jsSuppliers = $suppliers->map(fn($s) => [
        'id'    => $s->id,
        'name'  => $s->name,
        'phone' => $s->phone ?? '',
    ])->values();

    $jsProducts = $products->map(fn($p) => [
        'id'         => (string)$p->id,
        'name'       => $p->name,
        'sku'        => $p->sku ?? '',
        'cost_price' => (float)($p->cost_price ?? 0),
        'price'      => (float)($p->price ?? 0),
        'category'   => $p->category->name ?? 'General',
    ])->values();

    $jsProductCosts = $products->mapWithKeys(fn($p) => [
        (string)$p->id => (float)($p->cost_price ?? 0)
    ]);

    $existingItems = isset($purchase)
        ? $purchase->items->map(fn($i) => [
            'product_id' => (string)($i->display_product_id ?? $i->product_id),
            'quantity'   => (int)$i->quantity,
            'unit_cost'  => (float)$i->unit_cost,
        ])->values()
        : collect([]);
@endphp

@push('scripts')
<script>
const allSuppliers = {!! json_encode($jsSuppliers) !!};
const allProducts  = {!! json_encode($jsProducts) !!};

/* ── Supplier searchable dropdown ── */
function supplierDropdown(initId, initName) {
    return {
        open: false,
        search: initName || '',
        selectedId: initId || '',
        get filtered() {
            const q = this.search.toLowerCase();
            return allSuppliers.filter(s => s.name.toLowerCase().includes(q) || (s.phone && s.phone.includes(q)));
        },
        pick(s) { this.selectedId = s.id; this.search = s.name; this.open = false; },
        clear() { this.selectedId = ''; this.search = ''; },
    };
}

/* ── Product quick search ── */
function productSearch() {
    return {
        q: '',
        results: [],
        open: false,
        search() {
            const query = this.q.trim().toLowerCase();
            if (query.length < 1) {
                this.results = [];
                this.open = false;
                return;
            }
            this.results = allProducts.filter(p =>
                p.name.toLowerCase().includes(query) ||
                p.sku.toLowerCase().includes(query) ||
                p.category.toLowerCase().includes(query)
            ).slice(0, 12);
            this.open = this.results.length > 0;
        },
        addProduct(p) {
            const root = this.$root.closest('[x-data]');
            if (root && root._x_dataStack) {
                const pf = Alpine.$data(root);
                pf.addItemFromProduct(p.id, p.cost_price || 0);
            }
            this.q = '';
            this.results = [];
            this.open = false;
        }
    };
}

/* ── Main form ── */
const productCosts = {!! json_encode($jsProductCosts) !!};

function purchaseForm() {
    const existing = {!! json_encode($existingItems) !!};
    return {
        items:       existing.length ? existing : [{ product_id: '', quantity: 1, unit_cost: 0 }],
        discount:    {{ old('discount', $purchase->discount ?? 0) }},
        paidAmount:  {{ old('paid_amount', $purchase->paid_amount ?? 0) }},
        submitting:  false,

        get subtotal() { return this.items.reduce((s, i) => s + Number(i.quantity) * Number(i.unit_cost), 0); },
        get total()    { return Math.max(0, this.subtotal - this.discount); },
        get due()      { return Math.max(0, this.total - this.paidAmount); },

        addItem()    { this.items.push({ product_id: '', quantity: 1, unit_cost: 0 }); },
        removeItem(i){ if (this.items.length > 1) this.items.splice(i, 1); },

        addItemFromProduct(productId, cost) {
            const exists = this.items.find(i => i.product_id == productId);
            if (exists) { exists.quantity++; return; }
            this.items.push({ product_id: String(productId), quantity: 1, unit_cost: Number(cost) || 0 });
            // set the select value in next tick
            this.$nextTick(() => {
                const selects = this.$el.querySelectorAll('select[name^="items"]');
                const last = selects[selects.length - 1];
                if (last) last.value = productId;
            });
        },

        onProductChange(i) {
            const id = this.items[i].product_id;
            if (id && productCosts[id] !== undefined) {
                this.items[i].unit_cost = productCosts[id];
            }
        },

        submitForm(e) {
            if (this.items.length === 0) { showToast('Add at least one item.', 'error'); return; }
            const empty = this.items.some(i => !i.product_id);
            if (empty) { showToast('Select a product for each row.', 'error'); return; }
            this.submitting = true;
            e.target.submit();
        }
    };
}
</script>
@endpush
