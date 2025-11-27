<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created_completed_and_skipped(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/tasks', [
            'title' => 'Lifecycle Task',
            'allDay' => false,
            'dueAt' => Carbon::now()->addDay()->toIso8601String(),
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $create->assertCreated();

        $taskId = Task::query()->first()->id;

        $this->postJson("/api/v1/tasks/{$taskId}/complete")
            ->assertOk();

        $this->assertTrue(Task::find($taskId)->status->value === 'done');

        $this->postJson("/api/v1/tasks/{$taskId}/skip")
            ->assertOk();

        $this->assertTrue(Task::find($taskId)->status->value === 'skipped');
    }
}
