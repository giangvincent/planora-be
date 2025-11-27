<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum IntegrationStatus: string
{
    use ProvidesValues;

    case Connected = 'connected';
    case Revoked = 'revoked';
    case Error = 'error';
}
