<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum RoleVisibility: string
{
    use ProvidesValues;

    case Private = 'private';
    case Unlisted = 'unlisted';
    case Public = 'public';
}
