@extends('layouts.app')
@section('title', 'IP Block & Login Security')
@section('heading', 'IP Block & Login Security')

@section('content')
<div class="py-4 space-y-6" x-data="{
    activeTab: 'blocked',
    showModal: false,
    manualIp: '',
    manualReason: '',
    openBlockModal(ip = '', reason = '') {
        this.manualIp = ip;
        this.manualReason = reason;
        this.showModal = true;
    }
}">

    {{-- ── Top Hero / Banner ── --}}
    <div class="bg-gradient-to-r from-slate-900 via-rose-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl border border-rose-900/50 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-13 h-13 p-3.5 rounded-2xl bg-rose-500/20 border border-rose-400/30 flex items-center justify-center flex-shrink-0 shadow-inner">
                    <i class="fas fa-shield-halved text-rose-400 text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-black tracking-tight text-white">IP Block &amp; Security Shield</h2>
                        <span class="px-2.5 py-0.5 text-[10.5px] font-extrabold uppercase rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            Firewall &amp; Brute-Force Defense
                        </span>
                    </div>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        If an IP fails admin login 2 consecutive times, an instant security alert is emailed to 
                        <strong class="text-rose-200">({{ \App\Helpers\AppSetting::get('email') ?: 'ebsbazarbd@gmail.com' }})</strong> 
                        with a 1-click block link. Manage blocked IPs, monitor login attempts, or ban suspicious addresses below.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 w-full md:w-auto flex-wrap">
                <button type="button" @click="openBlockModal()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-xs font-black text-white transition-all shadow-lg shadow-rose-600/30">
                    <i class="fas fa-ban"></i>
                    <span>Block New IP</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
            <i class="fas fa-circle-exclamation text-rose-600 text-lg"></i>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-2 font-bold text-sm mb-1 text-rose-700">
                <i class="fas fa-triangle-exclamation"></i> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-600">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Summary KPI Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Blocked --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Block Records</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalBlocked) }}</h3>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 inline-block">All recorded IP blocks</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-xl">
                <i class="fas fa-database"></i>
            </div>
        </div>

        {{-- Active Blocks --}}
        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-rose-500">Currently Active Blocks</p>
                <h3 class="text-2xl font-black text-rose-600 mt-1">{{ number_format($activeBlocked) }}</h3>
                <span class="text-[11px] text-rose-500/80 font-medium mt-0.5 inline-block">Active firewall bans</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fas fa-ban"></i>
            </div>
        </div>

        {{-- Recent Failed Attempts (24h) --}}
        <div class="bg-white rounded-2xl p-5 border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">24h Failed Logins</p>
                <h3 class="text-2xl font-black text-amber-600 mt-1">{{ number_format($recentFails) }}</h3>
                <span class="text-[11px] text-amber-600/80 font-medium mt-0.5 inline-block">Failed attempts in 24 hrs</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </div>

        {{-- Security Alert Destination --}}
        <div class="bg-white rounded-2xl p-5 border border-blue-100 shadow-sm flex items-center justify-between">
            <div class="overflow-hidden pr-2">
                <p class="text-xs font-bold uppercase tracking-wider text-blue-500">Alert Destination Email</p>
                <h3 class="text-sm font-black text-slate-800 mt-1 truncate" title="{{ \App\Helpers\AppSetting::get('email') ?: 'ebsbazarbd@gmail.com' }}">
                    {{ \App\Helpers\AppSetting::get('email') ?: 'ebsbazarbd@gmail.com' }}
                </h3>
                <span class="text-[11px] text-blue-600/80 font-medium mt-0.5 inline-block">Security notification recipient</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-envelope-shield"></i>
            </div>
        </div>
    </div>

    {{-- ── Main Tab Navigation ── --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 pt-4 bg-slate-50/50 flex-wrap gap-3">
            <div class="flex gap-2">
                <button type="button" @click="activeTab = 'blocked'"
                        :class="activeTab === 'blocked' ? 'border-rose-600 text-rose-600 bg-white shadow-sm font-extrabold' : 'border-transparent text-slate-600 hover:text-slate-800 font-semibold'"
                        class="px-5 py-3 border-b-2 text-xs sm:text-sm rounded-t-xl transition-all flex items-center gap-2">
                    <i class="fas fa-ban text-xs"></i>
                    <span>Blocked IP List</span>
                    <span class="px-2 py-0.5 text-[10px] rounded-full"
                          :class="activeTab === 'blocked' ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-600'">
                        {{ $totalBlocked }}
                    </span>
                </button>

                <button type="button" @click="activeTab = 'attempts'"
                        :class="activeTab === 'attempts' ? 'border-blue-600 text-blue-600 bg-white shadow-sm font-extrabold' : 'border-transparent text-slate-600 hover:text-slate-800 font-semibold'"
                        class="px-5 py-3 border-b-2 text-xs sm:text-sm rounded-t-xl transition-all flex items-center gap-2">
                    <i class="fas fa-list-check text-xs"></i>
                    <span>Recent Login Attempts</span>
                    <span class="px-2 py-0.5 text-[10px] rounded-full"
                          :class="activeTab === 'attempts' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600'">
                        {{ count($recentAttempts) }}
                    </span>
                </button>
            </div>

            {{-- Quick action indicator --}}
            <div class="text-[11px] text-slate-500 font-medium pb-2">
                <i class="fas fa-clock-rotate-left mr-1 text-slate-400"></i> Auto-refreshed with real-time request tracking
            </div>
        </div>

        {{-- ── TAB 1: BLOCKED IPS ── --}}
        <div x-show="activeTab === 'blocked'" class="p-6 space-y-4">
            {{-- Filter & Search Bar --}}
            <form method="GET" action="{{ route('admin.ip-blocks.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="flex flex-1 gap-2 w-full sm:w-auto">
                    <div class="relative flex-1">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Search IP, Reason, Location or Email..."
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition-all">
                    </div>

                    <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Blocks Only</option>
                        <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Unblocked Only</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition-all">
                        Filter
                    </button>

                    @if($search || $statusFilter !== 'all')
                        <a href="{{ route('admin.ip-blocks.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-bold tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">IP Address</th>
                            <th class="py-3 px-4">Location &amp; Network</th>
                            <th class="py-3 px-4">Reason / Trigger</th>
                            <th class="py-3 px-4">Attempted Email</th>
                            <th class="py-3 px-4">Blocked Date</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($blockedIps as $record)
                        <tr class="hover:bg-slate-50/80 transition-colors {{ !$record->is_active ? 'opacity-70 bg-slate-50/40' : '' }}">
                            {{-- IP --}}
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 select-all">
                                        {{ $record->ip_address }}
                                    </span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $record->ip_address }}')"
                                            title="Copy IP" class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <i class="far fa-copy text-[11px]"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Location --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-slate-600">
                                    <i class="fas fa-location-dot text-[11px] text-rose-500"></i>
                                    <span>{{ $record->location ?: 'Unknown Location' }}</span>
                                </div>
                            </td>

                            {{-- Reason --}}
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="truncate text-slate-800 font-semibold" title="{{ $record->reason }}">
                                    {{ $record->reason }}
                                </div>
                                @if($record->blockedBy)
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        By: {{ $record->blockedBy->name }}
                                    </div>
                                @endif
                            </td>

                            {{-- Attempted Email --}}
                            <td class="py-3.5 px-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $record->attempted_email ?: '—' }}
                            </td>

                            {{-- Blocked Date --}}
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                <div>{{ $record->blocked_at ? $record->blocked_at->format('d M Y') : '—' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $record->blocked_at ? $record->blocked_at->format('h:i A') : '' }}</div>
                            </td>

                            {{-- Status --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($record->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        <i class="fas fa-circle text-[6px]"></i> Blocked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fas fa-circle-check text-[10px] text-emerald-500"></i> Unblocked
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($record->is_active)
                                        <form method="POST" action="{{ route('admin.ip-blocks.unblock', $record) }}" onsubmit="return confirm('Do you want to UNBLOCK this IP address: {{ $record->ip_address }}?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition-all flex items-center gap-1">
                                                <i class="fas fa-lock-open text-[10px]"></i> Unblock
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.ip-blocks.block', $record) }}" onsubmit="return confirm('Do you want to RE-BLOCK this IP address: {{ $record->ip_address }}?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-all flex items-center gap-1">
                                                <i class="fas fa-ban text-[10px]"></i> Block
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.ip-blocks.destroy', $record) }}" onsubmit="return confirm('Are you sure you want to permanently delete this block record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete Record">
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                                        <i class="fas fa-shield-check"></i>
                                    </div>
                                    <p class="font-bold text-slate-700">No blocked IP addresses found</p>
                                    <p class="text-xs text-slate-400">To block an IP address, click the 'Block New IP' button above.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pt-2">
                {{ $blockedIps->links() }}
            </div>
        </div>

        {{-- ── TAB 2: RECENT LOGIN ATTEMPTS ── --}}
        <div x-show="activeTab === 'attempts'" class="p-6 space-y-4" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-800">Recent Login Attempt Logs</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Shows last 25 login activities with location and user-agent details.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-bold tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Date &amp; Time</th>
                            <th class="py-3 px-4">IP Address</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Attempted Account</th>
                            <th class="py-3 px-4">Browser &amp; User Agent</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($recentAttempts as $log)
                        @php
                            $isAlreadyBlocked = in_array($log->ip_address, $activeBlockedIpAddresses, true);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ !$log->is_success ? 'bg-rose-50/20' : '' }}">
                            {{-- Date --}}
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500">
                                <div>{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $log->created_at->format('h:i:s A') }}</div>
                            </td>

                            {{-- IP --}}
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 select-all">
                                    {{ $log->ip_address }}
                                </span>
                            </td>

                            {{-- Location --}}
                            <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-location-dot text-[11px] text-slate-400"></i>
                                    <span>{{ $log->location ?: 'Unknown' }}</span>
                                </div>
                            </td>

                            {{-- Account --}}
                            <td class="py-3 px-4 font-mono text-slate-700 whitespace-nowrap">
                                {{ $log->email ?: 'Unknown' }}
                            </td>

                            {{-- User Agent --}}
                            <td class="py-3 px-4 max-w-xs text-slate-500">
                                <div class="truncate text-[11px]" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent ?: 'Unknown User-Agent' }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($log->is_success)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-circle-check"></i> Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        <i class="fas fa-xmark"></i> Failed
                                    </span>
                                @endif
                            </td>

                            {{-- Action --}}
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if($isAlreadyBlocked)
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 text-[11px] font-bold inline-flex items-center gap-1">
                                        <i class="fas fa-ban text-[10px]"></i> Blocked
                                    </span>
                                @else
                                    <button type="button"
                                            @click="openBlockModal('{{ $log->ip_address }}', 'Blocked due to suspicious login attempt for account: {{ $log->email }}')"
                                            class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold transition-all shadow-sm flex items-center gap-1 ml-auto">
                                        <i class="fas fa-ban text-[10px]"></i> Block IP
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                No login attempt records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── MANUAL BLOCK MODAL ── --}}
    <div x-show="showModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.away="showModal = false"
             class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Block IP Address</h3>
                        <p class="text-xs text-slate-400">Configure manual IP block</p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.ip-blocks.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        IP Address <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="ip_address" x-model="manualIp" required
                           placeholder="e.g. 103.204.81.42 or 203.0.113.195"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    <p class="text-[11px] text-slate-400 mt-1">Enter a valid IPv4 or IPv6 address.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Block Reason
                    </label>
                    <textarea name="reason" x-model="manualReason" rows="3"
                              placeholder="e.g. Multiple failed login attempts, Brute force attack, Suspicious activity..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 flex items-start gap-2">
                    <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5 flex-shrink-0"></i>
                    <span>Once blocked, any requests from this IP will be immediately denied access (HTTP 403 Forbidden).</span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-lg shadow-rose-600/20 transition-all flex items-center gap-1.5">
                        <i class="fas fa-ban"></i> Confirm &amp; Block IP
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
