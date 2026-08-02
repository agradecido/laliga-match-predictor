<?php

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\User;

it('forbids non-admins from every admin predictions route', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $round = Round::factory()->create();
    $fixture = Fixture::factory()->for($round)->create();
    $player = User::factory()->create();

    $this->actingAs($user)->get('/admin/predictions')->assertForbidden();
    $this->actingAs($user)->get("/admin/predictions/{$round->id}")->assertForbidden();
    $this->actingAs($user)->put("/admin/predictions/{$round->id}", [
        'entries' => [['user_id' => $player->id, 'fixture_id' => $fixture->id, 'choice' => '1']],
    ])->assertForbidden();
});

it('lets an admin view the predictions index and edit form', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    Fixture::factory()->for($round)->create();

    $this->actingAs($admin)->get('/admin/predictions')->assertOk();
    $this->actingAs($admin)->get("/admin/predictions/{$round->id}")->assertOk();
});

it('lets an admin create a missing prediction for a player', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    $fixture = Fixture::factory()->for($round)->create();
    $player = User::factory()->create();

    $response = $this->actingAs($admin)->put("/admin/predictions/{$round->id}", [
        'entries' => [['user_id' => $player->id, 'fixture_id' => $fixture->id, 'choice' => '1']],
    ]);

    $response->assertRedirect();
    expect(Prediction::where(['user_id' => $player->id, 'fixture_id' => $fixture->id])->sole()->choice)->toBe('1');
});

it('lets an admin correct an existing prediction', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    $fixture = Fixture::factory()->for($round)->create();
    $player = User::factory()->create();
    Prediction::factory()->for($player)->for($fixture)->create(['choice' => '1']);

    $this->actingAs($admin)->put("/admin/predictions/{$round->id}", [
        'entries' => [['user_id' => $player->id, 'fixture_id' => $fixture->id, 'choice' => 'X']],
    ])->assertRedirect();

    expect(Prediction::where(['user_id' => $player->id, 'fixture_id' => $fixture->id])->sole()->choice)->toBe('X');
});

it('lets an admin delete a prediction by submitting a null choice', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    $fixture = Fixture::factory()->for($round)->create();
    $player = User::factory()->create();
    Prediction::factory()->for($player)->for($fixture)->create(['choice' => '2']);

    $this->actingAs($admin)->put("/admin/predictions/{$round->id}", [
        'entries' => [['user_id' => $player->id, 'fixture_id' => $fixture->id, 'choice' => null]],
    ])->assertRedirect();

    expect(Prediction::where(['user_id' => $player->id, 'fixture_id' => $fixture->id])->exists())->toBeFalse();
});

it('rejects a fixture that belongs to a different round', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $round = Round::factory()->create();
    $otherRound = Round::factory()->create();
    $foreignFixture = Fixture::factory()->for($otherRound)->create();
    $player = User::factory()->create();

    $response = $this->actingAs($admin)->put("/admin/predictions/{$round->id}", [
        'entries' => [['user_id' => $player->id, 'fixture_id' => $foreignFixture->id, 'choice' => '1']],
    ]);

    $response->assertSessionHasErrors();
    expect(Prediction::where('fixture_id', $foreignFixture->id)->exists())->toBeFalse();
});
