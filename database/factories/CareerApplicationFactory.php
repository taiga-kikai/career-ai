<?php

namespace Database\Factories;

use App\Models\CareerApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerApplication>
 */
class CareerApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(['main', 'summer', 'autumn']),
            'company' => fake()->company(),
            'program' => fake()->optional()->words(2, true),
            'status' => '応募状況未更新',
            'detail' => fake()->optional()->sentence(),
        ];
    }
}
