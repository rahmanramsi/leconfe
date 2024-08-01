<?php

namespace Database\Seeders\Developments;

use App\Models\Registration;
use App\Models\RegistrationType;
use Illuminate\Database\Seeder;
use App\Models\ScheduledConference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;

class RegistrationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScheduledConference::lazy()->each(function (ScheduledConference $scheduled_conference) {
            RegistrationType::factory() // make the factory
                ->count(2)
                ->for($scheduled_conference)
                ->state(new Sequence(
                    ['type' => 'Pemakalah'],
                    ['type' => 'Peserta'],
                ))
                ->create();
        });
    }
}
