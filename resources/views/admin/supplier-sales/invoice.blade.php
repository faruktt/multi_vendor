<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }} — Marketplace Order</title>

    {{-- Tailwind CSS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Noto Sans Bengali', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        .invoice-card {
            width: 210mm;
            min-height: 297mm;
            margin: 75px auto 40px auto;
            background: #ffffff;
            padding: 16mm 18mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .invoice-card {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body onload="{{ request()->has('print') ? 'window.print()' : '' }}">

    {{-- TOP CONTROL TOOLBAR --}}
    <header class="no-print fixed top-0 left-0 right-0 h-16 bg-slate-900 border-b border-slate-800 text-white z-50 px-4 md:px-8 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.supplier-sales.show', $sale) }}"
               class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition-colors"
               title="Back to Order Details">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-sm md:text-base font-bold flex items-center gap-2">
                    <i class="fas fa-file-invoice text-blue-400"></i>
                    <span>Marketplace Invoice #{{ $sale->invoice_no }}</span>
                </h1>
                <p class="text-[11px] text-slate-400 hidden sm:block">Customer: {{ $sale->customer?->name ?: 'Guest' }} (৳{{ number_format($sale->total, 2) }})</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button"
                    onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-900/30 flex items-center gap-2 transition cursor-pointer">
                <i class="fas fa-print text-sm"></i>
                <span>Print Invoice</span>
            </button>
        </div>
    </header>

    {{-- PRINTABLE INVOICE SHEET (A4 Standard) --}}
    <main class="invoice-card flex flex-col justify-between">
        <div class="space-y-6">

            {{-- 1. Invoice Top Header --}}
            <div class="flex items-start justify-between border-b-2 border-slate-200 pb-6">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-black shadow-md">
                            <i class="fas fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ config('app.name', 'Fayaz Marketplace') }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Supplier / Vendor Marketplace Order</p>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-blue-100 text-blue-800">
                        MARKETPLACE INVOICE
                    </span>
                    <h3 class="text-2xl font-black font-mono text-slate-900 mt-2">{{ $sale->invoice_no }}</h3>
                    <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                        <div><strong>Order Date:</strong> {{ $sale->created_at->format('d M, Y \a\t h:i A') }}</div>
                        <div><strong>Order Status:</strong> <span class="font-bold uppercase text-slate-800">{{ $sale->order_status }}</span></div>
                        <div><strong>Payment:</strong> <span class="font-bold uppercase text-slate-800">{{ $sale->payment_method ?? 'COD' }} ({{ $sale->payment_status }})</span></div>
                    </div>
                </div>
            </div>

            {{-- 2. Customer & Supplier Info Grid --}}
            <div class="grid grid-cols-2 gap-6 bg-slate-50 rounded-2xl p-5 border border-slate-200/80">
                {{-- Customer Details --}}
                <div>
                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                        <i class="fas fa-user mr-1 text-blue-600"></i> Customer &amp; Delivery Address
                    </h4>
                    <div class="text-sm font-bold text-slate-900">{{ $sale->customer?->name ?: 'Customer' }}</div>
                    <div class="text-xs text-slate-600 mt-1 flex items-center gap-1.5 font-mono font-semibold">
                        <i class="fas fa-phone-alt text-[10px] text-blue-600"></i> {{ $sale->customer?->phone ?: 'No phone provided' }}
                    </div>
                    <div class="text-xs text-slate-800 font-medium mt-1 leading-relaxed">
                        {{ $sale->customer?->address ?: ($sale->address ?: 'Standard Address') }}
                    </div>
                    <div class="text-xs text-slate-600 mt-1">
                        <strong>Thana:</strong> {{ $sale->thana ?: ($sale->customer?->thana ?: '—') }} &bull;
                        <strong>District:</strong> {{ $sale->district ?: ($sale->customer?->district ?: '—') }}
                    </div>
                </div>

                {{-- Primary Supplier Details --}}
                @php
                    $primarySupplier = $sale->supplier ?: $sale->saleItems->firstWhere('supplier_id', '!=', null)?->supplier;
                @endphp
                <div>
                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                        <i class="fas fa-store mr-1 text-indigo-600"></i> Vendor / Supplier Information
                    </h4>
                    @if($primarySupplier)
                        <div class="text-sm font-bold text-indigo-900">{{ $primarySupplier->company_name ?: $primarySupplier->name }}</div>
                        <div class="text-xs text-slate-600 mt-1 flex items-center gap-1.5">
                            <i class="fas fa-user-circle text-[10px] text-slate-400"></i> Owner: {{ $primarySupplier->name }}
                        </div>
                        @if($primarySupplier->phone)
                            <div class="text-xs text-slate-600 mt-0.5 flex items-center gap-1.5 font-mono">
                                <i class="fas fa-phone text-[10px] text-slate-400"></i> {{ $primarySupplier->phone }}
                            </div>
                        @endif
                        <div class="text-[11px] text-amber-700 font-semibold mt-1">
                            Default Commission: {{ $primarySupplier->commission_percentage }}%
                        </div>
                    @else
                        <div class="text-xs text-slate-400 italic mt-1">Direct / Multiple Vendors</div>
                    @endif
                </div>
            </div>

            {{-- 3. Ordered Products Table --}}
            <div>
                <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2.5">
                    Ordered Products &amp; Financial Breakdown
                </h4>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 border-b border-slate-200 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-3 py-3">Supplier</th>
                                <th class="px-4 py-3 text-right">Unit Price</th>
                                <th class="px-3 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-right text-amber-700">Admin Comm.</th>
                                <th class="px-4 py-3 text-right text-indigo-800">Supplier Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @php
                                $totGross = 0;
                                $totAdmin = 0;
                                $totNet   = 0;
                                $totQty   = 0;
                            @endphp
                            @foreach($sale->saleItems as $idx => $item)
                            @php
                                $totGross += $item->subtotal;
                                $totAdmin += $item->admin_commission_amount;
                                $totNet   += $item->supplier_earning;
                                $totQty   += $item->quantity;
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg border border-slate-200 overflow-hidden bg-slate-100 flex-shrink-0 flex items-center justify-center">
                                            @if($item->product?->first_image_url)
                                                <img src="{{ $item->product->first_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-box text-slate-300 text-xs"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-xs">{{ $item->product?->name ?: 'Product #' . $item->product_id }}</div>
                                            @if($item->variant_name)
                                                <div class="text-[10px] text-indigo-600 font-semibold mt-0.5">↳ {{ $item->variant_name }}</div>
                                            @endif
                                            <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $item->product?->sku ?: '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 font-semibold text-slate-700">
                                    {{ $item->supplier ? ($item->supplier->company_name ?: $item->supplier->name) : 'Direct' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-slate-700 whitespace-nowrap">
                                    ৳{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-slate-900 whitespace-nowrap">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap">
                                    ৳{{ number_format($item->subtotal, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-amber-700 whitespace-nowrap font-medium">
                                    +৳{{ number_format($item->admin_commission_amount, 2) }}
                                    <span class="text-[9px] text-slate-400 block">({{ $item->admin_commission_rate }}%)</span>
                                </td>
                                <td class="px-4 py-3 text-right font-black text-indigo-700 whitespace-nowrap">
                                    ৳{{ number_format($item->supplier_earning, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-xs">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-bold text-slate-600 uppercase">
                                    Total Items ({{ $totQty }} pcs):
                                </td>
                                <td class="px-4 py-3 text-right font-black text-slate-900">
                                    ৳{{ number_format($totGross, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-amber-700">
                                    +৳{{ number_format($totAdmin, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-indigo-700 text-sm">
                                    ৳{{ number_format($totNet, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 4. Financial Summary Cards --}}
            <div class="flex justify-end pt-2">
                <div class="w-80 bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal:</span>
                        <span class="font-bold text-slate-900">৳{{ number_format($totGross, 2) }}</span>
                    </div>
                    @if($sale->delivery_charge > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Delivery Charge:</span>
                            <span class="font-bold text-slate-900">+৳{{ number_format($sale->delivery_charge, 2) }}</span>
                        </div>
                    @endif
                    @if($sale->discount > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Discount:</span>
                            <span class="font-bold">-৳{{ number_format($sale->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="border-t border-slate-200 pt-2 flex justify-between items-center text-slate-900">
                        <span class="font-bold text-sm">Grand Total:</span>
                        <span class="text-base font-black">৳{{ number_format($sale->total, 2) }}</span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 flex justify-between text-amber-700 font-bold">
                        <span>Admin Commission Profit:</span>
                        <span>+৳{{ number_format($totAdmin, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-indigo-700 font-bold">
                        <span>Supplier Net Share:</span>
                        <span>৳{{ number_format($totNet, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- 5. Footer --}}
        <div class="border-t border-slate-200 pt-6 mt-8">
            <div class="grid grid-cols-2 gap-8 text-center text-xs text-slate-400">
                <div>
                    <div class="border-b border-slate-300 w-44 mx-auto mb-1"></div>
                    <span>Prepared By</span>
                </div>
                <div>
                    <div class="border-b border-slate-300 w-44 mx-auto mb-1"></div>
                    <span>Admin Authorization</span>
                </div>
            </div>
            <div class="text-center text-[10px] text-slate-400 mt-6">
                {{ config('app.name', 'Fayaz') }} Marketplace Administration &bull; Invoice generated on {{ now()->format('d M, Y h:i A') }}
            </div>
        </div>
    </main>

</body>
</html>
