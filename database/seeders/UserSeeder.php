<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        RolePermissionSeeder::class;
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => '123admin',
            'email_verified_at' => now(),
        ])->assignRole('admin');

        User::create([
            'name' => 'Agent Smith',
            'email' => 'agent@example.com',
            'password' =>'password',
            'email_verified_at' => now(),
        ])->assignRole('support-agent');

        User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ])->assignRole('customer');
    }
}
