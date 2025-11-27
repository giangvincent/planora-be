<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum NotificationStatus: string
{
    use ProvidesValues;

    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
