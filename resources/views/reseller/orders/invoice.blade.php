<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_no }} — {{ $reseller->business_name ?: $reseller->name }}</title>

    {{-- Tailwind CSS & FontAwesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Noto Sans Bengali', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Screen Styles */
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
            border-radius: 8px;
        }

        /* Print Media Styles */
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
<body x-data="{ copyType: 'customer', autoPrint: {{ request()->has('print') ? 'true' : 'false' }} }"
      x-init="if (autoPrint) { setTimeout(() => window.print(), 500); }">

    {{-- ════════════════════════════════════════════════════════════════
         TOP CONTROL TOOLBAR (Hidden when printing)
         ════════════════════════════════════════════════════════════════ --}}
    <header class="no-print fixed top-0 left-0 right-0 h-16 bg-slate-900 border-b border-slate-800 text-white z-50 px-4 md:px-8 flex items-center justify-between shadow-lg">
        
        {{-- Left: Back & Title --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('reseller.orders.show', $order) }}"
               class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 hover:text-white transition-colors"
               title="Back to Order Details">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-sm md:text-base font-bold flex items-center gap-2">
                    <i class="fas fa-file-invoice text-indigo-400"></i>
                    <span>Invoice #{{ $order->invoice_no }}</span>
                </h1>
                <p class="text-[11px] text-slate-400 hidden sm:block">Customer: {{ $order->customer?->name ?? 'Walk-in' }} (৳{{ number_format($order->total, 2) }})</p>
            </div>
        </div>

        {{-- Center: Copy Type Switcher --}}
        <div class="flex items-center gap-1 bg-slate-800 p-1 rounded-xl border border-slate-700">
            <button type="button"
                    @click="copyType = 'customer'"
                    :class="copyType === 'customer' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-user-check text-[11px]"></i>
                <span>Customer Slip</span>
                <span class="text-[10px] opacity-75 hidden md:inline">(গ্রাহক কপি)</span>
            </button>
            <button type="button"
                    @click="copyType = 'reseller'"
                    :class="copyType === 'reseller' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-receipt text-[11px]"></i>
                <span>Reseller Copy</span>
                <span class="text-[10px] opacity-75 hidden md:inline">(প্রফিট সহ)</span>
            </button>
        </div>

        {{-- Right: Print & Close Buttons --}}
        <div class="flex items-center gap-2.5">
            <button type="button"
                    onclick="window.print()"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-sm cursor-pointer">
                <i class="fas fa-print"></i>
                <span>Print Invoice</span>
            </button>
            <button type="button"
                    onclick="window.close()"
                    class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition"
                    title="Close">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>
    </header>

    {{-- ════════════════════════════════════════════════════════════════
         PRINTABLE INVOICE SHEET (A4 Standard)
         ════════════════════════════════════════════════════════════════ --}}
    <main class="invoice-card flex flex-col justify-between">
        <div>
            
            {{-- 1. Invoice Top Header: Brand & Invoice Meta --}}
            <div class="border-b-2 border-slate-900 pb-5 mb-5 flex items-start justify-between gap-4">
                
                {{-- Store / Reseller Business Information --}}
                <div class="max-w-[55%] space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white font-black text-sm">
                            {{ strtoupper(substr($reseller->business_name ?: $reseller->name, 0, 1)) }}
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 leading-none tracking-tight">
                                {{ $reseller->business_name ?: $reseller->name }}
                            </h2>
                            <p class="text-[10px] font-bold text-indigo-600 tracking-wider uppercase mt-0.5">
                                Verified Reseller Store
                            </p>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 space-y-0.5 pt-2">
                        @if($reseller->phone)
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-phone text-slate-400 text-[10px] w-3 text-center"></i>
                                <span>{{ $reseller->phone }}</span>
                            </div>
                        @endif
                        @if($reseller->email)
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-envelope text-slate-400 text-[10px] w-3 text-center"></i>
                                <span>{{ $reseller->email }}</span>
                            </div>
                        @endif
                        @if($reseller->address)
                            <div class="flex items-start gap-1.5">
                                <i class="fas fa-location-dot text-slate-400 text-[10px] w-3 text-center mt-0.5"></i>
                                <span class="leading-tight">{{ $reseller->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Invoice Meta Details & Barcode --}}
                <div class="text-right space-y-1.5 flex flex-col items-end">
                    <div class="inline-block px-3 py-1 bg-slate-900 text-white text-xs font-black tracking-widest uppercase rounded">
                        RETAIL INVOICE
                    </div>

                    <div class="font-mono text-base font-black text-slate-900 tracking-tight">
                        #{{ $order->invoice_no }}
                    </div>

                    <div class="text-xs text-slate-500 font-medium">
                        তারিখ: <strong class="text-slate-800">{{ $order->created_at->format('d M Y, h:i A') }}</strong>
                    </div>

                    <div class="flex items-center gap-1.5 justify-end text-xs">
                        <span class="text-slate-500">পেমেন্ট মেথড:</span>
                        <span class="px-2 py-0.5 rounded font-bold text-[11px] bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase">
                            {{ $order->payment_method ?? 'Cash on Delivery (COD)' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 justify-end text-xs">
                        <span class="text-slate-500">অর্ডার স্ট্যাটাস:</span>
                        <span class="font-bold text-slate-800 uppercase text-[11px]">
                            {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                        </span>
                    </div>

                    {{-- Barcode SVG --}}
                    @if(!empty($barcodeSvg))
                        <div class="pt-1 flex flex-col items-end">
                            <div class="barcode-container" style="max-height: 28px; overflow: hidden;">
                                {!! $barcodeSvg !!}
                            </div>
                            <span class="font-mono text-[9px] text-slate-400 tracking-wider">#{{ $order->invoice_no }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Customer & Shipping Details (Bill To / Ship To) --}}
            <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 mb-5 leading-normal text-xs">
                
                {{-- Bill To --}}
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block border-b border-slate-200 pb-1 mb-1.5">
                        <i class="fas fa-user mr-1 text-slate-400"></i> গ্রাহকের বিবরণ (Customer Details)
                    </span>
                    <div class="font-bold text-slate-900 text-sm">
                        {{ $order->customer?->name ?? 'Walk-in Customer' }}
                    </div>
                    @if($order->customer?->phone)
                        <div class="font-mono font-bold text-slate-800 text-xs flex items-center gap-1">
                            <i class="fas fa-phone text-[9px] text-slate-400"></i>
                            <span>{{ $order->customer->phone }}</span>
                        </div>
                    @endif
                    <div class="text-slate-600 pt-0.5">
                        <span class="font-semibold text-slate-700">ঠিকানা:</span>
                        {{ $order->customer?->address ?? ($order->note ?? 'ঠিকানা প্রদান করা হয়নি') }}
                    </div>
                </div>

                {{-- Ship To / Courier Shipping --}}
                <div class="space-y-1 border-l border-slate-200 pl-4">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block border-b border-slate-200 pb-1 mb-1.5">
                        <i class="fas fa-truck-fast mr-1 text-slate-400"></i> ডেলিভারি ও কুরিয়ার তথ্য (Shipping Info)
                    </span>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500">ডেলিভারি এরিয়া:</span>
                        @if($order->delivery_zone)
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded {{ $order->delivery_zone === 'inside' ? 'bg-emerald-100 text-emerald-800' : ($order->delivery_zone === 'sub_dhaka' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $order->delivery_zone === 'inside' ? 'Inside Dhaka' : ($order->delivery_zone === 'sub_dhaka' ? 'Sub Dhaka' : 'Outside Dhaka') }}
                            </span>
                        @else
                            <span class="font-semibold text-slate-700">Standard Delivery</span>
                        @endif
                    </div>

                    @if($order->district || $order->customer?->district)
                        <div class="text-slate-700 font-medium">
                            <span class="text-slate-500">জেলা/থানা:</span>
                            {{ implode(', ', array_filter([$order->thana ?? $order->customer?->thana, $order->district ?? $order->customer?->district])) }}
                        </div>
                    @endif

                    @if($order->courier_tracking_id || $order->courier_name)
                        <div class="text-slate-700 pt-0.5">
                            <span class="text-slate-500">কুরিয়ার:</span>
                            <strong>{{ ucfirst($order->courier_name ?? 'Courier') }}</strong>
                            @if($order->courier_tracking_id)
                                (Tracking: <span class="font-mono font-bold">{{ $order->courier_tracking_id }}</span>)
                            @endif
                        </div>
                    @endif

                    @if($order->note)
                        <div class="text-slate-600 italic text-[11px] pt-1">
                            <span class="font-semibold text-slate-700 not-italic">Note:</span> {{ $order->note }}
                        </div>
                    @endif
                </div>

            </div>

            {{-- 3. Items Table --}}
            <div class="overflow-hidden border border-slate-200 rounded-xl mb-5">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-900 text-white uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-2.5 px-3 w-10 text-center">#</th>
                            <th class="py-2.5 px-3">পণ্যের বিবরণ (Product Description)</th>
                            <th class="py-2.5 px-3 text-center w-16">পরিমাণ</th>
                            <th class="py-2.5 px-3 text-right w-24">দর (Price)</th>
                            <th class="py-2.5 px-3 text-right w-28">মোট (Total)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($order->saleItems as $index => $item)
                            <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                                <td class="py-2.5 px-3 text-center font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-2.5 px-3 font-medium text-slate-900">
                                    <div class="font-bold text-slate-800 text-xs leading-tight">
                                        {{ $item->product->name ?? 'Product' }}
                                    </div>
                                    @if($item->variant_name)
                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                            ভ্যারিয়েন্ট: <span class="font-semibold text-slate-700">{{ $item->variant_name }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-slate-800">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-medium text-slate-700">
                                    ৳{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-slate-900">
                                    ৳{{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 4. Financial Calculations & Summary --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
                
                {{-- Left: Notes & Reseller Accounting Breakdown (Visible on Reseller Copy) --}}
                <div class="space-y-3">
                    
                    {{-- Instructions & Policies --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <div class="font-bold text-slate-800 flex items-center gap-1 text-xs">
                            <i class="fas fa-circle-info text-indigo-600 text-[10px]"></i> ডেলিভারি ও রিসিভ সংক্রান্ত নির্দেশনা:
                        </div>
                        <ul class="list-disc list-inside text-[10.5px] space-y-0.5 text-slate-500">
                            <li>প্যাকেট খোলার সময় পণ্য ডেলিভারি প্রতিনিধির সামনে চেক করুন।</li>
                            <li>কোনো ত্রুটি থাকলে তাৎক্ষণিক ডেলিভারি প্রতিনিধিকে জানান।</li>
                            <li>আমাদের সাথে কেনাকাটা করার জন্য ধন্যবাদ!</li>
                        </ul>
                    </div>

                    {{-- Reseller Accounting Copy Box (Hidden on Customer Slip) --}}
                    <div x-show="copyType === 'reseller'" x-cloak
                         class="p-3.5 rounded-xl bg-indigo-50 border border-indigo-200 text-xs space-y-1.5 transition-all">
                        <div class="font-bold text-indigo-900 text-xs flex items-center justify-between border-b border-indigo-200 pb-1">
                            <span><i class="fas fa-lock text-[10px] mr-1"></i> রিসেলার অ্যাকাউন্ট সামারি (গোপনীয়)</span>
                            <span class="text-[10px] bg-indigo-200 text-indigo-800 px-1.5 py-0.2 rounded font-bold">Reseller Only</span>
                        </div>
                        <div class="flex justify-between text-slate-600 pt-0.5">
                            <span>রিসেলার পাইকারি খরচ:</span>
                            <span class="font-bold text-slate-800">৳{{ number_format(max(0, $order->subtotal - $order->reseller_profit), 2) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 bg-emerald-100/70 px-2 py-1 rounded font-bold">
                            <span>এই অর্ডারে আপনার লাভ (Profit):</span>
                            <span>৳{{ number_format($order->reseller_profit, 2) }}</span>
                        </div>
                        <div class="text-[10px] text-slate-400">
                            * কাস্টমার কপি প্রিন্ট করলে এই অংশটি গ্রাহক দেখতে পাবে না।
                        </div>
                    </div>
                </div>

                {{-- Right: Total Bill Amounts --}}
                <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>পণ্যের উপমোট (Subtotal):</span>
                        <span class="font-bold text-slate-800">৳{{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    @if($order->discount > 0)
                        <div class="flex justify-between text-emerald-600 font-semibold">
                            <span>ডিসকাউন্ট (Discount):</span>
                            <span>- ৳{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif

                    @if($order->delivery_charge > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>ডেলিভারি চার্জ (Delivery Charge):</span>
                            <span class="font-bold text-slate-800">+ ৳{{ number_format($order->delivery_charge, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t-2 border-slate-900 pt-2 mt-2 flex justify-between items-baseline">
                        <div>
                            <span class="font-black text-slate-900 text-sm uppercase block">সর্বমোট প্রদেয় (Net Total):</span>
                            <span class="text-[10px] text-slate-500 font-semibold">
                                @if($order->payment_status === 'paid')
                                    <i class="fas fa-circle-check text-emerald-500"></i> পেইড (Full Paid)
                                @else
                                    <i class="fas fa-hand-holding-dollar text-indigo-600"></i> ক্যাশ অন ডেলিভারি (COD)
                                @endif
                            </span>
                        </div>
                        <span class="font-black text-indigo-700 text-xl font-mono">
                            ৳{{ number_format($order->total, 2) }}
                        </span>
                    </div>

                    @if($order->paid_amount > 0 && $order->paid_amount < $order->total)
                        <div class="flex justify-between text-xs pt-1 border-t border-slate-200 text-emerald-700">
                            <span>অগ্রিম পরিশোধিত:</span>
                            <span class="font-bold">৳{{ number_format($order->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-rose-700 font-bold">
                            <span>বাকি ক্যাশ অন ডেলিভারি:</span>
                            <span>৳{{ number_format($order->due_amount, 2) }}</span>
                        </div>
                    @endif
                </div>

            </div>

        </div>

        {{-- 5. Invoice Bottom: Signatures & Footer --}}
        <div class="pt-12 mt-8 border-t border-slate-200">
            <div class="grid grid-cols-2 gap-8 text-center text-xs">
                <div>
                    <div class="border-t border-dashed border-slate-400 w-48 mx-auto pt-1 font-bold text-slate-700">
                        গ্রাহকের স্বাক্ষর (Customer Signature)
                    </div>
                </div>
                <div>
                    <div class="border-t border-dashed border-slate-400 w-48 mx-auto pt-1 font-bold text-slate-700">
                        অনুমোদিত স্বাক্ষর (Authorized Signature)
                    </div>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-400 mt-6 pt-2 border-t border-slate-100 flex items-center justify-between">
                <span>Invoice Generated by Reseller Portal · {{ config('app.name', 'Fayaz') }}</span>
                <span>Print Time: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </main>

</body>
</html>
