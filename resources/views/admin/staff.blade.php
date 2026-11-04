@extends('layouts.app')
@section('title', 'All Staff')
@section('heading', 'All Staff')

@section('content')
<div x-data="adminStaffPage()">

{{-- ══ Flash Alerts ═══════════════════════════════════════════════════ --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-semibold shadow-xs">
    <i class="fas fa-circle-check text-emerald-600 text-sm"></i>
    <span>{{ session('success') }}</span>
    <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-700"><i class="fas fa-xmark"></i></button>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" x-show="show"
     class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-xs font-semibold shadow-xs">
    <i class="fas fa-triangle-exclamation text-red-600 text-sm"></i>
    <span>{{ session('error') }}</span>
    <button @click="show = false" class="ml-auto text-red-400 hover:text-red-700"><i class="fas fa-xmark"></i></button>
</div>
@endif

{{-- ══ Header ═══════════════════════════════════════════════════════ --}}
<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-2.5 flex items-center gap-2">
            <i class="fas fa-users text-slate-400 text-sm"></i>
            <span class="text-[13px] font-bold text-slate-700">{{ $users->total() }}</span>
            <span class="text-[11.5px] text-slate-400">staff across all branches</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-2.5 flex items-center gap-2">
            <i class="fas fa-store text-slate-400 text-sm"></i>
            <span class="text-[13px] font-bold text-slate-700">{{ $branches->count() }}</span>
            <span class="text-[11.5px] text-slate-400">branches</span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.roles-permissions') }}"
           class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-3.5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs transition-colors">
            <i class="fas fa-shield-halved text-indigo-600"></i> Roles &amp; Permissions Matrix
        </a>
        <button @click="addOpen = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-blue-200 transition-colors">
            <i class="fas fa-plus"></i> Add Staff
        </button>
    </div>
</div>

{{-- ══ Filter by branch ════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.staff.index') }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 transition-all
              {{ !request('branch') ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-200 text-slate-500 hover:border-blue-400 hover:text-blue-600 bg-white' }}">
        All Branches
    </a>
    @foreach($branches as $b)
    <a href="{{ route('admin.staff.index', ['branch' => $b->id]) }}"
       class="px-3 py-1.5 rounded-xl text-xs font-semibold border-2 transition-all
              {{ request('branch') == $b->id ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-200 text-slate-500 hover:border-blue-400 hover:text-blue-600 bg-white' }}">
        {{ $b->name }}
    </a>
    @endforeach
</div>

{{-- ══ Table ════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
            <i class="fas fa-user-gear text-blue-600 text-xs"></i>
        </div>
        <h2 class="text-[13.5px] font-bold text-slate-700">Staff Members &amp; Permissions</h2>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide w-8">#</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Staff</th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Contact</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">NID Documents</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Branch</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Role &amp; Permissions</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Joined</th>
                <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($users as $i => $u)
            @php
                $role = $u->getRoleNames()->first() ?? '—';
                $roleColor = match(true) {
                    str_contains($role,'owner')     => ['bg-purple-100 text-purple-700 border-purple-200','bg-purple-500'],
                    str_contains($role,'cashier')   => ['bg-blue-100 text-blue-700 border-blue-200','bg-blue-500'],
                    str_contains($role,'manager')   => ['bg-amber-100 text-amber-700 border-amber-200','bg-amber-500'],
                    str_contains($role,'sub-admin') => ['bg-rose-100 text-rose-700 border-rose-200','bg-rose-500'],
                    default                         => ['bg-slate-100 text-slate-600 border-slate-200','bg-slate-400'],
                };
                $directPermCount = $u->permissions->count();
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
                    <span class="bg-slate-100 text-slate-600 border border-slate-200 text-[10.5px] font-semibold px-2.5 py-0.5 rounded-lg">
                        {{ $u->vendor?->name ?? '—' }}
                    </span>
                </td>
                <td class="px-3 py-3 text-center">
                    <div class="flex flex-col items-center gap-1">
                        <span class="border {{ $roleColor[0] }} text-[10.5px] font-semibold px-2.5 py-0.5 rounded-lg capitalize">
                            {{ str_replace('-',' ',$role) }}
                        </span>
                        @if($u->has_custom_permissions)
                            <button type="button" @click="openPermissionsModal({{ $u->load('roles', 'permissions')->toJson() }})"
                                    class="inline-flex items-center gap-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[9.5px] font-bold px-2 py-0.5 rounded-full border border-indigo-200 transition-colors"
                                    title="Custom user permissions assigned. Click to view/edit.">
                                <i class="fas fa-shield-halved text-[9px]"></i>
                                <span>{{ $directPermCount }} custom perms</span>
                            </button>
                        @else
                            <button type="button" @click="openPermissionsModal({{ $u->load('roles', 'permissions')->toJson() }})"
                                    class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-500 text-[9.5px] font-semibold px-2 py-0.5 rounded-full border border-slate-200 transition-colors"
                                    title="Inherits role defaults. Click to customize.">
                                <span>Role Defaults</span>
                            </button>
                        @endif
                    </div>
                </td>
                <td class="px-3 py-3 text-center text-[11.5px] text-slate-400 hidden lg:table-cell">{{ $u->created_at->format('d M Y') }}</td>
                <td class="px-3 py-3">
                    <div class="flex items-center justify-center gap-1.5">
                        <button type="button" @click="openPermissionsModal({{ $u->load('roles', 'permissions')->toJson() }})"
                                title="Manage Individual Permissions"
                                class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-shield-halved text-[10px]"></i>
                        </button>
                        <a href="{{ route('admin.staff.commission', $u) }}" title="Commission Report"
                           class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-sack-dollar text-[10px]"></i>
                        </a>
                        <button type="button" @click="openEdit({{ $u->load('roles', 'permissions')->toJson() }})"
                                title="Edit Staff"
                                class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-600 flex items-center justify-center transition-colors">
                            <i class="fas fa-pen text-[10px]"></i>
                        </button>
                        <button type="button" @click="openDelete({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ route('admin.staff.destroy', $u) }}')"
                                title="Delete Staff"
                                class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 flex items-center justify-center transition-colors">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-gear text-slate-400 text-2xl"></i>
                    </div>
                    <p class="text-slate-400 text-sm">No staff found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">{{ $users->links() }}</div>
    @endif
</div>

{{-- ══ ADD MODAL ════════════════════════════════════════════════════ --}}
<div x-show="addOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="addOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] overflow-y-auto p-6" @click.outside="addOpen = false">
        <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
            <div class="w-11 h-11 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-plus text-blue-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Add Staff Member</p>
                <p class="text-xs text-slate-400 mt-0.5">Assign to any branch and set custom granular permissions</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="permissions_submitted" value="1">

            {{-- Basic Profile Info --}}
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/70 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Account Details</h4>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="01XXXXXXXXX"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="6" placeholder="Min 6 chars"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" x-model="addRole" @change="onAddRoleChange()" required
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 capitalize">
                            <option value="">Select role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucwords(str_replace('-',' ',$role->name)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Branch <span class="text-red-500">*</span></label>
                        <select name="vendor_id" required
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Assign to branch</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Documents upload --}}
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/70 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Documents &amp; Photo</h4>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Profile Photo <span class="text-red-500">*</span></label>
                    <input type="file" name="image" accept="image/*" required
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Staff NID (Front) <span class="text-red-500">*</span></label>
                        <input type="file" name="nid_front" accept="image/*" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Staff NID (Back) <span class="text-red-500">*</span></label>
                        <input type="file" name="nid_back" accept="image/*" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Guardian NID (Front) <span class="text-red-500">*</span></label>
                        <input type="file" name="guardian_nid_front" accept="image/*" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Guardian NID (Back) <span class="text-red-500">*</span></label>
                        <input type="file" name="guardian_nid_back" accept="image/*" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                </div>
            </div>

            {{-- ══ GRANULAR PERMISSIONS SELECTOR ══ --}}
            <div class="border-2 border-indigo-100 rounded-2xl p-4 bg-indigo-50/20 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-indigo-100/70 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide">
                                Custom Granular Permissions for this Staff
                            </h4>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Selecting a role pre-fills its template permissions. Customize checkboxes below so this user only receives their intended permissions.
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button type="button" @click="toggleAllAdd(true)"
                                class="text-[10px] font-bold text-indigo-700 bg-white hover:bg-indigo-50 border border-indigo-200 px-2 py-1 rounded-lg">
                            Select All
                        </button>
                        <button type="button" @click="toggleAllAdd(false)"
                                class="text-[10px] font-bold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg">
                            Clear
                        </button>
                        <button type="button" @click="onAddRoleChange()"
                                class="text-[10px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2 py-1 rounded-lg"
                                title="Reset to role template default">
                            Reset to Role
                        </button>
                    </div>
                </div>

                {{-- Permission Modules Checklist --}}
                <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                    @foreach($groups as $gName => $perms)
                    <div class="border border-slate-200 rounded-xl bg-white p-3 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-shield-halved text-indigo-500 text-xs"></i>
                                <span class="text-xs font-bold text-slate-700">{{ $gName }}</span>
                                <span class="text-[10px] text-slate-400">({{ count($perms) }})</span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px]">
                                <button type="button" @click="toggleGroupAdd(@json($perms), true)"
                                        class="text-indigo-600 hover:underline font-semibold">All</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="toggleGroupAdd(@json($perms), false)"
                                        class="text-slate-500 hover:underline">None</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($perms as $pName)
                            @php $lbl = $labels[$pName] ?? [$pName, '']; @endphp
                            <label class="flex items-start gap-2 p-1.5 rounded-lg border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-pointer select-none">
                                <input type="checkbox" name="permissions[]" value="{{ $pName }}"
                                       x-model="addPerms['{{ $pName }}']"
                                       class="add-perm-cb mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                                <div class="min-w-0">
                                    <div class="text-[11.5px] font-bold text-slate-800 leading-tight">{{ $lbl[0] }}</div>
                                    @if($lbl[1])
                                    <div class="text-[10px] text-slate-400 mt-0.5 leading-tight">{{ $lbl[1] }}</div>
                                    @endif
                                    <span class="text-[9px] font-mono text-slate-400">{{ $pName }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="addOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">Save &amp; Add Staff</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ EDIT MODAL ═══════════════════════════════════════════════════ --}}
<div x-show="editOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] overflow-y-auto p-6" @click.outside="editOpen = false">
        <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-pen text-amber-600 text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-slate-800 text-base">Edit Staff Member &amp; Permissions</p>
                <p class="text-xs text-slate-400 mt-0.5" x-text="editName"></p>
            </div>
        </div>
        <form :action="editAction" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="permissions_submitted" value="1">

            {{-- Basic Profile Info --}}
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/70 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Account Details</h4>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                    <input type="text" name="name" x-model="editName" required
                           class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" x-model="editEmail" required
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                        <input type="text" name="phone" x-model="editPhone" placeholder="01XXXXXXXXX"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">New Password (Optional)</label>
                        <input type="password" name="password" minlength="6" placeholder="Blank = keep current"
                               class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Role</label>
                        <select name="role" x-model="editRole" required
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 capitalize">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucwords(str_replace('-',' ',$role->name)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Branch</label>
                        <select name="vendor_id" x-model="editVendorId" required
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Update Documents --}}
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/70 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Update Documents (Leave blank to keep current)</h4>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Profile Photo</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Staff NID Front</label>
                        <input type="file" name="nid_front" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Staff NID Back</label>
                        <input type="file" name="nid_back" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Guardian NID Front</label>
                        <input type="file" name="guardian_nid_front" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Guardian NID Back</label>
                        <input type="file" name="guardian_nid_back" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 border border-slate-200 rounded-xl bg-white">
                    </div>
                </div>
            </div>

            {{-- ══ EDIT GRANULAR PERMISSIONS SELECTOR ══ --}}
            <div class="border-2 border-amber-200 rounded-2xl p-4 bg-amber-50/20 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-amber-200/70 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h4 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide">
                                Custom Granular Permissions for this Staff
                            </h4>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Modify permissions assigned specifically to this user record.
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button type="button" @click="toggleAllEdit(true)"
                                class="text-[10px] font-bold text-amber-800 bg-white hover:bg-amber-50 border border-amber-200 px-2 py-1 rounded-lg">
                            Select All
                        </button>
                        <button type="button" @click="toggleAllEdit(false)"
                                class="text-[10px] font-bold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg">
                            Clear
                        </button>
                        <button type="button" @click="onEditRoleChange()"
                                class="text-[10px] font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2 py-1 rounded-lg"
                                title="Reset to role template default">
                            Reset to Role
                        </button>
                    </div>
                </div>

                {{-- Permission Modules Checklist --}}
                <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                    @foreach($groups as $gName => $perms)
                    <div class="border border-slate-200 rounded-xl bg-white p-3 shadow-xs">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-shield-halved text-amber-500 text-xs"></i>
                                <span class="text-xs font-bold text-slate-700">{{ $gName }}</span>
                                <span class="text-[10px] text-slate-400">({{ count($perms) }})</span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px]">
                                <button type="button" @click="toggleGroupEdit(@json($perms), true)"
                                        class="text-amber-700 hover:underline font-semibold">All</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="toggleGroupEdit(@json($perms), false)"
                                        class="text-slate-500 hover:underline">None</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($perms as $pName)
                            @php $lbl = $labels[$pName] ?? [$pName, '']; @endphp
                            <label class="flex items-start gap-2 p-1.5 rounded-lg border border-slate-100 hover:border-amber-200 hover:bg-amber-50/30 transition-colors cursor-pointer select-none">
                                <input type="checkbox" name="permissions[]" value="{{ $pName }}"
                                       x-model="editPerms['{{ $pName }}']"
                                       class="edit-perm-cb mt-0.5 rounded text-amber-600 focus:ring-amber-500">
                                <div class="min-w-0">
                                    <div class="text-[11.5px] font-bold text-slate-800 leading-tight">{{ $lbl[0] }}</div>
                                    @if($lbl[1])
                                    <div class="text-[10px] text-slate-400 mt-0.5 leading-tight">{{ $lbl[1] }}</div>
                                    @endif
                                    <span class="text-[9px] font-mono text-slate-400">{{ $pName }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="editOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">Update Staff Member</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DEDICATED QUICK PERMISSIONS MODAL ═══════════════════════════════ --}}
<div x-show="permModalOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="permModalOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto p-6" @click.outside="permModalOpen = false">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span x-text="permUserName"></span>
                        <span class="text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-md capitalize" x-text="permUserRole"></span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Customize individual website permissions for this user</p>
                </div>
            </div>
            <button type="button" @click="permModalOpen = false" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>

        <form :action="permAction" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                <div class="text-xs font-semibold text-slate-600">
                    Active: <strong class="text-indigo-600 font-bold" x-text="countSelected(permUserPerms)"></strong> permissions selected
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="toggleAllPerm(true)"
                            class="text-[10px] font-bold text-indigo-700 bg-white hover:bg-indigo-50 border border-indigo-200 px-2 py-1 rounded-lg">
                        Select All
                    </button>
                    <button type="button" @click="toggleAllPerm(false)"
                            class="text-[10px] font-bold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg">
                        Clear All
                    </button>
                    <button type="button" @click="resetPermToRole()"
                            class="text-[10px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2 py-1 rounded-lg">
                        Role Defaults
                    </button>
                </div>
            </div>

            {{-- Permission Groups --}}
            <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                @foreach($groups as $gName => $perms)
                <div class="border border-slate-200 rounded-xl bg-white p-3 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2">
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-shield-halved text-indigo-500 text-xs"></i>
                            <span class="text-xs font-bold text-slate-700">{{ $gName }}</span>
                            <span class="text-[10px] text-slate-400">({{ count($perms) }})</span>
                        </div>
                        <div class="flex items-center gap-2 text-[10px]">
                            <button type="button" @click="toggleGroupPerm(@json($perms), true)"
                                    class="text-indigo-600 hover:underline font-semibold">All</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="toggleGroupPerm(@json($perms), false)"
                                    class="text-slate-500 hover:underline">None</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($perms as $pName)
                        @php $lbl = $labels[$pName] ?? [$pName, '']; @endphp
                        <label class="flex items-start gap-2 p-1.5 rounded-lg border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors cursor-pointer select-none">
                            <input type="checkbox" name="permissions[]" value="{{ $pName }}"
                                   x-model="permUserPerms['{{ $pName }}']"
                                   class="quick-perm-cb mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                            <div class="min-w-0">
                                <div class="text-[11.5px] font-bold text-slate-800 leading-tight">{{ $lbl[0] }}</div>
                                @if($lbl[1])
                                <div class="text-[10px] text-slate-400 mt-0.5 leading-tight">{{ $lbl[1] }}</div>
                                @endif
                                <span class="text-[9px] font-mono text-slate-400">{{ $pName }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="permModalOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                    Save Permissions
                </button>
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
                <p class="font-bold text-slate-800">Delete Staff?</p>
                <p class="text-sm text-slate-500 mt-0.5">This cannot be undone.</p>
            </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm font-bold text-red-700" x-text="delName"></p>
        </div>
        <div class="flex gap-3">
            <button @click="delModal = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">Cancel</button>
            <form :action="delUrl" method="POST" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl text-xs font-bold">Delete</button>
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

</div>

@endsection

@push('scripts')
<script>
function adminStaffPage() {
    return {
        addOpen: false,
        addRole: '',
        addPerms: {},

        editOpen: false,
        editAction: '',
        editName: '',
        editEmail: '',
        editPhone: '',
        editRole: '',
        editVendorId: '',
        editPerms: {},

        permModalOpen: false,
        permAction: '',
        permUserName: '',
        permUserRole: '',
        permUserPerms: {},

        delModal: false,
        delName: '',
        delUrl: '',

        previewModalOpen: false,
        previewSrc: '',
        previewTitle: '',

        roleDefaults: @json($roleDefaults),

        onAddRoleChange() {
            var defaults = this.roleDefaults[this.addRole] || [];
            this.addPerms = {};
            defaults.forEach(p => { this.addPerms[p] = true; });
        },

        onEditRoleChange() {
            var defaults = this.roleDefaults[this.editRole] || [];
            this.editPerms = {};
            defaults.forEach(p => { this.editPerms[p] = true; });
        },

        resetPermToRole() {
            var defaults = this.roleDefaults[this.permUserRole] || [];
            this.permUserPerms = {};
            defaults.forEach(p => { this.permUserPerms[p] = true; });
        },

        toggleAllAdd(check) {
            document.querySelectorAll('.add-perm-cb').forEach(el => {
                this.addPerms[el.value] = check;
            });
        },

        toggleGroupAdd(perms, check) {
            perms.forEach(p => { this.addPerms[p] = check; });
        },

        toggleAllEdit(check) {
            document.querySelectorAll('.edit-perm-cb').forEach(el => {
                this.editPerms[el.value] = check;
            });
        },

        toggleGroupEdit(perms, check) {
            perms.forEach(p => { this.editPerms[p] = check; });
        },

        toggleAllPerm(check) {
            document.querySelectorAll('.quick-perm-cb').forEach(el => {
                this.permUserPerms[el.value] = check;
            });
        },

        toggleGroupPerm(perms, check) {
            perms.forEach(p => { this.permUserPerms[p] = check; });
        },

        openEdit(u) {
            this.editAction   = '/admin/staff/' + u.id;
            this.editName     = u.name        || '';
            this.editEmail    = u.email       || '';
            this.editPhone    = u.phone       || '';
            this.editRole     = u.roles?.[0]?.name || '';
            this.editVendorId = String(u.vendor_id || '');

            this.editPerms = {};
            if (u.has_custom_permissions && u.permissions && u.permissions.length > 0) {
                u.permissions.forEach(p => { this.editPerms[p.name] = true; });
            } else if (this.roleDefaults[this.editRole]) {
                this.roleDefaults[this.editRole].forEach(p => { this.editPerms[p] = true; });
            }
            this.editOpen = true;
        },

        openPermissionsModal(u) {
            this.permAction   = '/admin/staff/' + u.id + '/permissions';
            this.permUserName = u.name;
            this.permUserRole = u.roles?.[0]?.name || 'No Role';
            this.permUserPerms = {};

            if (u.has_custom_permissions && u.permissions && u.permissions.length > 0) {
                u.permissions.forEach(p => { this.permUserPerms[p.name] = true; });
            } else if (this.roleDefaults[this.permUserRole]) {
                this.roleDefaults[this.permUserRole].forEach(p => { this.permUserPerms[p] = true; });
            }
            this.permModalOpen = true;
        },

        countSelected(permsObj) {
            if (!permsObj) return 0;
            return Object.values(permsObj).filter(Boolean).length;
        },

        previewImage(src, title) {
            this.previewSrc = src;
            this.previewTitle = title;
            this.previewModalOpen = true;
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
