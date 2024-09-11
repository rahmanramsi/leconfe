<?php

namespace Database\Seeders\Developments;

use App\Models\Conference;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Conference::lazy()->each(function (Conference $conference) {
            Topic::factory()->count(100)->for($conference)->create();
        });
    }
}
