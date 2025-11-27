<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        collect(['admin', 'support', 'user'])->each(function (string $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web'],
                []
            );
        });
    }
}
