<?php

namespace App\Support;

use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Collection;

class Leaderboard
{
    public static function compute(?Season $season = null): Collection
    {
        return User::with('predictions.fixture.round')
            ->get()
            ->map(fn (User $user) => [
                'user_id' => $user->id,
                'name' => $user->name,
                'points' => $user->predictions
                    ->filter(fn ($prediction) => $season === null || $prediction->fixture->round->season_id === $season->id)
                    ->filter(fn ($prediction) => $prediction->choice === $prediction->fixture->resultSign())
                    ->count(),
            ])
            ->sortByDesc('points')
            ->values();
    }
}
