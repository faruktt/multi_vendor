@extends('layouts.app')
@section('title', 'Page Management — ' . ($appSettings['name'] ?? 'Super POS'))
@section('heading', 'Page Management')

@push('styles')
{{-- Summernote Lite CSS --}}
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
        min-height: 250px !important;
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
<div x-data="pageManagement()" class="space-y-6">

    {{-- ── Alerts ──────────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-xs animate-in fade-in">
        <i class="fas fa-circle-check text-emerald-600 text-lg flex-shrink-0"></i>
        <div class="text-sm font-medium">{{ session('success') }}</div>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-xs">
        <div class="flex items-center gap-2 font-bold text-sm mb-1">
            <i class="fas fa-circle-exclamation text-rose-600"></i> Please fix the following errors:
        </div>
        <ul class="list-disc list-inside text-xs space-y-0.5 ml-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── Header Banner ──────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 via-blue-600 to-indigo-700 p-6 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fas fa-file-lines text-sky-200"></i> Storefront Pages CMS
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight flex items-center gap-2.5">
                    Custom Pages Management
                </h1>
                <p class="text-white/90 text-sm mt-1 max-w-xl">
                    Create and manage website pages (e.g. Terms & Conditions, Privacy Policy, Return Policy, FAQ). These page names will appear directly under Customer Service in the website footer.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('root') }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white font-semibold px-4 py-2.5 rounded-xl text-sm border border-white/20 backdrop-blur-sm transition-all">
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i> View Website
                </a>
                <button type="button" @click="openCreateModal()"
                        class="inline-flex items-center gap-2 bg-white text-blue-700 hover:bg-blue-50 font-bold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all hover:scale-105">
                    <i class="fas fa-plus text-xs"></i> Create New Page
                </button>
            </div>
        </div>
        <i class="fas fa-file-contract absolute -right-6 -bottom-8 text-white/10 text-[180px] pointer-events-none"></i>
    </div>

    {{-- ── Stat Cards ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center flex-shrink-0 text-sky-600">
                    <i class="fas fa-layer-group text-lg"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Total Pages</p>
                    <p class="text-2xl font-black text-slate-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600">
                    <i class="fas fa-circle-check text-lg"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Active in Footer</p>
                    <p class="text-2xl font-black text-emerald-600">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 text-slate-600">
                    <i class="fas fa-eye-slash text-lg"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Inactive Pages</p>
                    <p class="text-2xl font-black text-slate-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filters & Search ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
        <form method="GET" action="{{ route('admin.pages.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex-1 flex flex-col sm:flex-row items-center gap-2">
                <div class="relative w-full sm:w-80">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by page name, slug or content..."
                           class="w-full pl-9 pr-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-44 py-2 px-3 text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>

                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.pages.index') }}"
                   class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 font-semibold hover:bg-slate-100 rounded-xl transition">
                    Clear Filter
                </a>
                @endif
            </div>

            <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow-xs transition">
                <i class="fas fa-plus"></i> Add New Page
            </button>
        </form>
    </div>

    {{-- ── Pages List Table ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Page Name (Title)</th>
                        <th class="py-3.5 px-4">URL Slug</th>
                        <th class="py-3.5 px-4 text-center">Sort Order</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Last Updated</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($pages as $item)
                    <tr class="hover:bg-slate-50/60 transition-colors group">
                        <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                            {{ $loop->iteration + ($pages->currentPage() - 1) * $pages->perPage() }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-800 text-[13px] flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                {{ $item->title }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1 max-w-md">
                                {{ Str::limit(strip_tags($item->description), 80) }}
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11.5px] text-slate-600">
                            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md border border-slate-200">
                                /page/{{ $item->slug }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-slate-600">
                            {{ $item->sort_order }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <form action="{{ route('admin.pages.toggle', $item) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition-all {{ $item->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                                        title="Click to toggle status">
                                    <i class="fas {{ $item->is_active ? 'fa-check' : 'fa-xmark' }} text-[10px]"></i>
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500">
                            <div>{{ $item->updated_at->format('d M, Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $item->updated_at->format('h:i A') }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- View on live storefront --}}
                                <a href="{{ route('shop.pages.show', $item->slug) }}" target="_blank"
                                   class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 hover:text-sky-700 flex items-center justify-center transition"
                                   title="Preview live page">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>

                                {{-- Edit button --}}
                                <button type="button" @click="openEditModal({{ json_encode($item) }})"
                                        class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 flex items-center justify-center transition"
                                        title="Edit page">
                                    <i class="fas fa-pen-to-square text-xs"></i>
                                </button>

                                {{-- Delete button --}}
                                <form action="{{ route('admin.pages.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete the page &quot;{{ addslashes($item->title) }}&quot;?');"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition"
                                            title="Delete page">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 px-4 text-center">
                            <div class="max-w-sm mx-auto">
                                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="fas fa-file-circle-plus"></i>
                                </div>
                                <h3 class="font-bold text-slate-700 text-base mb-1">No Custom Pages Found</h3>
                                <p class="text-xs text-slate-400 mb-4">
                                    Create pages like "Privacy Policy", "Terms & Conditions", "Returns & Refunds". They will automatically be displayed in the website footer under Customer Service!
                                </p>
                                <button type="button" @click="openCreateModal()"
                                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl text-xs shadow-xs transition">
                                    <i class="fas fa-plus"></i> Create First Page
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pages->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $pages->links() }}
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── CREATE PAGE MODAL ───────────────────────────────────────────── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="createModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeCreateModal()"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">

            {{-- Modal Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-sky-600 to-blue-600 text-white flex items-center justify-between flex-shrink-0">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fas fa-file-circle-plus text-sky-200"></i> Create New Page
                </h3>
                <button type="button" @click="closeCreateModal()" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Modal Form --}}
            <form action="{{ route('admin.pages.store') }}" method="POST" id="createPageForm" class="p-5 space-y-4 overflow-y-auto flex-1">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Page Title --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                            Page Name (Title) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" x-model="createTitle" @input="updateCreateSlug()" required
                               placeholder="e.g. Terms & Conditions, Privacy Policy"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 font-medium">
                        <p class="text-[11px] text-slate-400 mt-1">This name will be displayed in the Customer Service footer section.</p>
                    </div>

                    {{-- URL Slug --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                            URL Slug <span class="text-slate-400 text-[10px] font-normal">(Auto-generated or custom)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">/page/</span>
                            <input type="text" name="slug" x-model="createSlug"
                                   placeholder="terms-and-conditions"
                                   class="w-full pl-16 pr-3.5 py-2.5 text-sm font-mono border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-700">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Friendly web address for this page.</p>
                    </div>
                </div>

                {{-- Page Description / Content (Summernote) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                        <span>Page Description / Content <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-slate-400 font-normal">Rich Text Editor (headings, bold, lists, etc.)</span>
                    </label>
                    <textarea id="summernote_create" name="description" class="w-full" required></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                            Sort Order <span class="text-slate-400 text-[10px] font-normal">(Lower numbers show first)</span>
                        </label>
                        <input type="number" name="sort_order" min="0" value="0"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-700 font-medium">
                    </div>

                    {{-- Status Switch --}}
                    <div class="flex items-center gap-3 pt-4 sm:pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2.5 text-xs font-bold text-slate-700">Publish Immediately (Visible in Footer)</span>
                        </label>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 flex-shrink-0">
                    <button type="button" @click="closeCreateModal()"
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2 rounded-xl text-xs shadow-md transition hover:scale-[1.02]">
                        <i class="fas fa-check text-xs"></i> Save &amp; Publish Page
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- ── EDIT PAGE MODAL ─────────────────────────────────────────────── --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div x-show="editModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeEditModal()"
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">

            {{-- Modal Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex items-center justify-between flex-shrink-0">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-indigo-200"></i> Edit Page
                </h3>
                <button type="button" @click="closeEditModal()" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Modal Form --}}
            <form :action="'{{ url('admin/pages') }}/' + (editingPage ? editingPage.id : '')" method="POST" id="editPageForm" class="p-5 space-y-4 overflow-y-auto flex-1">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Page Title --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                            Page Name (Title) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" x-model="editTitle" required
                               placeholder="e.g. Terms & Conditions, Privacy Policy"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-slate-800 font-medium">
                        <p class="text-[11px] text-slate-400 mt-1">This name will be displayed in the Customer Service footer section.</p>
                    </div>

                    {{-- URL Slug --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
                            URL Slug
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-mono">/page/</span>
                            <input type="text" name="slug" x-model="editSlug"
                                   placeholder="terms-and-conditions"
                                   class="w-full pl-16 pr-3.5 py-2.5 text-sm font-mono border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-slate-700">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">URL slug for the storefront page.</p>
                    </div>
                </div>

                {{-- Page Description / Content (Summernote) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5 flex items-center justify-between">
                        <span>Page Description / Content <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-slate-400 font-normal">Rich Text Editor</span>
                    </label>
                    <textarea id="summernote_edit" name="description" class="w-full" required></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 uppercase tracking-wide mb-1">
                            Sort Order
                        </label>
                        <input type="number" name="sort_order" min="0" x-model="editSortOrder"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-slate-700 font-medium">
                    </div>

                    {{-- Status Switch --}}
                    <div class="flex items-center gap-3 pt-4 sm:pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="editIsActive" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2.5 text-xs font-bold text-slate-700">Active (Visible in Footer)</span>
                        </label>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 flex-shrink-0">
                    <button type="button" @click="closeEditModal()"
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2 rounded-xl text-xs shadow-md transition hover:scale-[1.02]">
                        <i class="fas fa-check text-xs"></i> Update Page
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Summernote Lite JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<script>
function pageManagement() {
    return {
        createModalOpen: false,
        editModalOpen: false,
        editingPage: null,

        createTitle: '',
        createSlug: '',

        editTitle: '',
        editSlug: '',
        editSortOrder: 0,
        editIsActive: true,

        init() {
            // Setup Summernote for create
            $('#summernote_create').summernote({
                placeholder: 'Write page description, policy details, guidelines or information...',
                tabsize: 2,
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Setup Summernote for edit
            $('#summernote_edit').summernote({
                placeholder: 'Edit page description...',
                tabsize: 2,
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        },

        updateCreateSlug() {
            this.createSlug = this.createTitle
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        },

        openCreateModal() {
            this.createTitle = '';
            this.createSlug = '';
            $('#summernote_create').summernote('code', '');
            this.createModalOpen = true;
        },

        closeCreateModal() {
            this.createModalOpen = false;
        },

        openEditModal(item) {
            this.editingPage = item;
            this.editTitle = item.title;
            this.editSlug = item.slug;
            this.editSortOrder = item.sort_order;
            this.editIsActive = Boolean(item.is_active);

            $('#summernote_edit').summernote('code', item.description || '');
            this.editModalOpen = true;
        },

        closeEditModal() {
            this.editModalOpen = false;
            this.editingPage = null;
        }
    }
}
</script>
@endpush
