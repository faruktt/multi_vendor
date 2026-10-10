@extends('layouts.app')

@section('title', 'Shipping Charges & Delivery Zones')

@section('content')
<div class="max-w-6xl mx-auto space-y-6"
     x-data="{
        insideCharge: {{ old('delivery_charge_inside_dhaka', $vendor->delivery_charge_inside_dhaka ?? 60) }},
        subCharge: {{ old('delivery_charge_sub_dhaka', $vendor->delivery_charge_sub_dhaka ?? 100) }},
        outsideCharge: {{ old('delivery_charge_outside_dhaka', $vendor->delivery_charge_outside_dhaka ?? 150) }},
        
        selectedThanas: {{ json_encode($subDhakaThanas ?? []) }},
        allLocations: {{ json_encode($allDistrictsWithThanas) }},
        allDistricts: {{ json_encode($allDistricts) }},
        
        activeDistrict: 'Dhaka',
        districtFilter: '',
        thanaSearch: '',
        showSummaryModal: false,
        
        testDistrict: 'Dhaka',
        testThana: 'Savar',

        init() {
            // Ensure selectedThanas is an object
            if (Array.isArray(this.selectedThanas)) {
                this.selectedThanas = {};
            }
            // Auto initialize test thana
            if (this.allLocations[this.testDistrict] && this.allLocations[this.testDistrict].length > 0) {
                this.testThana = this.allLocations[this.testDistrict].includes('Savar') 
                    ? 'Savar' 
                    : this.allLocations[this.testDistrict][0];
            }
        },

        isThanaSelected(dist, thana) {
            if (!this.selectedThanas || !this.selectedThanas[dist]) return false;
            return this.selectedThanas[dist].includes(thana);
        },

        toggleThana(dist, thana) {
            if (!this.selectedThanas) this.selectedThanas = {};
            if (!this.selectedThanas[dist]) this.selectedThanas[dist] = [];

            if (this.selectedThanas[dist].includes(thana)) {
                this.selectedThanas[dist] = this.selectedThanas[dist].filter(t => t !== thana);
                if (this.selectedThanas[dist].length === 0) {
                    delete this.selectedThanas[dist];
                }
            } else {
                this.selectedThanas[dist].push(thana);
            }
            // Trigger reactivity
            this.selectedThanas = Object.assign({}, this.selectedThanas);
        },

        selectAllInDistrict(dist) {
            if (!this.selectedThanas) this.selectedThanas = {};
            const thanas = this.allLocations[dist] || [];
            this.selectedThanas[dist] = [...thanas];
            this.selectedThanas = Object.assign({}, this.selectedThanas);
        },

        deselectAllInDistrict(dist) {
            if (this.selectedThanas && this.selectedThanas[dist]) {
                delete this.selectedThanas[dist];
                this.selectedThanas = Object.assign({}, this.selectedThanas);
            }
        },

        selectDhakaOuter() {
            if (!this.selectedThanas) this.selectedThanas = {};
            const outer = ['Savar', 'Dhamrai', 'Keraniganj', 'Nawabganj', 'Dohar'];
            const available = this.allLocations['Dhaka'] || [];
            this.selectedThanas['Dhaka'] = outer.filter(t => available.includes(t));
            this.selectedThanas = Object.assign({}, this.selectedThanas);
        },

        applyStandardPreset() {
            const standard = {
                'Dhaka': ['Savar', 'Dhamrai', 'Keraniganj', 'Nawabganj', 'Dohar'],
                'Gazipur': [...(this.allLocations['Gazipur'] || [])],
                'Narayanganj': [...(this.allLocations['Narayanganj'] || [])],
                'Munshiganj': [...(this.allLocations['Munshiganj'] || [])],
                'Narsingdi': [...(this.allLocations['Narsingdi'] || [])],
                'Manikganj': [...(this.allLocations['Manikganj'] || [])],
            };
            this.selectedThanas = Object.assign({}, standard);
        },

        clearAll() {
            this.selectedThanas = {};
        },

        getSelectedCount(dist) {
            if (!this.selectedThanas || !this.selectedThanas[dist]) return 0;
            return this.selectedThanas[dist].length;
        },

        getTotalSelectedCount() {
            if (!this.selectedThanas) return 0;
            return Object.values(this.selectedThanas).reduce((acc, curr) => acc + (Array.isArray(curr) ? curr.length : 0), 0);
        },

        getTotalDistrictsCount() {
            if (!this.selectedThanas) return 0;
            return Object.keys(this.selectedThanas).filter(d => (this.selectedThanas[d] || []).length > 0).length;
        },

        get activeThanas() {
            const list = this.allLocations[this.activeDistrict] || [];
            if (!this.thanaSearch.trim()) return list;
            const q = this.thanaSearch.toLowerCase();
            return list.filter(t => t.toLowerCase().includes(q));
        },

        get filteredDistricts() {
            if (!this.districtFilter.trim()) return this.allDistricts;
            const q = this.districtFilter.toLowerCase();
            return this.allDistricts.filter(d => d.toLowerCase().includes(q));
        },

        onTestDistrictChange() {
            const thanas = this.allLocations[this.testDistrict] || [];
            this.testThana = thanas.length > 0 ? thanas[0] : '';
        },

        get testThanaList() {
            return this.allLocations[this.testDistrict] || [];
        },

        get testResult() {
            const isSub = this.isThanaSelected(this.testDistrict, this.testThana);
            if (isSub) {
                return {
                    zone: 'Sub Dhaka',
                    charge: this.subCharge,
                    color: 'bg-blue-50 text-blue-700 border-blue-200',
                    desc: 'Selected as Sub Dhaka thana'
                };
            }
            if (this.testDistrict === 'Dhaka') {
                return {
                    zone: 'Inside Dhaka',
                    charge: this.insideCharge,
                    color: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    desc: 'Dhaka Metropolitan (non-sub thana)'
                };
            }
            return {
                zone: 'Outside Dhaka',
                charge: this.outsideCharge,
                color: 'bg-amber-50 text-amber-700 border-amber-200',
                desc: 'All other districts & thanas'
            };
        }
     }">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-xs">
                <i class="fas fa-truck-fast"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Shipping Charges &amp; Delivery Zones</h1>
                <p class="text-xs text-slate-500 mt-0.5">Configure Thana-wise Sub-Dhaka zones and delivery rates for Inside, Sub-Dhaka, and Outside Dhaka.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-2xl flex items-center gap-2.5 shadow-sm">
        <i class="fas fa-check-circle text-emerald-600 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.shipping-charges.update') }}" class="space-y-6">
        @csrf
        {{-- Hidden JSON input holding all selected thanas --}}
        <input type="hidden" name="sub_dhaka_thanas" :value="JSON.stringify(selectedThanas)">

        {{-- 3 Zone Summary Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            {{-- 1. Inside Dhaka --}}
            <div class="bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -mr-8 -mt-8 pointer-events-none opacity-60"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Zone 1
                        </span>
                        <span class="text-[11px] font-semibold text-emerald-600">Dhaka Metro</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Inside Dhaka</h3>
                    <p class="text-xs text-slate-500 mt-1">Dhaka Metropolitan area (applies to all non-suburban Dhaka thanas automatically).</p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Delivery Fee (৳) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">৳</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge_inside_dhaka"
                               x-model="insideCharge" required
                               class="w-full pl-8 pr-4 py-2.5 text-base font-bold text-slate-800 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-colors">
                    </div>
                </div>
            </div>

            {{-- 2. Sub Dhaka --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-8 -mt-8 pointer-events-none opacity-60"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Zone 2
                        </span>
                        <span class="text-[11px] font-semibold text-blue-600 font-mono" x-text="getTotalSelectedCount() + ' thanas in ' + getTotalDistrictsCount() + ' dist.'"></span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Sub Dhaka</h3>
                    <p class="text-xs text-slate-500 mt-1">Suburban thanas around Dhaka (e.g. Savar, Keraniganj) and designated border thanas.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Delivery Fee (৳) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">৳</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge_sub_dhaka"
                               x-model="subCharge" required
                               class="w-full pl-8 pr-4 py-2.5 text-base font-bold text-slate-800 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            {{-- 3. Outside Dhaka --}}
            <div class="bg-white rounded-2xl border border-amber-100 p-5 shadow-sm relative overflow-hidden flex flex-col justify-between">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-full -mr-8 -mt-8 pointer-events-none opacity-60"></div>
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Zone 3
                        </span>
                        <span class="text-[11px] font-semibold text-slate-400">All Remaining</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Outside Dhaka</h3>
                    <p class="text-xs text-slate-500 mt-1">All other districts and thanas across Bangladesh outside Dhaka &amp; Sub-Dhaka.</p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Delivery Fee (৳) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">৳</span>
                        <input type="number" step="0.01" min="0" name="delivery_charge_outside_dhaka"
                               x-model="outsideCharge" required
                               class="w-full pl-8 pr-4 py-2.5 text-base font-bold text-slate-800 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                    </div>
                </div>
            </div>

        </div>

        {{-- Sub-Dhaka Thana Configuration Card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            
            {{-- Section Header & Global Action Buttons --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-map-marked-alt text-blue-600"></i>
                        <span>Thana-Wise Sub-Dhaka Configuration</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Select a district, then check the thanas that should be treated as <strong class="text-blue-600 font-semibold">Sub Dhaka</strong>. During checkout, selecting these thanas will automatically apply Sub-Dhaka delivery rates.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="applyStandardPreset()"
                            class="px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl transition-colors flex items-center gap-1.5">
                        <i class="fas fa-magic text-[11px]"></i>
                        <span>Standard Sub-Dhaka Preset</span>
                    </button>
                    <button type="button" @click="clearAll()"
                            class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-red-600 bg-slate-50 hover:bg-red-50 border border-slate-200 rounded-xl transition-colors flex items-center gap-1.5">
                        <i class="fas fa-trash-can text-[11px]"></i>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>

            {{-- 1. District Selection Tabs & Search --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                        1. Select District:
                    </label>
                    <span class="text-xs text-slate-400">
                        Active: <strong class="text-slate-800 font-bold" x-text="activeDistrict"></strong> 
                        (<span class="text-blue-600 font-bold" x-text="getSelectedCount(activeDistrict)"></span> of <span x-text="(allLocations[activeDistrict] || []).length"></span> thanas selected as Sub Dhaka)
                    </span>
                </div>

                {{-- Popular District Fast-Pills --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-0.5">
                    @foreach(['Dhaka', 'Gazipur', 'Narayanganj', 'Munshiganj', 'Narsingdi', 'Manikganj', 'Chattogram', 'Sylhet'] as $quickDist)
                    <button type="button" @click="activeDistrict = '{{ $quickDist }}'; thanaSearch = ''"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 border transition-all flex-shrink-0 cursor-pointer"
                            :class="activeDistrict === '{{ $quickDist }}' 
                                ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                                : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'">
                        <span>{{ $quickDist }}</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] font-bold"
                              :class="activeDistrict === '{{ $quickDist }}' 
                                  ? 'bg-blue-800/60 text-white' 
                                  : (getSelectedCount('{{ $quickDist }}') > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600')"
                              x-text="getSelectedCount('{{ $quickDist }}')"></span>
                    </button>
                    @endforeach

                    {{-- All 64 Districts Dropdown Selector --}}
                    <div class="relative flex-shrink-0">
                        <select x-model="activeDistrict" @change="thanaSearch = ''"
                                class="px-3 py-1.5 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <template x-for="d in allDistricts" :key="d">
                                <option :value="d" x-text="d + (getSelectedCount(d) > 0 ? ' (' + getSelectedCount(d) + ')' : '')"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 2. Thanas for Active District Panel --}}
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 sm:p-5 space-y-4">
                
                {{-- District Notice & Controls --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/70">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-extrabold text-slate-800" x-text="activeDistrict + ' District'"></span>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                                  x-text="getSelectedCount(activeDistrict) + ' Thanas selected as Sub Dhaka'"></span>
                        </div>
                        
                        {{-- Contextual explanation for Dhaka vs other districts --}}
                        <template x-if="activeDistrict === 'Dhaka'">
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                <span><strong>For Dhaka District:</strong> Checked thanas will be marked as <span class="text-blue-600 font-bold">Sub Dhaka</span> (e.g., Savar, Dhamrai, Keraniganj, Nawabganj, Dohar). All unchecked thanas will remain <span class="text-emerald-600 font-bold">Inside Dhaka</span>.</span>
                            </p>
                        </template>
                        <template x-if="activeDistrict !== 'Dhaka'">
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                <span><strong>For Other Districts:</strong> Checked thanas will be marked as <span class="text-blue-600 font-bold">Sub Dhaka</span>. All unchecked thanas will remain <span class="text-amber-600 font-bold">Outside Dhaka</span>.</span>
                            </p>
                        </template>
                    </div>

                    {{-- Actions for this district --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <template x-if="activeDistrict === 'Dhaka'">
                            <button type="button" @click="selectDhakaOuter()"
                                    class="px-2.5 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                                + Savar, Dhamrai, Keraniganj, Nawabganj, Dohar
                            </button>
                        </template>
                        <button type="button" @click="selectAllInDistrict(activeDistrict)"
                                class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                            Select All
                        </button>
                        <button type="button" @click="deselectAllInDistrict(activeDistrict)"
                                class="px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:text-red-600 bg-slate-100 hover:bg-red-50 rounded-lg transition-colors">
                            Deselect All
                        </button>
                    </div>
                </div>

                {{-- Thana Search Filter --}}
                <div class="relative max-w-xs">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" x-model="thanaSearch" :placeholder="'Search thanas in ' + activeDistrict + '...'"
                           class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>

                {{-- Thanas Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 max-h-96 overflow-y-auto p-1">
                    <template x-for="t in activeThanas" :key="t">
                        <div @click="toggleThana(activeDistrict, t)"
                             class="flex items-center justify-between p-2.5 rounded-xl border text-xs cursor-pointer select-none transition-all shadow-2xs"
                             :class="isThanaSelected(activeDistrict, t)
                                 ? 'border-blue-500 bg-blue-50/90 text-blue-900 font-bold ring-1 ring-blue-400/40' 
                                 : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50'">
                            
                            <div class="flex items-center gap-2 truncate">
                                <input type="checkbox"
                                       :checked="isThanaSelected(activeDistrict, t)"
                                       @click.stop="toggleThana(activeDistrict, t)"
                                       class="w-3.5 h-3.5 rounded text-blue-600 border-slate-300 focus:ring-blue-500 pointer-events-none">
                                <span x-text="t" class="truncate"></span>
                            </div>

                            {{-- Status Badge on card --}}
                            <div>
                                <template x-if="isThanaSelected(activeDistrict, t)">
                                    <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-blue-600 text-white flex-shrink-0">
                                        Sub
                                    </span>
                                </template>
                                <template x-if="!isThanaSelected(activeDistrict, t) && activeDistrict === 'Dhaka'">
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 flex-shrink-0">
                                        Inside
                                    </span>
                                </template>
                                <template x-if="!isThanaSelected(activeDistrict, t) && activeDistrict !== 'Dhaka'">
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 flex-shrink-0">
                                        Outside
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 3. Configured Sub-Dhaka Overview Summary Bar --}}
            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                        <i class="fas fa-list-check text-blue-600"></i>
                        <span>Current Sub-Dhaka Coverage Overview (<span x-text="getTotalSelectedCount()"></span> Thanas total):</span>
                    </span>
                </div>
                
                <div class="flex flex-wrap gap-2 max-h-28 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200/80">
                    <template x-for="d in Object.keys(selectedThanas || {})" :key="d">
                        <template x-if="(selectedThanas[d] || []).length > 0">
                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-slate-800 border border-slate-200 shadow-2xs">
                                <span class="text-blue-700" x-text="d"></span>
                                <span class="text-slate-400 font-mono text-[11px]" x-text="'(' + selectedThanas[d].length + ')'"></span>
                                <button type="button" @click="deselectAllInDistrict(d)" class="text-slate-400 hover:text-red-600 ml-1">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            </div>
                        </template>
                    </template>
                    <template x-if="getTotalSelectedCount() === 0">
                        <span class="text-xs text-slate-400 italic">No thanas currently configured for Sub-Dhaka.</span>
                    </template>
                </div>
            </div>

        </div>

        {{-- Live Test Simulator Card --}}
        <div class="bg-gradient-to-r from-slate-50 to-blue-50/50 rounded-2xl border border-slate-200 p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-blue-600 text-lg shadow-2xs">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Live Zone Detection Simulator</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Select any district and thana to preview the exact shipping fee applied during checkout.</p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto flex-wrap sm:flex-nowrap">
                {{-- Test District --}}
                <select x-model="testDistrict" @change="onTestDistrictChange()" 
                        class="px-3.5 py-2 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-2xs">
                    <template x-for="d in allDistricts" :key="d">
                        <option :value="d" x-text="d"></option>
                    </template>
                </select>

                {{-- Test Thana --}}
                <select x-model="testThana"
                        class="px-3.5 py-2 text-xs font-semibold bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-2xs">
                    <template x-for="t in testThanaList" :key="t">
                        <option :value="t" x-text="t"></option>
                    </template>
                </select>

                {{-- Test Result Indicator --}}
                <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl border text-xs font-bold shadow-2xs flex-shrink-0"
                     :class="testResult.color">
                    <span x-text="testResult.zone"></span>
                    <span class="opacity-40">|</span>
                    <span>৳<span x-text="testResult.charge"></span></span>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="px-7 py-3.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                <i class="fas fa-save"></i>
                <span>Save Shipping Settings</span>
            </button>
        </div>

    </form>
</div>
@endsection
