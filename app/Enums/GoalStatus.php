<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum GoalStatus: string
{
    use ProvidesValues;

    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
}
