<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Developments\CommitteeSeeder;
use Database\Seeders\Developments\ConferenceSeeder;
use Database\Seeders\Developments\ProceedingSeeder;
use Database\Seeders\Developments\ScheduledConferenceSeeder;
use Database\Seeders\Developments\SpeakerSeeder;
use Database\Seeders\Developments\TimelineSeeder;
use Database\Seeders\Developments\TopicSeeder;
use Database\Seeders\Developments\UserSeeder;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
        $this->call(ConferenceSeeder::class);
        $this->call(ScheduledConferenceSeeder::class);
        $this->call(TopicSeeder::class);
        $this->call(ProceedingSeeder::class);
        $this->call(TimelineSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(SpeakerSeeder::class);
        $this->call(UserSeeder::class);
    }
}
