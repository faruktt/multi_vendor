<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print Orders ({{ count($sales) }}) — {{ $appSettings['name'] ?? config('app.name') }}</title>
    
    {{-- Tailwind & Fonts --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        /* ── Screen Styles ── */
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        .screen-container {
            max-width: 210mm;
            margin: 80px auto 40px auto;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* Cut line separator */
        .cut-line {
            position: relative;
            text-align: center;
            margin: 12px 0;
            border-top: 2px dashed #94a3b8;
        }
        .cut-line-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            padding: 0 10px;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 9999px;
            border: 1px solid #cbd5e1;
        }

        /* Screen Layouts */
        .layout-4 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            padding: 16px;
        }
        .layout-2, .layout-3, .layout-1 {
            padding: 16px;
        }

        /* ── Print Media Styles ── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm 6mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .screen-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
            }
            .order-card {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                border: 1.5px solid #000000 !important;
                border-radius: 6px !important;
                background: #ffffff !important;
                box-shadow: none !important;
            }
            .cut-line {
                border-top: 1.5px dashed #475569 !important;
                margin: 3mm 0 !important;
            }
            .cut-line-badge {
                border-color: #475569 !important;
                color: #000000 !important;
                background: #ffffff !important;
            }

            /* Layout: 1 Order Per Page */
            .layout-1 .order-card {
                min-height: 275mm !important;
                max-height: 280mm !important;
                margin-bottom: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
                padding: 24px !important;
            }
            .layout-1 .cut-line { display: none !important; }

            /* Layout: 2 Orders Per Page (Default A4 Half-page) */
            .layout-2 .order-card {
                height: 137mm !important;
                max-height: 137mm !important;
                margin-bottom: 0 !important;
                overflow: hidden !important;
            }
            .layout-2 .order-card:nth-of-type(2n) {
                page-break-after: always !important;
                break-after: page !important;
            }

            /* Layout: 3 Orders Per Page (1/3 A4 Slip) */
            .layout-3 .order-card {
                height: 90mm !important;
                max-height: 90mm !important;
                margin-bottom: 0 !important;
                overflow: hidden !important;
                font-size: 11px !important;
            }
            .layout-3 .order-card:nth-of-type(3n) {
                page-break-after: always !important;
                break-after: page !important;
            }
            .layout-3 .order-card .header-title { font-size: 13px !important; }
            .layout-3 .order-card .compact-hide { display: none !important; }
            .layout-3 .order-card table th, 
            .layout-3 .order-card table td { padding: 2px 4px !important; font-size: 10px !important; }

            /* Layout: 4 Orders Per Page (2x2 Grid) */
            .layout-4 {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 4mm !important;
                padding: 0 !important;
            }
            .layout-4 .order-card {
                height: 138mm !important;
                max-height: 138mm !important;
                margin-bottom: 0 !important;
                overflow: hidden !important;
                font-size: 10px !important;
            }
            .layout-4 .order-card:nth-of-type(4n) {
                page-break-after: always !important;
                break-after: page !important;
            }
            .layout-4 .cut-line { display: none !important; }
        }

        /* SVG barcode sizing */
        .barcode-svg svg {
            width: 100% !important;
            height: 28px !important;
            display: block;
        }
    </style>
</head>
<body x-data="bulkPrintApp()">

    {{-- ════════════════════════════════════════════════════════════════
         TOP CONTROLS TOOLBAR (Hidden when printing)
         ════════════════════════════════════════════════════════════════ --}}
    <header class="no-print fixed top-0 left-0 right-0 h-16 bg-slate-900 border-b border-slate-800 text-white z-50 px-4 md:px-8 flex items-center justify-between shadow-lg">
        
        {{-- Left: Title & Count --}}
        <div class="flex items-center gap-3">
            <a href="javascript:window.close()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition-colors" title="Close Window">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-sm md:text-base font-bold flex items-center gap-2">
                    <i class="fas fa-print text-blue-400"></i>
                    <span>Batch Print Orders</span>
                    <span class="text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30 px-2 py-0.5 rounded-full">
                        {{ count($sales) }} Orders Selected
                    </span>
                </h1>
            </div>
        </div>

        {{-- Center: Layout Switcher --}}
        <div class="hidden lg:flex items-center gap-1 bg-slate-800 p-1 rounded-xl border border-slate-700">
            <button type="button" @click="setLayout(2)"
                    :class="layout === 2 ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-table-cells-large text-[11px]"></i>
                <span>2 Per Page</span>
                <span class="text-[9.5px] opacity-75">(A4 Half)</span>
            </button>
            <button type="button" @click="setLayout(3)"
                    :class="layout === 3 ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-bars text-[11px]"></i>
                <span>3 Per Page</span>
                <span class="text-[9.5px] opacity-75">(1/3 Slip)</span>
            </button>
            <button type="button" @click="setLayout(4)"
                    :class="layout === 4 ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-grip text-[11px]"></i>
                <span>4 Per Page</span>
                <span class="text-[9.5px] opacity-75">(2x2 Grid)</span>
            </button>
            <button type="button" @click="setLayout(1)"
                    :class="layout === 1 ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-file-invoice text-[11px]"></i>
                <span>1 Per Page</span>
            </button>
        </div>

        {{-- Right: Options & Print Button --}}
        <div class="flex items-center gap-3">
            {{-- Quick Options Dropdown / Toggles --}}
            <div class="hidden sm:flex items-center gap-3 text-xs text-slate-300 mr-2 border-r border-slate-700 pr-3">
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                    <input type="checkbox" x-model="showBarcode" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0 w-3.5 h-3.5">
                    <span>Barcode</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                    <input type="checkbox" x-model="showCourier" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0 w-3.5 h-3.5">
                    <span>Courier</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-white">
                    <input type="checkbox" x-model="showNotes" class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-0 w-3.5 h-3.5">
                    <span>Notes</span>
                </label>
            </div>

            <button type="button" onclick="window.print()"
                    class="bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-bold px-5 py-2 rounded-xl text-xs md:text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <i class="fas fa-print"></i>
                <span>Print Now</span>
            </button>
        </div>
    </header>

    {{-- Screen Instruction Banner (Hidden on print) --}}
    <div class="no-print max-w-[210mm] mx-auto mt-20 mb-2 px-4 flex items-center justify-between text-xs text-slate-500 bg-white/70 backdrop-blur rounded-xl border border-slate-200 py-2.5 shadow-xs">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>In printer settings, keep <strong>Paper: A4</strong> and <strong>Margins: Default / Minimum</strong>.</span>
        </div>
        <div class="font-mono text-slate-500">
            Total Pages: <span class="font-bold text-slate-800" x-text="Math.ceil({{ count($sales) }} / layout)"></span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         PRINTABLE PAGES CONTAINER
         ════════════════════════════════════════════════════════════════ --}}
    <div class="screen-container" :class="'layout-' + layout">

        @php
            $currency = $appSettings['currency'] ?? '৳';
        @endphp


        @php
            $totalSales = count($sales);
        @endphp

        @foreach($sales as $index => $sale)

            {{-- Open a page wrapper --}}
            <div class="order-card bg-white p-3.5 rounded-lg border border-slate-300 shadow-2xs relative flex flex-col justify-between"
                 :class="{
                     'mb-3': layout !== 4,
                     'text-[11px]': layout === 3,
                     'text-[10px]': layout === 4
                 }">

                {{-- Card Content --}}
                <div class="flex-1 flex flex-col justify-between">
                    
                    {{-- 1. Header: Store Name & Invoice Info --}}
                    <div class="border-b border-slate-200 pb-2 mb-2 flex items-start justify-between gap-2">
                        {{-- Store details --}}
                        <div class="min-w-0">
                            <h2 class="header-title font-extrabold text-slate-900 tracking-tight leading-tight"
                                :class="layout === 3 ? 'text-xs' : (layout === 4 ? 'text-xs' : 'text-sm sm:text-base')">
                                {{ strtoupper($sale->vendor->system_name ?? $sale->vendor->name ?? $appSettings['name'] ?? config('app.name')) }}
                            </h2>
                            <p class="text-[10px] text-slate-500 leading-tight mt-0.5 flex flex-wrap gap-x-2">
                                @if(!empty($sale->vendor->phone ?? $appSettings['phone'] ?? null))
                                    <span><i class="fas fa-phone text-[8px] mr-0.5"></i>{{ $sale->vendor->phone ?? $appSettings['phone'] }}</span>
                                @endif
                                @if(!empty($sale->vendor->address ?? $appSettings['address'] ?? null))
                                    <span class="compact-hide"><i class="fas fa-location-dot text-[8px] mr-0.5"></i>{{ Str::limit($sale->vendor->address ?? $appSettings['address'], 40) }}</span>
                                @endif
                            </p>
                        </div>

                        {{-- Invoice Meta & Barcode --}}
                        <div class="text-right flex-shrink-0 flex flex-col items-end">
                            <div class="flex items-center gap-1.5 justify-end">
                                <span class="font-mono font-black text-slate-900 leading-none"
                                      :class="layout === 3 ? 'text-xs' : (layout === 4 ? 'text-xs' : 'text-sm sm:text-base')">
                                    {{ $sale->invoice_no }}
                                </span>
                                @if($sale->channel === 'web')
                                    <span class="text-[9px] font-extrabold uppercase px-1 py-0.2 bg-blue-100 text-blue-800 rounded border border-blue-200">WEB</span>
                                @elseif($sale->channel === 'reseller')
                                    <span class="text-[9px] font-extrabold uppercase px-1 py-0.2 bg-purple-100 text-purple-800 rounded border border-purple-200">RESELLER</span>
                                @else
                                    <span class="text-[9px] font-extrabold uppercase px-1 py-0.2 bg-slate-100 text-slate-700 rounded border border-slate-200">POS</span>
                                @endif
                            </div>

                            <p class="text-[9.5px] text-slate-500 font-mono mt-0.5">
                                {{ $sale->created_at->format('d/m/Y h:i A') }}
                            </p>

                            {{-- SVG Barcode --}}
                            <template x-if="showBarcode">
                                <div class="barcode-svg mt-1 flex justify-end" style="height: 24px; max-width: 140px;">
                                    @if(isset($barcodes[$sale->id]) && $barcodes[$sale->id])
                                        {!! $barcodes[$sale->id] !!}
                                    @else
                                        <span class="text-[8px] font-mono text-slate-400">#{{ $sale->invoice_no }}</span>
                                    @endif
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- 2. Customer & Courier Information Panel (2 columns) --}}
                    <div class="grid grid-cols-2 gap-2 p-2 rounded-md bg-slate-50 border border-slate-200 mb-2 leading-tight">
                        
                        {{-- Customer details --}}
                        <div class="border-r border-slate-200 pr-2">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Customer</div>
                            <p class="font-bold text-slate-900 text-[11px] truncate">
                                {{ $sale->customer->name ?? 'Walk-in Customer' }}
                            </p>
                            @if($sale->customer?->phone)
                                <p class="font-mono font-bold text-slate-800 text-[10.5px]">
                                    {{ $sale->customer->phone }}
                                </p>
                            @endif
                            <p class="text-[10px] text-slate-600 mt-0.5 line-clamp-2">
                                {{ $sale->customer->address ?? ($sale->district ? ($sale->district . ', ' . $sale->thana) : 'Address not specified') }}
                            </p>
                        </div>

                        {{-- Courier & Shipping Details --}}
                        <div class="pl-1">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Shipping & Courier</div>
                            
                            {{-- Delivery Zone --}}
                            @if($sale->delivery_zone)
                                <div class="mb-0.5">
                                    <span class="inline-block text-[9px] font-bold px-1.5 py-0.2 rounded border {{ $sale->delivery_zone === 'inside' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300' }}">
                                        {{ $sale->delivery_zone === 'inside' ? 'Inside Dhaka' : ($sale->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }}
                                    </span>
                                </div>
                            @endif

                            {{-- Courier info if present --}}
                            <template x-if="showCourier">
                                <div>
                                    @if($sale->isSentToCourier())
                                        <p class="font-bold text-slate-900 text-[10.5px] flex items-center gap-1">
                                            <i class="fas fa-truck-fast text-blue-600 text-[9px]"></i>
                                            <span>{{ $sale->courier_display_name }}</span>
                                        </p>
                                        <p class="font-mono text-[10px] text-slate-700 font-semibold truncate" title="{{ $sale->courier_tracking_code }}">
                                            Trk: {{ $sale->courier_tracking_code }}
                                        </p>
                                    @else
                                        <p class="text-[10px] text-slate-500 italic">Self Delivery / Standard</p>
                                    @endif
                                </div>
                            </template>

                            {{-- District / Thana --}}
                            @if($sale->district || $sale->customer?->district)
                                <p class="text-[9.5px] text-slate-600 font-medium truncate mt-0.5">
                                    📍 {{ implode(', ', array_filter([$sale->district ?? $sale->customer?->district, $sale->thana ?? $sale->customer?->thana])) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Items Table --}}
                    <div class="mb-2 flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-300 text-slate-600 text-[9.5px] uppercase font-bold bg-slate-100/70">
                                    <th class="py-1 px-1.5">Item Description</th>
                                    <th class="py-1 px-1 text-center w-10">Qty</th>
                                    <th class="py-1 px-1 text-right w-16">Price</th>
                                    <th class="py-1 px-1.5 text-right w-20">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-slate-800 text-[10.5px]">
                                @foreach($sale->saleItems as $item)
                                @php
                                    $itemImg = $item->product?->first_image_url;
                                @endphp
                                <tr>
                                    <td class="py-1 px-1.5">
                                        <div class="flex items-center gap-1.5">
                                            @if($itemImg)
                                                <img src="{{ $itemImg }}" alt="{{ $item->product?->name }}"
                                                     class="w-7 h-7 rounded object-cover border border-slate-200 flex-shrink-0">
                                            @else
                                                <div class="w-7 h-7 rounded bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0 text-slate-300">
                                                    <i class="fas fa-box text-[9px]"></i>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="font-medium truncate max-w-[170px] sm:max-w-[240px]">
                                                    {{ $item->product->name ?? 'Product' }}
                                                </div>
                                                @if($item->variant_name)
                                                    <span class="text-[9px] text-slate-500 font-mono">({{ $item->variant_name }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-1 px-1 text-center font-bold font-mono">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-1 px-1 text-right font-mono text-slate-600">
                                        {{ number_format($item->unit_price, 0) }}
                                    </td>
                                    <td class="py-1 px-1.5 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($item->subtotal, 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 4. Financials Summary & COD Highlight Box --}}
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200 items-end">
                        
                        {{-- Left: Notes / Instructions & Payment Badge --}}
                        <div class="text-[9.5px] text-slate-500">
                            @if(!empty($sale->note))
                                <template x-if="showNotes">
                                    <div class="bg-amber-50 text-amber-900 p-1.5 rounded border border-amber-200 mb-1">
                                        <span class="font-bold">Note:</span> {{ Str::limit($sale->note, 60) }}
                                    </div>
                                </template>
                            @endif

                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded border {{ $sale->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : ($sale->payment_status === 'partial' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-red-100 text-red-800 border-red-300') }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                                <span class="text-[9px] text-slate-500 uppercase font-mono">
                                    {{ str_replace('_', ' ', $sale->payment_method ?? 'cash') }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: Subtotal, Delivery, Total, and Prominent COD Box --}}
                        <div class="text-right">
                            <div class="text-[10px] space-y-0.5 text-slate-600">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span class="font-mono font-medium">{{ $currency }}{{ number_format($sale->subtotal, 0) }}</span>
                                </div>
                                @if($sale->delivery_charge > 0)
                                <div class="flex justify-between">
                                    <span>Delivery:</span>
                                    <span class="font-mono font-medium">+{{ $currency }}{{ number_format($sale->delivery_charge, 0) }}</span>
                                </div>
                                @endif
                                @if($sale->discount > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>Discount:</span>
                                    <span class="font-mono font-medium">-{{ $currency }}{{ number_format($sale->discount, 0) }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-0.5">
                                    <span>Total:</span>
                                    <span class="font-mono text-[11px]">{{ $currency }}{{ number_format($sale->total, 0) }}</span>
                                </div>
                            </div>

                            {{-- Highlighted COD / DUE Box --}}
                            @php
                                $codAmount = $sale->due_amount > 0 ? $sale->due_amount : ($sale->payment_status === 'paid' ? 0 : $sale->total);
                            @endphp
                            <div class="mt-1 p-1 px-2 rounded border-2 {{ $codAmount > 0 ? 'bg-amber-100 border-amber-500 text-amber-950' : 'bg-emerald-100 border-emerald-500 text-emerald-950' }} flex items-center justify-between font-bold">
                                <span class="text-[10px] uppercase tracking-wide">
                                    {{ $codAmount > 0 ? 'Cash on Delivery (COD)' : 'Paid in Full' }}:
                                </span>
                                <span class="font-mono text-xs sm:text-sm font-black">
                                    {{ $currency }}{{ number_format($codAmount, 0) }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── CUT LINE (between orders on the same page) ────────────── --}}
            {{-- In 2-per-page mode: cut line after odd-indexed (first) card --}}
            <template x-if="layout === 2 && ({{ $index }} % 2 === 0) && ({{ $index }} < {{ $totalSales - 1 }})">
                <div class="cut-line">
                    <span class="cut-line-badge">
                        <i class="fas fa-scissors mr-1"></i> Cut Here
                    </span>
                </div>
            </template>

            {{-- In 3-per-page mode: cut line between cards 1 & 2, and 2 & 3 --}}
            <template x-if="layout === 3 && ({{ $index }} % 3 !== 2) && ({{ $index }} < {{ $totalSales - 1 }})">
                <div class="cut-line">
                    <span class="cut-line-badge">
                        <i class="fas fa-scissors mr-1"></i> Cut Here
                    </span>
                </div>
            </template>


        @endforeach

    </div>

    <script>
        function bulkPrintApp() {
            return {
                layout: 2, // Default: 2 orders per page (recommended A4 half-page)
                showBarcode: true,
                showCourier: true,
                showNotes: true,

                setLayout(n) {
                    this.layout = n;
                }
            };
        }
    </script>
</body>
</html>
