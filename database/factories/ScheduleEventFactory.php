<?php

namespace Database\Factories;

use App\Models\CareerApplication;
use App\Models\ScheduleEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleEvent>
 */
class ScheduleEventFactory extends Factory
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
            'event_type' => fake()->randomElement(['briefing', 'interview', 'aptitude', 'group_discussion', 'es_deadline', 'other']),
            'title' => fake()->sentence(4),
            'starts_at' => fake()->dateTimeBetween('now', '+1 month'),
            'ends_at' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'location' => fake()->optional()->city(),
            'meeting_url' => null,
            'notes' => fake()->optional()->sentence(),
            'is_completed' => false,
        ];
    }
}
