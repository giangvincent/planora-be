<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\NotificationStatus;
use App\Jobs\SendTaskReminder;
use App\Models\Notification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class DispatchDueNotifications extends Command
{
    protected $signature = 'notifications:dispatch-due';

    protected $description = 'Dispatch reminder jobs for due notifications.';

    public function handle(): int
    {
        $now = CarbonImmutable::now();
        $count = 0;

        Notification::query()
            ->where('status', NotificationStatus::Pending->value)
            ->where('scheduled_for', '<=', $now)
            ->orderBy('scheduled_for')
            ->chunkById(100, function ($notifications) use (&$count): void {
                foreach ($notifications as $notification) {
                    SendTaskReminder::dispatch($notification->id);
                    $count++;
                }
            });

        $this->info(sprintf('Dispatched %d notification job(s).', $count));

        return self::SUCCESS;
    }
}
