<?php

namespace Database\Factories;

use App\Models\PotPayment;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PotPayment>
 */
class PotPaymentFactory extends Factory
{
    protected $model = PotPayment::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'user_id' => User::factory(),
            'paid_at' => null,
            'marked_by_id' => null,
        ];
    }
}
