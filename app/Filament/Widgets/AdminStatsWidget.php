<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AdminStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();

        $totalUsers = User::query()->count();
        $recentActiveUsers = User::query()
            ->where('last_login_at', '>=', $weekStart)
            ->count();

        $tasksCreatedThisWeek = Task::query()
            ->where('created_at', '>=', $weekStart)
            ->count();

        $completedTasks = Task::query()->where('status', 'done')->count();
        $totalTasks = Task::query()->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        $failedNotifications = Notification::query()
            ->where('status', 'failed')
            ->count();

        return [
            Stat::make('Active Users (7d)', "{$recentActiveUsers} / {$totalUsers}")
                ->description('Users who logged in this week'),
            Stat::make('Tasks Created (week)', (string) $tasksCreatedThisWeek),
            Stat::make('Completion Rate', "{$completionRate}%")
                ->description('All-time task completion'),
            Stat::make('Failed Notifications', (string) $failedNotifications)
                ->color($failedNotifications > 0 ? 'danger' : 'success'),
        ];
    }
}
