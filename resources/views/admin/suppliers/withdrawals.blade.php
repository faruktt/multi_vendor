@extends('layouts.app')
@section('title', 'Supplier Withdrawals')
@section('heading', 'Supplier Withdrawals')

@section('content')
<div class="py-4 space-y-6" x-data="supplierWithdrawalsData()">

    {{-- Sub Navigation Tabs & Direct Action --}}
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.suppliers.manage') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.suppliers.manage*') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-store mr-1.5"></i> Suppliers / Vendors
            </a>
            <a href="{{ route('admin.supplier-products.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.supplier-products.*') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-boxes-stacked mr-1.5"></i> Supplier Products
            </a>
            <a href="{{ route('admin.supplier-sales.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.supplier-sales.*') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-boxes-packing mr-1.5"></i> Supplier Sales
            </a>
            <a href="{{ route('admin.suppliers.withdrawals') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.suppliers.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-wallet mr-1.5"></i> Supplier Withdrawals
                @if($stats['pending_count'] > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-[10px] font-black bg-amber-400 text-amber-950 rounded-full animate-pulse">{{ $stats['pending_count'] }}</span>
                @endif
            </a>
        </div>

        <div>
            <button type="button" @click="openDirectWithdrawModal()"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98]">
                <i class="fas fa-hand-holding-dollar text-sm"></i>
                <span>+ Withdraw Payout</span>
            </button>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Pending Requests --}}
        <div class="bg-amber-50/80 rounded-2xl border border-amber-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-amber-800 text-xs font-bold uppercase tracking-wider">Pending Payouts</span>
                <div class="text-2xl font-black text-amber-950 mt-1">৳{{ number_format($stats['pending_amount'], 2) }}</div>
                <div class="text-xs text-amber-700 mt-1 font-semibold">{{ $stats['pending_count'] }} requests pending review</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-200/70 flex items-center justify-center text-amber-800 shadow-inner">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
        </div>

        {{-- Approved / Paid --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Approved &amp; Paid</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">৳{{ number_format($stats['approved_amount'], 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $stats['approved_count'] }} requests approved &amp; deducted</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Rejected Requests</span>
                <div class="text-2xl font-black text-rose-600 mt-1">৳{{ number_format($stats['rejected_amount'], 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $stats['rejected_count'] }} requests rejected</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-inner">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.suppliers.withdrawals') }}" class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search supplier, phone, account, note..."
                       class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- Supplier Dropdown --}}
            <div class="w-48">
                <select name="supplier_id" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Suppliers</option>
                    @foreach($allSuppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->display_name }} ({{ $sup->phone }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="w-36">
                <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            {{-- Date Range --}}
            <div class="flex items-center gap-2">
                <input type="date" name="from" value="{{ request('from') }}"
                       class="py-2 px-3 rounded-xl border border-slate-200 text-xs font-medium">
                <span class="text-xs text-slate-400">to</span>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="py-2 px-3 rounded-xl border border-slate-200 text-xs font-medium">
            </div>

            {{-- Action Buttons --}}
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition">
                Filter
            </button>

            @if(request()->anyFilled(['search', 'supplier_id', 'status', 'from', 'to']))
                <a href="{{ route('admin.suppliers.withdrawals') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Withdrawal Requests Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Request / Date</th>
                        <th class="py-3.5 px-4">Supplier</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Payment Method &amp; Details</th>
                        <th class="py-3.5 px-4">Supplier Note</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Admin Note / Trx</th>
                        <th class="py-3.5 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($withdrawals as $item)
                        @php
                            $supplier = $item->supplier;
                            $currentBalance = $supplier ? $supplier->availableBalance() : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            {{-- ID & Date --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">#REQ-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->created_at->format('d M Y, h:i A') }}</div>
                            </td>

                            {{-- Supplier --}}
                            <td class="py-3.5 px-4">
                                @if($supplier)
                                    <div class="font-bold text-slate-800">{{ $supplier->display_name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $supplier->phone }}</div>
                                    <div class="mt-0.5 flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Avail: ৳{{ number_format($currentBalance, 2) }}
                                        </span>
                                        <button type="button"
                                                @click="openDirectWithdrawModal({{ $supplier->id }})"
                                                class="px-1.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition"
                                                title="Direct Profit Withdraw to {{ $supplier->display_name }}">
                                            <i class="fas fa-plus"></i> Payout
                                        </button>
                                    </div>
                                @else
                                    <span class="text-rose-500">Deleted Supplier</span>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-sm font-black {{ $item->status === 'approved' ? 'text-emerald-700' : ($item->status === 'pending' ? 'text-amber-700' : 'text-slate-400 line-through') }}">
                                    ৳{{ number_format($item->amount, 2) }}
                                </div>
                            </td>

                            {{-- Payment Details --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase
                                        {{ $item->payment_method === 'bkash' ? 'bg-pink-100 text-pink-700' : '' }}
                                        {{ $item->payment_method === 'nagad' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $item->payment_method === 'rocket' ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $item->payment_method === 'bank' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $item->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-700' : '' }}">
                                        {{ $item->payment_method }}
                                    </span>
                                    <span class="font-semibold text-slate-700 text-xs">{{ $item->payment_details }}</span>
                                </div>
                            </td>

                            {{-- Supplier Note --}}
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $item->note ?: '—' }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pending
                                    </span>
                                @elseif($item->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-check text-[10px]"></i>
                                        Approved &amp; Paid
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fas fa-times text-[10px]"></i>
                                        Rejected
                                    </span>
                                @endif
                            </td>

                            {{-- Admin Note --}}
                            <td class="py-3.5 px-4 max-w-xs">
                                @if($item->admin_note)
                                    <div class="p-2 rounded-xl text-xs {{ $item->status === 'approved' ? 'bg-emerald-50 border border-emerald-200 text-emerald-900 font-semibold' : 'bg-rose-50 border border-rose-200 text-rose-900 font-semibold' }}">
                                        {{ $item->admin_note }}
                                    </div>
                                    @if($item->processed_at)
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            {{ $item->processed_at->format('d M Y, h:i A') }}
                                            @if($item->processedBy) (by {{ $item->processedBy->name }}) @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">No admin note</span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($item->status === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                                @click="openApprove({{ $item->id }}, '{{ addslashes($supplier?->display_name ?? 'Supplier') }}', {{ (float) $item->amount }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition inline-flex items-center gap-1">
                                            <i class="fas fa-check text-[10px]"></i> Approve
                                        </button>
                                        <button type="button"
                                                @click="openReject({{ $item->id }}, '{{ addslashes($supplier?->display_name ?? 'Supplier') }}', {{ (float) $item->amount }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition inline-flex items-center gap-1">
                                            <i class="fas fa-times text-[10px]"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-300">
                                    <i class="fas fa-wallet text-xl"></i>
                                </div>
                                <div class="font-bold text-slate-600">No withdrawal requests found</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($withdrawals->hasPages())
            <div class="px-6 py-3 border-t border-slate-200/80 bg-slate-50/50">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>

    {{-- Approve Modal --}}
    <div x-show="approveModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="approveModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="approveModalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="approveModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-slate-100">

                <form :action="'{{ url('admin/suppliers-management/withdrawals') }}/' + selectedId + '/approve'" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="font-bold text-base">Approve Withdrawal</h3>
                        </div>
                        <button type="button" @click="approveModalOpen = false" class="text-white/70 hover:text-white text-base">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-3">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
                            <div class="font-bold flex items-center justify-between">
                                <span>Supplier:</span>
                                <span class="text-slate-800" x-text="selectedSupplier"></span>
                            </div>
                            <div class="font-bold flex items-center justify-between text-sm">
                                <span>Amount:</span>
                                <span class="text-emerald-700 font-black">৳<span x-text="Number(selectedAmount).toFixed(2)"></span></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Trx ID / Reference
                            </label>
                            <input type="text" name="admin_note" x-model="adminNote"
                                   placeholder="Trx ID or reference..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="approveModalOpen = false"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="rejectModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="rejectModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="rejectModalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="rejectModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-slate-100">

                <form :action="'{{ url('admin/suppliers-management/withdrawals') }}/' + selectedId + '/reject'" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-rose-600 to-red-700 px-5 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3 class="font-bold text-base">Reject Withdrawal</h3>
                        </div>
                        <button type="button" @click="rejectModalOpen = false" class="text-white/70 hover:text-white text-base">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-3">
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-1">
                            <div class="font-bold flex items-center justify-between">
                                <span>Supplier:</span>
                                <span class="text-slate-800" x-text="selectedSupplier"></span>
                            </div>
                            <div class="font-bold flex items-center justify-between text-sm">
                                <span>Amount:</span>
                                <span class="text-rose-700 font-black">৳<span x-text="Number(selectedAmount).toFixed(2)"></span></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Rejection Reason <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="admin_note" x-model="adminNote" required
                                   placeholder="Enter reason..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition">
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="rejectModalOpen = false"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Close
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i class="fas fa-ban"></i> Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Direct Profit Withdrawal Modal --}}
    <div x-show="directModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="directModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="directModalOpen = false"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="directModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-slate-100">

                <form action="{{ route('admin.suppliers.withdrawals.direct') }}" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h3 class="font-bold text-base">Supplier Payout Withdrawal</h3>
                        </div>
                        <button type="button" @click="directModalOpen = false" class="text-white/70 hover:text-white text-base">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-3.5 max-h-[75vh] overflow-y-auto">
                        {{-- Searchable Supplier Input --}}
                        <div class="relative" @click.outside="dropdownOpen = false">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Supplier <span class="text-rose-500">*</span>
                            </label>

                            <input type="hidden" name="supplier_id" :value="selectedSupplierId" required>

                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text"
                                       x-model="supplierSearch"
                                       @focus="dropdownOpen = true"
                                       @input="dropdownOpen = true"
                                       placeholder="Search by name or phone..."
                                       autocomplete="off"
                                       class="w-full pl-8 pr-8 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white transition">
                                <template x-if="supplierSearch || selectedSupplierId">
                                    <button type="button" @click="clearSelectedSupplier()"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </template>
                            </div>

                            {{-- Dropdown list --}}
                            <div x-show="dropdownOpen"
                                 x-cloak
                                 class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 divide-y divide-slate-100">
                                <template x-if="getFilteredSuppliers().length === 0">
                                    <div class="p-3 text-xs text-slate-500 text-center">No suppliers found</div>
                                </template>
                                <template x-for="sup in getFilteredSuppliers()" :key="sup.id">
                                    <div @click="selectSupplier(sup)"
                                         :class="selectedSupplierId == sup.id ? 'bg-emerald-50 text-emerald-900 font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                         class="p-2.5 cursor-pointer text-xs flex items-center justify-between transition">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="sup.display_name"></div>
                                            <div class="text-[11px] text-slate-400" x-text="sup.phone || ''"></div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400">Balance:</span>
                                            <span class="font-extrabold text-emerald-600 ml-1">৳<span x-text="Number(sup.available_balance || 0).toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Supplier Balance Info --}}
                        <template x-if="selectedSupplierObj">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-slate-800" x-text="selectedSupplierObj.display_name"></div>
                                    <div class="text-[11px] text-slate-500" x-text="selectedSupplierObj.phone || 'No phone'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Balance</div>
                                    <div class="text-base font-black text-emerald-600">৳<span x-text="Number(selectedSupplierObj.available_balance || 0).toFixed(2)"></span></div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedSupplierObj && Number(selectedSupplierObj.available_balance || 0) <= 0">
                            <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-1.5">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i>
                                <span>No balance available</span>
                            </div>
                        </template>

                        {{-- Amount Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">
                                    Amount (৳) <span class="text-rose-500">*</span>
                                </label>
                                <template x-if="selectedSupplierObj && Number(selectedSupplierObj.available_balance || 0) > 0">
                                    <button type="button" @click="setFullDirectBalance()"
                                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800">
                                        Full Amount (৳<span x-text="Number(selectedSupplierObj.available_balance).toFixed(2)"></span>)
                                    </button>
                                </template>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">৳</span>
                                <input type="number" step="0.01" min="0.01"
                                       :max="selectedSupplierObj ? selectedSupplierObj.available_balance : null"
                                       name="amount" x-model="directAmount" required
                                       placeholder="0.00"
                                       class="w-full pl-7 pr-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        {{-- Payment Method Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" name="payment_method" :value="directPaymentMethod">
                            <div class="grid grid-cols-5 gap-1.5">
                                <button type="button" @click="setPaymentMethod('bkash')"
                                        :class="directPaymentMethod === 'bkash' ? 'bg-pink-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    bKash
                                </button>
                                <button type="button" @click="setPaymentMethod('nagad')"
                                        :class="directPaymentMethod === 'nagad' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Nagad
                                </button>
                                <button type="button" @click="setPaymentMethod('rocket')"
                                        :class="directPaymentMethod === 'rocket' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Rocket
                                </button>
                                <button type="button" @click="setPaymentMethod('bank')"
                                        :class="directPaymentMethod === 'bank' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Bank
                                </button>
                                <button type="button" @click="setPaymentMethod('cash')"
                                        :class="directPaymentMethod === 'cash' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center">
                                    Cash
                                </button>
                            </div>
                        </div>

                        {{-- Payment Details / Account / Trx ID --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Account / Trx ID
                            </label>
                            <input type="text" name="payment_details" x-model="directPaymentDetails"
                                   placeholder="Number or Trx ID"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>

                        {{-- Note --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Note (Optional)
                            </label>
                            <input type="text" name="note" x-model="directNote"
                                   placeholder="Enter note..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="directModalOpen = false"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="!selectedSupplierId || !directAmount || Number(directAmount) <= 0 || (selectedSupplierObj && Number(directAmount) > Number(selectedSupplierObj.available_balance || 0))"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i class="fas fa-check"></i> Withdraw Payout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function supplierWithdrawalsData() {
    return {
        approveModalOpen: false,
        rejectModalOpen: false,
        directModalOpen: false,
        selectedId: null,
        selectedSupplier: '',
        selectedAmount: 0,
        adminNote: '',

        // Direct withdraw state
        directSuppliers: @json($allSuppliers),
        supplierSearch: '',
        dropdownOpen: false,
        selectedSupplierId: '',
        selectedSupplierObj: null,
        directAmount: '',
        directPaymentMethod: 'bkash',
        directPaymentDetails: '',
        directNote: '',

        getFilteredSuppliers() {
            const q = (this.supplierSearch || '').trim().toLowerCase();
            if (!q) {
                return this.directSuppliers;
            }
            return this.directSuppliers.filter(s => {
                const name = (s.name || '').toLowerCase();
                const disp = (s.display_name || '').toLowerCase();
                const comp = (s.company_name || '').toLowerCase();
                const phone = (s.phone || '').toLowerCase();
                return name.includes(q) || disp.includes(q) || comp.includes(q) || phone.includes(q);
            });
        },

        openApprove(id, supplierName, amount) {
            this.selectedId = id;
            this.selectedSupplier = supplierName;
            this.selectedAmount = amount;
            this.adminNote = '';
            this.approveModalOpen = true;
        },
        openReject(id, supplierName, amount) {
            this.selectedId = id;
            this.selectedSupplier = supplierName;
            this.selectedAmount = amount;
            this.adminNote = '';
            this.rejectModalOpen = true;
        },
        openDirectWithdrawModal(supplierId = null) {
            this.directAmount = '';
            this.directNote = '';
            this.dropdownOpen = false;
            if (supplierId) {
                const sup = this.directSuppliers.find(s => s.id == supplierId);
                if (sup) {
                    this.selectSupplier(sup);
                } else {
                    this.selectedSupplierId = supplierId;
                    this.onSupplierChange();
                }
            } else {
                this.selectedSupplierId = '';
                this.selectedSupplierObj = null;
                this.supplierSearch = '';
                this.directPaymentDetails = '';
            }
            this.directModalOpen = true;
        },
        selectSupplier(sup) {
            this.selectedSupplierId = sup.id;
            this.selectedSupplierObj = sup;
            this.supplierSearch = sup.display_name || sup.name || '';
            this.dropdownOpen = false;
            this.directAmount = '';
            this.autoFillPaymentDetails();
        },
        clearSelectedSupplier() {
            this.selectedSupplierId = '';
            this.selectedSupplierObj = null;
            this.supplierSearch = '';
            this.directAmount = '';
            this.directPaymentDetails = '';
            this.dropdownOpen = true;
        },
        onSupplierChange() {
            this.selectedSupplierObj = this.directSuppliers.find(s => s.id == this.selectedSupplierId) || null;
            if (this.selectedSupplierObj) {
                this.supplierSearch = this.selectedSupplierObj.display_name || this.selectedSupplierObj.name || '';
            }
            this.directAmount = '';
            this.autoFillPaymentDetails();
        },
        setPaymentMethod(method) {
            this.directPaymentMethod = method;
            this.autoFillPaymentDetails();
        },
        autoFillPaymentDetails() {
            if (!this.selectedSupplierObj) return;
            if (this.directPaymentMethod === 'bkash' && this.selectedSupplierObj.bkash_number) {
                this.directPaymentDetails = this.selectedSupplierObj.bkash_number;
            } else if (this.directPaymentMethod === 'bank' && this.selectedSupplierObj.bank_info) {
                this.directPaymentDetails = this.selectedSupplierObj.bank_info;
            } else if (this.directPaymentMethod === 'cash') {
                this.directPaymentDetails = 'Cash';
            } else {
                this.directPaymentDetails = '';
            }
        },
        setFullDirectBalance() {
            if (this.selectedSupplierObj && Number(this.selectedSupplierObj.available_balance || 0) > 0) {
                this.directAmount = parseFloat(this.selectedSupplierObj.available_balance).toFixed(2);
            }
        }
    };
}
</script>
@endsection
