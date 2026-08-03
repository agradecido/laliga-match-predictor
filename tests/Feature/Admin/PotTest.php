<?php

use App\Models\PotPayment;
use App\Models\Round;
use App\Models\Season;
use App\Models\User;

it('forbids non-admins from every admin pot route', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $other = User::factory()->create();
    $season = Season::factory()->create();
    Round::factory()->for($season)->create();

    $this->actingAs($user)->get('/admin/pot')->assertForbidden();
    $this->actingAs($user)->put('/admin/pot/fee', ['entry_fee' => 10])->assertForbidden();
    $this->actingAs($user)->post("/admin/pot/payments/{$other->id}/toggle")->assertForbidden();
    $this->actingAs($user)->post('/admin/pot/payout', ['winner_user_id' => $other->id])->assertForbidden();
});

it('lets an admin set the entry fee for the active season', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $season = Season::factory()->create();
    Round::factory()->for($season)->create();

    $this->actingAs($admin)->put('/admin/pot/fee', ['entry_fee' => 15.5])->assertRedirect();

    expect($season->refresh()->entry_fee)->toBe('15.50');
});

it('lets an admin toggle a player payment on and off', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $player = User::factory()->create();
    $season = Season::factory()->create();
    Round::factory()->for($season)->create();

    $this->actingAs($admin)->post("/admin/pot/payments/{$player->id}/toggle")->assertRedirect();

    $payment = PotPayment::where('season_id', $season->id)->where('user_id', $player->id)->firstOrFail();
    expect($payment->paid_at)->not->toBeNull();
    expect($payment->marked_by_id)->toBe($admin->id);

    $this->actingAs($admin)->post("/admin/pot/payments/{$player->id}/toggle")->assertRedirect();

    expect($payment->refresh()->paid_at)->toBeNull();
});

it('lets an admin mark the payout with a chosen winner', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $winner = User::factory()->create();
    $season = Season::factory()->create();
    Round::factory()->for($season)->create();

    $this->actingAs($admin)->post('/admin/pot/payout', ['winner_user_id' => $winner->id])->assertRedirect();

    $season->refresh();
    expect($season->winner_user_id)->toBe($winner->id);
    expect($season->payout_at)->not->toBeNull();
    expect($season->payout_marked_by_id)->toBe($admin->id);
});
