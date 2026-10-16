@extends('layouts.app')
@section('title', 'Categories')
@section('heading', 'Category Management')

@section('content')
<div x-data="{
    showAdd: false,
    editId: null,
    previewImg: null,
    editPreviewImg: null,
    openAdd() {
        this.previewImg = null;
        this.showAdd = true;
    },
    openEdit(id, imgUrl) {
        this.editId = id;
        this.editPreviewImg = imgUrl || null;
    },
    handleImageUpload(e, target) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                if (target === 'add') this.previewImg = ev.target.result;
                if (target === 'edit') this.editPreviewImg = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-800">Categories</h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $categories->count() }} Total
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Manage parent categories across all branches and website.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.warehouse.subcategories.index', 'warehouse') }}"
               class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                <i class="fas fa-sitemap text-xs"></i>
                <span>Subcategories</span>
            </a>
            <button @click="openAdd()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm shadow-blue-200 transition-all hover:scale-105 cursor-pointer">
                <i class="fas fa-plus text-xs"></i>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    {{-- ══ ADD MODAL ══ --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="showAdd = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-tags text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Add New Category</h3>
                </div>
                <button type="button" @click="showAdd = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.warehouse.categories.store', 'warehouse') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors"
                           placeholder="e.g. Mens Shoes, Electronics, etc.">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Category Banner Image <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <span class="text-[11px] font-black text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md">
                            1400 × 400 px
                        </span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-blue-50/60 border border-blue-100 mb-2 text-[11px] text-slate-600 leading-relaxed">
                        <strong class="text-blue-900 flex items-center gap-1.5 mb-0.5">
                            <i class="fas fa-circle-info text-blue-600"></i> অরিজিনাল ব্যানার সাইজ: 1400 × 400 পিক্সেল (রেশিও ৩.৫:১)
                        </strong>
                        ফ্রন্টএন্ডে ক্যাটাগরি ব্যানারে এই সাইজের ছবি দিলে কোনো অংশ কাটা/ক্রপ ছাড়া সম্পূর্ণ নিখুঁতভাবে প্রদর্শিত হবে।
                    </div>
                    <div class="flex items-center gap-3">
                        <template x-if="previewImg">
                            <div class="w-28 h-10 rounded-lg overflow-hidden border border-blue-200 bg-slate-100 flex-shrink-0 shadow-2xs">
                                <img :src="previewImg" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <input type="file" name="image" accept="image/*" @change="handleImageUpload($event, 'add')"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                </div>
                <div class="flex gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAdd = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-blue-200 transition-colors">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ EDIT MODAL ══ --}}
    <div x-show="editId !== null" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="editId = null" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fas fa-pen text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Edit Category</h3>
                </div>
                <button type="button" @click="editId = null" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <template x-for="cat in {{ $categories->toJson() }}" :key="cat.id">
                <form x-show="editId === cat.id" :action="'{{ route('admin.warehouse.categories.store', 'warehouse') }}/' + cat.id"
                      method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" :value="cat.name" required
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-slate-50 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">
                                New Banner Image <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <span class="text-[11px] font-black text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                                1400 × 400 px
                            </span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50/60 border border-amber-100 mb-2 text-[11px] text-slate-600 leading-relaxed">
                            <strong class="text-amber-900 flex items-center gap-1.5 mb-0.5">
                                <i class="fas fa-circle-info text-amber-600"></i> অরিজিনাল ব্যানার সাইজ: 1400 × 400 পিক্সেল (রেশিও ৩.৫:১)
                            </strong>
                            ফ্রন্টএন্ডে ক্যাটাগরি ব্যানারে এই সাইজের ছবি দিলে কোনো অংশ কাটা/ক্রপ ছাড়া সম্পূর্ণ নিখুঁতভাবে প্রদর্শিত হবে।
                        </div>
                        <div class="flex items-center gap-3">
                            <template x-if="editPreviewImg || cat.image_url">
                                <div class="w-28 h-10 rounded-lg overflow-hidden border border-amber-200 bg-slate-100 flex-shrink-0 shadow-2xs">
                                    <img :src="editPreviewImg || cat.image_url" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <input type="file" name="image" accept="image/*" @change="handleImageUpload($event, 'edit')"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="editId = null" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-amber-200 transition-colors">Update</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ══ CATEGORIES TABLE ══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-3 py-3.5 text-center text-slate-400 text-xs font-bold uppercase w-12">#</th>
                    <th class="px-4 py-3.5 text-left text-slate-400 text-xs font-bold uppercase w-16">Image</th>
                    <th class="px-4 py-3.5 text-left text-slate-400 text-xs font-bold uppercase">Name</th>
                    <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase">Subcategories</th>
                    <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase">Products</th>
                    <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase">Homepage</th>
                    <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase w-32">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($categories as $index => $cat)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-3 py-3.5 text-center text-slate-400 text-xs font-medium">{{ $index + 1 }}</td>
                    <td class="px-4 py-3.5">
                        <div class="w-14 h-8 rounded-lg bg-slate-100 border border-slate-200/60 overflow-hidden flex items-center justify-center">
                            @if($cat->image_url)
                                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-image text-slate-300 text-xs"></i>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="font-bold text-slate-800 text-[13.5px]">{{ $cat->name }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($cat->subcategories_count > 0)
                            <a href="{{ route('admin.warehouse.subcategories.index', ['warehouse', 'category' => $cat->name]) }}"
                               class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/60 px-2 py-0.5 rounded-full transition-colors"
                               title="Click to view subcategories">
                                <i class="fas fa-sitemap text-[9px]"></i>
                                {{ $cat->subcategories_count }} Subcategories
                            </a>
                        @else
                            <a href="{{ route('admin.warehouse.subcategories.index', 'warehouse') }}"
                               class="inline-flex items-center gap-1 text-[10.5px] text-slate-400 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-plus text-[9px]"></i> Add Sub
                            </a>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($cat->products_count > 0)
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                {{ $cat->products_count }}
                            </span>
                        @else
                            <span class="text-xs text-slate-300 font-mono">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($cat->website_category_id)
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" action="{{ route('admin.warehouse.categories.toggle-homepage', ['warehouse', $cat->website_category_id]) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 rounded-full text-[10.5px] font-semibold transition-colors cursor-pointer {{ $cat->show_on_homepage ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        {{ $cat->show_on_homepage ? '✓ Visible' : 'Hidden' }}
                                    </button>
                                </form>
                                @if($cat->show_on_homepage)
                                    <form method="POST" action="{{ route('admin.warehouse.categories.reorder-homepage', ['warehouse', $cat->website_category_id]) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" title="Move up" class="text-slate-400 hover:text-slate-700 p-1 text-[10px] cursor-pointer">▲</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.warehouse.categories.reorder-homepage', ['warehouse', $cat->website_category_id]) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" title="Move down" class="text-slate-400 hover:text-slate-700 p-1 text-[10px] cursor-pointer">▼</button>
                                    </form>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-slate-300 font-mono">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <button @click="openEdit({{ $cat->id }}, '{{ $cat->image_url }}')"
                                    class="w-7 h-7 rounded-md bg-[#6366f1] hover:bg-[#4f46e5] text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                    title="Edit">
                                <i class="fas fa-pen text-[10px]"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.warehouse.categories.destroy', ['warehouse', $cat->id]) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($cat->name) }}\'? This will also delete any subcategories under it.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-7 h-7 rounded-md bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-2xs transition-colors cursor-pointer"
                                        title="Delete">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                            <i class="fas fa-tags text-xl"></i>
                        </div>
                        <p class="font-medium">No categories found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
