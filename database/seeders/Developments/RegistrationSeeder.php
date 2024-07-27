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
        RegistrationType::lazy()->each(function (RegistrationType $registration_type) {
            Registration::factory()
                ->count(5)
                ->for($registration_type)
                ->create([
                    'scheduled_conference_id' => $registration_type->scheduled_conference_id,
                ]);
        });
    }
}
