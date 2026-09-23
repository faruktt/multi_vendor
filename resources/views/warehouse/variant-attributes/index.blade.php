@extends('layouts.app')
@section('title', 'Variant Attributes — Colors & Sizes')
@section('heading', 'Variant Attributes')

@section('content')
<div x-data="{ tab: 'colors' }">

    {{-- ── Tab switcher ─────────────────────────────────────────── --}}
    <div class="flex gap-1 bg-slate-100 p-1 rounded-2xl w-fit mb-6">
        <button @click="tab = 'colors'"
                :class="tab === 'colors' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-gradient-to-br from-red-400 to-blue-400 flex-shrink-0"></span>
            Colors
            <span class="text-xs bg-slate-200 text-slate-600 rounded-full px-2 py-0.5">{{ $colors->count() }}</span>
        </button>
        <button @click="tab = 'sizes'"
                :class="tab === 'sizes' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
            <i class="fas fa-ruler-horizontal text-xs text-slate-400"></i>
            Sizes
            <span class="text-xs bg-slate-200 text-slate-600 rounded-full px-2 py-0.5">{{ $sizes->count() }}</span>
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-4 text-sm">
        <i class="fas fa-check-circle flex-shrink-0"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-2.5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">
        <i class="fas fa-triangle-exclamation flex-shrink-0"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ═══════════════════════════════════ COLORS TAB ══════════════════════════════════════ --}}
    <div x-show="tab === 'colors'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Add Color Form --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
                        <i class="fas fa-plus text-red-500 text-xs"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 text-sm">Add New Color</h3>
                </div>
                <form method="POST" action="{{ route('admin.warehouse.variant-attributes.colors.store', 'warehouse') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Color Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Red, Blue, Black..."
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 bg-slate-50 focus:bg-white transition-colors" required>
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Hex Color Code <span class="text-slate-400 font-normal">(optional)</span></label>
                        <div class="flex gap-2">
                            <input type="color" name="hex_code_picker" value="#000000"
                                   class="w-12 h-10 rounded-lg border border-slate-200 cursor-pointer p-1 bg-white"
                                   oninput="document.getElementById('hex_input').value = this.value">
                            <input type="text" name="hex_code" id="hex_input" placeholder="#FF0000"
                                   class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-red-400 bg-slate-50 focus:bg-white transition-colors">
                        </div>
                    </div>
                    <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-plus text-xs"></i> Add Color
                    </button>
                </form>
            </div>

            {{-- Colors List --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                        <i class="fas fa-palette text-slate-500 text-xs"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 text-sm">All Colors</h3>
                </div>

                @if($colors->isEmpty())
                <div class="text-center py-10 text-slate-400">
                    <i class="fas fa-palette text-3xl mb-2 block text-slate-300"></i>
                    <p class="text-sm">No colors added yet. Add your first color →</p>
                </div>
                @else
                <div class="space-y-2">
                    @foreach($colors as $color)
                    <div class="flex items-center gap-3 border border-slate-200 rounded-xl px-4 py-3 hover:border-slate-300 transition-colors group"
                         x-data="{ editing: false }">

                        {{-- Color Swatch --}}
                        <div class="w-8 h-8 rounded-lg border border-slate-200 flex-shrink-0"
                             style="background-color: {{ $color->hex_code ?? '#e2e8f0' }}"></div>

                        {{-- View mode --}}
                        <div x-show="!editing" class="flex-1 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-700 text-sm">{{ $color->name }}</p>
                                @if($color->hex_code)
                                <p class="text-xs text-slate-400 font-mono">{{ $color->hex_code }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editing = true"
                                        class="w-7 h-7 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.warehouse.variant-attributes.colors.destroy', ['warehouse', $color]) }}"
                                      onsubmit="return confirm('Delete color \'{{ $color->name }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-7 h-7 rounded-lg border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-colors">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Edit mode --}}
                        <form x-show="editing" x-cloak method="POST"
                              action="{{ route('admin.warehouse.variant-attributes.colors.update', ['warehouse', $color]) }}"
                              class="flex-1 flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="color" name="hex_code_picker" value="{{ $color->hex_code ?? '#000000' }}"
                                   class="w-9 h-9 rounded-lg border border-slate-200 cursor-pointer p-1 bg-white flex-shrink-0"
                                   oninput="this.nextElementSibling.value = this.value">
                            <input type="text" name="hex_code" value="{{ $color->hex_code }}" placeholder="#000000"
                                   class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <input type="text" name="name" value="{{ $color->name }}" required
                                   class="flex-1 border border-blue-300 rounded-lg px-3 py-1.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <button type="submit"
                                    class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                            <button type="button" @click="editing = false"
                                    class="w-8 h-8 border border-slate-200 text-slate-400 hover:bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════ SIZES TAB ═══════════════════════════════════════ --}}
    <div x-show="tab === 'sizes'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Add Size Form --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-plus text-indigo-500 text-xs"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 text-sm">Add New Size</h3>
                </div>
                <form method="POST" action="{{ route('admin.warehouse.variant-attributes.sizes.store', 'warehouse') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Size Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. S, M, L, XL, 2XL, 32..."
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-slate-50 focus:bg-white transition-colors" required>
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit"
                            class="w-full bg-indigo-500 hover:bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-plus text-xs"></i> Add Size
                    </button>
                </form>

                {{-- Quick add common sizes --}}
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-2">Quick Add Common Sizes</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Free Size'] as $s)
                        <form method="POST" action="{{ route('admin.warehouse.variant-attributes.sizes.store', 'warehouse') }}">
                            @csrf
                            <input type="hidden" name="name" value="{{ $s }}">
                            <button type="submit"
                                    class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 rounded-lg text-xs font-semibold transition-colors">
                                {{ $s }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sizes List --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                        <i class="fas fa-ruler-horizontal text-slate-500 text-xs"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 text-sm">All Sizes</h3>
                </div>

                @if($sizes->isEmpty())
                <div class="text-center py-10 text-slate-400">
                    <i class="fas fa-ruler-horizontal text-3xl mb-2 block text-slate-300"></i>
                    <p class="text-sm">No sizes added yet. Add your first size →</p>
                </div>
                @else
                <div class="grid grid-cols-2 gap-2">
                    @foreach($sizes as $size)
                    <div class="flex items-center gap-2 border border-slate-200 rounded-xl px-4 py-3 hover:border-slate-300 transition-colors group"
                         x-data="{ editing: false }">

                        {{-- View mode --}}
                        <div x-show="!editing" class="flex-1 flex items-center justify-between">
                            <p class="font-semibold text-slate-700 text-sm">{{ $size->name }}</p>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editing = true"
                                        class="w-6 h-6 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 flex items-center justify-center transition-colors">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.warehouse.variant-attributes.sizes.destroy', ['warehouse', $size]) }}"
                                      onsubmit="return confirm('Delete size \'{{ $size->name }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-6 h-6 rounded-lg border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition-colors">
                                        <i class="fas fa-trash text-[9px]"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Edit mode --}}
                        <form x-show="editing" x-cloak method="POST"
                              action="{{ route('admin.warehouse.variant-attributes.sizes.update', ['warehouse', $size]) }}"
                              class="flex-1 flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $size->name }}" required
                                   class="flex-1 border border-blue-300 rounded-lg px-3 py-1.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <button type="submit"
                                    class="w-7 h-7 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-check text-[10px]"></i>
                            </button>
                            <button type="button" @click="editing = false"
                                    class="w-7 h-7 border border-slate-200 text-slate-400 hover:bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
