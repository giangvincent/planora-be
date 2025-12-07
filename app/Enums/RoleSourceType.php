<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum RoleSourceType: string
{
    use ProvidesValues;

    case Manual = 'manual';
    case Ai = 'ai';
}
