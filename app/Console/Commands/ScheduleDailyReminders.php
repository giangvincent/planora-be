<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendUserDailyDigest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ScheduleDailyReminders extends Command
{
    protected $signature = 'notifications:send-daily-digests {--timezone=}';

    protected $description = 'Dispatch daily digest notifications for users hitting their morning window.';

    public function handle(): int
    {
        $nowUtc = CarbonImmutable::now('UTC');
        $targetTimezones = $this->timezonesToProcess($nowUtc);

        if ($targetTimezones->isEmpty()) {
            $this->info('No timezones matched the digest window.');

            return self::SUCCESS;
        }

        $dispatched = 0;

        $users = User::query()
            ->whereIn('timezone', $targetTimezones->all())
            ->get();

        foreach ($users as $user) {
            $localDate = $nowUtc->setTimezone($user->timezone)->toDateString();
            SendUserDailyDigest::dispatch($user->id, $localDate);
            $dispatched++;
        }

        $this->info(sprintf('Dispatched %d daily digest job(s).', $dispatched));

        return self::SUCCESS;
    }

    private function timezonesToProcess(CarbonImmutable $nowUtc)
    {
        if ($timezone = $this->option('timezone')) {
            return collect([$timezone]);
        }

        return User::query()
            ->select('timezone')
            ->distinct()
            ->pluck('timezone')
            ->filter(function (?string $timezone) use ($nowUtc): bool {
                if (! $timezone) {
                    return false;
                }

                $localTime = $nowUtc->setTimezone($timezone);
                $minutes = (int) $localTime->format('H') * 60 + (int) $localTime->format('i');

                // Target window: 07:25 - 07:35 local time.
                return $minutes >= (7 * 60 + 25) && $minutes <= (7 * 60 + 35);
            });
    }
}
