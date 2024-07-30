<?php

namespace Database\Seeders\Developments;

use App\Models\Registration;
use App\Models\RegistrationType;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RegistrationType::lazy()->each(function (RegistrationType $registrationType) {
            Registration::factory()
                ->count(5)
                ->for($registrationType)
                ->create([
                    'scheduled_conference_id' => $registrationType->scheduled_conference_id,
                ]);
        });
    }
}
