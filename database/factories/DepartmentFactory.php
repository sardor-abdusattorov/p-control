<?php

namespace Database\Factories;

use App\Enums\DepartmentEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => DepartmentEnum::IT_DEPARTMENT->value,
            'head_of_department' => null
        ];
    }

    /**
     * Set the name from an Enum.
     */
    public function withEnumName(DepartmentEnum $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name->value,
        ]);
    }
}
