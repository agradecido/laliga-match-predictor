<?php

use App\Models\Round;
use App\Models\Season;
use App\Models\User;

it('forbids non-admins from every admin rounds route', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $season = Season::factory()->create();
    $round = Round::factory()->for($season)->create();

    $this->actingAs($user)->get('/admin/rounds')->assertForbidden();
    $this->actingAs($user)->get('/admin/rounds/create')->assertForbidden();
    $this->actingAs($user)->post('/admin/rounds', [
        'season_id' => $season->id,
        'number' => 1,
        'match_date' => now()->addWeek()->toDateString(),
    ])->assertForbidden();
    $this->actingAs($user)->get("/admin/rounds/{$round->id}/edit")->assertForbidden();
    $this->actingAs($user)->put("/admin/rounds/{$round->id}", [
        'season_id' => $season->id,
        'number' => $round->number,
        'match_date' => $round->match_date->toDateString(),
    ])->assertForbidden();
});

it('lets an admin create and update a round', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $season = Season::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/rounds', [
        'season_id' => $season->id,
        'number' => 5,
        'match_date' => now()->addWeek()->toDateString(),
    ]);

    $response->assertRedirect();
    $round = Round::where('season_id', $season->id)->where('number', 5)->firstOrFail();

    $this->actingAs($admin)->put("/admin/rounds/{$round->id}", [
        'season_id' => $season->id,
        'number' => 6,
        'match_date' => now()->addWeeks(2)->toDateString(),
    ])->assertRedirect();

    expect($round->refresh()->number)->toBe(6);
});

it('rejects a duplicate round number within the same season', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $season = Season::factory()->create();
    Round::factory()->for($season)->create(['number' => 3]);

    $this->actingAs($admin)->post('/admin/rounds', [
        'season_id' => $season->id,
        'number' => 3,
        'match_date' => now()->addWeek()->toDateString(),
    ])->assertSessionHasErrors('number');
});
