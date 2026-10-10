@extends('layouts.app')
@section('title', 'Moderator Work Shifts & Reports')
@section('heading', 'Moderator Work Shifts & Reports')

@section('content')
<div class="py-4 space-y-6" x-data="{ showReportModal: false, selectedSession: null }">

    {{-- Sub Navigation Tabs --}}
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.moderators.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.moderators.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-user-shield mr-1.5"></i> Moderators
            </a>
            <a href="{{ route('admin.moderators.reports') }}"
               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.moderators.reports') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-chart-line mr-1.5"></i> Work Shifts & Reports
            </a>
            <a href="{{ route('admin.moderators.withdrawals') }}"
               class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.moderators.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                <i class="fas fa-wallet mr-1.5"></i> Salary Withdrawals
                @if(\App\Models\ModeratorWithdrawal::where('status', 'pending')->count() > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ \App\Models\ModeratorWithdrawal::where('status', 'pending')->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Filter Presets & Controls --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
        {{-- Preset Buttons --}}
        <div class="flex flex-wrap items-center gap-2">
            @foreach(['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'] as $pKey => $pLabel)
                <a href="{{ route('admin.moderators.reports', array_merge(request()->except(['from', 'to', 'page']), ['preset' => $pKey])) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($preset === $pKey && !request()->filled('from')) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $pLabel }}
                </a>
            @endforeach
        </div>

        {{-- Dropdowns & Date Pickers Form --}}
        <form method="GET" action="{{ route('admin.moderators.reports') }}" class="flex flex-wrap items-end gap-3 pt-3 border-t border-slate-100">
            {{-- Moderator Filter --}}
            <div class="min-w-[180px]">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Select Moderator</label>
                <select name="moderator_id" class="w-full h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="">All Moderators</option>
                    @foreach($allModerators as $mod)
                        <option value="{{ $mod->id }}" {{ request('moderator_id') == $mod->id ? 'selected' : '' }}>
                            {{ $mod->name }} ({{ $mod->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Session Status</label>
                <select name="status" class="h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>

            {{-- Custom From --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">From Date</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>

            {{-- Custom To --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">To Date</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="h-10 border border-slate-200 rounded-xl px-3 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>

            {{-- Submit --}}
            <button type="submit" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs sm:text-sm font-bold transition-colors cursor-pointer">
                <i class="fas fa-filter mr-1.5"></i> Apply Filter
            </button>

            @if(request()->hasAny(['moderator_id', 'status', 'from', 'to']))
            <a href="{{ route('admin.moderators.reports') }}" class="h-10 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs sm:text-sm font-bold transition-colors flex items-center">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Duration in Period --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Work Duration</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 font-mono">{{ $formattedTotalTime }}</div>
            <div class="text-xs text-slate-400 mt-1">Total work duration for selected filter</div>
        </div>

        {{-- Completed Sessions --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Completed Shifts</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-business-time"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($completedSessions) }}</div>
            <div class="text-xs text-slate-400 mt-1">Total completed shift count</div>
        </div>

        {{-- Reports Submitted --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Reports Submitted</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ number_format($reportsSubmitted) }}</div>
            <div class="text-xs text-slate-400 mt-1">Reports submitted by moderators</div>
        </div>

        {{-- Active Right Now --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Shifts Now</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fas fa-stopwatch"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <span>{{ number_format($currentlyWorking) }}</span>
                @if($currentlyWorking > 0)
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                @endif
            </div>
            <div class="text-xs text-slate-400 mt-1">Currently on duty in live shift</div>
        </div>
    </div>

    {{-- Sessions & Reports Log Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Moderator</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5">Shift Window</th>
                        <th class="px-5 py-3.5">Duration</th>
                        <th class="px-5 py-3.5">Tasks Summary</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Work Report</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($sessions as $s)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Moderator info --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                @if($s->moderator?->image_url)
                                    <img src="{{ $s->moderator->image_url }}" alt="{{ $s->moderator->name }}"
                                         class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-2xs flex-shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-black flex items-center justify-center text-xs flex-shrink-0">
                                        {{ strtoupper(substr($s->moderator?->name ?? 'M', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-extrabold text-slate-800">{{ $s->moderator?->name ?? 'Unknown' }}</div>
                                    <div class="text-slate-400 text-[11px] font-mono">{{ $s->moderator?->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 font-bold text-slate-800">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('d M, Y') }}
                        </td>

                        {{-- Start - End --}}
                        <td class="px-5 py-4 font-mono text-slate-600">
                            <span class="text-slate-800 font-semibold">{{ $s->started_at->timezone('Asia/Dhaka')->format('h:i:s A') }}</span>
                            <span class="text-slate-400 mx-1">→</span>
                            <span class="text-slate-800 font-semibold">{{ $s->ended_at ? $s->ended_at->timezone('Asia/Dhaka')->format('h:i:s A') : 'In Progress...' }}</span>
                        </td>

                        {{-- Duration --}}
                        <td class="px-5 py-4">
                            @if($s->isInProgress())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    {{ $s->started_at->diffForHumans(null, true) }} (In Progress)
                                </span>
                            @else
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg font-mono">
                                    {{ $s->formattedDuration() }}
                                </span>
                            @endif
                        </td>

                        {{-- Tasks Summary --}}
                        <td class="px-5 py-4 max-w-xs truncate" title="{{ $s->tasks_summary }}">
                            {{ $s->tasks_summary ?: ($s->isInProgress() ? 'Shift in progress' : '—') }}
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4 text-center">
                            @if($s->isInProgress())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    In Progress
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    Completed
                                </span>
                            @endif
                        </td>

                        {{-- Work Report Action --}}
                        <td class="px-5 py-4 text-right">
                            @if($s->work_report)
                                <button type="button" @click="selectedSession = @js($s); showReportModal = true;"
                                        class="text-indigo-600 hover:text-indigo-800 font-bold text-xs inline-flex items-center gap-1 hover:underline cursor-pointer">
                                    <i class="fas fa-file-alt text-[11px]"></i>
                                    <span>{{ $s->isInProgress() ? 'View Live Activity' : 'View Report' }}</span>
                                </button>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            <i class="far fa-clipboard text-3xl text-slate-300 mb-2 block"></i>
                            <p>No work shift records found for the selected timeframe.</p>
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

    {{-- Detailed Report Modal --}}
    <div x-show="showReportModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showReportModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showReportModal = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-indigo-50 to-violet-50">
                <div class="flex items-center gap-3">
                    <template x-if="selectedSession?.moderator?.image_url">
                        <img :src="selectedSession.moderator.image_url" :alt="selectedSession.moderator.name"
                             class="w-11 h-11 rounded-2xl object-cover border-2 border-indigo-200 shadow-md flex-shrink-0">
                    </template>
                    <template x-if="!selectedSession?.moderator?.image_url">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-200 flex-shrink-0">
                            <span x-text="selectedSession?.moderator?.name ? selectedSession.moderator.name.charAt(0).toUpperCase() : 'M'"></span>
                        </div>
                    </template>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm sm:text-base" x-text="selectedSession?.moderator?.name ? selectedSession.moderator.name + ' - Work Report' : 'Moderator Work Report'"></h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="(selectedSession?.moderator?.email || '') + (selectedSession?.started_at ? ' • ' + new Date(selectedSession.started_at).toLocaleDateString() : '')"></p>
                    </div>
                </div>
                <button type="button" @click="showReportModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto">
                {{-- Duration info --}}
                <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Work Duration</span>
                        <span class="text-lg font-black text-indigo-900 font-mono" x-text="selectedSession ? formatSec(selectedSession.duration_seconds) : ''"></span>
                    </div>
                    <div class="text-right text-xs text-slate-600">
                        <span class="block text-[10px] text-slate-400 uppercase font-bold">Report Submission Time</span>
                        <span class="font-bold text-slate-700" x-text="selectedSession?.report_submitted_at ? new Date(selectedSession.report_submitted_at).toLocaleTimeString() : 'N/A'"></span>
                    </div>
                </div>

                {{-- Summary --}}
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Summary</span>
                    <p class="text-sm font-bold text-slate-800 mt-0.5" x-text="selectedSession?.tasks_summary || 'Not provided'"></p>
                </div>

                {{-- Full Work Report --}}
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Detailed Work Report</span>
                    <div class="mt-1.5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed"
                         x-text="selectedSession?.work_report || 'No detailed report submitted.'">
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end">
                <button type="button" @click="showReportModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function formatSec(totalSec) {
    if (!totalSec) return '0 min';
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    let parts = [];
    if (h > 0) parts.push(`${h} hr` + (h > 1 ? 's' : ''));
    if (m > 0 || h > 0) parts.push(`${m} min` + (m > 1 ? 's' : ''));
    parts.push(`${s} sec`);
    return parts.join(' ');
}
</script>
@endpush
