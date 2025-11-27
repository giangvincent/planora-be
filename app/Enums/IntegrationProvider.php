<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum IntegrationProvider: string
{
    use ProvidesValues;

    case GoogleCalendar = 'gcal';
    case Notion = 'notion';
    case Todoist = 'todoist';
}
