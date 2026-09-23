@extends('layouts.app')
@section('title','Fraud Check')
@section('heading','Fraud Check')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')

{{-- Hero search form --}}
<div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 mb-6 shadow-lg">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center">
            <i class="fas fa-shield-halved text-red-400 text-lg"></i>
        </div>
        <div>
            <h2 class="text-white font-bold text-[15px]">Customer Fraud Checker</h2>
            <p class="text-slate-400 text-[12px] mt-0.5">Powered by fraudchecker.link — Enter customer phone number to check delivery history</p>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.fraud-check') }}" class="flex gap-3">
        <div class="flex-1 relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="fas fa-mobile-screen text-sm"></i>
            </span>
            <input type="text" name="phone" value="{{ $phone }}"
                   placeholder="e.g. 01712345678"
                   class="w-full bg-white/10 border border-white/20 text-white placeholder-slate-500 rounded-xl pl-10 pr-4 py-3 text-[14px] font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
        </div>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-semibold text-[13.5px] transition-colors flex items-center gap-2 whitespace-nowrap">
            <i class="fas fa-magnifying-glass text-sm"></i> Check Now
        </button>
        @if($phone)
        <a href="{{ route('admin.fraud-check') }}"
           class="bg-white/10 hover:bg-white/20 text-white px-4 py-3 rounded-xl font-medium text-[13px] transition-colors flex items-center gap-2">
            <i class="fas fa-xmark"></i>
        </a>
        @endif
    </form>
</div>

@if($phone)

    {{-- API Error --}}
    @if($apiError)
    <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-5 flex items-start gap-3">
        <i class="fas fa-triangle-exclamation text-red-500 mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="text-red-800 font-semibold text-[13px]">API Error</p>
            <p class="text-red-600 text-[12.5px] mt-0.5">{{ $apiError }}</p>
        </div>
    </div>
    @endif

    {{-- API Result Card --}}
    @if($apiResult)
    @php
        $total     = (int) ($apiResult['total_parcels'] ?? 0);
        $delivered = (int) ($apiResult['total_delivered'] ?? 0);
        $cancelled = (int) ($apiResult['total_cancel'] ?? 0);
        $pending   = $total - $delivered - $cancelled;
        $cancelPct = $total > 0 ? round($cancelled / $total * 100, 1) : 0;
        $deliverPct= $total > 0 ? round($delivered / $total * 100, 1) : 0;

        $risk = match(true) {
            $total === 0     => 'unknown',
            $cancelPct >= 50 => 'high',
            $cancelPct >= 25 => 'medium',
            default          => 'low',
        };

        $riskConfig = match($risk) {
            'high'    => ['label'=>'HIGH RISK',    'color'=>'red',   'icon'=>'fa-circle-exclamation',  'bg'=>'bg-red-50',    'border'=>'border-red-200',   'badge'=>'bg-red-500'],
            'medium'  => ['label'=>'MEDIUM RISK',  'color'=>'amber', 'icon'=>'fa-triangle-exclamation','bg'=>'bg-amber-50',  'border'=>'border-amber-200', 'badge'=>'bg-amber-500'],
            'low'     => ['label'=>'LOW RISK',     'color'=>'emerald','icon'=>'fa-circle-check',       'bg'=>'bg-emerald-50','border'=>'border-emerald-200','badge'=>'bg-emerald-500'],
            default   => ['label'=>'NO DATA',      'color'=>'slate', 'icon'=>'fa-circle-question',    'bg'=>'bg-slate-50',  'border'=>'border-slate-200', 'badge'=>'bg-slate-400'],
        };
    @endphp

    <div class="bg-white rounded-2xl border {{ $riskConfig['border'] }} shadow-sm overflow-hidden mb-5">
        {{-- Header --}}
        <div class="{{ $riskConfig['bg'] }} px-5 py-4 border-b {{ $riskConfig['border'] }} flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <i class="fas {{ $riskConfig['icon'] }} text-{{ $riskConfig['color'] }}-500 text-xl"></i>
                <div>
                    <p class="text-[11px] font-semibold text-{{ $riskConfig['color'] }}-600 tracking-widest uppercase">Fraud Check Result</p>
                    <p class="text-slate-800 font-bold text-[15px] mt-0.5">{{ $phone }}</p>
                </div>
            </div>
            <span class="{{ $riskConfig['badge'] }} text-white text-[12px] font-bold px-4 py-1.5 rounded-full shadow-sm tracking-wide">
                {{ $riskConfig['label'] }}
            </span>
        </div>

        {{-- Stats grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-0 divide-x divide-slate-100 divide-y md:divide-y-0">
            <div class="px-5 py-5 text-center">
                <p class="text-[11.5px] text-slate-500 mb-1">Total Parcels</p>
                <p class="text-3xl font-black text-slate-800">{{ $total }}</p>
            </div>
            <div class="px-5 py-5 text-center">
                <p class="text-[11.5px] text-slate-500 mb-1">Delivered</p>
                <p class="text-3xl font-black text-emerald-600">{{ $delivered }}</p>
                @if($total > 0)
                <p class="text-[11px] text-emerald-500 mt-0.5">{{ $deliverPct }}%</p>
                @endif
            </div>
            <div class="px-5 py-5 text-center">
                <p class="text-[11.5px] text-slate-500 mb-1">Cancelled</p>
                <p class="text-3xl font-black text-red-500">{{ $cancelled }}</p>
                @if($total > 0)
                <p class="text-[11px] text-red-400 mt-0.5">{{ $cancelPct }}%</p>
                @endif
            </div>
            <div class="px-5 py-5 text-center">
                <p class="text-[11.5px] text-slate-500 mb-1">Pending</p>
                <p class="text-3xl font-black text-amber-500">{{ max(0, $pending) }}</p>
            </div>
        </div>

        {{-- Progress bar --}}
        @if($total > 0)
        <div class="px-5 pb-5">
            <p class="text-[11px] text-slate-400 mb-2">Delivery breakdown</p>
            <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden flex">
                <div class="bg-emerald-500 h-full transition-all" style="width: {{ $deliverPct }}%"></div>
                <div class="bg-amber-400 h-full transition-all" style="width: {{ $total > 0 ? round(max(0,$pending)/$total*100,1) : 0 }}%"></div>
                <div class="bg-red-500 h-full transition-all" style="width: {{ $cancelPct }}%"></div>
            </div>
            <div class="flex gap-4 mt-2">
                <span class="flex items-center gap-1 text-[10.5px] text-slate-500"><span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Delivered</span>
                <span class="flex items-center gap-1 text-[10.5px] text-slate-500"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400"></span> Pending</span>
                <span class="flex items-center gap-1 text-[10.5px] text-slate-500"><span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span> Cancelled</span>
            </div>
        </div>
        @endif

        {{-- Courier-wise breakdown --}}
        @php
            $courierOrder = ['Pathao', 'Steadfast', 'Paperfly', 'Redx', 'Carrybee'];
            $apisByName = collect($apiResult['apis'] ?? [])->mapWithKeys(fn($d) => [strtolower($d['courier_name'] ?? '') => $d]);
            $courierRows = collect($courierOrder)->map(function ($name) use ($apisByName) {
                $d = $apisByName->get(strtolower($name));
                $cTotal     = (int) ($d['total_parcels'] ?? 0);
                $cDelivered = (int) ($d['total_delivered_parcels'] ?? 0);
                $cCancelled = (int) ($d['total_cancelled_parcels'] ?? 0);
                return [
                    'name'      => $name,
                    'total'     => $cTotal,
                    'delivered' => $cDelivered,
                    'cancelled' => $cCancelled,
                    'rate'      => $cTotal > 0 ? round($cDelivered / $cTotal * 100, 1) . '%' : 'N/A',
                ];
            });
        @endphp
        <div class="border-t border-slate-100 px-5 py-4">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide mb-3">Courier-wise Breakdown</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Courier</th>
                            <th class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Orders</th>
                            <th class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Delivered</th>
                            <th class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Cancelled</th>
                            <th class="px-3 py-2 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Delivery Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($courierRows as $row)
                        <tr>
                            <td class="px-3 py-2.5 font-semibold text-slate-700">{{ $row['name'] }}</td>
                            <td class="px-3 py-2.5 text-center text-slate-600">{{ $row['total'] }}</td>
                            <td class="px-3 py-2.5 text-center text-emerald-600 font-medium">{{ $row['delivered'] }}</td>
                            <td class="px-3 py-2.5 text-center text-red-500 font-medium">{{ $row['cancelled'] }}</td>
                            <td class="px-3 py-2.5 text-center font-semibold {{ $row['rate'] === 'N/A' ? 'text-slate-400' : 'text-slate-700' }}">{{ $row['rate'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @elseif(!$apiError)
    {{-- No data returned --}}
    <div class="bg-slate-50 border border-slate-200 rounded-2xl px-5 py-6 mb-5 text-center">
        <i class="fas fa-circle-question text-slate-300 text-3xl mb-2"></i>
        <p class="text-slate-500 text-[13px]">No data returned from fraud checker API for this number.</p>
    </div>
    @endif

    {{-- Matching customers in this POS --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-users text-blue-500 text-sm"></i>
            </div>
            <div>
                <h3 class="text-[14px] font-bold text-slate-800">POS Records for {{ $phone }}</h3>
                <p class="text-[11.5px] text-slate-400">
                    {{ $customers->count() > 0 ? $customers->count().' customer record(s) found across branches' : 'No records in this POS system' }}
                </p>
            </div>
        </div>

        @if($customers->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Name</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Branch</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Email</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Orders</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($customers as $c)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-800 text-[13px]">{{ $c->name }}</td>
                        <td class="px-4 py-3">
                            <span class="bg-blue-50 text-blue-700 text-[11px] font-medium px-2 py-0.5 rounded-full">
                                {{ $c->vendor->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-[12.5px] hidden md:table-cell">{{ $c->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-center font-medium text-slate-700">{{ $c->sales_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">
                            {{ $currency }}{{ number_format($c->total_spent ?? 0, 0) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-8 text-center">
            <i class="fas fa-user-slash text-slate-200 text-3xl mb-2"></i>
            <p class="text-slate-400 text-[13px]">This phone number is not registered as a customer in any branch.</p>
        </div>
        @endif
    </div>

@else
{{-- Empty state --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-14 text-center">
    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-shield-halved text-slate-300 text-2xl"></i>
    </div>
    <h3 class="text-slate-700 font-semibold text-[15px] mb-1.5">Enter a phone number to check</h3>
    <p class="text-slate-400 text-[13px] max-w-sm mx-auto">
        This tool checks a customer's parcel delivery history using the fraudchecker.link API to help identify risky buyers.
    </p>
    <div class="mt-5 flex flex-wrap justify-center gap-5 text-[12px] text-slate-400">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> 0–24% cancel = Low Risk</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> 25–49% cancel = Medium Risk</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> 50%+ cancel = High Risk</span>
    </div>
</div>
@endif

@endsection
