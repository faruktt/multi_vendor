@extends('layouts.app')
@section('title', 'Subcategories')
@section('heading', 'Subcategories Management')

@section('content')
<div x-data="{
    showCreate: false,
    editData: null,
    createPreview: null,
    editPreview: null,
    openCreate() {
        this.createPreview = null;
        this.showCreate = true;
    },
    openEdit(sub) {
        this.editData = JSON.parse(JSON.stringify(sub));
        this.editPreview = sub.image_url || null;
    },
    handleImageUpload(e, target) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                if (target === 'create') this.createPreview = ev.target.result;
                if (target === 'edit') this.editPreview = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">

    {{-- ══ TOP HEADER ══ --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-800">Subcategories</h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $paginatedSubcategories->total() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Manage all subcategories linked under parent categories.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.warehouse.categories.index', 'warehouse') }}"
               class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors">
                <i class="fas fa-tags text-[11px] text-slate-400"></i>
                <span>Categories</span>
            </a>
            <button @click="openCreate()"
                    class="bg-[#6366f1] hover:bg-[#4f46e5] text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm shadow-indigo-200 transition-all hover:scale-102 cursor-pointer">
                <i class="fas fa-plus text-[11px]"></i>
                <span>Create</span>
            </button>
        </div>
    </div>

    {{-- ══ FILTERS & SEARCH ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3.5 mb-5">
        <form method="GET" action="{{ route('admin.warehouse.subcategories.index', 'warehouse') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search subcategory or category..."
                       class="w-full pl-9 pr-3.5 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 focus:bg-white transition-colors">
            </div>

            <div class="w-52">
                <select name="category" onchange="this.form.submit()"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 focus:bg-white transition-colors">
                    <option value="">All Categories</option>
                    @foreach($parentCategories as $pCat)
                        <option value="{{ $pCat->name }}" {{ request('category') == $pCat->name || request('category') == $pCat->id ? 'selected' : '' }}>
                            {{ $pCat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-colors">
                Filter
            </button>

            @if(request()->hasAny(['search', 'category']))
            <a href="{{ route('admin.warehouse.subcategories.index', 'warehouse') }}"
               class="text-xs text-slate-500 hover:text-slate-800 font-medium px-2 py-2">
                <i class="fas fa-rotate-left mr-1"></i> Reset
            </a>
            @endif

            <div class="ml-auto text-xs text-slate-400 font-medium">
                Showing {{ $paginatedSubcategories->firstItem() ?? 0 }} to {{ $paginatedSubcategories->lastItem() ?? 0 }} of {{ $paginatedSubcategories->total() }} entries
            </div>
        </form>
    </div>

    {{-- ══ SUBCATEGORIES TABLE (MATCHING USER SCREENSHOT) ══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f1f5f9]/70 border-b border-slate-200/80 text-slate-600 text-xs font-semibold select-none">
                        <th class="py-3.5 px-4 w-16 text-center">
                            <span class="inline-flex items-center gap-1.5 cursor-pointer">
                                SL
                                <i class="fas fa-chevron-up text-[9px] text-slate-400"></i>
                            </span>
                        </th>
                        <th class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 cursor-pointer">
                                Category
                                <i class="fas fa-sort text-[10px] text-slate-300"></i>
                            </span>
                        </th>
                        <th class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 cursor-pointer">
                                Name
                                <i class="fas fa-sort text-[10px] text-slate-300"></i>
                            </span>
                        </th>
                        <th class="py-3.5 px-6">
                            <span class="inline-flex items-center gap-1.5 cursor-pointer">
                                Status
                                <i class="fas fa-sort text-[10px] text-slate-300"></i>
                            </span>
                        </th>
                        <th class="py-3.5 px-6 text-center w-36">
                            <span class="inline-flex items-center gap-1.5 cursor-pointer">
                                Action
                                <i class="fas fa-sort text-[10px] text-slate-300"></i>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($paginatedSubcategories as $index => $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- SL --}}
                        <td class="py-4 px-4 text-center font-medium text-slate-500">
                            {{ $paginatedSubcategories->firstItem() + $index }}
                        </td>

                        {{-- Parent Category --}}
                        <td class="py-4 px-6 font-medium text-slate-600">
                            {{ $sub->parent_name }}
                        </td>

                        {{-- Subcategory Name (with thumbnail) --}}
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2.5">
                                @if($sub->image_url)
                                    <img src="{{ $sub->image_url }}" alt="{{ $sub->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200/80 flex-shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200/60 flex items-center justify-center flex-shrink-0 text-slate-400">
                                        <i class="fas fa-sitemap text-[11px]"></i>
                                    </div>
                                @endif
                                <span class="font-semibold text-slate-800 text-[13px]">{{ $sub->name }}</span>
                                @if($sub->products_count > 0)
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $sub->products_count }} products)</span>
                                @endif
                            </div>
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-4 px-6">
                            @if($sub->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#d1fae5] text-[#059669]">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Actions: Thumbs down/up toggle, Purple Edit, Delete --}}
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Status Toggle Button --}}
                                <form method="POST" action="{{ route('admin.warehouse.subcategories.toggle-status', ['warehouse', $sub->id]) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            title="{{ $sub->is_active ? 'Set Inactive' : 'Set Active' }}"
                                            class="w-7 h-7 rounded-md bg-[#475569] hover:bg-[#334155] text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer">
                                        @if($sub->is_active)
                                            <i class="fas fa-thumbs-down text-[11px]"></i>
                                        @else
                                            <i class="fas fa-thumbs-up text-[11px]"></i>
                                        @endif
                                    </button>
                                </form>

                                {{-- Edit Button (Purple icon button) --}}
                                <button type="button"
                                        @click="openEdit({{ json_encode($sub) }})"
                                        title="Edit Subcategory"
                                        class="w-7 h-7 rounded-md bg-[#6366f1] hover:bg-[#4f46e5] text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </button>

                                {{-- Delete Button --}}
                                <form method="POST" action="{{ route('admin.warehouse.subcategories.destroy', ['warehouse', $sub->id]) }}"
                                      onsubmit="return confirm('Are you sure you want to delete subcategory \'{{ addslashes($sub->name) }}\'?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Delete Subcategory"
                                            class="w-7 h-7 rounded-md bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                                <i class="fas fa-sitemap text-xl"></i>
                            </div>
                            <p class="font-semibold text-slate-600 text-sm">No subcategories found</p>
                            <p class="text-xs text-slate-400 mt-1">Click the "Create" button on top to add a subcategory.</p>
                            <button @click="openCreate()" class="mt-3.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition-colors">
                                <i class="fas fa-plus text-[10px]"></i> Create First Subcategory
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ══ TABLE FOOTER / PAGINATION (MATCHING USER SCREENSHOT) ══ --}}
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Showing {{ $paginatedSubcategories->firstItem() ?? 0 }} to {{ $paginatedSubcategories->lastItem() ?? 0 }} of {{ $paginatedSubcategories->total() }} entries
            </div>

            @if($paginatedSubcategories->hasPages())
            <div class="flex items-center gap-1 select-none">
                {{-- Previous Page Link --}}
                @if($paginatedSubcategories->onFirstPage())
                    <span class="w-7 h-7 flex items-center justify-center text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                    </span>
                @else
                    <a href="{{ $paginatedSubcategories->previousPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach($paginatedSubcategories->getUrlRange(1, $paginatedSubcategories->lastPage()) as $page => $url)
                    @if($page == $paginatedSubcategories->currentPage())
                        <span class="w-7 h-7 rounded-full bg-[#6366f1] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="w-7 h-7 rounded-full text-slate-600 hover:bg-slate-100 flex items-center justify-center font-medium text-xs transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if($paginatedSubcategories->hasMorePages())
                    <a href="{{ $paginatedSubcategories->nextPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                @else
                    <span class="w-7 h-7 flex items-center justify-center text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </span>
                @endif
            </div>
            @else
            <div class="flex items-center gap-1 select-none">
                <span class="w-7 h-7 flex items-center justify-center text-slate-300">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                </span>
                <span class="w-7 h-7 rounded-full bg-[#6366f1] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                    1
                </span>
                <span class="w-7 h-7 flex items-center justify-center text-slate-300">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ CREATE MODAL ══ --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="showCreate = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-sitemap text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Create Subcategory</h3>
                </div>
                <button type="button" @click="showCreate = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.warehouse.subcategories.store', 'warehouse') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Parent Category Dropdown --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="parent_id" required
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                        <option value="">— Select Category —</option>
                        @foreach($parentCategories as $pCat)
                            <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subcategory Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Subcategory Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors"
                           placeholder="e.g. T-Shirt, Sneakers, Formal Shirt, etc.">
                </div>

                {{-- Image File Input & Preview --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-slate-700">Subcategory Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <span class="text-[10.5px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded">৩০০ × ৩০০ px</span>
                    </div>
                    <p class="text-[10.5px] text-slate-400 mb-1.5">প্রস্তাবিত সাইজ: ৩০০ × ৩০০ পিক্সেল (১:১ স্কয়ার আইকন)</p>
                    <div class="flex items-center gap-3">
                        <template x-if="createPreview">
                            <img :src="createPreview" class="w-12 h-12 rounded-xl object-cover border border-indigo-200 shadow-xs flex-shrink-0">
                        </template>
                        <input type="file" name="image" accept="image/*" @change="handleImageUpload($event, 'create')"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status"
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreate = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-[#6366f1] hover:bg-[#4f46e5] text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-indigo-200 transition-colors">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ EDIT MODAL ══ --}}
    <div x-show="editData !== null" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="editData = null" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-pen text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Edit Subcategory</h3>
                </div>
                <button type="button" @click="editData = null" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="editData">
                <form :action="'{{ route('admin.warehouse.subcategories.store', 'warehouse') }}/' + editData.id"
                      method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Parent Category Dropdown --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="parent_id" required x-model="editData.parent_id"
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                            @foreach($parentCategories as $pCat)
                                <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subcategory Name --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Subcategory Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required x-model="editData.name"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>

                    {{-- Image File Input & Preview --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <span class="text-[10.5px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded">৩০০ × ৩০০ px</span>
                        </div>
                        <p class="text-[10.5px] text-slate-400 mb-1.5">প্রস্তাবিত সাইজ: ৩০০ × ৩০০ পিক্সেল (১:১ স্কয়ার আইকন)</p>
                        <div class="flex items-center gap-3">
                            <template x-if="editPreview">
                                <img :src="editPreview" class="w-12 h-12 rounded-xl object-cover border border-indigo-200 shadow-xs flex-shrink-0">
                            </template>
                            <input type="file" name="image" accept="image/*" @change="handleImageUpload($event, 'edit')"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                        <select name="status" x-model="editData.status"
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 focus:bg-white transition-colors">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="editData = null" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 bg-[#6366f1] hover:bg-[#4f46e5] text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-indigo-200 transition-colors">Update</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
