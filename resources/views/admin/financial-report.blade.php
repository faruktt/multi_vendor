@extends('layouts.app')
@section('title','Financial Report')
@section('heading','Financial Report')
@php
    $currency    = $appSettings['currency'] ?? '৳';
    $totalIncome  = collect($branchSummary)->sum('income');
    $totalExpense = collect($branchSummary)->sum('expense');
    $totalNet     = $totalIncome - $totalExpense;
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')

{{-- ══ KPI CARDS ══ --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wide">Total Income</p>
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-arrow-trend-up text-emerald-600 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-emerald-600">{{ $currency }}{{ number_format($totalIncome,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">all branches · all time</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold text-red-500 uppercase tracking-wide">Total Expense</p>
            <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-arrow-trend-down text-red-500 text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-red-500">{{ $currency }}{{ number_format($totalExpense,0) }}</p>
        <p class="text-[11px] text-slate-400 mt-1">total outflows</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[11px] font-semibold {{ $totalNet >= 0 ? 'text-blue-600' : 'text-red-600' }} uppercase tracking-wide">Net P&amp;L</p>
            <div class="w-8 h-8 rounded-xl {{ $totalNet >= 0 ? 'bg-blue-100' : 'bg-red-100' }} flex items-center justify-center">
                <i class="fas fa-scale-balanced {{ $totalNet >= 0 ? 'text-blue-600' : 'text-red-500' }} text-xs"></i>
            </div>
        </div>
        <p class="text-2xl font-bold {{ $totalNet >= 0 ? 'text-blue-600' : 'text-red-500' }}">
            {{ $totalNet >= 0 ? '+' : '-' }}{{ $currency }}{{ number_format(abs($totalNet),0) }}
        </p>
        <p class="text-[11px] text-slate-400 mt-1">{{ $totalNet >= 0 ? 'net profit' : 'net loss' }}</p>
    </div>
</div>

{{-- ══ BRANCH PERFORMANCE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-5">
    <p class="text-[12px] font-bold text-slate-700 mb-4">Branch P&amp;L Breakdown</p>
    <div class="space-y-3">
        @foreach($branchSummary as $idx => $s)
        @php
            $c = $colors[$idx % count($colors)];
            $net = $s['income'] - $s['expense'];
            $pct = $totalIncome > 0 ? round(($s['income'] / $totalIncome) * 100) : 0;
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                    <a href="{{ route('admin.financial-report') }}?vendor_id={{ $s['vendor']->id }}"
                       class="text-[12.5px] font-semibold text-slate-700 hover:text-blue-600 transition-colors">{{ $s['vendor']->name }}</a>
                </div>
                <div class="flex items-center gap-4 text-right">
                    <span class="text-[11.5px] text-emerald-600 font-semibold">
                        +{{ $currency }}{{ number_format($s['income'],0) }}
                    </span>
                    <span class="text-[11.5px] text-red-500 font-semibold">
                        -{{ $currency }}{{ number_format($s['expense'],0) }}
                    </span>
                    <span class="text-[12px] font-bold {{ $net >= 0 ? 'text-blue-600' : 'text-red-600' }} w-28 text-right">
                        {{ $net >= 0 ? '=' : '=' }}{{ $currency }}{{ number_format(abs($net),0) }} {{ $net >= 0 ? 'profit' : 'loss' }}
                    </span>
                    <span class="text-[11px] text-slate-400 w-8">{{ $pct }}%</span>
                </div>
            </div>
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%; background:{{ $c }}"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Note or reference..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <select name="type" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Income &amp; Expense</option>
            <option value="income"  {{ request('type')=='income'  ? 'selected':'' }}>Income Only</option>
            <option value="expense" {{ request('type')=='expense' ? 'selected':'' }}>Expense Only</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="to" value="{{ request('to') }}"
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.financial-report') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <button type="button" onclick="window.print()"
                class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors flex items-center gap-2">
            <i class="fas fa-print text-xs"></i> Print
        </button>
        <span class="ml-auto text-[12px] text-slate-400">{{ $records->total() }} records</span>
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Date</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden md:table-cell">Category</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase">Amount</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Note</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase hidden lg:table-cell">Reference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($records as $r)
                <tr class="hover:bg-slate-50/60 transition-colors {{ $r->type==='income' ? 'border-l-2 border-l-emerald-300' : 'border-l-2 border-l-red-300' }}">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $records->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-[11.5px] text-slate-600">{{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                            {{ $r->type === 'income' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($r->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $r->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 text-[12.5px] hidden md:table-cell">{{ $r->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-[13.5px] {{ $r->type === 'income' ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $r->type === 'income' ? '+' : '-' }}{{ $currency }}{{ number_format($r->amount, 0) }}
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-[12px] hidden lg:table-cell">{{ $r->note ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-400 text-[12px] hidden lg:table-cell font-mono">{{ $r->reference ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-16 text-center">
                        <i class="fas fa-scale-balanced text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No financial records found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($records->count() > 0)
            @php
                $pageIncome  = $records->where('type','income')->sum('amount');
                $pageExpense = $records->where('type','expense')->sum('amount');
            @endphp
            <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-[12px] font-semibold text-slate-500">
                        Page total ({{ $records->count() }} records)
                    </td>
                    <td class="px-4 py-3 text-right">
                        <p class="text-[12px] font-bold text-emerald-600">+{{ $currency }}{{ number_format($pageIncome,0) }}</p>
                        <p class="text-[12px] font-bold text-red-500">-{{ $currency }}{{ number_format($pageExpense,0) }}</p>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>
    @endif
</div>

@endsection
