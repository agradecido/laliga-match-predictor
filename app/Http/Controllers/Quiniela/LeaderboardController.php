<?php

namespace App\Http\Controllers\Quiniela;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(): Response
    {
        $leaderboard = User::with('predictions.fixture')
            ->get()
            ->map(fn (User $user) => [
                'name' => $user->name,
                'points' => $user->predictions
                    ->filter(fn ($prediction) => $prediction->choice === $prediction->fixture->resultSign())
                    ->count(),
            ])
            ->sortByDesc('points')
            ->values();

        return Inertia::render('Quiniela/Leaderboard', [
            'leaderboard' => $leaderboard,
        ]);
    }
}
