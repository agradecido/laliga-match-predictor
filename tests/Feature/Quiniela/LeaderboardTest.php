<?php

use App\Models\Fixture;
use App\Models\PotPayment;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\Season;
use App\Models\User;

it('ranks users by total correct predictions, ignoring unplayed fixtures', function () {
    $alice = User::factory()->create(['name' => 'Alice']);
    $bob = User::factory()->create(['name' => 'Bob']);
    $carol = User::factory()->create(['name' => 'Carol']);

    $round = Round::factory()->create();

    $playedFixtureOne = Fixture::factory()->for($round)->played(2, 0)->create();
    $playedFixtureTwo = Fixture::factory()->for($round)->played(1, 1)->create();
    $unplayedFixture = Fixture::factory()->for($round)->create();

    // Alice: correct on both played fixtures, plus a pick on the unplayed one.
    Prediction::factory()->create(['user_id' => $alice->id, 'fixture_id' => $playedFixtureOne->id, 'choice' => '1']);
    Prediction::factory()->create(['user_id' => $alice->id, 'fixture_id' => $playedFixtureTwo->id, 'choice' => 'X']);
    Prediction::factory()->create(['user_id' => $alice->id, 'fixture_id' => $unplayedFixture->id, 'choice' => '1']);

    // Bob: correct on one, wrong on the other.
    Prediction::factory()->create(['user_id' => $bob->id, 'fixture_id' => $playedFixtureOne->id, 'choice' => '1']);
    Prediction::factory()->create(['user_id' => $bob->id, 'fixture_id' => $playedFixtureTwo->id, 'choice' => '2']);

    // Carol: wrong on both.
    Prediction::factory()->create(['user_id' => $carol->id, 'fixture_id' => $playedFixtureOne->id, 'choice' => '2']);
    Prediction::factory()->create(['user_id' => $carol->id, 'fixture_id' => $playedFixtureTwo->id, 'choice' => '1']);

    $response = $this->actingAs($alice)->get('/quiniela-leaderboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('leaderboard', [
            ['name' => 'Alice', 'points' => 2],
            ['name' => 'Bob', 'points' => 1],
            ['name' => 'Carol', 'points' => 0],
        ])
    );
});

it('omits the pot when the active season has no entry fee configured', function () {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    Round::factory()->for($season)->create();

    $response = $this->actingAs($user)->get('/quiniela-leaderboard');

    $response->assertInertia(fn ($page) => $page->where('pot', null));
});

it('shows the pot summary when the active season has an entry fee configured', function () {
    $alice = User::factory()->create(['name' => 'Alice']);
    $bob = User::factory()->create(['name' => 'Bob']);
    $season = Season::factory()->create(['entry_fee' => 10]);
    Round::factory()->for($season)->create();

    PotPayment::factory()->for($season)->for($alice)->create(['paid_at' => now()]);

    $response = $this->actingAs($alice)->get('/quiniela-leaderboard');

    $response->assertInertia(fn ($page) => $page
        ->where('pot.entry_fee', 10)
        ->where('pot.total_collected', 10)
        ->where('pot.total_expected', 20)
        ->where('pot.has_paid', true)
        ->where('pot.winner_name', null)
    );

    $response = $this->actingAs($bob)->get('/quiniela-leaderboard');

    $response->assertInertia(fn ($page) => $page->where('pot.has_paid', false));
});

it('shows the winner once the pot has been paid out', function () {
    $alice = User::factory()->create(['name' => 'Alice']);
    $season = Season::factory()->create(['entry_fee' => 10, 'winner_user_id' => $alice->id, 'payout_at' => now()]);
    Round::factory()->for($season)->create();

    $response = $this->actingAs($alice)->get('/quiniela-leaderboard');

    $response->assertInertia(fn ($page) => $page->where('pot.winner_name', 'Alice'));
});
