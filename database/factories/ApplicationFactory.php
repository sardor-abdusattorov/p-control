<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'project_id' => 16,
            'user_id' => User::inRandomOrder()->first()->id,
            'status_id' => $this->faker->randomElement([1, 2, 3]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
