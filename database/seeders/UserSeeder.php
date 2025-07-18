<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        $user = User::create([
            'name' => config('demo.super_admin_name'),
            'email' => config('demo.super_admin_email'),
            'password' => bcrypt(config('demo.super_admin_password')),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $user->assignRole(Role::SuperAdmin);

        // Create Guest
        $user = User::create([
            'name' => config('demo.guest_name'),
            'email' => config('demo.guest_email'),
            'password' => bcrypt(config('demo.guest_password')),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $user->assignRole(Role::Guest);
    }
}
