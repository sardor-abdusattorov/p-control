<?php

namespace Database\Factories;

use App\Enums\PositionEnum;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => PositionEnum::PROJECT_MANAGER->value // Default value; will be overridden by method
        ];
    }

    /**
     * Set the name from an Enum.
     */
    public function withEnumName(PositionEnum $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name->value,
        ]);
    }
}
