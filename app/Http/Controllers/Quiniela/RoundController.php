<?php

namespace App\Http\Controllers\Quiniela;

use App\Http\Controllers\Controller;
use App\Models\Round;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoundController extends Controller
{
    public function index(): Response
    {
        $rounds = Round::query()
            ->orderBy('number')
            ->get()
            ->map(fn (Round $round) => [
                'id' => $round->id,
                'number' => $round->number,
                'match_date' => $round->match_date->toDateString(),
                'is_locked' => $round->isLocked(),
            ]);

        return Inertia::render('Quiniela/Index', [
            'rounds' => $rounds,
        ]);
    }

    public function show(Request $request, Round $round): Response
    {
        $round->load('fixtures.homeTeam', 'fixtures.awayTeam');

        $predictions = $request->user()
            ->predictions()
            ->whereIn('fixture_id', $round->fixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        return Inertia::render('Quiniela/Show', [
            'round' => [
                'id' => $round->id,
                'number' => $round->number,
                'match_date' => $round->match_date->toDateString(),
                'is_locked' => $round->isLocked(),
            ],
            'fixtures' => $round->fixtures->map(fn ($fixture) => [
                'id' => $fixture->id,
                'home_team' => $fixture->homeTeam->only('name', 'normalized_name'),
                'away_team' => $fixture->awayTeam->only('name', 'normalized_name'),
                'choice' => $predictions->get($fixture->id)?->choice,
            ]),
        ]);
    }
}
