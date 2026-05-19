<?php

namespace Database\Factories;

use App\Enums\PositionEnum;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'name' => PositionEnum::PROJECT_MANAGER->value
        ];
    }

    public function withEnumName(PositionEnum $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name->value,
        ]);
    }
}
