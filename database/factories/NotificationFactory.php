<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTransport;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(NotificationStatus::values());

        $scheduled = fake()->dateTimeBetween('now', '+3 days');
        $sentAt = null;

        if ($status === NotificationStatus::Sent->value) {
            $sentAt = (clone $scheduled)->modify('+'.fake()->numberBetween(5, 120).' minutes');
        }

        return [
            'user_id' => User::factory(),
            'task_id' => null,
            'channel' => fake()->randomElement(NotificationTransport::values()),
            'scheduled_for' => $scheduled,
            'sent_at' => $sentAt,
            'payload' => [
                'title' => fake()->sentence(3),
                'body' => fake()->sentence(),
            ],
            'status' => $status,
            'error' => $status === NotificationStatus::Failed->value ? fake()->sentence() : null,
        ];
    }
}
