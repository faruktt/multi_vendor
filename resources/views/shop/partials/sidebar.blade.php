{{-- Expects: $categories (top-level, each with ->children eager loaded), optional $activeCategoryId, optional $showFilters --}}
@php
    $catColors = [
        ['bg' => 'bg-blue-100',   'fg' => 'text-blue-600'],
        ['bg' => 'bg-purple-100', 'fg' => 'text-purple-600'],
        ['bg' => 'bg-amber-100',  'fg' => 'text-amber-600'],
        ['bg' => 'bg-pink-100',   'fg' => 'text-pink-600'],
        ['bg' => 'bg-green-100',  'fg' => 'text-green-700'],
        ['bg' => 'bg-sky-100',    'fg' => 'text-sky-600'],
    ];
    $activeCategoryId = $activeCategoryId ?? null;
@endphp
<div class="w-[280px] flex-shrink-0 self-stretch hidden lg:flex lg:flex-col gap-4">

    @if($showFilters ?? false)
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4">
        <form method="GET" action="{{ url()->current() }}" class="space-y-3">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-500 mb-1.5">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..."
                           class="w-full h-10 rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wide text-gray-500 mb-1.5">Price Range (৳)</label>
                <div class="flex items-center gap-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" min="0"
                           class="w-full h-10 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:bg-white">
                    <span class="text-gray-300">–</span>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" min="0"
                           class="w-full h-10 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:bg-white">
                </div>
            </div>
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 h-10 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-bold transition-colors">Apply</button>
                @if(request('q') || request('min_price') || request('max_price'))
                <a href="{{ url()->current() }}" class="h-10 px-3 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 text-sm font-semibold">Clear</a>
                @endif
            </div>
        </form>
    </div>
    @endif

<nav class="bg-white border border-gray-200 rounded-2xl shadow-sm">
    <a href="{{ route('shop.products.index') }}"
       class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 font-bold text-[13.5px] text-gray-900 hover:bg-gray-50 rounded-t-2xl {{ !$activeCategoryId ? 'bg-brand-light text-brand-dark' : '' }}">
        All Products
    </a>
    @foreach($categories as $i => $cat)
    @php
        $c = $catColors[$i % count($catColors)];
        $hasChildren = $cat->children->isNotEmpty();
        $isActive = $activeCategoryId === $cat->id || $cat->children->pluck('id')->contains($activeCategoryId);
    @endphp
    <div class="relative" @if($hasChildren) x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @endif>
        <a href="{{ route('shop.products.category', $cat->slug) }}"
           class="flex items-center gap-3 px-5 py-2.5 text-[13px] font-semibold text-gray-600 hover:bg-gray-50 hover:text-gray-900 {{ $loop->last ? 'rounded-b-2xl' : '' }} {{ $isActive ? 'bg-brand-light text-brand-dark' : '' }}"
           @if($hasChildren) :class="open ? 'bg-brand-light text-brand-dark' : ''" @endif>
            <span class="w-7 h-7 rounded-lg {{ $c['bg'] }} {{ $c['fg'] }} flex items-center justify-center flex-shrink-0">
                <i class="fas fa-tag text-[11px]"></i>
            </span>
            <span class="flex-1">{{ $cat->name }}</span>
            @if($hasChildren)
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 flex-shrink-0"><path d="M9 18l6-6-6-6"/></svg>
            @endif
        </a>

        @if($hasChildren)
        <div x-show="open" x-cloak
             class="absolute left-full top-0 ml-1 w-56 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-20">
            @foreach($cat->children as $child)
            <a href="{{ route('shop.products.category', $child->slug) }}"
               class="block px-4 py-2 text-[13px] {{ $activeCategoryId === $child->id ? 'text-brand-dark bg-brand-light' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-dark' }}">{{ $child->name }}</a>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
</nav>

</div>
