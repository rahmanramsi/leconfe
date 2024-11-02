<?php

namespace Database\Factories;

use App\Models\ScheduledConference;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduledConference>
 */
class ScheduledConferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::parse(fake()->date());

        return [
            'title' => $date->year,
            'path' => Str::slug($date->year),
            'date_start' => $date,
            'date_end' => $date->copy()->addDays(3),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ScheduledConference $scheduledConference) {
            $scheduledConference->setManyMeta([
                'summary' => fake()->paragraphs(3, true),
                'about' => fake()->paragraphs(3, true),
            ]);
        });
    }
}
