<?php

use App\Models\Fixture;
use App\Models\Round;
use App\Models\User;

it('exposes each fixture kickoff time when it is known', function () {
    $user = User::factory()->create();
    $round = Round::factory()->open()->create();
    $fixture = Fixture::factory()->for($round)->create(['kickoff_at' => '2026-08-15 19:30:00']);

    $response = $this->actingAs($user)->get("/quiniela/{$round->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('Quiniela/Show')
        ->where('fixtures.0.kickoff_at', '2026-08-15 19:30:00')
    );
});

it('exposes a null kickoff time when it is not yet known', function () {
    $user = User::factory()->create();
    $round = Round::factory()->open()->create();
    $fixture = Fixture::factory()->for($round)->create(['kickoff_at' => null]);

    $response = $this->actingAs($user)->get("/quiniela/{$round->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('Quiniela/Show')
        ->where('fixtures.0.kickoff_at', null)
    );
});
