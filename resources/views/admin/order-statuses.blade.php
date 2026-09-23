@extends('layouts.app')
@section('title', 'Order Statuses')
@section('heading', 'Order Statuses')

@section('content')

<div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-2xl p-5 mb-5 text-white">
    <div class="flex items-start gap-4">
        <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-truck-fast text-white text-lg"></i>
        </div>
        <div>
            <h2 class="font-bold text-[15px]">Order Status Options</h2>
            <p class="text-slate-300 text-[12.5px] mt-1">These are the statuses shown on the Sales list/detail pages. Reorder with the arrows, add your own, or edit the label/color. "Protected" ones are relied on elsewhere in the system and can't be deleted.</p>
        </div>
    </div>
</div>

{{-- ── Add New ── --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-5">
    <h3 class="text-[13.5px] font-semibold text-slate-700 mb-3">Add New Status</h3>
    <form method="POST" action="{{ route('admin.order-statuses.store') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div class="flex-1 min-w-[180px]">
            <label class="block text-[11.5px] font-medium text-slate-500 mb-1.5">Label</label>
            <input type="text" name="label" value="{{ old('label') }}" placeholder="e.g. Return Requested" required
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <div>
            <label class="block text-[11.5px] font-medium text-slate-500 mb-1.5">Color</label>
            <select name="color" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @foreach($colors as $c)
                <option value="{{ $c }}" {{ old('color') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            <i class="fas fa-plus text-xs mr-1"></i> Add
        </button>
    </form>
    @error('label')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
</div>

{{-- ── List ── --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
            <i class="fas fa-list text-slate-500 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-semibold text-slate-800">Statuses</h2>
            <p class="text-[11.5px] text-slate-400">{{ $orderStatuses->count() }} {{ Str::plural('status', $orderStatuses->count()) }} — use the arrows to reorder</p>
        </div>
    </div>

    <div class="divide-y divide-slate-100">
        @foreach($orderStatuses as $status)
        <div class="flex items-center gap-4 px-6 py-4">
            <div class="flex flex-col gap-1 flex-shrink-0">
                <button type="button" onclick="moveStatus({{ $status->id }}, -1)"
                        class="w-6 h-6 rounded-md border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}">
                    <i class="fas fa-chevron-up text-[10px]"></i>
                </button>
                <button type="button" onclick="moveStatus({{ $status->id }}, 1)"
                        class="w-6 h-6 rounded-md border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
            </div>

            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-{{ $status->color }}-100 text-{{ $status->color }}-700 flex-shrink-0 w-36 text-center">
                {{ $status->label }}
            </span>

            <form method="POST" action="{{ route('admin.order-statuses.update', $status) }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                @csrf @method('PUT')
                <input type="text" name="label" value="{{ old('label', $status->label) }}" placeholder="Label"
                       class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                <div class="flex gap-2">
                    <select name="color" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($colors as $c)
                        <option value="{{ $c }}" {{ $status->color === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="flex-shrink-0 text-[11.5px] font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                        Save
                    </button>
                </div>
            </form>

            @if($status->is_protected)
            <span class="text-[11px] font-semibold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 flex-shrink-0">
                <i class="fas fa-lock text-[10px] mr-1"></i> Protected
            </span>
            @else
            <form method="POST" action="{{ route('admin.order-statuses.destroy', $status) }}"
                  onsubmit="return confirm('Delete this status? Orders currently using it will block this.');">
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

<form id="reorder-form" method="POST" action="{{ route('admin.order-statuses.reorder') }}" class="hidden">
    @csrf
    <div id="reorder-inputs"></div>
</form>

@push('scripts')
<script>
let statusOrder = @json($orderStatuses->pluck('id'));

function moveStatus(id, dir) {
    const order = [...statusOrder];
    const from = order.indexOf(id);
    const to = from + dir;
    if (to < 0 || to >= order.length) return;
    [order[from], order[to]] = [order[to], order[from]];

    const container = document.getElementById('reorder-inputs');
    container.innerHTML = '';
    order.forEach(function (statusId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order[]';
        input.value = statusId;
        container.appendChild(input);
    });
    document.getElementById('reorder-form').submit();
}
</script>
@endpush

@endsection
