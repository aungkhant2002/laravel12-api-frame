<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure roles exist with SAME guard as RBACSeeder
        foreach (['admin', 'coach', 'user'] as $name) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        // Create ONLY if you want a second admin; otherwise remove this block
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone' => '+959000000001',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']); // safer than assignRole

        // Coaches
        User::factory()
            ->count(5)
            ->create()
            ->each(fn (User $u) => $u->syncRoles(['coach']));

        // Normal users
        User::factory()
            ->count(50)
            ->create()
            ->each(fn (User $u) => $u->syncRoles(['user']));
    }
}
