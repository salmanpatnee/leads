<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => \App\Models\Site::factory(),
            'form_name' => fake()->randomElement(['Contact Form 1', 'Newsletter Signup', 'Quote Request']),
            'form_data' => [
                'your-name' => fake()->name(),
                'your-email' => fake()->safeEmail(),
                'your-message' => fake()->paragraph(),
            ],
            'status' => 'new',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'submitted_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the lead has been contacted.
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'contacted',
        ]);
    }

    /**
     * Indicate that the lead has been converted.
     */
    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'converted',
        ]);
    }
}
