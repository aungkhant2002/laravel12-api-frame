<?php

namespace Modules\RBAC\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RBACSeeder::class,
        ]);
    }
}
