@extends('layouts.app')
@section('title', $user->name . ' — Commission Report')
@section('heading', 'Commission Report')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2.5 mb-5">
    <a href="{{ route('admin.staff.index') }}"
       class="flex items-center gap-1.5 border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

{{-- ── Staff Card ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-4">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-slate-800">{{ $user->name }}</h2>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-sm text-slate-500">
                <span>{{ $user->email }}</span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-store text-[10px] text-slate-400"></i>
                    {{ $user->vendor?->name ?? '—' }}
                </span>
                <span class="capitalize bg-purple-50 text-purple-700 border border-purple-100 px-2.5 py-0.5 rounded-full text-[11px] font-semibold">
                    {{ str_replace('-', ' ', $user->getRoleNames()->first() ?? 'staff') }}
                </span>
            </div>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-[11px] text-slate-400 font-medium">Commission Rate</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($totals['commission_rate'], 2) }}%</p>
        </div>
    </div>
    @if($totals['commission_rate'] <= 0)
    <div class="mt-4 flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 text-[12.5px] px-3.5 py-2.5 rounded-xl">
        <i class="fas fa-triangle-exclamation text-xs"></i>
        No commission rate set for {{ $user->vendor?->name ?? 'this branch' }} yet — set it in that branch's Settings → Business Information.
    </div>
    @endif
</div>

{{-- ── Stats Cards ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center mb-2">
            <i class="fas fa-receipt text-blue-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($totals['count']) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Sales (all-time)</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center mb-2">
            <i class="fas fa-sack-dollar text-indigo-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($totals['amount'], 0) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total Sales Value</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center mb-2">
            <i class="fas fa-calendar-days text-amber-600 text-xs"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ number_format($totals['month_count']) }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Sales This Month ({{ $currency }}{{ number_format($totals['month_amount'], 0) }})</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-4 text-white shadow-md shadow-emerald-200/40">
        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center mb-2">
            <i class="fas fa-hand-holding-dollar text-white text-xs"></i>
        </div>
        <p class="text-xl font-bold">{{ $currency }}{{ number_format($totals['commission_total'], 0) }}</p>
        <p class="text-[11px] text-emerald-100 font-medium mt-0.5">Commission Earned (all-time)</p>
        <p class="text-[10.5px] text-emerald-200 mt-1">{{ $currency }}{{ number_format($totals['commission_month'], 0) }} this month</p>
    </div>
</div>

{{-- ── Monthly Breakdown ────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="flex items-center gap-2.5 px-5 py-3.5 border-b border-slate-100">
        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
            <i class="fas fa-chart-line text-slate-500 text-xs"></i>
        </div>
        <h3 class="font-bold text-slate-700 text-sm">Last 6 Months</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Month</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Sales</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Value</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Commission</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @foreach($monthly as $m)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3 font-medium text-slate-700">{{ $m['label'] }}</td>
                <td class="px-3 py-3 text-center text-slate-600">{{ number_format($m['count']) }}</td>
                <td class="px-3 py-3 text-right text-slate-700">{{ $currency }}{{ number_format($m['amount'], 0) }}</td>
                <td class="px-3 py-3 text-right font-bold text-emerald-600">{{ $currency }}{{ number_format($m['commission'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
