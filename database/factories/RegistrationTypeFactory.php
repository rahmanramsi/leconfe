<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Squire\Models\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistrationType>
 */
class RegistrationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $closedDate = Carbon::parse(now())->addDays(rand(15, 60)); // 15 days to 2 month

        return [
            'cost' => rand(10000000, 50000000),
            'quota' => rand(5, 50),
            'currency' => Currency::get()->pluck('id')->random(),
            'opened_at' => now(),
            'closed_at' => $closedDate,
        ];
    }
}
