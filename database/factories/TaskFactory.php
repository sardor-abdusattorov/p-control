<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;


class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => $this->faker->numberBetween(1, 3),
            'priority' => $this->faker->numberBetween(1, 3),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'user_id' => 1,
            'project_id' => 1,
            'assigned_user' => 1,
        ];
    }
}
