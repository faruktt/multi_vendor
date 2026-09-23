@extends('layouts.app')
@section('title', 'Homepage Content Management')
@section('heading', 'Homepage Content Management')

@push('styles')
{{-- Summernote Lite CSS (Independent of Bootstrap) --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        overflow: hidden !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .note-toolbar {
        background-color: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 8px !important;
    }
    .note-btn {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.375rem !important;
        color: #334155 !important;
        font-size: 12px !important;
        padding: 4px 8px !important;
    }
    .note-btn:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .note-editable {
        background-color: #ffffff !important;
        min-height: 220px !important;
        font-size: 14px !important;
        color: #1e293b !important;
        line-height: 1.6 !important;
        padding: 16px !important;
    }
    .note-statusbar {
        background-color: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
    }
</style>
@endpush

@section('content')

{{-- ── Header Banner ──────────────────────────────────────────────── --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-600 p-6 text-white shadow-lg mb-6">
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                <i class="fas fa-file-lines text-teal-200"></i> Storefront CMS Section
            </div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight flex items-center gap-2.5">
                Homepage Content Management
            </h1>
            <p class="text-white/90 text-sm mt-1 max-w-xl">
                Add and customize rich text sections displayed on the website homepage (below products and above the footer). Ideal for SEO articles, store introductions, policy highlights, or promotional write-ups.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('shop.home') }}" target="_blank"
               class="inline-flex items-center gap-2 bg-white text-teal-700 hover:bg-teal-50 font-bold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all hover:scale-105">
                <i class="fas fa-arrow-up-right-from-square text-xs"></i> View Live Homepage
            </a>
        </div>
    </div>
    <i class="fas fa-pen-nib absolute -right-6 -bottom-8 text-white/10 text-[180px] pointer-events-none"></i>
</div>

{{-- ── Stat Cards ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center flex-shrink-0 text-teal-600">
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Sections</p>
                <p class="text-2xl font-black text-slate-800">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600">
                <i class="fas fa-handshake text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Reseller Content</p>
                <div class="flex items-center gap-2">
                    <p class="text-2xl font-black text-indigo-700">{{ $stats['reseller'] ?? 0 }}</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">Reseller</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                <i class="fas fa-store text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Supplier Content</p>
                <div class="flex items-center gap-2">
                    <p class="text-2xl font-black text-emerald-700">{{ $stats['supplier'] ?? 0 }}</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Supplier</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 text-slate-600">
                <i class="fas fa-circle-check text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Active (Live)</p>
                <div class="flex items-center gap-2">
                    <p class="text-2xl font-black text-slate-800">{{ $stats['active'] }}</p>
                    @if($stats['active'] > 0)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 animate-pulse">Live</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Two-Column Layout ─────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="homeContentManager()">

    {{-- ════ LEFT: Add New Content Section (5 cols) ════ --}}
    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden sticky top-4">
        <div class="px-5 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-teal-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center text-sm shadow-sm">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 text-sm">Add Homepage Content</h2>
                    <p class="text-[11px] text-slate-500">Choose Reseller or Supplier section</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.home-contents.store') }}" method="POST" id="createContentForm" class="p-5 space-y-4">
            @csrf

            {{-- Content Type Selection --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                    Content Target / Category <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2.5">
                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 has-[:checked]:ring-2 has-[:checked]:ring-indigo-500/20">
                        <input type="radio" name="type" value="reseller" class="text-indigo-600 focus:ring-indigo-500" {{ old('type', 'reseller') === 'reseller' ? 'checked' : '' }}>
                        <div>
                            <span class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-handshake text-indigo-600"></i> Reseller
                            </span>
                            <span class="block text-[10.5px] text-slate-400">রিসেলার কার্ডের জন্য</span>
                        </div>
                    </label>
                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="type" value="supplier" class="text-emerald-600 focus:ring-emerald-500" {{ old('type') === 'supplier' ? 'checked' : '' }}>
                        <div>
                            <span class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fas fa-store text-emerald-600"></i> Supplier
                            </span>
                            <span class="block text-[10.5px] text-slate-400">সাপ্লায়ার কার্ডের জন্য</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Title / Heading (Optional) --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                    Section Title / Heading <span class="text-slate-400 text-[10px] font-normal">(Optional)</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. আমাদের সাথে রিসেলার হিসেবে ব্যবসা শুরু করুন..."
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white text-slate-800 font-medium">
            </div>

            {{-- Summernote Editor for Content --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                    <span>Content (Rich Text) <span class="text-red-500">*</span></span>
                    <span class="text-[10px] text-teal-600 font-semibold lowercase">Powered by Summernote</span>
                </label>
                <textarea id="summernote_create" name="content" class="w-full" required>{{ old('content') }}</textarea>
            </div>

            {{-- Sort Order & Status Row --}}
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                        Sort Order
                    </label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}"
                           class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white text-slate-700 font-medium">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                        Active Status
                    </label>
                    <div class="flex items-center justify-between h-[38px] px-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-xs font-semibold text-slate-700">Show on Home</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <button type="submit"
                    class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md shadow-teal-100 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-check text-xs"></i> Publish to Homepage
            </button>
        </form>
    </div>

    {{-- ════ RIGHT: Content List (7 cols) ════ --}}
    <div class="lg:col-span-7 min-w-0">

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
                <div class="relative flex-1 min-w-[160px]">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search title or text..."
                           class="w-full pl-9 pr-3.5 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white">
                </div>
                <select name="type"
                        class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-600">
                    <option value="">All Types</option>
                    <option value="reseller" {{ request('type') === 'reseller' ? 'selected' : '' }}>Reseller Only</option>
                    <option value="supplier" {{ request('type') === 'supplier' ? 'selected' : '' }}>Supplier Only</option>
                </select>
                <select name="status"
                        class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-600">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
                <button type="submit"
                        class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                    Filter
                </button>
                @if(request('search') || request('type') || request('status'))
                <a href="{{ route('admin.home-contents.index') }}"
                   class="border border-slate-200 text-slate-500 px-3 py-2 rounded-xl text-sm hover:bg-slate-50 transition-colors">
                    Reset
                </a>
                @endif
            </form>
            <span class="text-xs text-slate-400 font-medium whitespace-nowrap">
                {{ $contents->total() }} sections
            </span>
        </div>

        {{-- Content Cards List --}}
        <div class="space-y-4">
            @forelse($contents as $item)
            <div class="bg-white rounded-2xl border {{ $item->is_active ? 'border-slate-200 shadow-xs' : 'border-slate-200 opacity-75' }} p-5 transition-all hover:shadow-md relative">
                
                {{-- Card Header --}}
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-slate-100">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Type Badge --}}
                            @if(($item->type ?? 'reseller') === 'supplier')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fas fa-store text-[9px]"></i> Supplier
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-indigo-100 text-indigo-800 border border-indigo-200">
                                <i class="fas fa-handshake text-[9px]"></i> Reseller
                            </span>
                            @endif

                            <h3 class="font-bold text-slate-900 text-base leading-snug">
                                {{ $item->title ?: '(Untitled Content Section)' }}
                            </h3>

                            @if($item->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                Inactive
                            </span>
                            @endif

                            <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                Order: {{ $item->sort_order }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Added {{ $item->created_at->diffForHumans() }}
                            @if($item->createdBy)
                            by <span class="font-medium text-slate-600">{{ $item->createdBy->name }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Toggle Status Button --}}
                        <form action="{{ route('admin.home-contents.toggle', $item->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition-colors {{ $item->is_active ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                <i class="fas {{ $item->is_active ? 'fa-toggle-on text-base' : 'fa-toggle-off text-base' }}"></i>
                            </button>
                        </form>

                        {{-- Edit Button --}}
                        <button type="button"
                                @click="openEditModal(@js($item))"
                                title="Edit Content"
                                class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 flex items-center justify-center text-xs transition-colors">
                            <i class="fas fa-pen-to-square"></i>
                        </button>

                        {{-- Delete Button --}}
                        <form action="{{ route('admin.home-contents.destroy', $item->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this content section?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    title="Delete Content"
                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center text-xs transition-colors">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Content Preview Container --}}
                <div class="mt-3 text-xs text-slate-600 line-clamp-3 prose prose-xs max-w-none bg-slate-50/70 p-3 rounded-xl border border-slate-100 overflow-hidden">
                    {!! $item->content !!}
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fas fa-file-lines"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-base mb-1">No Homepage Content Added Yet</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">
                    Use the form on the left to add Reseller or Supplier content. They will show up side-by-side in the homepage partnership section!
                </p>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($contents->hasPages())
            <div class="mt-4">
                {{ $contents->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- ════ Edit Modal ════ --}}
    <div x-show="editModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeEditModal()"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="px-5 py-4 bg-gradient-to-r from-teal-600 to-emerald-600 text-white flex items-center justify-between">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-teal-200"></i> Edit Homepage Content
                </h3>
                <button type="button" @click="closeEditModal()" class="text-white/80 hover:text-white">
                    <i class="fas fa-xmark text-base"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/home-contents') }}/' + (editingItem ? editingItem.id : '')" method="POST" id="editContentForm" class="p-5 space-y-4">
                @csrf
                @method('PUT')

                {{-- Content Type in Edit Modal --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                        Content Target / Category <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition" :class="editType === 'reseller' ? 'border-indigo-500 bg-indigo-50/50 ring-2 ring-indigo-500/20' : ''">
                            <input type="radio" name="type" value="reseller" x-model="editType" class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-handshake text-indigo-600"></i> Reseller
                                </span>
                                <span class="block text-[10.5px] text-slate-400">রিসেলার কার্ড</span>
                            </div>
                        </label>
                        <label class="relative flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition" :class="editType === 'supplier' ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20' : ''">
                            <input type="radio" name="type" value="supplier" x-model="editType" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-store text-emerald-600"></i> Supplier
                                </span>
                                <span class="block text-[10.5px] text-slate-400">সাপ্লায়ার কার্ড</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                        Section Title / Heading <span class="text-slate-400 text-[10px] font-normal">(Optional)</span>
                    </label>
                    <input type="text" name="title" x-model="editTitle" placeholder="e.g. About Our Shop..."
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white text-slate-800 font-medium">
                </div>

                {{-- Summernote Editor for Edit --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                        <span>Content (Summernote) <span class="text-red-500">*</span></span>
                    </label>
                    <textarea id="summernote_edit" name="content" class="w-full" required></textarea>
                </div>

                {{-- Sort Order & Status Row --}}
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                            Sort Order
                        </label>
                        <input type="number" name="sort_order" min="0" x-model="editSortOrder"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white text-slate-700 font-medium">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                            Active Status
                        </label>
                        <div class="flex items-center justify-between h-[38px] px-3 bg-slate-50 border border-slate-200 rounded-xl">
                            <span class="text-xs font-semibold text-slate-700">Show on Home</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" :checked="editIsActive">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeEditModal()" class="px-4 py-2 rounded-xl text-sm border border-slate-200 text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold bg-teal-600 hover:bg-teal-700 text-white shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
{{-- jQuery (Required by Summernote) --}}
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
{{-- Summernote Lite JS --}}
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<script>
$(document).ready(function() {
    // Common Summernote toolbar configuration
    const summernoteConfig = {
        placeholder: 'Type your rich text content here... You can add headings, bold text, bullet points, links, tables, and images.',
        tabsize: 2,
        height: 240,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    };

    // Initialize create form editor
    $('#summernote_create').summernote(summernoteConfig);

    // Initialize edit modal editor
    $('#summernote_edit').summernote(summernoteConfig);
});

function homeContentManager() {
    return {
        editModalOpen: false,
        editingItem: null,
        editTitle: '',
        editType: 'reseller',
        editSortOrder: 0,
        editIsActive: true,

        openEditModal(item) {
            this.editingItem = item;
            this.editTitle = item.title || '';
            this.editType = item.type || 'reseller';
            this.editSortOrder = item.sort_order || 0;
            this.editIsActive = Boolean(item.is_active);
            this.editModalOpen = true;

            // Set content in Summernote
            setTimeout(() => {
                $('#summernote_edit').summernote('code', item.content || '');
            }, 100);
        },

        closeEditModal() {
            this.editModalOpen = false;
            this.editingItem = null;
        }
    };
}
</script>
@endpush

@endsection
