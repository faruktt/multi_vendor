@extends('layouts.app')
@section('title', 'Coupons Management')
@section('heading', 'Coupons & Discounts')

@section('content')
<div class="py-4 space-y-6" x-data="couponManagement()">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Coupons</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ $totalCoupons }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Configured promo codes</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Coupons</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $activeCoupons }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Ready for redemption</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Times Used</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-600">{{ number_format($totalRedemptions) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Total order redemptions</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Discount Given</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-600">৳{{ number_format($totalDiscountGiven, 2) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Cumulative savings given</div>
        </div>
    </div>

    {{-- Filter & Action Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="lg:col-span-4">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search coupon code or name..."
                       class="w-full h-11 border border-slate-200 rounded-xl px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Applicable For</label>
                <select name="applicable_for" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    <option value="">All Audiences</option>
                    <option value="everyone" {{ request('applicable_for') === 'everyone' ? 'selected' : '' }}>Everyone</option>
                    <option value="customer" {{ request('applicable_for') === 'customer' ? 'selected' : '' }}>Customer Only</option>
                    <option value="reseller" {{ request('applicable_for') === 'reseller' ? 'selected' : '' }}>Reseller Only</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Discount Type</label>
                <select name="discount_type" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    <option value="">All Types</option>
                    <option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ request('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 h-11 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <button type="button" @click="openCreateModal()"
                        class="h-11 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-emerald-100 flex items-center justify-center gap-1.5 flex-shrink-0">
                    <i class="fas fa-plus"></i> Create
                </button>
            </div>
        </form>
    </div>

    {{-- Coupons Table --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-black text-slate-800 text-base">Coupon Codes Directory</h3>
                <p class="text-xs text-slate-400">Manage promotional discount codes for Customers and Resellers</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Showing {{ $coupons->count() }} of {{ $coupons->total() }} coupons
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wider font-bold">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Coupon Code</th>
                        <th class="px-5 py-3.5 text-left">Applicable For</th>
                        <th class="px-5 py-3.5 text-left">Discount Value</th>
                        <th class="px-5 py-3.5 text-right">Min Order</th>
                        <th class="px-5 py-3.5 text-left">Validity Period</th>
                        <th class="px-5 py-3.5 text-center">Usage Stats</th>
                        <th class="px-5 py-3.5 text-left">Status</th>
                        <th class="px-5 py-3.5 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-indigo-50/20 transition-colors">
                            {{-- Code & Name --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-black text-xs flex-shrink-0 border border-indigo-100">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div>
                                        <div class="font-mono font-black text-indigo-700 text-base tracking-wide">{{ $coupon->code }}</div>
                                        <div class="text-xs text-slate-500 font-medium">{{ $coupon->name ?? 'No label' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Applicable For --}}
                            <td class="px-5 py-3.5">
                                @if($coupon->applicable_for === 'everyone')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fas fa-users text-[10px]"></i> Everyone
                                    </span>
                                @elseif($coupon->applicable_for === 'customer')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-shopping-bag text-[10px]"></i> Customer Only
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fas fa-user-tag text-[10px]"></i> Reseller Only
                                    </span>
                                @endif
                            </td>

                            {{-- Discount Value --}}
                            <td class="px-5 py-3.5">
                                @if($coupon->discount_type === 'percentage')
                                    <div class="font-black text-slate-800 text-sm">
                                        {{ (float) $coupon->discount_amount }}% OFF
                                    </div>
                                    @if($coupon->max_discount_amount)
                                        <div class="text-[10px] text-slate-400 font-medium">Max discount: ৳{{ number_format($coupon->max_discount_amount, 0) }}</div>
                                    @endif
                                @else
                                    <div class="font-black text-slate-800 text-sm">
                                        ৳{{ number_format($coupon->discount_amount, 2) }} Flat
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-medium">Fixed deduction</div>
                                @endif
                            </td>

                            {{-- Min Order --}}
                            <td class="px-5 py-3.5 text-right font-black text-slate-700">
                                @if($coupon->min_order_amount > 0)
                                    ৳{{ number_format($coupon->min_order_amount, 0) }}
                                @else
                                    <span class="text-xs text-slate-400 font-normal">None (৳0)</span>
                                @endif
                            </td>

                            {{-- Validity Period --}}
                            <td class="px-5 py-3.5 text-xs">
                                @if($coupon->start_date || $coupon->end_date)
                                    <div class="font-semibold text-slate-700">
                                        {{ $coupon->start_date ? $coupon->start_date->format('d M Y') : 'Start' }}
                                        &rarr;
                                        {{ $coupon->end_date ? $coupon->end_date->format('d M Y') : 'Ongoing' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 font-medium">No Expiry Limit</span>
                                @endif
                            </td>

                            {{-- Usage Stats --}}
                            <td class="px-5 py-3.5 text-center">
                                <div class="font-bold text-slate-800">
                                    {{ $coupon->used_count }}
                                    @if($coupon->usage_limit)
                                        <span class="text-slate-400 font-normal text-xs">/ {{ $coupon->usage_limit }}</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400">redemptions</div>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5">
                                <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-colors {{ $coupon->status === 'active' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $coupon->status === 'active' ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                                        {{ ucfirst($coupon->status) }}
                                    </button>
                                </form>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEditModal(@js($coupon))"
                                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 flex items-center justify-center transition-colors">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}"
                                          onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-red-50 text-slate-500 hover:text-red-600 flex items-center justify-center transition-colors">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-ticket-alt text-4xl mb-2 text-slate-300"></i>
                                    <span class="font-semibold text-slate-500">No coupons found</span>
                                    <p class="text-xs text-slate-400 mt-1">Create your first coupon promo code above!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

    {{-- CREATE / EDIT MODAL --}}
    <template x-teleport="body">
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="modalOpen = false"></div>

            <div x-show="modalOpen" x-transition
                 class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 overflow-y-auto max-h-[90vh]">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                    <div>
                        <h3 class="text-lg font-black text-slate-800" x-text="isEditing ? 'Edit Coupon Code' : 'Create New Coupon Code'"></h3>
                        <p class="text-xs text-slate-400">Configure discount value, minimum order requirement, and target audience</p>
                    </div>
                    <button type="button" @click="modalOpen = false"
                            class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-red-50 text-slate-500 hover:text-red-600 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                <form :action="isEditing ? `/admin/coupons/${form.id}` : '{{ route('admin.coupons.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Coupon Code & Title --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Coupon Code *</label>
                            <input type="text" name="code" x-model="form.code" required placeholder="e.g. SAVE50, EID2026"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 font-mono font-bold uppercase text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Coupon Title / Description</label>
                            <input type="text" name="name" x-model="form.name" placeholder="e.g. Eid 10% Discount"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                    </div>

                    {{-- Applicable For (Audience) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Applicable For *
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="applicable_for" value="everyone" x-model="form.applicable_for" class="sr-only peer">
                                <div class="border-2 rounded-2xl p-3 text-center transition-all border-slate-200 peer-checked:border-purple-600 peer-checked:bg-purple-50/50">
                                    <i class="fas fa-users text-purple-600 text-base mb-1 block"></i>
                                    <div class="text-xs font-black text-slate-800">Everyone</div>
                                    <div class="text-[10px] text-slate-400">All users</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="applicable_for" value="customer" x-model="form.applicable_for" class="sr-only peer">
                                <div class="border-2 rounded-2xl p-3 text-center transition-all border-slate-200 peer-checked:border-blue-600 peer-checked:bg-blue-50/50">
                                    <i class="fas fa-shopping-bag text-blue-600 text-base mb-1 block"></i>
                                    <div class="text-xs font-black text-slate-800">Customer Only</div>
                                    <div class="text-[10px] text-slate-400">Retail store</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="applicable_for" value="reseller" x-model="form.applicable_for" class="sr-only peer">
                                <div class="border-2 rounded-2xl p-3 text-center transition-all border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50/50">
                                    <i class="fas fa-user-tag text-emerald-600 text-base mb-1 block"></i>
                                    <div class="text-xs font-black text-slate-800">Reseller Only</div>
                                    <div class="text-[10px] text-slate-400">Reseller portal</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Discount Type & Value --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Discount Type *</label>
                            <select name="discount_type" x-model="form.discount_type" required
                                    class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (৳)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                                <span x-text="form.discount_type === 'percentage' ? 'Discount Percentage (%) *' : 'Discount Amount (৳) *'"></span>
                            </label>
                            <input type="number" step="0.01" min="0.01" name="discount_amount" x-model="form.discount_amount" required
                                   :placeholder="form.discount_type === 'percentage' ? 'e.g. 10 for 10%' : 'e.g. 50.00'"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                    </div>

                    {{-- Conditions: Min Order & Max Discount --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                                Minimum Order Amount (৳)
                            </label>
                            <input type="number" step="0.01" min="0" name="min_order_amount" x-model="form.min_order_amount"
                                   placeholder="e.g. 500 (Leave 0 for none)"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Order subtotal must be equal or above this</span>
                        </div>
                        <div x-show="form.discount_type === 'percentage'">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
                                Max Discount Cap (৳)
                            </label>
                            <input type="number" step="0.01" min="0" name="max_discount_amount" x-model="form.max_discount_amount"
                                   placeholder="e.g. 200 (Optional)"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Upper limit on percentage discount</span>
                        </div>
                    </div>

                    {{-- Validity Dates --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Start Date</label>
                            <input type="date" name="start_date" x-model="form.start_date"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Expiry Date</label>
                            <input type="date" name="end_date" x-model="form.end_date"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                    </div>

                    {{-- Usage Limits & Status --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Total Usage Limit</label>
                            <input type="number" min="1" name="usage_limit" x-model="form.usage_limit"
                                   placeholder="Unlimited"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Per User Limit</label>
                            <input type="number" min="1" name="usage_limit_per_user" x-model="form.usage_limit_per_user"
                                   placeholder="Unlimited"
                                   class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Status *</label>
                            <select name="status" x-model="form.status" required
                                    class="w-full h-11 border border-slate-300 rounded-xl px-3 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100">
                        <button type="button" @click="modalOpen = false"
                                class="flex-1 h-11 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black transition-colors shadow-md shadow-indigo-200">
                            <span x-text="isEditing ? 'Save Changes' : 'Create Coupon'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function couponManagement() {
    return {
        modalOpen: false,
        isEditing: false,
        form: {
            id: null,
            code: '',
            name: '',
            applicable_for: 'everyone',
            discount_type: 'percentage',
            discount_amount: '',
            min_order_amount: '500',
            max_discount_amount: '',
            start_date: '',
            end_date: '',
            usage_limit: '',
            usage_limit_per_user: '',
            status: 'active',
        },
        openCreateModal() {
            this.isEditing = false;
            this.form = {
                id: null,
                code: '',
                name: '',
                applicable_for: 'everyone',
                discount_type: 'percentage',
                discount_amount: '',
                min_order_amount: '500',
                max_discount_amount: '',
                start_date: '',
                end_date: '',
                usage_limit: '',
                usage_limit_per_user: '',
                status: 'active',
            };
            this.modalOpen = true;
        },
        openEditModal(coupon) {
            this.isEditing = true;
            this.form = {
                id: coupon.id,
                code: coupon.code,
                name: coupon.name || '',
                applicable_for: coupon.applicable_for || 'everyone',
                discount_type: coupon.discount_type || 'percentage',
                discount_amount: coupon.discount_amount,
                min_order_amount: coupon.min_order_amount || '0',
                max_discount_amount: coupon.max_discount_amount || '',
                start_date: coupon.start_date ? coupon.start_date.substring(0, 10) : '',
                end_date: coupon.end_date ? coupon.end_date.substring(0, 10) : '',
                usage_limit: coupon.usage_limit || '',
                usage_limit_per_user: coupon.usage_limit_per_user || '',
                status: coupon.status || 'active',
            };
            this.modalOpen = true;
        }
    }
}
</script>
@endsection
