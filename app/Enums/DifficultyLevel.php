<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum DifficultyLevel: string
{
    use ProvidesValues;

    case Intro = 'intro';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
}
