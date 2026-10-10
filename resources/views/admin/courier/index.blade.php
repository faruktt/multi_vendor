@extends('layouts.app')

@section('title', 'Courier API Settings')
@section('heading', 'Courier Settings')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="courierSettingsPage()">

    {{-- ══ Header Strip ══ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-truck-fast"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Courier API Settings</h1>
                <p class="text-xs text-slate-400 mt-0.5">Configure and activate Steadfast, Pathao, and RedX courier APIs for direct sales booking</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold">
                <i class="fas fa-circle-check text-emerald-500 text-[10px]"></i>
                <span>Active Couriers: <span class="font-bold text-blue-600" x-text="activeCount"></span></span>
            </span>
        </div>
    </div>

    {{-- ══ Info Notice ══ --}}
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50/50 to-blue-50 border border-blue-100/80 rounded-2xl p-4 flex items-start gap-3 text-xs text-slate-600">
        <i class="fas fa-circle-info text-blue-500 text-base mt-0.5 flex-shrink-0"></i>
        <div class="space-y-1">
            <p class="font-semibold text-slate-800 text-[13px]">Instructions:</p>
            <p>1. Log into your courier merchant portal to obtain API credentials and enter them below.</p>
            <p>2. Enable <strong>Status "Active"</strong> for courier services you want to activate on the Sales page.</p>
            <p>3. On the Sales page, click <strong>"Send to Courier"</strong> to dispatch orders and generate tracking parcels instantly.</p>
        </div>
    </div>

    {{-- ══ Flash Alerts ══ --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-xs flex items-center gap-2">
        <i class="fas fa-circle-check text-emerald-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- ══ Courier Cards Grid ══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        @foreach($couriers as $courier)
        @php
            $accentColor = match($courier->code) {
                'steadfast' => 'teal',
                'pathao'    => 'rose',
                'redx'      => 'indigo',
                default     => 'blue',
            };
            $icon = match($courier->code) {
                'steadfast' => 'fas fa-shipping-fast',
                'pathao'    => 'fas fa-motorcycle',
                'redx'      => 'fas fa-truck-fast',
                default     => 'fas fa-truck',
            };
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between"
             id="courier-card-{{ $courier->id }}">

            {{-- Card Header --}}
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-{{ $accentColor }}-50 text-{{ $accentColor }}-600 flex items-center justify-center text-lg shadow-xs">
                            <i class="{{ $icon }}"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-800 text-base leading-tight">{{ $courier->name }}</h2>
                            <span class="text-[11px] font-mono text-slate-400 capitalize">{{ $courier->code }}</span>
                        </div>
                    </div>

                    {{-- Active Toggle Switch --}}
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="toggleStatus({{ $courier->id }}, '{{ route('admin.couriers.toggle', $courier) }}')"
                                :disabled="toggling === {{ $courier->id }}"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="statuses[{{ $courier->id }}] ? 'bg-emerald-500' : 'bg-slate-200'"
                                title="Toggle Active / Inactive">
                            <span class="sr-only">Toggle</span>
                            <span aria-hidden="true"
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                  :class="statuses[{{ $courier->id }}] ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="font-medium" :class="statuses[{{ $courier->id }}] ? 'text-emerald-600' : 'text-slate-400'">
                        <i class="fas fa-circle text-[8px] mr-1" :class="statuses[{{ $courier->id }}] ? 'text-emerald-500' : 'text-slate-300'"></i>
                        <span x-text="statuses[{{ $courier->id }}] ? 'Active (Shows on Sales Page)' : 'Inactive'"></span>
                    </span>

                    <button type="button"
                            @click="testConnection({{ $courier->id }}, '{{ route('admin.couriers.test', $courier) }}')"
                            :disabled="testing === {{ $courier->id }}"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition-colors shadow-2xs cursor-pointer">
                        <i class="fas fa-plug text-[9.5px] text-blue-500" :class="{ 'fa-spin fa-spinner': testing === {{ $courier->id }} }"></i>
                        <span x-text="testing === {{ $courier->id }} ? 'Testing...' : 'Test API'"></span>
                    </button>
                </div>

                {{-- Test Result Alert --}}
                <div x-show="testResults[{{ $courier->id }}]" x-cloak class="mt-2.5">
                    <div class="p-2.5 rounded-xl text-xs flex items-start gap-2"
                         :class="testResults[{{ $courier->id }}]?.success ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'">
                        <i class="fas text-[11px] mt-0.5" :class="testResults[{{ $courier->id }}]?.success ? 'fa-circle-check text-emerald-500' : 'fa-circle-xmark text-red-500'"></i>
                        <span class="flex-1 break-words" x-text="testResults[{{ $courier->id }}]?.message"></span>
                        <button @click="testResults[{{ $courier->id }}] = null" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Card Form Body --}}
            <form action="{{ route('admin.couriers.update', $courier) }}" method="POST" class="p-5 space-y-3.5 flex-1 flex flex-col justify-between">
                @csrf

                <div class="space-y-3">
                    {{-- Display Name --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Courier Display Name</label>
                        <input type="text" name="name" value="{{ old('name', $courier->name) }}" required
                               class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- STEADFAST SPECIFIC FIELDS --}}
                    @if($courier->code === 'steadfast')
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">API Key</label>
                        <input type="text" name="api_key" value="{{ old('api_key', $courier->api_key) }}" placeholder="Steadfast API Key"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Secret Key</label>
                        <input type="password" name="secret_key" value="{{ old('secret_key', $courier->secret_key) }}" placeholder="Steadfast Secret Key"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Base URL (API Endpoint)</label>
                        <input type="text" name="base_url" value="{{ old('base_url', $courier->base_url ?: 'https://portal.steadfast.com.bd/api/v1') }}"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endif

                    {{-- PATHAO SPECIFIC FIELDS --}}
                    @if($courier->code === 'pathao')
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Client ID</label>
                        <input type="text" name="client_id" value="{{ old('client_id', $courier->client_id) }}" placeholder="Pathao Client ID"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Client Secret / Secret Key</label>
                        <input type="password" name="secret_key" value="{{ old('secret_key', $courier->secret_key) }}" placeholder="Pathao Client Secret"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Username / Merchant Email</label>
                        <input type="text" name="username" value="{{ old('username', $courier->username) }}" placeholder="merchant@email.com"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Password</label>
                        <input type="password" name="password" value="{{ old('password', $courier->password) }}" placeholder="Pathao Password"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Store ID (Pickup Store)</label>
                        <input type="text" name="store_id" value="{{ old('store_id', $courier->store_id) }}" placeholder="Store ID (e.g. 12345)"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-[10px] text-slate-400 mt-0.5">Leave blank to automatically use your default primary store</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Base URL (API Endpoint)</label>
                        <input type="text" name="base_url" value="{{ old('base_url', $courier->base_url ?: 'https://api-hermes.pathao.com') }}"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endif

                    {{-- REDX SPECIFIC FIELDS --}}
                    @if($courier->code === 'redx')
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">API Access Token</label>
                        <input type="password" name="api_key" value="{{ old('api_key', $courier->api_key) }}" placeholder="RedX API Access Token"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-1 uppercase tracking-wider">Base URL (API Endpoint)</label>
                        <input type="text" name="base_url" value="{{ old('base_url', $courier->base_url ?: 'https://openapi.redx.com.bd/v1.0.0') }}"
                               class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endif
                </div>

                {{-- Save Button --}}
                <div class="pt-3 border-t border-slate-100">
                    <button type="submit"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs py-2.5 rounded-xl transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fas fa-save text-[11px]"></i>
                        <span>Save Configuration</span>
                    </button>
                </div>
            </form>

        </div>
        @endforeach

    </div>

</div>

@push('scripts')
<script>
function courierSettingsPage() {
    return {
        statuses: {
            @foreach($couriers as $c)
            {{ $c->id }}: {{ $c->is_active ? 'true' : 'false' }},
            @endforeach
        },
        toggling: null,
        testing: null,
        testResults: {},

        get activeCount() {
            return Object.values(this.statuses).filter(Boolean).length;
        },

        async toggleStatus(id, url) {
            this.toggling = id;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.statuses[id] = data.is_active;
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    }
                }
            } catch (err) {
                alert('Failed to update courier status.');
            } finally {
                this.toggling = null;
            }
        },

        async testConnection(id, url) {
            this.testing = id;
            this.testResults[id] = null;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                this.testResults[id] = data;
            } catch (err) {
                this.testResults[id] = {
                    success: false,
                    message: 'API connection test failed: ' + err.message
                };
            } finally {
                this.testing = null;
            }
        }
    };
}
</script>
@endpush
@endsection
