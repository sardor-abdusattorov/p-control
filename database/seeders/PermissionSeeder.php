<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::create(['name' => 'manage rbac']);
        Permission::create(['name' => 'manage currency']);
        Permission::create(['name' => 'manage status']);
        Permission::create(['name' => 'manage position']);
        Permission::create(['name' => 'manage department']);

        //TASKS
        Permission::create(['name' => 'create task']);
        Permission::create(['name' => 'update task']);
        Permission::create(['name' => 'delete task']);
        Permission::create(['name' => 'view task']);
        Permission::create(['name' => 'view all tasks']);
        Permission::create(['name' => 'start task']);
        Permission::create(['name' => 'complete task']);

        //Applications
        Permission::create(['name' => 'create application']);
        Permission::create(['name' => 'submit application']);
        Permission::create(['name' => 'update application']);
        Permission::create(['name' => 'delete application']);
        Permission::create(['name' => 'view application']);
        Permission::create(['name' => 'view all applications']);
        Permission::create(['name' => 'approve application']);

        //PROJECTS
        Permission::create(['name' => 'create project']);
        Permission::create(['name' => 'update project']);
        Permission::create(['name' => 'delete project']);
        Permission::create(['name' => 'view project']);

        //CONTRACTS
        Permission::create(['name' => 'create contract']);
        Permission::create(['name' => 'submit contract']);
        Permission::create(['name' => 'update contract']);
        Permission::create(['name' => 'delete contract']);
        Permission::create(['name' => 'view contract']);
        Permission::create(['name' => 'view all contracts']);
        Permission::create(['name' => 'approve contract']);

        Permission::create(['name' => 'view logs']);
    }
}
