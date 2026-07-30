<?php

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\Round;
use App\Models\User;

it('lets an authenticated user submit predictions for an open round', function () {
    $user = User::factory()->create();
    $round = Round::factory()->open()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $response = $this->actingAs($user)->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [
            ['fixture_id' => $fixture->id, 'choice' => '1'],
        ],
    ]);

    $response->assertRedirect();

    expect(Prediction::query()->where([
        'user_id' => $user->id,
        'fixture_id' => $fixture->id,
    ])->sole()->choice)->toBe('1');
});

it('updates an existing prediction instead of duplicating it', function () {
    $user = User::factory()->create();
    $round = Round::factory()->open()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $this->actingAs($user)->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [['fixture_id' => $fixture->id, 'choice' => '1']],
    ]);

    $this->actingAs($user)->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [['fixture_id' => $fixture->id, 'choice' => 'X']],
    ]);

    expect(Prediction::where(['user_id' => $user->id, 'fixture_id' => $fixture->id])->count())->toBe(1)
        ->and(Prediction::where(['user_id' => $user->id, 'fixture_id' => $fixture->id])->sole()->choice)->toBe('X');
});

it('rejects predictions for a locked round and writes nothing', function () {
    $user = User::factory()->create();
    $round = Round::factory()->locked()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $response = $this->actingAs($user)->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [['fixture_id' => $fixture->id, 'choice' => '1']],
    ]);

    $response->assertForbidden();
    expect(Prediction::count())->toBe(0);
});

it('rejects a fixture that belongs to a different round', function () {
    $user = User::factory()->create();
    $round = Round::factory()->open()->create();
    $otherRound = Round::factory()->open()->create();
    $foreignFixture = Fixture::factory()->for($otherRound)->create();

    $response = $this->actingAs($user)->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [['fixture_id' => $foreignFixture->id, 'choice' => '1']],
    ]);

    $response->assertSessionHasErrors();
    expect(Prediction::count())->toBe(0);
});

it('rejects an invalid choice', function () {
    $user = User::factory()->create();
    $round = Round::factory()->open()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $response = $this->actingAs($user)->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [['fixture_id' => $fixture->id, 'choice' => 'Z']],
    ]);

    $response->assertSessionHasErrors();
    expect(Prediction::count())->toBe(0);
});

it('redirects guests to the login page', function () {
    $round = Round::factory()->open()->create();
    $fixture = Fixture::factory()->for($round)->create();

    $response = $this->post("/quiniela/{$round->id}/predictions", [
        'predictions' => [['fixture_id' => $fixture->id, 'choice' => '1']],
    ]);

    $response->assertRedirect('/login');
    expect(Prediction::count())->toBe(0);
});
