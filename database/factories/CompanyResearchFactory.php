<?php

namespace Database\Factories;

use App\Models\CareerApplication;
use App\Models\CompanyResearch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyResearch>
 */
class CompanyResearchFactory extends Factory
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
            'industry' => fake()->word(),
            'business_outline' => fake()->paragraph(),
            'products_services' => fake()->paragraph(),
            'strengths' => fake()->paragraph(),
            'competitors' => fake()->paragraph(),
            'culture' => fake()->paragraph(),
            'recent_topics' => fake()->paragraph(),
            'motivation' => fake()->paragraph(),
            'sources' => [['label' => '企業公式サイト', 'url' => fake()->url()]],
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
