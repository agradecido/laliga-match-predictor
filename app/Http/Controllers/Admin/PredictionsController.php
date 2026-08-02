<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePredictionsRequest;
use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PredictionsController extends Controller
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

        return Inertia::render('Admin/PredictionsIndex', [
            'rounds' => $rounds,
        ]);
    }

    public function edit(Round $round): Response
    {
        $round->load('fixtures.homeTeam', 'fixtures.awayTeam');

        $predictionsByUser = Prediction::query()
            ->whereIn('fixture_id', $round->fixtures->pluck('id'))
            ->get()
            ->groupBy('user_id');

        $users = User::query()->orderBy('name')->get();

        return Inertia::render('Admin/PredictionsEdit', [
            'round' => [
                'id' => $round->id,
                'number' => $round->number,
                'match_date' => $round->match_date->toDateString(),
            ],
            'fixtures' => $round->fixtures->map(fn (Fixture $fixture) => [
                'id' => $fixture->id,
                'home_team' => $fixture->homeTeam->only('name', 'normalized_name'),
                'away_team' => $fixture->awayTeam->only('name', 'normalized_name'),
                'result_sign' => $fixture->resultSign(),
            ]),
            'players' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'picks' => $round->fixtures->map(fn (Fixture $fixture) => [
                    'fixture_id' => $fixture->id,
                    'choice' => $predictionsByUser->get($user->id, collect())
                        ->firstWhere('fixture_id', $fixture->id)
                        ?->choice,
                ]),
            ]),
        ]);
    }

    public function update(UpdatePredictionsRequest $request, Round $round): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['entries'] as $entry) {
                $keys = [
                    'user_id' => $entry['user_id'],
                    'fixture_id' => $entry['fixture_id'],
                ];

                if ($entry['choice'] === null) {
                    Prediction::where($keys)->delete();

                    continue;
                }

                Prediction::updateOrCreate($keys, ['choice' => $entry['choice']]);
            }
        });

        return back();
    }
}
