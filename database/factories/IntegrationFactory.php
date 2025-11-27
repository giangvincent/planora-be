<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IntegrationProvider;
use App\Enums\IntegrationStatus;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Integration>
 */
class IntegrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(IntegrationProvider::values());

        $status = fake()->randomElement(IntegrationStatus::values());

        return [
            'user_id' => User::factory(),
            'provider' => $provider,
            'access_token' => Str::random(40),
            'refresh_token' => fake()->optional()->sha1(),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'settings' => [
                'syncDirection' => fake()->randomElement(['one-way', 'two-way']),
                'filters' => [
                    'includeCompleted' => fake()->boolean(),
                ],
            ],
            'status' => $status,
        ];
    }
}
