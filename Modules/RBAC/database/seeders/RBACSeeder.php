<?php

namespace Modules\RBAC\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = 'sanctum';
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $coachRole = Role::firstOrCreate(['name' => 'coach', 'guard_name' => $guard]);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);

        // Permissions
        $permissions = [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            'booking.create',
            'booking.update',
            'booking.cancel',

            'role.assign',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        // assign all permissions to admin
        $adminRole->syncPermissions(
            Permission::where('guard_name', $guard)->get()
        );

        // create admin user
//        $adminUser = User::firstOrCreate(
//            ['email' => 'admin@gmail.com'],
//            [
//                'name' => 'Super Admin',
//                'password' => Hash::make('password'),
//                'phone' => '0123456789',
//                'phone_verified_at' => now(),
//                'is_active' => true,
//            ]
//        );

        // assign admin role
//        if (!$adminUser->hasRole('admin')) {
//            $adminUser->assignRole('admin');
//        }
    }
}
