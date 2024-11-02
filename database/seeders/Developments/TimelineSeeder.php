<?php

namespace Database\Seeders\Developments;

use App\Models\ScheduledConference;
use App\Models\Timeline;
use Illuminate\Database\Seeder;

class TimelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScheduledConference::query()
            ->with(['conference'])
            ->lazy()
            ->each(function (ScheduledConference $scheduledConference) {
                Timeline::create([
                    'scheduled_conference_id' => $scheduledConference->getKey(),
                    'name' => 'Submission Open',
                    'date' => now()->subDay(),
                    'type' => Timeline::TYPE_SUBMISSION_OPEN,
                ]);
            });
    }
}
