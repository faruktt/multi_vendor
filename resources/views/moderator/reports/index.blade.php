@extends('moderator.layouts.app')
@section('title', 'Work History & Reports - Moderator Portal')

@section('content')
<div class="space-y-6" x-data="{ showReportModal: false, selectedSession: null }">

    {{-- Header & Total Time Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-clipboard-list text-indigo-600"></i>
                <span>Work History & Reports</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Summary of all your past work shifts and submitted reports</p>
        </div>

        @php
            $totH = floor($totalSeconds / 3600);
            $totM = floor(($totalSeconds % 3600) / 60);
        @endphp
        <div class="flex items-center gap-3 bg-indigo-50/70 border border-indigo-100 px-4 py-2.5 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shadow-indigo-200">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-indigo-500 tracking-wider block">Filtered Total Work Time</span>
                <span class="text-lg font-black text-indigo-950 block">{{ $totH > 0 ? "{$totH} hr {$totM} min" : "{$totM} min" }}</span>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form method="GET" action="{{ route('moderator.reports') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="h-9.5 text-xs rounded-xl border border-slate-200 px-3 bg-slate-50 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="h-9.5 text-xs rounded-xl border border-slate-200 px-3 bg-slate-50 text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit"
                    class="h-9.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['from', 'to']))
            <a href="{{ route('moderator.reports') }}"
               class="h-9.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-colors flex items-center gap-1">
                <i class="fas fa-times text-[10px]"></i>
                <span>Reset</span>
            </a>
            @endif
        </form>
    </div>

    {{-- Table of Sessions --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Shift Date</th>
                        <th class="px-5 py-3.5">Start Time</th>
                        <th class="px-5 py-3.5">End Time</th>
                        <th class="px-5 py-3.5">Duration</th>
                        <th class="px-5 py-3.5">Summary</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sessions as $s)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-slate-800">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('d M, Y') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('h:i:s A') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600">
                            {{ $s->ended_at ? $s->ended_at->timezone('Asia/Dhaka')->format('h:i:s A') : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($s->isInProgress())
                                <span class="text-emerald-600 font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    In Progress...
                                </span>
                            @else
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">
                                    {{ $s->formattedDuration() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 max-w-sm truncate" title="{{ $s->tasks_summary }}">
                            {{ $s->tasks_summary ?: ($s->isInProgress() ? 'Shift In Progress' : 'No summary provided') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($s->isInProgress())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    In Progress
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Completed
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if($s->work_report)
                                <button type="button" @click="selectedSession = @js($s); showReportModal = true;"
                                        class="text-indigo-600 hover:text-indigo-800 font-bold text-xs inline-flex items-center gap-1 hover:underline cursor-pointer">
                                    <i class="fas fa-eye text-[10px]"></i>
                                    <span>View Report</span>
                                </button>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            <i class="far fa-folder-open text-3xl text-slate-300 mb-2 block"></i>
                            <p>No work sessions found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

    {{-- Report View Modal --}}
    <div x-show="showReportModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showReportModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showReportModal = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Work Shift Report</h3>
                    <p class="text-xs text-slate-400" x-text="'Date: ' + (selectedSession?.started_at ? new Date(selectedSession.started_at).toLocaleDateString() : '')"></p>
                </div>
                <button type="button" @click="showReportModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Summary</span>
                    <p class="text-sm font-bold text-slate-800 mt-0.5" x-text="selectedSession?.tasks_summary || 'Not provided'"></p>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Detailed Report</span>
                    <div class="mt-1.5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed"
                         x-text="selectedSession?.work_report || 'No detailed description provided.'">
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end">
                <button type="button" @click="showReportModal = false" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
