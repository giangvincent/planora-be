<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonInterface;

final class ResourceHelper
{
    private function __construct()
    {
    }

    public static function formatDate(?CarbonInterface $value): ?string
    {
        return $value?->toDateString();
    }

    public static function formatDateTime(?CarbonInterface $value): ?string
    {
        return $value?->toIso8601String();
    }
}
