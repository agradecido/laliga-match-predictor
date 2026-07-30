<?php

namespace Database\Factories;

use App\Models\Round;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Round>
 */
class RoundFactory extends Factory
{
    protected $model = Round::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'number' => fake()->numberBetween(1, 38),
            'match_date' => Carbon::now()->addWeek()->toDateString(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'match_date' => Carbon::now()->addDay()->toDateString(),
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'match_date' => Carbon::now()->subDay()->toDateString(),
        ]);
    }
}
