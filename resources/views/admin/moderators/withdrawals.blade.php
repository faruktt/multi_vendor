@extends('layouts.app')
@section('title', 'মডারেটর বেতন উত্তোলন রিকোয়েস্ট')
@section('heading', 'মডারেটর বেতন উত্তোলন রিকোয়েস্ট')

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

    {{-- ── Approve Modal With Note ────────────────────────────────── --}}
    <div x-show="approveModalOpen" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="approveModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col" @click.outside="approveModalOpen = false">
            <div class="p-5 border-b border-slate-100 bg-emerald-50 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-emerald-900 font-black text-sm sm:text-base">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
                    <span>উইথড্র রিকোয়েস্ট অ্যাপ্রুভ করুন</span>
                </div>
                <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'/admin/moderators/withdrawals/' + (selectedWithdrawal ? selectedWithdrawal.id : '') + '/approve'" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">মডারেটর:</span>
                        <span class="font-bold text-slate-800" x-text="selectedWithdrawal?.moderator?.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">উত্তোলনের পরিমাণ:</span>
                        <span class="font-black font-mono text-emerald-600 text-sm" x-text="'৳' + Number(selectedWithdrawal?.amount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">পেমেন্ট মেথড:</span>
                        <span class="font-bold uppercase font-mono text-indigo-600" x-text="selectedWithdrawal?.payment_method"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">অ্যাকাউন্ট নম্বর:</span>
                        <span class="font-bold font-mono text-slate-800" x-text="selectedWithdrawal?.payment_details"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        এডমিন নোট / ট্রানজেকশন রেফারেন্স (Admin Note)
                    </label>
                    <textarea name="admin_note" rows="3" x-model="adminNote"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                              placeholder="e.g. bKash TrxID: 9X82KJLS, টাকা সফলভাবে পাঠানো হয়েছে..."></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">এই নোটটি মডারেটর তার "আমার একাউন্ট" ড্যাশবোর্ডে দেখতে পাবেন।</p>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">
                        বাতিল
                    </button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 rounded-xl shadow-sm cursor-pointer">
                        হ্যাঁ, অ্যাপ্রুভ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Reject Modal With Note ─────────────────────────────────── --}}
    <div x-show="rejectModalOpen" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="rejectModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col" @click.outside="rejectModalOpen = false">
            <div class="p-5 border-b border-slate-100 bg-rose-50 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-rose-900 font-black text-sm sm:text-base">
                    <i class="fas fa-times-circle text-rose-600 text-lg"></i>
                    <span>উইথড্র রিকোয়েস্ট বাতিল করুন</span>
                </div>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'/admin/moderators/withdrawals/' + (selectedWithdrawal ? selectedWithdrawal.id : '') + '/reject'" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">মডারেটর:</span>
                        <span class="font-bold text-slate-800" x-text="selectedWithdrawal?.moderator?.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">পরিমাণ:</span>
                        <span class="font-black font-mono text-slate-900" x-text="'৳' + Number(selectedWithdrawal?.amount || 0).toFixed(2)"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        বাতিল করার কারণ / এডমিন নোট (Admin Note) *
                    </label>
                    <textarea name="admin_note" rows="3" required x-model="adminNote"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500"
                              placeholder="বাতিল করার সুনির্দিষ্ট কারণ লিখুন (যেমন: ভুল বিকাশ নম্বর দেওয়া হয়েছে, অনুগ্রহ করে সঠিক নম্বর দিয়ে পুনরায় রিকোয়েস্ট করুন)..."></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">মডারেটর এই কারণটি দেখতে পারবেন এবং টাকা তার ব্যালেন্সে ফেরত যুক্ত থাকবে।</p>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">
                        বাতিল
                    </button>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm px-6 py-2.5 rounded-xl shadow-sm cursor-pointer">
                        রিকোয়েস্ট বাতিল করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
