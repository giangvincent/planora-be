<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CalendarEntry;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_endpoint_returns_entries_in_range(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()
            ->for($user, 'user')
            ->create([
                'due_at' => Carbon::now()->addHour(),
                'status' => 'pending',
            ]);

        CalendarEntry::factory()
            ->for($user, 'user')
            ->for($task, 'task')
            ->create([
                'start_at' => Carbon::now()->addHours(2),
                'end_at' => Carbon::now()->addHours(3),
            ]);

        $response = $this->getJson('/api/v1/calendar?start=' . Carbon::now()->subDay()->toIso8601String() . '&end=' . Carbon::now()->addDay()->toIso8601String());

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
    }
}
