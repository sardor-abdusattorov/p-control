<?php

namespace Database\Factories;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Project::class;

    public function definition()
    {
        return [
            'project_number' => $this->faker->unique()->numerify('PROJECT-####'),
            'title' => $this->faker->word(),
            'budget_sum' => $this->faker->randomFloat(2, 1000, 100000),
            'budget_balance' => $this->faker->randomFloat(2, 500, 50000),
            'project_year' => now(),
            'user_id' => 1,
            'status_id' => 1,
            'currency_id' => 1,
            'deadline' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }
}
