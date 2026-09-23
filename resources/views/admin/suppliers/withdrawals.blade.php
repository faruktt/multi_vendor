@extends('layouts.app')
@section('title', 'Supplier Withdrawals (সাপ্লায়ার পেমেন্ট উত্তোলন)')
@section('heading', 'Supplier Withdrawals')

@section('content')
<div class="py-4 space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    selectedId: null,
    selectedSupplier: '',
    selectedAmount: 0,
    adminNote: '',
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
    }
}">

    {{-- Sub Navigation Tabs --}}
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
                <span class="text-amber-800 text-xs font-bold uppercase tracking-wider">Pending Payouts (অপেক্ষমান)</span>
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
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Approved &amp; Paid (পরিশোধিত)</span>
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
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Rejected Requests (বাতিল)</span>
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
                                    <div class="mt-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Avail: ৳{{ number_format($currentBalance, 2) }}
                                        </span>
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
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md w-full border border-slate-100">

                <form :action="'{{ url('admin/suppliers-management/withdrawals') }}/' + selectedId + '/approve'" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white text-lg">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-base">Approve Withdrawal Request</h3>
                                <p class="text-xs text-emerald-100">অনুমোদন করলে ব্যালেন্স থেকে টাকা কর্তন হবে</p>
                            </div>
                        </div>
                        <button type="button" @click="approveModalOpen = false" class="text-white/70 hover:text-white text-lg">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-1">
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
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Admin Note / Transaction ID (এডমিন নোট / Trx ID)
                            </label>
                            <textarea name="admin_note" x-model="adminNote" rows="3"
                                      placeholder="e.g. bKash TrxID: 9XF120A, Paid from Admin Account or Bank Ref..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">This note and transaction reference will be shown to the supplier.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="approveModalOpen = false"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                            <i class="fas fa-check"></i> Approve &amp; Deduct Balance
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

                    <div class="bg-gradient-to-r from-rose-600 to-red-700 px-6 py-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white text-lg">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-base">Reject Withdrawal Request</h3>
                                <p class="text-xs text-rose-100">বাতিল করার কারণ সাপ্লায়ার দেখতে পাবে</p>
                            </div>
                        </div>
                        <button type="button" @click="rejectModalOpen = false" class="text-white/70 hover:text-white text-lg">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-1">
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
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Rejection Reason / Note <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="admin_note" x-model="adminNote" rows="3" required
                                      placeholder="e.g. Incorrect account number, please provide valid bKash personal number..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">Please provide a clear reason so the supplier can resubmit correctly.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="rejectModalOpen = false"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/30 transition flex items-center gap-1.5">
                            <i class="fas fa-ban"></i> Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
