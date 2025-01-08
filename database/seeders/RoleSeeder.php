<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = Role::create([
            'name' => 'superadmin'
        ]);
        $superadmin->givePermissionTo([
            'manage rbac',
            'manage currency',
            'manage status',
            'manage position',
            'manage department',

            'view logs',

            'create task',
            'update task',
            'delete task',
            'view task',
            'view all tasks',
            'start task',
            'complete task',

            'create application',
            'update application',
            'delete application',
            'view application',
            'view all applications',

            'create project',
            'update project',
            'delete project',
            'view project',

            'create contract',
            'update contract',
            'delete contract',
            'view contract',
            'view all contracts',
        ]);
        $manager = Role::create([
            'name' => 'manager'
        ]);
        $manager->givePermissionTo([
            'view task',
            'start task',
            'complete task',

            'create application',
            'update application',
            'view application',

            'view project',

            'create contract',
            'update contract',
            'view contract',
        ]);
        $accountant = Role::create([
            'name' => 'accountant'
        ]);
        $accountant->givePermissionTo([
        ]);
        $chief = Role::create([
            'name' => 'chief'
        ]);
        $chief->givePermissionTo([

        ]);
        $lawyer = Role::create([
            'name' => 'lawyer'
        ]);
        $lawyer->givePermissionTo([

        ]);
    }
}
