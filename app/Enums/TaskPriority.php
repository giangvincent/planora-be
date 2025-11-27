<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum TaskPriority: string
{
    use ProvidesValues;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
