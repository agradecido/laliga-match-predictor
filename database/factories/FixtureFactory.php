<?php

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Round;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    public function definition(): array
    {
        return [
            'round_id' => Round::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'kickoff_at' => now()->addWeek(),
            'home_score' => null,
            'away_score' => null,
        ];
    }

    public function played(int $homeScore, int $awayScore): static
    {
        return $this->state(fn () => [
            'home_score' => $homeScore,
            'away_score' => $awayScore,
        ]);
    }
}
