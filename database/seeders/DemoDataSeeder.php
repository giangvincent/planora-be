<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IntegrationProvider;
use App\Enums\IntegrationStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\CalendarEntry;
use App\Models\Goal;
use App\Models\Integration;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Planora Admin',
            'email' => 'admin@planora.test',
            'password' => Hash::make('password'),
            'settings' => [
                'quietHours' => ['start' => '22:00', 'end' => '07:00'],
                'dailyDigest' => true,
            ],
        ]);

        $support = User::factory()->create([
            'name' => 'Planora Support',
            'email' => 'support@planora.test',
            'password' => Hash::make('password'),
            'notification_channel' => NotificationChannel::Email->value,
        ]);

        $additionalUsers = User::factory()->count(3)->create();

        $users = Collection::make([$admin, $support])->merge($additionalUsers);

        $admin->assignRole('admin');
        $support->assignRole('support');
        $additionalUsers->each(fn (User $user) => $user->assignRole('user'));

        $users->each(function (User $user): void {
            $goals = Goal::factory()
                ->count(fake()->numberBetween(2, 4))
                ->for($user, 'user')
                ->create();

            $goals->each(function (Goal $goal) use ($user): void {
                $tasks = Task::factory()
                    ->count(fake()->numberBetween(3, 6))
                    ->for($user, 'user')
                    ->for($goal, 'goal')
                    ->create();

                $tasks->each(function (Task $task) use ($user): void {
                    if ($task->due_at) {
                        CalendarEntry::factory()
                            ->for($user, 'user')
                            ->for($task, 'task')
                            ->state(fn () => [
                                'start_at' => $task->due_at,
                                'end_at' => $task->due_at?->addMinutes($task->estimated_minutes ?? 60),
                                'all_day' => $task->all_day,
                                'is_generated' => true,
                            ])
                            ->create();
                    }

                    Notification::factory()
                        ->for($user, 'user')
                        ->for($task, 'task')
                        ->state(function () use ($task) {
                            $scheduled = $task->due_at?->subHour() ?? now()->addMinutes(fake()->numberBetween(10, 120));

                            return [
                                'scheduled_for' => $scheduled,
                                'status' => NotificationStatus::Pending->value,
                            ];
                        })
                        ->create();
                });
            });

            Task::factory()
                ->count(fake()->numberBetween(2, 4))
                ->for($user, 'user')
                ->create();

            collect(IntegrationProvider::values())->shuffle()
                ->take(2)
                ->each(function (string $provider) use ($user): void {
                    Integration::factory()
                        ->for($user, 'user')
                        ->state(fn () => [
                            'status' => fake()->randomElement(IntegrationStatus::values()),
                            'provider' => $provider,
                            'settings' => [
                                'syncWindow' => fake()->randomElement(['7d', '14d', '30d']),
                                'webhookUrl' => fake()->url(),
                            ],
                        ])
                        ->create();
                });
        });
    }
}
