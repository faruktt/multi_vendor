@extends('layouts.app')
@section('title','Roles & Permissions')
@section('heading','Roles & Permissions')

@php
    $roleColors = [
        'vendor-owner' => ['bg' => 'bg-blue-600',   'light' => 'bg-blue-50',   'text' => 'text-blue-700',   'ring' => 'ring-blue-300',   'icon' => 'fas fa-crown'],
        'cashier'      => ['bg' => 'bg-violet-600',  'light' => 'bg-violet-50', 'text' => 'text-violet-700', 'ring' => 'ring-violet-300', 'icon' => 'fas fa-cash-register'],
        'staff'        => ['bg' => 'bg-slate-500',   'light' => 'bg-slate-50',  'text' => 'text-slate-700',  'ring' => 'ring-slate-300',  'icon' => 'fas fa-user'],
    ];
    $groupIcons = [
        'Dashboard' => 'fas fa-gauge-high',
        'Sales'     => 'fas fa-receipt',
        'Products'  => 'fas fa-box',
        'People'    => 'fas fa-users',
        'Staff'     => 'fas fa-user-gear',
        'Reports'   => 'fas fa-chart-bar',
    ];
@endphp

@section('content')

{{-- ══ FLASH ══ --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-semibold">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
    <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600"><i class="fas fa-xmark"></i></button>
</div>
@endif

{{-- ══ HEADER INFO ══ --}}
<div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-2xl p-5 mb-5 text-white">
    <div class="flex items-start gap-4">
        <div class="w-11 h-11 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shield-halved text-white text-lg"></i>
        </div>
        <div>
            <h2 class="font-bold text-[15px] mb-1">Role Permissions Manager</h2>
            <p class="text-slate-300 text-[12.5px] leading-relaxed">
                Control what each role can access. <strong class="text-white">Super Admin</strong> always has all permissions (locked).
                Changes take effect immediately for all users with the updated role.
            </p>
        </div>
    </div>
    {{-- Role legend --}}
    <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-white/10">
        <div class="flex items-center gap-2 bg-white/10 rounded-xl px-3 py-1.5">
            <i class="fas fa-infinity text-yellow-300 text-[11px]"></i>
            <span class="text-[12px] font-semibold">super-admin — All permissions, always</span>
        </div>
        @foreach($roles as $role)
        @php $rc = $roleColors[$role->name] ?? ['bg'=>'bg-slate-500','icon'=>'fas fa-user']; @endphp
        <div class="flex items-center gap-2 bg-white/10 rounded-xl px-3 py-1.5">
            <i class="{{ $rc['icon'] }} text-[11px] text-slate-300"></i>
            <span class="text-[12px] font-semibold">{{ $role->name }}</span>
            <span class="text-[10.5px] text-slate-400">— customisable</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ══ PERMISSION MATRIX ══ --}}
<form method="POST" action="{{ route('admin.roles-permissions.update') }}" id="permForm">
@csrf

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-5">

    {{-- Table header --}}
    <div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100">
                <th class="px-5 py-4 text-left w-[340px]">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Permission</span>
                </th>
                {{-- Super admin locked column --}}
                <th class="px-4 py-4 text-center min-w-[130px]">
                    <div class="inline-flex flex-col items-center gap-1.5">
                        <div class="w-9 h-9 rounded-xl bg-yellow-400 flex items-center justify-center shadow-sm">
                            <i class="fas fa-infinity text-white text-sm"></i>
                        </div>
                        <span class="text-[11.5px] font-bold text-slate-700">super-admin</span>
                        <span class="text-[10px] text-slate-400 bg-yellow-50 border border-yellow-200 px-2 py-0.5 rounded-full">Locked</span>
                    </div>
                </th>
                @foreach($roles as $role)
                @php $rc = $roleColors[$role->name] ?? ['bg'=>'bg-slate-500','light'=>'bg-slate-50','text'=>'text-slate-700','ring'=>'ring-slate-300','icon'=>'fas fa-user']; @endphp
                <th class="px-4 py-4 text-center min-w-[130px]">
                    <div class="inline-flex flex-col items-center gap-1.5">
                        <div class="w-9 h-9 rounded-xl {{ $rc['bg'] }} flex items-center justify-center shadow-sm">
                            <i class="{{ $rc['icon'] }} text-white text-sm"></i>
                        </div>
                        <span class="text-[11.5px] font-bold text-slate-700">{{ $role->name }}</span>
                        <button type="button"
                                onclick="toggleAll({{ $role->id }})"
                                class="text-[10px] {{ $rc['text'] }} {{ $rc['light'] }} border border-current/20 px-2 py-0.5 rounded-full hover:opacity-80 transition-opacity">
                            Toggle All
                        </button>
                    </div>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>

        @foreach($groups as $groupName => $permNames)
        {{-- Group header row --}}
        <tr class="bg-slate-50/80">
            <td colspan="{{ 2 + count($roles) }}" class="px-5 py-2.5">
                <div class="flex items-center gap-2">
                    <i class="{{ $groupIcons[$groupName] ?? 'fas fa-circle' }} text-slate-500 text-[11px] w-3.5 text-center"></i>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{{ $groupName }}</span>
                </div>
            </td>
        </tr>

        @foreach($permNames as $permName)
        @php
            $lbl = $labels[$permName] ?? [$permName, ''];
        @endphp
        <tr class="border-b border-slate-50 hover:bg-blue-50/20 transition-colors group">
            {{-- Permission label --}}
            <td class="px-5 py-3.5 pl-10">
                <p class="text-[13px] font-semibold text-slate-800">{{ $lbl[0] }}</p>
                @if($lbl[1])
                <p class="text-[11.5px] text-slate-400 mt-0.5">{{ $lbl[1] }}</p>
                @endif
            </td>

            {{-- Super admin cell — always checked, locked --}}
            <td class="px-4 py-3.5 text-center">
                <label class="inline-flex items-center justify-center cursor-not-allowed">
                    <div class="w-5 h-5 rounded-md bg-yellow-400 border-2 border-yellow-400 flex items-center justify-center">
                        <i class="fas fa-check text-white text-[10px]"></i>
                    </div>
                </label>
            </td>

            {{-- Role columns --}}
            @foreach($roles as $role)
            @php
                $rc      = $roleColors[$role->name] ?? ['ring'=>'ring-blue-300','text'=>'text-blue-600','light'=>'bg-blue-50'];
                $checked = isset($rolePerms[$role->id][$permName]);
            @endphp
            <td class="px-4 py-3.5 text-center">
                <label class="inline-flex items-center justify-center cursor-pointer group/cb">
                    <input type="checkbox"
                           name="roles[{{ $role->id }}][]"
                           value="{{ $permName }}"
                           data-role="{{ $role->id }}"
                           class="perm-cb sr-only"
                           {{ $checked ? 'checked' : '' }}>
                    <div class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all
                                {{ $checked ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300 group-hover/cb:border-blue-400' }}"
                         style="{{ $checked ? '' : '' }}"
                         x-ref="box">
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

{{-- ══ SAVE BAR ══ --}}
<div class="sticky bottom-4 flex items-center gap-3 bg-white border border-slate-200 shadow-lg rounded-2xl px-5 py-4">
    <div class="flex items-center gap-2 text-slate-500 text-[12.5px]">
        <i class="fas fa-circle-info text-blue-500"></i>
        Changes apply immediately to all users with the affected role.
    </div>
    <div class="ml-auto flex items-center gap-3">
        <a href="{{ route('admin.index') }}"
           class="border border-slate-200 text-slate-600 hover:bg-slate-50 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            Cancel
        </a>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-7 py-2.5 rounded-xl text-sm font-bold transition-colors flex items-center gap-2 shadow-md shadow-blue-200">
            <i class="fas fa-floppy-disk"></i> Save All Permissions
        </button>
    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
// Update the visual box to match the checkbox state
function syncBox(cb) {
    var box = cb.nextElementSibling;
    if (cb.checked) {
        box.classList.add('bg-blue-600', 'border-blue-600');
        box.classList.remove('bg-white', 'border-slate-300');
        box.innerHTML = '<i class="fas fa-check text-white text-[10px]"></i>';
    } else {
        box.classList.remove('bg-blue-600', 'border-blue-600');
        box.classList.add('bg-white', 'border-slate-300');
        box.innerHTML = '';
    }
}

// The <label> wrapping the checkbox already handles click-to-toggle natively.
// We only need to listen for 'change' to update the visual.
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
    // Mark form dirty manually since we bypassed the change event
    formDirty = true;
}

// Warn before leaving with unsaved changes
var formDirty = false;
document.getElementById('permForm').addEventListener('change', function() { formDirty = true; });
document.getElementById('permForm').addEventListener('submit', function() { formDirty = false; });
window.addEventListener('beforeunload', function(e) {
    if (formDirty) { e.preventDefault(); e.returnValue = ''; }
});
</script>
@endpush
