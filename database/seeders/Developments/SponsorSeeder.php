<?php

namespace Database\Seeders\Developments;

use App\Models\Conference;
use App\Models\ScheduledConference;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Serie::lazy()->each(function (Serie $scheduledConference) {
        //     Sponsor::factory()->count(10)->for($scheduledConference)->create();
        // });
    }
}
