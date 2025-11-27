<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CalendarEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEntry>
 */
class CalendarEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allDay = fake()->boolean(20);
        $start = fake()->dateTimeBetween('+0 days', '+2 weeks');
        $end = $allDay
            ? null
            : (clone $start)->modify('+'.fake()->numberBetween(1, 4).' hours');

        return [
            'user_id' => User::factory(),
            'task_id' => null,
            'start_at' => $start,
            'end_at' => $end,
            'all_day' => $allDay,
            'is_generated' => fake()->boolean(30),
        ];
    }
}
