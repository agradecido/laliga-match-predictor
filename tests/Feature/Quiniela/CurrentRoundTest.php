<?php

use App\Models\Fixture;
use App\Models\Round;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('closes 24 hours after the last fixture kickoff', function () {
    $round = Round::factory()->create();

    Fixture::factory()->for($round)->create(['kickoff_at' => '2026-08-16 17:00:00']);
    Fixture::factory()->for($round)->create(['kickoff_at' => '2026-08-17 21:00:00']);

    expect($round->closesAt()->toDateTimeString())->toBe('2026-08-18 21:00:00');
});

it('is the current round while its window is still open', function () {
    Carbon::setTestNow('2026-08-15 12:00:00');

    $round1 = Round::factory()->create(['number' => 1]);
    Fixture::factory()->for($round1)->create(['kickoff_at' => '2026-08-17 21:00:00']);

    $round2 = Round::factory()->create(['number' => 2]);
    Fixture::factory()->for($round2)->create(['kickoff_at' => '2026-08-24 21:00:00']);

    expect(Round::current()->is($round1))->toBeTrue();
});

it('is still the current round exactly at the 24 hour boundary', function () {
    Carbon::setTestNow('2026-08-18 21:00:00');

    $round1 = Round::factory()->create(['number' => 1]);
    Fixture::factory()->for($round1)->create(['kickoff_at' => '2026-08-17 21:00:00']);

    $round2 = Round::factory()->create(['number' => 2]);
    Fixture::factory()->for($round2)->create(['kickoff_at' => '2026-08-24 21:00:00']);

    expect(Round::current()->is($round1))->toBeTrue();
});

it('moves on to the next round once the previous one closes', function () {
    Carbon::setTestNow('2026-08-18 21:00:01');

    $round1 = Round::factory()->create(['number' => 1]);
    Fixture::factory()->for($round1)->create(['kickoff_at' => '2026-08-17 21:00:00']);

    $round2 = Round::factory()->create(['number' => 2]);
    Fixture::factory()->for($round2)->create(['kickoff_at' => '2026-08-24 21:00:00']);

    expect(Round::current()->is($round2))->toBeTrue();
});

it('falls back to the last round once the whole season has closed', function () {
    Carbon::setTestNow('2099-01-01 00:00:00');

    $round1 = Round::factory()->create(['number' => 1]);
    Fixture::factory()->for($round1)->create(['kickoff_at' => '2026-08-17 21:00:00']);

    $round2 = Round::factory()->create(['number' => 2]);
    Fixture::factory()->for($round2)->create(['kickoff_at' => '2026-08-24 21:00:00']);

    expect(Round::current()->is($round2))->toBeTrue();
});

it('returns null when no rounds exist', function () {
    expect(Round::current())->toBeNull();
});
