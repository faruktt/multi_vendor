@extends('layouts.app')
@section('title','Roles & Permissions')
@section('heading','Roles & Permissions')

@php
    $roleColors = [
        'vendor-owner' => ['bg' => 'bg-blue-600',   'light' => 'bg-blue-50',   'text' => 'text-blue-700',   'ring' => 'ring-blue-300',   'icon' => 'fas fa-crown'],
        'cashier'      => ['bg' => 'bg-violet-600',  'light' => 'bg-violet-50', 'text' => 'text-violet-700', 'ring' => 'ring-violet-300', 'icon' => 'fas fa-cash-register'],
        'staff'        => ['bg' => 'bg-emerald-600', 'light' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-300', 'icon' => 'fas fa-user'],
        'moderator'    => ['bg' => 'bg-amber-600',   'light' => 'bg-amber-50',   'text' => 'text-amber-700',   'ring' => 'ring-amber-300',   'icon' => 'fas fa-headset'],
        'manager'      => ['bg' => 'bg-indigo-600',  'light' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'ring' => 'ring-indigo-300', 'icon' => 'fas fa-user-tie'],
        'sub-admin'    => ['bg' => 'bg-rose-600',    'light' => 'bg-rose-50',   'text' => 'text-rose-700',   'ring' => 'ring-rose-300',   'icon' => 'fas fa-user-shield'],
    ];

    $groupIcons = [
        'Dashboard & Sales'     => 'fas fa-gauge-high',
        'Products & Catalog'    => 'fas fa-box',
        'Purchases & Suppliers' => 'fas fa-truck',
        'Customers'             => 'fas fa-users',
        'Reports & Analytics'   => 'fas fa-chart-line',
        'Reseller Hub'          => 'fas fa-handshake',
        'Supplier Hub'          => 'fas fa-store',
        'Live Chat & Support'   => 'fas fa-comments',
        'Staff & Roles'         => 'fas fa-user-shield',
        'Storefront & CMS'      => 'fas fa-palette',
        'Settings & Logistics'  => 'fas fa-sliders',
    ];
@endphp

@section('content')

<div x-data="{
    addRoleOpen: false,
    editRoleOpen: false,
    editRoleName: '',
    editRoleAction: '',
    deleteRoleOpen: false,
    deleteRoleName: '',
    deleteRoleAction: '',
    openEditRole(id, name) {
        this.editRoleName = name;
        this.editRoleAction = '/admin/roles-permissions/roles/' + id;
        this.editRoleOpen = true;
    },
    openDeleteRole(id, name) {
        this.deleteRoleName = name;
        this.deleteRoleAction = '/admin/roles-permissions/roles/' + id;
        this.deleteRoleOpen = true;
    }
}">

{{-- ══ FLASH MESSAGES ══ --}}
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

{{-- ══ HEADER INFO ══ --}}
<div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-5 mb-5 text-white shadow-md border border-slate-800">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 flex items-center justify-center flex-shrink-0 text-xl shadow-inner">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <h2 class="font-bold text-base md:text-lg mb-0.5">Roles &amp; Website Permissions Matrix</h2>
                <p class="text-slate-300 text-xs leading-relaxed max-w-2xl">
                    Create custom roles (Staff, Manager, Sub Admin, etc.) and configure baseline permission templates. When creating individual staff, you can further fine-tune specific permissions per user.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0 flex-wrap">
            <button type="button" @click="addRoleOpen = true"
                    class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-plus-circle text-indigo-300"></i> Add New Role
            </button>
            <button type="submit" form="permForm" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-indigo-950/40">
                <i class="fas fa-floppy-disk"></i> Save Permissions
            </button>
        </div>
    </div>

    {{-- Role legend --}}
    <div class="flex flex-wrap items-center gap-2.5 mt-4 pt-3.5 border-t border-white/10">
        <div class="flex items-center gap-1.5 bg-white/10 rounded-lg px-2.5 py-1 text-xs">
            <i class="fas fa-infinity text-yellow-300 text-[10px]"></i>
            <span class="font-bold text-slate-200">super-admin</span>
            <span class="text-[10px] text-yellow-300 font-semibold bg-yellow-400/20 px-1.5 py-0.2 rounded">All Unlocked</span>
        </div>
        @foreach($roles as $role)
        @php
            $rc = $roleColors[$role->name] ?? ['bg'=>'bg-slate-700','icon'=>'fas fa-user-tag'];
        @endphp
        <div class="flex items-center gap-2 bg-white/10 rounded-lg px-2.5 py-1 text-xs group">
            <i class="{{ $rc['icon'] }} text-[10px] text-indigo-300"></i>
            <span class="font-bold text-slate-200 capitalize">{{ str_replace('-',' ',$role->name) }}</span>
            <span class="text-[10px] text-slate-400">({{ $role->permissions->count() }})</span>
            <button type="button" @click="openEditRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                    title="Rename Role" class="text-slate-400 hover:text-amber-300 text-[10px] transition-colors ml-1">
                <i class="fas fa-pen"></i>
            </button>
            @if($role->name !== 'super-admin')
            <button type="button" @click="openDeleteRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                    title="Delete Role" class="text-slate-400 hover:text-red-400 text-[10px] transition-colors">
                <i class="fas fa-trash-can"></i>
            </button>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ══ FILTER & SEARCH BAR ══ --}}
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-3.5 mb-4 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="relative w-full sm:w-80">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="permSearch" placeholder="Search permissions (e.g. sales, courier, reseller, products)..."
               class="w-full pl-9 pr-3 py-1.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50">
    </div>
    <div class="flex items-center gap-2.5 text-xs text-slate-500 ml-auto">
        <span class="bg-slate-100 text-slate-700 font-bold px-2.5 py-1 rounded-lg border border-slate-200/60">
            Total: {{ count($labels) }} permissions
        </span>
        <span class="text-slate-300">•</span>
        <span class="bg-slate-100 text-slate-700 font-bold px-2.5 py-1 rounded-lg border border-slate-200/60">
            {{ count($groups) }} modules
        </span>
    </div>
</div>

{{-- ══ PERMISSION MATRIX ══ --}}
<form method="POST" action="{{ route('admin.roles-permissions.update') }}" id="permForm">
@csrf

<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-5">
    <div class="overflow-x-auto">
    <table class="w-full text-left" id="permTable">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50/90">
                <th class="px-5 py-4 w-[380px]">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Module / Action</span>
                </th>
                {{-- Super admin locked column --}}
                <th class="px-3.5 py-4 text-center min-w-[120px] bg-amber-50/30 border-l border-slate-200/60">
                    <div class="inline-flex flex-col items-center gap-1">
                        <div class="w-8 h-8 rounded-xl bg-yellow-400 text-white flex items-center justify-center shadow-xs text-xs">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">super-admin</span>
                        <span class="text-[9.5px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full">Always All</span>
                    </div>
                </th>
                @foreach($roles as $role)
                @php
                    $rc = $roleColors[$role->name] ?? ['bg'=>'bg-slate-700','light'=>'bg-slate-50','text'=>'text-slate-700','icon'=>'fas fa-user-tag'];
                @endphp
                <th class="px-3.5 py-4 text-center min-w-[140px] border-l border-slate-100">
                    <div class="inline-flex flex-col items-center gap-1">
                        <div class="flex items-center gap-1">
                            <div class="w-8 h-8 rounded-xl {{ $rc['bg'] }} text-white flex items-center justify-center shadow-xs text-xs">
                                <i class="{{ $rc['icon'] }}"></i>
                            </div>
                            <div class="flex flex-col gap-0.5">
                                <button type="button" @click="openEditRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                                        title="Rename" class="w-4 h-4 rounded text-slate-400 hover:text-amber-600 hover:bg-amber-50 flex items-center justify-center text-[9px]">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" @click="openDeleteRole({{ $role->id }}, '{{ addslashes($role->name) }}')"
                                        title="Delete" class="w-4 h-4 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 flex items-center justify-center text-[9px]">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-800 capitalize leading-tight">{{ str_replace('-',' ',$role->name) }}</span>
                        <button type="button"
                                onclick="toggleAll({{ $role->id }})"
                                class="text-[9.5px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 border border-indigo-200/80 px-2.5 py-0.5 rounded-full transition-colors mt-0.5">
                            Toggle All
                        </button>
                    </div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">

        @foreach($groups as $groupName => $permNames)
        @php
            $groupSlug = Str::slug($groupName);
        @endphp
        {{-- Group header row --}}
        <tr class="bg-slate-100/80 border-t-2 border-slate-200 group-header-row" data-group="{{ $groupSlug }}">
            <td class="px-5 py-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-indigo-600 flex items-center justify-center text-xs shadow-xs">
                        <i class="{{ $groupIcons[$groupName] ?? 'fas fa-shield-halved' }}"></i>
                    </div>
                    <div>
                        <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wide">{{ $groupName }}</span>
                    </div>
                    <span class="text-[10px] font-bold bg-white text-slate-500 px-2 py-0.5 rounded-full border border-slate-200">
                        {{ count($permNames) }}
                    </span>
                </div>
            </td>
            <td class="text-center bg-amber-50/20 border-l border-slate-200/40">
                <span class="text-[10px] text-slate-400 font-semibold">—</span>
            </td>
            @foreach($roles as $role)
            <td class="text-center px-2 py-2 border-l border-slate-100">
                <button type="button"
                        onclick="toggleGroup('{{ $groupSlug }}', {{ $role->id }})"
                        class="text-[9.5px] font-bold text-slate-600 hover:text-indigo-700 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 px-2.5 py-1 rounded-lg transition-all shadow-xs">
                    Toggle Group
                </button>
            </td>
            @endforeach
        </tr>

        @foreach($permNames as $permName)
        @php
            $lbl = $labels[$permName] ?? [$permName, ''];
        @endphp
        <tr class="hover:bg-indigo-50/30 transition-colors perm-row" data-group="{{ $groupSlug }}" data-text="{{ strtolower($lbl[0] . ' ' . $lbl[1] . ' ' . $permName) }}">
            {{-- Permission label --}}
            <td class="px-5 py-3 pl-8">
                <p class="text-xs font-bold text-slate-800">{{ $lbl[0] }}</p>
                @if($lbl[1])
                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $lbl[1] }}</p>
                @endif
                <span class="inline-block mt-1 text-[9.5px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.2 rounded border border-slate-200/50">
                    {{ $permName }}
                </span>
            </td>

            {{-- Super admin cell — always checked, locked --}}
            <td class="px-3.5 py-3 text-center bg-amber-50/20 border-l border-slate-200/40">
                <div class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-yellow-400 border border-yellow-500 text-white text-[10px] shadow-xs cursor-not-allowed">
                    <i class="fas fa-check"></i>
                </div>
            </td>

            {{-- Role columns --}}
            @foreach($roles as $role)
            @php
                $checked = isset($rolePerms[$role->id][$permName]);
            @endphp
            <td class="px-3.5 py-3 text-center border-l border-slate-100">
                <label class="inline-flex items-center justify-center cursor-pointer select-none">
                    <input type="checkbox"
                           name="roles[{{ $role->id }}][]"
                           value="{{ $permName }}"
                           data-role="{{ $role->id }}"
                           data-group="{{ $groupSlug }}"
                           class="perm-cb sr-only"
                           {{ $checked ? 'checked' : '' }}>
                    <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all
                                {{ $checked ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-slate-300 hover:border-indigo-400' }}">
                        @if($checked)
                        <i class="fas fa-check text-white text-[10px]"></i>
                        @endif
                    </div>
                </label>
            </td>
            @endforeach
        </tr>
        @endforeach
        @endforeach

        </tbody>
    </table>
    </div>
</div>

{{-- ══ STICKY SAVE BAR ══ --}}
<div class="sticky bottom-4 z-20 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white/95 backdrop-blur-md border border-slate-200 shadow-xl rounded-2xl px-5 py-3.5">
    <div class="flex items-center gap-2 text-slate-600 text-xs">
        <i class="fas fa-circle-info text-indigo-500 text-sm"></i>
        <span>Role matrix sets default permissions for roles. Individual staff permissions can also be fine-tuned inside Staff Management.</span>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ route('admin.staff.index') }}"
           class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-xl text-xs font-semibold transition-colors">
            Staff Members
        </a>
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-indigo-600/30">
            <i class="fas fa-floppy-disk"></i> Save All Permissions
        </button>
    </div>
</div>

</form>

{{-- ══ ADD ROLE MODAL ══ --}}
<div x-show="addRoleOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="addRoleOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="addRoleOpen = false">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg flex-shrink-0">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">Create New Role</h3>
                <p class="text-xs text-slate-400 mt-0.5">Add custom role (e.g. Staff, Manager, Sub Admin)</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.roles-permissions.roles.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Manager, Sub Admin, Moderator"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                <p class="text-[11px] text-slate-400 mt-1">Will be converted to lowercase slug (e.g., "Sub Admin" → "sub-admin").</p>
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" @click="addRoleOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ EDIT ROLE MODAL ══ --}}
<div x-show="editRoleOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="editRoleOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="editRoleOpen = false">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 text-lg flex-shrink-0">
                <i class="fas fa-pen-to-square"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">Rename Role</h3>
                <p class="text-xs text-slate-400 mt-0.5">Update role display name and identifier</p>
            </div>
        </div>

        <form :action="editRoleAction" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Role Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="editRoleName" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-xs bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white">
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" @click="editRoleOpen = false"
                        class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                    Update Name
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ DELETE ROLE MODAL ══ --}}
<div x-show="deleteRoleOpen" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
     @keydown.escape.window="deleteRoleOpen = false">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.outside="deleteRoleOpen = false">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-11 h-11 rounded-2xl bg-red-100 flex items-center justify-center text-red-600 text-lg flex-shrink-0">
                <i class="fas fa-trash-can"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">Delete Role?</h3>
                <p class="text-xs text-slate-400 mt-0.5">Are you sure you want to remove this role?</p>
            </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-2.5 mb-4">
            <span class="text-xs font-bold text-red-700 capitalize" x-text="deleteRoleName"></span>
            <p class="text-[11px] text-red-600 mt-1">If any staff member is assigned to this role, they must be reassigned first.</p>
        </div>
        <div class="flex gap-2.5">
            <button type="button" @click="deleteRoleOpen = false"
                    class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-semibold hover:bg-slate-50">
                Cancel
            </button>
            <form :action="deleteRoleAction" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

</div>

@endsection

@push('scripts')
<script>
// Visual box checkbox sync
function syncBox(cb) {
    var box = cb.nextElementSibling;
    if (cb.checked) {
        box.classList.add('bg-indigo-600', 'border-indigo-600');
        box.classList.remove('bg-white', 'border-slate-300');
        box.innerHTML = '<i class="fas fa-check text-white text-[10px]"></i>';
    } else {
        box.classList.remove('bg-indigo-600', 'border-indigo-600');
        box.classList.add('bg-white', 'border-slate-300');
        box.innerHTML = '';
    }
}

document.querySelectorAll('.perm-cb').forEach(function(cb) {
    cb.addEventListener('change', function() { syncBox(this); });
});

// Toggle all permissions for a role
function toggleAll(roleId) {
    var cbs = document.querySelectorAll('[data-role="' + roleId + '"]');
    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    cbs.forEach(function(cb) {
        cb.checked = !allChecked;
        syncBox(cb);
    });
    formDirty = true;
}

// Toggle group permissions for a specific role
function toggleGroup(groupSlug, roleId) {
    var cbs = document.querySelectorAll('[data-role="' + roleId + '"][data-group="' + groupSlug + '"]');
    var allChecked = Array.from(cbs).every(function(cb) { return cb.checked; });
    cbs.forEach(function(cb) {
        cb.checked = !allChecked;
        syncBox(cb);
    });
    formDirty = true;
}

// Live search filter
var searchInput = document.getElementById('permSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('.perm-row');
        var groupRows = document.querySelectorAll('.group-header-row');

        if (!q) {
            rows.forEach(function(r) { r.style.display = ''; });
            groupRows.forEach(function(g) { g.style.display = ''; });
            return;
        }

        var visibleGroups = {};
        rows.forEach(function(r) {
            var text = r.getAttribute('data-text') || '';
            var group = r.getAttribute('data-group') || '';
            if (text.indexOf(q) !== -1) {
                r.style.display = '';
                visibleGroups[group] = true;
            } else {
                r.style.display = 'none';
            }
        });

        groupRows.forEach(function(g) {
            var gSlug = g.getAttribute('data-group');
            g.style.display = visibleGroups[gSlug] ? '' : 'none';
        });
    });
}

// Dirty form prompt
var formDirty = false;
document.getElementById('permForm').addEventListener('change', function() { formDirty = true; });
document.getElementById('permForm').addEventListener('submit', function() { formDirty = false; });
window.addEventListener('beforeunload', function(e) {
    if (formDirty) { e.preventDefault(); e.returnValue = ''; }
});
</script>
@endpush
