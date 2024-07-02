<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.s
     */
    public function run(): void
    {
        $this->call(DocumentSeeder::class);

        $this->call(PermissionSeeder::class);
        $this->call(ModuleSeeder::class);
        $this->call(ListPermissionSeeder::class);
        $this->call(DesPermissionSeeder::class);
        $this->call(ClousureReasonSeeder::class);
    }
}
