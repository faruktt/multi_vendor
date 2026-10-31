<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অর্ডার সম্পন্ন হয়েছে — {{ $sale->invoice_no }}</title>
    @if(!empty($appSettings['favicon']))
    <link rel="icon" href="{{ $appSettings['favicon'] }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/solaimanlipi.css">
    <style>
        body { font-family: 'SolaimanLipi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; }
    </style>

    {{-- Global & Custom Tracking (GTM, DataLayer, Meta Pixel) --}}
    @include('shop.partials.analytics')
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen py-10 px-4 flex items-center justify-center">

{{-- Google Tag Manager (noscript) --}}
@if(!empty($appSettings['gtm_id']))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $appSettings['gtm_id'] }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

    <div class="max-w-xl w-full bg-white rounded-3xl border border-slate-200/90 shadow-2xl p-6 sm:p-8 space-y-6 text-center">
        
        {{-- Success Checkmark Icon --}}
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl mx-auto shadow-inner animate-bounce">
            <i class="fas fa-check"></i>
        </div>

        <div class="space-y-1">
            <span class="text-xs font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                অর্ডার স্ট্যাটাস: পেন্ডিং কনফার্মেশন
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight pt-2">
                অভিনন্দন! আপনার অর্ডার সফল হয়েছে
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                আমাদের কাস্টমার কেয়ার প্রতিনিধি কিছুক্ষণের মধ্যে কল করে আপনার অর্ডারটি কনফার্ম করবেন।
            </p>
        </div>

        {{-- Invoice Number Highlight Box --}}
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
            <div class="text-left">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ইনভয়েস নম্বর</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $sale->invoice_no }}</div>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">অর্ডারের তারিখ</span>
                <div class="text-xs font-bold text-slate-700">{{ $sale->created_at->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        {{-- Order Items Breakdown --}}
        <div class="text-left space-y-3 pt-2">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">অর্ডারকৃত আইটেম:</h3>
            
            <div class="space-y-2">
                @foreach($sale->saleItems as $item)
                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($item->product?->image_url)
                        <img src="{{ $item->product->image_url }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 flex-shrink-0">
                        @else
                        <div class="w-12 h-12 rounded-lg bg-slate-200 flex items-center justify-center text-slate-400 text-xs flex-shrink-0">
                            <i class="fas fa-box"></i>
                        </div>
                        @endif
                        <div class="min-w-0">
                            <h4 class="text-xs font-extrabold text-slate-900 truncate">{{ $item->product?->name }}</h4>
                            @if($item->variant_name)
                            <p class="text-[11px] text-slate-500 font-semibold">{{ $item->variant_name }}</p>
                            @endif
                            <p class="text-[11px] text-slate-400">{{ $item->quantity }} x ৳{{ number_format($item->unit_price, 0) }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-black text-slate-900 flex-shrink-0">
                        ৳{{ number_format($item->subtotal, 0) }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Summary Breakdown --}}
            <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs font-medium text-slate-600">
                <div class="flex items-center justify-between">
                    <span>সাবটোটাল:</span>
                    <span class="font-bold text-slate-800">৳{{ number_format($sale->subtotal, 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>ডেলিভারি চার্জ:</span>
                    <span class="font-bold text-slate-800">{{ $sale->delivery_charge > 0 ? '৳' . number_format($sale->delivery_charge, 0) : 'ফ্রি' }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-base font-black text-slate-900">
                    <span>ক্যাশ অন ডেলিভারি পরিশোধযোগ্য:</span>
                    <span class="text-xl text-emerald-600">৳{{ number_format($sale->total, 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Shipping Details --}}
        <div class="text-left p-4 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-1.5 text-xs text-slate-700">
            <div class="font-bold text-blue-900 flex items-center gap-1.5">
                <i class="fas fa-truck text-blue-600"></i> ডেলিভারি ঠিকানা:
            </div>
            <div><strong>নাম:</strong> {{ $sale->customer?->name }}</div>
            <div><strong>মোবাইল:</strong> {{ $sale->customer?->phone }}</div>
            <div><strong>ঠিকানা:</strong> {{ $sale->customer?->address }}</div>
        </div>

        {{-- Action Buttons --}}
        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
            @php
                $rawPhone = preg_replace('/\D/', '', $appSettings['phone'] ?? '');
                $waNum = $rawPhone ? '880' . ltrim($rawPhone, '0') : null;
            @endphp
            @if($waNum)
            <a href="https://wa.me/{{ $waNum }}?text={{ urlencode('আসসালামু আলাইকুম, আমার অর্ডার ইনভয়েস #' . $sale->invoice_no) }}"
               target="_blank" rel="noopener"
               class="w-full sm:flex-1 py-3 px-4 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-sm transition-colors">
                <i class="fab fa-whatsapp text-sm"></i>
                <span>হোয়াটসঅ্যাপে যোগাযোগ</span>
            </a>
            @endif

            <a href="{{ route('root') }}"
               class="w-full sm:flex-1 py-3 px-4 rounded-xl bg-slate-900 hover:bg-black text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-sm transition-colors">
                <i class="fas fa-arrow-left text-[11px]"></i>
                <span>ওয়েবসাইটে ফিরে যান</span>
            </a>
        </div>

    </div>

@php
    $landingSuccessItems = $sale->saleItems->map(function($item) {
        return [
            'id'       => $item->product_id,
            'name'     => $item->product?->name ?? 'Product',
            'price'    => (float) $item->price,
            'quantity' => (int) $item->qty,
            'category' => $item->product?->category?->name ?? '',
        ];
    })->values()->all();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.trackPurchase === 'function') {
        window.trackPurchase({
            transaction_id: '{{ $sale->invoice_no }}',
            value: {{ (float) $sale->total }},
            tax: 0,
            shipping: {{ (float) ($sale->delivery_charge ?? 0) }},
            currency: 'BDT',
            items: @json($landingSuccessItems)
        });
    }
});
</script>

</body>
</html>

