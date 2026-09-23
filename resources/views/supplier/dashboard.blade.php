@extends('supplier.layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Supplier Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-900 p-6 text-white shadow-lg">
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-8 pointer-events-none">
            <i class="fas fa-store text-9xl"></i>
        </div>
        <div class="relative z-10 max-w-2xl">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 text-xs font-semibold border border-emerald-400/30">
                    <i class="fas fa-certificate text-[11px] text-emerald-300"></i> Verified Supplier Partner
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-400/20 text-yellow-200 text-xs font-semibold border border-yellow-400/30">
                    <i class="fas fa-percent text-[10px]"></i> Platform Fee Rate: {{ $supplier->commission_percentage ?? 5.00 }}%
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Welcome back, {{ $supplier->display_name }}!</h2>
            <p class="text-emerald-100/80 text-sm mt-1">
                Manage your product catalog, track customer sales in real-time, and monitor your commission & net earnings.
            </p>
            <div class="flex flex-wrap gap-3 mt-5">
                <a href="{{ route('supplier.products.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-900 font-bold text-xs hover:bg-emerald-50 transition shadow">
                    <i class="fas fa-circle-plus text-emerald-600"></i> Add New Product
                </a>
                <a href="{{ route('supplier.orders.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700/80 hover:bg-emerald-700 text-white font-semibold text-xs transition border border-emerald-500/40">
                    <i class="fas fa-receipt"></i> View Sales &amp; Commission
                </a>
                <a href="{{ route('supplier.withdrawals.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold text-xs transition shadow-md">
                    <i class="fas fa-wallet text-amber-900"></i> Withdrawals &amp; Balance (৳{{ number_format($withdrawableBalance, 2) }})
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Cards with Commission & Payout Breakdown --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Card 1: Total Net Earned --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Net Earned</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base">
                    <i class="fas fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-800">৳{{ number_format($totalNetEarnings, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    Gross: ৳{{ number_format($totalSalesAmount, 2) }} (Fee: -৳{{ number_format($totalAdminCommission, 2) }})
                </div>
            </div>
        </div>

        {{-- Card 2: Total Withdrawn (Paid) --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Withdrawn</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-base">
                    <i class="fas fa-money-bill-transfer"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-indigo-600">৳{{ number_format($totalWithdrawn, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">Deducted from balance</div>
            </div>
        </div>

        {{-- Card 3: Pending Payout --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Payout</span>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-base">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-amber-600">৳{{ number_format($pendingPayout, 2) }}</div>
                <div class="text-xs text-slate-500 mt-1">Awaiting admin review</div>
            </div>
        </div>

        {{-- Card 4: Available Withdrawable Balance --}}
        <div class="bg-white rounded-2xl border-2 border-emerald-500/40 p-5 shadow-sm hover:shadow-md transition bg-gradient-to-br from-white to-emerald-50/70">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Withdrawable Balance</span>
                <a href="{{ route('supplier.withdrawals.index') }}" class="w-10 h-10 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center text-base shadow-md shadow-emerald-500/25 transition">
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                </a>
            </div>
            <div class="mt-3 flex items-end justify-between">
                <div>
                    <div class="text-2xl font-black text-emerald-700">৳{{ number_format($withdrawableBalance, 2) }}</div>
                    <div class="text-xs text-emerald-600 font-bold mt-1">Ready for payout</div>
                </div>
                <a href="{{ route('supplier.withdrawals.index') }}" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition">
                    Withdraw
                </a>
            </div>
        </div>

    </div>

    {{-- Grid: Recent Orders & Recent Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Sales Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Recent Sales &amp; Earnings</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Customer orders and calculated net earnings</p>
                </div>
                <a href="{{ route('supplier.orders.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Invoice</th>
                            <th class="px-3 py-3 font-semibold text-right">Gross</th>
                            <th class="px-3 py-3 font-semibold text-right text-rose-600">Admin Comm.</th>
                            <th class="px-3 py-3 font-semibold text-right text-emerald-700">Your Earning</th>
                            <th class="px-3 py-3 font-semibold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentOrders as $order)
                        @php
                            $subtotal = $order->saleItems->sum('subtotal');
                            $adminComm = $order->saleItems->sum('admin_commission_amount');
                            $netEarning = $order->saleItems->sum('supplier_earning');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-mono font-bold text-emerald-700">
                                <a href="{{ route('supplier.orders.show', $order->id) }}" class="hover:underline">
                                    {{ $order->invoice_no }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-sans">{{ $order->created_at->format('d M, Y') }}</div>
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-slate-800">
                                ৳{{ number_format($subtotal, 2) }}
                            </td>
                            <td class="px-3 py-3 text-right font-semibold text-rose-600">
                                -৳{{ number_format($adminComm, 2) }}
                            </td>
                            <td class="px-3 py-3 text-right font-black text-emerald-700">
                                ৳{{ number_format($netEarning, 2) }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $order->order_status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                                       ($order->order_status === 'cancelled' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-inbox text-3xl text-slate-200 mb-2"></i>
                                <div>No sales recorded yet. Once customers purchase your items, earnings will show here!</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Products Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Recently Added Products</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Your product uploads and stock status</p>
                </div>
                <a href="{{ route('supplier.products.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Product</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold text-right">Price</th>
                            <th class="px-4 py-3 font-semibold text-center">Stock</th>
                            <th class="px-4 py-3 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentProducts as $prod)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        @if($prod->first_image_url)
                                            <img src="{{ $prod->first_image_url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-box text-slate-300 text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate max-w-[160px]">{{ $prod->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $prod->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500 truncate max-w-[100px]">
                                {{ $prod->category?->name ?: 'General' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800">
                                ৳{{ number_format($prod->price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $prod->stock_qty > 5 ? 'bg-emerald-50 text-emerald-700' :
                                       ($prod->stock_qty > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                    {{ $prod->stock_qty }} {{ $prod->unit }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('supplier.products.edit', $prod->id) }}"
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                <i class="fas fa-boxes-stacked text-3xl text-slate-200 mb-2"></i>
                                <div>You haven't uploaded any products yet.</div>
                                <a href="{{ route('supplier.products.create') }}" class="inline-block mt-3 px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">
                                    Upload First Product
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
