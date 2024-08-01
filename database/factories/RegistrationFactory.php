<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\User;
use Squire\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegistrationType>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomUser = User::pluck('id')->random();
        return [
            'user_id' => $randomUser,
            'is_trashed' => false,
        ];
    }
}
