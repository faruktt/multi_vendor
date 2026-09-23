@extends('layouts.app')
@section('title', 'Moderator Management')
@section('heading', 'Moderator Management')

@section('content')
<div class="py-4 space-y-6" x-data="moderatorAdminManager()">

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
                @if(isset($pendingWithdrawals) && $pendingWithdrawals > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ $pendingWithdrawals }}</span>
                @endif
            </a>
        </div>

        {{-- Add Moderator Button --}}
        <button type="button" @click="openCreateModal()"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer hover:scale-105 active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Add Moderator (নতুন মডারেটর)</span>
        </button>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Moderators</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($totalCount) }}</div>
            <div class="text-xs text-slate-400 mt-1">সর্বমোট নিবন্ধিত মডারেটর</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Accounts</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($activeCount) }}</div>
            <div class="text-xs text-slate-400 mt-1">সক্রিয় মডারেটর অ্যাকাউন্ট</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Currently Working Now</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <span>{{ number_format($workingNow) }}</span>
                @if($workingNow > 0)
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                @endif
            </div>
            <div class="text-xs text-slate-400 mt-1">বর্তমানে শিফটে কাজ করছেন</div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.moderators.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="মডারেটরের নাম, ইমেইল অথবা ফোন নম্বর দিয়ে খুঁজুন..."
                       class="w-full h-10 border border-slate-200 rounded-xl px-4 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>
            <div>
                <select name="status" class="h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="">All Status (সকল)</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (সক্রিয়)</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive (নিষ্ক্রিয়)</option>
                </select>
            </div>
            <button type="submit" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-colors cursor-pointer">
                <i class="fas fa-search mr-1.5"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.moderators.index') }}" class="h-10 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Moderators Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">মডারেটর তথ্য</th>
                        <th class="px-5 py-3.5">ফোন ও ঠিকানা</th>
                        <th class="px-5 py-3.5 text-center">রেট (৳/মি.)</th>
                        <th class="px-5 py-3.5 text-center">NID ডকুমেন্টস</th>
                        <th class="px-5 py-3.5 text-center">ডিউটি স্ট্যাটাস</th>
                        <th class="px-5 py-3.5 text-center">মোট সময়</th>
                        <th class="px-5 py-3.5 text-center">স্ট্যাটাস</th>
                        <th class="px-5 py-3.5 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($moderators as $m)
                    @php
                        $activeSess = $m->activeSession();
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Name & Email --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($m->image_url)
                                    <img src="{{ $m->image_url }}" alt="{{ $m->name }}"
                                         class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs flex-shrink-0 cursor-pointer"
                                         @click="previewImage('{{ $m->image_url }}', '{{ $m->name }} (Profile Photo)')">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-sm shadow-sm shadow-indigo-200 flex-shrink-0">
                                        {{ strtoupper(substr($m->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-extrabold text-slate-800 text-sm">{{ $m->name }}</div>
                                    <div class="text-slate-400 text-[11px] font-mono">{{ $m->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Contact & Address --}}
                        <td class="px-5 py-4">
                            <div>
                                <span class="font-semibold text-slate-700 block font-mono">{{ $m->phone ?: 'ফোন নম্বর নেই' }}</span>
                                <span class="text-slate-400 text-[11px] block truncate max-w-xs" title="{{ $m->address }}">
                                    {{ $m->address ?: 'ঠিকানা দেওয়া হয়নি' }}
                                </span>
                            </div>
                        </td>

                        {{-- Rate per minute --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-black font-mono text-xs border border-emerald-200">
                                ৳{{ number_format($m->rate_per_minute ?? 0, 2) }}
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">প্রতি মিনিটে</span>
                        </td>

                        {{-- NID & Documents --}}
                        <td class="px-5 py-4 text-center">
                            <div class="flex flex-col items-center gap-1 text-[10px]">
                                {{-- Moderator NID --}}
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-400 font-semibold">NID:</span>
                                    @if($m->nid_front_url)
                                        <button type="button" @click="previewImage('{{ $m->nid_front_url }}', '{{ $m->name }} - Moderator NID Front')"
                                                class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                            Front
                                        </button>
                                    @endif
                                    @if($m->nid_back_url)
                                        <button type="button" @click="previewImage('{{ $m->nid_back_url }}', '{{ $m->name }} - Moderator NID Back')"
                                                class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                            Back
                                        </button>
                                    @endif
                                    @if(!$m->nid_front_url && !$m->nid_back_url)
                                        <span class="text-slate-300">নেই</span>
                                    @endif
                                </div>
                                {{-- Guardian NID --}}
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-400 font-semibold">G-NID:</span>
                                    @if($m->guardian_nid_front_url)
                                        <button type="button" @click="previewImage('{{ $m->guardian_nid_front_url }}', '{{ $m->name }} - Guardian NID Front')"
                                                class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                            Front
                                        </button>
                                    @endif
                                    @if($m->guardian_nid_back_url)
                                        <button type="button" @click="previewImage('{{ $m->guardian_nid_back_url }}', '{{ $m->name }} - Guardian NID Back')"
                                                class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                            Back
                                        </button>
                                    @endif
                                    @if(!$m->guardian_nid_front_url && !$m->guardian_nid_back_url)
                                        <span class="text-slate-300">নেই</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Duty Status (Working now or Off Duty) --}}
                        <td class="px-5 py-4 text-center">
                            @if($activeSess)
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        On Duty (চলমান)
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        শুরু: {{ $activeSess->started_at->timezone('Asia/Dhaka')->format('h:i A') }}
                                    </span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Off Duty
                                </span>
                            @endif
                        </td>

                        {{-- Total Work Hours --}}
                        <td class="px-5 py-4 text-center">
                            <span class="inline-block px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-extrabold text-xs font-mono">
                                {{ $m->formattedTotalWorkTime() }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">({{ $m->total_sessions }} টি শিফট)</span>
                        </td>

                        {{-- Account Status Toggle --}}
                        <td class="px-5 py-4 text-center">
                            <form method="POST" action="{{ route('admin.moderators.toggle', $m) }}">
                                @csrf
                                <button type="submit"
                                        title="Click to toggle status"
                                        class="cursor-pointer transition-all hover:scale-105 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $m->isActive() ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                    <i class="fas {{ $m->isActive() ? 'fa-check-circle' : 'fa-ban' }} text-[9px]"></i>
                                    <span>{{ ucfirst($m->status) }}</span>
                                </button>
                            </form>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- View Reports --}}
                                <a href="{{ route('admin.moderators.reports', ['moderator_id' => $m->id]) }}"
                                   title="কাজের রিপোর্ট দেখুন"
                                   class="w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-indigo-600 flex items-center justify-center transition-colors">
                                    <i class="fas fa-clipboard-list text-xs"></i>
                                </a>

                                {{-- Edit Moderator --}}
                                <button type="button" @click="openEditModal(@js($m))"
                                        title="সম্পাদনা করুন"
                                        class="w-8 h-8 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 flex items-center justify-center transition-colors cursor-pointer">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>

                                {{-- Delete Moderator --}}
                                <form method="POST" action="{{ route('admin.moderators.destroy', $m) }}" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই মডারেটরটি মুছে ফেলতে চান?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="মুছে ফেলুন"
                                            class="w-8 h-8 rounded-lg border border-red-200 bg-white hover:bg-red-50 text-red-600 flex items-center justify-center transition-colors cursor-pointer">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <i class="fas fa-user-shield text-3xl text-slate-300 mb-2 block"></i>
                            <p>কোনো মডারেটর পাওয়া যায়নি। "Add Moderator" বাটনে ক্লিক করে নতুন মডারেটর যুক্ত করুন।</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($moderators->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $moderators->links() }}
        </div>
        @endif
    </div>

    {{-- ── Add Moderator Modal ────────────────────────────────────── --}}
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showCreateModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showCreateModal = false">
            
            <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-violet-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                        <i class="fas fa-user-plus text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">নতুন মডারেটর তৈরি করুন</h3>
                        <p class="text-xs text-slate-500 mt-0.5">মডারেটরের লগইন তথ্য, প্রতি মিনিট রেট, ছবি ও NID ডকুমেন্টস যুক্ত করুন</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.moderators.store') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">পূর্ণ নাম (Full Name) *</label>
                    <input type="text" name="name" required
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g. Shakil Ahmed">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">ইমেইল (লগইনের জন্য) *</label>
                        <input type="email" name="email" required
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="moderator@example.com">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">পাসওয়ার্ড *</label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="কমপক্ষে ৬ অক্ষর">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">ফোন নম্বর</label>
                        <input type="text" name="phone"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="01XXXXXXXXX">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">প্রতি মিনিট রেট (টাকা / মিনিট) *</label>
                        <input type="number" step="0.01" min="0" name="rate_per_minute" value="1.00" required
                               class="w-full text-xs sm:text-sm font-black font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="e.g. 0.50 বা 1.00">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">স্ট্যাটাস</label>
                        <select name="status" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active" selected>Active (সক্রিয়)</option>
                            <option value="inactive">Inactive (নিষ্ক্রিয়)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">ঠিকানা (Address)</label>
                    <textarea name="address" rows="2"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="মডারেটরের ঠিকানা..."></textarea>
                </div>

                {{-- Profile Image --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">প্রোফাইল ছবি (Profile Image)</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-slate-50 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                {{-- Moderator NID Documents Section --}}
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-3">
                    <div class="text-xs font-black text-indigo-900 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-600"></i>
                        <span>মডারেটরের জাতীয় পরিচয়পত্র (Moderator NID Card)</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">NID Front (সামনের অংশ)</label>
                            <input type="file" name="nid_front" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">NID Back (পেছনের অংশ)</label>
                            <input type="file" name="nid_back" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                        </div>
                    </div>
                </div>

                {{-- Guardian NID Documents Section --}}
                <div class="p-4 rounded-2xl bg-teal-50/50 border border-teal-100 space-y-3">
                    <div class="text-xs font-black text-teal-900 flex items-center gap-2">
                        <i class="fas fa-user-shield text-teal-600"></i>
                        <span>অভিভাবকের জাতীয় পরিচয়পত্র (Guardian NID Card)</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Guardian NID Front (সামনের অংশ)</label>
                            <input type="file" name="guardian_nid_front" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Guardian NID Back (পেছনের অংশ)</label>
                            <input type="file" name="guardian_nid_back" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        বাতিল
                    </button>
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold px-6 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-check"></i>
                        <span>সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ── Edit Moderator Modal ──────────────────────────────────── --}}
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showEditModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showEditModal = false">
            
            <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">মডারেটর তথ্য ও ডকুমেন্টস সম্পাদনা</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="editForm.name"></p>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'/admin/moderators/' + editForm.id" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">পূর্ণ নাম (Name) *</label>
                    <input type="text" name="name" x-model="editForm.name" required
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">ইমেইল *</label>
                        <input type="email" name="email" x-model="editForm.email" required
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">নতুন পাসওয়ার্ড (ঐচ্ছিক)</label>
                        <input type="password" name="password" minlength="6"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="পরিবর্তন করতে চাইলে লিখুন">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">ফোন নম্বর</label>
                        <input type="text" name="phone" x-model="editForm.phone"
                               class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">প্রতি মিনিট রেট (টাকা / মিনিট) *</label>
                        <input type="number" step="0.01" min="0" name="rate_per_minute" x-model="editForm.rate_per_minute" required
                               class="w-full text-xs sm:text-sm font-black font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">স্ট্যাটাস</label>
                        <select name="status" x-model="editForm.status" class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active (সক্রিয়)</option>
                            <option value="inactive">Inactive (নিষ্ক্রিয়)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">ঠিকানা (Address)</label>
                    <textarea name="address" x-model="editForm.address" rows="2"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Profile Image --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">প্রোফাইল ছবি</label>
                    <div class="flex items-center gap-3 mb-2" x-show="editForm.imageUrl">
                        <img :src="editForm.imageUrl" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-2xs">
                        <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 font-bold cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                            <span>ছবি মুছে ফেলুন</span>
                        </label>
                    </div>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-slate-50 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                {{-- Moderator NID Documents Section --}}
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-3">
                    <div class="text-xs font-black text-indigo-900 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-600"></i>
                        <span>মডারেটরের জাতীয় পরিচয়পত্র (Moderator NID)</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">NID Front</label>
                            <div class="flex items-center gap-2 mb-1.5" x-show="editForm.nid_front_url">
                                <img :src="editForm.nid_front_url" class="w-12 h-8 rounded object-cover border">
                                <label class="text-[10px] text-rose-600 font-bold cursor-pointer">
                                    <input type="checkbox" name="remove_nid_front" value="1" class="rounded text-rose-600"> মুছে ফেলুন
                                </label>
                            </div>
                            <input type="file" name="nid_front" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">NID Back</label>
                            <div class="flex items-center gap-2 mb-1.5" x-show="editForm.nid_back_url">
                                <img :src="editForm.nid_back_url" class="w-12 h-8 rounded object-cover border">
                                <label class="text-[10px] text-rose-600 font-bold cursor-pointer">
                                    <input type="checkbox" name="remove_nid_back" value="1" class="rounded text-rose-600"> মুছে ফেলুন
                                </label>
                            </div>
                            <input type="file" name="nid_back" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                        </div>
                    </div>
                </div>

                {{-- Guardian NID Documents Section --}}
                <div class="p-4 rounded-2xl bg-teal-50/50 border border-teal-100 space-y-3">
                    <div class="text-xs font-black text-teal-900 flex items-center gap-2">
                        <i class="fas fa-user-shield text-teal-600"></i>
                        <span>অভিভাবকের জাতীয় পরিচয়পত্র (Guardian NID)</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Guardian NID Front</label>
                            <div class="flex items-center gap-2 mb-1.5" x-show="editForm.guardian_nid_front_url">
                                <img :src="editForm.guardian_nid_front_url" class="w-12 h-8 rounded object-cover border">
                                <label class="text-[10px] text-rose-600 font-bold cursor-pointer">
                                    <input type="checkbox" name="remove_guardian_nid_front" value="1" class="rounded text-rose-600"> মুছে ফেলুন
                                </label>
                            </div>
                            <input type="file" name="guardian_nid_front" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-1">Guardian NID Back</label>
                            <div class="flex items-center gap-2 mb-1.5" x-show="editForm.guardian_nid_back_url">
                                <img :src="editForm.guardian_nid_back_url" class="w-12 h-8 rounded object-cover border">
                                <label class="text-[10px] text-rose-600 font-bold cursor-pointer">
                                    <input type="checkbox" name="remove_guardian_nid_back" value="1" class="rounded text-rose-600"> মুছে ফেলুন
                                </label>
                            </div>
                            <input type="file" name="guardian_nid_back" accept="image/*"
                                   class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 bg-white file:mr-2 file:py-0.5 file:px-2 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700">
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        বাতিল
                    </button>
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold px-6 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-save"></i>
                        <span>আপডেট করুন</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ── Image Lightbox Preview Modal ───────────────────────────── --}}
    <div x-show="previewModalOpen" x-cloak
         class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="previewModalOpen = false">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col" @click.outside="previewModalOpen = false">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <span class="text-xs font-bold text-slate-800 truncate" x-text="previewTitle"></span>
                <button type="button" @click="previewModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-slate-900/5 max-h-[75vh] overflow-auto">
                <img :src="previewSrc" :alt="previewTitle" class="max-w-full max-h-[70vh] rounded-xl object-contain shadow-md">
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 flex justify-between items-center text-xs">
                <a :href="previewSrc" target="_blank" class="text-indigo-600 hover:underline font-bold flex items-center gap-1">
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                    <span>আসল সাইজে দেখুন</span>
                </a>
                <button type="button" @click="previewModalOpen = false" class="px-4 py-1.5 rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300">
                    বন্ধ করুন
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function moderatorAdminManager() {
    return {
        showCreateModal: false,
        showEditModal: false,
        previewModalOpen: false,
        previewSrc: '',
        previewTitle: '',

        editForm: {
            id: null,
            name: '',
            email: '',
            phone: '',
            address: '',
            rate_per_minute: '1.00',
            imageUrl: null,
            nid_front_url: null,
            nid_back_url: null,
            guardian_nid_front_url: null,
            guardian_nid_back_url: null,
            status: 'active'
        },

        openCreateModal() {
            this.showCreateModal = true;
        },

        openEditModal(moderator) {
            this.editForm = {
                id: moderator.id,
                name: moderator.name || '',
                email: moderator.email || '',
                phone: moderator.phone || '',
                address: moderator.address || '',
                rate_per_minute: moderator.rate_per_minute ? Number(moderator.rate_per_minute).toFixed(2) : '1.00',
                imageUrl: moderator.image_url || null,
                nid_front_url: moderator.nid_front_url || null,
                nid_back_url: moderator.nid_back_url || null,
                guardian_nid_front_url: moderator.guardian_nid_front_url || null,
                guardian_nid_back_url: moderator.guardian_nid_back_url || null,
                status: moderator.status || 'active'
            };
            this.showEditModal = true;
        },

        previewImage(url, title) {
            if (!url) return;
            this.previewSrc = url;
            this.previewTitle = title || 'Document Preview';
            this.previewModalOpen = true;
        }
    };
}
</script>
@endpush
