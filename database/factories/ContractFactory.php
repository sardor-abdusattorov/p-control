<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;


class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'contract_number' => $this->faker->unique()->word,
            'title' => $this->faker->sentence,
            'project_id' => 1,
            'application_id' => $this->faker->randomElement([null, $this->faker->randomNumber()]),
            'user_id' => 1,
            'status' => $this->faker->randomElement([1, 2, 3]),
            'currency_id' => 2,
            'budget_sum' => $this->faker->randomFloat(2, 1000, 1000000),
            'deadline' => $this->faker->date(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
