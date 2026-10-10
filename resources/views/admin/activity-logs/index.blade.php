@extends('layouts.app')
@section('title', 'Activity Logs')
@section('heading', 'Activity Logs')

@section('content')
<div x-data="activityLogsPage()" class="space-y-5">

    {{-- ══ STAT CARDS ══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        {{-- Total --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Logs</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-history"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-800">{{ number_format($stats['total']) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">All time records</p>
        </div>

        {{-- Today --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Today</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-calendar-day"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-blue-600">{{ number_format($stats['today']) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Activities today</p>
        </div>

        {{-- Created --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Created</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-circle-plus"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['created']) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">New items added</p>
        </div>

        {{-- Updated --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Updated</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-pen-to-square"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-amber-600">{{ number_format($stats['updated']) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Edits &amp; updates</p>
        </div>

        {{-- Deleted --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Deleted</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fas fa-trash-can"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-rose-600">{{ number_format($stats['deleted']) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Records removed</p>
        </div>
    </div>

    {{-- ══ FILTERS & ACTIONS BAR ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap gap-2.5 items-center">
            {{-- Search input --}}
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search title, description, user, IP..."
                       class="w-full border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 placeholder-slate-400">
            </div>

            {{-- Module filter --}}
            <select name="module" class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white">
                <option value="">All Modules</option>
                @foreach($modules as $mod)
                    <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                @endforeach
            </select>

            {{-- Action filter --}}
            <select name="action" class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white">
                <option value="">All Actions</option>
                <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created (+)</option>
                <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated (✎)</option>
                <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted (🗑)</option>
                <option value="login"   {{ request('action') === 'login'   ? 'selected' : '' }}>Login (🔑)</option>
            </select>

            {{-- User filter --}}
            <select name="user_id" class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 bg-white max-w-[160px]">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>

            {{-- Date range --}}
            <input type="date" name="from" value="{{ request('from') }}" title="From date"
                   class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-white">
            <input type="date" name="to" value="{{ request('to') }}" title="To date"
                   class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-600 bg-white">

            {{-- Submit & Reset --}}
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i class="fas fa-filter text-[10px]"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'module', 'action', 'user_id', 'from', 'to']))
                <a href="{{ route('admin.activity-logs.index') }}" class="border border-slate-200 hover:bg-slate-50 text-slate-600 px-3.5 py-2 rounded-xl text-xs font-medium transition-colors">
                    Reset
                </a>
            @endif

            {{-- Clear logs button --}}
            <button type="button" @click="openClearModal = true"
                    class="ml-auto text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-2 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-trash-can text-[11px]"></i> Clear Logs
            </button>
        </form>
    </div>

    {{-- ══ LOGS TABLE ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="px-3.5 py-3 text-center w-10">#</th>
                        <th class="px-4 py-3">User / Actor</th>
                        <th class="px-3.5 py-3 text-center">Action</th>
                        <th class="px-4 py-3">Target / Module</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-3.5 py-3 text-center">Changes</th>
                        <th class="px-4 py-3 hidden md:table-cell">IP &amp; Device</th>
                        <th class="px-4 py-3 text-right">Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                    @php
                        $hasDiff = !empty($log->properties['new']) || !empty($log->properties['attributes']) || !empty($log->properties['old']);
                        $changeCount = !empty($log->properties['new']) ? count($log->properties['new']) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors group cursor-pointer"
                        @click="viewDetails({{ $log->id }})">
                        {{-- Row Index --}}
                        <td class="px-3.5 py-3 text-center text-slate-400 font-mono text-[11px]">
                            {{ $logs->firstItem() + $loop->index }}
                        </td>

                        {{-- User / Actor --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-200 text-slate-700 font-bold flex items-center justify-center flex-shrink-0 text-xs shadow-2xs">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : '?' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 text-[12px] truncate group-hover:text-blue-600 transition-colors">
                                        {{ $log->user->name ?? 'System / Guest' }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[9.5px] font-semibold px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">
                                            {{ $log->user?->roles->first()?->name ? ucfirst($log->user->roles->first()->name) : 'User' }}
                                        </span>
                                        @if($log->vendor)
                                            <span class="text-[9.5px] text-slate-400 truncate max-w-[90px]" title="{{ $log->vendor->name }}">
                                                · {{ $log->vendor->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Action Badge --}}
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10.5px] font-bold border shadow-2xs {{ $log->action_badge_class }}">
                                <i class="{{ $log->action_icon }} text-[9.5px]"></i>
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>

                        {{-- Module & Target --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 text-[10.5px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="{{ $log->module_icon }} text-[9px]"></i>
                                    {{ $log->module }}
                                </span>
                            </div>
                            @if($log->subject_title)
                                <p class="text-[11.5px] font-semibold text-slate-800 mt-1 truncate max-w-[180px]" title="{{ $log->subject_title }}">
                                    {{ $log->subject_title }}
                                </p>
                            @endif
                        </td>

                        {{-- Description --}}
                        <td class="px-4 py-3">
                            <p class="text-[12px] text-slate-700 line-clamp-2 leading-relaxed font-medium">
                                {{ $log->description }}
                            </p>
                        </td>

                        {{-- Changes Button / Count --}}
                        <td class="px-3.5 py-3 text-center whitespace-nowrap" @click.stop>
                            @if($hasDiff)
                                <button type="button" @click="viewDetails({{ $log->id }})"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-all hover:scale-105 shadow-2xs cursor-pointer">
                                    <i class="fas fa-eye text-[10px]"></i>
                                    <span>{{ $changeCount > 0 ? $changeCount . ' fields' : 'Diff' }}</span>
                                </button>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- IP & Device --}}
                        <td class="px-4 py-3 hidden md:table-cell text-slate-500 whitespace-nowrap">
                            <div class="flex items-center gap-1 font-mono text-[11px] text-slate-600">
                                <i class="fas fa-network-wired text-[9.5px] text-slate-400"></i>
                                <span>{{ $log->ip_address ?? '—' }}</span>
                            </div>
                            @if($log->user_agent)
                                <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[140px]" title="{{ $log->user_agent }}">
                                    {{ Str::limit($log->user_agent, 25) }}
                                </p>
                            @endif
                        </td>

                        {{-- Date & Time --}}
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <p class="text-[11.5px] font-semibold text-slate-800 font-mono">
                                {{ $log->created_at->format('d M Y, h:i A') }}
                            </p>
                            <p class="text-[10.5px] text-slate-400 mt-0.5">
                                {{ $log->created_at->diffForHumans() }}
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fas fa-clock-rotate-left"></i>
                            </div>
                            <p class="font-bold text-slate-600 text-sm">No activities found</p>
                            <p class="text-xs text-slate-400 mt-1">System events, updates, and actions will appear here automatically.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- ══ INTERACTIVE DIFF & DETAILS MODAL ══ --}}
    <div x-show="detailsModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="detailsModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="detailsModal = false">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-indigo-50/30">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-200 flex-shrink-0">
                        <i :class="activeLog?.module_icon || 'fas fa-history'" class="text-sm"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-800 text-[15px]">Activity Details</h3>
                            <span class="font-mono text-xs text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded-md font-semibold"
                                  x-text="'#' + (activeLog?.id || '')"></span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="activeLog?.description"></p>
                    </div>
                </div>
                <button type="button" @click="detailsModal = false"
                        class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Modal Scrollable Body --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                {{-- Metadata Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block">Actor / User</span>
                        <p class="font-bold text-slate-800 mt-0.5 truncate" x-text="activeLog?.user_name || 'System'"></p>
                        <span class="text-[10px] text-slate-500" x-text="activeLog?.user_role"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block">Action</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full border mt-0.5"
                              :class="activeLog?.badge_class"
                              x-text="activeLog?.action_label"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block">Module</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="activeLog?.module"></p>
                        <span class="text-[10px] text-slate-500 truncate block" x-text="activeLog?.subject_title || ''"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block">IP Address</span>
                        <p class="font-mono text-slate-700 mt-0.5" x-text="activeLog?.ip_address || '—'"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block">Branch</span>
                        <p class="font-semibold text-slate-700 mt-0.5" x-text="activeLog?.vendor_name || 'All'"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[10px] block">Date &amp; Time</span>
                        <p class="font-mono text-slate-700 mt-0.5 text-[11px]" x-text="activeLog?.created_at"></p>
                    </div>
                </div>

                {{-- User Agent --}}
                <template x-if="activeLog?.user_agent && activeLog.user_agent !== '—'">
                    <div class="text-[11px] bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-slate-500 flex items-start gap-2">
                        <i class="fas fa-desktop text-slate-400 mt-0.5"></i>
                        <span class="font-mono break-all leading-relaxed" x-text="activeLog.user_agent"></span>
                    </div>
                </template>

                {{-- DIFF TABLE (For Updated Records) --}}
                <template x-if="activeLog?.properties && activeLog.properties.new && Object.keys(activeLog.properties.new).length > 0">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-code-compare text-blue-600"></i>
                                <span>Changes Diff (Old vs New)</span>
                            </h4>
                            <span class="text-[10.5px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700"
                                  x-text="Object.keys(activeLog.properties.new).length + ' fields changed'"></span>
                        </div>
                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-100 text-slate-600 text-[11px] uppercase font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="px-3 py-2 text-left w-1/3">Field</th>
                                        <th class="px-3 py-2 text-left w-1/3 text-rose-700">Before (Old Value)</th>
                                        <th class="px-3 py-2 text-left w-1/3 text-emerald-700">After (New Value)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-sans">
                                    <template x-for="(val, field) in activeLog.properties.new" :key="field">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-3 py-2 font-mono font-bold text-slate-700 capitalize" x-text="field.replace(/_/g, ' ')"></td>
                                            <td class="px-3 py-2 bg-rose-50/40 text-rose-800 font-mono text-[11px] break-all">
                                                <span class="line-through decoration-rose-400" x-text="activeLog.properties.old ? (activeLog.properties.old[field] ?? 'null') : '—'"></span>
                                            </td>
                                            <td class="px-3 py-2 bg-emerald-50/40 text-emerald-800 font-mono text-[11px] font-semibold break-all">
                                                <span x-text="val ?? 'null'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                {{-- SNAPSHOT TABLE (For Created or Deleted Records) --}}
                <template x-if="activeLog?.properties && (activeLog.properties.attributes || activeLog.properties.old) && (!activeLog.properties.new || Object.keys(activeLog.properties.new).length === 0)">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-database text-indigo-600"></i>
                                <span x-text="activeLog?.action === 'deleted' ? 'Deleted Record Snapshot' : 'Created Record Attributes'"></span>
                            </h4>
                        </div>
                        <div class="border border-slate-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-100 text-slate-600 text-[11px] uppercase font-bold border-b border-slate-200 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left w-1/3">Field</th>
                                        <th class="px-3 py-2 text-left w-2/3">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-sans">
                                    <template x-for="(val, field) in (activeLog.properties.attributes || activeLog.properties.old)" :key="field">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-3 py-2 font-mono font-semibold text-slate-600 capitalize" x-text="field.replace(/_/g, ' ')"></td>
                                            <td class="px-3 py-2 font-mono text-slate-800 text-[11px] break-all" x-text="val ?? 'null'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
                <button type="button" @click="detailsModal = false"
                        class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs px-5 py-2 rounded-xl transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- ══ CLEAR LOGS MODAL ══ --}}
    <div x-show="openClearModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="openClearModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
             @click.outside="openClearModal = false">
            <form method="POST" action="{{ route('admin.activity-logs.clear') }}" class="p-6">
                @csrf
                @method('DELETE')
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fas fa-trash-can"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 text-center mb-1">Clear Activity Logs</h3>
                <p class="text-xs text-slate-500 text-center mb-5 leading-relaxed">
                    To keep the system database light and optimized, you can prune logs older than a specific date range or clear all history.
                </p>

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select Timeframe</label>
                    <select name="days" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-400 bg-white">
                        <option value="90">Logs older than 90 days</option>
                        <option value="60">Logs older than 60 days</option>
                        <option value="30">Logs older than 30 days</option>
                        <option value="all">Clear all logs completely</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="openClearModal = false"
                            class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-xl text-xs font-bold transition-colors shadow-sm">
                        Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function activityLogsPage() {
    return {
        detailsModal: false,
        openClearModal: false,
        activeLog: null,
        loadingLog: false,

        async viewDetails(logId) {
            this.loadingLog = true;
            this.detailsModal = true;
            try {
                const res = await fetch(`{{ url('admin/activity-logs') }}/${logId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    this.activeLog = await res.json();
                }
            } catch (e) {
                console.error('Failed to load log details:', e);
            } finally {
                this.loadingLog = false;
            }
        }
    };
}
</script>
@endpush
