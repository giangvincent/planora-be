<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum LearningTaskStatus: string
{
    use ProvidesValues;

    case Pending = 'pending';
    case Completed = 'completed';
    case Skipped = 'skipped';
}
