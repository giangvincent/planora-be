<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum RoleStatus: string
{
    use ProvidesValues;

    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
