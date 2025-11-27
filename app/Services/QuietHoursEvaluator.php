<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;

class QuietHoursEvaluator
{
    public static function isWithinQuietHours(User $user, CarbonImmutable $moment): bool
    {
        $quietHours = self::quietHours($user);

        if ($quietHours === null) {
            return false;
        }

        $moment = $moment->setTimezone($user->timezone);

        [$startHour, $startMinute] = explode(':', $quietHours['start']);
        [$endHour, $endMinute] = explode(':', $quietHours['end']);

        $start = $moment->setTime((int) $startHour, (int) $startMinute);
        $end = $moment->setTime((int) $endHour, (int) $endMinute);

        if ($start->greaterThan($end)) {
            // Quiet hours wrap past midnight.
            return $moment->greaterThanOrEqualTo($start) || $moment->lt($end);
        }

        return $moment->between($start, $end, true);
    }

    public static function nextAllowedMoment(User $user, CarbonImmutable $moment): CarbonImmutable
    {
        $quietHours = self::quietHours($user);

        if ($quietHours === null) {
            return $moment;
        }

        $moment = $moment->setTimezone($user->timezone);
        [$endHour, $endMinute] = explode(':', $quietHours['end']);

        $end = $moment->setTime((int) $endHour, (int) $endMinute);

        if ($moment->greaterThan($end)) {
            $end = $end->addDay();
        }

        return $end->setTimezone('UTC');
    }

    private static function quietHours(User $user): ?array
    {
        $settings = $user->settings ?? [];

        return $settings['quietHours'] ?? null;
    }
}
