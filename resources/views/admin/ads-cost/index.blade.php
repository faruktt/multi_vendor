@extends('layouts.app')
@section('title', 'Ads Cost Management')
@section('heading', 'Ads Cost Management')

@section('content')
<div class="py-4 space-y-6" x-data="adsCostManager()">

    {{-- ── Top Hero / Banner ── --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl border border-indigo-900/50 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-13 h-13 p-3 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center flex-shrink-0 shadow-inner">
                    <i class="fas fa-rectangle-ad text-amber-400 text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black tracking-tight text-white">Ads Cost &amp; Marketing Spend</h2>
                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30">Analytics</span>
                    </div>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        Track and analyze your ad expenditures across Facebook, Google, TikTok, Instagram, YouTube, and other marketing channels. Monitor total spend, conversions, CPC, and return on ad spend.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 w-full md:w-auto flex-wrap">
                <a href="{{ route('admin.ads-cost.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold text-white transition-all backdrop-blur-sm shadow-sm">
                    <i class="fas fa-file-csv text-emerald-400"></i> Export CSV
                </a>
                <button type="button" @click="openCreateModal()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-xs font-black text-white transition-all shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-plus"></i> Add Ads Cost
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

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 shadow-sm">
            <div class="flex items-center gap-2 font-bold text-sm mb-1 text-rose-700">
                <i class="fas fa-triangle-exclamation"></i> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Summary KPI Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
        {{-- Selected Period Spend --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Filtered Spend</span>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-filter"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-800">৳{{ number_format($totalCost, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1 truncate">Current filter selection</div>
        </div>

        {{-- Today's Spend --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Today's Spend</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600">৳{{ number_format($todayCost, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ now()->format('d M, Y') }}</div>
        </div>

        {{-- This Month's Spend --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">This Month</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-blue-600">৳{{ number_format($thisMonthCost, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ now()->format('F Y') }} total</div>
        </div>

        {{-- Conversions & CPA --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Conversions / Orders</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-cart-shopping"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-600">{{ number_format($totalConversions) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                @if($totalConversions > 0)
                    Avg CPA: <span class="font-bold text-slate-700">৳{{ number_format($avgCpa, 2) }}</span>
                @else
                    Cost Per Order: N/A
                @endif
            </div>
        </div>

        {{-- Traffic & CPC --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-md transition-shadow col-span-2 md:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Clicks &amp; CPC</span>
                <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fas fa-arrow-pointer"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-purple-600">{{ number_format($totalClicks) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                @if($totalClicks > 0)
                    CPC: <span class="font-bold text-slate-700">৳{{ number_format($avgCpc, 2) }}</span> · {{ number_format($totalImpressions) }} imp
                @else
                    {{ number_format($totalImpressions) }} total impressions
                @endif
            </div>
        </div>
    </div>

    {{-- ── Platform Breakdown Cards ── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-chart-pie text-indigo-500"></i> Platform-Wise Spend Breakdown
                </h3>
                <p class="text-[11.5px] text-slate-400 mt-0.5">Click any platform card to quickly filter records</p>
            </div>
            <span class="text-xs font-bold text-slate-500">All-Time Total: <span class="text-indigo-600 font-black">৳{{ number_format($allTimeCost, 2) }}</span></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            @php
                $platformMeta = [
                    'facebook'  => ['label' => 'Facebook', 'icon' => 'fab fa-facebook text-blue-600', 'bg' => 'hover:border-blue-300'],
                    'google'    => ['label' => 'Google', 'icon' => 'fab fa-google text-red-500', 'bg' => 'hover:border-red-300'],
                    'tiktok'    => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok text-slate-900', 'bg' => 'hover:border-slate-400'],
                    'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram text-pink-600', 'bg' => 'hover:border-pink-300'],
                    'youtube'   => ['label' => 'YouTube', 'icon' => 'fab fa-youtube text-red-600', 'bg' => 'hover:border-rose-300'],
                    'snapchat'  => ['label' => 'Snapchat', 'icon' => 'fab fa-snapchat text-amber-500', 'bg' => 'hover:border-amber-300'],
                    'other'     => ['label' => 'Others', 'icon' => 'fas fa-bullhorn text-indigo-500', 'bg' => 'hover:border-indigo-300'],
                ];
            @endphp

            @foreach($platformsList as $pKey)
                @php
                    $pData = $platformBreakdown->get($pKey);
                    $pSpent = $pData ? (float) $pData->total_amount : 0.0;
                    $pCount = $pData ? (int) $pData->count : 0;
                    $pShare = $allTimeCost > 0 ? round(($pSpent / $allTimeCost) * 100, 1) : 0;
                    $meta = $platformMeta[$pKey] ?? ['label' => ucfirst($pKey), 'icon' => 'fas fa-bullhorn text-slate-600', 'bg' => ''];
                    $isSelected = request('platform') === $pKey;
                @endphp
                <a href="{{ route('admin.ads-cost.index', array_merge(request()->except('platform', 'page'), $isSelected ? [] : ['platform' => $pKey])) }}"
                   class="group block p-3 rounded-xl border {{ $isSelected ? 'border-indigo-500 bg-indigo-50/50 shadow-sm ring-2 ring-indigo-200' : 'border-slate-200/80 bg-slate-50/50 hover:bg-white' }} {{ $meta['bg'] }} transition-all">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <i class="{{ $meta['icon'] }}"></i>
                            <span class="truncate">{{ $meta['label'] }}</span>
                        </div>
                        @if($isSelected)
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                        @endif
                    </div>
                    <div class="text-sm font-black text-slate-800">৳{{ number_format($pSpent) }}</div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                        <span>{{ $pShare }}%</span>
                        <span>{{ $pCount }} {{ Str::plural('ad', $pCount) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── Filter & Action Bar ── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.ads-cost.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            {{-- Search --}}
            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Campaign, ad account, notes..."
                           class="w-full h-10 pl-9 pr-3 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                </div>
            </div>

            {{-- Platform --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Platform</label>
                <select name="platform" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="all">All Platforms</option>
                    <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Facebook Ads</option>
                    <option value="google" {{ request('platform') === 'google' ? 'selected' : '' }}>Google Ads</option>
                    <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok Ads</option>
                    <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Instagram Ads</option>
                    <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube Ads</option>
                    <option value="snapchat" {{ request('platform') === 'snapchat' ? 'selected' : '' }}>Snapchat Ads</option>
                    <option value="other" {{ request('platform') === 'other' ? 'selected' : '' }}>Other / Custom</option>
                </select>
            </div>

            {{-- Period Presets --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Date Range</label>
                <select name="period" x-model="selectedPeriod" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom Range...</option>
                </select>
            </div>

            {{-- Custom Date Pickers (Shown if period is custom or dates are already set) --}}
            <div class="lg:col-span-3 grid grid-cols-2 gap-2" x-show="selectedPeriod === 'custom'" x-cloak>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="w-full h-10 border border-slate-200 rounded-xl px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="w-full h-10 border border-slate-200 rounded-xl px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                </div>
            </div>

            {{-- Actions --}}
            <div class="lg:col-span-2 flex items-center gap-2" :class="selectedPeriod === 'custom' ? 'lg:col-span-2' : 'lg:col-span-5'">
                <button type="submit" class="flex-1 h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'platform', 'period', 'from_date', 'to_date']))
                    <a href="{{ route('admin.ads-cost.index') }}"
                       class="h-10 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all flex items-center justify-center" title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Records Table ── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Ads Cost Entries</h3>
                    <p class="text-xs text-slate-400">Total {{ $records->total() }} records found</p>
                </div>
            </div>

            <div class="text-xs text-slate-500 font-semibold">
                Total Shown Amount: <span class="text-emerald-600 font-black">৳{{ number_format($totalCost, 2) }}</span>
            </div>
        </div>

        @if($records->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-200/60">
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4">Platform</th>
                            <th class="py-3.5 px-4">Campaign &amp; Account</th>
                            <th class="py-3.5 px-4 text-right">Spend (BDT)</th>
                            <th class="py-3.5 px-4 text-center">Traffic (Clicks/Imp)</th>
                            <th class="py-3.5 px-4 text-center">Results (CPA)</th>
                            <th class="py-3.5 px-4">Logged By</th>
                            <th class="py-3.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($records as $ad)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Date --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ $ad->cost_date?->format('d M, Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $ad->cost_date?->diffForHumans() }}</div>
                                </td>

                                {{-- Platform --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $ad->platform_badge_class }}">
                                        <i class="{{ $ad->platform_icon }}"></i>
                                        <span>{{ $ad->platform_label }}</span>
                                    </span>
                                </td>

                                {{-- Campaign & Account --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 max-w-xs truncate" title="{{ $ad->campaign_name }}">
                                        {{ $ad->campaign_name }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                        @if($ad->ad_account)
                                            <span class="inline-flex items-center gap-1 text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">
                                                <i class="fas fa-id-badge text-[9px]"></i> {{ $ad->ad_account }}
                                            </span>
                                        @endif
                                        @if($ad->target_url)
                                            <a href="{{ $ad->target_url }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800 underline flex items-center gap-0.5 text-[10.5px]" title="{{ $ad->target_url }}">
                                                <i class="fas fa-arrow-up-right-from-square text-[9px]"></i> Link
                                            </a>
                                        @endif
                                    </div>
                                    @if($ad->notes)
                                        <div class="text-[11px] text-slate-400 mt-1 italic truncate max-w-xs" title="{{ $ad->notes }}">
                                            "{{ $ad->notes }}"
                                        </div>
                                    @endif
                                </td>

                                {{-- Amount --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="text-sm font-black text-slate-900">৳{{ number_format($ad->amount, 2) }}</div>
                                    @if($ad->amount_usd && $ad->amount_usd > 0)
                                        <div class="text-[10px] font-medium text-slate-400">${{ number_format($ad->amount_usd, 2) }} USD</div>
                                    @endif
                                </td>

                                {{-- Traffic (Clicks, Imp, CPC, CTR) --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($ad->clicks > 0 || $ad->impressions > 0)
                                        <div class="font-semibold text-slate-800">
                                            <span class="text-indigo-600 font-bold">{{ number_format($ad->clicks) }}</span> clicks
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ number_format($ad->impressions) }} imp
                                            @if($ad->cpc > 0)
                                                · CPC: <span class="font-bold text-slate-600">৳{{ number_format($ad->cpc, 2) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-300 text-[11px]">-</span>
                                    @endif
                                </td>

                                {{-- Results / Conversions --}}
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($ad->conversions > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fas fa-cart-shopping text-[10px]"></i>
                                            {{ $ad->conversions }} conv.
                                        </span>
                                        <div class="text-[10px] text-slate-500 mt-0.5 font-medium">
                                            CPA: <span class="font-bold text-slate-700">৳{{ number_format($ad->cpa, 2) }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-300 text-[11px]">-</span>
                                    @endif
                                </td>

                                {{-- Logged By --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-700">{{ $ad->createdBy?->name ?? 'Admin' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $ad->created_at?->format('d M, h:i A') }}</div>
                                </td>

                                {{-- Action Buttons --}}
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="openEditModal({{ json_encode($ad) }})"
                                                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 flex items-center justify-center transition-colors"
                                                title="Edit Entry">
                                            <i class="fas fa-pen-to-square text-[11px]"></i>
                                        </button>
                                        <form method="POST" action="/admin/ads-cost/{{ $ad->id }}"
                                              onsubmit="return confirm('Are you sure you want to delete this ads cost record ({{ addslashes($ad->campaign_name) }})?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 flex items-center justify-center transition-colors"
                                                    title="Delete Entry">
                                                <i class="fas fa-trash text-[11px]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $records->links() }}
                </div>
            @endif
        @else
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 mx-auto flex items-center justify-center text-2xl mb-3 shadow-inner">
                    <i class="fas fa-rectangle-ad"></i>
                </div>
                <h4 class="text-base font-bold text-slate-700">No Ads Cost Records Found</h4>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    No ad expenses recorded for this filter selection. Click below to add your first advertising expense!
                </p>
                <button type="button" @click="openCreateModal()"
                        class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-md shadow-indigo-100">
                    <i class="fas fa-plus"></i> Add New Ads Cost
                </button>
            </div>
        @endif
    </div>

    {{-- ── Create / Edit Modal ── --}}
    <template x-if="modalOpen">
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
             @click.self="modalOpen = false">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 relative transform transition-all"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                            <i class="fas fa-rectangle-ad"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-800" x-text="isEditing ? 'Edit Ads Cost Record' : 'Record New Ads Cost'"></h3>
                            <p class="text-xs text-slate-400" x-text="isEditing ? 'Update existing advertisement expense details' : 'Enter expenditure details for Facebook, Google, TikTok or other ads'"></p>
                        </div>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form :action="formAction" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="currency" :value="form.currency || 'BDT'">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {{-- Platform --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Platform *</label>
                            <select name="platform" x-model="form.platform" required
                                    class="w-full h-11 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                                <option value="facebook">Facebook Ads (Meta)</option>
                                <option value="google">Google Ads</option>
                                <option value="tiktok">TikTok Ads</option>
                                <option value="instagram">Instagram Ads</option>
                                <option value="youtube">YouTube Ads</option>
                                <option value="snapchat">Snapchat Ads</option>
                                <option value="other">Other / Custom Channel</option>
                            </select>
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cost Date *</label>
                            <input type="date" name="cost_date" x-model="form.cost_date" required
                                   class="w-full h-11 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                        </div>
                    </div>

                    {{-- Campaign Name --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Campaign Name / Ad Title *</label>
                        <input type="text" name="campaign_name" x-model="form.campaign_name" required
                               placeholder="e.g. Eid Mega Sale Campaign #1 / Summer T-Shirt Promo"
                               class="w-full h-11 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        {{-- Ad Account / Page --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ad Account / BM / Page <span class="text-slate-400 font-normal">(optional)</span></label>
                            <input type="text" name="ad_account" x-model="form.ad_account"
                                   placeholder="e.g. Fayaz Main BM / Account #2"
                                   class="w-full h-11 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                        </div>

                        {{-- Target URL --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Landing / Product URL <span class="text-slate-400 font-normal">(optional)</span></label>
                            <input type="url" name="target_url" x-model="form.target_url"
                                   placeholder="https://..."
                                   class="w-full h-11 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                        </div>
                    </div>

                    {{-- Amounts --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Amount in BDT (৳) *
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-3 text-slate-400 font-black text-xs">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" x-model="form.amount" required
                                       placeholder="0.00"
                                       class="w-full h-11 pl-8 pr-3 border border-slate-200 rounded-xl text-sm font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Amount in USD ($) <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-3 text-slate-400 font-black text-xs">$</span>
                                <input type="number" step="0.01" min="0" name="amount_usd" x-model="form.amount_usd"
                                       placeholder="0.00"
                                       class="w-full h-11 pl-8 pr-3 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Performance Metrics (Optional) --}}
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <i class="fas fa-chart-line text-indigo-500"></i> Performance Metrics (Optional for CPA &amp; CPC tracking)
                        </div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Conversions</label>
                                <input type="number" min="0" name="conversions" x-model="form.conversions"
                                       placeholder="0 orders"
                                       class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Clicks</label>
                                <input type="number" min="0" name="clicks" x-model="form.clicks"
                                       placeholder="0 clicks"
                                       class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Impressions</label>
                                <input type="number" min="0" name="impressions" x-model="form.impressions"
                                       placeholder="0 impressions"
                                       class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Notes / Strategy Details <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="notes" x-model="form.notes" rows="2"
                                  placeholder="Notes regarding targeting, audience, ad creative, or performance remarks..."
                                  class="w-full border border-slate-200 rounded-xl p-3 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50"></textarea>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="flex gap-2.5 pt-4 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false"
                                class="flex-1 h-11 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-bold transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 h-11 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs sm:text-sm font-black transition-all shadow-md shadow-indigo-200 flex items-center justify-center gap-1.5">
                            <i class="fas fa-check"></i>
                            <span x-text="isEditing ? 'Save Changes' : 'Record Ad Expense'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function adsCostManager() {
    return {
        modalOpen: false,
        isEditing: false,
        selectedPeriod: @json(request('period', 'all')),
        formAction: '/admin/ads-cost',
        form: {
            id: null,
            platform: 'facebook',
            campaign_name: '',
            ad_account: '',
            cost_date: @json(now()->toDateString()),
            amount: '',
            currency: 'BDT',
            amount_usd: '',
            conversions: '',
            clicks: '',
            impressions: '',
            target_url: '',
            notes: '',
        },
        openCreateModal() {
            this.isEditing = false;
            this.formAction = '/admin/ads-cost';
            this.form = {
                id: null,
                platform: 'facebook',
                campaign_name: '',
                ad_account: '',
                cost_date: new Date().toISOString().substring(0, 10),
                amount: '',
                currency: 'BDT',
                amount_usd: '',
                conversions: '',
                clicks: '',
                impressions: '',
                target_url: '',
                notes: '',
            };
            this.modalOpen = true;
        },
        openEditModal(ad) {
            this.isEditing = true;
            this.formAction = '/admin/ads-cost/' + ad.id;
            this.form = {
                id: ad.id,
                platform: ad.platform || 'facebook',
                campaign_name: ad.campaign_name || '',
                ad_account: ad.ad_account || '',
                cost_date: ad.cost_date ? ad.cost_date.substring(0, 10) : new Date().toISOString().substring(0, 10),
                amount: ad.amount,
                currency: ad.currency || 'BDT',
                amount_usd: ad.amount_usd || '',
                conversions: ad.conversions || '',
                clicks: ad.clicks || '',
                impressions: ad.impressions || '',
                target_url: ad.target_url || '',
                notes: ad.notes || '',
            };
            this.modalOpen = true;
        }
    }
}
</script>
@endsection
