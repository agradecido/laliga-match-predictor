<?php

use App\Models\Round;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('is not locked before 08:00 on the match date', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-15 07:59:59'));

    $round = Round::factory()->create(['match_date' => '2026-08-15']);

    expect($round->isLocked())->toBeFalse();
});

it('is locked at exactly 08:00 on the match date', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-15 08:00:00'));

    $round = Round::factory()->create(['match_date' => '2026-08-15']);

    expect($round->isLocked())->toBeTrue();
});

it('is locked after 08:00 on the match date', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-15 08:00:01'));

    $round = Round::factory()->create(['match_date' => '2026-08-15']);

    expect($round->isLocked())->toBeTrue();
});

it('exposes locksAt as 08:00 on the match date', function () {
    $round = Round::factory()->create(['match_date' => '2026-08-15']);

    expect($round->locksAt()->toDateTimeString())->toBe('2026-08-15 08:00:00');
});

it('the open factory state produces an unlocked round', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-15 12:00:00'));

    $round = Round::factory()->open()->create();

    expect($round->isLocked())->toBeFalse();
});

it('the locked factory state produces a locked round', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-15 12:00:00'));

    $round = Round::factory()->locked()->create();

    expect($round->isLocked())->toBeTrue();
});
