<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }} — {{ $supplier->company_name ?: $supplier->name }}</title>

    {{-- Tailwind CSS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
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
            <a href="{{ route('supplier.orders.show', $sale->id) }}"
               class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition-colors"
               title="Back to Order Details">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-sm md:text-base font-bold flex items-center gap-2">
                    <i class="fas fa-file-invoice text-emerald-400"></i>
                    <span>Order Invoice #{{ $sale->invoice_no }}</span>
                </h1>
                <p class="text-[11px] text-slate-400 hidden sm:block">Customer: {{ $sale->customer?->name ?: 'Customer' }} (৳{{ number_format($supplierNet, 2) }})</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button"
                    onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-900/30 flex items-center gap-2 transition cursor-pointer">
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
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl font-black shadow-md shadow-emerald-200">
                            @if($supplier->logo_url)
                                <img src="{{ $supplier->logo_url }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover rounded-2xl">
                            @else
                                <i class="fas fa-store"></i>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ $supplier->company_name ?: $supplier->name }}</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Supplier Store · Marketplace Partner</p>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-slate-600 space-y-0.5">
                        @if($supplier->phone)
                            <div class="flex items-center gap-1.5"><i class="fas fa-phone text-[10px] text-slate-400"></i> {{ $supplier->phone }}</div>
                        @endif
                        @if($supplier->email)
                            <div class="flex items-center gap-1.5"><i class="fas fa-envelope text-[10px] text-slate-400"></i> {{ $supplier->email }}</div>
                        @endif
                        @if($supplier->address)
                            <div class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-[10px] text-slate-400"></i> {{ $supplier->address }}</div>
                        @endif
                    </div>
                </div>

                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        SUPPLIER INVOICE
                    </span>
                    <h3 class="text-2xl font-black font-mono text-slate-900 mt-2">{{ $sale->invoice_no }}</h3>
                    <div class="text-xs text-slate-500 mt-1 space-y-0.5">
                        <div><strong>Order Date:</strong> {{ $sale->created_at->format('d M, Y') }}</div>
                        <div><strong>Time:</strong> {{ $sale->created_at->format('h:i A') }}</div>
                        <div><strong>Status:</strong> <span class="font-bold uppercase text-slate-800">{{ $sale->order_status }}</span></div>
                    </div>
                </div>
            </div>

            {{-- 2. Customer & Delivery Address Card --}}
            <div class="grid grid-cols-2 gap-6 bg-slate-50 rounded-2xl p-5 border border-slate-200/80">
                <div>
                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                        <i class="fas fa-user mr-1 text-slate-500"></i> Customer Details
                    </h4>
                    <div class="text-sm font-bold text-slate-900">{{ $sale->customer?->name ?: 'Customer' }}</div>
                    <div class="text-xs text-slate-600 mt-1 flex items-center gap-1.5 font-mono font-semibold">
                        <i class="fas fa-phone-alt text-[10px] text-emerald-600"></i> {{ $sale->customer?->phone ?: 'No phone provided' }}
                    </div>
                    @if($sale->customer?->email)
                        <div class="text-xs text-slate-500 mt-0.5">{{ $sale->customer->email }}</div>
                    @endif
                </div>

                <div>
                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">
                        <i class="fas fa-location-dot mr-1 text-emerald-600"></i> Delivery Address
                    </h4>
                    <div class="text-xs font-bold text-slate-900 leading-relaxed">
                        {{ $sale->customer?->address ?: ($sale->address ?: 'Standard Address') }}
                    </div>
                    <div class="text-xs text-slate-600 mt-1">
                        <strong>Thana:</strong> {{ $sale->thana ?: ($sale->customer?->thana ?: '—') }} &bull;
                        <strong>District:</strong> {{ $sale->district ?: ($sale->customer?->district ?: '—') }}
                    </div>
                    @if($sale->delivery_zone)
                        <div class="text-[11px] text-emerald-700 font-semibold mt-1">
                            Zone: {{ ucfirst(str_replace('_', ' ', $sale->delivery_zone)) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Items Table with Product Images --}}
            <div>
                <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2.5">
                    Ordered Products ({{ $supplierQty }} items)
                </h4>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 border-b border-slate-200 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Product</th>
                                <th class="px-3 py-3">Variant</th>
                                <th class="px-4 py-3 text-right">Unit Price</th>
                                <th class="px-3 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3 text-right text-rose-600">Admin Fee</th>
                                <th class="px-4 py-3 text-right text-emerald-800">Your Net</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($supplierItems as $idx => $item)
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
                                            <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $item->product?->sku ?: '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if($item->variant_name)
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 font-semibold text-slate-700 text-[10px]">
                                            {{ $item->variant_name }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
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
                                <td class="px-4 py-3 text-right text-rose-600 whitespace-nowrap font-medium">
                                    -৳{{ number_format($item->admin_commission_amount, 2) }}
                                    <span class="text-[9px] text-slate-400 block">({{ $item->admin_commission_rate ?? $supplier->commission_percentage }}%)</span>
                                </td>
                                <td class="px-4 py-3 text-right font-black text-emerald-700 whitespace-nowrap">
                                    ৳{{ number_format($item->supplier_earning, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-xs">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-bold text-slate-600 uppercase">
                                    Total Sold ({{ $supplierQty }} pcs):
                                </td>
                                <td class="px-4 py-3 text-right font-black text-slate-900">
                                    ৳{{ number_format($supplierGross, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-rose-600">
                                    -৳{{ number_format($supplierCommission, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-emerald-700 text-sm">
                                    ৳{{ number_format($supplierNet, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 4. Financial Summary Cards --}}
            <div class="flex justify-end pt-2">
                <div class="w-72 bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Gross Products Subtotal:</span>
                        <span class="font-bold text-slate-900">৳{{ number_format($supplierGross, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-rose-600">
                        <span>Platform Admin Fee:</span>
                        <span class="font-bold">-৳{{ number_format($supplierCommission, 2) }}</span>
                    </div>
                    <div class="border-t border-emerald-200 pt-2 flex justify-between items-center text-emerald-800">
                        <span class="font-bold">Your Net Payable:</span>
                        <span class="text-base font-black">৳{{ number_format($supplierNet, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- 5. Footer & Signatures --}}
        <div class="border-t border-slate-200 pt-6 mt-8">
            <div class="grid grid-cols-2 gap-8 text-center text-xs text-slate-400">
                <div>
                    <div class="border-b border-slate-300 w-44 mx-auto mb-1"></div>
                    <span>Prepared / Packed By</span>
                </div>
                <div>
                    <div class="border-b border-slate-300 w-44 mx-auto mb-1"></div>
                    <span>Authorized Signature</span>
                </div>
            </div>
            <div class="text-center text-[10px] text-slate-400 mt-6">
                Thank you for being a valued partner on {{ config('app.name', 'Fayaz') }} Marketplace &bull; Generated on {{ now()->format('d M, Y h:i A') }}
            </div>
        </div>
    </main>

</body>
</html>
