<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum TaskStatus: string
{
    use ProvidesValues;

    case Pending = 'pending';
    case Done = 'done';
    case Skipped = 'skipped';
}
