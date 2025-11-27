<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.4)->paragraph(),
            'target_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'status' => fake()->randomElement(GoalStatus::values()),
            'progress' => fake()->numberBetween(0, 100),
            'color' => fake()->optional()->safeHexColor(),
        ];
    }
}
