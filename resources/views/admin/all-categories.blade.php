@extends('layouts.app')
@section('title', 'All Categories')
@section('heading', 'All Categories')

@section('content')
<div x-data="categoriesAdminPage()">

    {{-- ══ TOP BAR & FILTERS ══ --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-slate-800">All Categories</h2>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                    {{ count($categories) }} Categories
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Central category catalog used across all branches and website storefront.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.all-subcategories') }}"
               class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                <i class="fas fa-sitemap text-xs"></i>
                <span>Subcategories</span>
            </a>
            <button type="button" @click="openAdd()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm shadow-blue-200 transition-all hover:scale-105 cursor-pointer">
                <i class="fas fa-plus text-xs"></i>
                <span>Add Category</span>
            </button>
        </div>
    </div>

    {{-- ══ FILTER FORM ══ --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
        <div class="flex flex-wrap gap-2.5 items-center">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search category..."
                       class="border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 bg-slate-50 focus:bg-white transition-colors">
            </div>

            <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-colors">Filter</button>
            <a href="{{ route('admin.all-categories') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-xs transition-colors">Reset</a>

            <div class="ml-auto text-[11.5px] text-slate-400">
                <span>Showing <strong>{{ count($categories) }}</strong> unique categories</span>
            </div>
        </div>
    </form>

    {{-- ══ CATEGORIES TABLE ══ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3.5 text-center text-slate-400 text-xs font-bold uppercase w-12">#</th>
                        <th class="px-3 py-3.5 text-center text-slate-400 text-xs font-bold uppercase w-28">Order</th>
                        <th class="px-4 py-3.5 text-left text-slate-400 text-xs font-bold uppercase w-16">Image</th>
                        <th class="px-4 py-3.5 text-left text-slate-400 text-xs font-bold uppercase">Category Name</th>
                        <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase">Subcategories</th>
                        <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase">Products</th>
                        <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase w-28">Status</th>
                        <th class="px-4 py-3.5 text-center text-slate-400 text-xs font-bold uppercase w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($categories as $index => $cat)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        {{-- Row Index --}}
                        <td class="px-3 py-3.5 text-center text-slate-400 text-xs font-medium">{{ $index + 1 }}</td>

                        {{-- Order / Sort Order with Up/Down buttons --}}
                        <td class="px-3 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" action="{{ route('admin.categories.central-reorder', $cat->id) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" title="Move Up" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-blue-700 flex items-center justify-center text-[10px] transition-colors cursor-pointer" {{ $loop->first ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                </form>

                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-xs min-w-[28px] text-center" title="Sort Order: {{ $cat->sort_order }}">
                                    {{ $cat->sort_order }}
                                </span>

                                <form method="POST" action="{{ route('admin.categories.central-reorder', $cat->id) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" title="Move Down" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-blue-700 flex items-center justify-center text-[10px] transition-colors cursor-pointer" {{ $loop->last ? 'disabled opacity-30 cursor-not-allowed' : '' }}>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </form>
                            </div>
                        </td>

                        {{-- Image --}}
                        <td class="px-4 py-3.5">
                            <div class="w-12 h-9 rounded-lg bg-slate-100 border border-slate-200/60 overflow-hidden flex items-center justify-center">
                                @if($cat->image_url)
                                    <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-tag text-slate-300 text-xs"></i>
                                @endif
                            </div>
                        </td>

                        {{-- Name --}}
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800 text-[13.5px]">{{ $cat->name }}</span>
                                @if(!$cat->show_on_homepage)
                                <span class="text-[9.5px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200 font-medium" title="Hidden on homepage shelf">
                                    Shelf Off
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- Subcategories --}}
                        <td class="px-4 py-3.5 text-center">
                            @if($cat->subcategories_count > 0)
                                <a href="{{ route('admin.all-subcategories') }}?category={{ urlencode($cat->name) }}"
                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/60 px-2.5 py-0.5 rounded-full transition-colors"
                                   title="View subcategories">
                                    <i class="fas fa-sitemap text-[9px]"></i>
                                    {{ $cat->subcategories_count }} Subs
                                </a>
                            @else
                                <span class="text-xs text-slate-300 font-mono">—</span>
                            @endif
                        </td>

                        {{-- Products --}}
                        <td class="px-4 py-3.5 text-center">
                            @if($cat->products_count > 0)
                                <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                    {{ $cat->products_count }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300 font-mono">0</span>
                            @endif
                        </td>

                        {{-- Status (Active / Inactive Toggle) --}}
                        <td class="px-4 py-3.5 text-center">
                            <form method="POST" action="{{ route('admin.categories.central-toggle-status', $cat->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        title="Click to toggle Active / Inactive"
                                        class="px-3 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer inline-flex items-center gap-1 shadow-2xs {{ $cat->status === 'active' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $cat->status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>{{ $cat->status === 'active' ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </form>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Edit Button --}}
                                <button type="button"
                                        @click="openEdit({{ json_encode(['id' => $cat->id, 'name' => $cat->name, 'image_url' => $cat->image_url, 'sort_order' => $cat->sort_order, 'status' => $cat->status]) }})"
                                        class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors cursor-pointer"
                                        title="Edit Category">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </button>

                                {{-- Delete Button --}}
                                <button type="button"
                                        @click="openDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                        class="w-7 h-7 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-600 flex items-center justify-center transition-colors cursor-pointer"
                                        title="Delete Category">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
                                <i class="fas fa-tags text-xl"></i>
                            </div>
                            <p class="font-medium text-sm">No categories found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
                <button type="button" @click="showAdd = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.categories.central-store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors"
                           placeholder="e.g. Fashion, Electronics, etc.">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Sort Order (Position)
                        </label>
                        <input type="number" name="sort_order" value="0" min="0"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white transition-colors"
                               placeholder="e.g. 0, 1, 2">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Smaller number shows first</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50 focus:bg-white">
                            <option value="active">Active (Visible)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Inactive hides from frontend</span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Category Banner Image <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <span class="text-[10.5px] font-black text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md">
                            1400 × 400 px
                        </span>
                    </div>
                    <div class="flex items-center gap-3 mt-2">
                        <template x-if="addPreviewImg">
                            <div class="w-24 h-10 rounded-lg overflow-hidden border border-blue-200 bg-slate-100 flex-shrink-0 shadow-2xs">
                                <img :src="addPreviewImg" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <input type="file" name="image" accept="image/*" @change="handleImageUpload($event, 'add')"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                </div>

                <div class="flex gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showAdd = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-blue-200 transition-colors cursor-pointer">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ EDIT MODAL ══ --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="showEdit = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fas fa-pen text-sm"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Edit Category</h3>
                </div>
                <button type="button" @click="showEdit = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form :action="editUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="editName" required
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-slate-50 focus:bg-white transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Sort Order (Position)
                        </label>
                        <input type="number" name="sort_order" x-model="editSortOrder" min="0"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-slate-50 focus:bg-white transition-colors">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Smaller number shows first</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" x-model="editStatus" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-slate-50 focus:bg-white">
                            <option value="active">Active (Visible)</option>
                            <option value="inactive">Inactive (Hidden)</option>
                        </select>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Inactive hides from frontend</span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Banner Image <span class="text-slate-400 font-normal">(Optional)</span>
                        </label>
                        <span class="text-[10.5px] font-black text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md">
                            1400 × 400 px
                        </span>
                    </div>
                    <div class="flex items-center gap-3 mt-2">
                        <template x-if="editPreviewImg">
                            <div class="w-24 h-10 rounded-lg overflow-hidden border border-amber-200 bg-slate-100 flex-shrink-0 shadow-2xs">
                                <img :src="editPreviewImg" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <input type="file" name="image" accept="image/*" @change="handleImageUpload($event, 'edit')"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                    </div>
                </div>

                <div class="flex gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEdit = false" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                    <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-amber-200 transition-colors cursor-pointer">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══ DELETE MODAL ══ --}}
    <div x-show="showDelete" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div @click.outside="showDelete = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="w-14 h-14 rounded-2xl bg-rose-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash text-rose-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-bold text-slate-800 mb-1">Delete Category?</h3>
            <p class="text-[12.5px] text-slate-500 mb-5 leading-relaxed">
                "<span class="font-bold text-slate-700" x-text="deleteName"></span>" will be removed from all branches and website. Any subcategories under it will also be deleted.
            </p>
            <form :action="deleteUrl" method="POST" class="flex gap-3">
                @csrf @method('DELETE')
                <button type="button" @click="showDelete = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2.5 rounded-xl text-xs font-bold shadow-sm shadow-rose-200 transition-colors cursor-pointer">Yes, Delete</button>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function categoriesAdminPage() {
    return {
        showAdd: false,
        addPreviewImg: null,

        showEdit: false,
        editUrl: '',
        editName: '',
        editSortOrder: 0,
        editStatus: 'active',
        editPreviewImg: null,

        showDelete: false,
        deleteUrl: '',
        deleteName: '',

        openAdd() {
            this.addPreviewImg = null;
            this.showAdd = true;
        },

        openEdit(cat) {
            this.editName       = cat.name || '';
            this.editSortOrder  = cat.sort_order ?? 0;
            this.editStatus     = cat.status || 'active';
            this.editPreviewImg = cat.image_url || null;
            this.editUrl        = '{{ url("admin/all-categories") }}/' + cat.id;
            this.showEdit       = true;
        },

        openDelete(id, name) {
            this.deleteName = name;
            this.deleteUrl  = '{{ url("admin/all-categories") }}/' + id;
            this.showDelete = true;
        },

        handleImageUpload(e, target) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    if (target === 'add') this.addPreviewImg = ev.target.result;
                    if (target === 'edit') this.editPreviewImg = ev.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    };
}
</script>
@endpush
