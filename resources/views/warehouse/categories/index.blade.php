@extends('layouts.app')
@section('title', 'Categories')
@section('heading', 'Category Management')

@section('content')
<div x-data="{ showAdd: false, editId: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-gray-500 text-sm">Total {{ $categories->count() }} categories</p>
            <p class="text-[11.5px] text-slate-400 mt-0.5">Managed from here only — renaming or deleting one applies everywhere it's used.</p>
        </div>
        <button @click="showAdd = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>

    {{-- Add Modal --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.outside="showAdd = false" class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">Add New Category</h3>
            <form method="POST" action="{{ route('admin.warehouse.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Category name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Save</button>
                    <button type="button" @click="showAdd = false" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="editId !== null" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div @click.outside="editId = null" class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">Edit Category</h3>
            <p class="text-[12px] text-slate-400 -mt-2 mb-4">Renaming applies to every branch currently using this category.</p>
            <template x-for="cat in {{ $categories->toJson() }}" :key="cat.id">
                <form x-show="editId === cat.id" :action="'{{ route('admin.warehouse.categories.store') }}/' + cat.id"
                      method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" :value="cat.name" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Image</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Update</button>
                        <button type="button" @click="editId = null" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Cancel</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-center text-gray-600 font-medium w-10">#</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium hidden md:table-cell">Image</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Category</th>
                    <th class="px-4 py-3 text-left text-gray-600 font-medium">Used At</th>
                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Products</th>
                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Homepage</th>
                    <th class="px-4 py-3 text-center text-gray-600 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $index => $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-3 text-center text-gray-400 text-xs">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                            @if($cat->image_url)
                                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-tag text-gray-400 text-sm"></i>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $cat->name }}
                        @if($cat->parent_id)
                        <span class="ml-1 text-[10px] font-normal text-slate-400">(sub)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($cat->branch_names as $bn)
                            <span class="bg-orange-50 text-orange-700 border border-orange-100 px-2 py-0.5 rounded-full text-[10.5px] font-medium">{{ $bn }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs">{{ $cat->products_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($cat->website_category_id && !$cat->parent_id)
                        <div class="flex items-center justify-center gap-2">
                            <form method="POST" action="{{ route('admin.warehouse.categories.toggle-homepage', $cat->website_category_id) }}">
                                @csrf
                                <button type="submit"
                                        title="{{ $cat->show_on_homepage ? 'Shown on homepage — click to hide' : 'Hidden from homepage — click to show' }}"
                                        class="text-[11px] font-semibold px-2.5 py-1 rounded-lg transition-colors {{ $cat->show_on_homepage ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    <i class="fas {{ $cat->show_on_homepage ? 'fa-eye' : 'fa-eye-slash' }} text-[10px] mr-1"></i>
                                    {{ $cat->show_on_homepage ? 'Shown' : 'Hidden' }}
                                </button>
                            </form>
                            <div class="flex flex-col gap-0.5">
                                <form method="POST" action="{{ route('admin.warehouse.categories.reorder-homepage', $cat->website_category_id) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="w-5 h-5 rounded border border-gray-200 text-gray-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center">
                                        <i class="fas fa-chevron-up text-[9px]"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.warehouse.categories.reorder-homepage', $cat->website_category_id) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="w-5 h-5 rounded border border-gray-200 text-gray-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center">
                                        <i class="fas fa-chevron-down text-[9px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @else
                        <span class="text-gray-300 text-xs block text-center">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button @click="editId = {{ $cat->id }}"
                                    class="text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-lg text-xs border border-blue-200">Edit</button>
                            <form method="POST" action="{{ route('admin.warehouse.categories.destroy', $cat->id) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($cat->name) }}\'? This removes it from all {{ $cat->branch_names->count() }} branch(es) using it.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg text-xs border border-red-200">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
                    <i class="fas fa-tags text-4xl mb-3 block"></i><p>No categories found</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
