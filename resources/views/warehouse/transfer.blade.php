@extends('layouts.app')
@section('title','Warehouse — Stock Transfer')
@section('heading','Warehouse')

@section('content')
<div x-data="warehouseTransfer()" x-init="init()" class="grid grid-cols-1 lg:grid-cols-5 gap-4">

    {{-- ══ TRANSFER FORM ══ --}}
    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-right-left text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-white text-[14.5px]">Stock Transfer</p>
                    <p class="text-[11.5px] text-amber-50">Send Warehouse stock to a branch</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.warehouse.transfer.store') }}" class="p-5" @submit="onSubmit($event)">
                @csrf

                {{-- From (fixed) / To picker --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="flex-1">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1.5">From</label>
                        <div class="w-full border-2 border-slate-200 rounded-xl px-3 py-2.5 text-sm font-semibold bg-slate-100 text-slate-600 flex items-center gap-2">
                            <i class="fas fa-warehouse text-orange-500 text-xs"></i> {{ $warehouse->name }}
                        </div>
                    </div>
                    <div class="pt-5">
                        <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center">
                            <i class="fas fa-arrow-right text-amber-600 text-xs"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1.5">To Branch</label>
                        <select name="to_vendor_id" x-model="toVendor" required
                                class="w-full border-2 border-slate-200 rounded-xl px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 bg-slate-50">
                            <option value="">Select branch</option>
                            @foreach($toBranches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <template x-if="allProducts.length === 0">
                    <div class="text-center py-8 mb-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <i class="fas fa-box-open text-slate-300 text-2xl mb-2 block"></i>
                        <p class="text-slate-400 text-[12.5px]">The Warehouse has no in-stock products. Purchase some first.</p>
                    </div>
                </template>

                <div class="space-y-2.5" x-show="allProducts.length > 0">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Products</label>

                    <template x-for="(row, idx) in rows" :key="row.key">
                        <div class="flex gap-2 items-start bg-slate-50 rounded-xl p-2.5 border border-slate-100">
                            <div class="flex-1 relative" @click.outside="row.open = false">
                                <input type="hidden" :name="`items[${idx}][product_id]`" :value="row.productId">
                                <div class="relative">
                                    <input type="text" x-model="row.search" autocomplete="off"
                                           @focus="row.open = true"
                                           @input="row.productId = ''; row.open = true"
                                           @keydown.escape="row.open = false"
                                           placeholder="Search product..."
                                           class="w-full border border-slate-200 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                                    <i class="fas fa-times absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs cursor-pointer hover:text-red-500"
                                       x-show="row.productId" @click="clearProduct(row)"></i>
                                    <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-xs pointer-events-none"
                                       x-show="!row.productId"></i>
                                </div>
                                <div x-show="row.open" x-cloak
                                     class="absolute z-20 top-full mt-1 left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-xl max-h-52 overflow-y-auto">
                                    <template x-for="p in filteredProducts(row)" :key="p.id">
                                        <div @click="selectProduct(row, p)"
                                             class="px-3 py-2 text-sm hover:bg-amber-50 cursor-pointer border-b border-slate-50 last:border-0 flex items-center justify-between gap-2">
                                            <span x-text="p.name"></span>
                                            <span class="text-[11px] text-slate-400 flex-shrink-0" x-text="'stock: ' + p.stock_qty"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredProducts(row).length === 0" class="px-3 py-3 text-sm text-slate-400 text-center">
                                        No matching products
                                    </div>
                                </div>
                            </div>
                            <div class="w-24">
                                <input type="number" :name="`items[${idx}][quantity]`" x-model.number="row.quantity"
                                       @input="clampRow(row)" min="1" :max="maxQty(row.productId)" required
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm font-bold text-right focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                            <button type="button" @click="removeRow(idx)" x-show="rows.length > 1"
                                    class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors flex-shrink-0">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="addRow()"
                            class="text-xs font-semibold text-amber-600 hover:text-amber-700 disabled:text-slate-300 disabled:cursor-not-allowed inline-flex items-center gap-1.5 mt-1">
                        <i class="fas fa-plus text-[10px]"></i> Add another product
                    </button>
                </div>

                <div class="mt-4">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Note (optional)</label>
                    <input type="text" name="note" placeholder="e.g. Weekly restock"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <button type="submit"
                        :disabled="submitting || !toVendor || !rows.every(r => r.productId && r.quantity > 0)"
                        class="w-full mt-5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 disabled:opacity-40 disabled:cursor-not-allowed text-white py-3 rounded-xl text-sm font-bold transition-all shadow-sm">
                    <i class="fas fa-right-left text-xs mr-1.5"></i> <span x-text="submitting ? 'Transferring...' : 'Transfer Stock'"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- ══ RECENT TRANSFERS ══ --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3.5 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-slate-400 text-xs"></i>
                <p class="font-bold text-slate-800 text-[13.5px]">Recent Transfers</p>
            </div>
            <div class="divide-y divide-slate-50 max-h-[600px] overflow-y-auto">
                @forelse($recentTransfers as $t)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-slate-800 text-[12.5px] truncate">{{ $t->product->name ?? 'Deleted product' }}</p>
                        <span class="text-[11px] font-bold text-white bg-amber-500 rounded-full px-2 py-0.5 flex-shrink-0">{{ $t->quantity }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                        <i class="fas fa-warehouse text-[9px]"></i>{{ $warehouse->name }}
                        <i class="fas fa-arrow-right text-[8px] text-slate-300"></i>
                        <span class="text-slate-500">{{ Str::after($t->note, ' to ') }}</span>
                    </p>
                    <p class="text-[10.5px] text-slate-400 mt-1">
                        <i class="fas fa-clock text-[9px] mr-0.5"></i>{{ $t->created_at->format('d M Y, h:i A') }}
                    </p>
                </div>
                @empty
                <div class="px-4 py-16 text-center">
                    <i class="fas fa-right-left text-slate-200 text-4xl mb-3 block"></i>
                    <p class="text-slate-400 text-sm">No transfers yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@php
    $transferProductsJson = $products->map(fn($p) => [
        'id' => $p->id, 'name' => $p->name, 'stock_qty' => $p->stock_qty,
    ]);
@endphp
@push('scripts')
<script>
function warehouseTransfer() {
    return {
        allProducts: @json($transferProductsJson),
        toVendor: '',
        rows: [{ key: 1, productId: '', quantity: 1, search: '', open: false }],
        nextKey: 2,
        submitting: false,

        init() {},

        stockFor(productId) {
            const p = this.allProducts.find(p => p.id == productId);
            return p ? p.stock_qty : 0;
        },
        maxQty(productId) {
            return this.stockFor(productId) || null;
        },
        clampRow(row) {
            const max = this.stockFor(row.productId);
            if (max && row.quantity > max) row.quantity = max;
            if (row.quantity < 1) row.quantity = 1;
        },
        isPicked(productId, exceptKey) {
            return this.rows.some(r => r.productId == productId && r.key !== exceptKey);
        },
        filteredProducts(row) {
            const q = (row.search || '').toLowerCase();
            return this.allProducts.filter(p => !this.isPicked(p.id, row.key) && p.name.toLowerCase().includes(q));
        },
        selectProduct(row, p) {
            row.productId = p.id;
            row.search    = p.name;
            row.open      = false;
            this.clampRow(row);
        },
        clearProduct(row) {
            row.productId = '';
            row.search    = '';
        },
        addRow() {
            this.rows.push({ key: this.nextKey++, productId: '', quantity: 1, search: '', open: false });
        },
        removeRow(idx) {
            this.rows.splice(idx, 1);
        },
        onSubmit() {
            this.submitting = true;
            return true;
        }
    };
}
</script>
@endpush
