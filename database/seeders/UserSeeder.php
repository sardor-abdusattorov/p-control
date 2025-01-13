<?php

namespace Database\Seeders;

use App\Enums\DepartmentEnum;
use App\Enums\PositionEnum;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DepartmentsSeeder::class,
            PositionSeeder::class,
        ]);

        $departments = Department::all();
        $itDepartment = $departments->where("name", DepartmentEnum::IT_DEPARTMENT->value)->first();
        $creativeProjectsDepartment = $departments->where("name", DepartmentEnum::CREATIVE_PROJECTS->value)->first();
        $accountantDepartment = $departments->where("name", DepartmentEnum::ACCOUNTING->value)->first();

        $positions = Position::all();
        $adminPosition = $positions->where("name", PositionEnum::ADMIN->value)->first();
        $managerPosition = $positions->where("name", PositionEnum::PROJECT_MANAGER->value)->first();
        $staffPosition = $positions->where("name", PositionEnum::STAFF->value)->first();

        $superadmin = User::create([
            'name'              => 'Sardor Abdusattorov',
            'email'             => 'mr.silverwind1998@gmail.com',
            'department_id'     => $itDepartment->id,
            'position_id'       => $adminPosition->id,
            'password'          => bcrypt('123456'),
            'email_verified_at' => date('Y-m-d H:i')
        ]);
        $superadmin->assignRole('superadmin');

        $manager = User::create([
            'name'              => 'Manager',
            'email'             => 'manager@company.com',
            'department_id'     => $creativeProjectsDepartment->id,
            'position_id'       => $managerPosition->id,
            'password'          => bcrypt('manager123'),
            'email_verified_at' => date('Y-m-d H:i')
        ]);
        $manager->assignRole('manager');

        $accountant = User::create([
            'name'              => 'Accountant',
            'email'             => 'accountant@company.com',
            'department_id'     => $accountantDepartment->id,
            'position_id'       => $staffPosition->id,
            'password'          => bcrypt('accountant123'),
            'email_verified_at' => date('Y-m-d H:i')
        ]);
        $accountant->assignRole('lawyer');


    }
}
