<?php

namespace Database\Factories;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prediction>
 */
class PredictionFactory extends Factory
{
    protected $model = Prediction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fixture_id' => Fixture::factory(),
            'choice' => fake()->randomElement(['1', 'X', '2']),
        ];
    }
}
