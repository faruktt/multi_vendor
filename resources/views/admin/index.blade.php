@extends('layouts.app')
@section('title', 'Admin Overview')
@section('heading', 'Admin Overview')
@php
    $currency = $appSettings['currency'] ?? '৳';
    $presets  = ['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','last_week'=>'Last Week','this_month'=>'This Month','last_month'=>'Last Month','this_year'=>'This Year','all_time'=>'All Time'];
@endphp

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div x-data="adminPage()">

{{-- ══ DATE RANGE BAR ══════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 mb-4">
    <div class="flex flex-wrap gap-2 items-center">
        <div class="flex flex-wrap gap-1.5">
            @foreach($presets as $key => $label)
            <a href="{{ route('admin.index', ['preset' => $key]) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 transition-all
                      {{ $preset === $key && !request()->hasAny(['from','to'])
                         ? 'bg-blue-600 border-blue-600 text-white'
                         : 'border-slate-200 text-slate-500 hover:border-blue-400 hover:text-blue-600 bg-white' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
        <form method="GET" class="flex items-center gap-2 ml-auto flex-wrap">
            <span class="text-xs text-slate-400 font-medium">Custom:</span>
            <input type="date" name="from" value="{{ $from }}"
                   class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <span class="text-slate-300 text-xs">→</span>
            <input type="date" name="to" value="{{ $to }}"
                   class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors">Apply</button>
        </form>
    </div>
    <div class="mt-2 pt-2 border-t border-slate-50 flex items-center gap-2">
        <i class="fas fa-calendar text-slate-400 text-[10px]"></i>
        <p class="text-[11px] text-slate-400">
            Showing: <span class="font-semibold text-slate-600">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </span>
        </p>
    </div>
</div>

{{-- ══ ROW 1: Quick Stats ══════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 text-sm"></i>
            </div>
            @if($revChange !== null)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $revChange >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $revChange >= 0 ? '+' : '' }}{{ $revChange }}%
            </span>
            @endif
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($revenue, 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Period Revenue</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $orders }} orders</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-start justify-between mb-2">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-calendar-day text-amber-600 text-sm"></i>
            </div>
            @if($stats['today_vs_yest'] !== null)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $stats['today_vs_yest'] >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                {{ $stats['today_vs_yest'] >= 0 ? '+' : '' }}{{ $stats['today_vs_yest'] }}%
            </span>
            @endif
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($stats['today_revenue'], 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Today's Revenue</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center mb-2">
            <i class="fas fa-coins text-purple-600 text-sm"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($stats['all_revenue'], 0) }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">All-Time Revenue</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ number_format($stats['all_orders']) }} orders</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center mb-2">
            <i class="fas fa-store text-slate-600 text-sm"></i>
        </div>
        <p class="text-xl font-bold text-slate-800">{{ $stats['total_branches'] }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Branches</p>
        <p class="text-[10.5px] text-slate-400 mt-1">{{ $stats['total_users'] }} staff members</p>
    </div>

</div>

{{-- ══ ROW 1.5: OPERATIONAL ECOSYSTEM & TEAMS (Moderators, Resellers, Couriers, Stock) ══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">

    {{-- ── 1. MODERATORS & LIVE DUTY ───────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-indigo-100/90 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden group hover:border-indigo-300 transition-all">
        <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-indigo-50/60 pointer-events-none"></div>
        <div>
            <div class="flex items-center justify-between mb-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shadow-2xs">
                    <i class="fas fa-user-shield"></i>
                </div>
                @if($moderatorStats['on_duty_count'] > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10.5px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ $moderatorStats['on_duty_count'] }} জন কর্মরত
                    </span>
                @else
                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                        ডিউটিতে নেই
                    </span>
                @endif
            </div>

            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-black text-slate-800 font-mono">{{ $moderatorStats['total'] }}</p>
                <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                    {{ $moderatorStats['active'] }} সক্রিয়
                </span>
            </div>
            <p class="text-[11.5px] font-bold text-slate-700 mt-0.5">মডারেটর টিম ও ডিউটি</p>

            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">আজকের মোট কাজ:</span>
                @php
                    $tMins = round($moderatorStats['today_work_seconds'] / 60);
                    $mH = floor($tMins / 60);
                    $mM = $tMins % 60;
                @endphp
                <span class="font-bold text-slate-800 font-mono">
                    {{ $mH > 0 ? "{$mH}h {$mM}m" : "{$mM}m" }}
                </span>
            </div>
        </div>

        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
            <a href="{{ route('admin.moderators.index') }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-[11px] flex items-center gap-1 hover:underline">
                <span>টিম লিস্ট</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </a>
            <a href="{{ route('admin.moderators.reports') }}" class="text-slate-500 hover:text-slate-800 font-semibold text-[11px] flex items-center gap-1 hover:underline">
                <span>রিপোর্ট দেখুন</span>
                <i class="fas fa-file-alt text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- ── 2. RESELLER NETWORK ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-violet-100/90 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden group hover:border-violet-300 transition-all">
        <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-violet-50/60 pointer-events-none"></div>
        <div>
            <div class="flex items-center justify-between mb-2.5">
                <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold text-sm shadow-2xs">
                    <i class="fas fa-handshake"></i>
                </div>
                @if($resellerStats['pending'] > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        {{ $resellerStats['pending'] }} পেন্ডিং
                    </span>
                @else
                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                        সব অনুমোদিত
                    </span>
                @endif
            </div>

            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-black text-slate-800 font-mono">{{ $resellerStats['total'] }}</p>
                <span class="text-[11px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-md">
                    {{ $resellerStats['active'] }} সক্রিয় রিসেলার
                </span>
            </div>
            <p class="text-[11.5px] font-bold text-slate-700 mt-0.5">রিসেলার নেটওয়ার্ক</p>

            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">মোট রিসেলার সেল:</span>
                <span class="font-bold text-slate-800 font-mono">
                    {{ $currency }}{{ number_format($resellerStats['total_revenue'], 0) }} ({{ $resellerStats['total_orders'] }})
                </span>
            </div>
        </div>

        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
            <a href="{{ route('admin.resellers.index') }}" class="text-violet-600 hover:text-violet-800 font-bold text-[11px] flex items-center gap-1 hover:underline">
                <span>রিসেলার তালিকা</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
            </a>
            <a href="{{ route('admin.resellers.orders') }}" class="text-slate-500 hover:text-slate-800 font-semibold text-[11px] flex items-center gap-1 hover:underline">
                <span>অর্ডার সমূহ</span>
                <i class="fas fa-shopping-bag text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- ── 3. COURIER LOGISTICS ────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-sky-100/90 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden group hover:border-sky-300 transition-all">
        <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-sky-50/60 pointer-events-none"></div>
        <div>
            <div class="flex items-center justify-between mb-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm shadow-2xs">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                    {{ $courierStats['active_count'] }} কুরিয়ার কানেক্টেড
                </span>
            </div>

            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-black text-slate-800 font-mono">{{ $courierStats['total_parcels'] }}</p>
                <span class="text-[11px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md">
                    মোট পার্সেল
                </span>
            </div>
            <p class="text-[11.5px] font-bold text-slate-700 mt-0.5">কুরিয়ার ডেলিভারি ও পার্সেল</p>

            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">আজ পাঠানো হয়েছে:</span>
                <span class="font-bold text-emerald-600 font-mono">
                    {{ $courierStats['today_parcels'] }} টি পার্সেল
                </span>
            </div>
        </div>

        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
            <a href="{{ route('admin.couriers.index') }}" class="text-sky-600 hover:text-sky-800 font-bold text-[11px] flex items-center gap-1 hover:underline">
                <span>API সেটিংস</span>
                <i class="fas fa-cog text-[9px]"></i>
            </a>
            <a href="{{ route('admin.all-sales') }}" class="text-slate-500 hover:text-slate-800 font-semibold text-[11px] flex items-center gap-1 hover:underline">
                <span>সেলস ও কুরিয়ার</span>
                <i class="fas fa-paper-plane text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- ── 4. INVENTORY & STOCK ALERT ──────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-amber-100/90 shadow-sm p-4 flex flex-col justify-between relative overflow-hidden group hover:border-amber-300 transition-all">
        <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-amber-50/60 pointer-events-none"></div>
        <div>
            <div class="flex items-center justify-between mb-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shadow-2xs">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                @if($inventoryStats['out_of_stock'] > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                        {{ $inventoryStats['out_of_stock'] }} স্টক আউট
                    </span>
                @elseif($inventoryStats['low_stock'] > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        {{ $inventoryStats['low_stock'] }} লো স্টক
                    </span>
                @else
                    <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                        স্টক স্বাস্থ্য ভালো
                    </span>
                @endif
            </div>

            <div class="flex items-baseline gap-2">
                <p class="text-2xl font-black text-slate-800 font-mono">{{ number_format($inventoryStats['total_products']) }}</p>
                <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                    প্রোডাক্ট আইটেম
                </span>
            </div>
            <p class="text-[11.5px] font-bold text-slate-700 mt-0.5">ইনভেন্টরি ও স্টক অ্যালার্ট</p>

            <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">মোট স্টক ভ্যালু:</span>
                <span class="font-bold text-slate-800 font-mono">
                    {{ $currency }}{{ number_format($inventoryStats['stock_value'], 0) }}
                </span>
            </div>
        </div>

        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-[11px] text-slate-400">
                <i class="fas fa-users text-[10px] mr-0.5"></i> {{ $inventoryStats['total_customers'] }} কাস্টমার
            </span>
            <span class="text-[11px] text-emerald-600 font-bold">
                <i class="fas fa-cart-shopping text-[10px] mr-0.5"></i> আজ {{ $inventoryStats['today_orders'] }} অর্ডার
            </span>
        </div>
    </div>

</div>

{{-- ══ ROW 1.6: LIVE DUTY COMMAND CENTER (কারেন্টলি ডিউটিরত মডারেটরদের লাইভ স্ট্যাটাস) ══ --}}
@if($moderatorStats['on_duty_count'] > 0 || $recentWorkLogs->isNotEmpty())
<div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-3xl p-5 mb-4 shadow-md border border-slate-800">
    <div class="flex items-center justify-between flex-wrap gap-2 mb-4 pb-3 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-base">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
            </div>
            <div>
                <h3 class="font-black text-white text-sm sm:text-base flex items-center gap-2">
                    <span>লাইভ ডিউটি ও মডারেটর মনিটরিং</span>
                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ $moderatorStats['on_duty_count'] }} জন এখন কাজ করছেন
                    </span>
                </h3>
                <p class="text-xs text-slate-300">কোন মডারেটর কখন কাজ শুরু করেছেন এবং এই মুহূর্তে কী কাজ করছেন তার রিয়েল-টাইম তথ্য</p>
            </div>
        </div>
        <a href="{{ route('admin.moderators.reports') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all flex items-center gap-1.5 border border-white/10">
            <span>সকল শিফট ও সম্পূর্ণ রিপোর্ট</span>
            <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>

    @if($onDutyModerators->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($onDutyModerators as $duty)
        <div class="bg-white/5 border border-white/10 rounded-2xl p-3.5 space-y-2.5 backdrop-blur-xs">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    @if($duty->moderator?->image_url)
                        <img src="{{ $duty->moderator->image_url }}" alt="{{ $duty->moderator->name }}"
                             class="w-9 h-9 rounded-xl object-cover border border-white/20 shadow-xs flex-shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 flex items-center justify-center text-xs font-black flex-shrink-0">
                            {{ strtoupper(substr($duty->moderator?->name ?? 'M', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="font-bold text-white text-xs">{{ $duty->moderator?->name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ $duty->moderator?->phone ?: $duty->moderator?->email }}</div>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    অন-ডিউটি
                </span>
            </div>

            <div class="flex items-center justify-between text-[11px] bg-black/20 rounded-xl px-3 py-1.5 border border-white/5">
                <span class="text-slate-400">শুরু হয়েছে:</span>
                <span class="font-mono text-emerald-400 font-semibold">{{ $duty->started_at->timezone('Asia/Dhaka')->format('h:i A') }} ({{ $duty->started_at->diffForHumans(null, true) }})</span>
            </div>

            @php
                $latestLog = $duty->logs->first();
            @endphp
            <div class="text-[11px] bg-white/5 rounded-xl p-2.5 border border-white/5">
                <span class="text-[10px] text-indigo-300 font-bold uppercase tracking-wider block mb-1">সর্বশেষ আপডেট:</span>
                @if($latestLog)
                    <p class="text-slate-200 font-medium line-clamp-2">
                        <span class="font-mono text-indigo-300 font-bold mr-1">[{{ $latestLog->log_time->timezone('Asia/Dhaka')->format('h:i A') }}]</span>
                        {{ $latestLog->activity }}
                    </p>
                @else
                    <p class="text-slate-400 italic text-[10.5px]">কাজ শুরু করেছেন, এখনও কোনো আপডেট যোগ করেননি।</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Recent task activities timeline feed --}}
    @if($recentWorkLogs->isNotEmpty())
    <div class="mt-4 pt-3 border-t border-white/10">
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">আজকের সর্বশেষ কাজের আপডেট ফিড (Recent Activity Feed)</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($recentWorkLogs->take(6) as $log)
            <div class="bg-white/5 rounded-xl px-3 py-2 border border-white/5 text-xs flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 truncate">
                    @if($log->moderator?->image_url)
                        <img src="{{ $log->moderator->image_url }}" alt="{{ $log->moderator->name }}"
                             class="w-5 h-5 rounded-full object-cover border border-white/20 flex-shrink-0">
                    @else
                        <div class="w-5 h-5 rounded-full bg-indigo-500/30 text-indigo-200 flex items-center justify-center text-[9px] font-black flex-shrink-0">
                            {{ strtoupper(substr($log->moderator?->name ?? 'M', 0, 1)) }}
                        </div>
                    @endif
                    <span class="font-mono text-[10px] text-emerald-400 font-bold flex-shrink-0">
                        {{ $log->log_time->timezone('Asia/Dhaka')->format('h:i A') }}
                    </span>
                    <span class="font-bold text-indigo-200 flex-shrink-0">{{ $log->moderator?->name }}:</span>
                    <span class="text-slate-300 truncate">{{ $log->activity }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- ══ ROW 2: P&L STATEMENT ════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-4">

    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-blue-600 text-xs"></i>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Financial Statement</h3>
            <span class="text-[11px] text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </span>
        </div>
        @php
            $netColor = $statement['netProfit'] >= 0 ? 'text-emerald-600' : 'text-red-500';
        @endphp
        <p class="text-sm font-bold {{ $netColor }}">
            Net: {{ $statement['netProfit'] >= 0 ? '+' : '' }}{{ $currency }}{{ number_format(abs($statement['netProfit']), 0) }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">

        {{-- ── INCOME SIDE ────────────────────────────────── --}}
        <div class="p-5">
            <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="fas fa-arrow-trend-up text-[10px]"></i> Income
            </p>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-semibold text-slate-700">Sales Revenue</p>
                        <p class="text-[10.5px] text-slate-400">{{ $statement['orders'] }} orders · {{ $currency }}{{ number_format($statement['periodPaid'],0) }} received</p>
                    </div>
                    <p class="text-[13.5px] font-bold text-slate-800">{{ $currency }}{{ number_format($statement['revenue'],0) }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-semibold text-slate-700">Other Income</p>
                        <p class="text-[10.5px] text-slate-400">Finance / misc income</p>
                    </div>
                    <p class="text-[13.5px] font-bold text-slate-800">{{ $currency }}{{ number_format($statement['income'],0) }}</p>
                </div>
                <div class="border-t border-slate-100 pt-2.5 flex items-center justify-between">
                    <p class="text-[12px] font-bold text-emerald-700">Total Income</p>
                    <p class="text-[15px] font-bold text-emerald-600">{{ $currency }}{{ number_format($statement['totalIncome'],0) }}</p>
                </div>
            </div>
        </div>

        {{-- ── COSTS SIDE ─────────────────────────────────── --}}
        <div class="p-5">
            <p class="text-[11px] font-bold text-red-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="fas fa-arrow-trend-down text-[10px]"></i> Costs & Expenses
            </p>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-semibold text-slate-700">Purchases (COGS)</p>
                        <p class="text-[10.5px] text-slate-400">Supplier due: {{ $currency }}{{ number_format($statement['supplierDue'],0) }}</p>
                    </div>
                    <p class="text-[13.5px] font-bold text-slate-800">{{ $currency }}{{ number_format($statement['purchases'],0) }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-semibold text-slate-700">Operating Expenses</p>
                        <p class="text-[10.5px] text-slate-400">Finance / misc expenses</p>
                    </div>
                    <p class="text-[13.5px] font-bold text-slate-800">{{ $currency }}{{ number_format($statement['expense'],0) }}</p>
                </div>
                <div class="border-t border-slate-100 pt-2.5 flex items-center justify-between">
                    <p class="text-[12px] font-bold text-red-600">Total Costs</p>
                    <p class="text-[15px] font-bold text-red-500">{{ $currency }}{{ number_format($statement['totalCosts'],0) }}</p>
                </div>
            </div>
        </div>

        {{-- ── NET RESULT ──────────────────────────────────── --}}
        <div class="p-5 {{ $statement['netProfit'] >= 0 ? 'bg-emerald-50/50' : 'bg-red-50/50' }}">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="fas fa-scale-balanced text-[10px]"></i> Net Result
            </p>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <p class="text-[12.5px] text-slate-600">Gross Profit</p>
                    <p class="text-[13px] font-bold {{ $statement['grossProfit'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $statement['grossProfit'] >= 0 ? '+' : '' }}{{ $currency }}{{ number_format(abs($statement['grossProfit']),0) }}
                    </p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-[12.5px] text-slate-600">Other Income</p>
                    <p class="text-[13px] font-semibold text-emerald-600">+{{ $currency }}{{ number_format($statement['income'],0) }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-[12.5px] text-slate-600">Operating Expenses</p>
                    <p class="text-[13px] font-semibold text-red-500">-{{ $currency }}{{ number_format($statement['expense'],0) }}</p>
                </div>
                <div class="border-t-2 {{ $statement['netProfit'] >= 0 ? 'border-emerald-200' : 'border-red-200' }} pt-3 mt-1">
                    <div class="flex items-center justify-between">
                        <p class="text-[13px] font-bold text-slate-700">Net {{ $statement['netProfit'] >= 0 ? 'Profit' : 'Loss' }}</p>
                        <p class="text-[22px] font-bold {{ $statement['netProfit'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $statement['netProfit'] >= 0 ? '+' : '-' }}{{ $currency }}{{ number_format(abs($statement['netProfit']),0) }}
                        </p>
                    </div>
                    @if($statement['revenue'] > 0)
                    @php $margin = round(($statement['netProfit'] / $statement['revenue']) * 100, 1); @endphp
                    <div class="mt-2 h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $statement['netProfit'] >= 0 ? 'bg-emerald-500' : 'bg-red-400' }}"
                             style="width:{{ min(abs($margin),100) }}%"></div>
                    </div>
                    <p class="text-[11px] {{ $statement['netProfit'] >= 0 ? 'text-emerald-600' : 'text-red-500' }} font-semibold mt-1">
                        {{ $margin }}% net margin
                    </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Outstanding Dues ──────────────────────────────────────── --}}
    <div class="border-t border-slate-100 px-5 py-3 bg-slate-50/30 flex flex-wrap gap-6">
        <div class="flex items-center gap-2">
            <i class="fas fa-user-clock text-amber-400 text-xs"></i>
            <span class="text-[11.5px] text-slate-500">Customer Due:</span>
            <span class="text-[12px] font-bold {{ $stats['all_due'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                {{ $currency }}{{ number_format($stats['all_due'],0) }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-industry text-purple-400 text-xs"></i>
            <span class="text-[11.5px] text-slate-500">Supplier Due:</span>
            <span class="text-[12px] font-bold {{ $statement['supplierDue'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                {{ $currency }}{{ number_format($statement['supplierDue'],0) }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-receipt text-blue-400 text-xs"></i>
            <span class="text-[11.5px] text-slate-500">Period Paid:</span>
            <span class="text-[12px] font-bold text-emerald-600">{{ $currency }}{{ number_format($statement['periodPaid'],0) }}</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="fas fa-clock text-red-400 text-xs"></i>
            <span class="text-[11.5px] text-slate-500">Period Due:</span>
            <span class="text-[12px] font-bold {{ $statement['periodDue'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                {{ $currency }}{{ number_format($statement['periodDue'],0) }}
            </span>
        </div>
    </div>

</div>

{{-- ══ ROW 4: Charts ═══════════════════════════════════════════════ --}}
<div class="flex gap-4 mb-4">

    <div class="flex-[3] bg-white rounded-2xl border border-slate-100 shadow-sm p-5 min-w-0">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-[13.5px] font-bold text-slate-800">Revenue — Last 30 Days</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">All branches combined</p>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-blue-500 rounded inline-block"></span> Revenue</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-400 inline-block"></span> Orders</span>
            </div>
        </div>
        <canvas id="revenueChart" height="85"></canvas>
    </div>

    <div class="w-[250px] flex-shrink-0 space-y-3">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h3 class="text-[13px] font-bold text-slate-800 mb-0.5">Payment Methods</h3>
            <p class="text-[11px] text-slate-400 mb-3">All-time</p>
            <canvas id="paymentChart" height="110"></canvas>
            <div class="mt-3 space-y-1.5">
                @foreach($paymentBreakdown as $p)
                <div class="flex items-center justify-between">
                    <span class="text-[11.5px] text-slate-600 capitalize">{{ str_replace('_',' ',$p->payment_method) }}</span>
                    <span class="text-[11.5px] font-bold text-slate-800">{{ $currency }}{{ number_format($p->rev,0) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <h3 class="text-[13px] font-bold text-slate-800 mb-3">6-Month Trend</h3>
            <canvas id="monthlyChart" height="120"></canvas>
        </div>

    </div>
</div>

{{-- ══ ROW 5: Recent Sales ═════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-receipt text-blue-600 text-xs"></i>
            </div>
            <h3 class="text-[13.5px] font-bold text-slate-800">Recent Sales</h3>
        </div>
        <span class="text-[11px] text-slate-400">All branches · latest 8</span>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Invoice</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Branch</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Customer</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Time</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($recentSales as $sale)
            @php
                $bc = match($sale->payment_status) {
                    'paid'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'partial' => 'bg-amber-100 text-amber-700 border-amber-200',
                    default   => 'bg-red-100 text-red-600 border-red-200',
                };
            @endphp
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-2.5 font-mono text-[12px] font-semibold text-slate-700">{{ $sale->invoice_no }}</td>
                <td class="px-3 py-2.5 text-[12px] text-slate-600">{{ $sale->vendor?->name ?? '—' }}</td>
                <td class="px-3 py-2.5 text-[12px] text-slate-500 hidden md:table-cell">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                <td class="px-3 py-2.5 text-right font-bold text-slate-800">{{ $currency }}{{ number_format($sale->total,0) }}</td>
                <td class="px-3 py-2.5 text-center">
                    <span class="border {{ $bc }} px-2.5 py-0.5 rounded-lg text-[10.5px] font-semibold capitalize">{{ $sale->payment_status }}</span>
                </td>
                <td class="px-3 py-2.5 text-right text-[11px] text-slate-400 hidden lg:table-cell">{{ $sale->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No sales yet</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ══ ROW 3: Branch Cards ══════════════════════════════════════════ --}}
<div class="mb-4 mt-6">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fas fa-store text-blue-600 text-xs"></i>
            </div>
            <h2 class="font-bold text-slate-700 text-sm">All Branches</h2>
            <span class="text-[11px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $branchList->count() }}</span>
        </div>
        <button @click="addOpen = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-blue-200 transition-colors">
            <i class="fas fa-plus"></i> New Branch
        </button>
    </div>

    <div class="space-y-3">
        @foreach($branchList as $idx => $b)
        @php
            $color       = $branchColors[$idx % count($branchColors)];
            $status      = $b->status ?? 'active';
            $profitColor = $b->gross_profit >= 0 ? 'text-emerald-600' : 'text-red-500';
            $maxRev      = $branchList->max('all_revenue') ?: 1;
            $revPct      = $maxRev > 0 ? round(($b->all_revenue / $maxRev) * 100) : 0;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
             style="border-left: 4px solid {{ $color }}">
            <div class="p-4">

                {{-- Top row: info + actions --}}
                <div class="flex items-start justify-between gap-4 mb-4">

                    {{-- Left: identity --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-base font-bold flex-shrink-0 shadow-sm"
                             style="background: {{ $color }}">
                            {{ strtoupper(substr($b->name,0,2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-bold text-slate-800 text-[14px]">{{ $b->name }}</p>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border
                                      {{ $status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                    {{ ucfirst($status) }}
                                </span>
                                @if($b->is_online_store)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border bg-indigo-50 text-indigo-700 border-indigo-200">
                                    <i class="fas fa-globe text-[9px] mr-0.5"></i> Website
                                </span>
                                @endif
                                @if($b->is_warehouse)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border bg-orange-50 text-orange-700 border-orange-200">
                                    <i class="fas fa-warehouse text-[9px] mr-0.5"></i> Warehouse
                                </span>
                                @endif
                                @if($b->rev_change !== null)
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg {{ $b->rev_change >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                    {{ $b->rev_change >= 0 ? '+' : '' }}{{ $b->rev_change }}%
                                </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                                @if($b->owner_name)<span class="text-[11px] text-slate-400"><i class="fas fa-user text-[9px] mr-1"></i>{{ $b->owner_name }}</span>@endif
                                @if($b->phone)<span class="text-[11px] text-slate-400"><i class="fas fa-phone text-[9px] mr-1"></i>{{ $b->phone }}</span>@endif
                                @if($b->email ?? false)<span class="text-[11px] text-slate-400 hidden lg:inline"><i class="fas fa-envelope text-[9px] mr-1"></i>{{ $b->email }}</span>@endif
                            </div>
                        </div>
                    </div>

                    {{-- Right: action buttons --}}
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <a href="{{ route('branch.dashboard', $b) }}"
                           class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition-colors">
                            <i class="fas fa-arrow-right text-[10px]"></i> Open
                        </a>
                        <a href="{{ route('branch.reports.index', $b) }}"
                           class="flex items-center gap-1.5 border border-slate-200 text-slate-600 hover:bg-slate-50 px-3 py-2 rounded-xl text-xs font-semibold transition-colors">
                            <i class="fas fa-chart-bar text-[10px]"></i> Report
                        </a>
                        @if($b->is_online_store)
                        <a href="{{ route('branch.sales.index', $b) }}?channel=web"
                           class="flex items-center gap-1.5 border border-indigo-200 text-indigo-600 hover:bg-indigo-50 px-3 py-2 rounded-xl text-xs font-semibold transition-colors">
                            <i class="fas fa-globe text-[10px]"></i> Website Orders
                        </a>
                        @endif
                        <button type="button" @click="openEdit({{ $b->toJson() }})"
                                class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 transition-colors">
                            <i class="fas fa-pen text-[10px]"></i>
                        </button>
                        <button type="button" @click="openDelete({{ $b->id }}, '{{ addslashes($b->name) }}', '{{ route('admin.branches.destroy', $b) }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-colors">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </div>
                </div>

                {{-- Revenue progress bar --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10.5px] text-slate-400">All-time revenue</span>
                        <span class="text-[11px] font-bold text-slate-600">{{ $currency }}{{ number_format($b->all_revenue,0) }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ $revPct }}%; background:{{ $color }}"></div>
                    </div>
                </div>

                {{-- Stats grid --}}
                <div class="grid grid-cols-2 lg:grid-cols-6 gap-2">

                    <div class="bg-slate-50 rounded-xl px-3 py-2.5 border border-slate-100">
                        <p class="text-[10.5px] text-slate-400 font-medium">Period Revenue</p>
                        <p class="text-[13.5px] font-bold text-blue-600 mt-0.5">{{ $currency }}{{ number_format($b->period_revenue,0) }}</p>
                        <p class="text-[10px] text-slate-400">{{ $b->period_orders }} orders</p>
                    </div>

                    <div class="bg-slate-50 rounded-xl px-3 py-2.5 border border-slate-100">
                        <p class="text-[10.5px] text-slate-400 font-medium">Gross Profit</p>
                        <p class="text-[13.5px] font-bold {{ $profitColor }} mt-0.5">
                            {{ $b->gross_profit >= 0 ? '+' : '' }}{{ $currency }}{{ number_format(abs($b->gross_profit),0) }}
                        </p>
                        <p class="text-[10px] text-slate-400">vs purchases</p>
                    </div>

                    <div class="bg-slate-50 rounded-xl px-3 py-2.5 border border-slate-100">
                        <p class="text-[10.5px] text-slate-400 font-medium">Stock Value</p>
                        <p class="text-[13.5px] font-bold text-indigo-600 mt-0.5">{{ $currency }}{{ number_format($b->stock_value,0) }}</p>
                        <p class="text-[10px] text-slate-400">{{ $b->product_count }} products</p>
                    </div>

                    <div class="bg-slate-50 rounded-xl px-3 py-2.5 border border-slate-100">
                        <p class="text-[10.5px] text-slate-400 font-medium">Customers</p>
                        <p class="text-[13.5px] font-bold text-cyan-600 mt-0.5">{{ $b->customer_count }}</p>
                        <p class="text-[10px] text-slate-400">{{ $b->supplier_count }} suppliers</p>
                    </div>

                    <div class="bg-red-50 rounded-xl px-3 py-2.5 border border-red-100">
                        <p class="text-[10.5px] text-red-400 font-medium">Customer Due</p>
                        <p class="text-[13.5px] font-bold {{ $b->customer_due > 0 ? 'text-red-500' : 'text-emerald-600' }} mt-0.5">
                            {{ $b->customer_due > 0 ? $currency.number_format($b->customer_due,0) : 'Settled' }}
                        </p>
                        <p class="text-[10px] text-red-300">receivable</p>
                    </div>

                    <div class="bg-orange-50 rounded-xl px-3 py-2.5 border border-orange-100">
                        <p class="text-[10.5px] text-orange-400 font-medium">Supplier Due</p>
                        <p class="text-[13.5px] font-bold {{ $b->supplier_due > 0 ? 'text-orange-500' : 'text-emerald-600' }} mt-0.5">
                            {{ $b->supplier_due > 0 ? $currency.number_format($b->supplier_due,0) : 'Settled' }}
                        </p>
                        <p class="text-[10px] text-orange-300">payable</p>
                    </div>

                </div>

                {{-- Alerts --}}
                @if($b->out_of_stock > 0 || $b->low_stock > 0)
                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-100">
                    @if($b->out_of_stock > 0)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-red-600 bg-red-50 border border-red-200 px-2.5 py-1 rounded-lg">
                        <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $b->out_of_stock }} out of stock
                    </span>
                    @endif
                    @if($b->low_stock > 0)
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg">
                        <i class="fas fa-triangle-exclamation text-[10px]"></i> {{ $b->low_stock }} low stock
                    </span>
                    @endif
                    <a href="{{ route('branch.stock.index', $b) }}" class="ml-auto text-[11px] text-blue-600 hover:underline">View Stock →</a>
                </div>
                @endif

            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ══ MODALS ══════════════════════════════════════════════════════ --}}

{{-- Add Branch --}}
<div x-show="addOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="addOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="addOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-store text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">New Branch</p>
                <p class="text-xs text-slate-400 mt-0.5">Create branch + owner account</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.branches.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Branch Name <span class="text-red-500">*</span></label>
                <input type="text" name="vendor_name" required placeholder="e.g. Gulshan Branch"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Manager Name <span class="text-red-500">*</span></label>
                    <input type="text" name="owner_name" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label>
                    <input type="text" name="phone"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Login Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="6"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="addOpen = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">Cancel</button>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-bold">Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Branch --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="editOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-pen text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Edit Branch</p>
                <p class="text-xs text-slate-400 mt-0.5" x-text="editName"></p>
            </div>
        </div>
        <form :action="editAction" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Branch Name</label>
                <input type="text" name="name" x-model="editName" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Manager</label>
                    <input type="text" name="owner_name" x-model="editOwner"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label>
                    <input type="text" name="phone" x-model="editPhone"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status</label>
                <div class="grid grid-cols-2 gap-2">
                    <label :class="editStatus==='active' ? 'bg-emerald-50 border-emerald-400' : 'border-slate-200'"
                           class="flex items-center gap-2 border rounded-xl px-4 py-2.5 cursor-pointer hover:border-emerald-400 transition-colors">
                        <input type="radio" name="status" value="active" x-model="editStatus" class="text-emerald-600">
                        <span class="text-sm font-medium text-slate-700">Active</span>
                    </label>
                    <label :class="editStatus==='inactive' ? 'bg-slate-100 border-slate-400' : 'border-slate-200'"
                           class="flex items-center gap-2 border rounded-xl px-4 py-2.5 cursor-pointer hover:border-slate-400 transition-colors">
                        <input type="radio" name="status" value="inactive" x-model="editStatus" class="text-slate-500">
                        <span class="text-sm font-medium text-slate-700">Inactive</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="editOpen = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">Cancel</button>
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-bold">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Branch --}}
<div x-show="delModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delModal = false">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trash-alt text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Delete Branch?</p>
                <p class="text-sm text-slate-500 mt-0.5">Permanently removes all data.</p>
            </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm font-bold text-red-700" x-text="delName"></p>
            <p class="text-xs text-red-400 mt-1">All sales, products, customers & staff will be deleted!</p>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">Cancel</button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">Delete</button>
            </form>
        </div>
    </div>
</div>

</div>{{-- x-data --}}

@push('scripts')
<script>
function adminPage() {
    return {
        addOpen: false,
        editOpen: false, editAction: '', editName: '', editOwner: '', editPhone: '', editStatus: 'active',
        delModal: false, delName: '', delUrl: '',
        openEdit(b) {
            this.editAction = `/admin/branches/${b.id}`;
            this.editName   = b.name       || '';
            this.editOwner  = b.owner_name || '';
            this.editPhone  = b.phone      || '';
            this.editStatus = b.status     || 'active';
            this.editOpen   = true;
        },
        openDelete(id, name, url) {
            this.delName  = name;
            this.delUrl   = url;
            this.delModal = true;
        },
    };
}

const currency = '{{ $currency }}';
Chart.defaults.font.family = "'Inter','Segoe UI',system-ui,sans-serif";
Chart.defaults.color = '#94a3b8';

new Chart(document.getElementById('revenueChart'), {
    data: {
        labels: @json($chartLabels),
        datasets: [
            { type:'line', label:'Revenue', data:@json($chartRevenue), borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.07)', borderWidth:2, tension:0.4, fill:true, pointRadius:0, pointHoverRadius:5, yAxisID:'y' },
            { type:'bar',  label:'Orders',  data:@json($chartOrders),  backgroundColor:'rgba(167,139,250,.35)', borderRadius:3, yAxisID:'y1' }
        ]
    },
    options: {
        responsive:true, interaction:{mode:'index',intersect:false},
        plugins: { legend:{display:false}, tooltip:{backgroundColor:'#1e293b',titleColor:'#94a3b8',bodyColor:'#f1f5f9',padding:10,callbacks:{label:ctx=>ctx.dataset.label==='Revenue'?' '+currency+Number(ctx.parsed.y).toLocaleString():' '+ctx.parsed.y+' orders'}} },
        scales: {
            x:{grid:{display:false},ticks:{maxTicksLimit:10,font:{size:11}}},
            y:{position:'left',grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:11},callback:v=>currency+(v>=1000?(v/1000).toFixed(0)+'k':v)}},
            y1:{position:'right',grid:{drawOnChartArea:false},ticks:{font:{size:11}}}
        }
    }
});

new Chart(document.getElementById('paymentChart'), {
    type:'doughnut',
    data:{ labels:@json($paymentBreakdown->pluck('payment_method')->map(fn($m)=>ucfirst($m))), datasets:[{data:@json($paymentBreakdown->pluck('cnt')),backgroundColor:['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#ec4899'],borderWidth:0,hoverOffset:4}] },
    options:{ responsive:true, cutout:'70%', plugins:{legend:{display:false},tooltip:{backgroundColor:'#1e293b',callbacks:{label:ctx=>' '+ctx.label+': '+ctx.raw}}} }
});

new Chart(document.getElementById('monthlyChart'), {
    type:'bar',
    data:{ labels:@json(collect($last6Months)->pluck('label')), datasets:[{data:@json(collect($last6Months)->pluck('revenue')),backgroundColor:'#3b82f6',borderRadius:4,borderSkipped:false}] },
    options:{ responsive:true, plugins:{legend:{display:false},tooltip:{backgroundColor:'#1e293b',callbacks:{label:ctx=>' '+currency+Number(ctx.parsed.y).toLocaleString()}}}, scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:10},callback:v=>currency+(v>=1000?(v/1000).toFixed(0)+'k':v)}}} }
});
</script>
@endpush
@endsection
