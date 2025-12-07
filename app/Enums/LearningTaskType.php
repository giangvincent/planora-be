<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\ProvidesValues;

enum LearningTaskType: string
{
    use ProvidesValues;

    case Study = 'study';
    case Practice = 'practice';
    case Project = 'project';
    case Quiz = 'quiz';
}
