@extends('layouts.app')
@section('title','All Categories')
@section('heading','All Categories')
@php
    $colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
@endphp

@section('content')
<div x-data="categoriesAdminPage()">

{{-- ══ BRANCH SUMMARY STRIP ══ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-4 overflow-x-auto">
    <p class="text-[10.5px] font-semibold text-slate-400 uppercase tracking-wide mb-3">Branch Breakdown</p>
    <div class="flex gap-3 min-w-max">
        @foreach($branchSummary as $idx => $s)
        @php $c = $colors[$idx % count($colors)]; @endphp
        <a href="{{ route('admin.all-categories') }}?vendor_id={{ $s['vendor']->id }}"
           class="flex items-center gap-2.5 border border-slate-100 rounded-xl px-3.5 py-2.5 min-w-[170px] hover:border-blue-200 hover:bg-blue-50/30 transition-colors group">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
            <div>
                <p class="text-[11.5px] font-semibold text-slate-700 truncate max-w-[110px] group-hover:text-blue-700">{{ $s['vendor']->name }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5">
                    <span class="font-bold text-slate-600">{{ $s['count'] }}</span> categories
                    &nbsp;·&nbsp;<span class="font-bold text-blue-600">{{ $s['products'] }}</span> products
                </p>
            </div>
        </a>
        @endforeach
    </div>
</div>

{{-- ══ FILTERS ══ --}}
<form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Category name..."
               class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
        <select name="vendor_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Branches</option>
            @foreach($vendors as $v)
            <option value="{{ $v->id }}" {{ request('vendor_id')==$v->id ? 'selected':'' }}>{{ $v->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Filter</button>
        <a href="{{ route('admin.all-categories') }}" class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-sm transition-colors">Reset</a>
        <div class="ml-auto flex items-center gap-3">
            <span class="text-[12px] text-slate-400 hidden sm:inline">{{ $categories->total() }} categories</span>
            <a href="{{ route('admin.warehouse.categories.index', 'warehouse') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm shadow-blue-200 transition-colors">
                <i class="fas fa-plus text-xs"></i> Add Category
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
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase">Branch</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Products</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($categories as $i => $cat)
                @php
                    $catColors = ['bg-blue-100 text-blue-700','bg-violet-100 text-violet-700','bg-emerald-100 text-emerald-700',
                                  'bg-amber-100 text-amber-700','bg-rose-100 text-rose-700','bg-cyan-100 text-cyan-700'];
                    $cc = $catColors[$loop->index % count($catColors)];
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-400 text-[11.5px]">{{ $categories->firstItem() + $i }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            @if($cat->image)
                            <img src="{{ Storage::disk('uploads')->url($cat->image) }}"
                                 class="w-8 h-8 rounded-lg object-cover flex-shrink-0" alt="">
                            @else
                            <div class="w-8 h-8 rounded-lg {{ $cc }} flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tag text-[11px]"></i>
                            </div>
                            @endif
                            <p class="font-semibold text-slate-800 text-[12.5px]">{{ $cat->name }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-[10.5px] font-semibold px-2 py-0.5 rounded-full">
                            {{ $cat->vendor->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-slate-700 text-[13px]">{{ $cat->products_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Edit --}}
                            <button @click="openEdit({{ json_encode(['id'=>$cat->id,'vendor_id'=>$cat->vendor_id,'name'=>$cat->name]) }})"
                                    class="w-7 h-7 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-700 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-[11px]"></i>
                            </button>
                            {{-- Delete --}}
                            <button @click="openDelete({{ $cat->id }}, {{ $cat->vendor_id }}, '{{ addslashes($cat->name) }}')"
                                    class="w-7 h-7 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center transition-colors" title="Delete">
                                <i class="fas fa-trash text-[11px]"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-16 text-center">
                        <i class="fas fa-tag text-slate-200 text-4xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm">No categories found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $categories->links() }}</div>
    @endif
</div>

{{-- ══ EDIT MODAL ══ --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.outside="editOpen = false">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-tag text-amber-600 text-sm"></i>
            </div>
            <h3 class="text-[14px] font-bold text-slate-800">Edit Category</h3>
        </div>
        <form :action="editUrl" method="POST" class="p-5 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="editName" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div class="flex gap-3">
                <button type="button" @click="editOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DELETE MODAL ══ --}}
<div x-show="deleteOpen" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="deleteOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="deleteOpen = false">
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash text-red-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-bold text-slate-800 mb-1">Delete Category?</h3>
            <p class="text-[12.5px] text-slate-500 mb-5">
                <span class="font-semibold text-slate-700" x-text="deleteName"></span> will be deleted. Products in this category won't be deleted.
            </p>
        </div>
        <form :action="deleteUrl" method="POST" class="flex gap-3">
            @csrf @method('DELETE')
            <button type="button" @click="deleteOpen = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors">Delete</button>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
function categoriesAdminPage() {
    return {
        editOpen: false, editUrl: '', editName: '',
        deleteOpen: false, deleteName: '', deleteUrl: '',
        openEdit(cat) {
            this.editName = cat.name || '';
            this.editUrl  = `/branch/${cat.vendor_id}/categories/${cat.id}`;
            this.editOpen = true;
        },
        openDelete(id, vendor, name) {
            this.deleteName = name;
            this.deleteUrl  = `/branch/${vendor}/categories/${id}`;
            this.deleteOpen = true;
        }
    };
}
</script>
@endpush
