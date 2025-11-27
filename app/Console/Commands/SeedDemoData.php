<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo';

    protected $description = 'Seed the database with demo data.';

    public function handle(): int
    {
        Artisan::call('db:seed', ['--class' => DemoDataSeeder::class]);

        $this->info('Demo data seeded.');

        return self::SUCCESS;
    }
}
