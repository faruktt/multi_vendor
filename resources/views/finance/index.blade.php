@extends('layouts.app')
@section('title', 'Finance')
@section('heading', 'Income & Expense Tracker')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')
<div x-data="financePage()">

{{-- ══ STATS ════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-4">

    {{-- All-time Income --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-arrow-up text-emerald-600 text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-slate-400 font-medium">Total Income</p>
                <p class="text-xl font-bold text-emerald-600 truncate">{{ $currency }}{{ number_format($totalIncome, 0) }}</p>
            </div>
        </div>
    </div>

    {{-- All-time Expense --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-arrow-down text-red-500 text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-slate-400 font-medium">Total Expense</p>
                <p class="text-xl font-bold text-red-500 truncate">{{ $currency }}{{ number_format($totalExpense, 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Net Balance --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 col-span-2 lg:col-span-1">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $netBalance >= 0 ? 'bg-blue-100' : 'bg-orange-100' }}">
                <i class="fas fa-scale-balanced text-sm {{ $netBalance >= 0 ? 'text-blue-600' : 'text-orange-500' }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] text-slate-400 font-medium">Net Balance</p>
                <p class="text-xl font-bold truncate {{ $netBalance >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
                    {{ $netBalance >= 0 ? '+' : '' }}{{ $currency }}{{ number_format($netBalance, 0) }}
                </p>
            </div>
        </div>
    </div>

    {{-- This Month Income --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-calendar-check text-emerald-600 text-xs"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10.5px] text-slate-400 font-medium">This Month Income</p>
                    <p class="text-lg font-bold text-emerald-600 truncate">{{ $currency }}{{ number_format($thisMonthIncome, 0) }}</p>
                </div>
            </div>
            @if($lastMonthIncome > 0)
            @php $pct = round((($thisMonthIncome - $lastMonthIncome) / $lastMonthIncome) * 100); @endphp
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex-shrink-0 {{ $pct >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $pct >= 0 ? '+' : '' }}{{ $pct }}%
            </span>
            @endif
        </div>
    </div>

    {{-- This Month Expense --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-red-50 border border-red-200 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-calendar-xmark text-red-500 text-xs"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10.5px] text-slate-400 font-medium">This Month Expense</p>
                    <p class="text-lg font-bold text-red-500 truncate">{{ $currency }}{{ number_format($thisMonthExpense, 0) }}</p>
                </div>
            </div>
            @if($lastMonthExpense > 0)
            @php $epct = round((($thisMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100); @endphp
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex-shrink-0 {{ $epct <= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $epct >= 0 ? '+' : '' }}{{ $epct }}%
            </span>
            @endif
        </div>
    </div>

    {{-- This Month Net --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 col-span-2 lg:col-span-1">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $thisMonthNet >= 0 ? 'bg-blue-50 border border-blue-200' : 'bg-orange-50 border border-orange-200' }}">
                <i class="fas fa-coins text-xs {{ $thisMonthNet >= 0 ? 'text-blue-600' : 'text-orange-500' }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10.5px] text-slate-400 font-medium">This Month Net</p>
                <p class="text-lg font-bold truncate {{ $thisMonthNet >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
                    {{ $thisMonthNet >= 0 ? '+' : '' }}{{ $currency }}{{ number_format($thisMonthNet, 0) }}
                </p>
            </div>
        </div>
    </div>

</div>

{{-- ══ BODY: Split Layout ═══════════════════════════════════════════ --}}
<div class="flex gap-4 items-start">

    {{-- ════ LEFT: Transactions (col-7) ════ --}}
    <div class="flex-[7] min-w-0">

        {{-- Filter bar --}}
        <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-3 flex flex-wrap gap-2.5 items-center">
            <select name="type"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600">
                <option value="">All Types</option>
                <option value="income"  {{ request('type')==='income'  ? 'selected':'' }}>↑ Income</option>
                <option value="expense" {{ request('type')==='expense' ? 'selected':'' }}>↓ Expense</option>
            </select>
            <select name="category_id"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id ? 'selected':'' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
            <input type="month" name="month" value="{{ request('month') }}"
                   class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="relative flex-1 min-w-[140px]">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Note / reference..."
                       class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                <i class="fas fa-filter text-xs mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['type','category_id','month','search']))
            <a href="{{ route('branch.finance.index', $branch) }}"
               class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50">Reset</a>
            @endif
            <span class="text-[11.5px] text-slate-400">{{ $transactions->total() }} entries</span>
        </form>

        {{-- Transactions table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                        <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Category</th>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Note / Ref</th>
                        <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                        <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-3 py-3 text-slate-400 text-[11px]">{{ $transactions->firstItem() + $loop->index }}</td>
                        <td class="px-3 py-3">
                            <p class="text-[12.5px] font-semibold text-slate-700">{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</p>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($tx->type === 'income')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <i class="fas fa-arrow-up text-[9px]"></i> Income
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-red-100 text-red-600 border border-red-200">
                                <i class="fas fa-arrow-down text-[9px]"></i> Expense
                            </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 hidden md:table-cell">
                            @if($tx->category)
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $tx->category->color }}"></span>
                                <span class="text-[12px] text-slate-600">{{ $tx->category->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 hidden lg:table-cell">
                            <div class="max-w-[200px]">
                                @if($tx->note)
                                <p class="text-[12px] text-slate-600 truncate">{{ $tx->note }}</p>
                                @endif
                                @if($tx->reference)
                                <p class="text-[10.5px] text-slate-400 font-mono truncate">{{ $tx->reference }}</p>
                                @endif
                                @if(!$tx->note && !$tx->reference)
                                <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <p class="font-bold text-[14px] {{ $tx->type === 'income' ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }}{{ $currency }}{{ number_format($tx->amount, 0) }}
                            </p>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <button type="button"
                                    @click="openDelete({{ $tx->id }}, '{{ route('branch.finance.destroy', [$branch, $tx]) }}')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-colors mx-auto">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-14 text-center text-slate-400">
                            <i class="fas fa-receipt text-3xl text-slate-300 mb-3 block"></i>
                            <p>No transactions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($transactions->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 text-sm">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>{{-- /left --}}

    {{-- ════ RIGHT: Add + Categories (col-5) ════ --}}
    <div class="w-[340px] flex-shrink-0 space-y-3">

        {{-- Add Transaction --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-plus text-blue-600 text-xs"></i>
                </div>
                <p class="font-bold text-slate-700 text-sm">New Transaction</p>
            </div>
            <form method="POST" action="{{ route('branch.finance.store', $branch) }}" class="p-4 space-y-2.5">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="txType = 'income'"
                            :class="txType === 'income' ? 'bg-emerald-500 text-white border-emerald-500' : 'border-slate-200 text-slate-500 hover:border-emerald-400 hover:text-emerald-600'"
                            class="py-2 rounded-xl text-xs font-bold border-2 transition-all flex items-center justify-center gap-1.5">
                        <i class="fas fa-arrow-up text-[10px]"></i> Income
                    </button>
                    <button type="button" @click="txType = 'expense'"
                            :class="txType === 'expense' ? 'bg-red-500 text-white border-red-500' : 'border-slate-200 text-slate-500 hover:border-red-400 hover:text-red-500'"
                            class="py-2 rounded-xl text-xs font-bold border-2 transition-all flex items-center justify-center gap-1.5">
                        <i class="fas fa-arrow-down text-[10px]"></i> Expense
                    </button>
                </div>
                <input type="hidden" name="type" :value="txType">
                <select name="category_id"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600">
                    <option value="">Category (optional)</option>
                    @foreach($categories->where('type', 'income') as $cat)
                    <option value="{{ $cat->id }}" x-show="txType === 'income'">[Income] {{ $cat->name }}</option>
                    @endforeach
                    @foreach($categories->where('type', 'expense') as $cat)
                    <option value="{{ $cat->id }}" x-show="txType === 'expense'">[Expense] {{ $cat->name }}</option>
                    @endforeach
                </select>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">{{ $currency }}</span>
                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00"
                           class="w-full border border-slate-200 rounded-xl pl-7 pr-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="reference" placeholder="Reference (optional)"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                <textarea name="note" rows="2" placeholder="Note (optional)"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-colors"
                        :class="txType === 'expense' ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-500 hover:bg-emerald-600'">
                    <i class="fas fa-plus text-xs mr-1"></i>
                    <span x-text="txType === 'expense' ? 'Add Expense' : 'Add Income'"></span>
                </button>
            </form>
        </div>

        {{-- Income Categories --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="fas fa-tags text-emerald-600 text-xs"></i>
                    </div>
                    <p class="font-bold text-slate-700 text-sm">Income Categories</p>
                </div>
                <button @click="catType = 'income'; showCat = true"
                        class="w-6 h-6 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 flex items-center justify-center transition-colors" title="Add">
                    <i class="fas fa-plus text-[10px]"></i>
                </button>
            </div>
            @php $incomeCats = $categories->where('type', 'income'); @endphp
            @if($incomeCats->count())
            <div class="divide-y divide-slate-50">
                @foreach($incomeCats as $cat)
                <div class="px-4 py-2.5 flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $cat->color }}"></span>
                    <p class="flex-1 text-[12.5px] font-medium text-slate-700 truncate">{{ $cat->name }}</p>
                    <p class="text-[11.5px] font-bold text-emerald-600 flex-shrink-0">
                        {{ $currency }}{{ number_format($cat->transactions_sum_amount ?? 0, 0) }}
                    </p>
                    <span class="text-[10px] text-slate-400 flex-shrink-0">({{ $cat->transactions_count }})</span>
                    <button type="button"
                            @click="openDeleteCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ route('branch.finance.categories.destroy', [$branch, $cat]) }}')"
                            class="w-5 h-5 rounded-md flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0">
                        <i class="fas fa-times text-[9px]"></i>
                    </button>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-4 py-5 text-center text-slate-400 text-xs">No categories yet</div>
            @endif
        </div>

        {{-- Expense Categories --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
                        <i class="fas fa-tags text-red-500 text-xs"></i>
                    </div>
                    <p class="font-bold text-slate-700 text-sm">Expense Categories</p>
                </div>
                <button @click="catType = 'expense'; showCat = true"
                        class="w-6 h-6 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors" title="Add">
                    <i class="fas fa-plus text-[10px]"></i>
                </button>
            </div>
            @php $expenseCats = $categories->where('type', 'expense'); @endphp
            @if($expenseCats->count())
            <div class="divide-y divide-slate-50">
                @foreach($expenseCats as $cat)
                <div class="px-4 py-2.5 flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $cat->color }}"></span>
                    <p class="flex-1 text-[12.5px] font-medium text-slate-700 truncate">{{ $cat->name }}</p>
                    <p class="text-[11.5px] font-bold text-red-500 flex-shrink-0">
                        {{ $currency }}{{ number_format($cat->transactions_sum_amount ?? 0, 0) }}
                    </p>
                    <span class="text-[10px] text-slate-400 flex-shrink-0">({{ $cat->transactions_count }})</span>
                    <button type="button"
                            @click="openDeleteCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ route('branch.finance.categories.destroy', [$branch, $cat]) }}')"
                            class="w-5 h-5 rounded-md flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0">
                        <i class="fas fa-times text-[9px]"></i>
                    </button>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-4 py-5 text-center text-slate-400 text-xs">No categories yet</div>
            @endif
        </div>

    </div>{{-- /right --}}
</div>{{-- /body --}}

{{-- ══ ADD CATEGORY MODAL ══════════════════════════════════════════ --}}
<div x-show="showCat" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="showCat = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="showCat = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0"
                 :class="catType === 'expense' ? 'bg-red-100' : 'bg-emerald-100'">
                <i class="fas fa-tags text-sm" :class="catType === 'expense' ? 'text-red-500' : 'text-emerald-600'"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Add Category</p>
                <p class="text-xs text-slate-400 mt-0.5" x-text="catType === 'expense' ? 'Expense category' : 'Income category'"></p>
            </div>
        </div>
        <form method="POST" action="{{ route('branch.finance.categories.store', $branch) }}" class="space-y-3">
            @csrf
            <input type="hidden" name="type" :value="catType">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Salary, Office Rent..."
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="#3b82f6"
                           class="w-12 h-10 border border-slate-200 rounded-xl cursor-pointer p-1 bg-slate-50">
                    <p class="text-xs text-slate-400">Pick a color for this category</p>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="showCat = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 text-white py-2.5 rounded-xl text-sm font-bold transition-colors"
                        :class="catType === 'expense' ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-500 hover:bg-emerald-600'">
                    <i class="fas fa-plus text-xs mr-1"></i> Add
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DELETE TRANSACTION MODAL ════════════════════════════════════ --}}
<div x-show="delModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delModal = false">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trash-alt text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Delete Transaction?</p>
                <p class="text-sm text-slate-500 mt-0.5">This action cannot be undone.</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                Cancel
            </button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ══ DELETE CATEGORY MODAL ═══════════════════════════════════════ --}}
<div x-show="delCatModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delCatModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delCatModal = false">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tag text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Delete Category?</p>
                <p class="text-sm text-slate-500 mt-0.5">Transactions will lose this category.</p>
            </div>
        </div>
        <div class="bg-slate-50 rounded-xl px-4 py-2.5 mb-5 border border-slate-100">
            <p class="text-sm font-semibold text-slate-700" x-text="delCatName"></p>
        </div>
        <div class="flex gap-3">
            <button @click="delCatModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                Cancel
            </button>
            <form :action="delCatUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

</div>{{-- x-data --}}

@push('scripts')
<script>
function financePage() {
    return {
        txType:      'income',
        showCat:     false,
        catType:     'income',
        delModal:    false,
        delUrl:      '',
        delCatModal: false,
        delCatName:  '',
        delCatUrl:   '',

        openDelete(id, url) {
            this.delUrl   = url;
            this.delModal = true;
        },

        openDeleteCat(id, name, url) {
            this.delCatName  = name;
            this.delCatUrl   = url;
            this.delCatModal = true;
        },
    };
}
</script>
@endpush

@endsection
