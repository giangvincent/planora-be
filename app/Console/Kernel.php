<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\DispatchDueNotifications;
use App\Console\Commands\ScheduleDailyReminders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ScheduleDailyReminders::class)->hourly();
        $schedule->command(DispatchDueNotifications::class)->everyMinute()->withoutOverlapping();
    }
}
