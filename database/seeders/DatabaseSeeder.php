<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Version;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Version::application();
        if (app()->isProduction()) {
            $this->call(ProductionSeeder::class);
        } else {
            $this->call(DevelopmentSeeder::class);
        }

    }
}
