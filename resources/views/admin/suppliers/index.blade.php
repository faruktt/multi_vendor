@extends('layouts.app')
@section('title', 'Supplier / Vendor Management')
@section('heading', 'Supplier Management')

@section('content')
<div class="py-4 space-y-6">

    {{-- Top Section Navigation --}}
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.suppliers.manage') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors bg-indigo-600 text-white shadow-sm">
                <i class="fas fa-store mr-1.5"></i> Suppliers / Vendors
            </a>
            <a href="{{ route('admin.supplier-products.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors bg-white text-slate-600 hover:bg-slate-50 border border-slate-200">
                <i class="fas fa-boxes-stacked mr-1.5"></i> Supplier Products
            </a>
            <a href="{{ route('admin.supplier-sales.index') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors bg-white text-slate-600 hover:bg-slate-50 border border-slate-200">
                <i class="fas fa-boxes-packing mr-1.5"></i> Supplier Sales
            </a>
            <a href="{{ route('admin.suppliers.withdrawals') }}"
               class="px-4 py-2 rounded-xl font-bold text-xs transition-colors bg-white text-slate-600 hover:bg-slate-50 border border-slate-200">
                <i class="fas fa-wallet mr-1.5"></i> Supplier Withdrawals
                @php $pendingW = \App\Models\SupplierWithdrawal::where('status', 'pending')->count(); @endphp
                @if($pendingW > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-[10px] font-black bg-amber-400 text-amber-950 rounded-full animate-pulse">{{ $pendingW }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Sub Navigation / Status Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.suppliers.manage') }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ !request('status') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-store mr-1.5"></i> All Suppliers ({{ $totalCount }})
        </a>

        <a href="{{ route('admin.suppliers.manage', ['status' => 'pending']) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ request('status') === 'pending' ? 'bg-amber-500 text-white shadow-sm shadow-amber-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-clock mr-1.5"></i> Pending Approval
            @if($pendingCount > 0)
                <span class="ml-1.5 px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-900 font-black animate-pulse">{{ $pendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.suppliers.manage', ['status' => 'active']) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ request('status') === 'active' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-check-circle mr-1.5"></i> Active ({{ $activeCount }})
        </a>

        <a href="{{ route('admin.suppliers.manage', ['status' => 'inactive']) }}"
           class="px-4 py-2 rounded-xl font-bold text-xs sm:text-sm transition-colors {{ request('status') === 'inactive' ? 'bg-rose-600 text-white shadow-sm shadow-rose-200' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i class="fas fa-ban mr-1.5"></i> Inactive ({{ $inactiveCount }})
        </a>
    </div>

    {{-- Platform Supplier Financial Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-sack-dollar"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Supplier Sales</div>
                <div class="text-xl font-black text-slate-800 mt-0.5">৳{{ number_format($totalPlatformSupplierSales, 2) }}</div>
                <div class="text-[10px] text-slate-500">Gross sales across all vendors</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-200 p-4 shadow-sm flex items-center gap-4 bg-gradient-to-br from-white to-emerald-50/50">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-xl flex-shrink-0 shadow-md shadow-emerald-500/20">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Admin Commission Earned</div>
                <div class="text-xl font-black text-emerald-700 mt-0.5">৳{{ number_format($totalPlatformAdminCommission, 2) }}</div>
                <div class="text-[10px] text-emerald-600 font-semibold">Net platform profit revenue</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Supplier Net Payable</div>
                <div class="text-xl font-black text-slate-800 mt-0.5">৳{{ number_format($totalPlatformSupplierPayable, 2) }}</div>
                <div class="text-[10px] text-slate-500">Due/Payout to suppliers</div>
            </div>
        </div>
    </div>

    {{-- Filter / Search Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.suppliers.manage') }}" class="flex flex-wrap items-center gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="relative flex-1 min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by supplier name, company, email, phone..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                Search
            </button>

            @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('admin.suppliers.manage') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Supplier Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Supplier / Store</th>
                        <th class="px-4 py-3.5 font-semibold">Contact Info</th>
                        <th class="px-3 py-3.5 font-semibold text-center">Products</th>
                        <th class="px-4 py-3.5 font-semibold text-center">Commission Rate</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Total Sales</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Admin Comm.</th>
                        <th class="px-4 py-3.5 font-semibold text-right">Supplier Net</th>
                        <th class="px-3 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/80 transition">
                        {{-- Name & Store --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-emerald-800">
                                    @if($supplier->logo_url)
                                        <img src="{{ $supplier->logo_url }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                        <span>{{ $supplier->display_name }}</span>
                                        @if($supplier->isActive())
                                            <i class="fas fa-circle-check text-emerald-500 text-xs" title="Approved Supplier"></i>
                                        @endif
                                    </div>
                                    @if($supplier->company_name && $supplier->company_name !== $supplier->name)
                                        <div class="text-[11px] text-slate-400">Owner: {{ $supplier->name }}</div>
                                    @endif
                                    @if($supplier->address)
                                        <div class="text-[10px] text-slate-400 truncate max-w-[200px]">{{ $supplier->address }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Contact Info --}}
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800">{{ $supplier->email }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $supplier->phone ?: 'No phone' }}</div>
                        </td>

                        {{-- Products Count --}}
                        <td class="px-3 py-3.5 text-center">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800">
                                {{ $supplier->products_count }}
                            </span>
                        </td>

                        {{-- Commission Rate (Editable Inline) --}}
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.suppliers.commission.update', $supplier->id) }}"
                                  class="inline-flex items-center gap-1">
                                @csrf
                                <div class="relative w-20">
                                    <input type="number" step="0.1" min="0" max="100" name="commission_percentage"
                                           value="{{ old('commission_percentage', $supplier->commission_percentage ?? 5.00) }}"
                                           class="w-full px-2 py-1 text-xs font-black text-center rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                    <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 pointer-events-none">%</span>
                                </div>
                                <button type="submit"
                                        title="Save Commission Rate"
                                        class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 transition flex items-center justify-center text-[10px]">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>

                        {{-- Total Sales --}}
                        <td class="px-4 py-3.5 text-right font-bold text-slate-800 whitespace-nowrap">
                            ৳{{ number_format($supplier->total_sales_amount ?? 0, 2) }}
                            <div class="text-[10px] font-normal text-slate-400">{{ $supplier->sale_items_count }} items</div>
                        </td>

                        {{-- Admin Commission Earned --}}
                        <td class="px-4 py-3.5 text-right font-black text-emerald-600 whitespace-nowrap">
                            +৳{{ number_format($supplier->total_commission_earned ?? 0, 2) }}
                        </td>

                        {{-- Supplier Net Share --}}
                        <td class="px-4 py-3.5 text-right font-bold text-slate-700 whitespace-nowrap">
                            ৳{{ number_format($supplier->total_supplier_earnings ?? 0, 2) }}
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-3 py-3.5 text-center whitespace-nowrap">
                            @if($supplier->isPending())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    <span>Pending</span>
                                </span>
                            @elseif($supplier->isActive())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fas fa-check text-[9px]"></i>
                                    <span>Active</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                    <i class="fas fa-ban text-[9px]"></i>
                                    <span>Inactive</span>
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
                            {{-- Financial & Commission Report --}}
                            <a href="{{ route('admin.suppliers.commission.report', $supplier->id) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition border border-indigo-200 shadow-sm"
                               title="View detailed Commission & Sales Report">
                                <i class="fas fa-file-invoice-dollar text-[11px]"></i>
                                <span>Report</span>
                            </a>

                            @if($supplier->isPending() || !$supplier->isActive())
                                <form method="POST" action="{{ route('admin.suppliers.approve', $supplier->id) }}" class="inline-block"
                                      onsubmit="return confirm('Approve supplier {{ $supplier->display_name }}? They will be granted login access and receive a confirmation email.');">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                                        <i class="fas fa-check"></i>
                                        <span>Approve</span>
                                    </button>
                                </form>
                            @endif

                            @if($supplier->isActive())
                                <form method="POST" action="{{ route('admin.suppliers.reject', $supplier->id) }}" class="inline-block"
                                      onsubmit="return confirm('Deactivate supplier {{ $supplier->display_name }}? Their dashboard access will be disabled.');">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-2 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs transition border border-amber-200"
                                            title="Deactivate Supplier">
                                        <i class="fas fa-pause text-[10px]"></i>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier->id) }}" class="inline-block"
                                  onsubmit="return confirm('Delete this supplier completely?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition"
                                        title="Delete Supplier">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 mx-auto flex items-center justify-center text-2xl text-slate-300 mb-3">
                                <i class="fas fa-store-slash"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">No suppliers found</div>
                            <p class="text-xs text-slate-400 mt-1">There are no suppliers matching the selected filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
