<?php

use App\Models\Fixture;
use App\Models\Round;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Facades\Artisan;

function quinielaFixturePath(): string
{
    return base_path('tests/Fixtures/schedule-sample.json');
}

it('imports the season, rounds, teams and fixtures from the schedule json', function () {
    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);

    expect(Season::count())->toBe(1)
        ->and(Season::sole()->name)->toBe('2026/2027')
        ->and(Round::count())->toBe(2)
        ->and(Team::count())->toBe(3)
        ->and(Fixture::count())->toBe(3);

    $round1 = Round::where('number', 1)->sole();
    expect($round1->match_date->toDateString())->toBe('2026-08-15');

    $fixture1 = $round1->fixtures->sole();
    expect($fixture1->kickoff_at->toDateTimeString())->toBe('2026-08-15 19:30:00');
});

it('keeps kickoff_at in sync on re-import without touching scores', function () {
    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);

    $fixture = Fixture::first();
    $fixture->update(['home_score' => 2, 'away_score' => 1]);

    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);

    expect($fixture->refresh()->kickoff_at->toDateTimeString())->toBe('2026-08-15 19:30:00')
        ->and($fixture->home_score)->toBe(2)
        ->and($fixture->away_score)->toBe(1);
});

it('is idempotent when run twice', function () {
    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);
    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);

    expect(Season::count())->toBe(1)
        ->and(Round::count())->toBe(2)
        ->and(Team::count())->toBe(3)
        ->and(Fixture::count())->toBe(3);
});

it('does not clear a score already entered when re-imported', function () {
    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);

    $fixture = Fixture::first();
    $fixture->update(['home_score' => 2, 'away_score' => 1]);

    Artisan::call('quiniela:import', ['path' => quinielaFixturePath()]);

    expect($fixture->refresh()->home_score)->toBe(2)
        ->and($fixture->refresh()->away_score)->toBe(1);
});
