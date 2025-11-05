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

        $positions = Position::all();
        $adminPosition = $positions->where("name", PositionEnum::ADMIN->value)->first();
        $superadmin = User::create([
            'name'              => 'Sardor Abdusattorov',
            'email'             => 'mr.silverwind1998@gmail.com',
            'department_id'     => $itDepartment->id,
            'position_id'       => $adminPosition->id,
            'password'          => bcrypt('123456'),
            'email_verified_at' => date('Y-m-d H:i')
        ]);
        $superadmin->assignRole('superadmin');
    }
}
