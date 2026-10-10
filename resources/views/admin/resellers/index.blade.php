@extends('layouts.app')
@section('title', 'Reseller Management')
@section('heading', 'Reseller Management')

@section('content')
<div class="py-4 space-y-6" x-data="resellerAdminManager()">

    {{-- Sub Navigation Tabs --}}
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.resellers.index') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-users mr-1.5"></i> Resellers
            @if($pendingCount > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs rounded-full bg-yellow-400 text-yellow-950 font-extrabold">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.resellers.orders') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.orders') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-shopping-bag mr-1.5"></i> Reseller Orders
        </a>
        <a href="{{ route('admin.resellers.withdrawals') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.withdrawals*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-wallet mr-1.5"></i> Withdraw Requests
            @if(isset($globalStats['pending_withdrawals']) && $globalStats['pending_withdrawals'] > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs font-black bg-amber-400 text-amber-950 rounded-full">{{ $globalStats['pending_withdrawals'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.resellers.report') }}"
           class="px-4 py-2 rounded-xl font-bold text-sm transition-colors {{ request()->routeIs('admin.resellers.report') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-chart-pie mr-1.5"></i> Reseller Report
        </a>
    </div>

    {{-- Search & Select Reseller Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.resellers.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-user-check text-indigo-500 mr-1"></i> Select Reseller
                </label>
                <select name="reseller_id" onchange="this.form.submit()"
                        class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 font-medium text-slate-700">
                    <option value="">-- All Resellers (Overview) --</option>
                    @foreach($allResellers as $ar)
                        <option value="{{ $ar->id }}" {{ (request('reseller_id') == $ar->id || (isset($selectedReseller) && $selectedReseller->id == $ar->id)) ? 'selected' : '' }}>
                            {{ $ar->name }} {{ $ar->business_name ? "({$ar->business_name})" : '' }} - {{ $ar->phone ?? $ar->email }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-search text-slate-400 mr-1"></i> Search Query
                </label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, phone, email, business..."
                       class="w-full h-11 border border-slate-200 rounded-xl px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    <i class="fas fa-toggle-on text-slate-400 mr-1"></i> Status
                </label>
                <select name="status" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> Filter
                </button>
                @if(request()->hasAny(['reseller_id', 'search', 'status']))
                    <a href="{{ route('admin.resellers.index') }}" title="Reset"
                       class="h-11 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-bold transition-colors flex items-center justify-center">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- CONDITIONAL DETAILS CARD: Reseller Details vs All Resellers Overview --}}
    @if($selectedReseller && $resellerStats)
        {{-- Single Reseller Details Card --}}
        <div class="bg-gradient-to-br from-white to-slate-50/80 rounded-3xl border-2 border-indigo-500/20 shadow-lg shadow-indigo-100/40 p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white overflow-hidden flex items-center justify-center font-black text-xl shadow-md shadow-indigo-300 flex-shrink-0">
                        @if($selectedReseller->image_url)
                            <img src="{{ $selectedReseller->image_url }}" alt="{{ $selectedReseller->name }}" class="w-full h-full object-cover cursor-pointer"
                                 @click="previewImage('{{ $selectedReseller->image_url }}', '{{ addslashes($selectedReseller->name) }} (Profile Photo)')">
                        @else
                            {{ strtoupper(substr($selectedReseller->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Reseller Details</h2>
                            @if($selectedReseller->status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-circle-check text-[10px] mr-1"></i> Active
                                </span>
                            @elseif($selectedReseller->status === 'pending')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    <i class="fas fa-clock text-[10px] mr-1"></i> Pending Approval
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                    <i class="fas fa-ban text-[10px] mr-1"></i> Inactive
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Profile, financial statistics and profit summary</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($selectedReseller->status === 'active' && $resellerStats['available_profit'] > 0)
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = true"
                                    class="h-10 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-200 flex items-center gap-2">
                                <i class="fas fa-money-bill-transfer"></i> Withdraw Profit
                            </button>

                            <template x-teleport="body">
                                <div x-show="open" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
                                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                                    <div x-show="open" x-transition class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 p-5">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h3 class="text-base font-bold text-slate-800">Withdraw / Deduct Profit</h3>
                                                <p class="text-xs text-slate-400">{{ $selectedReseller->name }} ({{ $selectedReseller->business_name ?? 'Reseller' }})</p>
                                            </div>
                                            <button type="button" @click="open = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-600 flex items-center justify-center transition-colors">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>

                                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 mb-4">
                                            <div class="text-xs text-indigo-500 font-semibold">Available Profit Balance</div>
                                            <div class="text-2xl font-black text-indigo-700 mt-0.5">
                                                ৳{{ number_format($resellerStats['available_profit'], 2) }}
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('admin.resellers.withdraw', $selectedReseller) }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Withdrawal Amount (৳) *</label>
                                                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $resellerStats['available_profit'] }}" required
                                                       placeholder="Enter amount to withdraw"
                                                       class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            </div>

                                            <div class="mb-4">
                                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Note (Optional)</label>
                                                <input type="text" name="note" placeholder="E.g. Bank transfer, bKash payout"
                                                       class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            </div>

                                            <div class="flex gap-2">
                                                <button type="button" @click="open = false" class="flex-1 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold transition-colors">
                                                    Cancel
                                                </button>
                                                <button type="submit" class="flex-1 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors">
                                                    Confirm Deduct
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif

                    <a href="{{ route('admin.resellers.orders', ['reseller_id' => $selectedReseller->id]) }}"
                       class="h-10 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                        <i class="fas fa-shopping-bag"></i> View Orders
                    </a>

                    <a href="{{ route('admin.resellers.index') }}"
                       class="h-10 px-3.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                        <i class="fas fa-arrow-left"></i> All Resellers
                    </a>
                </div>
            </div>

            {{-- Reseller Details Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Profile Info Column --}}
                <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-5 space-y-3.5 shadow-sm">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Profile Information</div>
                    
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Name</span>
                        <span class="text-sm font-bold text-slate-800">{{ $selectedReseller->name }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Business</span>
                        <span class="text-sm font-bold text-indigo-700">{{ $selectedReseller->business_name ?? '—' }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Phone</span>
                        <span class="text-sm font-bold text-slate-700 font-mono">{{ $selectedReseller->phone ?? '—' }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Email</span>
                        <span class="text-xs font-semibold text-slate-600 break-all">{{ $selectedReseller->email }}</span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Status</span>
                        <span class="text-xs font-bold {{ $selectedReseller->status === 'active' ? 'text-emerald-600' : ($selectedReseller->status === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ ucfirst($selectedReseller->status) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Reseller NID</span>
                        <div class="flex items-center gap-1.5">
                            @if($selectedReseller->nid_front_url)
                                <button type="button" @click="previewImage('{{ $selectedReseller->nid_front_url }}', '{{ addslashes($selectedReseller->name) }} - Reseller NID Front')"
                                        class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                    Front
                                </button>
                            @endif
                            @if($selectedReseller->nid_back_url)
                                <button type="button" @click="previewImage('{{ $selectedReseller->nid_back_url }}', '{{ addslashes($selectedReseller->name) }} - Reseller NID Back')"
                                        class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                    Back
                                </button>
                            @endif
                            @if(!$selectedReseller->nid_front_url && !$selectedReseller->nid_back_url)
                                <span class="text-slate-300 text-xs">None</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-medium text-slate-500">Guardian NID</span>
                        <div class="flex items-center gap-1.5">
                            @if($selectedReseller->guardian_nid_front_url)
                                <button type="button" @click="previewImage('{{ $selectedReseller->guardian_nid_front_url }}', '{{ addslashes($selectedReseller->name) }} - Guardian NID Front')"
                                        class="px-2 py-0.5 rounded bg-teal-50 text-teal-700 font-bold text-xs border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                    Front
                                </button>
                            @endif
                            @if($selectedReseller->guardian_nid_back_url)
                                <button type="button" @click="previewImage('{{ $selectedReseller->guardian_nid_back_url }}', '{{ addslashes($selectedReseller->name) }} - Guardian NID Back')"
                                        class="px-2 py-0.5 rounded bg-teal-50 text-teal-700 font-bold text-xs border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                    Back
                                </button>
                            @endif
                            @if(!$selectedReseller->guardian_nid_front_url && !$selectedReseller->guardian_nid_back_url)
                                <span class="text-slate-300 text-xs">None</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-xs font-medium text-slate-500">Registered</span>
                        <span class="text-xs font-bold text-slate-700">{{ $selectedReseller->created_at?->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>

                {{-- Financial & Performance Statistics Grid --}}
                <div class="lg:col-span-8 grid grid-cols-2 md:grid-cols-3 gap-3.5">
                    {{-- Orders --}}
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Orders</span>
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-slate-800">{{ number_format($resellerStats['orders']) }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Total orders placed</div>
                        </div>
                    </div>

                    {{-- Sales --}}
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sales</span>
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                <i class="fas fa-taka-sign"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-indigo-700">৳{{ number_format($resellerStats['sales'], 2) }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Customer sale volume</div>
                        </div>
                    </div>

                    {{-- Total Profit --}}
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Profit</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-emerald-600">৳{{ number_format($resellerStats['total_profit'], 2) }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">From delivered orders</div>
                        </div>
                    </div>

                    {{-- Return Delivery Charges --}}
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-500">Return Charges</span>
                            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                                <i class="fas fa-undo-alt"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-rose-600">৳{{ number_format($resellerStats['total_return_charges'], 2) }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Shipping fee deducted</div>
                        </div>
                    </div>

                    {{-- Withdrawn --}}
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Withdrawn</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-amber-700">৳{{ number_format($resellerStats['withdrawn'], 2) }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Total payout paid</div>
                        </div>
                    </div>

                    {{-- Available Profit / Net Balance --}}
                    @if($resellerStats['available_profit'] < 0)
                        <div class="bg-gradient-to-br from-rose-600 to-red-700 text-white rounded-2xl p-4 shadow-md shadow-rose-100 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-rose-100">Negative Balance</span>
                                <div class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs backdrop-blur-sm">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-2xl font-black tracking-tight">-৳{{ number_format(abs($resellerStats['available_profit']), 2) }}</div>
                                <div class="text-[11px] text-rose-100 mt-0.5">Deficit from return orders</div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-4 shadow-md shadow-emerald-100 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-100">Available Profit</span>
                                <div class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs backdrop-blur-sm">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-2xl font-black tracking-tight">৳{{ number_format($resellerStats['available_profit'], 2) }}</div>
                                <div class="text-[11px] text-emerald-100 mt-0.5">Ready for payout</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- Default: All Resellers Overview --}}
        <div class="bg-gradient-to-br from-white to-indigo-50/30 rounded-3xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-indigo-600"></i> All Resellers Overview
                    </h2>
                    <p class="text-xs text-slate-400">Aggregated sales, orders, and profit across all registered resellers</p>
                </div>
                <div class="text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-100">
                    Default View
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3.5">
                {{-- Total Resellers --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Resellers</span>
                        <i class="fas fa-users text-slate-400 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black text-slate-800">{{ $globalStats['total_resellers'] }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $globalStats['active_resellers'] }} Active &bull; {{ $globalStats['pending_resellers'] }} Pending</div>
                </div>

                {{-- Total Orders --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Orders</span>
                        <i class="fas fa-box text-blue-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black text-blue-600">{{ number_format($globalStats['total_orders']) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">All channel orders</div>
                </div>

                {{-- Total Sales --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sales</span>
                        <i class="fas fa-taka-sign text-indigo-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black text-indigo-700">৳{{ number_format($globalStats['total_sales'], 0) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Total sale revenue</div>
                </div>

                {{-- Total Profit --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Profit</span>
                        <i class="fas fa-chart-line text-emerald-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">৳{{ number_format($globalStats['total_profit'], 0) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Earned from delivered</div>
                </div>

                {{-- Return Charges --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-rose-500">Return Deduct</span>
                        <i class="fas fa-undo-alt text-rose-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black text-rose-600">৳{{ number_format($globalStats['total_return_charges'], 0) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Shipping charge cut</div>
                </div>

                {{-- Total Withdrawn --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Withdrawn</span>
                        <i class="fas fa-hand-holding-dollar text-amber-500 text-xs"></i>
                    </div>
                    <div class="text-2xl font-black text-amber-700">৳{{ number_format($globalStats['total_withdrawn'], 0) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Total payout done</div>
                </div>

                {{-- Available Profit --}}
                @if($globalStats['available_profit'] < 0)
                    <div class="bg-white rounded-2xl border border-rose-200/80 bg-rose-50/30 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Available Net</span>
                            <i class="fas fa-exclamation-circle text-rose-600 text-xs"></i>
                        </div>
                        <div class="text-2xl font-black text-rose-700">-৳{{ number_format(abs($globalStats['available_profit']), 0) }}</div>
                        <div class="text-[11px] text-rose-600/70 mt-0.5">Net deficit balance</div>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-emerald-200/80 bg-emerald-50/20 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Available Profit</span>
                            <i class="fas fa-wallet text-emerald-600 text-xs"></i>
                        </div>
                        <div class="text-2xl font-black text-emerald-700">৳{{ number_format($globalStats['available_profit'], 0) }}</div>
                        <div class="text-[11px] text-emerald-600/70 mt-0.5">Ready for payout</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Resellers Table --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-black text-slate-800 text-base">Resellers Directory</h3>
                <p class="text-xs text-slate-400">Select any reseller to view their complete details card and manage withdrawals</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Showing {{ $resellers->count() }} of {{ $resellers->total() }} resellers
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wider font-bold">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Reseller Info</th>
                        <th class="px-5 py-3.5 text-left">Business</th>
                        <th class="px-4 py-3.5 text-center">NID Documents</th>
                        <th class="px-5 py-3.5 text-center">Orders</th>
                        <th class="px-5 py-3.5 text-right">Total Sales</th>
                        <th class="px-5 py-3.5 text-right">Available Profit</th>
                        <th class="px-5 py-3.5 text-left">Status</th>
                        <th class="px-5 py-3.5 text-left">Registered</th>
                        <th class="px-5 py-3.5 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($resellers as $r)
                        <tr class="hover:bg-indigo-50/30 transition-colors {{ (isset($selectedReseller) && $selectedReseller->id == $r->id) ? 'bg-indigo-50/60 font-semibold' : '' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-sm flex-shrink-0 overflow-hidden">
                                        @if($r->image_url)
                                            <img src="{{ $r->image_url }}" alt="{{ $r->name }}" class="w-full h-full object-cover cursor-pointer"
                                                 @click="previewImage('{{ $r->image_url }}', '{{ addslashes($r->name) }} (Profile Photo)')">
                                        @else
                                            {{ strtoupper(substr($r->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $r->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $r->email }}</div>
                                        <div class="text-xs text-slate-500 font-mono">{{ $r->phone ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-700">{{ $r->business_name ?? '—' }}</div>
                                <div class="text-xs text-slate-400 truncate max-w-[160px]">{{ $r->address ?? '' }}</div>
                            </td>

                            {{-- NID & Documents --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center gap-1 text-[10px]">
                                    {{-- Reseller NID --}}
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400 font-semibold">NID:</span>
                                        @if($r->nid_front_url)
                                            <button type="button" @click="previewImage('{{ $r->nid_front_url }}', '{{ addslashes($r->name) }} - Reseller NID Front')"
                                                    class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                                Front
                                            </button>
                                        @endif
                                        @if($r->nid_back_url)
                                            <button type="button" @click="previewImage('{{ $r->nid_back_url }}', '{{ addslashes($r->name) }} - Reseller NID Back')"
                                                    class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                                Back
                                            </button>
                                        @endif
                                        @if(!$r->nid_front_url && !$r->nid_back_url)
                                            <span class="text-slate-300">None</span>
                                        @endif
                                    </div>
                                    {{-- Guardian NID --}}
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400 font-semibold">G-NID:</span>
                                        @if($r->guardian_nid_front_url)
                                            <button type="button" @click="previewImage('{{ $r->guardian_nid_front_url }}', '{{ addslashes($r->name) }} - Guardian NID Front')"
                                                    class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                                Front
                                            </button>
                                        @endif
                                        @if($r->guardian_nid_back_url)
                                            <button type="button" @click="previewImage('{{ $r->guardian_nid_back_url }}', '{{ addslashes($r->name) }} - Guardian NID Back')"
                                                    class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                                Back
                                            </button>
                                        @endif
                                        @if(!$r->guardian_nid_front_url && !$r->guardian_nid_back_url)
                                            <span class="text-slate-300">None</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                <a href="{{ route('admin.resellers.orders', ['reseller_id' => $r->id]) }}"
                                   class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg text-xs transition-colors">
                                    {{ $r->orders_count }} <i class="fas fa-external-link-alt text-[9px]"></i>
                                </a>
                            </td>

                            <td class="px-5 py-3.5 text-right font-black text-slate-800">
                                ৳{{ number_format($r->total_sales_amount, 2) }}
                            </td>

                            <td class="px-5 py-3.5 text-right">
                                @if($r->available_balance < 0)
                                    <div class="font-black text-rose-600">-৳{{ number_format(abs($r->available_balance), 2) }}</div>
                                    <div class="text-[10px] text-rose-500 font-bold">Negative Balance</div>
                                @else
                                    <div class="font-black text-emerald-600">৳{{ number_format($r->available_balance, 2) }}</div>
                                @endif
                                <div class="text-[10px] text-slate-400 font-medium">Profit: ৳{{ number_format($r->total_profit, 2) }}</div>
                                @if($r->total_return_charge > 0)
                                    <div class="text-[10px] text-rose-500 font-medium">Return: -৳{{ number_format($r->total_return_charge, 2) }}</div>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                @if($r->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($r->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Active</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Inactive</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-slate-500 text-xs font-medium">
                                {{ $r->created_at?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Select / View Details Button --}}
                                    <a href="{{ route('admin.resellers.index', ['reseller_id' => $r->id]) }}"
                                       title="View Details"
                                       class="w-8 h-8 rounded-xl {{ (isset($selectedReseller) && $selectedReseller->id == $r->id) ? 'bg-indigo-600 text-white' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700' }} flex items-center justify-center transition-colors">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>

                                    {{-- Withdraw Profit Modal Button --}}
                                    @if($r->status === 'active' && $r->available_balance > 0)
                                        <div x-data="{ open: false }">
                                            <button type="button" @click="open = true" title="Withdraw Profit"
                                                    class="w-8 h-8 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-700 flex items-center justify-center transition-colors">
                                                <i class="fas fa-money-bill-transfer text-xs"></i>
                                            </button>

                                            <template x-teleport="body">
                                                <div x-show="open" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
                                                    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                                                    <div x-show="open" x-transition class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 p-5">
                                                        <div class="flex items-center justify-between mb-4">
                                                            <div>
                                                                <h3 class="text-base font-bold text-slate-800">Withdraw Profit</h3>
                                                                <p class="text-xs text-slate-400">{{ $r->name }} ({{ $r->business_name ?? 'Reseller' }})</p>
                                                            </div>
                                                            <button type="button" @click="open = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-600 flex items-center justify-center transition-colors">
                                                                <i class="fas fa-times text-xs"></i>
                                                            </button>
                                                        </div>

                                                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 mb-4">
                                                            <div class="text-xs text-indigo-500 font-semibold">Available Profit</div>
                                                            <div class="text-xl font-bold text-indigo-700">
                                                                ৳{{ number_format($r->available_balance, 2) }}
                                                            </div>
                                                        </div>

                                                        <form method="POST" action="{{ route('admin.resellers.withdraw', $r) }}">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Amount (৳) *</label>
                                                                <input type="number" name="amount" step="0.01" min="0.01" max="{{ $r->available_balance }}" required placeholder="Enter amount"
                                                                       class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                                            </div>

                                                            <div class="mb-4">
                                                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Note (Optional)</label>
                                                                <input type="text" name="note" placeholder="Note (optional)"
                                                                       class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                                            </div>

                                                            <div class="flex gap-2">
                                                                <button type="button" @click="open = false" class="flex-1 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold transition-colors">
                                                                    Cancel
                                                                </button>
                                                                <button type="submit" class="flex-1 h-10 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold transition-colors">
                                                                    Confirm Deduct
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    @endif

                                    @if($r->status !== 'active')
                                        <form method="POST" action="{{ route('admin.resellers.approve', $r) }}">
                                            @csrf
                                            <button type="submit" title="Approve" class="w-8 h-8 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-700 flex items-center justify-center transition-colors">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($r->status === 'active')
                                        <form method="POST" action="{{ route('admin.resellers.reject', $r) }}">
                                            @csrf
                                            <button type="submit" title="Deactivate" class="w-8 h-8 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors">
                                                <i class="fas fa-ban text-xs"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.resellers.destroy', $r) }}" onsubmit="return confirm('Delete reseller {{ $r->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-600 flex items-center justify-center transition-colors">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-users-slash text-4xl mb-2 text-slate-300"></i>
                                    <span class="font-semibold text-slate-500">No resellers found</span>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your search query or status filter</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($resellers->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $resellers->links() }}
            </div>
        @endif
    </div>

    {{-- ── Image Lightbox Preview Modal ── --}}
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
                    <span>View Original Size</span>
                </a>
                <button type="button" @click="previewModalOpen = false" class="px-4 py-1.5 rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function resellerAdminManager() {
    return {
        previewModalOpen: false,
        previewSrc: '',
        previewTitle: '',

        previewImage(src, title) {
            this.previewSrc = src;
            this.previewTitle = title;
            this.previewModalOpen = true;
        }
    };
}
</script>
@endpush
