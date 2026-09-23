@extends('layouts.app')
@section('title', 'Reseller Withdraw Requests')
@section('heading', 'Reseller Withdraw Requests')

@section('content')
<div class="py-4 space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    selectedWithdrawal: null,
    adminNote: '',
    openApprove(item) {
        this.selectedWithdrawal = item;
        this.adminNote = '';
        this.approveModalOpen = true;
    },
    openReject(item) {
        this.selectedWithdrawal = item;
        this.adminNote = '';
        this.rejectModalOpen = true;
    }
}">

    {{-- Sub Navigation Tabs --}}
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.resellers.index') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-users mr-1.5"></i> Resellers
        </a>
        <a href="{{ route('admin.resellers.orders') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.orders') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-shopping-bag mr-1.5"></i> Reseller Orders
        </a>
        <a href="{{ route('admin.resellers.withdrawals') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-wallet mr-1.5"></i> Withdraw Requests
            @if($stats['pending_count'] > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ $stats['pending_count'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.resellers.report') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.report') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-chart-pie mr-1.5"></i> Reseller Report
        </a>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
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
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Pending Requests --}}
        <div class="bg-amber-50/70 rounded-2xl border border-amber-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-amber-800 text-xs font-bold uppercase tracking-wider">Pending Approval</span>
                <div class="text-2xl font-black text-amber-950 mt-1">৳{{ number_format($stats['pending_amount'], 2) }}</div>
                <div class="text-xs text-amber-700 mt-1 font-semibold">{{ $stats['pending_count'] }} request(s) awaiting action</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-200/70 flex items-center justify-center text-amber-800 shadow-inner">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
        </div>

        {{-- Approved / Paid --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Approved & Paid</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">৳{{ number_format($stats['approved_amount'], 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $stats['approved_count'] }} request(s) paid</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                <i class="fas fa-circle-check text-xl"></i>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Rejected Requests</span>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['rejected_count'] }}</div>
                <div class="text-xs text-slate-400 mt-1">No profit deducted</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-inner">
                <i class="fas fa-circle-xmark text-xl"></i>
            </div>
        </div>

        {{-- Total All Requests --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">All Requests</span>
                <div class="text-2xl font-black text-indigo-600 mt-1">{{ $stats['total_count'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Lifetime total requests</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner">
                <i class="fas fa-list-check text-xl"></i>
            </div>
        </div>

    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Only</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved Only</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected Only</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Reseller</label>
                <select name="reseller_id" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium text-slate-700">
                    <option value="">All Resellers</option>
                    @foreach($resellers as $r)
                        <option value="{{ $r->id }}" {{ request('reseller_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->name }} {{ $r->business_name ? "({$r->business_name})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Method</label>
                <select name="payment_method" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium text-slate-700">
                    <option value="">All Methods</option>
                    <option value="bkash" {{ request('payment_method') === 'bkash' ? 'selected' : '' }}>bKash</option>
                    <option value="nagad" {{ request('payment_method') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                    <option value="rocket" {{ request('payment_method') === 'rocket' ? 'selected' : '' }}>Rocket</option>
                    <option value="bank" {{ request('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium text-slate-700">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium text-slate-700">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['status', 'reseller_id', 'payment_method', 'from', 'to', 'search']))
                    <a href="{{ route('admin.resellers.withdrawals') }}" title="Reset" class="h-11 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-bold transition-colors flex items-center justify-center">
                        <i class="fas fa-redo-alt"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Requests Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Reseller Withdrawal Requests</h3>
            <span class="text-xs text-slate-400">Total {{ $withdrawals->total() }} record(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4">Req # / Date</th>
                        <th class="py-3.5 px-4">Reseller</th>
                        <th class="py-3.5 px-4 text-right">Requested Amount</th>
                        <th class="py-3.5 px-4">Payment Method & Account</th>
                        <th class="py-3.5 px-4">Reseller Note</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Admin Note / Processed</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($withdrawals as $w)
                        @php
                            $r = $w->reseller;
                            $resellerAvailable = $r ? $r->available_balance : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition {{ $w->status === 'pending' ? 'bg-amber-50/20' : '' }}">

                            {{-- Req # / Date --}}
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900">#REQ-{{ str_pad($w->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $w->created_at ? $w->created_at->format('d M Y, h:i A') : '—' }}
                                </div>
                            </td>

                            {{-- Reseller --}}
                            <td class="py-4 px-4">
                                @if($r)
                                    <div class="font-bold text-slate-800">{{ $r->name }}</div>
                                    @if($r->business_name)
                                        <div class="text-[11px] text-indigo-600 font-medium">{{ $r->business_name }}</div>
                                    @endif
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $r->phone ?? $r->email }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Current Avail: <span class="font-bold text-emerald-600">৳{{ number_format($resellerAvailable, 2) }}</span>
                                    </div>
                                @else
                                    <span class="text-rose-500 italic">Reseller Deleted</span>
                                @endif
                            </td>

                            {{-- Requested Amount --}}
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <span class="text-sm font-black text-slate-900">৳{{ number_format($w->amount, 2) }}</span>
                            </td>

                            {{-- Method & Details --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 mb-1">
                                    @if(strtolower($w->payment_method) === 'bkash')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-700">
                                            <i class="fas fa-mobile-screen"></i> bKash
                                        </span>
                                    @elseif(strtolower($w->payment_method) === 'nagad')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">
                                            <i class="fas fa-mobile-screen"></i> Nagad
                                        </span>
                                    @elseif(strtolower($w->payment_method) === 'rocket')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">
                                            <i class="fas fa-mobile-screen"></i> Rocket
                                        </span>
                                    @elseif(strtolower($w->payment_method) === 'bank')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                            <i class="fas fa-building-columns"></i> Bank
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                            <i class="fas fa-wallet"></i> {{ ucfirst($w->payment_method ?? 'Cash') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="font-medium text-slate-800 select-all font-mono text-[11px]">
                                    {{ $w->payment_details ?? 'N/A' }}
                                </div>
                            </td>

                            {{-- Reseller Note --}}
                            <td class="py-4 px-4 max-w-xs">
                                @if($w->note)
                                    <span class="text-slate-600 italic">"{{ $w->note }}"</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($w->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                @elseif($w->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Admin Note / Process Info --}}
                            <td class="py-4 px-4 max-w-xs">
                                @if($w->status === 'approved')
                                    <div class="text-[11px] text-emerald-700 font-medium">
                                        Approved {{ $w->processed_at ? $w->processed_at->format('d M, Y') : '' }}
                                        @if($w->processedBy)
                                            <span class="text-slate-400">by {{ $w->processedBy->name }}</span>
                                        @endif
                                    </div>
                                    @if($w->admin_note)
                                        <div class="text-[11px] text-slate-600 mt-0.5">Note: {{ $w->admin_note }}</div>
                                    @endif
                                @elseif($w->status === 'rejected')
                                    <div class="text-[11px] text-rose-700 font-medium">
                                        Reason: {{ $w->admin_note ?: 'Rejected' }}
                                    </div>
                                    @if($w->processed_at)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $w->processed_at->format('d M, Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Waiting admin action</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                @if($w->status === 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button"
                                                @click="openApprove({
                                                    id: {{ $w->id }},
                                                    amount: '{{ number_format($w->amount, 2) }}',
                                                    reseller_name: '{{ addslashes($r ? $r->name : 'Reseller') }}',
                                                    payment_method: '{{ ucfirst($w->payment_method) }}',
                                                    payment_details: '{{ addslashes($w->payment_details) }}',
                                                    available: '{{ number_format($resellerAvailable, 2) }}',
                                                    url: '{{ route('admin.resellers.withdrawals.approve', $w->id) }}'
                                                })"
                                                class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] shadow-sm transition flex items-center gap-1">
                                            <i class="fas fa-check"></i> Approve
                                        </button>

                                        <button type="button"
                                                @click="openReject({
                                                    id: {{ $w->id }},
                                                    amount: '{{ number_format($w->amount, 2) }}',
                                                    reseller_name: '{{ addslashes($r ? $r->name : 'Reseller') }}',
                                                    payment_method: '{{ ucfirst($w->payment_method) }}',
                                                    payment_details: '{{ addslashes($w->payment_details) }}',
                                                    url: '{{ route('admin.resellers.withdrawals.reject', $w->id) }}'
                                                })"
                                                class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg font-bold text-[11px] border border-rose-200 transition flex items-center gap-1">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Completed</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <i class="fas fa-wallet text-xl"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 text-sm">No Withdrawal Requests Found</h4>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or search criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>

    {{-- Approve Modal --}}
    <div x-show="approveModalOpen"
         x-cloak
         @keydown.escape.window="approveModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="approveModalOpen = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl transition-all border border-slate-100 overflow-hidden"
                 @click.stop>

                <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 p-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center">
                            <i class="fas fa-check-double text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base">Approve Profit Withdrawal</h3>
                            <p class="text-xs text-emerald-100 mt-0.5">Deduct profit from reseller balance</p>
                        </div>
                    </div>
                    <button type="button" @click="approveModalOpen = false" class="text-emerald-100 hover:text-white">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="selectedWithdrawal ? selectedWithdrawal.url : '#'" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div class="p-4 bg-emerald-50/70 border border-emerald-100 rounded-xl space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Reseller:</span>
                            <span class="font-bold text-slate-800" x-text="selectedWithdrawal ? selectedWithdrawal.reseller_name : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Requested Amount:</span>
                            <span class="font-black text-emerald-700 text-sm" x-text="selectedWithdrawal ? '৳' + selectedWithdrawal.amount : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Payment Destination:</span>
                            <span class="font-medium text-slate-800" x-text="selectedWithdrawal ? selectedWithdrawal.payment_method + ' - ' + selectedWithdrawal.payment_details : ''"></span>
                        </div>
                        <div class="flex justify-between border-t border-emerald-200/60 pt-2">
                            <span class="text-slate-500">Current Balance:</span>
                            <span class="font-bold text-slate-700" x-text="selectedWithdrawal ? '৳' + selectedWithdrawal.available : ''"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Admin Note / Transaction ID <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <input type="text"
                               name="admin_note"
                               placeholder="e.g. TrxID: 9X321KJS, sent via bKash Merchant"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="approveModalOpen = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition transform active:scale-95">
                            <i class="fas fa-check mr-1"></i> Approve & Deduct Profit
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="rejectModalOpen"
         x-cloak
         @keydown.escape.window="rejectModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="rejectModalOpen = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl transition-all border border-slate-100 overflow-hidden"
                 @click.stop>

                <div class="bg-gradient-to-r from-rose-600 to-rose-700 p-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center">
                            <i class="fas fa-circle-xmark text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base">Reject Withdrawal Request</h3>
                            <p class="text-xs text-rose-100 mt-0.5">No profit will be deducted</p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModalOpen = false" class="text-rose-100 hover:text-white">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <form :action="selectedWithdrawal ? selectedWithdrawal.url : '#'" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div class="p-4 bg-rose-50/70 border border-rose-100 rounded-xl space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Reseller:</span>
                            <span class="font-bold text-slate-800" x-text="selectedWithdrawal ? selectedWithdrawal.reseller_name : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Requested Amount:</span>
                            <span class="font-bold text-rose-700" x-text="selectedWithdrawal ? '৳' + selectedWithdrawal.amount : ''"></span>
                        </div>
                        <p class="text-[11px] text-rose-600 pt-1">
                            Rejecting this request will mark it as rejected. The amount will remain in the reseller's available balance.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Rejection Reason <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="admin_note"
                                  rows="2"
                                  required
                                  placeholder="e.g. Invalid bKash account number, please re-submit with correct number."
                                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" @click="rejectModalOpen = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition transform active:scale-95">
                            <i class="fas fa-times mr-1"></i> Confirm Rejection
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
