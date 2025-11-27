<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum NotificationTransport: string
{
    use ProvidesValues;

    case Push = 'push';
    case Email = 'email';
}
