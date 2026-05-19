<?php

namespace Database\Factories;

use App\Enums\DepartmentEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => DepartmentEnum::IT_DEPARTMENT->value,
            'head_of_department' => null
        ];
    }

    public function withEnumName(DepartmentEnum $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name->value,
        ]);
    }
}
