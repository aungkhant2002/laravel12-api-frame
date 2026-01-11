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
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
        $managerRole = Role::firstOrCreate(['name' => 'Court Manager', 'guard_name' => $guard]);
        $staffRole = Role::firstOrCreate(['name' => 'Support Staff', 'guard_name' => $guard]);
        $coachRole = Role::firstOrCreate(['name' => 'Coach', 'guard_name' => $guard]);
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => $guard]);

        // Permissions
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'booking.create',
            'booking.update',
            'booking.cancel',

            'roles.assign',
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

    }
}
