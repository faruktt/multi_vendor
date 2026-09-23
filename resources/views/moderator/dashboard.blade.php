@extends('moderator.layouts.app')
@section('title', 'মডারেটর ড্যাশবোর্ড - কাজের শিফট ট্র্যাকার')

@section('content')
<div class="space-y-6" x-data="moderatorDashboard({{ $activeSession ? 'true' : 'false' }}, '{{ $activeSession?->started_at?->toIso8601String() }}', {{ (float)$ratePerMinute }})">

    {{-- Welcome banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('moderator.profile') }}" class="relative group block flex-shrink-0" title="প্রোফাইল এডিট করুন">
                @if($moderator->image_url)
                    <img src="{{ $moderator->image_url }}" alt="{{ $moderator->name }}"
                         class="w-13 h-13 rounded-2xl object-cover border-2 border-indigo-100 shadow-sm group-hover:scale-105 transition-transform">
                @else
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-lg shadow-sm shadow-indigo-200 group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr($moderator->name, 0, 1)) }}
                    </div>
                @endif
                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white {{ $moderator->isWorkingNow() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                    <span>স্বাগতম, {{ $moderator->name }}!</span>
                    <span class="text-lg">👋</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    প্রতি মিনিট রেট: <span class="font-bold text-indigo-600 font-mono">৳{{ number_format($ratePerMinute, 2) }}/মি.</span> • কাজের সময় অনুযায়ী স্বয়ংক্রিয়ভাবে আয় গণনা করা হবে।
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 sm:self-center flex-wrap">
            <a href="{{ route('moderator.account') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-all flex items-center gap-1.5 shadow-2xs">
                <i class="fas fa-wallet text-[11px]"></i>
                <span>ব্যালেন্স: ৳{{ number_format($availableBalance, 2) }}</span>
            </a>
            <a href="{{ route('moderator.profile') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 border border-slate-200 transition-all flex items-center gap-1.5">
                <i class="fas fa-user-edit text-[11px]"></i>
                <span>প্রোফাইল</span>
            </a>
            <div class="text-xs text-slate-500 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/70 flex items-center gap-2">
                <i class="far fa-calendar-alt text-indigo-500"></i>
                <span class="font-bold text-slate-700">{{ now()->format('d F Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Pending Report Alert (if previous shift was stopped without report) --}}
    @if($needsReportSession)
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between flex-wrap gap-3 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-sm"></i>
            </div>
            <div>
                <p class="text-xs sm:text-sm font-bold">সর্বশেষ সমাপ্ত শিফটের কাজের বিবরণ জমা দেওয়া হয়নি</p>
                <p class="text-xs text-amber-700">তারিখ: {{ $needsReportSession->started_at->timezone('Asia/Dhaka')->format('d M, h:i A') }} • সময়কাল: {{ $needsReportSession->formattedDuration() }} • অর্জিত: ৳{{ number_format($needsReportSession->earned_amount, 2) }}</p>
            </div>
        </div>
        <button type="button" @click="viewReportModal(@js($needsReportSession))"
                class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-all shadow-sm cursor-pointer">
            <i class="fas fa-pen mr-1"></i> বিবরণ ও রিপোর্ট লিখুন
        </button>
    </div>
    @endif

    {{-- ── Shift & Timer Tracker Card ────────────────────────────── --}}
    <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        {{-- Background decorative glows --}}
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -top-16 w-64 h-64 bg-violet-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto text-center">

            {{-- State A: Working Active Session --}}
            <template x-if="isWorking">
                <div class="space-y-5">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-bold shadow-lg">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span>শিফট চালু রয়েছে (Shift In Progress)</span>
                    </div>

                    {{-- Live Big Stopwatch --}}
                    <div>
                        <div class="text-xs font-semibold text-indigo-300 uppercase tracking-widest mb-1">কাজের সময়কাল (Active Elapsed Time)</div>
                        <div class="text-5xl sm:text-7xl font-black font-mono tracking-tight text-white drop-shadow-md" x-text="timerDisplay">
                            00:00:00
                        </div>
                        <div class="flex items-center justify-center gap-4 text-xs mt-3 flex-wrap">
                            <span class="text-indigo-200/80 flex items-center gap-1.5">
                                <i class="far fa-clock"></i>
                                <span>শুরু: {{ $activeSession?->started_at?->timezone('Asia/Dhaka')->format('h:i:s A') }}</span>
                            </span>
                            <span class="bg-white/10 px-3 py-1 rounded-full text-emerald-300 font-bold border border-white/10">
                                আনুমানিক আয়: ৳<span x-text="liveEarned">0.00</span>
                            </span>
                        </div>
                    </div>

                    {{-- Stop Work Action Button --}}
                    <div class="pt-2">
                        <button type="button" @click="stopWork()" :disabled="isStopping"
                                class="inline-flex items-center justify-center gap-2.5 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-black px-8 py-4 rounded-2xl shadow-xl shadow-rose-600/30 hover:shadow-rose-600/50 hover:scale-105 active:scale-95 transition-all text-sm sm:text-base cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                            <template x-if="isStopping">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>কাউন্টডাউন বন্ধ হচ্ছে...</span>
                                </span>
                            </template>
                            <template x-if="!isStopping">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-stop-circle text-lg"></i>
                                    <span>কাজ স্টপ করুন (Stop Work)</span>
                                </span>
                            </template>
                        </button>
                        <p class="text-xs text-indigo-200/70 mt-2">স্টপ বাটনে চাপ দিলে টাইমার ও কাউন্টডাউন তাৎক্ষণিক বন্ধ হবে এবং কাজের বিবরণ দেওয়ার ফর্ম ওপেন হবে।</p>
                    </div>
                </div>
            </template>

            {{-- State B: Idle (No Active Session) --}}
            <template x-if="!isWorking">
                <div class="space-y-6">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center mx-auto shadow-inner">
                        <i class="fas fa-play text-white text-2xl ml-1"></i>
                    </div>

                    <div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white">আপনি কি কাজ শুরু করতে প্রস্তুত?</h2>
                        <p class="text-indigo-200/80 text-xs sm:text-sm mt-1.5 max-w-md mx-auto">
                            নিচের <b>"Start Work"</b> বাটনে ক্লিক করার সাথে সাথে আপনার সময় কাউন্ট শুরু হবে। প্রতি মিনিটে আয় হবে <b>৳{{ number_format($ratePerMinute, 2) }}</b>।
                        </p>
                    </div>

                    {{-- Start Work Button --}}
                    <form method="POST" action="{{ route('moderator.work.start') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black px-8 py-4 rounded-2xl shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-105 active:scale-95 transition-all text-sm sm:text-base cursor-pointer">
                            <i class="fas fa-play-circle text-lg"></i>
                            <span>কাজ শুরু করুন (Start Work)</span>
                        </button>
                    </form>
                </div>
            </template>

        </div>
    </div>

    {{-- ── Real-time Work Activity Log (কাজ চলাকালীন সারা দিনের আপডেট) ── --}}
    @if($activeSession)
    <div class="bg-white rounded-3xl border border-indigo-100 shadow-sm p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 text-white flex items-center justify-center text-sm shadow-md shadow-indigo-200">
                    <i class="fas fa-pen-nib"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm sm:text-base">কাজ চলাকালীন লাইভ আপডেট যোগ করুন</h3>
                    <p class="text-xs text-slate-400">কাজ শেষ না করেই সারাদিন যা যা করবেন এখানে লিখে রাখুন (যেমন: fb comment reply, 2ta order nilam...)</p>
                </div>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                আজকের শিফটে মোট {{ $activeLogs->count() }} টি আপডেট
            </span>
        </div>

        {{-- Quick Log Entry Form --}}
        <form method="POST" action="{{ route('moderator.work.log') }}" class="space-y-3">
            @csrf
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                        <i class="fas fa-comment-dots"></i>
                    </span>
                    <input type="text" name="activity" id="activity_input" required
                           class="w-full h-11 pl-9 pr-3 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50"
                           placeholder="কী কাজ করলেন লিখুন... (যেমন: fb comment reply korlam, 2ta order nilam, 15ta message answer dilam...)">
                </div>
                <button type="submit"
                        class="h-11 px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer flex-shrink-0">
                    <i class="fas fa-plus-circle text-xs"></i>
                    <span>আপডেট যুক্ত করুন</span>
                </button>
            </div>

            {{-- Quick Suggestion Chips --}}
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-[11px] font-bold text-slate-400 mr-1">কুইক টেমপ্লেট:</span>
                @foreach(['ফেসবুক কমেন্ট রিপ্লাই দিয়েছি', '২টি নতুন অর্ডার কনফার্ম করেছি', 'কাস্টমার ইনকোয়ারি হ্যান্ডেল করেছি', 'রিটার্ন পার্সেল রিসিভ করেছি', 'প্যাকিং ও ডেলিভারি রেডি করেছি'] as $chip)
                    <button type="button" onclick="document.getElementById('activity_input').value = '{{ $chip }}'; document.getElementById('activity_input').focus();"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 text-[11px] font-medium transition-colors cursor-pointer">
                        + {{ $chip }}
                    </button>
                @endforeach
            </div>
        </form>

        {{-- Today's Logs Timeline for this shift --}}
        @if($activeLogs->isNotEmpty())
        <div class="pt-3 border-t border-slate-100 space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">আজকের কাজের লাইভ টাইমলাইন (Live Timeline)</span>
            <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto pr-1">
                @foreach($activeLogs as $log)
                <div class="py-2.5 flex items-start justify-between gap-3 text-xs">
                    <div class="flex items-start gap-2">
                        <span class="font-mono text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md flex-shrink-0 mt-0.5">
                            {{ $log->log_time->timezone('Asia/Dhaka')->format('h:i A') }}
                        </span>
                        <span class="text-slate-700 font-semibold">{{ $log->activity }}</span>
                    </div>
                    <form method="POST" action="{{ route('moderator.work.log.delete', $log) }}" onsubmit="return confirm('এই কাজের রেকর্ডটি মুছে ফেলতে চান?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="মুছে ফেলুন">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Summary Metrics KPI ────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        {{-- Today's Completed Time & Earned --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">আজকের কাজের সময়</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            @php
                $tH = floor($todaySeconds / 3600);
                $tM = floor(($todaySeconds % 3600) / 60);
            @endphp
            <div class="text-2xl font-black text-slate-900">
                {{ $tH > 0 ? "{$tH}h {$tM}m" : "{$tM}m" }}
            </div>
            <div class="text-[11px] text-emerald-600 font-bold mt-1">আজকের আয়: ৳{{ number_format($todayEarned, 2) }}</div>
        </div>

        {{-- Rate per minute --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">প্রতি মিনিট রেট</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">৳{{ number_format($ratePerMinute, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">প্রতি ১ মিনিট কাজের পারিশ্রমিক</div>
        </div>

        {{-- Total Earned --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">সর্বমোট অর্জিত আয়</span>
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-600 font-mono">৳{{ number_format($totalEarned, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">মোট {{ number_format($totalSessions) }} টি শিফটের আয়</div>
        </div>

        {{-- Available Balance & My Account Link --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">বর্তমান ব্যালেন্স</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 font-mono">৳{{ number_format($availableBalance, 2) }}</div>
            </div>
            <a href="{{ route('moderator.account') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mt-2">
                <span>হিসাব ও উইথড্র দেখুন</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- ── Recent Work Shifts & Reports ──────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-800 text-sm sm:text-base">সাম্প্রতিক কাজের শিফটসমূহ</h3>
                <p class="text-xs text-slate-400 mt-0.5">আপনার বিগত শিফটগুলোর সময়, অর্জিত টাকা ও কাজের সারসংক্ষেপ</p>
            </div>
            <a href="{{ route('moderator.reports') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors">
                <span>সবগুলো দেখুন</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold uppercase text-slate-400 tracking-wider">
                    <tr>
                        <th class="px-5 py-3">তারিখ</th>
                        <th class="px-5 py-3">শুরু - শেষ</th>
                        <th class="px-5 py-3">মোট কাজের সময়</th>
                        <th class="px-5 py-3 text-right">অর্জিত টাকা (৳)</th>
                        <th class="px-5 py-3">কাজের সারসংক্ষেপ</th>
                        <th class="px-5 py-3 text-center">স্ট্যাটাস</th>
                        <th class="px-5 py-3 text-right">রিপোর্ট</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentSessions as $s)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-slate-800">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('d M, Y') }}
                        </td>
                        <td class="px-5 py-3.5 font-mono text-slate-600">
                            {{ $s->started_at->timezone('Asia/Dhaka')->format('h:i A') }}
                            -
                            {{ $s->ended_at ? $s->ended_at->timezone('Asia/Dhaka')->format('h:i A') : 'চলমান' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($s->isInProgress())
                                <span class="text-emerald-600 font-bold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    চলমান...
                                </span>
                            @else
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">
                                    {{ $s->formattedDuration() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-black font-mono {{ $s->earned_amount > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                            ৳{{ number_format($s->earned_amount, 2) }}
                        </td>
                        <td class="px-5 py-3.5 max-w-xs truncate" title="{{ $s->tasks_summary }}">
                            {{ $s->tasks_summary ?: ($s->isInProgress() ? 'শিফট চলমান' : 'বিবরণ দেওয়া হয়নি') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($s->isInProgress())
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    চলমান
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    সম্পন্ন
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <button type="button" @click="viewReportModal(@js($s))"
                                    class="text-indigo-600 hover:text-indigo-800 font-bold text-xs inline-flex items-center gap-1 hover:underline cursor-pointer">
                                <i class="fas {{ $s->work_report ? 'fa-eye' : 'fa-edit' }} text-[10px]"></i>
                                <span>{{ $s->work_report ? 'রিপোর্ট দেখুন' : 'বিবরণ লিখুন' }}</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            <i class="far fa-clock text-3xl text-slate-300 mb-2 block"></i>
                            <p>এখনও কোনো কাজের শিফট রেকর্ড নেই।</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Work Report Input Modal (স্টপ দেওয়ার পর কাউন্টডাউন শেষ হলে অটো ওপেন হবে) ── --}}
    <div x-show="showReportInputModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showReportInputModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showReportInputModal = false">
            
            {{-- Modal Header --}}
            <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-indigo-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-200">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">কাজ স্টপ সম্পন্ন হয়েছে!</h3>
                        <p class="text-xs text-slate-500 mt-0.5">টাইমার থেমেছে। অনুগ্রহ করে আজকের কাজের বিবরণ লিখুন।</p>
                    </div>
                </div>
                <button type="button" @click="showReportInputModal = false" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/80">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Body Form --}}
            <form :action="'/moderator/work/' + (stoppedSession ? stoppedSession.id : '') + '/report'" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf

                {{-- Time and Earnings Summary Banner --}}
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">কাজের মোট সময়</span>
                        <span class="text-xl font-black text-slate-900 font-mono mt-0.5 block" x-text="stoppedSession?.formatted_time || '0m'">0m</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">অর্জিত পারিশ্রমিক</span>
                        <span class="text-xl font-black text-emerald-600 font-mono mt-0.5 block">
                            ৳<span x-text="stoppedSession?.earned_amount || '0.00'">0.00</span>
                        </span>
                        <span class="text-[10px] text-slate-400 block">রেট: ৳<span x-text="stoppedSession?.rate_per_minute || '0.00'"></span>/মি.</span>
                    </div>
                </div>

                {{-- Tasks summary --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        আজকে কী কী কাজ করেছেন? (সংক্ষিপ্ত শিরোনাম) *
                    </label>
                    <input type="text" name="tasks_summary" required x-model="stoppedSession.tasks_summary"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="যেমন: ১৫টি অর্ডার ভেরিফাই, কাস্টমার সাপোর্ট প্রদান ও ইনভেন্টরি আপডেট">
                </div>

                {{-- Detailed work report --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        কাজের বিস্তারিত বিবরণ / রিপোর্ট (Detailed Report)
                    </label>
                    <textarea name="work_report" rows="5" x-model="stoppedSession.work_report"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs leading-relaxed"
                              placeholder="আজকের কাজের বিস্তারিত বিবরণ, কোনো মন্তব্য থাকলে লিখুন..."></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">শিফট চলাকালীন যোগ করা কাজের লাইভ আপডেট স্বয়ংক্রিয়ভাবে এখানে যুক্ত হয়েছে।</p>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showReportInputModal = false"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        পরে জমা দিব
                    </button>
                    <button type="submit"
                            class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs sm:text-sm font-bold px-6 py-2.5 rounded-xl shadow-md shadow-emerald-200 transition-all flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-check-circle"></i>
                        <span>কাজের বিবরণ সংরক্ষণ করুন</span>
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ── View / Edit Report Modal (পূর্ববর্তী রিপোর্টের জন্য) ────── --}}
    <div x-show="showReportModal" x-cloak
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showReportModal = false">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showReportModal = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">কাজের শিফট রিপোর্ট</h3>
                    <p class="text-xs text-slate-400" x-text="'তারিখ: ' + (selectedSession?.started_at ? new Date(selectedSession.started_at).toLocaleDateString() : '')"></p>
                </div>
                <button type="button" @click="showReportModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'/moderator/work/' + (selectedSession ? selectedSession.id : '') + '/report'" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
                @csrf
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">কাজের সময়কাল</span>
                        <span class="text-sm font-bold text-indigo-700 font-mono" x-text="selectedSession?.duration_seconds ? formatSec(selectedSession.duration_seconds) : '—'"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">অর্জিত পারিশ্রমিক</span>
                        <span class="text-sm font-bold text-emerald-600 font-mono" x-text="'৳' + Number(selectedSession?.earned_amount || 0).toFixed(2)"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">সংক্ষিপ্ত শিরোনাম</label>
                    <input type="text" name="tasks_summary" :value="selectedSession?.tasks_summary || ''"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">কাজের বিস্তারিত রিপোর্ট</label>
                    <textarea name="work_report" rows="5"
                              class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs leading-relaxed"
                              :value="selectedSession?.work_report || ''"></textarea>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="showReportModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700">
                        বন্ধ করুন
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">
                        আপডেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function moderatorDashboard(isWorkingInitial, startedAtIso, ratePerMin) {
    return {
        isWorking: isWorkingInitial,
        startedAt: startedAtIso ? new Date(startedAtIso) : null,
        ratePerMinute: ratePerMin || 0,
        timerDisplay: '00:00:00',
        liveEarned: '0.00',
        intervalId: null,
        isStopping: false,
        showReportInputModal: false,
        showReportModal: false,
        selectedSession: null,
        stoppedSession: null,

        init() {
            if (this.isWorking && this.startedAt) {
                this.updateTimer();
                this.intervalId = setInterval(() => {
                    this.updateTimer();
                }, 1000);
            }
        },

        updateTimer() {
            if (!this.startedAt) return;
            const now = new Date();
            const diffSeconds = Math.max(0, Math.floor((now - this.startedAt) / 1000));

            const hours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diffSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(diffSeconds % 60).padStart(2, '0');

            this.timerDisplay = `${hours}:${minutes}:${seconds}`;

            const earned = (diffSeconds / 60) * this.ratePerMinute;
            this.liveEarned = earned.toFixed(2);
        },

        stopWork() {
            if (this.isStopping) return;

            // 1. Immediately stop countdown timer on client
            clearInterval(this.intervalId);
            this.isStopping = true;

            // 2. Call server to end shift and record time
            fetch('{{ route('moderator.work.stop') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.isStopping = false;
                if (data.success) {
                    this.isWorking = false;
                    this.stoppedSession = {
                        id: data.session_id,
                        formatted_time: data.formatted_time,
                        compact_time: data.compact_time,
                        duration_seconds: data.duration_seconds,
                        rate_per_minute: Number(data.rate_per_minute || 0).toFixed(2),
                        earned_amount: Number(data.earned_amount || 0).toFixed(2),
                        tasks_summary: data.tasks_summary || '',
                        work_report: data.work_report || ''
                    };
                    // 3. Automatically open work description input field
                    this.showReportInputModal = true;
                } else {
                    alert(data.message || 'Error stopping shift');
                    this.resumeTimer();
                }
            })
            .catch(err => {
                this.isStopping = false;
                console.error(err);
                alert('শিফট বন্ধ করতে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।');
                this.resumeTimer();
            });
        },

        resumeTimer() {
            this.updateTimer();
            this.intervalId = setInterval(() => {
                this.updateTimer();
            }, 1000);
        },

        viewReportModal(session) {
            this.selectedSession = session;
            this.showReportModal = true;
        },

        formatSec(totalSec) {
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = totalSec % 60;
            return `${h} hr ${m} min ${s} sec`;
        }
    };
}
</script>
@endpush
