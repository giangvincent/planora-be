<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum AutoCheckType: string
{
    use ProvidesValues;

    case Quiz = 'quiz';
    case TextKeywords = 'text_keywords';
    case Code = 'code';
    case Rating = 'rating';
}
