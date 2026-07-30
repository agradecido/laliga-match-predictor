<?php

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\Round;
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
