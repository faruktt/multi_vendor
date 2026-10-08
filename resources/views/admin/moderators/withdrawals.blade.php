@extends('layouts.app')
@section('title', 'মডারেটর বেতন উত্তোলন রিকোয়েস্ট')
@section('heading', 'মডারেটর বেতন উত্তোলন রিকোয়েস্ট')

@section('content')
<div class="py-4 space-y-6" x-data="moderatorWithdrawalsData()">

    {{-- Sub Navigation Tabs & Direct Action --}}
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.moderators.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.moderators.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-user-shield mr-1.5"></i> Moderators (মডারেটর তালিকা)
            </a>
            <a href="{{ route('admin.moderators.reports') }}"
               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.moderators.reports') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-chart-line mr-1.5"></i> Work Shifts & Reports (কাজের রিপোর্ট)
            </a>
            <a href="{{ route('admin.moderators.withdrawals') }}"
               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.moderators.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-wallet mr-1.5"></i> Salary Withdrawals (বেতন উত্তোলন)
                @if($stats['pending_count'] > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ $stats['pending_count'] }}</span>
                @endif
            </a>
        </div>

        <div>
            <button type="button" @click="openDirectWithdrawModal()"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98]">
                <i class="fas fa-hand-holding-dollar text-sm"></i>
                <span>+ বেতন উইথড্র</span>
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Pending Requests --}}
        <div class="bg-amber-50/70 rounded-2xl border border-amber-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-amber-800 text-xs font-bold uppercase tracking-wider">Pending Approval (অপেক্ষমান)</span>
                <div class="text-2xl font-black text-amber-950 mt-1">৳{{ number_format($stats['pending_amount'], 2) }}</div>
                <div class="text-xs text-amber-700 mt-1 font-semibold">{{ $stats['pending_count'] }} টি রিকোয়েস্ট অনুমোদনের অপেক্ষায়</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-200/70 flex items-center justify-center text-amber-800 shadow-inner">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
        </div>

        {{-- Approved / Paid --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Approved & Paid (পরিশোধিত)</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">৳{{ number_format($stats['approved_amount'], 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ $stats['approved_count'] }} টি রিকোয়েস্ট অনুমোদিত</div>
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
                <div class="text-xs text-slate-400 mt-1">{{ $stats['rejected_count'] }} টি রিকোয়েস্ট বাতিল করা হয়েছে</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-inner">
                <i class="fas fa-ban text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.moderators.withdrawals') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="status" class="h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (অপেক্ষমান)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (অনুমোদিত)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (বাতিল)</option>
                </select>
            </div>

            <div>
                <select name="moderator_id" class="h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="">সকল মডারেটর</option>
                    @foreach($allModerators as $mod)
                        <option value="{{ $mod->id }}" {{ request('moderator_id') == $mod->id ? 'selected' : '' }}>{{ $mod->name }} ({{ $mod->phone ?: $mod->email }})</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-colors cursor-pointer">
                <i class="fas fa-search mr-1.5"></i> Filter
            </button>
            @if(request()->hasAny(['status', 'moderator_id', 'from', 'to']))
                <a href="{{ route('admin.moderators.withdrawals') }}" class="h-10 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Withdrawals Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">মডারেটর</th>
                        <th class="px-5 py-3.5 text-right">পরিমাণ (৳)</th>
                        <th class="px-5 py-3.5">পেমেন্ট মেথড</th>
                        <th class="px-5 py-3.5">অ্যাকাউন্ট নম্বর / বিবরণ</th>
                        <th class="px-5 py-3.5">তারিখ ও সময়</th>
                        <th class="px-5 py-3.5 text-center">স্ট্যাটাস</th>
                        <th class="px-5 py-3.5">এডমিন নোট (Admin Note)</th>
                        <th class="px-5 py-3.5 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($withdrawals as $w)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Moderator --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($w->moderator?->image_url)
                                    <img src="{{ $w->moderator->image_url }}" alt="{{ $w->moderator->name }}"
                                         class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-2xs flex-shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-xs shadow-sm shadow-indigo-200 flex-shrink-0">
                                        {{ strtoupper(substr($w->moderator?->name ?? 'M', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-extrabold text-slate-800 text-sm">{{ $w->moderator?->name ?? 'Deleted Moderator' }}</div>
                                    <div class="text-slate-400 text-[11px] font-mono">{{ $w->moderator?->phone ?: $w->moderator?->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Amount --}}
                        <td class="px-5 py-4 text-right font-black font-mono text-sm {{ $w->isApproved() ? 'text-emerald-600' : ($w->isRejected() ? 'text-rose-500 line-through' : 'text-slate-900') }}">
                            ৳{{ number_format($w->amount, 2) }}
                        </td>

                        {{-- Method --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $w->payment_method === 'bkash' ? 'bg-pink-50 text-pink-700 border border-pink-200' : ($w->payment_method === 'nagad' ? 'bg-orange-50 text-orange-700 border border-orange-200' : ($w->payment_method === 'rocket' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-blue-50 text-blue-700 border border-blue-200')) }}">
                                {{ $w->payment_method }}
                            </span>
                        </td>

                        {{-- Account Details --}}
                        <td class="px-5 py-4 max-w-xs">
                            <div class="font-mono text-xs text-slate-800 font-semibold">{{ $w->payment_details }}</div>
                            @if($w->note)
                                <div class="text-[11px] text-slate-400 mt-0.5">নোট: {{ $w->note }}</div>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 font-mono text-slate-600 text-xs">
                            <div>{{ $w->created_at->timezone('Asia/Dhaka')->format('d M, Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $w->created_at->timezone('Asia/Dhaka')->format('h:i A') }}</div>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 text-center">
                            @if($w->isPending())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    Pending (অপেক্ষমান)
                                </span>
                            @elseif($w->isApproved())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-check-circle text-[10px]"></i>
                                    Approved (অনুমোদিত)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="fas fa-times-circle text-[10px]"></i>
                                    Rejected (বাতিল)
                                </span>
                            @endif
                        </td>

                        {{-- Admin Note --}}
                        <td class="px-5 py-4 max-w-xs">
                            @if($w->admin_note)
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-700">
                                    <span class="font-semibold">{{ $w->admin_note }}</span>
                                    @if($w->processedBy)
                                        <div class="text-[10px] text-slate-400 mt-0.5">By: {{ $w->processedBy->name }} ({{ $w->processed_at?->timezone('Asia/Dhaka')->format('d M, h:i A') }})</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            @if($w->isPending())
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Approve Button --}}
                                    <button type="button" @click="openApprove(@js($w))"
                                            title="অ্যাপ্রুভ করুন"
                                            class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs flex items-center gap-1 transition-colors cursor-pointer shadow-xs">
                                        <i class="fas fa-check text-[10px]"></i>
                                        <span>Approve</span>
                                    </button>

                                    {{-- Reject Button --}}
                                    <button type="button" @click="openReject(@js($w))"
                                            title="বাতিল করুন"
                                            class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold text-xs flex items-center gap-1 transition-colors cursor-pointer">
                                        <i class="fas fa-times text-[10px]"></i>
                                        <span>Reject</span>
                                    </button>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 font-semibold">প্রক্রিয়াজাত</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <i class="fas fa-wallet text-3xl text-slate-300 mb-2 block"></i>
                            <p>কোনো বেতন উত্তোলন রিকোয়েস্ট পাওয়া যায়নি।</p>
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

    {{-- ── Approve Modal ─────────────────────────────────────────── --}}
    <div x-show="approveModalOpen" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="approveModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col" @click.outside="approveModalOpen = false">
            <div class="p-5 border-b border-slate-100 bg-emerald-50 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-emerald-900 font-black text-sm sm:text-base">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
                    <span>উইথড্র অনুমোদন</span>
                </div>
                <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'/admin/moderators/withdrawals/' + (selectedWithdrawal ? selectedWithdrawal.id : '') + '/approve'" method="POST" class="p-5 space-y-3">
                @csrf
                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">মডারেটর:</span>
                        <span class="font-bold text-slate-800" x-text="selectedWithdrawal?.moderator?.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">পরিমাণ:</span>
                        <span class="font-black text-emerald-700 text-sm" x-text="'৳' + Number(selectedWithdrawal?.amount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">মেথড / বিবরণ:</span>
                        <span class="font-medium text-slate-800" x-text="(selectedWithdrawal?.payment_method || '') + ' - ' + (selectedWithdrawal?.payment_details || '')"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Trx ID / রেফারেন্স
                    </label>
                    <input type="text" name="admin_note" x-model="adminNote"
                           placeholder="Trx ID বা রেফারেন্স..."
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="approveModalOpen = false"
                            class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        বাতিল
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-check"></i> অনুমোদন করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Reject Modal ──────────────────────────────────────────── --}}
    <div x-show="rejectModalOpen" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="rejectModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col" @click.outside="rejectModalOpen = false">
            <div class="p-5 border-b border-slate-100 bg-rose-50 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-rose-900 font-black text-sm sm:text-base">
                    <i class="fas fa-times-circle text-rose-600 text-lg"></i>
                    <span>উইথড্র বাতিল</span>
                </div>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'/admin/moderators/withdrawals/' + (selectedWithdrawal ? selectedWithdrawal.id : '') + '/reject'" method="POST" class="p-5 space-y-3">
                @csrf
                <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">মডারেটর:</span>
                        <span class="font-bold text-slate-800" x-text="selectedWithdrawal?.moderator?.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">পরিমাণ:</span>
                        <span class="font-bold text-rose-700" x-text="'৳' + Number(selectedWithdrawal?.amount || 0).toFixed(2)"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        বাতিলের কারণ <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="admin_note" required x-model="adminNote"
                           placeholder="কারণ লিখুন..."
                           class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition">
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="rejectModalOpen = false"
                            class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        বন্ধ
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-ban"></i> বাতিল করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Direct Moderator Salary/Profit Withdrawal Modal --}}
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

                <form action="{{ route('admin.moderators.withdrawals.direct') }}" method="POST">
                    @csrf

                    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white text-base">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <h3 class="font-bold text-base">মডারেটর বেতন উইথড্র</h3>
                        </div>
                        <button type="button" @click="directModalOpen = false" class="text-white/70 hover:text-white text-base">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-3.5 max-h-[75vh] overflow-y-auto">
                        {{-- Searchable Moderator Input --}}
                        <div class="relative" @click.outside="dropdownOpen = false">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                মডারেটর <span class="text-rose-500">*</span>
                            </label>

                            <input type="hidden" name="moderator_id" :value="selectedModeratorId" required>

                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-search text-xs"></i>
                                </span>
                                <input type="text"
                                       x-model="moderatorSearch"
                                       @focus="dropdownOpen = true"
                                       @input="dropdownOpen = true"
                                       placeholder="নাম বা মোবাইল নম্বর লিখে খুঁজুন..."
                                       autocomplete="off"
                                       class="w-full pl-8 pr-8 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white transition">
                                <template x-if="moderatorSearch || selectedModeratorId">
                                    <button type="button" @click="clearSelectedModerator()"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </template>
                            </div>

                            {{-- Dropdown list --}}
                            <div x-show="dropdownOpen"
                                 x-cloak
                                 class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 divide-y divide-slate-100">
                                <template x-if="getFilteredModerators().length === 0">
                                    <div class="p-3 text-xs text-slate-500 text-center">কোনো মডারেটর পাওয়া যায়নি</div>
                                </template>
                                <template x-for="m in getFilteredModerators()" :key="m.id">
                                    <div @click="selectModerator(m)"
                                         :class="selectedModeratorId == m.id ? 'bg-emerald-50 text-emerald-900 font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                         class="p-2.5 cursor-pointer text-xs flex items-center justify-between transition">
                                        <div>
                                            <div class="font-bold text-slate-800" x-text="m.name"></div>
                                            <div class="text-[11px] text-slate-400" x-text="m.phone || m.email || ''"></div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400">ব্যালেন্স:</span>
                                            <span class="font-extrabold text-emerald-600 ml-1">৳<span x-text="Number(m.available_balance || 0).toFixed(2)"></span></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Moderator Balance Info --}}
                        <template x-if="selectedModeratorObj">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-slate-800" x-text="selectedModeratorObj.name"></div>
                                    <div class="text-[11px] text-slate-500" x-text="selectedModeratorObj.phone || selectedModeratorObj.email"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">ব্যালেন্স</div>
                                    <div class="text-base font-black text-emerald-600">৳<span x-text="Number(selectedModeratorObj.available_balance || 0).toFixed(2)"></span></div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedModeratorObj && Number(selectedModeratorObj.available_balance || 0) <= 0">
                            <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center gap-1.5">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i>
                                <span>উত্তোলনযোগ্য ব্যালেন্স নেই</span>
                            </div>
                        </template>

                        {{-- Amount Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-slate-700">
                                    পরিমাণ (৳) <span class="text-rose-500">*</span>
                                </label>
                                <template x-if="selectedModeratorObj && Number(selectedModeratorObj.available_balance || 0) > 0">
                                    <button type="button" @click="setFullDirectBalance()"
                                            class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 cursor-pointer">
                                        সব টাকা (৳<span x-text="Number(selectedModeratorObj.available_balance).toFixed(2)"></span>)
                                    </button>
                                </template>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold text-xs">৳</span>
                                <input type="number" step="0.01" min="0.01"
                                       :max="selectedModeratorObj ? selectedModeratorObj.available_balance : null"
                                       name="amount" x-model="directAmount" required
                                       placeholder="0.00"
                                       class="w-full pl-7 pr-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        {{-- Payment Method Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                পেমেন্ট মেথড <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" name="payment_method" :value="directPaymentMethod">
                            <div class="grid grid-cols-5 gap-1.5">
                                <button type="button" @click="setPaymentMethod('bkash')"
                                        :class="directPaymentMethod === 'bkash' ? 'bg-pink-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center cursor-pointer">
                                    bKash
                                </button>
                                <button type="button" @click="setPaymentMethod('nagad')"
                                        :class="directPaymentMethod === 'nagad' ? 'bg-orange-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center cursor-pointer">
                                    Nagad
                                </button>
                                <button type="button" @click="setPaymentMethod('rocket')"
                                        :class="directPaymentMethod === 'rocket' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center cursor-pointer">
                                    Rocket
                                </button>
                                <button type="button" @click="setPaymentMethod('bank')"
                                        :class="directPaymentMethod === 'bank' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center cursor-pointer">
                                    Bank
                                </button>
                                <button type="button" @click="setPaymentMethod('cash')"
                                        :class="directPaymentMethod === 'cash' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                        class="py-1.5 px-1 rounded-lg text-xs transition text-center cursor-pointer">
                                    Cash
                                </button>
                            </div>
                        </div>

                        {{-- Payment Details / Account / Trx ID --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                অ্যাকাউন্ট / Trx ID
                            </label>
                            <input type="text" name="payment_details" x-model="directPaymentDetails"
                                   placeholder="নম্বর বা Trx ID"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>

                        {{-- Note --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                নোট (ঐচ্ছিক)
                            </label>
                            <input type="text" name="note" x-model="directNote"
                                   placeholder="নোট লিখুন..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="directModalOpen = false"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                            বাতিল
                        </button>
                        <button type="submit"
                                :disabled="!selectedModeratorId || !directAmount || Number(directAmount) <= 0 || (selectedModeratorObj && Number(directAmount) > Number(selectedModeratorObj.available_balance || 0))"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-check"></i> উইথড্র করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function moderatorWithdrawalsData() {
    return {
        approveModalOpen: false,
        rejectModalOpen: false,
        directModalOpen: false,
        selectedWithdrawal: null,
        adminNote: '',

        // Direct withdraw state
        directModerators: @json($allModerators),
        moderatorSearch: '',
        dropdownOpen: false,
        selectedModeratorId: '',
        selectedModeratorObj: null,
        directAmount: '',
        directPaymentMethod: 'bkash',
        directPaymentDetails: '',
        directNote: '',

        getFilteredModerators() {
            const q = (this.moderatorSearch || '').trim().toLowerCase();
            if (!q) {
                return this.directModerators;
            }
            return this.directModerators.filter(m => {
                const name = (m.name || '').toLowerCase();
                const phone = (m.phone || '').toLowerCase();
                const email = (m.email || '').toLowerCase();
                return name.includes(q) || phone.includes(q) || email.includes(q);
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
        openDirectWithdrawModal(moderatorId = null) {
            this.directAmount = '';
            this.directNote = '';
            this.dropdownOpen = false;
            if (moderatorId) {
                const m = this.directModerators.find(item => item.id == moderatorId);
                if (m) {
                    this.selectModerator(m);
                } else {
                    this.selectedModeratorId = moderatorId;
                    this.onModeratorChange();
                }
            } else {
                this.selectedModeratorId = '';
                this.selectedModeratorObj = null;
                this.moderatorSearch = '';
                this.directPaymentDetails = '';
            }
            this.directModalOpen = true;
        },
        selectModerator(m) {
            this.selectedModeratorId = m.id;
            this.selectedModeratorObj = m;
            this.moderatorSearch = m.name + (m.phone ? ' (' + m.phone + ')' : '');
            this.dropdownOpen = false;
            this.directAmount = '';
            this.autoFillPaymentDetails();
        },
        clearSelectedModerator() {
            this.selectedModeratorId = '';
            this.selectedModeratorObj = null;
            this.moderatorSearch = '';
            this.directAmount = '';
            this.directPaymentDetails = '';
            this.dropdownOpen = true;
        },
        onModeratorChange() {
            this.selectedModeratorObj = this.directModerators.find(m => m.id == this.selectedModeratorId) || null;
            if (this.selectedModeratorObj) {
                this.moderatorSearch = this.selectedModeratorObj.name;
            }
            this.directAmount = '';
            this.autoFillPaymentDetails();
        },
        setPaymentMethod(method) {
            this.directPaymentMethod = method;
            this.autoFillPaymentDetails();
        },
        autoFillPaymentDetails() {
            if (!this.selectedModeratorObj) return;
            if (this.directPaymentMethod === 'cash') {
                this.directPaymentDetails = 'Cash';
            } else if (this.directPaymentMethod === 'bkash' && this.selectedModeratorObj.phone) {
                this.directPaymentDetails = this.selectedModeratorObj.phone;
            } else {
                this.directPaymentDetails = '';
            }
        },
        setFullDirectBalance() {
            if (this.selectedModeratorObj && Number(this.selectedModeratorObj.available_balance || 0) > 0) {
                this.directAmount = parseFloat(this.selectedModeratorObj.available_balance).toFixed(2);
            }
        }
    };
}
</script>
@endsection
