<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Print — {{ isset($product) ? $product->name : 'Bulk Print' }}</title>
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* ── Screen styles ── */
        body { background: #f1f5f9; font-family: 'SolaimanLipi', 'Segoe UI', sans-serif !important; }

        .label-card {
            width: 200px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 8px 8px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .label-card svg { width: 100%; height: auto; display: block; }

        /* ── Print styles ── */
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            .no-print { display: none !important; }

            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            #labels-container {
                display: flex;
                flex-wrap: wrap;
                gap: 0;
                padding: 4mm;
                align-items: flex-start;
                align-content: flex-start;
            }

            .label-card {
                width: 58mm;
                border: 1px dashed #aaa;
                border-radius: 0;
                padding: 2mm 2mm 1.5mm;
                margin: 1mm;
            }

            .label-card .product-name { font-size: 7.5pt; }
            .label-card .barcode-num  { font-size: 6pt; }
            .label-card .price-tag    { font-size: 8pt; }
        }
    </style>
</head>
<body>

{{-- ── Control bar (hidden on print) ── --}}
<div class="no-print sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm px-5 py-3 flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}" class="text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="font-bold text-slate-800 text-[14px]">
                @isset($product) {{ $product->name }} @else Bulk Barcode Print @endisset
            </h1>
            <p class="text-slate-400 text-[11.5px]">
                @isset($items) {{ $items->count() }} products @else 1 product @endisset
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        {{-- Quantity (single product only) --}}
        @isset($product)
        <div class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2 bg-slate-50">
            <label class="text-[12px] text-slate-500 font-medium whitespace-nowrap">Qty per page:</label>
            <input type="number" id="qty-input" min="1" max="200" value="1"
                   class="w-16 border border-slate-200 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                   onchange="updateQty(this.value)">
        </div>
        @endisset

        {{-- Label size selector --}}
        <div class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2 bg-slate-50">
            <label class="text-[12px] text-slate-500 font-medium">Label:</label>
            <select id="size-select" onchange="updateSize(this.value)"
                    class="border border-slate-200 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="58">58mm (Thermal)</option>
                <option value="50">50mm</option>
                <option value="80">80mm</option>
            </select>
        </div>

        <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold text-[13px] flex items-center gap-2 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
    </div>
</div>

{{-- ── Preview area ── --}}
<div class="no-print px-6 py-5">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-4 text-[12.5px] text-slate-500 flex items-start gap-2.5">
        <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
        <span>Label preview below. Set quantity and label size, then click <strong>Print</strong>. For thermal printers set paper size to <strong>58×30mm</strong> in print dialog.</span>
    </div>
</div>

{{-- ── Labels ── --}}
<div id="labels-container" class="flex flex-wrap gap-3 px-6 pb-10 no-print-gap">

    @isset($product)
    {{-- Single product: repeat by quantity --}}
    <template id="label-template">
        <div class="label-card" id="label-size-58">
            <div style="width:100%">{!! $svg !!}</div>
            <p class="barcode-num font-mono text-[10px] text-slate-500 tracking-widest text-center">{{ $product->barcode }}</p>
            <p class="product-name font-semibold text-[11.5px] text-slate-800 text-center leading-tight line-clamp-2 w-full text-center">{{ $product->name }}</p>
            <p class="price-tag font-bold text-[12px] text-blue-700">{{ $appSettings['currency'] ?? '৳' }}{{ number_format($product->price, 0) }}</p>
        </div>
    </template>
    {{-- JS will clone template into here --}}
    @endisset

    @isset($items)
    @foreach($items as $item)
    <div class="label-card">
        <div style="width:100%">{!! $item->barcode_svg !!}</div>
        <p class="barcode-num font-mono text-[10px] text-slate-500 tracking-widest text-center">{{ $item->barcode }}</p>
        <p class="product-name font-semibold text-[11.5px] text-slate-800 text-center leading-tight line-clamp-2 w-full text-center">{{ $item->name }}</p>
        <p class="price-tag font-bold text-[12px] text-blue-700">{{ $appSettings['currency'] ?? '৳' }}{{ number_format($item->price, 0) }}</p>
    </div>
    @endforeach
    @endisset

</div>

{{-- Print version (no gaps, proper sizing) --}}
<div id="print-area" style="display:none">
    @isset($product)
    <div id="print-labels" class="flex flex-wrap"></div>
    @endisset
    @isset($items)
    @foreach($items as $item)
    <div class="label-card">
        <div style="width:100%">{!! $item->barcode_svg !!}</div>
        <p class="barcode-num font-mono text-[10px] text-slate-500 tracking-widest text-center">{{ $item->barcode }}</p>
        <p class="product-name font-semibold text-[11.5px] text-slate-800 text-center leading-tight">{{ $item->name }}</p>
        <p class="price-tag font-bold text-[12px] text-blue-700">{{ $appSettings['currency'] ?? '৳' }}{{ number_format($item->price, 0) }}</p>
    </div>
    @endforeach
    @endisset
</div>

<script>
    const labelSizes = { '58': '200px', '50': '175px', '80': '280px' };

    @isset($product)
    const templateHtml = `
        <div class="label-card" style="">
            <div style="width:100%">{!! addslashes($svg) !!}</div>
            <p class="barcode-num font-mono text-slate-500 tracking-widest text-center" style="font-size:10px">{{ $product->barcode }}</p>
            <p class="product-name font-semibold text-slate-800 text-center" style="font-size:11.5px;line-height:1.3">{{ addslashes($product->name) }}</p>
            <p class="price-tag font-bold text-blue-700" style="font-size:12px">{{ $appSettings['currency'] ?? '৳' }}{{ number_format($product->price,0) }}</p>
        </div>`;

    function updateQty(qty) {
        qty = Math.min(Math.max(parseInt(qty) || 1, 1), 200);
        const container = document.getElementById('labels-container');
        container.innerHTML = '';
        const size = document.getElementById('size-select')?.value || '58';
        const w = labelSizes[size] || '200px';
        for (let i = 0; i < qty; i++) {
            const div = document.createElement('div');
            div.innerHTML = templateHtml;
            const card = div.firstElementChild;
            card.style.width = w;
            container.appendChild(card);
        }
        document.getElementById('qty-input').value = qty;
    }

    function updateSize(size) {
        const w = labelSizes[size] || '200px';
        document.querySelectorAll('#labels-container .label-card').forEach(c => {
            c.style.width = w;
        });
    }

    // Init with qty=1
    updateQty(1);
    @endisset

    @isset($items)
    function updateSize(size) {
        const w = labelSizes[size] || '200px';
        document.querySelectorAll('#labels-container .label-card').forEach(c => {
            c.style.width = w;
        });
    }
    @endisset
</script>
</body>
</html>
