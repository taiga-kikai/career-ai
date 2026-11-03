<?php

namespace Database\Factories;

use App\Models\ApplicationDocument;
use App\Models\CareerApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationDocument>
 */
class ApplicationDocumentFactory extends Factory
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
            'title' => fake()->randomElement(['エントリーシート', '志望動機', '学生時代に力を入れたこと']),
            'raw_text' => fake()->paragraphs(3, true),
            'questions' => [
                ['question' => '学生時代に力を入れたこと', 'answer' => fake()->paragraph()],
            ],
        ];
    }
}
