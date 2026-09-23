@extends('layouts.app')
@section('title', 'Supplier Products Management')
@section('heading', 'Supplier Products Management')

@section('content')
<div class="py-4 space-y-6" x-data="{
    rejectModalOpen: false,
    rejectProductId: null,
    rejectProductName: '',
    rejectionReason: '',

    openReject(id, name, reason = '') {
        this.rejectProductId = id;
        this.rejectProductName = name;
        this.rejectionReason = reason;
        this.rejectModalOpen = true;
    }
}">

    {{-- Status Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.supplier-products.index', array_merge(request()->except('tab', 'page'), ['tab' => 'all'])) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-boxes-stacked mr-1.5"></i> All Products ({{ $totalCount }})
        </a>

        <a href="{{ route('admin.supplier-products.index', array_merge(request()->except('tab', 'page'), ['tab' => 'pending'])) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ $tab === 'pending' ? 'bg-amber-500 text-white shadow-sm shadow-amber-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-clock mr-1.5"></i> Pending Review
            @if($pendingCount > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-900 font-black animate-pulse">{{ $pendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.supplier-products.index', array_merge(request()->except('tab', 'page'), ['tab' => 'approved'])) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ $tab === 'approved' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-circle-check mr-1.5"></i> Approved &amp; Live ({{ $approvedCount }})
        </a>

        <a href="{{ route('admin.supplier-products.index', array_merge(request()->except('tab', 'page'), ['tab' => 'rejected'])) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ $tab === 'rejected' ? 'bg-rose-600 text-white shadow-sm shadow-rose-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-ban mr-1.5"></i> Rejected ({{ $rejectedCount }})
        </a>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Products</div>
                <div class="text-xl font-black text-slate-800">{{ $totalCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border {{ $pendingCount > 0 ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200/80' }} p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $pendingCount > 0 ? 'bg-amber-500 text-white animate-pulse' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider {{ $pendingCount > 0 ? 'text-amber-700' : 'text-slate-400' }}">Pending Approval</div>
                <div class="text-xl font-black {{ $pendingCount > 0 ? 'text-amber-700' : 'text-slate-800' }}">{{ $pendingCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200/80 p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Approved &amp; Live</div>
                <div class="text-xl font-black text-emerald-800">{{ $approvedCount }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-rose-200/80 p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg flex-shrink-0">
                <i class="fas fa-circle-xmark"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Rejected</div>
                <div class="text-xl font-black text-rose-800">{{ $rejectedCount }}</div>
            </div>
        </div>
    </div>

    {{-- Filter / Search Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.supplier-products.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            {{-- Supplier Filter --}}
            <div class="min-w-[220px]">
                <select name="supplier_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white font-medium text-slate-700">
                    <option value="">All Suppliers / Vendors</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ (string)$supplierId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->company_name ?: $s->name }} (Def. Comm: {{ $s->commission_percentage ?? 5 }}%)
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Keyword Search --}}
            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search product title, SKU, or supplier..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                Filter Products
            </button>

            @if(request()->anyFilled(['search', 'supplier_id']) || $tab !== 'all')
            <a href="{{ route('admin.supplier-products.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Product Table with Inline Commission Setting & Instant Live Calculation --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Product</th>
                        <th class="px-4 py-3.5 font-semibold">Supplier</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Price (৳)</th>
                        <th class="px-3 py-3.5 font-semibold text-center">Stock</th>
                        <th class="px-4 py-3.5 font-semibold text-center min-w-[190px]">Admin Commission (%)</th>
                        <th class="px-4 py-3.5 font-semibold text-right min-w-[120px]">Supplier Net (৳)</th>
                        <th class="px-3 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right min-w-[160px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($products as $product)
                    @php
                        $initialRate = (float) ($product->admin_commission_rate !== null 
                            ? $product->admin_commission_rate 
                            : ($product->supplier?->commission_percentage ?? 5.0));
                        $price = (float) $product->price;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ $product->isPendingApproval() ? 'bg-amber-50/20' : '' }}"
                        x-data="{
                            price: {{ $price }},
                            rate: {{ $initialRate }},
                            get adminProfit() {
                                return ((this.price * (parseFloat(this.rate) || 0)) / 100).toFixed(2);
                            },
                            get supplierNet() {
                                return Math.max(0, this.price - parseFloat(this.adminProfit)).toFixed(2);
                            }
                        }">

                        {{-- Product Info --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-slate-400 relative">
                                    @if($product->first_image_url)
                                        <img src="{{ $product->first_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-image text-slate-300 text-lg"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 max-w-[230px]">
                                    <div class="font-bold text-slate-900 text-sm truncate" title="{{ $product->name }}">
                                        {{ $product->name }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                        <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ $product->sku ?: 'NO-SKU' }}</span>
                                        @if($product->category)
                                            <span class="truncate">{{ $product->category->name }}</span>
                                        @endif
                                    </div>
                                    @if($product->isApproved() && $product->status === 'active')
                                        <a href="{{ route('shop.products.show', $product->slug) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-[10px] text-emerald-600 hover:text-emerald-700 font-semibold mt-0.5">
                                            <span>View on website</span>
                                            <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Supplier Info --}}
                        <td class="px-4 py-3.5">
                            @if($product->supplier)
                                <div class="font-bold text-slate-900">{{ $product->supplier->company_name ?: $product->supplier->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $product->supplier->phone ?: 'No phone' }}</div>
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-600">
                                    Def. Comm: {{ $product->supplier->commission_percentage ?? 5 }}%
                                </span>
                            @else
                                <span class="text-slate-400 italic">No supplier</span>
                            @endif
                        </td>

                        {{-- Pricing --}}
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="font-black text-slate-900 text-sm">৳{{ number_format($product->price, 2) }}</div>
                            @if($product->old_price && $product->old_price > $product->price)
                                <div class="text-[11px] text-slate-400 line-through">৳{{ number_format($product->old_price, 2) }}</div>
                            @endif
                        </td>

                        {{-- Stock --}}
                        <td class="px-3 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $product->stock_qty > 5 ? 'bg-slate-100 text-slate-700' : ($product->stock_qty > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700') }}">
                                {{ $product->stock_qty }} {{ $product->unit ?? 'pcs' }}
                            </span>
                            @if($product->variants && $product->variants->count() > 0)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $product->variants->count() }} variants</div>
                            @endif
                        </td>

                        {{-- Admin Commission Rate (Editable Inline with Instant ৳ Preview) --}}
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-1">
                                <div class="relative inline-block w-24">
                                    <input type="number" step="0.1" min="0" max="100"
                                           x-model="rate"
                                           placeholder="Commission %"
                                           title="Set product-specific admin commission percentage"
                                           class="w-full text-center px-2 py-1 pr-6 text-xs font-black rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white shadow-inner">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 pointer-events-none">%</span>
                                </div>
                                <div class="text-[10.5px] font-bold text-emerald-700">
                                    Admin: <span class="font-black">+৳<span x-text="adminProfit"></span></span>
                                </div>
                            </div>
                        </td>

                        {{-- Supplier Earnings --}}
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="font-black text-slate-900 text-sm">৳<span x-text="supplierNet"></span></div>
                            <div class="text-[10px] text-slate-400">per unit sold</div>
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-3 py-3.5 text-center whitespace-nowrap">
                            @if($product->isPendingApproval())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                    <span>Pending</span>
                                </span>
                            @elseif($product->isApproved())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fas fa-check text-[9px]"></i>
                                    <span>Approved</span>
                                </span>
                                @if($product->approved_at)
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $product->approved_at->format('d M, Y') }}</div>
                                @endif
                            @elseif($product->isRejected())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                    <i class="fas fa-circle-xmark text-[9px]"></i>
                                    <span>Rejected</span>
                                </span>
                                @if($product->rejection_reason)
                                    <div class="text-[10px] text-rose-600 max-w-[140px] truncate mx-auto mt-0.5" title="{{ $product->rejection_reason }}">
                                        {{ $product->rejection_reason }}
                                    </div>
                                @endif
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                @if($product->isPendingApproval())
                                    {{-- Direct One-Click Approve with Inline Commission --}}
                                    <form method="POST" action="{{ route('admin.supplier-products.approve', $product->id) }}" class="inline-block"
                                          onsubmit="return confirm('Approve this product with ' + this.admin_commission_rate.value + '% admin commission? It will be published live on the website.');">
                                        @csrf
                                        <input type="hidden" name="admin_commission_rate" :value="rate">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-sm shadow-emerald-300 transition">
                                            <i class="fas fa-circle-check text-[11px]"></i>
                                            <span>Approve</span>
                                        </button>
                                    </form>

                                    {{-- Reject Button --}}
                                    <button type="button"
                                            @click="openReject({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->rejection_reason ?? '') }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition"
                                            title="Reject this product">
                                        <i class="fas fa-xmark"></i>
                                        <span>Reject</span>
                                    </button>
                                @elseif($product->isApproved())
                                    {{-- Update Commission on Live Product --}}
                                    <form method="POST" action="{{ route('admin.supplier-products.commission.update', $product->id) }}" class="inline-block"
                                          onsubmit="return confirm('Update admin commission to ' + this.admin_commission_rate.value + '% for this product?');">
                                        @csrf
                                        <input type="hidden" name="admin_commission_rate" :value="rate">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 font-bold text-xs border border-slate-300 transition"
                                                title="Save updated commission rate">
                                            <i class="fas fa-save text-[10px]"></i>
                                            <span>Save %</span>
                                        </button>
                                    </form>

                                    {{-- Reject / Suspend Button --}}
                                    <button type="button"
                                            @click="openReject({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->rejection_reason ?? '') }}')"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition"
                                            title="Suspend / Reject Product">
                                        <i class="fas fa-ban text-[10px]"></i>
                                    </button>
                                @else
                                    {{-- Re-approve --}}
                                    <form method="POST" action="{{ route('admin.supplier-products.approve', $product->id) }}" class="inline-block"
                                          onsubmit="return confirm('Re-approve this product with ' + this.admin_commission_rate.value + '% admin commission?');">
                                        @csrf
                                        <input type="hidden" name="admin_commission_rate" :value="rate">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm transition">
                                            <i class="fas fa-rotate-left text-[10px]"></i>
                                            <span>Re-approve</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto flex items-center justify-center text-2xl text-slate-300 mb-3">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">No supplier products found</div>
                            <p class="text-xs text-slate-400 mt-1">There are no products matching this filter criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- REJECT MODAL --}}
    <div x-cloak x-show="rejectModalOpen"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden"
             @click.away="rejectModalOpen = false">
            
            {{-- Modal Header --}}
            <div class="p-6 bg-gradient-to-r from-rose-600 to-red-700 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-ban text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base">Reject Supplier Product</h3>
                            <p class="text-xs text-rose-100 truncate max-w-[280px]" x-text="rejectProductName"></p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModalOpen = false" class="text-white/70 hover:text-white transition">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body Form --}}
            <form :action="'{{ url('admin/supplier-products') }}/' + rejectProductId + '/reject'" method="POST" class="p-6 space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">
                        Reason for Rejection <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="rejection_reason"
                              x-model="rejectionReason"
                              rows="3"
                              required
                              placeholder="e.g., Image quality is blurry, price does not match market value, or incorrect category selected..."
                              class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition-all"></textarea>
                    <p class="text-[11px] text-slate-400">The supplier will see this reason in their portal so they can fix and re-submit.</p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="rejectModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md shadow-rose-200 transition flex items-center gap-1.5">
                        <i class="fas fa-ban"></i>
                        <span>Reject Product</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
