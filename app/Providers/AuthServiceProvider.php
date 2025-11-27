<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\CalendarEntry;
use App\Models\Goal;
use App\Models\Task;
use App\Policies\CalendarEntryPolicy;
use App\Policies\GoalPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Goal::class => GoalPolicy::class,
        Task::class => TaskPolicy::class,
        CalendarEntry::class => CalendarEntryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
