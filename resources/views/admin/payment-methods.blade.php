@extends('layouts.app')
@section('title', 'Payment Methods')
@section('heading', 'Payment Methods')

@section('content')

<div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-2xl p-5 mb-5 text-white">
    <div class="flex items-start gap-4">
        <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-money-bill-wave text-white text-lg"></i>
        </div>
        <div>
            <h2 class="font-bold text-[15px]">Payment Method Options</h2>
            <p class="text-slate-300 text-[12.5px] mt-1">These options appear everywhere a payment or refund method is picked — POS checkout, Sales, Purchases, and returns. Reorder with the arrows, add your own, or deactivate one without deleting it. "Protected" ones are relied on elsewhere in the system and can't be deleted.</p>
        </div>
    </div>
</div>

{{-- ── Add New ── --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-5">
    <h3 class="text-[13.5px] font-semibold text-slate-700 mb-3">Add New Payment Method</h3>
    <form method="POST" action="{{ route('admin.payment-methods.store') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div class="flex-1 min-w-[180px]">
            <label class="block text-[11.5px] font-medium text-slate-500 mb-1.5">Label</label>
            <input type="text" name="label" value="{{ old('label') }}" placeholder="e.g. Rocket, Upay" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <div class="flex-[2] min-w-[220px]">
            <label class="block text-[11.5px] font-medium text-slate-500 mb-1.5">Payment Details <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="text" name="details" value="{{ old('details') }}" placeholder="e.g. bKash number 01712345678, or bank account details"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            <i class="fas fa-plus text-xs mr-1"></i> Add
        </button>
    </form>
    @error('label')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
    @error('details')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
</div>

{{-- ── List ── --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
            <i class="fas fa-list text-slate-500 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-semibold text-slate-800">Methods</h2>
            <p class="text-[11.5px] text-slate-400">{{ $paymentMethods->count() }} {{ Str::plural('method', $paymentMethods->count()) }} — use the arrows to reorder</p>
        </div>
    </div>

    <div class="divide-y divide-slate-100">
        @foreach($paymentMethods as $method)
        <div class="flex items-center gap-4 px-6 py-4 {{ !$method->is_active ? 'opacity-50' : '' }}">
            <div class="flex flex-col gap-1 flex-shrink-0">
                <button type="button" onclick="moveMethod({{ $method->id }}, -1)"
                        class="w-6 h-6 rounded-md border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}">
                    <i class="fas fa-chevron-up text-[10px]"></i>
                </button>
                <button type="button" onclick="moveMethod({{ $method->id }}, 1)"
                        class="w-6 h-6 rounded-md border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
            </div>

            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 flex-shrink-0 w-36 text-center truncate">
                {{ $method->label }}
            </span>

            <form method="POST" action="{{ route('admin.payment-methods.update', $method) }}" class="flex-1 flex flex-col sm:flex-row gap-2">
                @csrf @method('PUT')
                <input type="text" name="label" value="{{ old('label', $method->label) }}" placeholder="Label"
                       class="w-full sm:w-1/3 border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                <input type="text" name="details" value="{{ old('details', $method->details) }}" placeholder="Payment details (e.g. bKash/bank number)"
                       class="w-full flex-1 border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                <button type="submit" class="flex-shrink-0 text-[11.5px] font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                    Save
                </button>
            </form>

            <form method="POST" action="{{ route('admin.payment-methods.toggle', $method) }}" class="flex-shrink-0">
                @csrf
                <button type="submit"
                        title="{{ $method->is_active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}"
                        class="text-[11px] font-semibold px-3 py-1.5 rounded-lg transition-colors {{ $method->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                    <i class="fas {{ $method->is_active ? 'fa-eye' : 'fa-eye-slash' }} text-[10px] mr-1"></i>
                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                </button>
            </form>

            @if($method->is_protected)
            <span class="text-[11px] font-semibold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 flex-shrink-0">
                <i class="fas fa-lock text-[10px] mr-1"></i> Protected
            </span>
            @else
            <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}"
                  onsubmit="return confirm('Delete this payment method? Records currently using it will block this.');">
                @csrf @method('DELETE')
                <button type="submit" class="w-9 h-9 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-colors flex-shrink-0">
                    <i class="fas fa-trash text-[13px]"></i>
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
</div>

<form id="reorder-form" method="POST" action="{{ route('admin.payment-methods.reorder') }}" class="hidden">
    @csrf
    <div id="reorder-inputs"></div>
</form>

@push('scripts')
<script>
let methodOrder = @json($paymentMethods->pluck('id'));

function moveMethod(id, dir) {
    const order = [...methodOrder];
    const from = order.indexOf(id);
    const to = from + dir;
    if (to < 0 || to >= order.length) return;
    [order[from], order[to]] = [order[to], order[from]];

    const container = document.getElementById('reorder-inputs');
    container.innerHTML = '';
    order.forEach(function (methodId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order[]';
        input.value = methodId;
        container.appendChild(input);
    });
    document.getElementById('reorder-form').submit();
}
</script>
@endpush

@endsection
