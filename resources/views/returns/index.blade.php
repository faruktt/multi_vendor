@extends('layouts.app')
@section('title', 'Partial Returns')
@section('heading', 'Partial Returns')

@section('content')
<div x-data="partialReturnsPage()" class="space-y-4">

    {{-- ── 1. KPI / Summary Cards ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Total Partial Returns --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Partial Returns</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($totalCount) }}</span>
                    <span class="text-xs font-bold text-slate-500">৳{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-rotate-left"></i>
            </div>
        </div>

        {{-- Pending Restock --}}
        <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Pending Restock</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-amber-900">{{ number_format($pendingCount) }}</span>
                    <span class="text-xs font-bold text-amber-700">৳{{ number_format($pendingAmount, 2) }}</span>
                </div>
                <p class="text-[11px] text-amber-600/80 mt-0.5 font-medium">Awaiting return & stock restore</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        {{-- Restocked to Inventory --}}
        <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Restocked to Inventory</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-emerald-900">{{ number_format($returnedCount) }}</span>
                    <span class="text-xs font-bold text-emerald-700">৳{{ number_format($returnedAmount, 2) }}</span>
                </div>
                <p class="text-[11px] text-emerald-600/80 mt-0.5 font-medium">Returned & stock restored</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fas fa-check-double"></i>
            </div>
        </div>
    </div>

    {{-- ── 2. Filters & Status Tabs ────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-3">
        {{-- Tabs & Bulk Action --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-1.5 p-1 bg-slate-100/70 rounded-xl">
                @php
                    $currentStatus = request('status', 'all');
                    $baseRoute = isset($branch) ? route('branch.returns.index', $branch) : route('admin.returns.index');
                @endphp
                <a href="{{ $baseRoute }}?{{ http_build_query(array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentStatus === 'all' ? 'bg-white text-slate-800 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800' }}">
                    All Returns ({{ $totalCount }})
                </a>
                <a href="{{ $baseRoute }}?{{ http_build_query(array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 {{ $currentStatus === 'pending' ? 'bg-amber-500 text-white shadow-sm font-bold' : 'text-amber-700 hover:text-amber-900' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $currentStatus === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                    Pending Restock ({{ $pendingCount }})
                </a>
                <a href="{{ $baseRoute }}?{{ http_build_query(array_merge(request()->except('status', 'page'), ['status' => 'returned'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 {{ $currentStatus === 'returned' ? 'bg-emerald-600 text-white shadow-sm font-bold' : 'text-emerald-700 hover:text-emerald-900' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $currentStatus === 'returned' ? 'bg-white' : 'bg-emerald-600' }}"></span>
                    Restocked ({{ $returnedCount }})
                </a>
            </div>

            {{-- Bulk Actions Bar --}}
            <div x-show="selectedIds.length > 0" x-cloak class="flex items-center gap-2">
                <span class="text-xs text-slate-600 font-semibold" x-text="selectedIds.length + ' item(s) selected'"></span>
                <button @click="submitBulkRestock()" :disabled="bulkLoading"
                        class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm flex items-center gap-1.5 transition-all">
                    <i class="fas fa-rotate-left" :class="bulkLoading ? 'fa-spin' : ''"></i>
                    <span>Bulk Restock Selected</span>
                </button>
            </div>
        </div>

        {{-- Filter Inputs Form --}}
        <form method="GET" action="{{ $baseRoute }}" class="flex flex-wrap items-center gap-2.5">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search Invoice #, Product, Customer..."
                       class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-xl text-xs sm:text-sm bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Supplier Filter --}}
            <select name="supplier_id" class="border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm bg-slate-50 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>

            {{-- Branch Filter (if not branch-scoped) --}}
            @if(!isset($branch))
            <select name="branch_id" class="border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm bg-slate-50 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            @endif

            {{-- Dates --}}
            <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl px-2 py-1">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-transparent text-xs text-slate-600 focus:outline-none py-1">
                <span class="text-slate-400 text-xs">to</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-transparent text-xs text-slate-600 focus:outline-none py-1">
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                <i class="fas fa-filter text-xs"></i> Filter
            </button>

            @if(request()->hasAny(['search', 'supplier_id', 'branch_id', 'date_from', 'date_to']))
            <a href="{{ $baseRoute }}?status={{ $currentStatus }}" class="text-slate-500 hover:text-slate-700 px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm hover:bg-slate-50 transition-colors">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ── 3. Table of Return Items ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected()"
                                   class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Return Info</th>
                        <th class="px-4 py-3.5">Invoice & Customer</th>
                        <th class="px-4 py-3.5">Product Details</th>
                        <th class="px-4 py-3.5 text-right">Qty & Price</th>
                        <th class="px-4 py-3.5">Product Source (Supplier / Branch)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returnItems as $item)
                    @php
                        $sale = $item->saleReturn?->sale;
                        $product = $item->product;
                        $productSupplier = $item->supplier ?: ($product?->supplier);
                        $productBranch = $item->saleReturn?->vendor ?: ($product?->vendor);
                        $isPending = $item->status === 'pending';
                        $imgUrl = $product?->first_image_url;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors group {{ $isPending ? 'bg-amber-50/15' : '' }}">
                        {{-- Checkbox --}}
                        <td class="px-4 py-3 text-center">
                            @if($isPending)
                            <input type="checkbox" value="{{ $item->id }}" x-model="selectedIds"
                                   class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400 w-4 h-4 cursor-pointer">
                            @else
                            <i class="fas fa-check text-emerald-400 text-xs"></i>
                            @endif
                        </td>

                        {{-- Return Info (ID & Date) --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-bold text-slate-800 text-xs">#RET-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $item->created_at->format('d M Y, h:i A') }}</div>
                            @if($item->notes)
                            <div class="text-[10px] text-slate-400 italic max-w-[140px] truncate" title="{{ $item->notes }}">{{ $item->notes }}</div>
                            @endif
                        </td>

                        {{-- Invoice & Customer --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($sale)
                            <a href="{{ isset($branch) ? route('branch.sales.show', [$branch, $sale]) : route('branch.sales.show', [$sale->vendor_id, $sale]) }}"
                               class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-800 hover:underline text-xs">
                                <i class="fas fa-receipt text-[10px]"></i> {{ $sale->invoice_no }}
                            </a>
                            <div class="text-[11px] text-slate-700 font-medium mt-0.5">
                                {{ $sale->customer?->name ?? 'Walk-in Customer' }}
                            </div>
                            @if($sale->customer?->phone)
                            <div class="text-[10px] text-slate-400 font-mono">{{ $sale->customer->phone }}</div>
                            @endif
                            @if(!isset($branch) && $item->saleReturn?->vendor)
                            <span class="inline-block mt-0.5 px-1.5 py-0.2 bg-slate-100 text-slate-500 text-[10px] rounded font-semibold">
                                {{ $item->saleReturn->vendor->name }}
                            </span>
                            @endif
                            @else
                            <span class="text-slate-400 italic text-xs">Direct / N/A</span>
                            @endif
                        </td>

                        {{-- Product Details (with working thumbnail) --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden border border-slate-200">
                                    @if($imgUrl)
                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="{{ $product?->name }}"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-box text-slate-300 text-base\'></i>'">
                                    @else
                                    <i class="fas fa-box text-slate-300 text-base"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 text-[13px] leading-snug line-clamp-1" title="{{ $product?->name }}">
                                        {{ $product?->name ?? 'Deleted Product' }}
                                    </p>
                                    @if($item->variant_name || $item->productVariant)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-semibold text-[10px] mt-0.5">
                                        <i class="fas fa-layer-group text-[9px]"></i> {{ $item->variant_name ?: $item->productVariant?->variant_name }}
                                    </span>
                                    @endif
                                    @if($product?->sku)
                                    <p class="text-[10px] text-slate-400 font-mono">SKU: {{ $product->sku }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Qty & Price --}}
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                {{ $item->quantity }} pcs
                            </span>
                            <div class="font-bold text-slate-800 text-xs mt-1">৳{{ number_format($item->subtotal, 2) }}</div>
                            <div class="text-[10px] text-slate-400">@ ৳{{ number_format($item->unit_price, 2) }}</div>
                        </td>

                        {{-- Product Source (Supplier OR Branch automatically displayed) --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($productSupplier)
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 font-semibold text-xs border border-purple-200"
                                 title="Supplier: {{ $productSupplier->name }}">
                                <i class="fas fa-industry text-[11px] text-purple-500"></i>
                                <span class="max-w-[160px] truncate">{{ $productSupplier->name }}</span>
                            </div>
                            @elseif($productBranch)
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-blue-50 text-blue-700 font-semibold text-xs border border-blue-200"
                                 title="Branch Product: {{ $productBranch->name }}">
                                <i class="fas fa-store text-[11px] text-blue-500"></i>
                                <span class="max-w-[160px] truncate">Branch: {{ $productBranch->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 italic text-xs">In-house Stock</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($isPending)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Pending Restock
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fas fa-check-circle text-emerald-600 text-xs"></i>
                                Restocked
                            </span>
                            @if($item->returned_at)
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $item->returned_at->format('d M, h:i A') }}
                                @if($item->returnedBy)
                                by {{ $item->returnedBy->name }}
                                @endif
                            </div>
                            @endif
                            @endif
                        </td>

                        {{-- Action Button (Direct 1-click restock, no supplier selection needed) --}}
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($isPending)
                            <button @click="quickRestock({{ json_encode([
                                        'id' => $item->id,
                                        'product_name' => $product?->name ?? 'Product',
                                        'quantity' => $item->quantity,
                                        'source_name' => $productSupplier ? $productSupplier->name : ($productBranch ? 'Branch: ' . $productBranch->name : 'Stock'),
                                    ]) }})"
                                    :disabled="loadingId === {{ $item->id }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow transition-all active:scale-[0.97] disabled:opacity-50">
                                <i class="fas fa-rotate-left text-[11px]" :class="loadingId === {{ $item->id }} ? 'fa-spin' : ''"></i>
                                <span>Restock</span>
                            </button>
                            @else
                            <div class="inline-flex items-center gap-1 text-emerald-600 font-semibold text-xs py-1">
                                <i class="fas fa-box-open text-emerald-500"></i> Stock Restored
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-rotate-left text-2xl text-slate-300"></i>
                            </div>
                            <p class="font-bold text-slate-600 text-base">No partial return records found</p>
                            <p class="text-xs text-slate-400 mt-1">Items removed during order edits will appear here awaiting restock to inventory.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($returnItems->hasPages())
        <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
            {{ $returnItems->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function partialReturnsPage() {
    return {
        selectedIds: [],
        bulkLoading: false,
        loadingId: null,

        isAllSelected() {
            const pageItemIds = @json($returnItems->where('status', 'pending')->pluck('id')->values());
            return pageItemIds.length > 0 && pageItemIds.every(id => this.selectedIds.includes(id));
        },

        toggleSelectAll(e) {
            const pageItemIds = @json($returnItems->where('status', 'pending')->pluck('id')->values());
            if (e.target.checked) {
                this.selectedIds = [...new Set([...this.selectedIds, ...pageItemIds])];
            } else {
                this.selectedIds = this.selectedIds.filter(id => !pageItemIds.includes(id));
            }
        },

        async quickRestock(item) {
            if (!confirm(`Are you sure you want to restore ${item.quantity} unit(s) of "${item.product_name}" (${item.source_name}) back to inventory stock?`)) {
                return;
            }

            this.loadingId = item.id;
            try {
                const url = '{{ isset($branch) ? route('branch.returns.process', [$branch, ':id']) : route('admin.returns.process', ':id') }}'
                    .replace(':id', item.id);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(data.message || 'Restocked successfully to inventory!', 'success');
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(data.message || 'Failed to restock.', 'error');
                }
            } catch (e) {
                showToast('Network error. Please try again.', 'error');
            }
            this.loadingId = null;
        },

        async submitBulkRestock() {
            if (this.selectedIds.length === 0) return;
            if (!confirm(`Are you sure you want to restock ${this.selectedIds.length} item(s) back to inventory?`)) return;

            this.bulkLoading = true;
            try {
                const url = '{{ isset($branch) ? route('branch.returns.bulk-process', $branch ?? '') : route('admin.returns.bulk-process') }}';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds,
                    }),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(data.message || 'Bulk restock failed.', 'error');
                }
            } catch (e) {
                showToast('Network error. Please try again.', 'error');
            }
            this.bulkLoading = false;
        }
    };
}
</script>
@endpush
