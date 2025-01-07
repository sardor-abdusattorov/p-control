<?php

namespace Database\Seeders;

use App\Enums\DepartmentEnum;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run()
    {
        foreach (DepartmentEnum::cases() as $departmentEnum) {
            Department::firstOrCreate(
                ['name' => $departmentEnum->value],
                ['name' => $departmentEnum->value, 'head_of_department' => null]
            );
        }
    }
}
