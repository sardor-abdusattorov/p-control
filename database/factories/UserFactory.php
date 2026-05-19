<?php

namespace Database\Factories;

use App\Enums\DepartmentEnum;
use App\Enums\PositionEnum;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'department_id' => Department::factory()
                ->withEnumName(DepartmentEnum::IT_DEPARTMENT)
                ->create(),
            'position_id' => Position::factory()
                ->withEnumName(PositionEnum::ADMIN)
                ->create(),
            'telegram_id' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
