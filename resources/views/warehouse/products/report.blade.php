@extends('layouts.app')
@section('title', $product->name . ' — Stock Report')
@section('heading', 'Product Stock Report')

@push('styles')
<style>
@media print {
    @page { size: A4; margin: 15mm; }
    body * { visibility: hidden !important; }
    #print-area, #print-area * { visibility: visible !important; }
    #print-area { position: absolute !important; top: 0; left: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
@endpush

@section('content')

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2.5 mb-5 no-print">
    <a href="{{ route('admin.warehouse.products.index') }}"
       class="flex items-center gap-1.5 border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
    <button onclick="window.print()"
            class="flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
        <i class="fas fa-print text-xs"></i> Print Report
    </button>
</div>

<div id="print-area">

{{-- ── Product Card ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
    <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0 flex items-center justify-center border border-slate-100"
             style="background: linear-gradient(135deg,#f0f4ff,#e8f0fe)">
            @if(isset($product->image_url) || $product->image)
                <img src="{{ $product->image_url ?? \Illuminate\Support\Facades\Storage::disk('uploads')->url($product->image) }}"
                     alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <i class="fas fa-box text-blue-300 text-xl"></i>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-slate-800">{{ $product->name }}</h2>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-sm text-slate-500">
                @if($product->sku)
                <span class="font-mono text-[12px]">{{ $product->sku }}</span>
                @endif
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-warehouse text-[10px] text-orange-400"></i>
                    Originally entered at <span class="font-semibold text-slate-700">{{ $product->vendor->name ?? '—' }}</span>
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ── Stats Cards ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-truck text-blue-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($totals['purchased']) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Purchased</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-right-left text-amber-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($totals['transferred']) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Transferred</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-cash-register text-emerald-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($totals['sold']) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Sold (all branches)</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
        <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-boxes-stacked text-indigo-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($totals['current_total']) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">In Stock (everywhere)</p>
    </div>
</div>

{{-- ── Current Stock by Location ──────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-4">
    <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-slate-100">
        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
            <i class="fas fa-map-marker-alt text-slate-500 text-xs"></i>
        </div>
        <h3 class="font-bold text-slate-700 text-sm">Current Stock by Location</h3>
    </div>
    <div class="divide-y divide-slate-50">
        @foreach($locations as $loc)
        <div class="px-5 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                @if($loc->vendor?->is_warehouse)
                <i class="fas fa-warehouse text-orange-400 text-xs"></i>
                @else
                <i class="fas fa-store text-slate-400 text-xs"></i>
                @endif
                <span class="text-sm font-medium text-slate-700">{{ $loc->vendor->name ?? '—' }}</span>
                @if($loc->id === $product->id)
                <span class="text-[10px] font-semibold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full">origin</span>
                @endif
            </div>
            <span class="text-sm font-bold text-slate-800">{{ number_format($loc->stock_qty) }} {{ $loc->unit }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Movement History ──────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-slate-100">
        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
            <i class="fas fa-clock-rotate-left text-blue-600 text-xs"></i>
        </div>
        <h3 class="font-bold text-slate-700 text-sm">Stock Movement History</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Location</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Reference</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Quantity</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($movements as $m)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3">
                    <p class="text-[12.5px] text-slate-700 font-medium">{{ $m->created_at->format('d M Y') }}</p>
                    <p class="text-[10.5px] text-slate-400">{{ $m->created_at->format('h:i A') }}</p>
                </td>
                <td class="px-3 py-3">
                    <span class="text-[12.5px] text-slate-600">{{ $m->vendor->name ?? '—' }}</span>
                </td>
                <td class="px-3 py-3 text-center">
                    @if($m->type === 'in')
                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-lg text-[10.5px] font-semibold">
                        <i class="fas fa-arrow-up text-[9px]"></i> IN
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 bg-red-100 text-red-600 border border-red-200 px-2 py-0.5 rounded-lg text-[10.5px] font-semibold">
                        <i class="fas fa-arrow-down text-[9px]"></i> OUT
                    </span>
                    @endif
                </td>
                <td class="px-3 py-3">
                    @php
                        $refStyles = [
                            'purchase' => 'bg-blue-50 text-blue-700',
                            'transfer' => 'bg-amber-50 text-amber-700',
                            'sale'     => 'bg-purple-50 text-purple-700',
                        ];
                        $refStyle = $refStyles[$m->reference_type ?? ''] ?? 'bg-slate-50 text-slate-600';
                    @endphp
                    <span class="text-[10.5px] font-medium px-1.5 py-0.5 rounded-md {{ $refStyle }}">
                        {{ $m->reference_type ? ucfirst($m->reference_type) : 'Adjustment' }}
                    </span>
                    @if($m->note)
                    <p class="text-[11px] text-slate-400 mt-1">{{ $m->note }}</p>
                    @endif
                </td>
                <td class="px-3 py-3 text-right">
                    <span class="font-bold text-[13px] {{ $m->type === 'in' ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $m->type === 'in' ? '+' : '-' }}{{ number_format($m->quantity) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-16 text-center text-slate-400">
                    <i class="fas fa-clock-rotate-left text-3xl text-slate-300 mb-3 block"></i>
                    <p>No stock movements recorded yet</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($movements->hasPages())
    <div class="px-4 py-3 border-t border-slate-100 no-print">{{ $movements->links() }}</div>
    @endif
</div>

</div>{{-- #print-area --}}
@endsection
