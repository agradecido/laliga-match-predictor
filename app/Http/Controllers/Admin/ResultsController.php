<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateScoresRequest;
use App\Models\Fixture;
use App\Models\Round;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ResultsController extends Controller
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

        return Inertia::render('Admin/ResultsIndex', [
            'rounds' => $rounds,
        ]);
    }

    public function edit(Round $round): Response
    {
        $round->load('fixtures.homeTeam', 'fixtures.awayTeam');

        return Inertia::render('Admin/ResultsEdit', [
            'round' => [
                'id' => $round->id,
                'number' => $round->number,
                'match_date' => $round->match_date->toDateString(),
            ],
            'fixtures' => $round->fixtures->map(fn (Fixture $fixture) => [
                'id' => $fixture->id,
                'home_team' => $fixture->homeTeam->only('name', 'normalized_name'),
                'away_team' => $fixture->awayTeam->only('name', 'normalized_name'),
                'home_score' => $fixture->home_score,
                'away_score' => $fixture->away_score,
            ]),
        ]);
    }

    public function update(UpdateScoresRequest $request, Round $round): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['scores'] as $score) {
                Fixture::whereKey($score['fixture_id'])->update([
                    'home_score' => $score['home_score'],
                    'away_score' => $score['away_score'],
                ]);
            }
        });

        return back();
    }
}
