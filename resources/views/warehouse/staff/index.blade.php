@extends('layouts.app')
@section('title', 'Staff Management')
@section('heading', 'Staff Management')
@php $currency = $appSettings['currency'] ?? '৳'; @endphp

@section('content')
<div x-data="staffPage()">

{{-- ══ Header bar ══════════════════════════════════════════════════ --}}
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-2.5 flex items-center gap-2">
            <i class="fas fa-users text-slate-400 text-sm"></i>
            <span class="text-[13px] font-bold text-slate-700">{{ $users->total() }}</span>
            <span class="text-[11.5px] text-slate-400">total staff</span>
        </div>
        @php
            $roleCounts = $users->getCollection()->groupBy(fn($u) => $u->getRoleNames()->first() ?? 'none')->map->count();
        @endphp
        @foreach($roleCounts as $role => $count)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-3 py-2.5 flex items-center gap-1.5">
            <span class="text-[12px] font-bold text-slate-700">{{ $count }}</span>
            <span class="text-[11px] text-slate-400 capitalize">{{ str_replace('-',' ',$role) }}</span>
        </div>
        @endforeach
    </div>
    <button @click="addOpen = true"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-blue-200 transition-colors">
        <i class="fas fa-plus"></i> Add Staff
    </button>
</div>

{{-- ══ Table ════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
            <i class="fas fa-user-gear text-blue-600 text-xs"></i>
        </div>
        <h2 class="text-[13.5px] font-bold text-slate-700">Branch Staff</h2>
    </div>

    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Staff</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Contact</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">NID Documents</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Role</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Joined</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($users as $i => $u)
            @php
                $role = $u->getRoleNames()->first() ?? '—';
                $roleColor = match(true) {
                    str_contains($role,'owner')   => ['bg-purple-100 text-purple-700 border-purple-200', 'bg-purple-500'],
                    str_contains($role,'cashier') => ['bg-blue-100 text-blue-700 border-blue-200', 'bg-blue-500'],
                    str_contains($role,'manager') => ['bg-amber-100 text-amber-700 border-amber-200', 'bg-amber-500'],
                    default                       => ['bg-slate-100 text-slate-600 border-slate-200', 'bg-slate-400'],
                };
            @endphp
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 py-3 text-[12px] text-slate-400">{{ $users->firstItem() + $i }}</td>
                <td class="px-3 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl {{ $roleColor[1] }} overflow-hidden flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0">
                            @if($u->image_url)
                                <img src="{{ $u->image_url }}" alt="{{ $u->name }}" class="w-full h-full object-cover cursor-pointer"
                                     @click="previewImage('{{ $u->image_url }}', '{{ addslashes($u->name) }} (Profile Photo)')">
                            @else
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            @endif
                        </div>
                        <span class="text-[13px] font-semibold text-slate-800">{{ $u->name }}</span>
                    </div>
                </td>
                <td class="px-3 py-3 text-[12.5px] text-slate-500 hidden md:table-cell">
                    <div>{{ $u->email }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">{{ $u->phone ?: 'No phone' }}</div>
                </td>
                {{-- NID & Documents --}}
                <td class="px-3 py-3 text-center whitespace-nowrap">
                    <div class="flex flex-col items-center gap-1 text-[10px]">
                        {{-- Staff NID --}}
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 font-semibold">NID:</span>
                            @if($u->nid_front_url)
                                <button type="button" @click="previewImage('{{ $u->nid_front_url }}', '{{ addslashes($u->name) }} - NID Front')"
                                        class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-200 hover:bg-blue-100 cursor-pointer">
                                    Front
                                </button>
                            @endif
                            @if($u->nid_back_url)
                                <button type="button" @click="previewImage('{{ $u->nid_back_url }}', '{{ addslashes($u->name) }} - NID Back')"
                                        class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-200 hover:bg-blue-100 cursor-pointer">
                                    Back
                                </button>
                            @endif
                            @if(!$u->nid_front_url && !$u->nid_back_url)
                                <span class="text-slate-300">None</span>
                            @endif
                        </div>
                        {{-- Guardian NID --}}
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 font-semibold">G-NID:</span>
                            @if($u->guardian_nid_front_url)
                                <button type="button" @click="previewImage('{{ $u->guardian_nid_front_url }}', '{{ addslashes($u->name) }} - Guardian NID Front')"
                                        class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                    Front
                                </button>
                            @endif
                            @if($u->guardian_nid_back_url)
                                <button type="button" @click="previewImage('{{ $u->guardian_nid_back_url }}', '{{ addslashes($u->name) }} - Guardian NID Back')"
                                        class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 font-bold border border-teal-200 hover:bg-teal-100 cursor-pointer">
                                    Back
                                </button>
                            @endif
                            @if(!$u->guardian_nid_front_url && !$u->guardian_nid_back_url)
                                <span class="text-slate-300">None</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="border {{ $roleColor[0] }} text-[10.5px] font-semibold px-2.5 py-0.5 rounded-lg capitalize">
                        {{ str_replace('-',' ',$role) }}
                    </span>
                </td>
                <td class="px-3 py-3 text-center text-[11.5px] text-slate-400 hidden lg:table-cell">
                    {{ $u->created_at->format('d M Y') }}
                </td>
                <td class="px-3 py-3">
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button" @click="openEdit({{ $u->load('roles')->toJson() }})"
                                class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-pen text-[10px]"></i>
                        </button>
                        <button type="button" @click="openDelete({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ route('admin.warehouse.staff.destroy', $u) }}')"
                                class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 flex items-center justify-center transition-colors">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-gear text-slate-400 text-2xl"></i>
                    </div>
                    <p class="text-slate-400 text-sm">No staff yet.</p>
                    <button @click="addOpen = true" class="mt-3 text-blue-600 text-sm hover:underline">Add first staff member →</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">{{ $users->links() }}</div>
    @endif
</div>

{{-- ══ ADD MODAL ════════════════════════════════════════════════════ --}}
<div x-show="addOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="addOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.outside="addOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-plus text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Add Staff Member</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $branch->name }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.warehouse.staff.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Staff member name"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="staff@example.com"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" placeholder="01XXXXXXXXX"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6" placeholder="Min 6 chars"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                        <option value="">Select role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucwords(str_replace('-',' ',$role->name)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Profile Photo --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Profile Photo</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
            </div>

            {{-- Own NID --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NID (Front)</label>
                    <input type="file" name="nid_front" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NID (Back)</label>
                    <input type="file" name="nid_back" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
            </div>

            {{-- Guardian NID --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Guardian NID (Front)</label>
                    <input type="file" name="guardian_nid_front" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Guardian NID (Back)</label>
                    <input type="file" name="guardian_nid_back" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="addOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-sm font-bold">Add Staff</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ EDIT MODAL ═══════════════════════════════════════════════════ --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.outside="editOpen = false">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-pen text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Edit Staff</p>
                <p class="text-xs text-slate-400 mt-0.5" x-text="editName"></p>
            </div>
        </div>
        <form :action="editAction" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Full Name</label>
                <input type="text" name="name" x-model="editName" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                    <input type="email" name="email" x-model="editEmail" required
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" x-model="editPhone" placeholder="01XXXXXXXXX"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">New Password</label>
                    <input type="password" name="password" minlength="6" placeholder="Leave blank to keep"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Role</label>
                    <select name="role" x-model="editRole" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucwords(str_replace('-',' ',$role->name)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Update Profile Photo --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Profile Photo (Leave blank to keep)</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
            </div>

            {{-- Update Own NID --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NID Front (Optional)</label>
                    <input type="file" name="nid_front" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NID Back (Optional)</label>
                    <input type="file" name="nid_back" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
            </div>

            {{-- Update Guardian NID --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">G-NID Front (Optional)</label>
                    <input type="file" name="guardian_nid_front" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">G-NID Back (Optional)</label>
                    <input type="file" name="guardian_nid_back" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-slate-50">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="editOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-sm font-bold">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DELETE MODAL ════════════════════════════════════════════════ --}}
<div x-show="delModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="delModal = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="delModal = false">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-minus text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800">Remove Staff?</p>
                <p class="text-sm text-slate-500 mt-0.5">This action cannot be undone.</p>
            </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm font-bold text-red-700" x-text="delName"></p>
            <p class="text-xs text-red-400 mt-0.5">Will lose access to this branch.</p>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm hover:bg-slate-50">Cancel</button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-sm font-bold">Remove</button>
            </form>
        </div>
    </div>
</div>

{{-- ── Image Lightbox Preview Modal ── --}}
<div x-show="previewModalOpen" x-cloak
     class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="previewModalOpen = false">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col" @click.outside="previewModalOpen = false">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <span class="text-xs font-bold text-slate-800 truncate" x-text="previewTitle"></span>
            <button type="button" @click="previewModalOpen = false" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 flex items-center justify-center bg-slate-900/5 max-h-[75vh] overflow-auto">
            <img :src="previewSrc" :alt="previewTitle" class="max-w-full max-h-[70vh] rounded-xl object-contain shadow-md">
        </div>
        <div class="p-3 bg-slate-50 border-t border-slate-100 flex justify-between items-center text-xs">
            <a :href="previewSrc" target="_blank" class="text-blue-600 hover:underline font-bold flex items-center gap-1">
                <i class="fas fa-external-link-alt text-[10px]"></i>
                <span>View Original Size</span>
            </a>
            <button type="button" @click="previewModalOpen = false" class="px-4 py-1.5 rounded-xl bg-slate-200 text-slate-700 font-bold hover:bg-slate-300">
                Close
            </button>
        </div>
    </div>
</div>

</div>{{-- x-data --}}

@push('scripts')
<script>
function staffPage() {
    return {
        addOpen: false,
        editOpen: false, editAction: '', editName: '', editEmail: '', editPhone: '', editRole: '',
        delModal: false, delName: '', delUrl: '',
        previewModalOpen: false,
        previewSrc: '',
        previewTitle: '',

        previewImage(src, title) {
            this.previewSrc = src;
            this.previewTitle = title;
            this.previewModalOpen = true;
        },

        openEdit(u) {
            this.editAction = `{{ route('admin.warehouse.staff.update', ':id') }}`.replace(':id', u.id);
            this.editName   = u.name  || '';
            this.editEmail  = u.email || '';
            this.editPhone  = u.phone || '';
            this.editRole   = u.roles?.[0]?.name || '';
            this.editOpen   = true;
        },
        openDelete(id, name, url) {
            this.delName  = name;
            this.delUrl   = url;
            this.delModal = true;
        },
    };
}
</script>
@endpush
@endsection
