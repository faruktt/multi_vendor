<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('admin123'),
                'vendor_id' => null,
            ]
        );

        $admin->syncRoles(['super-admin']);
    }
}
