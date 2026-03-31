<?php

namespace Database\Factories;

use App\Models\Matches;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matches>
 */
class MatchesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'home_team' => ['en' => fake()->country(), 'ka' => 'გუნდი ა'],
            'away_team' => ['en' => fake()->country(), 'ka' => 'გუნდი ბ'],
            'stadium' => ['en' => fake()->city() . ' Stadium', 'ka' => 'სტადიონი'],
            'match_date' => now()->addDays(rand(1, 30)),
            'capacity' => fake()->numberBetween(30000, 80000),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Matches $match) {
            // ...
        })->afterCreating(function (Matches $match) {
            foreach ($match->ticketCategories as $category) {
                $category->available_count = rand(1, $category->seat_count);
                $category->save();
            }
        });
    }
}
