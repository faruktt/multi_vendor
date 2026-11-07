@extends('layouts.app')
@section('title', 'All Subcategories')
@section('heading', 'All Subcategories')

@section('content')
<div>

    {{-- ══ FILTERS ══ --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
        <div class="flex flex-wrap gap-2.5 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Subcategory name..."
                   class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 w-44 bg-slate-50 focus:bg-white transition-colors">

            <select name="parent_id" class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white">
                <option value="">All Categories</option>
                @foreach($parentCategories as $pCat)
                <option value="{{ $pCat->id }}" {{ (string)request('parent_id') === (string)$pCat->id ? 'selected' : '' }}>{{ $pCat->name }}</option>
                @endforeach
            </select>

            <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>

            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-colors">Filter</button>
            <a href="{{ route('admin.all-subcategories') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-xs transition-colors">Reset</a>

            <div class="ml-auto flex items-center gap-3">
                <span class="text-[12px] text-slate-400 hidden sm:inline">{{ count($subcategories) }} subcategories</span>
                <a href="{{ route('admin.warehouse.subcategories.index', 'warehouse') }}"
                   class="bg-[#6366f1] hover:bg-[#4f46e5] text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm shadow-indigo-200 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Manage Subcategories
                </a>
            </div>
        </div>
    </form>

    {{-- ══ TABLE ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase w-10">#</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Subcategory</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Parent Category</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Products</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($subcategories as $i => $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                @if($sub->image_url)
                                <img src="{{ $sub->image_url }}"
                                     class="w-8 h-8 rounded-lg object-cover flex-shrink-0" alt="">
                                @else
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-sitemap text-[11px]"></i>
                                </div>
                                @endif
                                <p class="font-semibold text-slate-800 text-[12.5px]">{{ $sub->name }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full text-xs">
                                {{ $sub->parent_name }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($sub->status ?? 'active') === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-slate-700 text-[13px]">{{ $sub->products_count }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400 text-sm">No subcategories found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
