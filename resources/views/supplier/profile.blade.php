@extends('supplier.layouts.app')
@section('title', 'Store Settings')
@section('heading', 'Store & Profile Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Store Profile Form --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                <i class="fas fa-store"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Store & Contact Information</h3>
                <p class="text-[11px] text-slate-400">Update your public brand name, contact info, and business location</p>
            </div>
        </div>

        <form method="POST" action="{{ route('supplier.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 mb-4">
                <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center shadow-sm">
                    @if($supplier->logo_url)
                        <img src="{{ $supplier->logo_url }}" alt="{{ $supplier->display_name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white font-black text-2xl">
                            {{ strtoupper(substr($supplier->display_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Store Logo / Brand Avatar</label>
                    <input type="file" name="logo" accept="image/*"
                           class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Recommended: square image, max 2MB (JPG, PNG)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Store Name --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Business / Store Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" required
                           placeholder="Store name displayed on products"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>

                {{-- Owner Name --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Contact Person Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address <span class="text-slate-400 font-normal">(Non-editable)</span></label>
                    <input type="email" value="{{ $supplier->email }}" readonly
                           class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-500 cursor-not-allowed">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
            </div>

            {{-- Address --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Business / Warehouse Address</label>
                <textarea name="address" rows="2"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition resize-none">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fas fa-building-columns text-emerald-600 text-sm"></i>
                    <h4 class="font-bold text-slate-800 text-xs">Payout & Financial Information</h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">bKash / Nagad Mobile Banking</label>
                        <input type="text" name="bkash_number" value="{{ old('bkash_number', $supplier->bkash_number) }}"
                               placeholder="01XXXXXXXXX"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Bank Account Information</label>
                        <textarea name="bank_info" rows="2"
                                  placeholder="Bank Name, Account Name, Account No, Branch..."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition resize-none">{{ old('bank_info', $supplier->bank_info) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-3">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password Form --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Security & Password</h3>
                <p class="text-[11px] text-slate-400">Keep your supplier account secure</p>
            </div>
        </div>

        <form method="POST" action="{{ route('supplier.password.update') }}" class="space-y-4 max-w-md">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">New Password</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Confirm New Password</label>
                <input type="password" name="password_confirmation" required minlength="6"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs transition">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
