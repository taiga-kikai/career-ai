<?php

namespace Database\Factories;

use App\Models\CareerApplication;
use App\Models\InterviewPreparation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewPreparation>
 */
class InterviewPreparationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_application_id' => CareerApplication::factory(),
            'self_introduction' => fake()->paragraph(),
            'motivation' => fake()->paragraph(),
            'student_experience' => fake()->paragraph(),
            'strengths' => fake()->paragraph(),
            'weaknesses' => fake()->paragraph(),
            'questions_to_ask' => fake()->paragraph(),
            'practice_questions' => [['question' => '自己紹介をお願いします', 'answer' => fake()->paragraph()]],
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
