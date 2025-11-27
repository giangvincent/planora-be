<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendUserDailyDigest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DigestScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_digest_command_dispatches_jobs_based_on_timezone(): void
    {
        User::factory()->create([
            'timezone' => 'Asia/Ho_Chi_Minh',
        ]);

        Bus::fake();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2025-01-01 00:30:00', 'UTC'));

        Artisan::call('notifications:send-daily-digests');

        Bus::assertDispatched(SendUserDailyDigest::class);

        CarbonImmutable::setTestNow();
    }
}
