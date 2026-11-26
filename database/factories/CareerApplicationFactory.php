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
     * 区分・架空企業名・選考状況を持つ、テスト用応募先の初期値を作る。
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
