@extends('layouts.app')
@section('title', 'Customers')
@section('heading', 'Customer Management')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')
<div x-data="customerPage()">

{{-- ── Toolbar ──────────────────────────────────────────────────── --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap gap-2.5 items-center">
    <div class="relative flex-1 min-w-[200px]">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, phone or email..."
               class="w-full pl-8 pr-4 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
    </div>
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-1.5 transition-colors">
        <i class="fas fa-filter text-xs"></i> Search
    </button>
    @if(request('search'))
    <a href="{{ route('branch.customers.index', $branch) }}"
       class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50">Reset</a>
    @endif
    <button type="button" @click="addOpen = true"
            class="ml-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
        <i class="fas fa-plus"></i> Add Customer
    </button>
</form>

{{-- ── Stats ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-users text-blue-600 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Customers</p>
                <p class="text-xl font-bold text-slate-800">{{ $totals['customers'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-receipt text-purple-600 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Sales</p>
                <p class="text-xl font-bold text-slate-800">{{ $totals['orders'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-money-bill-wave text-emerald-600 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Revenue</p>
                <p class="text-xl font-bold text-slate-800">{{ $currency }}{{ number_format($totals['revenue'], 0) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-red-500 text-sm"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Due</p>
                <p class="text-xl font-bold {{ $totals['due'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">{{ $currency }}{{ number_format($totals['due'], 0) }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Table ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Customer</th>
                <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Address</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Orders</th>
                <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Total Spent</th>
                <th class="px-3 py-3 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Due</th>
                <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($customers as $customer)
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3 text-slate-400 text-xs">{{ $customers->firstItem() + $loop->index }}</td>

                <td class="px-3 py-3">
                    <div class="flex items-center gap-3">
                        @if($customer->image)
                        <img src="{{ Storage::url($customer->image) }}" alt="{{ $customer->name }}"
                             class="w-9 h-9 rounded-xl object-cover flex-shrink-0 border border-slate-200">
                        @else
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 font-bold text-sm text-white"
                             style="background: linear-gradient(135deg, #06b6d4, #3b82f6)">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-800 text-[13px]">{{ $customer->name }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5">
                                @if($customer->phone)
                                <span class="text-[11px] text-slate-400">{{ $customer->phone }}</span>
                                <a href="tel:{{ $customer->whatsapp_number }}" title="Call"
                                   class="w-5 h-5 rounded-md bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <i class="fas fa-phone text-[9px]"></i>
                                </a>
                                <a href="https://wa.me/{{ $customer->whatsapp_number }}" target="_blank" rel="noopener" title="Chat on WhatsApp"
                                   class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <i class="fab fa-whatsapp text-[11px]"></i>
                                </a>
                                @endif
                                @if($customer->email)
                                <span class="text-[11px] text-slate-400 hidden sm:inline"><i class="fas fa-envelope text-[9px] mr-0.5"></i>{{ $customer->email }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-3 py-3 hidden lg:table-cell">
                    @if($customer->address)
                    <p class="text-xs text-slate-500 max-w-[160px] truncate" title="{{ $customer->address }}">
                        <i class="fas fa-map-marker-alt text-[9px] text-slate-300 mr-1"></i>{{ $customer->address }}
                    </p>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>

                <td class="px-3 py-3 text-center hidden md:table-cell">
                    <span class="bg-purple-50 text-purple-700 border border-purple-100 px-2.5 py-1 rounded-lg text-[11.5px] font-semibold">
                        {{ $customer->sales_count }}
                    </span>
                </td>

                <td class="px-3 py-3 text-right hidden md:table-cell">
                    <p class="font-bold text-slate-800 text-[13px]">{{ $currency }}{{ number_format($customer->sales_sum_total ?? 0, 0) }}</p>
                    @if(($customer->sales_sum_paid_amount ?? 0) > 0)
                    <p class="text-[10.5px] text-emerald-600">paid {{ $currency }}{{ number_format($customer->sales_sum_paid_amount ?? 0, 0) }}</p>
                    @endif
                </td>

                <td class="px-3 py-3 text-right hidden lg:table-cell">
                    @php $due = $customer->sales_sum_due_amount ?? 0; @endphp
                    @if($due > 0)
                    <span class="font-bold text-red-500 text-[13px]">{{ $currency }}{{ number_format($due, 0) }}</span>
                    @else
                    <span class="text-emerald-600 text-[11.5px] font-semibold">Settled</span>
                    @endif
                </td>

                <td class="px-3 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('branch.customers.report', [$branch, $customer]) }}"
                           title="Report"
                           class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-purple-50 hover:border-purple-200 hover:text-purple-600 transition-colors">
                            <i class="fas fa-chart-bar text-xs"></i>
                        </a>
                        <button type="button" title="Edit"
                                @click="openEdit({{ $customer->toJson() }})"
                                class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 transition-colors">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <button type="button" title="Delete"
                                @click="openDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ route('branch.customers.destroy', [$branch, $customer]) }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition-colors">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center text-slate-400">
                    <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-3xl text-slate-300"></i>
                    </div>
                    <p class="font-medium text-slate-500">No customers found</p>
                    <button type="button" @click="addOpen = true"
                            class="mt-3 inline-flex items-center gap-1.5 text-blue-600 text-sm hover:underline">
                        <i class="fas fa-plus text-xs"></i> Add first customer
                    </button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($customers->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $customers->links() }}</div>
    @endif
</div>

{{-- ══ ADD CUSTOMER MODAL ══════════════════════════════════════════ --}}
<div x-show="addOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="addOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="addOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-plus text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Add New Customer</p>
                <p class="text-xs text-slate-400 mt-0.5">Fill in customer details</p>
            </div>
        </div>
        <form method="POST" action="{{ route('branch.customers.store', $branch) }}"
              enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Customer full name"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label>
                    <input type="text" name="phone" placeholder="01XXXXXXXXX"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                    <input type="email" name="email" placeholder="email@example.com"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Address</label>
                <textarea name="address" rows="2" placeholder="Full address..."
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Photo <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="file" name="image" accept="image/*"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm bg-slate-50 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="addOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-bold">
                    <i class="fas fa-plus text-xs mr-1"></i> Add Customer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ EDIT CUSTOMER MODAL ═════════════════════════════════════════ --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="editOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-pen text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Edit Customer</p>
                <p class="text-xs text-slate-400 mt-0.5" x-text="editName"></p>
            </div>
        </div>
        <form :action="editAction" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="editName" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-slate-50 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone</label>
                    <input type="text" name="phone" x-model="editPhone"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                    <input type="email" name="email" x-model="editEmail"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-slate-50 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Address</label>
                <textarea name="address" rows="2" x-model="editAddress"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-slate-50 focus:bg-white resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Change Photo <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="file" name="image" accept="image/*"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm bg-slate-50 text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="editOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-bold">
                    <i class="fas fa-check text-xs mr-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DELETE MODAL ════════════════════════════════════════════════ --}}
<div x-show="delModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delModal = false">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-trash-alt text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Delete Customer?</p>
                <p class="text-sm text-slate-500 mt-0.5">Sales history will remain.</p>
            </div>
        </div>
        <div class="bg-slate-50 rounded-xl px-4 py-2.5 mb-5 border border-slate-100">
            <p class="text-sm font-semibold text-slate-700" x-text="delName"></p>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50">
                Cancel
            </button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">
                    <i class="fas fa-trash-alt text-xs mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

</div>{{-- x-data --}}

@push('scripts')
<script>
function customerPage() {
    return {
        addOpen:  false,
        editOpen: false,
        editAction: '',
        editName: '', editPhone: '', editEmail: '', editAddress: '',
        delModal: false, delName: '', delUrl: '',

        openEdit(c) {
            this.editAction  = `{{ route('branch.customers.update', [$branch, ':id']) }}`.replace(':id', c.id);
            this.editName    = c.name    || '';
            this.editPhone   = c.phone   || '';
            this.editEmail   = c.email   || '';
            this.editAddress = c.address || '';
            this.editOpen    = true;
        },

        openDelete(id, name, url) {
            this.delName  = name;
            this.delUrl   = url;
            this.delModal = true;
        },
    };
}
</script>
@endpush
@endsection
