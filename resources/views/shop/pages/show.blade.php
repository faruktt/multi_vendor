@extends('shop.layout')
@section('title', $page->title . ' — ' . ($branch->system_name ?? $branch->name ?? 'Shop'))
@section('main-class', 'w-full')

@section('breadcrumb')
<a href="{{ route('root') }}" class="flex items-center gap-1.5 hover:text-brand-dark flex-shrink-0">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
    Home
</a>
<span class="text-gray-300">/</span>
<span class="text-gray-900 font-semibold">{{ $page->title }}</span>
@endsection

@section('content')
<div class="max-w-[1200px] mx-auto px-4 py-8">

    {{-- ── Main Page Card ──────────────────────────────────────────────── --}}
    <article class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-8">

        {{-- Page Header --}}
        <div class="border-b border-gray-100 bg-gradient-to-b from-gray-50/80 to-white px-6 sm:px-10 py-8 sm:py-10">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 bg-brand/10 text-brand text-xs font-bold px-3 py-1 rounded-full mb-3 uppercase tracking-wider">
                    <i class="fas fa-file-lines text-[11px]"></i> Customer Service
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-gray-900 tracking-tight leading-tight mb-3">
                    {{ $page->title }}
                </h1>
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-calendar-alt"></i> Updated: {{ $page->updated_at->format('F d, Y') }}
                    </span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-shield-check text-emerald-500"></i> Official Page
                    </span>
                </div>
            </div>
        </div>

        {{-- Page Body / Description Content --}}
        <div class="px-6 sm:px-10 py-8 sm:py-12">
            <div class="page-content-wrapper prose max-w-none text-gray-700 leading-relaxed text-[15px]">
                {!! $page->description !!}
            </div>
        </div>

        {{-- Footer Help Bar --}}
        <div class="border-t border-gray-100 bg-gray-50/70 px-6 sm:px-10 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-gray-500 text-center sm:text-left">
                Need more information or help? Our customer support team is always here for you.
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('shop.products.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 transition">
                    <i class="fas fa-arrow-left text-[10px]"></i> Continue Shopping
                </a>
                <a href="{{ route('shop.track.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand hover:bg-brand-dark transition shadow-xs">
                    <i class="fas fa-truck-fast text-[10px]"></i> Track Your Order
                </a>
            </div>
        </div>

    </article>

</div>
@endsection

@push('styles')
<style>
    /* Styling for Summernote generated content inside page-content-wrapper */
    .page-content-wrapper {
        color: #334155;
        font-size: 15px;
        line-height: 1.8;
    }
    .page-content-wrapper h1,
    .page-content-wrapper h2,
    .page-content-wrapper h3,
    .page-content-wrapper h4,
    .page-content-wrapper h5,
    .page-content-wrapper h6 {
        color: #0f172a;
        font-weight: 800;
        margin-top: 1.5em;
        margin-bottom: 0.5em;
        line-height: 1.3;
    }
    .page-content-wrapper h1 { font-size: 1.85rem; }
    .page-content-wrapper h2 { font-size: 1.5rem; }
    .page-content-wrapper h3 { font-size: 1.25rem; }
    .page-content-wrapper p {
        margin-bottom: 1.2em;
    }
    .page-content-wrapper ul {
        list-style-type: disc;
        padding-left: 1.5em;
        margin-bottom: 1.2em;
    }
    .page-content-wrapper ol {
        list-style-type: decimal;
        padding-left: 1.5em;
        margin-bottom: 1.2em;
    }
    .page-content-wrapper li {
        margin-bottom: 0.4em;
    }
    .page-content-wrapper a {
        color: #2563eb;
        text-decoration: underline;
        font-weight: 500;
    }
    .page-content-wrapper a:hover {
        color: #1d4ed8;
    }
    .page-content-wrapper table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5em 0;
        font-size: 14px;
    }
    .page-content-wrapper table th,
    .page-content-wrapper table td {
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
    }
    .page-content-wrapper table th {
        background-color: #f8fafc;
        font-weight: 700;
        color: #1e293b;
    }
    .page-content-wrapper blockquote {
        border-left: 4px solid #3b82f6;
        padding-left: 16px;
        color: #64748b;
        font-style: italic;
        margin: 1.5em 0;
    }
    .page-content-wrapper hr {
        border: 0;
        border-top: 1px solid #e2e8f0;
        margin: 2em 0;
    }
</style>
@endpush
