<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allDay = fake()->boolean(25);
        $dueAt = $allDay ? null : fake()->optional()->dateTimeBetween('now', '+2 weeks');
        $dueDateSource = $allDay
            ? fake()->optional()->dateTimeBetween('now', '+2 weeks')
            : $dueAt;

        return [
            'user_id' => User::factory(),
            'goal_id' => null,
            'title' => fake()->sentence(6),
            'notes' => fake()->optional(0.5)->paragraph(),
            'status' => fake()->randomElement(TaskStatus::values()),
            'priority' => fake()->randomElement(TaskPriority::values()),
            'estimated_minutes' => fake()->optional(0.7)->numberBetween(15, 180),
            'actual_minutes' => fake()->optional(0.3)->numberBetween(10, 240),
            'due_date' => $dueDateSource?->format('Y-m-d'),
            'due_at' => $allDay ? null : $dueAt,
            'all_day' => $allDay,
            'repeat_rule' => null,
        ];
    }
}
