<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoundRequest;
use App\Http\Requests\Admin\UpdateRoundRequest;
use App\Models\Round;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoundController extends Controller
{
    public function index(): Response
    {
        $rounds = Round::query()
            ->with('season')
            ->orderBy('season_id')
            ->orderBy('number')
            ->get()
            ->map(fn (Round $round) => [
                'id' => $round->id,
                'season_id' => $round->season_id,
                'season_name' => $round->season->name,
                'number' => $round->number,
                'match_date' => $round->match_date->toDateString(),
                'is_locked' => $round->isLocked(),
            ]);

        return Inertia::render('Admin/RoundsIndex', [
            'rounds' => $rounds,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RoundsCreate', [
            'seasons' => Season::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreRoundRequest $request): RedirectResponse
    {
        Round::create($request->validated());

        return redirect()->route('admin.rounds.index');
    }

    public function edit(Round $round): Response
    {
        return Inertia::render('Admin/RoundsEdit', [
            'round' => [
                'id' => $round->id,
                'season_id' => $round->season_id,
                'number' => $round->number,
                'match_date' => $round->match_date->toDateString(),
            ],
            'seasons' => Season::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateRoundRequest $request, Round $round): RedirectResponse
    {
        $round->update($request->validated());

        return redirect()->route('admin.rounds.index');
    }
}
