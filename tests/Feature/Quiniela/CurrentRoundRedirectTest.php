<?php

use App\Models\Fixture;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('redirects guests to the login page', function () {
    $this->get('/')
        ->assertRedirect(route('login', absolute: false));
});

it('redirects an authenticated user straight to the current round', function () {
    Carbon::setTestNow('2026-08-15 12:00:00');

    $round = Round::factory()->create(['number' => 1]);
    Fixture::factory()->for($round)->create(['kickoff_at' => '2026-08-17 21:00:00']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('quiniela.show', $round, absolute: false));
});

it('redirects an authenticated user to the rounds list when none is scheduled yet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('quiniela.index', absolute: false));
});
