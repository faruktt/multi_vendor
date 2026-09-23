@extends('layouts.app')
@section('title', 'User Activity & Traffic Analytics')
@section('heading', 'User Activity')

@section('content')
<div class="space-y-5" x-data="{ clearModal: false }">

    {{-- ══ TOP HEADER & ACTIONS ══ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div>
            <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm shadow-sm">
                    <i class="fas fa-chart-line"></i>
                </span>
                User Activity &amp; Visitor Hits
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Monitor live traffic, total visits, and page-by-page hit counts across your website
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->user()?->hasRole('super-admin'))
            <button type="button" @click="clearModal = true"
                    class="px-3 py-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-trash-can text-[11px]"></i>
                <span>Clear Logs</span>
            </button>
            @endif
        </div>
    </div>

    {{-- ══ STAT CARDS ══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        {{-- Total All-Time Hits --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Page Hits</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-arrow-pointer"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-800">{{ number_format($totalAllTimeHits) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">All-time views</p>
        </div>

        {{-- Today Hits --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Today's Hits</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-calendar-day"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-blue-600">{{ number_format($totalTodayHits) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Hits recorded today</p>
        </div>

        {{-- Unique Visitors All Time --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unique Visitors</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-users"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-emerald-600">{{ number_format($totalAllTimeVisitors) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Distinct IP addresses</p>
        </div>

        {{-- Today's Unique Visitors --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Today's Visitors</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-user-check"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-amber-600">{{ number_format($todayVisitors) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Unique visitors today</p>
        </div>

        {{-- Filtered Results Count --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Filtered Hits</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fas fa-filter"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-purple-600">{{ number_format($filteredHits) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Matching current filters</p>
        </div>
    </div>

    {{-- ══ FILTERS BAR ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.user-activity.index') }}" class="flex flex-wrap gap-2.5 items-center">
            
            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search page URL, name, IP..."
                       class="w-full border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 placeholder-slate-400">
            </div>

            {{-- Date Preset --}}
            <select name="preset" onchange="this.form.submit()"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white font-medium">
                <option value="all_time"    {{ request('preset', $preset) === 'all_time' ? 'selected' : '' }}>All Time</option>
                <option value="today"       {{ request('preset', $preset) === 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday"   {{ request('preset') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="last_7_days" {{ request('preset') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="this_month"  {{ request('preset') === 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month"  {{ request('preset') === 'last_month' ? 'selected' : '' }}>Last Month</option>
            </select>

            {{-- Custom From/To --}}
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <input type="date" name="from" value="{{ request('from', $from) }}"
                       class="border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white">
                <span class="text-slate-400">to</span>
                <input type="date" name="to" value="{{ request('to', $to) }}"
                       class="border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white">
            </div>

            {{-- Device Filter --}}
            <select name="device" onchange="this.form.submit()"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white font-medium">
                <option value="">All Devices</option>
                <option value="Mobile"  {{ request('device') === 'Mobile' ? 'selected' : '' }}>Mobile</option>
                <option value="Desktop" {{ request('device') === 'Desktop' ? 'selected' : '' }}>Desktop</option>
                <option value="Tablet"  {{ request('device') === 'Tablet' ? 'selected' : '' }}>Tablet</option>
            </select>

            {{-- User Type Filter --}}
            <select name="user_type" onchange="this.form.submit()"
                    class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white font-medium">
                <option value="">All User Types</option>
                <option value="guest"    {{ request('user_type') === 'guest' ? 'selected' : '' }}>Guest Visitors</option>
                <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>Customers</option>
                <option value="reseller" {{ request('user_type') === 'reseller' ? 'selected' : '' }}>Resellers</option>
                <option value="supplier" {{ request('user_type') === 'supplier' ? 'selected' : '' }}>Suppliers</option>
                <option value="admin"    {{ request('user_type') === 'admin' ? 'selected' : '' }}>Admin / Staff</option>
            </select>

            {{-- Submit & Reset --}}
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-blue-500/20">
                <i class="fas fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['search', 'preset', 'from', 'to', 'device', 'user_type']))
            <a href="{{ route('admin.user-activity.index') }}"
               class="px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ══ 7-DAY TRAFFIC TREND & PLATFORM BREAKDOWN ══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Trend Chart/Bars --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                    <i class="fas fa-chart-simple text-blue-500"></i> Last 7 Days Traffic Trend
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">Daily Hits &amp; Visitors</span>
            </div>

            @php
                $maxHit = max(1, ...array_column($trendDays, 'hits'));
            @endphp

            <div class="grid grid-cols-7 gap-2 h-36 items-end pt-4 pb-2 border-b border-slate-100">
                @foreach($trendDays as $td)
                @php
                    $pct = round(($td['hits'] / $maxHit) * 100);
                @endphp
                <div class="flex flex-col items-center gap-1 h-full justify-end group relative">
                    {{-- Tooltip --}}
                    <div class="opacity-0 group-hover:opacity-100 pointer-events-none absolute -top-8 bg-slate-800 text-white text-[10px] px-2 py-0.5 rounded shadow whitespace-nowrap z-20 transition-opacity">
                        {{ $td['hits'] }} hits ({{ $td['visitors'] }} unique)
                    </div>

                    <span class="text-[10px] font-bold text-slate-600">{{ $td['hits'] }}</span>
                    <div class="w-full max-w-[28px] rounded-t-lg bg-gradient-to-t from-blue-600 to-indigo-400 transition-all duration-300 group-hover:from-blue-700 group-hover:to-indigo-500"
                         style="height: {{ max(10, $pct) }}%;"></div>
                    <span class="text-[10px] text-slate-400 truncate w-full text-center">{{ explode(',', $td['label'])[0] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Device Distribution Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                    <i class="fas fa-mobile-screen-button text-cyan-500"></i> Device &amp; Visitor Types
                </h3>

                {{-- Device percentages --}}
                <div class="space-y-3">
                    @php
                        $devTotal = max(1, array_sum($deviceStats));
                    @endphp
                    @foreach(['Mobile' => ['icon' => 'fa-mobile-screen', 'color' => 'bg-emerald-500'], 'Desktop' => ['icon' => 'fa-laptop', 'color' => 'bg-blue-500'], 'Tablet' => ['icon' => 'fa-tablet-screen-button', 'color' => 'bg-purple-500']] as $dName => $dMeta)
                    @php
                        $dCount = $deviceStats[$dName] ?? 0;
                        $dPct   = round(($dCount / $devTotal) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-slate-600 font-semibold flex items-center gap-1.5">
                                <i class="fas {{ $dMeta['icon'] }} text-slate-400 text-[11px]"></i> {{ $dName }}
                            </span>
                            <span class="font-bold text-slate-800">{{ $dCount }} <span class="text-slate-400 font-normal">({{ $dPct }}%)</span></span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $dMeta['color'] }} rounded-full" style="width: {{ $dPct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span>Guests: <strong>{{ number_format($userTypeStats['guest'] ?? 0) }}</strong></span>
                    <span>Customers: <strong>{{ number_format($userTypeStats['customer'] ?? 0) }}</strong></span>
                    <span>Resellers: <strong>{{ number_format($userTypeStats['reseller'] ?? 0) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ SECTION 1: PAGE HITS BREAKDOWN (MAIN USER REQUEST) ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gradient-to-r from-slate-50/70 to-white">
            <div>
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    Page Hits Breakdown
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Total hit count and unique visitor reach for each page on your website
                </p>
            </div>
            <div class="text-xs text-slate-400 font-mono">
                Showing {{ $pageBreakdown->total() }} tracked pages
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3">Page Name / Title</th>
                        <th class="px-4 py-3">Page URL Path</th>
                        <th class="px-4 py-3 text-right">Total Hits</th>
                        <th class="px-4 py-3 text-right">Unique Visitors</th>
                        <th class="px-4 py-3 text-right">Traffic Share</th>
                        <th class="px-4 py-3 text-right">Last Visit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @php
                        $maxPageHits = max(1, $pageBreakdown->max('total_hits') ?? 1);
                        $filteredSum = max(1, $filteredHits);
                    @endphp

                    @forelse($pageBreakdown as $idx => $p)
                    @php
                        $pctOfTotal = round(($p->total_hits / $filteredSum) * 100, 1);
                        $barWidth   = round(($p->total_hits / $maxPageHits) * 100);
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Rank --}}
                        <td class="px-4 py-3 text-center text-slate-400 font-bold">
                            @if($pageBreakdown->firstItem() + $idx === 1)
                                <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black inline-flex items-center justify-center">1</span>
                            @elseif($pageBreakdown->firstItem() + $idx === 2)
                                <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-black inline-flex items-center justify-center">2</span>
                            @elseif($pageBreakdown->firstItem() + $idx === 3)
                                <span class="w-5 h-5 rounded-full bg-amber-50 text-amber-800 text-[10px] font-black inline-flex items-center justify-center">3</span>
                            @else
                                {{ $pageBreakdown->firstItem() + $idx }}
                            @endif
                        </td>

                        {{-- Page Name --}}
                        <td class="px-4 py-3 font-bold text-slate-900">
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fas fa-file-lines text-slate-400 text-xs"></i>
                                {{ $p->page_name ?? 'Website Page' }}
                            </span>
                        </td>

                        {{-- URL Path --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 font-mono text-[11px] text-slate-600">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 max-w-xs truncate" title="{{ $p->path }}">
                                    {{ $p->path }}
                                </span>
                                <a href="{{ url($p->path) }}" target="_blank" class="text-blue-500 hover:text-blue-700" title="Open Page">
                                    <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            </div>
                        </td>

                        {{-- Total Hits --}}
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-black text-slate-900">{{ number_format($p->total_hits) }}</span>
                            <span class="text-[10px] text-slate-400 block">hits</span>
                        </td>

                        {{-- Unique Visitors --}}
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                            {{ number_format($p->unique_hits) }}
                            <span class="text-[10px] text-slate-400 block font-normal">visitors</span>
                        </td>

                        {{-- Traffic Share --}}
                        <td class="px-4 py-3 text-right">
                            <div class="w-24 ml-auto">
                                <div class="text-[10px] font-bold text-slate-600 mb-0.5">{{ $pctOfTotal }}%</div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ $barWidth }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Last Visit --}}
                        <td class="px-4 py-3 text-right text-slate-500 text-[11px] whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($p->last_visited_at)->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            <i class="fas fa-chart-line text-2xl text-slate-300 mb-2 block"></i>
                            No page hits recorded for this period. Visit the website to start tracking traffic!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pageBreakdown->hasPages())
        <div class="p-3 border-t border-slate-100 bg-slate-50/50">
            {{ $pageBreakdown->links() }}
        </div>
        @endif
    </div>

    {{-- ══ SECTION 2: RECENT ACTIVITY STREAM (LIVE VISITOR HITS) ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50/70 to-white">
            <div>
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-xs">
                        <i class="fas fa-bolt"></i>
                    </span>
                    Recent Visitor Hit Log
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Real-time chronological feed of visitors hitting pages on your website
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                Live Feed
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Page Visited</th>
                        <th class="px-4 py-3">Visitor Type</th>
                        <th class="px-4 py-3">IP Address</th>
                        <th class="px-4 py-3">Device &amp; Browser</th>
                        <th class="px-4 py-3">Referrer Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($recentVisits as $visit)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Time --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-slate-800 font-bold">{{ $visit->created_at->format('h:i:s A') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $visit->created_at->format('d M Y') }} ({{ $visit->created_at->diffForHumans() }})</div>
                        </td>

                        {{-- Page --}}
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-900">{{ $visit->page_name ?? 'Page' }}</div>
                            <a href="{{ url($visit->path) }}" target="_blank"
                               class="font-mono text-[10.5px] text-blue-600 hover:underline inline-flex items-center gap-1 max-w-xs truncate">
                                {{ $visit->path }}
                                <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                            </a>
                        </td>

                        {{-- User Type Badge --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($visit->user_type === 'customer')
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[10.5px] border border-blue-200">Customer</span>
                            @elseif($visit->user_type === 'reseller')
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-bold text-[10.5px] border border-purple-200">Reseller</span>
                            @elseif($visit->user_type === 'supplier')
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-[10.5px] border border-emerald-200">Supplier</span>
                            @elseif($visit->user_type === 'admin')
                                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold text-[10.5px] border border-rose-200">Admin Staff</span>
                            @else
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-semibold text-[10.5px]">Guest</span>
                            @endif
                        </td>

                        {{-- IP Address --}}
                        <td class="px-4 py-3 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                            <i class="fas fa-network-wired text-slate-400 text-[10px] mr-1"></i>
                            {{ $visit->ip_address ?? '—' }}
                        </td>

                        {{-- Device & Browser --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1.5 text-slate-800 font-semibold">
                                @if($visit->device === 'Mobile')
                                    <i class="fas fa-mobile-screen text-emerald-500"></i>
                                @elseif($visit->device === 'Tablet')
                                    <i class="fas fa-tablet-screen-button text-purple-500"></i>
                                @else
                                    <i class="fas fa-laptop text-blue-500"></i>
                                @endif
                                <span>{{ $visit->device }} &bull; {{ $visit->platform }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ $visit->browser }}
                            </div>
                        </td>

                        {{-- Referrer --}}
                        <td class="px-4 py-3 text-[11px] text-slate-500 max-w-xs truncate">
                            @if(!empty($visit->referer))
                                <span class="truncate block" title="{{ $visit->referer }}">
                                    <i class="fas fa-link text-slate-400 text-[10px] mr-1"></i>
                                    {{ parse_url($visit->referer, PHP_URL_HOST) ?? $visit->referer }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Direct Visit</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                            No visitor hits recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentVisits->hasPages())
        <div class="p-3 border-t border-slate-100 bg-slate-50/50">
            {{ $recentVisits->links() }}
        </div>
        @endif
    </div>

    {{-- ══ CLEAR LOGS CONFIRMATION MODAL ══ --}}
    <div x-show="clearModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 text-center"
             @click.outside="clearModal = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">Clear All User Activity Logs?</h3>
            <p class="text-xs text-slate-500 mb-6">
                This action will truncate the user activity history. Page hit statistics will be reset. This cannot be undone.
            </p>
            <div class="flex items-center justify-center gap-2">
                <button type="button" @click="clearModal = false"
                        class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-bold transition">
                    Cancel
                </button>
                <form method="POST" action="{{ route('admin.user-activity.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm shadow-rose-500/20">
                        Yes, Clear Logs
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
