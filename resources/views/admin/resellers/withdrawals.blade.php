@extends('layouts.app')
@section('title', 'Reseller Withdraw Requests')
@section('heading', 'Reseller Withdraw Requests')

@section('content')
<div class="py-4 space-y-6" x-data="resellerWithdrawalsData()">

    {{-- Sub Navigation Tabs & Direct Action --}}
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.resellers.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.resellers.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-users mr-1.5"></i> Resellers
            </a>
            <a href="{{ route('admin.resellers.orders') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.resellers.orders') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-shopping-bag mr-1.5"></i> Reseller Orders
            </a>
            <a href="{{ route('admin.resellers.withdrawals') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.resellers.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-wallet mr-1.5"></i> Withdraw Requests
                @if($stats['pending_count'] > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ $stats['pending_count'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.resellers.report') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors {{ request()->routeIs('admin.resellers.report') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-chart-pie mr-1.5"></i> Reseller Report
            </a>
        </div>

        <div>
            <button type="button" @click="openDirectWithdrawModal()"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98]">
                <i class="fas fa-hand-holding-dollar text-sm"></i>
                <span>+ Withdraw Profit</span>
            </button>
        </div>
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

                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="font-bold text-base">Approve Withdrawal</h3>
                    </div>
                    <button type="button" @click="approveModalOpen = false" class="text-white/70 hover:text-white text-base">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form :action="selectedWithdrawal ? selectedWithdrawal.url : '#'" method="POST" class="p-5 space-y-3">
                    @csrf

                    <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Reseller:</span>
                            <span class="font-bold text-slate-800" x-text="selectedWithdrawal ? selectedWithdrawal.reseller_name : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Amount:</span>
                            <span class="font-black text-emerald-700 text-sm" x-text="selectedWithdrawal ? '৳' + selectedWithdrawal.amount : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Method / Details:</span>
                            <span class="font-medium text-slate-800" x-text="selectedWithdrawal ? selectedWithdrawal.payment_method + ' - ' + selectedWithdrawal.payment_details : ''"></span>
                        </div>
                        <div class="flex justify-between border-t border-emerald-200/60 pt-1.5">
                            <span class="text-slate-500">Current Balance:</span>
                            <span class="font-bold text-slate-700" x-text="selectedWithdrawal ? '৳' + selectedWithdrawal.available : ''"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Trx ID / Reference
                        </label>
                        <input type="text"
                               name="admin_note"
                               placeholder="Trx ID or reference..."
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="approveModalOpen = false"
                                class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow transition flex items-center gap-1.5">
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
         @keydown.escape.window="rejectModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
             @click="rejectModalOpen = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl transition-all border border-slate-100 overflow-hidden"
                 @click.stop>

                <div class="bg-gradient-to-r from-rose-600 to-red-700 px-5 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3 class="font-bold text-base">Reject Withdrawal</h3>
                    </div>
                    <button type="button" @click="rejectModalOpen = false" class="text-white/70 hover:text-white text-base">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form :action="selectedWithdrawal ? selectedWithdrawal.url : '#'" method="POST" class="p-5 space-y-3">
                    @csrf

                    <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Reseller:</span>
                            <span class="font-bold text-slate-800" x-text="selectedWithdrawal ? selectedWithdrawal.reseller_name : ''"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Amount:</span>
                            <span class="font-bold text-rose-700" x-text="selectedWithdrawal ? '৳' + selectedWithdrawal.amount : ''"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Rejection Reason <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="admin_note"
                               required
                               placeholder="Enter reason..."
                               class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition">
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="rejectModalOpen = false"
                                class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Close
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow transition flex items-center gap-1.5">
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

                <form action="{{ route('admin.resellers.withdrawals.direct') }}" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h3 class="font-bold text-base">Reseller Profit Withdrawal</h3>
                        </div>
                        <button type="button" @click="directModalOpen = false" class="text-white/70 hover:text-white text-base">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-3.5 max-h-[75vh] overflow-y-auto">
                        {{-- Searchable Reseller Input --}}
                        <div class="relative" @click.outside="dropdownOpen = false">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Reseller <span class="text-rose-500">*</span>
                            </label>

                            <input type="hidden" name="reseller_id" :value="selectedResellerId" required>

                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text"
                                       x-model="resellerSearch"
                                       @focus="dropdownOpen = true"
                                       @input="dropdownOpen = true"
                                       placeholder="Search by name or phone..."
                                       autocomplete="off"
                                       class="w-full pl-8 pr-8 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white transition">
                                <template x-if="resellerSearch || selectedResellerId">
                                    <button type="button" @click="clearSelectedReseller()"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </template>
                            </div>

                            {{-- Dropdown list --}}
                            <div x-show="dropdownOpen"
                                 x-cloak
                                 class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 divide-y divide-slate-100">
                                <template x-if="getFilteredResellers().length === 0">
                                    <div class="p-3 text-xs text-slate-500 text-center">No resellers found</div>
                                </template>
                                <template x-for="r in getFilteredResellers()" :key="r.id">
                                    <div @click="selectReseller(r)"
                                         :class="selectedResellerId == r.id ? 'bg-emerald-50 text-emerald-900 font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                         class="p-2.5 cursor-pointer text-xs flex items-center justify-between transition">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="r.name + (r.business_name ? ' (' + r.business_name + ')' : '')"></div>
                                            <div class="text-[11px] text-slate-400" x-text="r.phone || ''"></div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400">Balance:</span>
                                            <span class="font-extrabold text-emerald-600 ml-1">৳<span x-text="Number(r.available_balance || 0).toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Reseller Balance Info --}}
                        <template x-if="selectedResellerObj">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-slate-800" x-text="selectedResellerObj.name"></div>
                                    <div class="text-[11px] text-slate-500" x-text="selectedResellerObj.phone || 'No phone'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Balance</div>
                                    <div class="text-base font-black text-emerald-600">৳<span x-text="Number(selectedResellerObj.available_balance || 0).toFixed(2)"></span></div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedResellerObj && Number(selectedResellerObj.available_balance || 0) <= 0">
                            <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-1.5">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i>
                                <span>No withdrawable balance available</span>
                            </div>
                        </template>

                        {{-- Amount Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">
                                    Amount (৳) <span class="text-rose-500">*</span>
                                </label>
                                <template x-if="selectedResellerObj && Number(selectedResellerObj.available_balance || 0) > 0">
                                    <button type="button" @click="setFullDirectBalance()"
                                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800">
                                        Full Amount (৳<span x-text="Number(selectedResellerObj.available_balance).toFixed(2)"></span>)
                                    </button>
                                </template>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">৳</span>
                                <input type="number" step="0.01" min="0.01"
                                       :max="selectedResellerObj ? selectedResellerObj.available_balance : null"
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
                                :disabled="!selectedResellerId || !directAmount || Number(directAmount) <= 0 || (selectedResellerObj && Number(directAmount) > Number(selectedResellerObj.available_balance || 0))"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i class="fas fa-check"></i> Withdraw Profit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function resellerWithdrawalsData() {
    return {
        approveModalOpen: false,
        rejectModalOpen: false,
        directModalOpen: false,
        selectedWithdrawal: null,
        adminNote: '',

        // Direct withdraw state
        directResellers: @json($resellers),
        resellerSearch: '',
        dropdownOpen: false,
        selectedResellerId: '',
        selectedResellerObj: null,
        directAmount: '',
        directPaymentMethod: 'bkash',
        directPaymentDetails: '',
        directNote: '',

        getFilteredResellers() {
            const q = (this.resellerSearch || '').trim().toLowerCase();
            if (!q) {
                return this.directResellers;
            }
            return this.directResellers.filter(r => {
                const name = (r.name || '').toLowerCase();
                const bname = (r.business_name || '').toLowerCase();
                const phone = (r.phone || '').toLowerCase();
                return name.includes(q) || bname.includes(q) || phone.includes(q);
            });
        },

        openApprove(item) {
            this.selectedWithdrawal = item;
            this.adminNote = '';
            this.approveModalOpen = true;
        },
        openReject(item) {
            this.selectedWithdrawal = item;
            this.adminNote = '';
            this.rejectModalOpen = true;
        },
        openDirectWithdrawModal(resellerId = null) {
            this.directAmount = '';
            this.directNote = '';
            this.dropdownOpen = false;
            if (resellerId) {
                const r = this.directResellers.find(item => item.id == resellerId);
                if (r) {
                    this.selectReseller(r);
                } else {
                    this.selectedResellerId = resellerId;
                    this.onResellerChange();
                }
            } else {
                this.selectedResellerId = '';
                this.selectedResellerObj = null;
                this.resellerSearch = '';
                this.directPaymentDetails = '';
            }
            this.directModalOpen = true;
        },
        selectReseller(r) {
            this.selectedResellerId = r.id;
            this.selectedResellerObj = r;
            this.resellerSearch = r.name + (r.business_name ? ' (' + r.business_name + ')' : '');
            this.dropdownOpen = false;
            this.directAmount = '';
            this.autoFillPaymentDetails();
        },
        clearSelectedReseller() {
            this.selectedResellerId = '';
            this.selectedResellerObj = null;
            this.resellerSearch = '';
            this.directAmount = '';
            this.directPaymentDetails = '';
            this.dropdownOpen = true;
        },
        onResellerChange() {
            this.selectedResellerObj = this.directResellers.find(r => r.id == this.selectedResellerId) || null;
            if (this.selectedResellerObj) {
                this.resellerSearch = this.selectedResellerObj.name;
            }
            this.directAmount = '';
            this.autoFillPaymentDetails();
        },
        setPaymentMethod(method) {
            this.directPaymentMethod = method;
            this.autoFillPaymentDetails();
        },
        autoFillPaymentDetails() {
            if (!this.selectedResellerObj) return;
            if (this.directPaymentMethod === 'cash') {
                this.directPaymentDetails = 'Cash';
            } else if (this.directPaymentMethod === 'bkash' && this.selectedResellerObj.phone) {
                this.directPaymentDetails = this.selectedResellerObj.phone;
            } else {
                this.directPaymentDetails = '';
            }
        },
        setFullDirectBalance() {
            if (this.selectedResellerObj && Number(this.selectedResellerObj.available_balance || 0) > 0) {
                this.directAmount = parseFloat(this.selectedResellerObj.available_balance).toFixed(2);
            }
        }
    };
}
</script>
@endsection
