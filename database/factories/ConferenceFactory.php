<?php

namespace Database\Factories;

use App\Models\Conference;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Squire\Models\Country;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conference>
 */
class ConferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Conference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Conference';
        $city = fake()->city();

        return [
            'name' => "$name $city",
            'path' => Str::slug($city),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Conference $conference) {
            $conference->setManyMeta([
                'publisher_name' => fake()->company(),
                'publisher_place' => fake()->city(),
                'theme' => fake()->sentence(),
                'affiliation' => fake()->company(),
                'country' => Country::inRandomOrder()->first()->id,
                'location' => fake()->city(),
                'page_footer' => view('frontend.examples.footer')->render(),
                'summary' => fake()->paragraph(),
                'about' => fake()->paragraphs(3, true),
            ]);
        });
    }
}
