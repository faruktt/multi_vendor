@extends('shop.account.layout')
@section('title', 'Profile & Security — ' . ($branch->system_name ?? $branch->name))

@section('account_content')
<div class="space-y-6">

    {{-- Update Profile Details --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-6 shadow-sm">
        <div class="border-b border-gray-100 pb-3 mb-5">
            <h2 class="text-base font-bold text-gray-900">Personal Information</h2>
            <p class="text-xs text-gray-400">Update your name, contact phone, email, and primary shipping address</p>
        </div>

        <form method="POST" action="{{ route('shop.customer.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full h-11 border border-gray-300 rounded-xl px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                           class="w-full h-11 border border-gray-300 rounded-xl px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                       placeholder="name@example.com"
                       class="w-full h-11 border border-gray-300 rounded-xl px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Default Shipping Address</label>
                <textarea name="address" rows="3" placeholder="Street, house number, area, district"
                          class="w-full border border-gray-300 rounded-xl p-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50 resize-none">{{ old('address', $customer->address) }}</textarea>
            </div>

            <button type="submit"
                    class="h-10 px-5 bg-brand hover:bg-brand-dark text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-brand/20">
                Save Profile Changes
            </button>
        </form>
    </div>

    {{-- Update Password --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 sm:p-6 shadow-sm">
        <div class="border-b border-gray-100 pb-3 mb-5">
            <h2 class="text-base font-bold text-gray-900">Change Password</h2>
            <p class="text-xs text-gray-400">Ensure your account is using a secure password</p>
        </div>

        <form method="POST" action="{{ route('shop.customer.password.update') }}" class="space-y-4 max-w-md">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Current Password *</label>
                <input type="password" name="current_password" required
                       placeholder="Enter current password"
                       class="w-full h-11 border border-gray-300 rounded-xl px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">New Password *</label>
                <input type="password" name="password" required
                       placeholder="Min 6 characters"
                       class="w-full h-11 border border-gray-300 rounded-xl px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Confirm New Password *</label>
                <input type="password" name="password_confirmation" required
                       placeholder="Re-type new password"
                       class="w-full h-11 border border-gray-300 rounded-xl px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-gray-50/50">
            </div>

            <button type="submit"
                    class="h-10 px-5 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition-all">
                Update Password
            </button>
        </form>
    </div>

</div>
@endsection
