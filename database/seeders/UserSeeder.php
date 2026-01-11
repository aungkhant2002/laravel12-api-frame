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
        foreach (['Super Admin', 'Court Manager', 'Support Staff', 'Coach', 'User'] as $name) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@mtf.com'],
            [
                'name' => 'Admin',
                'phone' => '09799123456',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['Super Admin']);

        // Coaches
        User::factory()
            ->count(5)
            ->create()
            ->each(fn (User $u) => $u->syncRoles(['Coach']));

        // Normal users
        User::factory()
            ->count(50)
            ->create()
            ->each(fn (User $u) => $u->syncRoles(['User']));
    }
}
