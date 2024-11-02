<?php

namespace Database\Factories;

use App\Models\ScheduledConference;
use App\Models\SpeakerRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Speaker>
 */
class SpeakerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomSerie = ScheduledConference::pluck('id')->random();

        return [
            'scheduled_conference_id' => $randomSerie,
            'speaker_role_id' => SpeakerRole::withoutGlobalScopes()->where('scheduled_conference_id', $randomSerie)->pluck('id')->random(),
            'given_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
            'public_name' => fake()->name(),
            'email' => fake()->email(),
        ];
    }
}
