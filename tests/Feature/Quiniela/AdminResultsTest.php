<?php

use App\Models\Fixture;
use App\Models\Round;
use App\Models\User;

it('forbids non-admins from every admin results route', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $round = Round::factory()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $this->actingAs($user)->get('/admin/results')->assertForbidden();
    $this->actingAs($user)->get("/admin/results/{$round->id}")->assertForbidden();
    $this->actingAs($user)->put("/admin/results/{$round->id}", [
        'scores' => [['fixture_id' => $fixture->id, 'home_score' => 1, 'away_score' => 0]],
    ])->assertForbidden();
});

it('lets an admin view the results index and edit form', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    Fixture::factory()->for($round)->create();

    $this->actingAs($admin)->get('/admin/results')->assertOk();
    $this->actingAs($admin)->get("/admin/results/{$round->id}")->assertOk();
});

it('lets an admin submit scores for a round', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $response = $this->actingAs($admin)->put("/admin/results/{$round->id}", [
        'scores' => [['fixture_id' => $fixture->id, 'home_score' => 2, 'away_score' => 1]],
    ]);

    $response->assertRedirect();
    expect($fixture->refresh()->home_score)->toBe(2)
        ->and($fixture->refresh()->away_score)->toBe(1);
});

it('rejects a fixture that belongs to a different round', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    $otherRound = Round::factory()->create();
    $foreignFixture = Fixture::factory()->for($otherRound)->create();

    $response = $this->actingAs($admin)->put("/admin/results/{$round->id}", [
        'scores' => [['fixture_id' => $foreignFixture->id, 'home_score' => 1, 'away_score' => 0]],
    ]);

    $response->assertSessionHasErrors();
    expect($foreignFixture->refresh()->home_score)->toBeNull();
});

it('allows submitting scores for an already-locked round', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->locked()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $response = $this->actingAs($admin)->put("/admin/results/{$round->id}", [
        'scores' => [['fixture_id' => $fixture->id, 'home_score' => 3, 'away_score' => 3]],
    ]);

    $response->assertRedirect();
    expect($fixture->refresh()->home_score)->toBe(3);
});
