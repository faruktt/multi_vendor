{{-- Expects: $group ('hero'|'mid'|'promo1'|'promo2'), $list (collection), $heading, $emptyText --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
            <i class="fas fa-list text-slate-500 text-sm"></i>
        </div>
        <div>
            <h2 class="text-[14px] font-semibold text-slate-800">{{ $heading }}</h2>
            <p class="text-[11.5px] text-slate-400">{{ $list->count() }} {{ Str::plural('banner', $list->count()) }} — use the arrows to reorder</p>
        </div>
    </div>

    @if($list->isEmpty())
        <div class="p-10 text-center">
            <i class="fas fa-image text-slate-200 text-4xl mb-3"></i>
            <p class="text-[13px] text-slate-400">{{ $emptyText }}</p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($list as $index => $banner)
            <div class="flex items-center gap-4 px-6 py-4">
                <div class="flex flex-col gap-1 flex-shrink-0">
                    <button type="button" onclick="moveBanner('{{ $group }}', {{ $banner->id }}, -1)"
                            class="w-6 h-6 rounded-md border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}">
                        <i class="fas fa-chevron-up text-[10px]"></i>
                    </button>
                    <button type="button" onclick="moveBanner('{{ $group }}', {{ $banner->id }}, 1)"
                            class="w-6 h-6 rounded-md border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-300 flex items-center justify-center {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                </div>

                <div class="w-32 h-16 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 relative flex items-center justify-center">
                    @php
                        $hasFile = !empty($banner->image) && file_exists(public_path('uploads/' . $banner->image));
                    @endphp
                    @if($hasFile)
                        <img src="{{ $banner->image_url }}" class="w-full h-full object-cover" alt="{{ $banner->title ?? 'Banner ' . ($index + 1) }}"
                             onerror="this.parentElement.innerHTML='<div class=\'flex flex-col items-center justify-center text-slate-400 text-[10px] p-1 text-center\'><i class=\'fas fa-triangle-exclamation text-amber-500 text-xs mb-0.5\'></i><span>File error</span></div>';">
                    @else
                        <div class="flex flex-col items-center justify-center text-slate-400 text-[10px] p-1 text-center">
                            <i class="fas fa-triangle-exclamation text-amber-500 text-xs mb-0.5"></i>
                            <span>Image missing</span>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.banners.update', $banner) }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ old('title', $banner->title) }}" placeholder="Title (admin-only) — Banner {{ $index + 1 }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                    <div class="flex gap-2">
                        <input type="text" name="link" value="{{ old('link', $banner->link) }}" placeholder="Link URL — e.g. /shop"
                               class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        <button type="submit" class="flex-shrink-0 text-[11.5px] font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                            Save
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.banners.toggle', $banner) }}">
                    @csrf
                    <button type="submit"
                            class="text-[12px] font-semibold px-3 py-1.5 rounded-lg transition-colors {{ $banner->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                        <i class="fas {{ $banner->is_active ? 'fa-eye' : 'fa-eye-slash' }} text-[11px] mr-1"></i>
                        {{ $banner->is_active ? 'Active' : 'Hidden' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete this banner?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-9 h-9 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-colors">
                        <i class="fas fa-trash text-[13px]"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    @endif
</div>
