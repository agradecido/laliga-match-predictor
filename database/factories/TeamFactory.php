<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = fake()->unique()->city().' FC';

        return [
            'name' => $name,
            'normalized_name' => Str::slug($name),
        ];
    }
}
